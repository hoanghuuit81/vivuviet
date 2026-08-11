<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/actions.php';

$page = (string) ($_GET['page'] ?? 'home');
$allowedPages = [
    'home', 'region', 'province', 'place', 'articles', 'article', 'search',
    'login', 'register', 'profile', 'submit-place', 'admin', 'admin-login', 'admin-place-create', 'about', 'contact',
];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $page = '404';
}

$currentUser = current_user();
$pageTitle = 'Vi Vu Việt — Khám phá Việt Nam theo cách của bạn';
$metaDescription = 'Cộng đồng chia sẻ địa danh, kinh nghiệm và cảm hứng du lịch khắp Việt Nam.';

ob_start();
require __DIR__ . '/pages/' . $page . '.php';
$content = ob_get_clean();

require __DIR__ . '/templates/header.php';
echo $content;
require __DIR__ . '/templates/footer.php';
