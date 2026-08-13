<?php
$admin     = require_admin();
$pageTitle = 'Quản trị Vi Vu Việt';
$tab       = (string) ($_GET['tab'] ?? 'overview');
$tabs      = ['overview', 'moderation', 'places', 'articles', 'comments', 'contacts', 'users'];
if (!in_array($tab, $tabs, true)) {
    $tab = 'overview';
}
$stats         = $pdo->query("SELECT (SELECT COUNT(*) FROM users WHERE role='customer') users,(SELECT COUNT(*) FROM places WHERE status='approved') places,(SELECT COUNT(*) FROM places WHERE status='pending') pending,(SELECT COUNT(*) FROM articles WHERE status='published') articles,(SELECT COUNT(*) FROM comments) comments")->fetch();
$pendingPlaces = [];
$places        = [];
$articles      = [];
$comments      = [];
$users         = [];
$contacts      = [];
$logs          = [];
if (in_array($tab, ['overview', 'moderation'], true)) {
    $pendingPlaces = $pdo->query("SELECT p.*,pr.name province_name,c.name category_name,u.name submitter_name,u.email submitter_email FROM places p JOIN provinces pr ON pr.id=p.province_id LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN users u ON u.id=p.submitted_by WHERE p.status IN ('pending','changes_requested') ORDER BY FIELD(p.status,'pending','changes_requested'),p.created_at")->fetchAll();
    $logs          = $pdo->query("SELECT ml.*,u.name admin_name,p.name place_name FROM moderation_logs ml JOIN users u ON u.id=ml.admin_id LEFT JOIN places p ON p.id=ml.place_id ORDER BY ml.created_at DESC LIMIT 8")->fetchAll();
}
if ($tab === 'places') {
    $places = $pdo->query("SELECT p.*,pr.name province_name,c.name category_name,u.name submitter_name FROM places p JOIN provinces pr ON pr.id=p.province_id LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN users u ON u.id=p.submitted_by ORDER BY p.created_at DESC")->fetchAll();
}
if ($tab === 'articles') {
    $articles = $pdo->query(article_metrics_sql() . " ORDER BY a.created_at DESC")->fetchAll();
}
if ($tab === 'comments') {
    $comments = $pdo->query("SELECT c.*,u.name user_name,u.email,a.title article_title,a.slug article_slug FROM comments c JOIN users u ON u.id=c.user_id JOIN articles a ON a.id=c.article_id ORDER BY c.created_at DESC")->fetchAll();
}
if ($tab === 'users') {
    $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM places p WHERE p.submitted_by=u.id) submissions,(SELECT COUNT(*) FROM comments c WHERE c.user_id=u.id) comments_count FROM users u ORDER BY u.created_at DESC")->fetchAll();
}
if ($tab === 'contacts') {
    $contacts = $pdo->query('SELECT cm.*, u.name AS account_name FROM contact_messages cm LEFT JOIN users u ON u.id=cm.user_id ORDER BY FIELD(cm.status,\'new\',\'read\',\'resolved\'), cm.created_at DESC')->fetchAll();
}
?>
<section class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-title"><span class="brand-mark"><svg viewBox="0 0 48 48"><path
                            d="M8 33c8-1 10-16 19-16 6 0 7 8 13 9-4 1-7 3-10 7H8Z"/><path
                            d="M13 18c4-8 11-11 19-8-8 2-12 7-14 13"/></svg></span>
            <div><strong>Vi Vu Việt</strong><small>ADMIN CONSOLE</small></div>
        </div>
        <nav><small>TỔNG QUAN</small><a class="<?= $tab === 'overview' ? 'active' : '' ?>"
                                        href="<?= url('admin') ?>"><span>⌂</span>Dashboard</a><small>NỘI DUNG</small><a
                    class="<?= $tab === 'moderation' ? 'active' : '' ?>"
                    href="<?= url('admin', ['tab' => 'moderation']) ?>"><span>✓</span>Chờ phê
                duyệt <?php if ($stats['pending']): ?><b><?= $stats['pending'] ?></b><?php endif; ?></a><a
                    class="<?= $tab === 'places' ? 'active' : '' ?>"
                    href="<?= url('admin', ['tab' => 'places']) ?>"><span>⌖</span>Địa danh</a><a
                    class="<?= $tab === 'articles' ? 'active' : '' ?>"
                    href="<?= url('admin', ['tab' => 'articles']) ?>"><span>▤</span>Bài viết</a><a
                    class="<?= $tab === 'comments' ? 'active' : '' ?>"
                    href="<?= url('admin', ['tab' => 'comments']) ?>"><span>◌</span>Bình luận</a><a
                    class="<?= $tab === 'contacts' ? 'active' : '' ?>"
                    href="<?= url('admin', ['tab' => 'contacts']) ?>"><span>✉</span>Liên hệ</a><small>HỆ THỐNG</small><a
                    class="<?= $tab === 'users' ? 'active' : '' ?>"
                    href="<?= url('admin', ['tab' => 'users']) ?>"><span>♙</span>Người dùng</a></nav>
        <a class="sidebar-home" href="<?= url() ?>">← Xem website</a></aside>
    <div class="admin-content">
        <header class="admin-topbar">
            <div>
                <button class="admin-menu-toggle" type="button">☰</button>
                <h1><?= [
                            'overview'   => 'Tổng quan',
                            'moderation' => 'Phê duyệt địa danh',
                            'places'     => 'Quản lý địa danh',
                            'articles'   => 'Quản lý bài viết',
                            'comments'   => 'Quản lý bình luận',
                            'contacts'   => 'Quản lý liên hệ',
                            'users'      => 'Quản lý người dùng'
                    ][$tab] ?></h1></div>
            <div class="admin-profile"><span class="avatar"><?= e(mb_strtoupper(mb_substr($admin['name'], 0,
                            1))) ?></span>
                <div><strong><?= e($admin['name']) ?></strong><small>Quản trị viên</small></div>
            </div>
        </header>
        <div class="admin-main">
            <?php if ($tab === 'overview'): ?>
                <div class="admin-welcome">
                    <div><p><?= date('d/m/Y') ?></p>
                        <h2>Chào <?= e($admin['name']) ?>,</h2>
                        <span>Đây là những gì đang diễn ra trên Vi Vu Việt.</span>
                    </div>
                    <a class="button" href="<?= url('admin-place-create') ?>">+ Thêm địa danh</a></div>
                <div class="admin-stats">
                    <div><span class="admin-stat-icon green">⌖</span>
                        <p><small>Địa danh công khai</small><strong><?= (int) $stats['places'] ?></strong></p></div>
                    <div><span class="admin-stat-icon amber">⌛</span>
                        <p><small>Đang chờ duyệt</small><strong><?= (int) $stats['pending'] ?></strong></p></div>
                    <div><span class="admin-stat-icon blue">▤</span>
                        <p><small>Bài viết</small><strong><?= (int) $stats['articles'] ?></strong></p></div>
                    <div><span class="admin-stat-icon rose">♙</span>
                        <p><small>Thành viên</small><strong><?= (int) $stats['users'] ?></strong></p></div>
                </div>
                <div class="admin-overview-grid">
                    <section class="admin-card">
                        <header>
                            <div><h2>Cần phê duyệt</h2>
                                <p>Các địa danh mới từ cộng đồng</p></div>
                            <a href="<?= url('admin', ['tab' => 'moderation']) ?>">Xem tất cả →</a>
                        </header><?php if ($pendingPlaces): ?>
                            <div class="approval-mini-list"><?php foreach (array_slice($pendingPlaces, 0,
                                    5) as $item): ?><a
                                href="<?= url('admin', ['tab' => 'moderation']) ?>#place-<?= $item['id'] ?>"><img
                                        src="<?= e(place_image($item['cover_image'])) ?>"
                                        alt=""><span><strong><?= e($item['name']) ?></strong><small><?= e($item['province_name']) ?> · bởi <?= e($item['submitter_name'] ?: 'Admin') ?></small></span><em><?= format_date($item['created_at']) ?></em>
                                </a><?php endforeach; ?></div><?php else: ?>
                            <div class="mini-empty">Tuyệt! Không có địa danh nào đang chờ.</div><?php endif; ?>
                    </section>
                    <section class="admin-card">
                        <header>
                            <div><h2>Hoạt động duyệt</h2>
                                <p>Các thao tác gần đây</p></div>
                        </header>
                        <div class="activity-list"><?php foreach ($logs as $log): ?>
                                <div><span>✓</span>
                                <p><strong><?= e($log['admin_name']) ?></strong> <?= e(status_label($log['action'])) ?>
                                    “<?= e($log['place_name'] ?: 'Địa điểm đã xóa') ?>
                                    ”<small><?= format_date($log['created_at']) ?></small></p>
                                </div><?php endforeach; ?><?php if (!$logs): ?>
                                <div class="mini-empty">Chưa có hoạt động duyệt.</div><?php endif; ?></div>
                    </section>
                </div>
            <?php elseif ($tab === 'moderation'): ?>
                <div class="admin-page-intro">
                <div><h2>Hàng đợi phê duyệt</h2>
                    <p>Kiểm tra kỹ thông tin, hình ảnh và tính hữu ích trước khi xuất bản.</p></div>
                <span class="queue-count"><?= count($pendingPlaces) ?> mục chờ xử lý</span>
                </div><?php if ($pendingPlaces): ?>
                    <div class="moderation-list"><?php foreach ($pendingPlaces as $item): ?>
                    <article class="moderation-card" id="place-<?= $item['id'] ?>">
                        <div class="moderation-image"><img src="<?= e(place_image($item['cover_image'])) ?>"
                                                           alt="<?= e($item['name']) ?>"><span><?= e($item['category_name'] ?: 'Chưa phân loại') ?></span>
                        </div>
                        <div class="moderation-copy">
                            <header>
                                <div><h3><?= e($item['name']) ?></h3>
                                    <p>⌖ <?= e($item['address']) ?></p></div>
                                <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                            </header>
                            <p class="moderation-excerpt"><?= e($item['short_description']) ?></p>
                            <details>
                                <summary>Xem nội dung đầy đủ</summary>
                                <div class="rich-text"><?php foreach (preg_split('/\R\R+/',
                                            trim($item['description'])) as $paragraph): ?>
                                        <p><?= e($paragraph) ?></p><?php endforeach; ?></div>
                            </details>
                            <div class="submitter"><span
                                        class="avatar"><?= e(mb_strtoupper(mb_substr($item['submitter_name'] ?: 'A', 0,
                                            1))) ?></span>
                                <div><small>Người đóng
                                        góp</small><strong><?= e($item['submitter_name'] ?: 'Quản trị viên') ?></strong><em><?= e($item['submitter_email'] ?: '') ?>
                                        · <?= format_date($item['created_at']) ?></em></div>
                            </div>
                            <form class="moderation-form" method="post"
                                  action="<?= url('admin', ['tab' => 'moderation']) ?>"><?= csrf_field() ?><input
                                        type="hidden" name="action" value="moderate_place"><input type="hidden"
                                                                                                  name="place_id"
                                                                                                  value="<?= $item['id'] ?>"><input
                                        type="hidden" name="return_to"
                                        value="<?= e(url('admin', ['tab' => 'moderation'])) ?>"><label>Ghi chú cho người
                                    đóng góp<textarea name="admin_note" rows="2"
                                                      placeholder="Bắt buộc khi yêu cầu sửa hoặc từ chối…"></textarea></label>
                                <div>
                                    <button class="button button-success" name="status" value="approved" type="submit">✓
                                        Phê duyệt
                                    </button>
                                    <button class="button button-warning" name="status" value="changes_requested"
                                            type="submit">Yêu cầu sửa
                                    </button>
                                    <button class="button button-danger" name="status" value="rejected" type="submit">Từ
                                        chối
                                    </button>
                                </div>
                            </form>
                        </div></article><?php endforeach; ?></div><?php else: ?>
                    <div class="empty-state"><span>✓</span>
                        <h2>Hàng đợi đã trống</h2>
                        <p>Tất cả địa danh đã được xử lý.</p></div><?php endif; ?>
            <?php elseif ($tab === 'places'): ?>
                <div class="admin-page-intro">
                    <div><h2>Tất cả địa danh</h2>
                        <p>Quản lý trạng thái và nội dung nổi bật.</p></div>
                    <a class="button" href="<?= url('admin-place-create') ?>">+ Thêm địa danh</a></div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Địa danh</th>
                            <th>Tỉnh/thành</th>
                            <th>Người thêm</th>
                            <th>Trạng thái</th>
                            <th>Nổi bật</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody><?php foreach ($places as $item): ?>
                            <tr>
                            <td>
                                <div class="table-primary"><img src="<?= e(place_image($item['cover_image'])) ?>"
                                                                alt=""><span><strong><?= e($item['name']) ?></strong><small><?= e($item['category_name'] ?: 'Chưa phân loại') ?></small></span>
                                </div>
                            </td>
                            <td><?= e($item['province_name']) ?></td>
                            <td><?= e($item['submitter_name'] ?: 'Hệ thống') ?></td>
                            <td>
                                <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                            </td>
                            <td>
                                <form method="post"
                                      action="<?= url('admin', ['tab' => 'places']) ?>"><?= csrf_field() ?><input
                                            type="hidden" name="action" value="admin_toggle_feature"><input
                                            type="hidden" name="type" value="places"><input type="hidden" name="id"
                                                                                            value="<?= $item['id'] ?>"><input
                                            type="hidden" name="return_to"
                                            value="<?= e(url('admin', ['tab' => 'places'])) ?>"><label
                                            class="switch"><input type="checkbox" name="featured"
                                                                  value="1" <?= $item['is_featured'] ? 'checked' : '' ?>
                                                                  onchange="this.form.submit()"><span></span></label>
                                </form>
                            </td>
                            <td><?php if ($item['status'] === 'approved'): ?><a class="table-link"href="<?= url('place',
                                        ['slug' => $item['slug']]) ?>">Xem ↗</a><?php else: ?><a class="table-link"
                                                                                                 href="<?= url('admin',
                                                                                                         ['tab' => 'moderation']) ?>#place-<?= $item['id'] ?>">
                                        Duyệt</a><?php endif; ?></td></tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            <?php elseif ($tab === 'articles'): ?>
                <div class="admin-page-intro">
                    <div><h2>Tất cả bài viết</h2>
                        <p>Xuất bản, ẩn và lựa chọn nội dung nổi bật.</p></div>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Bài viết</th>
                            <th>Địa điểm</th>
                            <th>Tương tác</th>
                            <th>Trạng thái</th>
                            <th>Nổi bật</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody><?php foreach ($articles as $item): ?>
                            <tr>
                            <td>
                                <div class="table-primary"><img src="<?= e(place_image($item['cover_image'])) ?>"
                                                                alt=""><span><strong><?= e($item['title']) ?></strong><small><?= format_date($item['created_at']) ?></small></span>
                                </div>
                            </td>
                            <td><?= e($item['place_name']) ?><small
                                        class="table-sub"><?= e($item['province_name']) ?></small></td>
                            <td>♥ <?= (int) $item['likes_count'] ?> · ★ <?= $item['rating_avg'] ?></td>
                            <td>
                                <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                            </td>
                            <td>
                                <form method="post"
                                      action="<?= url('admin', ['tab' => 'articles']) ?>"><?= csrf_field() ?><input
                                            type="hidden" name="action" value="admin_toggle_feature"><input
                                            type="hidden" name="type" value="articles"><input type="hidden" name="id"
                                                                                              value="<?= $item['id'] ?>"><input
                                            type="hidden" name="return_to"
                                            value="<?= e(url('admin', ['tab' => 'articles'])) ?>"><label class="switch"><input
                                                type="checkbox" name="featured"
                                                value="1" <?= $item['is_featured'] ? 'checked' : '' ?>
                                                onchange="this.form.submit()"><span></span></label></form>
                            </td>
                            <td>
                                <form method="post"
                                      action="<?= url('admin', ['tab' => 'articles']) ?>"><?= csrf_field() ?><input
                                            type="hidden" name="action" value="admin_article_status"><input
                                            type="hidden" name="article_id" value="<?= $item['id'] ?>"><input
                                            type="hidden" name="return_to" value="<?= e(url('admin',
                                            ['tab' => 'articles'])) ?>"><?php if ($item['status'] === 'published'): ?>
                                        <button class="table-link danger" name="status" value="hidden">Ẩn
                                        </button><?php else: ?>
                                        <button class="table-link" name="status" value="published">Xuất bản
                                        </button><?php endif; ?></form>
                            </td></tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            <?php elseif ($tab === 'comments'): ?>
                <div class="admin-page-intro">
                    <div><h2>Bình luận cộng đồng</h2>
                        <p>Ẩn nội dung không phù hợp hoặc khôi phục khi cần.</p></div>
                    <span class="queue-count"><?= count($comments) ?> bình luận</span></div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Thành viên</th>
                            <th>Nội dung</th>
                            <th>Bài viết</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody><?php foreach ($comments as $item): ?>
                            <tr>
                            <td><strong><?= e($item['user_name']) ?></strong><small
                                        class="table-sub"><?= e($item['email']) ?></small></td>
                            <td class="comment-cell"><?= e(excerpt($item['content'], 110)) ?><small
                                        class="table-sub"><?= format_date($item['created_at']) ?></small></td>
                            <td><a href="<?= url('article',
                                        ['slug' => $item['article_slug']]) ?>"><?= e(excerpt($item['article_title'],
                                            45)) ?></a></td>
                            <td>
                                <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                            </td>
                            <td>
                                <form method="post"
                                      action="<?= url('admin', ['tab' => 'comments']) ?>"><?= csrf_field() ?><input
                                            type="hidden" name="action" value="admin_toggle_comment"><input
                                            type="hidden" name="comment_id" value="<?= $item['id'] ?>"><input
                                            type="hidden" name="return_to"
                                            value="<?= e(url('admin', ['tab' => 'comments'])) ?>">
                                    <button class="table-link <?= $item['status'] === 'visible' ? 'danger' : '' ?>"
                                            name="status"
                                            value="<?= $item['status'] === 'visible' ? 'hidden' : 'visible' ?>"><?= $item['status'] === 'visible' ? 'Ẩn' : 'Hiện' ?></button>
                                </form>
                            </td></tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            <?php elseif ($tab === 'contacts'): ?>
                <div class="admin-page-intro">
                    <div><h2>Liên hệ từ website</h2>
                        <p>Theo dõi và xử lý các yêu cầu gửi từ khách truy cập và thành viên.</p></div>
                    <span class="queue-count"><?= count($contacts) ?> liên hệ</span></div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Người gửi</th>
                            <th>Chủ đề & nội dung</th>
                            <th>Thông tin</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody><?php foreach ($contacts as $item): ?>
                            <tr>
                            <td><strong><?= e($item['name']) ?></strong><small
                                        class="table-sub"><?= e($item['account_name'] ?: 'Khách vãng lai') ?></small>
                            </td>
                            <td class="comment-cell"><strong><?= e($item['subject']) ?></strong><small
                                        class="table-sub"><?= e(excerpt($item['message'], 130)) ?></small></td>
                            <td><?= e($item['email']) ?><small class="table-sub"><?= e($item['phone'] ?: '—') ?></small>
                            </td>
                            <td><?= format_date($item['created_at']) ?></td>
                            <td>
                                <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                            </td>
                            <td>
                                <form method="post"
                                      action="<?= url('admin', ['tab' => 'contacts']) ?>"><?= csrf_field() ?><input
                                            type="hidden" name="action" value="admin_contact_status"><input
                                            type="hidden" name="message_id" value="<?= $item['id'] ?>"><input
                                            type="hidden" name="return_to"
                                            value="<?= e(url('admin', ['tab' => 'contacts'])) ?>"><select
                                            class="admin-status-select" name="status" onchange="this.form.submit()">
                                        <option value="new" <?= $item['status'] === 'new' ? 'selected' : '' ?>>Mới
                                        </option>
                                        <option value="read" <?= $item['status'] === 'read' ? 'selected' : '' ?>>Đã
                                            đọc
                                        </option>
                                        <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>
                                            Đã xử lý
                                        </option>
                                    </select></form>
                            </td></tr><?php endforeach; ?><?php if (!$contacts): ?>
                            <tr>
                                <td colspan="6" class="table-empty">Chưa có liên hệ nào.</td>
                            </tr><?php endif; ?></tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="admin-page-intro">
                    <div><h2>Người dùng</h2>
                        <p>Quản lý quyền truy cập của thành viên.</p></div>
                    <span class="queue-count"><?= count($users) ?> tài khoản</span></div>
                <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Vai trò</th>
                        <th>Đóng góp</th>
                        <th>Ngày tham gia</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody><?php foreach ($users as $item): ?>
                        <tr>
                        <td>
                            <div class="table-user"><span class="avatar"><?= e(mb_strtoupper(mb_substr($item['name'], 0,
                                            1))) ?></span><span><strong><?= e($item['name']) ?></strong><small><?= e($item['email']) ?></small></span>
                            </div>
                        </td>
                        <td><?= $item['role'] === 'admin' ? 'Quản trị viên' : 'Customer' ?></td>
                        <td><?= (int) $item['submissions'] ?> địa danh · <?= (int) $item['comments_count'] ?> bình
                            luận
                        </td>
                        <td><?= format_date($item['created_at']) ?></td>
                        <td>
                            <em class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></em>
                        </td>
                        <td><?php if ($item['role'] === 'customer'): ?>
                                <form method="post" action="<?= url('admin', ['tab' => 'users']) ?>"><?= csrf_field() ?>
                                <input type="hidden" name="action" value="admin_toggle_user"><input type="hidden"
                                                                                                    name="user_id"
                                                                                                    value="<?= $item['id'] ?>">
                                <input type="hidden" name="return_to"
                                       value="<?= e(url('admin', ['tab' => 'users'])) ?>">
                                <button class="table-link <?= $item['status'] === 'active' ? 'danger' : '' ?>"
                                        name="status"
                                        value="<?= $item['status'] === 'active' ? 'blocked' : 'active' ?>"><?= $item['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?></button>
                                </form><?php endif; ?></td></tr><?php endforeach; ?></tbody>
                </table></div><?php endif; ?>
        </div>
    </div>
</section>
