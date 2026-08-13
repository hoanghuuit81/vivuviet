<?php
$user = require_login();
if ($user['role'] !== 'customer') {
    redirect(admin_url('dashboard'));
}
$pageTitle = 'Hồ sơ của tôi — Vi Vu Việt';
$tab       = (string) ($_GET['tab'] ?? 'overview');
$tabs      = ['overview', 'favorites', 'submissions', 'interactions', 'notifications', 'settings'];
if (!in_array($tab, $tabs, true)) {
    $tab = 'overview';
}

$countStmt = $pdo->prepare('SELECT (SELECT COUNT(*) FROM article_likes WHERE user_id=?) favorites,(SELECT COUNT(*) FROM places WHERE submitted_by=?) submissions,(SELECT COUNT(*) FROM comments WHERE user_id=?) comments_count,(SELECT COUNT(*) FROM ratings WHERE user_id=?) ratings_count');
$countStmt->execute([$user['id'], $user['id'], $user['id'], $user['id']]);
$counts        = $countStmt->fetch();
$favorites     = [];
$submissions   = [];
$comments      = [];
$ratings       = [];
$notifications = [];
if (in_array($tab, ['overview', 'favorites'], true)) {
    $stmt = $pdo->prepare(article_metrics_sql() . " JOIN article_likes my_likes ON my_likes.article_id=a.id AND my_likes.user_id=? WHERE a.status='published' AND p.status='approved' ORDER BY my_likes.created_at DESC");
    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll();
}
if (in_array($tab, ['overview', 'submissions'], true)) {
    $stmt = $pdo->prepare('SELECT p.*,pr.name province_name,c.name category_name FROM places p JOIN provinces pr ON pr.id=p.province_id LEFT JOIN categories c ON c.id=p.category_id WHERE p.submitted_by=? ORDER BY p.created_at DESC');
    $stmt->execute([$user['id']]);
    $submissions = $stmt->fetchAll();
}
if (in_array($tab, ['interactions'], true)) {
    $stmt = $pdo->prepare('SELECT c.*,a.title article_title,a.slug article_slug,p.name place_name FROM comments c JOIN articles a ON a.id=c.article_id JOIN places p ON p.id=a.place_id WHERE c.user_id=? ORDER BY c.created_at DESC');
    $stmt->execute([$user['id']]);
    $comments = $stmt->fetchAll();
    $stmt     = $pdo->prepare('SELECT r.*,a.title article_title,a.slug article_slug,p.name place_name FROM ratings r JOIN articles a ON a.id=r.article_id JOIN places p ON p.id=a.place_id WHERE r.user_id=? ORDER BY r.updated_at DESC');
    $stmt->execute([$user['id']]);
    $ratings = $stmt->fetchAll();
}
if (in_array($tab, ['overview', 'notifications'], true)) {
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 30');
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll();
    if ($tab === 'notifications') {
        $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$user['id']]);
    }
}
?>
<section class="client-profile-hero">
    <div class="container">
        <div class="profile-identity"><?= avatar_markup($user, 'profile-avatar') ?>
            <div>
                <div class="eyebrow"><span></span> Hành trình của tôi</div>
                <h1><?= e($user['name']) ?></h1>
                <p>Thành viên Vi Vu Việt từ <?= format_date($user['created_at']) ?></p></div>
        </div>
        <a class="button button-outline" href="<?= url('submit-place') ?>">+ Chia sẻ địa danh</a></div>
