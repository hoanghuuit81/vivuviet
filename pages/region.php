<?php
$slug = (string) ($_GET['slug'] ?? '');
$stmt = $pdo->prepare('SELECT * FROM regions WHERE slug=?');
$stmt->execute([$slug]);
$region = $stmt->fetch();
if (!$region) {
    http_response_code(404);
    require __DIR__ . '/404.php';

    return;
}
$pageTitle = $region['name'] . ' — Vi Vu Việt';
$stmt      = $pdo->prepare("SELECT pr.*, COUNT(pl.id) place_count FROM provinces pr LEFT JOIN places pl ON pl.province_id=pr.id AND pl.status='approved' WHERE pr.region_id=? GROUP BY pr.id ORDER BY pr.name");
$stmt->execute([$region['id']]);
$provinces = $stmt->fetchAll();
?>
<section class="page-hero compact-hero">
    <div class="container">
        <div class="breadcrumbs"><a href="<?= url() ?>">Trang
                chủ</a><span>/</span><span>Địa danh</span><span>/</span><b><?= e($region['name']) ?></b></div>
        <div class="eyebrow light"><span></span> Khám phá theo miền</div>
        <h1><?= e($region['name']) ?></h1>
        <p><?= e($region['description']) ?></p></div>
</section>
<section class="section">
    <div class="container">
        <div class="section-heading split">
            <div><h2>Chọn tỉnh, thành phố</h2>
                <p><?= count($provinces) ?> điểm bắt đầu cho hành trình <?= e(mb_strtolower($region['name'])) ?>.</p>
            </div>
        </div>
        <div class="province-grid">
            <?php foreach ($provinces as $province): ?><a class="province-card"
                                                          href="<?= url('province', ['slug' => $province['slug']]) ?>">
                <div class="province-image"><img src="<?= e(place_image($province['image'])) ?>"
                                                 alt="<?= e($province['name']) ?>"
                                                 loading="lazy"><span><?= (int) $province['place_count'] ?> địa điểm</span>
                </div>
                <div><h3><?= e($province['name']) ?></h3>
                    <p><?= e($province['description'] ?: 'Khám phá những địa điểm và câu chuyện đáng nhớ.') ?></p><b>Khám
                        phá <i>→</i></b></div></a><?php endforeach; ?>
        </div>
    </div>
</section>
