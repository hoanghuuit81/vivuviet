<?php if ($currentUser) {
    redirect(url('profile'));
}
$pageTitle = 'Tham gia cộng đồng — Vi Vu Việt'; ?>
<section class="auth-page register-page">
    <div class="auth-visual">
        <div>
            <div class="eyebrow light"><span></span> Cùng kể chuyện Việt Nam</div>
            <blockquote>“Một góc quê bạn yêu có thể trở thành hành trình đáng nhớ của một người khác.”</blockquote>
        </div>
    </div>
    <div class="auth-panel"><a class="brand" href="<?= url() ?>"><span class="brand-mark"><svg viewBox="0 0 48 48"><path
                            d="M8 33c8-1 10-16 19-16 6 0 7 8 13 9-4 1-7 3-10 7H8Z"/><path
                            d="M13 18c4-8 11-11 19-8-8 2-12 7-14 13"/></svg></span><span><strong>Vi Vu</strong><small>VIỆT</small></span></a>
        <div class="auth-form-wrap"><span class="form-icon">✦</span>
            <h1>Tham gia Vi Vu Việt</h1>
            <p>Lưu lại nơi yêu thích và chia sẻ địa danh của bạn.</p>
            <form method="post" action="<?= url('register') ?>" class="form-stack"><?= csrf_field() ?><input
                        type="hidden" name="action" value="register"><input type="hidden" name="return_to"
                                                                            value="<?= e(url('register')) ?>"><label>Họ
                    và tên<input name="name" minlength="2" maxlength="100" autocomplete="name"
                                 placeholder="Nguyễn Minh Anh" required></label><label>Email<input name="email"
                                                                                                   type="email"
                                                                                                   autocomplete="email"
                                                                                                   placeholder="ban@example.com"
                                                                                                   required></label><label>Mật
                    khẩu<input name="password" type="password" minlength="8" autocomplete="new-password"
                               placeholder="Ít nhất 8 ký tự, gồm chữ và số" required></label><label>Xác nhận mật
                    khẩu<input name="password_confirmation" type="password" minlength="8" autocomplete="new-password"
                               placeholder="Nhập lại mật khẩu" required></label>
                <button class="button button-block" type="submit">Tạo tài khoản <span>→</span></button>
            </form>
            <p class="form-note">Bằng việc đăng ký, bạn đồng ý giữ gìn một cộng đồng chia sẻ văn minh.</p>
            <p class="auth-switch">Đã có tài khoản? <a href="<?= url('login') ?>">Đăng nhập</a></p></div>
    </div>
</section>
