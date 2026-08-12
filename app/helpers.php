<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $route = 'home', array $params = []): string
{
    global $config;
    if ($route === 'admin') {
        return admin_url('dashboard', $params);
    }
    if ($route === 'admin-login') {
        return admin_url('', $params);
    }
    if ($route === 'admin-place-create') {
        return admin_url('places/new', $params);
    }
    $query = $route === 'home' && !$params ? '' : '?' . http_build_query(array_merge(['page' => $route], $params));
    return $config['base_url'] . '/' . $query;
}

function admin_url(string $path = '', array $params = []): string
{
    global $config;
    $path = trim($path, '/');
    $suffix = $path ? '/admin/' . $path : '/admin';
    return $config['base_url'] . $suffix . ($params ? '?' . http_build_query($params) : '');
}

function asset(string $path): string
{
    global $config;
    return $config['base_url'] . '/assets/' . ltrim($path, '/');
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function safe_return_url(?string $candidate, ?string $fallback = null): string
{
    global $config;
    $fallback ??= url();
    if (!$candidate || !str_starts_with($candidate, $config['base_url'])) {
        return $fallback;
    }
    return $candidate;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf'] ?? '') . '">';
}

function verify_csrf(): void
{
    $token = (string) ($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function current_user(): ?array
{
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, name, email, avatar, role, status, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] !== 'active') {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('warning', 'Vui lòng đăng nhập để sử dụng tính năng này.');
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? url();
        redirect(url('login'));
    }
    return $user;
}

function require_admin(): array
{
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        flash('error', 'Vui lòng đăng nhập bằng tài khoản quản trị để truy cập khu vực này.');
        redirect(admin_url());
    }
    return $user;
}

function require_customer(): array
{
    $user = require_login();
    if ($user['role'] !== 'customer') {
        flash('error', 'Tính năng này chỉ dành cho tài khoản thành viên.');
        redirect(url());
    }
    return $user;
}

function slugify(string $text): string
{
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'dia-diem';
}

function unique_slug(PDO $pdo, string $table, string $text): string
{
    if (!in_array($table, ['places', 'articles'], true)) {
        throw new InvalidArgumentException('Bảng không hợp lệ');
    }
    $base = slugify($text);
    $slug = $base;
    $counter = 2;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE slug = ?");
    while (true) {
        $stmt->execute([$slug]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $counter++;
    }
}

function excerpt(string $text, int $length = 150): string
{
    $plain = trim(strip_tags($text));
    return mb_strlen($plain) <= $length ? $plain : mb_substr($plain, 0, $length - 1) . '…';
}

function format_date(string $date): string
{
    return date('d/m/Y', strtotime($date));
}

function status_label(string $status): string
{
    return [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'changes_requested' => 'Cần chỉnh sửa',
        'rejected' => 'Bị từ chối',
        'visible' => 'Đang hiển thị',
        'hidden' => 'Đã ẩn',
        'published' => 'Đã xuất bản',
        'draft' => 'Bản nháp',
        'active' => 'Hoạt động',
        'blocked' => 'Đã khóa',
        'new' => 'Mới',
        'read' => 'Đã đọc',
        'resolved' => 'Đã xử lý',
    ][$status] ?? $status;
}

function place_image(?string $image): string
{
    return $image ?: asset('images/placeholder-place.svg');
}

function avatar_markup(?array $user, string $class = 'avatar'): string
{
    $name = (string) ($user['name'] ?? 'V');
    $avatar = (string) ($user['avatar'] ?? '');
    if ($avatar !== '') {
        return '<span class="' . e($class) . ' avatar-image"><img src="' . e($avatar) . '" alt="Ảnh đại diện của ' . e($name) . '"></span>';
    }
    return '<span class="' . e($class) . '">' . e(mb_strtoupper(mb_substr($name, 0, 1))) . '</span>';
}

function sanitize_rich_html(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote><a><figure><img>';
    $html = trim(strip_tags($html, $allowed));
    return preg_replace_callback('/<\s*(\/?)\s*(p|br|strong|b|em|i|u|ul|ol|li|h2|h3|blockquote|a|figure|img)\b([^>]*)>/i', static function (array $match): string {
        $closing = $match[1] === '/';
        $tag = strtolower($match[2]);
        if ($closing) {
            return '</' . $tag . '>';
        }
        if ($tag === 'img') {
            $src = '';
            $alt = '';
            if (preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $match[3], $srcMatch)) {
                $src = trim($srcMatch[2]);
            }
            if (!preg_match('#^https?://#i', $src) && !str_starts_with($src, '/')) {
                return '';
            }
            if (preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $match[3], $altMatch)) {
                $alt = trim($altMatch[2]);
            }
            return '<img src="' . e($src) . '" alt="' . e($alt) . '">';
        }
        if ($tag !== 'a') {
            return '<' . $tag . '>';
        }
        $href = '';
        if (preg_match('/\bhref\s*=\s*(["\'])(.*?)\1/i', $match[3], $hrefMatch)) {
            $href = trim($hrefMatch[2]);
        }
        if (!preg_match('#^(https?://|mailto:)#i', $href)) {
            return '<a>';
        }
        return '<a href="' . e($href) . '" rel="nofollow noopener" target="_blank">';
    }, $html) ?? '';
}

function rich_content(string $value): string
{
    $safe = sanitize_rich_html($value);
    if ($safe === '') {
        return '';
    }
    if (!str_contains($safe, '<')) {
        return '<p>' . nl2br(e($safe)) . '</p>';
    }
    return $safe;
}

function upload_avatar(array $file): ?string
{
    global $config;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Ảnh đại diện không hợp lệ hoặc lớn hơn 2MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Ảnh đại diện chỉ chấp nhận JPG, PNG hoặc WebP.');
    }
    if (!is_dir($config['avatar_uploads_dir']) && !mkdir($config['avatar_uploads_dir'], 0775, true)) {
        throw new RuntimeException('Không thể tạo thư mục ảnh đại diện.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $config['avatar_uploads_dir'] . '/' . $filename)) {
        throw new RuntimeException('Không thể lưu ảnh đại diện.');
    }
    return $config['avatar_uploads_url'] . '/' . $filename;
}

function upload_place_image(array $file): ?string
{
    global $config;
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Ảnh không hợp lệ hoặc lớn hơn 5MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.');
    }
    if (!is_dir($config['uploads_dir']) && !mkdir($config['uploads_dir'], 0775, true)) {
        throw new RuntimeException('Không thể tạo thư mục ảnh.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $config['uploads_dir'] . '/' . $filename)) {
        throw new RuntimeException('Không thể lưu ảnh đã tải lên.');
    }
    return $config['uploads_url'] . '/' . $filename;
}
