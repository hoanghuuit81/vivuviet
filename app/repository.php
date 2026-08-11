<?php

declare(strict_types=1);

function article_metrics_sql(): string
{
    return "SELECT a.*, p.name AS place_name, p.slug AS place_slug, p.address,
        pr.name AS province_name, pr.slug AS province_slug, r.name AS region_name,
        u.name AS author_name,
        (SELECT COUNT(*) FROM article_likes al WHERE al.article_id = a.id) AS likes_count,
        (SELECT COUNT(*) FROM comments c WHERE c.article_id = a.id AND c.status = 'visible') AS comments_count,
        COALESCE((SELECT ROUND(AVG(rt.score),1) FROM ratings rt WHERE rt.article_id = a.id), 0) AS rating_avg,
        (SELECT COUNT(*) FROM ratings rt2 WHERE rt2.article_id = a.id) AS ratings_count
        FROM articles a
        JOIN places p ON p.id = a.place_id
        JOIN provinces pr ON pr.id = p.province_id
        JOIN regions r ON r.id = pr.region_id
        LEFT JOIN users u ON u.id = a.author_id";
}

function featured_places(PDO $pdo, int $limit = 6): array
{
    $limit = max(1, min(12, $limit));
    return $pdo->query("SELECT p.*, pr.name AS province_name, pr.slug AS province_slug,
        r.name AS region_name, c.name AS category_name,
        COALESCE((SELECT ROUND(AVG(rt.score),1) FROM ratings rt JOIN articles ar ON ar.id=rt.article_id WHERE ar.place_id=p.id),0) AS rating_avg
        FROM places p JOIN provinces pr ON pr.id=p.province_id JOIN regions r ON r.id=pr.region_id
        LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='approved'
        ORDER BY p.is_featured DESC, rating_avg DESC, p.created_at DESC LIMIT {$limit}")->fetchAll();
}

function latest_articles(PDO $pdo, int $limit = 6): array
{
    $limit = max(1, min(12, $limit));
    return $pdo->query(article_metrics_sql() . " WHERE a.status='published' AND p.status='approved'
        ORDER BY a.is_featured DESC, a.published_at DESC LIMIT {$limit}")->fetchAll();
}
