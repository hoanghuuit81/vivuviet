<?php
$admin      = require_admin();
$pageTitle  = 'Thêm địa danh — Quản trị Vi Vu Việt';
$provinces  = $pdo->query('SELECT pr.*, r.name region_name FROM provinces pr JOIN regions r ON r.id=pr.region_id ORDER BY r.id, pr.name')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<section class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-title"><span class="brand-mark">✦</span>
            <div><strong>Vi Vu Việt</strong><small>ADMIN CONSOLE</small></div>
        </div>
        <nav><small>NỘI DUNG</small><a href="<?= admin_url('dashboard') ?>"><span>⌂</span>Dashboard</a><a class="active"
                                                                                                          href="<?= admin_url('places/new') ?>"><span>＋</span>Thêm
                địa danh</a><a href="<?= admin_url('dashboard', ['tab' => 'places']) ?>"><span>⌖</span>Địa danh</a>
        </nav>
        <a class="sidebar-home" href="<?= admin_url('dashboard') ?>">← Về dashboard</a></aside>
    <div class="admin-content">
        <header class="admin-topbar">
            <div><h1>Thêm địa danh mới</h1></div>
            <div class="admin-profile"><?= avatar_markup($admin) ?>
                <div><strong><?= e($admin['name']) ?></strong><small>Quản trị viên</small></div>
            </div>
        </header>
        <div class="admin-main">
            <div class="admin-page-intro">
                <div><h2>Xuất bản trực tiếp</h2>
                    <p>Địa danh được tạo tại đây sẽ hiển thị công khai ngay sau khi lưu.</p></div>
            </div>
            <form class="place-form admin-place-form" method="post" enctype="multipart/form-data"
                  action="<?= admin_url('places/new') ?>"><?= csrf_field() ?><input type="hidden" name="action"
                                                                                    value="admin_submit_place"><input
                        type="hidden" name="return_to" value="<?= e(admin_url('places/new')) ?>">
                <div class="form-card">
                    <div class="form-card-title"><span>01</span>
                        <div><h2>Thông tin địa danh</h2>
                            <p>Thông tin này sẽ hiển thị trên trang chi tiết.</p></div>
                    </div>
                    <div class="form-grid"><label class="span-2">Tên địa điểm <b>*</b><input name="name" minlength="3"
                                                                                             maxlength="180"
                                                                                             placeholder="Ví dụ: Khu bảo tồn thiên nhiên…"
                                                                                             required></label><label>Tỉnh/thành
                            phố <b>*</b><select name="province_id" required>
                                <option value="">Chọn tỉnh/thành</option>
                                <?php $lastRegion = '';
                                foreach ($provinces

                                as $province): ?><?php if ($lastRegion !== $province['region_name']): ?><?php if ($lastRegion !== ''): ?></optgroup><?php endif;
                                $lastRegion = $province['region_name']; ?>
                                <optgroup label="<?= e($lastRegion) ?>"><?php endif; ?>
                                    <option value="<?= $province['id'] ?>"><?= e($province['name']) ?></option><?php endforeach; ?>
                                </optgroup>
                            </select></label><label>Loại hình <b>*</b><select name="category_id" required>
                                <option value="">Chọn loại hình</option><?php foreach ($categories as $category): ?>
                                    <option
                                    value="<?= $category['id'] ?>"><?= e($category['name']) ?></option><?php endforeach; ?>
                            </select></label><label class="span-2">Địa chỉ <b>*</b><input name="address" minlength="5"
                                                                                          maxlength="255"
                                                                                          placeholder="Xã/phường, quận/huyện…"
                                                                                          required></label><label>Giờ
                            hoạt động<input name="opening_hours" maxlength="150"
                                            placeholder="Ví dụ: 07:00 – 17:30"></label><label>Chi phí tham khảo<input
                                    name="price_info" maxlength="150" placeholder="Ví dụ: 100.000đ/người"></label></div>
                </div>
                <div class="form-card">
                    <div class="form-card-title"><span>02</span>
                        <div><h2>Nội dung bài giới thiệu</h2>
                            <p>Sử dụng trình soạn thảo để định dạng bài viết.</p></div>
                    </div>
                    <div class="form-grid"><label class="span-2">Mô tả ngắn <b>*</b><textarea name="short_description"
                                                                                              rows="3" minlength="20"
                                                                                              maxlength="500"
                                                                                              required></textarea></label><label
                                class="span-2">Bài giới thiệu chi tiết <b>*</b><textarea class="rich-editor"
                                                                                         name="description" rows="10"
                                                                                         minlength="80"
                                                                                         required></textarea></label>
                    </div>
                </div>
                <div class="form-card">
                    <div class="form-card-title"><span>03</span>
                        <div><h2>Ảnh đại diện</h2>
                            <p>Tải ảnh từ thiết bị hoặc cung cấp URL.</p></div>
                    </div>
                    <label class="upload-zone"><input name="cover_image" type="file"
                                                      accept="image/jpeg,image/png,image/webp"><span>⇧</span><strong>Chọn
                            ảnh từ thiết bị</strong><small>JPG, PNG hoặc WebP · Tối đa 5MB</small><img
                                class="upload-preview" alt="Xem trước ảnh"></label>
                    <div class="or-divider"><span>hoặc dùng đường dẫn ảnh</span></div>
                    <label>URL ảnh<input name="image_url" type="url" placeholder="https://example.com/anh-dia-diem.jpg"></label>
                </div>
                <div class="form-submit"><p>Nội dung sẽ được xuất bản ngay với trạng thái công khai.</p>
                    <button class="button button-large" type="submit">Xuất bản địa danh <span>→</span></button>
                </div>
            </form>
        </div>
    </div>
</section>
