<?php
if ($currentUser && $currentUser['role'] === 'admin') {
    redirect(admin_url('dashboard'));
}
$pageTitle = 'Đăng nhập quản trị — Vi Vu Việt';
?>
<section class="admin-login-page">
    <div class="admin-login-panel">
        <a class="brand brand-light" href="<?= url() ?>"><span class="brand-mark"><svg viewBox="0 0 48 48"><path d="M8 33c8-1 10-16 19-16 6 0 7 8 13 9-4 1-7 3-10 7H8Z"/><path d="M13 18c4-8 11-11 19-8-8 2-12 7-14 13"/></svg></span><span><strong>Vi Vu</strong><small>ADMIN PORTAL</small></span></a>
        <div class="admin-login-card"><span class="admin-lock">⌘</span><p class="eyebrow"><span></span> Khu vực bảo mật</p><h1>Đăng nhập quản trị</h1><p>Chỉ tài khoản có quyền quản trị mới có thể truy cập bảng điều khiển.</p>
            <form method="post" action="<?= admin_url() ?>" class="form-stack"><?= csrf_field() ?><input type="hidden" name="action" value="admin_login"><input type="hidden" name="return_to" value="<?= e(admin_url()) ?>"><label>Email quản trị<input name="email" type="email" autocomplete="username" placeholder="admin@vivuviet.vn" required></label><label>Mật khẩu<input name="password" type="password" autocomplete="current-password" placeholder="••••••••" required></label><button class="button button-block" type="submit">Vào trang quản trị →</button></form>
            <div class="admin-demo"><span>Tài khoản demo:</span><button type="button" data-fill-email="admin@vivuviet.vn" data-fill-password="Admin@123">Điền tài khoản admin</button></div>
            <a class="admin-back" href="<?= url('login') ?>">← Quay về đăng nhập thành viên</a>
        </div>
    </div>
</section>
