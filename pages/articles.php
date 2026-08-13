<?php
$pageTitle = 'Cẩm nang & bài viết du lịch — Vi Vu Việt';
$regionId  = filter_input(INPUT_GET, 'region', FILTER_VALIDATE_INT);
$sort      = (string) ($_GET['sort'] ?? 'latest');
$sql       = article_metrics_sql() . " WHERE a.status='published' AND p.status='approved'";
$params    = [];
if ($regionId) {
    $sql      .= ' AND r.id=?';
    $params[] = $regionId;
}
$sql  .= $sort === 'popular' ? ' ORDER BY likes_count DESC,a.published_at DESC' : ' ORDER BY a.is_featured DESC,a.published_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>
<section class="page-hero articles-hero">
    <div class="container">
        <div class="eyebrow light"><span></span> Cẩm nang Vi Vu</div>
        <h1>Chuyện kể từ những hành trình</h1>
        <p>Kinh nghiệm chân thật, lịch trình hữu ích và những góc nhìn truyền cảm hứng.</p></div>
</section>
<section class="section">
    <div class="container">
        <div class="content-toolbar">
            <div class="filter-chips"><a class="<?= !$regionId ? 'active' : '' ?>"
                                         href="<?= url('articles', ['sort' => $sort]) ?>">Tất
                    cả</a><?php foreach ($regionsForMenu as $r): ?><a
                    class="<?= $regionId == (int) $r['id'] ? 'active' : '' ?>"href="<?= url('articles',
                            ['region' => $r['id'], 'sort' => $sort]) ?>"><?= e($r['name']) ?></a><?php endforeach; ?>
            </div>
            <div class="sort-links"><span>Sắp xếp:</span><a class="<?= $sort === 'latest' ? 'active' : '' ?>"
                                                            href="<?= url('articles', array_filter([
                                                                    'region' => $regionId,
                                                                    'sort'   => 'latest'
                                                            ])) ?>">Mới nhất</a><a
                        class="<?= $sort === 'popular' ? 'active' : '' ?>"
                        href="<?= url('articles', array_filter(['region' => $regionId, 'sort' => 'popular'])) ?>">Yêu
                    thích</a></div>
        </div>
        <div class="article-grid article-grid-wide"><?php foreach ($articles as $article): ?>
                <article class="article-card"><a class="article-image"
                                                 href="<?= url('article', ['slug' => $article['slug']]) ?>"><img
                            src="<?= e(place_image($article['cover_image'])) ?>" alt="<?= e($article['title']) ?>"></a>
                <div class="article-body">
                    <div class="article-kicker"><?= e($article['region_name']) ?>
                        · <?= format_date($article['published_at']) ?></div>
                    <h3><a href="<?= url('article', ['slug' => $article['slug']]) ?>"><?= e($article['title']) ?></a>
                    </h3>
                    <p><?= e($article['excerpt']) ?></p>
                    <div class="article-footer">
                        <span>♥ <?= (int) $article['likes_count'] ?> &nbsp; ◌ <?= (int) $article['comments_count'] ?></span><a
                                href="<?= url('article', ['slug' => $article['slug']]) ?>">Đọc tiếp →</a></div>
                </div></article><?php endforeach; ?></div>
    </div>
</section>
