<?php
if ($currentUser && $currentUser['role'] === 'customer') {
    redirect(url('profile'));
}
$pageTitle = 'Đăng nhập thành viên — Vi Vu Việt';
?>
<section class="auth-page">
    <div class="auth-visual">
        <div>
            <div class="eyebrow light"><span></span> Đi để nhớ, về để kể</div>
            <blockquote>“Mỗi vùng đất đều có một câu chuyện. Hãy để hành trình của bạn trở thành một phần trong đó.”
            </blockquote>
        </div>
    </div>
    <div class="auth-panel"><a class="brand" href="<?= url() ?>"><span class="brand-mark"><svg viewBox="0 0 48 48"><path
                            d="M8 33c8-1 10-16 19-16 6 0 7 8 13 9-4 1-7 3-10 7H8Z"/><path
                            d="M13 18c4-8 11-11 19-8-8 2-12 7-14 13"/></svg></span><span><strong>Vi Vu</strong><small>VIỆT</small></span></a>
        <div class="auth-form-wrap"><span class="form-icon">⌖</span>
            <h1>Chào mừng trở lại</h1>
            <p>Đăng nhập bằng tài khoản thành viên để tiếp tục hành trình.</p>
            <form method="post" action="<?= url('login') ?>" class="form-stack"><?= csrf_field() ?><input type="hidden"
                                                                                                          name="action"
                                                                                                          value="login"><input
                        type="hidden" name="return_to" value="<?= e(url('login')) ?>"><label>Email<input name="email"
                                                                                                         type="email"
                                                                                                         autocomplete="email"
                                                                                                         placeholder="ban@example.com"
                                                                                                         required></label><label>Mật
                    khẩu<input name="password" type="password" autocomplete="current-password" placeholder="••••••••"
                               required></label>
                <button class="button button-block" type="submit">Đăng nhập <span>→</span></button>
            </form>
            <div class="demo-accounts"><strong>Tài khoản dùng thử</strong>
                <button type="button" data-fill-email="user@vivuviet.vn" data-fill-password="User@123">Customer</button>
            </div>
            <p class="auth-switch">Chưa có tài khoản? <a href="<?= url('register') ?>">Tham gia cộng đồng</a></p>
            <p class="auth-switch admin-entry">Bạn là quản trị viên? <a href="<?= admin_url() ?>">Đến cổng quản trị</a>
            </p>
        </div>
    </div>
</section>
