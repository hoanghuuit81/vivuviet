</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand-light" href="<?= url() ?>">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 48 48"><path
                                d="M8 33c8-1 10-16 19-16 6 0 7 8 13 9-4 1-7 3-10 7H8Z"/><path
                                d="M13 18c4-8 11-11 19-8-8 2-12 7-14 13"/></svg></span>
                <span><strong>Vi Vu</strong><small>VIỆT</small></span>
            </a>
            <p>Cộng đồng khám phá và chia sẻ những vẻ đẹp chân thật trên khắp Việt Nam.</p>
        </div>
        <div><h3>Khám phá</h3><a href="<?= url('region', ['slug' => 'mien-bac']) ?>">Miền Bắc</a><a
                    href="<?= url('region', ['slug' => 'mien-trung']) ?>">Miền Trung</a><a
                    href="<?= url('region', ['slug' => 'mien-nam']) ?>">Miền Nam</a></div>
        <div><h3>Cộng đồng</h3><a href="<?= url('articles') ?>">Cẩm nang du lịch</a><a
                    href="<?= url('submit-place') ?>">Đóng góp địa danh</a><a href="<?= url('contact') ?>">Liên hệ</a><a
                    href="<?= url('about') ?>">Về Vi Vu Việt</a></div>
        <div class="footer-newsletter"><h3>Nhận cảm hứng mới</h3>
            <p>Những điểm đến và câu chuyện được chọn lọc.</p>
            <form><input type="email" aria-label="Email" placeholder="Email của bạn">
                <button type="button">→</button>
            </form>
        </div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> Vi Vu Việt. Made with ♥ in Vietnam.</span><span>Điều khoản · Quyền riêng tư</span>
    </div>
</footer>
<button class="back-to-top" type="button" aria-label="Lên đầu trang">↑</button>
<script src="<?= asset('js/app.js') ?>?v=1"></script>
</body>
</html>