</section>
<section class="client-profile-section">
    <div class="container">
        <nav class="client-profile-nav" aria-label="Quản lý tài khoản"><a
                    class="<?= $tab === 'overview' ? 'active' : '' ?>" href="<?= url('profile') ?>">Tổng quan</a><a
                    class="<?= $tab === 'favorites' ? 'active' : '' ?>"
                    href="<?= url('profile', ['tab' => 'favorites']) ?>">Bài viết đã thích
                <b><?= $counts['favorites'] ?></b></a><a class="<?= $tab === 'submissions' ? 'active' : '' ?>"
                                                         href="<?= url('profile', ['tab' => 'submissions']) ?>">Địa danh
                đã thêm <b><?= $counts['submissions'] ?></b></a><a
                    class="<?= $tab === 'interactions' ? 'active' : '' ?>"
                    href="<?= url('profile', ['tab' => 'interactions']) ?>">Bình luận & đánh giá</a><a
                    class="<?= $tab === 'notifications' ? 'active' : '' ?>"
                    href="<?= url('profile', ['tab' => 'notifications']) ?>">Thông báo</a><a
                    class="<?= $tab === 'settings' ? 'active' : '' ?>"
                    href="<?= url('profile', ['tab' => 'settings']) ?>">Cài đặt</a></nav>
        <?php if ($tab === 'overview'): ?>
            <div class="client-stats">
            <div><span>♥</span><strong><?= $counts['favorites'] ?></strong><small>Bài viết đã thích</small></div>
            <div><span>⌖</span><strong><?= $counts['submissions'] ?></strong><small>Địa danh đóng góp</small></div>
            <div><span>◌</span><strong><?= $counts['comments_count'] ?></strong><small>Bình luận đã viết</small></div>
            <div><span>★</span><strong><?= $counts['ratings_count'] ?></strong><small>Lượt đánh giá</small></div></div>
            <div class="client-profile-grid">
                <section class="client-panel">
                    <header>
                        <div><h2>Địa danh gần đây</h2>
                            <p>Những nơi bạn đã chia sẻ với cộng đồng.</p></div>
                        <a href="<?= url('profile', ['tab' => 'submissions']) ?>">Xem tất cả →</a>
                    </header><?php if ($submissions): ?>
                        <div class="client-mini-list"><?php foreach (array_slice($submissions, 0, 3) as $item): ?>
                            <article><img src="<?= e(place_image($item['cover_image'])) ?>" alt="">
                            <div><strong><?= e($item['name']) ?></strong><small>⌖ <?= e($item['province_name']) ?>
                                    · <?= format_date($item['created_at']) ?></small></div>
                            <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                            </article><?php endforeach; ?></div><?php else: ?>
                        <div class="client-empty-mini">Bạn chưa chia sẻ địa danh nào. <a
                                href="<?= url('submit-place') ?>">Đóng góp ngay →</a></div><?php endif; ?></section>
                <section class="client-panel">
                    <header>
                        <div><h2>Câu chuyện đã lưu</h2>
                            <p>Những cảm hứng bạn muốn quay lại.</p></div>
                        <a href="<?= url('profile', ['tab' => 'favorites']) ?>">Xem tất cả →</a>
                    </header><?php if ($favorites): ?>
                        <div class="client-mini-list"><?php foreach (array_slice($favorites, 0, 3) as $item): ?>
                            <article><img src="<?= e(place_image($item['cover_image'])) ?>" alt="">
                            <div><strong><?= e($item['title']) ?></strong><small>♥ <?= $item['likes_count'] ?>
                                    · <?= e($item['place_name']) ?></small></div></article><?php endforeach; ?>
                        </div><?php else: ?>
                        <div class="client-empty-mini">Lưu bài viết yêu thích để xem lại tại đây.</div><?php endif; ?>
                </section>
            </div>
        <?php elseif ($tab === 'favorites'): ?>
            <div class="client-page-heading"><h2>Bài viết đã thích</h2>
                <p>Những câu chuyện bạn muốn lưu lại cho chuyến đi tiếp theo.</p></div><?php if ($favorites): ?>
                <div class="article-grid client-article-grid"><?php foreach ($favorites as $item): ?>
                    <article class="article-card"><a class="article-image"
                                                     href="<?= url('article', ['slug' => $item['slug']]) ?>"><img
                                src="<?= e(place_image($item['cover_image'])) ?>" alt="<?= e($item['title']) ?>"></a>
                    <div class="article-body">
                        <div class="article-kicker"><?= e($item['province_name']) ?></div>
                        <h3><a href="<?= url('article', ['slug' => $item['slug']]) ?>"><?= e($item['title']) ?></a></h3>
                        <p><?= e($item['excerpt']) ?></p></div></article><?php endforeach; ?></div><?php else: ?>
                <div class="empty-state"><span>♡</span>
                <h2>Chưa có bài viết đã thích</h2>
                <p>Thả tim những bài viết hữu ích để tìm lại nhanh chóng.</p><a class="button"
                                                                                href="<?= url('articles') ?>">Khám phá
                    bài viết</a></div><?php endif; ?>
        <?php elseif ($tab === 'submissions'): ?>
            <div class="client-page-heading split">
            <div><h2>Địa danh đã thêm</h2>
                <p>Theo dõi từng đóng góp của bạn cho cộng đồng.</p></div>
            <a class="button" href="<?= url('submit-place') ?>">+ Thêm địa danh</a></div><?php if ($submissions): ?>
                <div class="client-submission-list"><?php foreach ($submissions as $item): ?>
                    <article><img src="<?= e(place_image($item['cover_image'])) ?>" alt="">
                    <div class="client-submission-copy"><h3><?= e($item['name']) ?></h3>
                        <p>⌖ <?= e($item['province_name']) ?> · <?= e($item['category_name'] ?: 'Chưa phân loại') ?></p>
                        <small>Gửi
                            ngày <?= format_date($item['created_at']) ?></small><?php if ($item['admin_note']): ?>
                            <blockquote><strong>Ghi chú từ admin:</strong> <?= e($item['admin_note']) ?>
                            </blockquote><?php endif; ?></div>
                    <div class="client-submission-actions"><em
                                class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em><?php if (in_array($item['status'],
                                ['changes_requested', 'rejected', 'pending'], true)): ?><a
                            href="<?= url('submit-place', ['edit' => $item['id']]) ?>">Chỉnh sửa</a><?php else: ?><a
                            href="<?= url('place', ['slug' => $item['slug']]) ?>">Xem trang →</a><?php endif; ?></div>
                    </article><?php endforeach; ?></div><?php else: ?>
                <div class="empty-state"><span>⌖</span>
                <h2>Chia sẻ góc nhỏ bạn yêu</h2>
                <p>Địa danh bạn biết có thể là cảm hứng cho hành trình tiếp theo.</p><a class="button"
                                                                                        href="<?= url('submit-place') ?>">Thêm
                    địa danh đầu tiên</a></div><?php endif; ?>
        <?php elseif ($tab === 'interactions'): ?>
            <div class="client-page-heading"><h2>Bình luận & đánh giá của bạn</h2>
                <p>Xem lại mọi nhận xét và số sao bạn đã để lại.</p></div>
            <div class="interaction-columns">
                <section class="client-panel">
                    <header>
                        <div><h2>Bình luận <span><?= count($comments) ?></span></h2>
                            <p>Những trao đổi bạn đã gửi.</p></div>
                    </header><?php foreach ($comments as $item): ?>
                        <article class="interaction-item">
                        <div><a href="<?= url('article',
                                    ['slug' => $item['article_slug']]) ?>"><strong><?= e($item['article_title']) ?></strong></a><small><?= e($item['place_name']) ?>
                                · <?= format_date($item['created_at']) ?></small>
                            <p><?= nl2br(e($item['content'])) ?></p></div>
                        <form method="post" action="<?= url('profile', ['tab' => 'interactions']) ?>"
                              onsubmit="return confirm('Xóa bình luận này?')"><?= csrf_field() ?><input type="hidden"
                                                                                                        name="action"
                                                                                                        value="delete_comment"><input
                                    type="hidden" name="comment_id" value="<?= $item['id'] ?>"><input type="hidden"
                                                                                                      name="return_to"
                                                                                                      value="<?= e(url('profile',
                                                                                                              ['tab' => 'interactions'])) ?>">
                            <button class="text-danger" type="submit">Xóa</button>
                        </form></article><?php endforeach; ?><?php if (!$comments): ?>
                        <div class="client-empty-mini">Bạn chưa viết bình luận nào.</div><?php endif; ?></section>
                <section class="client-panel">
                    <header>
                        <div><h2>Đánh giá <span><?= count($ratings) ?></span></h2>
                            <p>Những bài viết bạn đã chấm điểm.</p></div>
                    </header><?php foreach ($ratings as $item): ?>
                        <article class="interaction-item rating-item">
                        <div><a href="<?= url('article',
                                    ['slug' => $item['article_slug']]) ?>"><strong><?= e($item['article_title']) ?></strong></a><small><?= e($item['place_name']) ?>
                                · Cập nhật <?= format_date($item['updated_at']) ?></small>
                            <p class="rating-stars"><?= str_repeat('★', (int) $item['score']) ?>
                                <span><?= str_repeat('★', 5 - (int) $item['score']) ?></span> <b><?= $item['score'] ?>
                                    /5</b></p></div>
                        <a class="text-link" href="<?= url('article', ['slug' => $item['article_slug']]) ?>#reviews">Sửa
                            →</a></article><?php endforeach; ?><?php if (!$ratings): ?>
                        <div class="client-empty-mini">Bạn chưa đánh giá bài viết nào.</div><?php endif; ?></section>
            </div>
        <?php elseif ($tab === 'notifications'): ?>
            <div class="client-page-heading"><h2>Thông báo</h2>
                <p>Cập nhật mới nhất về những đóng góp của bạn.</p></div>
            <section class="client-panel notification-panel"><?php foreach ($notifications as $notice): ?><a
                    class="client-notice <?= !$notice['is_read'] ? 'unread' : '' ?>"
                    href="<?= e($notice['link'] ?: '#') ?>"><span>◉</span>
                    <div><strong><?= e($notice['title']) ?></strong>
                        <p><?= e($notice['message']) ?></p><small><?= format_date($notice['created_at']) ?></small>
                    </div></a><?php endforeach; ?><?php if (!$notifications): ?>
                    <div class="client-empty-mini">Chưa có thông báo nào.</div><?php endif; ?></section>
        <?php else: ?>
            <div class="client-page-heading"><h2>Cài đặt hồ sơ</h2>
                <p>Ảnh và tên này sẽ hiển thị cùng đóng góp của bạn.</p></div>
            <form class="client-settings-form" method="post" enctype="multipart/form-data"
                  action="<?= url('profile', ['tab' => 'settings']) ?>"><?= csrf_field() ?><input type="hidden"
                                                                                                  name="action"
                                                                                                  value="profile_update">
            <input type="hidden" name="return_to" value="<?= e(url('profile', ['tab' => 'settings'])) ?>">
            <div class="avatar-editor"><?= avatar_markup($user, 'profile-avatar profile-avatar-edit') ?><label
                        class="avatar-upload-button">Đổi ảnh<input name="avatar" type="file"
                                                                   accept="image/jpeg,image/png,image/webp"></label><small>JPG,
                    PNG hoặc WebP · Tối đa 2MB</small></div>
            <div class="form-grid"><label class="span-2">Họ và tên<input name="name" value="<?= e($user['name']) ?>"
                                                                         minlength="2" maxlength="100" required></label><label
                        class="span-2">Email<input value="<?= e($user['email']) ?>" disabled><small>Email chưa thể thay
                        đổi trong phiên bản này.</small></label></div>
            <button class="button" type="submit">Lưu thay đổi</button></form><?php endif; ?>
    </div>
</section>
