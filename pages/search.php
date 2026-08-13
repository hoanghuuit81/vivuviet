<?php
$q         = trim((string) ($_GET['q'] ?? ''));
$pageTitle = ($q ? 'Tìm kiếm “' . $q . '”' : 'Tìm kiếm') . ' — Vi Vu Việt';
$places    = [];
$articles  = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT p.*,pr.name province_name,c.name category_name FROM places p JOIN provinces pr ON pr.id=p.province_id LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='approved' AND (p.name LIKE ? OR p.short_description LIKE ? OR pr.name LIKE ?) ORDER BY p.is_featured DESC LIMIT 20");
    $stmt->execute([$like, $like, $like]);
    $places = $stmt->fetchAll();
    $stmt   = $pdo->prepare(article_metrics_sql() . " WHERE a.status='published' AND p.status='approved' AND (a.title LIKE ? OR a.excerpt LIKE ? OR p.name LIKE ?) ORDER BY a.published_at DESC LIMIT 20");
    $stmt->execute([$like, $like, $like]);
    $articles = $stmt->fetchAll();
}
?>
<section class="page-hero search-page-hero">
    <div class="container narrow">
        <div class="eyebrow light"><span></span> Tìm kiếm</div>
        <h1>Tìm hành trình của bạn</h1>
        <form class="hero-search" method="get" action="<?= url('search') ?>"><input type="hidden" name="page"
                                                                                    value="search"><input name="q"
                                                                                                          value="<?= e($q) ?>"
                                                                                                          type="search"
                                                                                                          placeholder="Tên địa điểm, tỉnh thành, trải nghiệm…"
                                                                                                          autofocus
                                                                                                          required>
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>
</section>
<section class="section">
    <div class="container"><?php if ($q === ''): ?>
            <div class="empty-state"><span>⌕</span>
                <h2>Bạn đang tìm nơi nào?</h2>
                <p>Nhập tên địa điểm hoặc trải nghiệm muốn khám phá.</p></div><?php elseif (!$places && !$articles): ?>
            <div class="empty-state"><span>⌕</span>
            <h2>Chưa tìm thấy “<?= e($q) ?>”</h2>
            <p>Thử một từ khóa ngắn hơn hoặc đóng góp địa danh bạn biết.</p><a class="button"
                                                                               href="<?= url('submit-place') ?>">Thêm
                địa danh</a></div><?php else: ?>
            <div class="section-heading"><h2>Kết quả cho “<?= e($q) ?>”</h2>
            <p>Tìm thấy <?= count($places) + count($articles) ?> kết quả.</p></div><?php if ($places): ?><h3
                    class="result-heading">Địa điểm (<?= count($places) ?>)</h3>
                <div class="place-grid"><?php foreach ($places as $place): ?>
                    <article class="place-card"><a class="place-image"
                                                   href="<?= url('place', ['slug' => $place['slug']]) ?>"><img
                                src="<?= e(place_image($place['cover_image'])) ?>" alt="<?= e($place['name']) ?>"></a>
                    <div class="place-body">
                        <div class="place-meta">⌖ <?= e($place['province_name']) ?></div>
                        <h3><a href="<?= url('place', ['slug' => $place['slug']]) ?>"><?= e($place['name']) ?></a></h3>
                        <p><?= e($place['short_description']) ?></p></div></article><?php endforeach; ?>
                </div><?php endif; ?><?php if ($articles): ?><h3 class="result-heading">Bài viết
                (<?= count($articles) ?>)</h3>
                <div class="article-grid"><?php foreach ($articles as $article): ?>
                    <article class="article-card"><a class="article-image"
                                                     href="<?= url('article', ['slug' => $article['slug']]) ?>"><img
                                src="<?= e(place_image($article['cover_image'])) ?>" alt="<?= e($article['title']) ?>"></a>
                    <div class="article-body">
                        <div class="article-kicker"><?= e($article['province_name']) ?></div>
                        <h3><a href="<?= url('article',
                                    ['slug' => $article['slug']]) ?>"><?= e($article['title']) ?></a></h3>
                        <p><?= e($article['excerpt']) ?></p></div></article><?php endforeach; ?>
                </div><?php endif; ?><?php endif; ?></div>
</section>
