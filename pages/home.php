<?php
$pageTitle = 'Vi Vu Việt — Khám phá Việt Nam theo cách của bạn';
$featured = featured_places($pdo, 6);
$articles = latest_articles($pdo, 3);
$stats = $pdo->query("SELECT (SELECT COUNT(*) FROM places WHERE status='approved') places, (SELECT COUNT(*) FROM provinces) provinces, (SELECT COUNT(*) FROM users WHERE status='active') users")->fetch();
$regionCards = $pdo->query("SELECT r.*, COUNT(DISTINCT p.id) province_count, COUNT(DISTINCT pl.id) place_count FROM regions r LEFT JOIN provinces p ON p.region_id=r.id LEFT JOIN places pl ON pl.province_id=p.id AND pl.status='approved' GROUP BY r.id ORDER BY r.id")->fetchAll();
?>
<section class="hero">
    <div class="hero-bg"></div><div class="hero-shade"></div>
    <div class="container hero-content">
        <div class="eyebrow light"><span></span> Đi để yêu thêm Việt Nam</div>
        <h1>Mỗi hành trình,<br><em>một Việt Nam mới.</em></h1>
        <p>Khám phá những miền đất đẹp, lắng nghe câu chuyện địa phương và chia sẻ nơi chốn bạn yêu.</p>
        <form class="hero-search" method="get" action="<?= url('search') ?>">
            <input type="hidden" name="page" value="search">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input name="q" type="search" placeholder="Bạn muốn khám phá nơi nào?" aria-label="Tìm địa điểm">
            <button type="submit">Khám phá ngay</button>
        </form>
        <div class="hero-chips"><span>Đang được yêu thích:</span><a href="<?= url('search',['q'=>'Hạ Long']) ?>">Hạ Long</a><a href="<?= url('search',['q'=>'Hội An']) ?>">Hội An</a><a href="<?= url('search',['q'=>'Phú Quốc']) ?>">Phú Quốc</a></div>
    </div>
    <a class="scroll-cue" href="#explore"><span>Cuộn để khám phá</span><i>↓</i></a>
</section>

<section class="section region-section" id="explore">
    <div class="container">
        <div class="section-heading centered"><div class="eyebrow"><span></span> Ba miền thương nhớ <span></span></div><h2>Chọn một miền, bắt đầu hành trình</h2><p>Mỗi miền mang một nhịp sống, một cảnh sắc và những câu chuyện rất riêng.</p></div>
        <div class="region-cards">
            <?php $regionImages = ['mien-bac'=>'https://images.unsplash.com/photo-1521993117367-b7f70ccd029d?auto=format&fit=crop&w=1200&q=85','mien-trung'=>'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=1200&q=85','mien-nam'=>'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=85']; ?>
            <?php foreach ($regionCards as $index => $region): ?>
                <a class="region-card" href="<?= url('region',['slug'=>$region['slug']]) ?>" style="--bg:url('<?= e($regionImages[$region['slug']]) ?>')">
                    <span class="region-number">0<?= $index+1 ?></span><div><small><?= (int)$region['province_count'] ?> tỉnh, thành phố</small><h3><?= e($region['name']) ?></h3><p><?= e($region['description']) ?></p><b>Khám phá <?= e($region['name']) ?> <i>→</i></b></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section featured-section">
    <div class="container">
        <div class="section-heading split"><div><div class="eyebrow"><span></span> Điểm đến nổi bật</div><h2>Những nơi nhất định phải đến</h2></div><a class="text-link" href="<?= url('articles') ?>">Xem tất cả <span>→</span></a></div>
        <div class="place-grid">
            <?php foreach ($featured as $place): ?>
                <article class="place-card">
                    <a class="place-image" href="<?= url('place',['slug'=>$place['slug']]) ?>"><img src="<?= e(place_image($place['cover_image'])) ?>" alt="<?= e($place['name']) ?>" loading="lazy"><span><?= e($place['category_name'] ?? 'Khám phá') ?></span></a>
                    <div class="place-body"><div class="place-meta"><span>⌖ <?= e($place['province_name']) ?></span><span class="rating">★ <?= $place['rating_avg'] ?: 'Mới' ?></span></div><h3><a href="<?= url('place',['slug'=>$place['slug']]) ?>"><?= e($place['name']) ?></a></h3><p><?= e($place['short_description']) ?></p><a class="card-link" href="<?= url('place',['slug'=>$place['slug']]) ?>">Xem hành trình <span>→</span></a></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="community-banner">
    <div class="container community-inner">
        <div class="community-visual"><span class="photo photo-1"></span><span class="photo photo-2"></span><div class="community-badge"><strong>+<?= max(1200, (int)$stats['users']) ?></strong><small>người yêu du lịch</small></div></div>
        <div class="community-copy"><div class="eyebrow light"><span></span> Cùng nhau kể chuyện Việt Nam</div><h2>Bạn biết một nơi thật đẹp?</h2><p>Chia sẻ góc nhỏ quê hương, quán ăn thân thuộc hay một điểm vui chơi ít người biết. Mỗi đóng góp của bạn có thể là cảm hứng cho hành trình tiếp theo của ai đó.</p><div class="community-actions"><a class="button button-light" href="<?= url('submit-place') ?>">+ Chia sẻ địa danh</a><a href="<?= url('about') ?>">Tìm hiểu cộng đồng →</a></div></div>
    </div>
</section>

<section class="section journal-section">
    <div class="container"><div class="section-heading split"><div><div class="eyebrow"><span></span> Cảm hứng lên đường</div><h2>Chuyện kể từ những hành trình</h2></div><a class="text-link" href="<?= url('articles') ?>">Đọc tất cả bài viết <span>→</span></a></div>
        <div class="article-grid">
            <?php foreach ($articles as $article): ?>
                <article class="article-card"><a class="article-image" href="<?= url('article',['slug'=>$article['slug']]) ?>"><img src="<?= e(place_image($article['cover_image'])) ?>" alt="<?= e($article['title']) ?>" loading="lazy"></a><div class="article-body"><div class="article-kicker"><?= e($article['region_name']) ?> · <?= format_date($article['published_at']) ?></div><h3><a href="<?= url('article',['slug'=>$article['slug']]) ?>"><?= e($article['title']) ?></a></h3><p><?= e($article['excerpt']) ?></p><div class="article-footer"><span>♥ <?= (int)$article['likes_count'] ?> &nbsp; ◌ <?= (int)$article['comments_count'] ?></span><a href="<?= url('article',['slug'=>$article['slug']]) ?>">Đọc tiếp →</a></div></div></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="stats-strip"><div class="container"><div><strong><?= (int)$stats['places'] ?>+</strong><span>Địa điểm được chia sẻ</span></div><div><strong><?= (int)$stats['provinces'] ?></strong><span>Tỉnh, thành phố</span></div><div><strong><?= max(1200,(int)$stats['users']) ?>+</strong><span>Thành viên khám phá</span></div><div><strong>3</strong><span>Miền thương nhớ</span></div></div></section>
