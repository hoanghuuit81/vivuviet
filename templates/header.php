<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="theme-color" content="#0d5c46">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1">
    <link rel="stylesheet" href="<?= asset('css/hero-opt.css') ?>?v=1">
    <?php if (in_array($page, ['profile', 'contact'], true)): ?>
        <link rel="stylesheet" href="<?= asset('css/client-account.css') ?>?v=1">
    <?php endif; ?>
    <?php if (in_array($page, ['admin', 'admin-login', 'admin-place-create'], true)): ?>
        <link rel="stylesheet" href="<?= asset('css/admin-gate.css') ?>?v=1">
    <?php endif; ?>
    <?php if (in_array($page, ['submit-place', 'admin-place-create'], true)): ?>
        <link rel="stylesheet" href="<?= asset('css/form-validation.css') ?>?v=1">
        <link rel="stylesheet" href="<?= asset('ckeditor5-48.4.0/ckeditor5/ckeditor5.css') ?>">
        <link rel="stylesheet" href="<?= asset('ckeditor5-48.4.0/ckeditor5/ckeditor5-content.css') ?>">
        <script type="importmap">{"imports":{"ckeditor5":"<?= asset('ckeditor5-48.4.0/ckeditor5/ckeditor5.js') ?>","ckeditor5/":"<?= asset('ckeditor5-48.4.0/ckeditor5/') ?>"}}</script>
        <script type="module" src="<?= asset('js/ckeditor-init.js') ?>?v=2"></script>
    <?php endif; ?>
</head>
<body class="page-<?= e($page) ?>">
<a class="skip-link" href="#main-content">Đi đến nội dung chính</a>
<header class="site-header" id="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= url() ?>" aria-label="Vi Vu Việt - Trang chủ">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 48 48"><path d="M8 33c8-1 10-16 19-16 6 0 7 8 13 9-4 1-7 3-10 7H8Z"/><path d="M13 18c4-8 11-11 19-8-8 2-12 7-14 13"/></svg>
            </span>
            <span><strong>Vi Vu</strong><small>VIỆT</small></span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Mở menu" aria-expanded="false" aria-controls="main-nav">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="main-nav" aria-label="Điều hướng chính">
            <a class="<?= $page === 'home' ? 'active' : '' ?>" href="<?= url() ?>">Trang chủ</a>
            <div class="nav-dropdown">
                <button class="dropdown-trigger <?= in_array($page, ['region','province','place'], true) ? 'active' : '' ?>" type="button" aria-expanded="false">
                    Địa danh <span aria-hidden="true">⌄</span>
                </button>
                <div class="dropdown-menu">
                    <div class="dropdown-intro">
                        <span>Khám phá dải đất chữ S</span>
                        <strong>Chọn miền bạn muốn đi</strong>
                    </div>
                    <?php foreach ($regionsForMenu as $menuRegion): ?>
                        <a href="<?= url('region', ['slug'=>$menuRegion['slug']]) ?>">
                            <span class="region-dot dot-<?= e($menuRegion['slug']) ?>"></span>
                            <span><strong><?= e($menuRegion['name']) ?></strong><small><?= e($menuRegion['description']) ?></small></span>
                            <b aria-hidden="true">→</b>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a class="<?= $page === 'articles' || $page === 'article' ? 'active' : '' ?>" href="<?= url('articles') ?>">Bài viết</a>
            <a class="<?= $page === 'contact' ? 'active' : '' ?>" href="<?= url('contact') ?>">Liên hệ</a>
            <a class="<?= $page === 'about' ? 'active' : '' ?>" href="<?= url('about') ?>">Về chúng tôi</a>
        </nav>

        <div class="nav-actions">
            <button class="icon-button search-open" type="button" aria-label="Tìm kiếm">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            </button>
            <?php if ($currentUser): ?>
                <?php
                $notificationStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
                $notificationStmt->execute([$currentUser['id']]);
                $notificationCount = (int)$notificationStmt->fetchColumn();
                ?>
                <div class="user-menu nav-dropdown">
                    <button class="user-trigger dropdown-trigger" type="button" aria-expanded="false">
                        <?= avatar_markup($currentUser) ?>
                        <span class="user-name"><?= e($currentUser['name']) ?></span>
                        <?php if ($notificationCount): ?><i><?= $notificationCount ?></i><?php endif; ?>
                    </button>
                    <div class="dropdown-menu user-dropdown">
                        <div class="user-summary"><strong><?= e($currentUser['name']) ?></strong><small><?= e($currentUser['email']) ?></small></div>
                        <a href="<?= url('profile') ?>">Hồ sơ của tôi</a>
                        <a href="<?= url('profile',['tab'=>'favorites']) ?>">Bài viết đã thích</a>
                        <a href="<?= url('profile',['tab'=>'submissions']) ?>">Địa danh đã thêm</a>
                        <a href="<?= url('profile',['tab'=>'interactions']) ?>">Bình luận & đánh giá</a>
                        <a href="<?= url('submit-place') ?>">Thêm địa danh mới</a>
                        <form method="post" action="<?= url() ?>">
                            <?= csrf_field() ?><input type="hidden" name="action" value="logout">
                            <button type="submit">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a class="login-link" href="<?= url('login') ?>">Đăng nhập</a>
                <a class="button button-small" href="<?= url('register') ?>">Tham gia</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="search-overlay" role="dialog" aria-modal="true" aria-label="Tìm kiếm" aria-hidden="true">
    <button class="search-close" type="button" aria-label="Đóng">×</button>
    <form action="<?= url('search') ?>" method="get" class="search-overlay-form">
        <input type="hidden" name="page" value="search">
        <label for="global-search">Bạn muốn đi đâu?</label>
        <div><input id="global-search" name="q" type="search" placeholder="Thử “Hội An”, “biển”, “Tây Bắc”…" required><button type="submit">Tìm kiếm</button></div>
        <p>Gợi ý: Hạ Long · Hội An · Phú Quốc · Huế</p>
    </form>
</div>

<?php foreach (consume_flashes() as $flash): ?>
    <div class="toast toast-<?= e($flash['type']) ?>" role="status"><span><?= e($flash['message']) ?></span><button type="button" aria-label="Đóng">×</button></div>
<?php endforeach; ?>

<main id="main-content">
