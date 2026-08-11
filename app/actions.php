<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

verify_csrf();
$action = (string) ($_POST['action'] ?? '');
$returnTo = safe_return_url($_POST['return_to'] ?? null, url());

try {
    switch ($action) {
        case 'login':
            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password'])) {
                throw new RuntimeException('Email hoặc mật khẩu chưa đúng.');
            }
            if ($user['status'] !== 'active') {
                throw new RuntimeException('Tài khoản này đang bị khóa.');
            }
            if ($user['role'] !== 'customer') {
                throw new RuntimeException('Tài khoản quản trị cần đăng nhập tại cổng quản trị riêng.');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $target = $_SESSION['intended_url'] ?? url('profile');
            unset($_SESSION['intended_url']);
            flash('success', 'Chào mừng ' . $user['name'] . ' quay trở lại!');
            redirect(safe_return_url($target));

        case 'admin_login':
            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password']) || $user['role'] !== 'admin') {
                throw new RuntimeException('Thông tin đăng nhập không hợp lệ hoặc tài khoản không có quyền quản trị.');
            }
            if ($user['status'] !== 'active') {
                throw new RuntimeException('Tài khoản quản trị này đang bị khóa.');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            flash('success', 'Đăng nhập quản trị thành công.');
            redirect(admin_url('dashboard'));

        case 'register':
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                throw new RuntimeException('Họ tên cần từ 2 đến 100 ký tự.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Email không hợp lệ.');
            }
            if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                throw new RuntimeException('Mật khẩu cần ít nhất 8 ký tự, gồm chữ và số.');
            }
            if ($password !== (string) ($_POST['password_confirmation'] ?? '')) {
                throw new RuntimeException('Xác nhận mật khẩu chưa khớp.');
            }
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
            try {
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            } catch (PDOException $exception) {
                if ((int) $exception->errorInfo[1] === 1062) {
                    throw new RuntimeException('Email này đã được sử dụng.');
                }
                throw $exception;
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            flash('success', 'Tạo tài khoản thành công. Chào mừng bạn đến với Vi Vu Việt!');
            redirect(url('profile'));

        case 'logout':
            unset($_SESSION['user_id']);
            session_regenerate_id(true);
            flash('success', 'Bạn đã đăng xuất. Hẹn sớm gặp lại!');
            redirect(url());

        case 'profile_update':
            $user = require_customer();
            $name = trim((string) ($_POST['name'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
                throw new RuntimeException('Họ tên cần từ 2 đến 100 ký tự.');
            }
            $avatar = upload_avatar($_FILES['avatar'] ?? []);
            $stmt = $pdo->prepare('UPDATE users SET name=?, avatar=COALESCE(?, avatar) WHERE id=?');
            $stmt->execute([$name, $avatar, $user['id']]);
            flash('success', 'Đã cập nhật hồ sơ.');
            redirect(url('profile', ['tab' => 'settings']));

        case 'contact_submit':
            $user = current_user();
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            $subject = trim((string) ($_POST['subject'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($subject) < 3 || mb_strlen($message) < 10) {
                throw new RuntimeException('Vui lòng điền đầy đủ họ tên, email, chủ đề và nội dung liên hệ.');
            }
            if (preg_match('/[\r\n]/', $subject . $email)) {
                throw new RuntimeException('Thông tin liên hệ không hợp lệ.');
            }
            $pdo->prepare('INSERT INTO contact_messages (user_id,name,email,phone,subject,message) VALUES (?,?,?,?,?,?)')
                ->execute([$user['id'] ?? null, $name, $email, $phone ?: null, $subject, $message]);
            $mailSubject = 'Vi Vu Việt | Liên hệ mới: ' . $subject;
            $mailBody = "Họ tên: {$name}\nEmail: {$email}\nSố điện thoại: {$phone}\n\nNội dung:\n{$message}";
            $headers = "From: Vi Vu Việt <no-reply@localhost>\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8";
            $sent = @mail($config['contact_email'], $mailSubject, $mailBody, $headers);
            flash('success', $sent ? 'Cảm ơn bạn! Tin nhắn đã được gửi tới đội ngũ Vi Vu Việt.' : 'Cảm ơn bạn! Tin nhắn đã được ghi nhận, đội ngũ sẽ phản hồi sớm.');
            redirect(url('contact'));

        case 'toggle_like':
            $user = require_customer();
            $articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
            $check = $pdo->prepare("SELECT id FROM articles WHERE id=? AND status='published'");
            $check->execute([$articleId]);
            if (!$check->fetchColumn()) {
                throw new RuntimeException('Bài viết không tồn tại.');
            }
            $stmt = $pdo->prepare('SELECT 1 FROM article_likes WHERE article_id=? AND user_id=?');
            $stmt->execute([$articleId, $user['id']]);
            if ($stmt->fetchColumn()) {
                $pdo->prepare('DELETE FROM article_likes WHERE article_id=? AND user_id=?')->execute([$articleId, $user['id']]);
                flash('success', 'Đã bỏ bài viết khỏi danh sách yêu thích.');
            } else {
                $pdo->prepare('INSERT INTO article_likes (article_id,user_id) VALUES (?,?)')->execute([$articleId, $user['id']]);
                flash('success', 'Đã lưu bài viết vào mục yêu thích.');
            }
            redirect($returnTo);

        case 'rate_article':
            $user = require_customer();
            $articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
            $score = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT);
            if (!$articleId || !$score || $score < 1 || $score > 5) {
                throw new RuntimeException('Vui lòng chọn số sao hợp lệ.');
            }
            $stmt = $pdo->prepare("SELECT id FROM articles WHERE id=? AND status='published'");
            $stmt->execute([$articleId]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('Bài viết không tồn tại.');
            }
            $pdo->prepare('INSERT INTO ratings (article_id,user_id,score) VALUES (?,?,?) ON DUPLICATE KEY UPDATE score=VALUES(score), updated_at=CURRENT_TIMESTAMP')->execute([$articleId, $user['id'], $score]);
            flash('success', 'Cảm ơn bạn đã đánh giá ' . $score . ' sao!');
            redirect($returnTo . '#reviews');

        case 'add_comment':
            $user = require_customer();
            $articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
            $content = trim((string) ($_POST['content'] ?? ''));
            if (mb_strlen($content) < 2 || mb_strlen($content) > 1500) {
                throw new RuntimeException('Bình luận cần từ 2 đến 1.500 ký tự.');
            }
            $stmt = $pdo->prepare("SELECT id FROM articles WHERE id=? AND status='published'");
            $stmt->execute([$articleId]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('Bài viết không tồn tại.');
            }
            $pdo->prepare('INSERT INTO comments (article_id,user_id,content) VALUES (?,?,?)')->execute([$articleId, $user['id'], $content]);
            flash('success', 'Bình luận của bạn đã được đăng.');
            redirect($returnTo . '#comments');

        case 'delete_comment':
            $user = require_customer();
            $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
            $stmt = $pdo->prepare('DELETE FROM comments WHERE id=? AND user_id=?');
            $stmt->execute([$commentId, $user['id']]);
            flash('success', $stmt->rowCount() ? 'Đã xóa bình luận.' : 'Không thể xóa bình luận này.');
            redirect($returnTo . '#comments');

        case 'submit_place':
        case 'admin_submit_place':
            $user = $action === 'admin_submit_place' ? require_admin() : require_customer();
            if ($action === 'submit_place' && $user['role'] !== 'customer') {
                throw new RuntimeException('Quản trị viên cần thêm địa danh từ khu vực quản trị.');
            }
            $placeId = filter_input(INPUT_POST, 'place_id', FILTER_VALIDATE_INT);
            $name = trim((string) ($_POST['name'] ?? ''));
            $provinceId = filter_input(INPUT_POST, 'province_id', FILTER_VALIDATE_INT);
            $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: null;
            $address = trim((string) ($_POST['address'] ?? ''));
            $short = trim((string) ($_POST['short_description'] ?? ''));
            $description = sanitize_rich_html((string) ($_POST['description'] ?? ''));
            $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
            if (mb_strlen($name) < 3 || !$provinceId || mb_strlen($address) < 5 || mb_strlen($short) < 20 || mb_strlen($description) < 80) {
                throw new RuntimeException('Vui lòng điền đầy đủ thông tin; bài giới thiệu cần ít nhất 80 ký tự.');
            }
            if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('Đường dẫn ảnh không hợp lệ.');
            }
            $uploaded = upload_place_image($_FILES['cover_image'] ?? []);
            $cover = $uploaded ?: ($imageUrl ?: null);
            $status = $action === 'admin_submit_place' ? 'approved' : 'pending';
            if ($placeId) {
                $stmt = $pdo->prepare("SELECT * FROM places WHERE id=? AND submitted_by=? AND status IN ('pending','changes_requested','rejected')");
                $stmt->execute([$placeId, $user['id']]);
                $existing = $stmt->fetch();
                if (!$existing) {
                    throw new RuntimeException('Không thể chỉnh sửa địa điểm này.');
                }
                $cover = $cover ?: $existing['cover_image'];
                $pdo->prepare("UPDATE places SET province_id=?,name=?,category_id=?,address=?,short_description=?,description=?,cover_image=?,price_info=?,opening_hours=?,status='pending',admin_note=NULL WHERE id=?")
                    ->execute([$provinceId,$name,$categoryId,$address,$short,$description,$cover,trim((string)($_POST['price_info']??'')),trim((string)($_POST['opening_hours']??'')),$placeId]);
                flash('success', 'Đã cập nhật và gửi lại địa điểm để admin duyệt.');
            } else {
                $slug = unique_slug($pdo, 'places', $name);
                $stmt = $pdo->prepare('INSERT INTO places (province_id,submitted_by,name,slug,category_id,address,short_description,description,cover_image,price_info,opening_hours,status,approved_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$provinceId,$user['id'],$name,$slug,$categoryId,$address,$short,$description,$cover,trim((string)($_POST['price_info']??'')),trim((string)($_POST['opening_hours']??'')),$status,$status==='approved'?date('Y-m-d H:i:s'):null]);
                $placeId = (int) $pdo->lastInsertId();
                if ($status === 'approved') {
                    $articleSlug = unique_slug($pdo, 'articles', 'Khám phá ' . $name);
                    $pdo->prepare("INSERT INTO articles (place_id,author_id,title,slug,excerpt,content,cover_image,status,published_at) VALUES (?,?,?,?,?,?,?,'published',NOW())")
                        ->execute([$placeId,$user['id'],'Khám phá '.$name,$articleSlug,$short,$description,$cover]);
                }
                flash('success', $status === 'approved' ? 'Đã thêm và xuất bản địa điểm.' : 'Đã gửi địa điểm. Admin sẽ xem xét sớm nhất có thể.');
            }
            redirect($action === 'admin_submit_place' ? admin_url('dashboard', ['tab'=>'places']) : url('profile', ['tab'=>'submissions']));

        case 'moderate_place':
            $admin = require_admin();
            $placeId = filter_input(INPUT_POST, 'place_id', FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? '');
            $note = trim((string) ($_POST['admin_note'] ?? ''));
            if (!in_array($status, ['approved','changes_requested','rejected'], true)) {
                throw new RuntimeException('Trạng thái duyệt không hợp lệ.');
            }
            if ($status !== 'approved' && mb_strlen($note) < 5) {
                throw new RuntimeException('Vui lòng ghi lý do hoặc nội dung cần chỉnh sửa.');
            }
            $stmt = $pdo->prepare('SELECT * FROM places WHERE id=?');
            $stmt->execute([$placeId]);
            $place = $stmt->fetch();
            if (!$place) {
                throw new RuntimeException('Không tìm thấy địa điểm.');
            }
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE places SET status=?,admin_note=?,approved_at=? WHERE id=?')->execute([$status,$note ?: null,$status==='approved'?date('Y-m-d H:i:s'):null,$placeId]);
            if ($status === 'approved') {
                $exists = $pdo->prepare('SELECT id FROM articles WHERE place_id=? LIMIT 1');
                $exists->execute([$placeId]);
                $articleId = $exists->fetchColumn();
                if ($articleId) {
                    $pdo->prepare("UPDATE articles SET status='published',published_at=COALESCE(published_at,NOW()) WHERE id=?")->execute([$articleId]);
                } else {
                    $slug = unique_slug($pdo, 'articles', 'Khám phá ' . $place['name']);
                    $pdo->prepare("INSERT INTO articles (place_id,author_id,title,slug,excerpt,content,cover_image,status,published_at) VALUES (?,?,?,?,?,?,?,'published',NOW())")
                        ->execute([$placeId,$place['submitted_by'],'Khám phá '.$place['name'],$slug,$place['short_description'],$place['description'],$place['cover_image']]);
                }
            }
            if ($place['submitted_by']) {
                $message = $status === 'approved' ? 'Địa điểm của bạn đã được duyệt và xuất bản.' : ($status === 'changes_requested' ? 'Admin yêu cầu bạn chỉnh sửa địa điểm.' : 'Địa điểm của bạn chưa được chấp thuận.');
                $pdo->prepare('INSERT INTO notifications (user_id,title,message,link) VALUES (?,?,?,?)')->execute([$place['submitted_by'],'Cập nhật địa điểm: '.$place['name'],$message . ($note ? ' Ghi chú: '.$note : ''),url('profile',['tab'=>'submissions'])]);
            }
            $pdo->prepare('INSERT INTO moderation_logs (admin_id,place_id,action,note) VALUES (?,?,?,?)')->execute([$admin['id'],$placeId,$status,$note ?: null]);
            $pdo->commit();
            flash('success', 'Đã cập nhật trạng thái địa điểm.');
            redirect(url('admin', ['tab' => 'moderation']));

        case 'admin_toggle_comment':
            require_admin();
            $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
            $status = ($_POST['status'] ?? '') === 'visible' ? 'visible' : 'hidden';
            $pdo->prepare('UPDATE comments SET status=? WHERE id=?')->execute([$status,$commentId]);
            flash('success', 'Đã cập nhật bình luận.');
            redirect(url('admin', ['tab'=>'comments']));

        case 'admin_toggle_user':
            $admin = require_admin();
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $status = ($_POST['status'] ?? '') === 'active' ? 'active' : 'blocked';
            if ((int)$userId === (int)$admin['id']) {
                throw new RuntimeException('Bạn không thể khóa chính tài khoản đang sử dụng.');
            }
            $pdo->prepare("UPDATE users SET status=? WHERE id=? AND role='customer'")->execute([$status,$userId]);
            flash('success', 'Đã cập nhật trạng thái tài khoản.');
            redirect(url('admin', ['tab'=>'users']));

        case 'admin_toggle_feature':
            require_admin();
            $type = (string)($_POST['type'] ?? '');
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $featured = !empty($_POST['featured']) ? 1 : 0;
            if (!in_array($type, ['places','articles'], true)) {
                throw new RuntimeException('Loại nội dung không hợp lệ.');
            }
            $pdo->prepare("UPDATE {$type} SET is_featured=? WHERE id=?")->execute([$featured,$id]);
            flash('success', 'Đã cập nhật nội dung nổi bật.');
            redirect(url('admin', ['tab'=>$type]));

        case 'admin_article_status':
            require_admin();
            $articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
            $status = (string)($_POST['status'] ?? '');
            if (!in_array($status, ['published','hidden','draft'], true)) {
                throw new RuntimeException('Trạng thái bài viết không hợp lệ.');
            }
            $pdo->prepare('UPDATE articles SET status=?,published_at=IF(?="published",COALESCE(published_at,NOW()),published_at) WHERE id=?')->execute([$status,$status,$articleId]);
            flash('success', 'Đã cập nhật trạng thái bài viết.');
            redirect(url('admin', ['tab'=>'articles']));

        case 'admin_contact_status':
            require_admin();
            $messageId = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? '');
            if (!in_array($status, ['new', 'read', 'resolved'], true)) {
                throw new RuntimeException('Trạng thái liên hệ không hợp lệ.');
            }
            $pdo->prepare('UPDATE contact_messages SET status=? WHERE id=?')->execute([$status, $messageId]);
            flash('success', 'Đã cập nhật trạng thái liên hệ.');
            redirect(admin_url('dashboard', ['tab' => 'contacts']));

        default:
            throw new RuntimeException('Thao tác không hợp lệ.');
    }
} catch (RuntimeException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash('error', $exception->getMessage());
    redirect($returnTo);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->__toString());
    flash('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
    redirect($returnTo);
}
