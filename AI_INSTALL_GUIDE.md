# Huong dan day len GitHub va cai dat lai project Vi Vu Viet

File nay duoc viet de ban hoac AI khac co the doc va dung lai project tren may moi.

## 1. Thong tin project

- Thu muc chay tren may hien tai: `/var/www/html/miniproject`
- URL mong muon: `http://localhost.com/miniproject`
- Cong admin: `http://localhost.com/miniproject/admin`
- Tech stack: PHP thuan, MySQL/MariaDB, HTML, CSS, JavaScript
- Khong dung Composer, Node build step, framework PHP hay bundler frontend.
- CKEditor 5 da nam san trong: `assets/ckeditor5-48.4.0`

Tai khoan mau:

- Customer: `user@vivuviet.vn` / `User@123`
- Admin: `admin@vivuviet.vn` / `Admin@123`

## 2. Cac file database trong project

- `database/schema.sql`: tao cau truc bang.
- `database/seed.sql`: du lieu mau ban dau.
- `database/miniproject_dump.sql`: ban dump day du cua database tai thoi diem xuat file.

Neu muon dung dung trang thai hien tai cua may nay, import `database/miniproject_dump.sql`.
Neu muon tao database sach voi du lieu mau, import `schema.sql` roi `seed.sql`.

## 3. Cach day project len GitHub tu may hien tai

Chay trong thu muc project:

```bash
cd /var/www/html/miniproject
git init
git add .
git status
git commit -m "Initial Vi Vu Viet project"
git branch -M main
git remote add origin https://github.com/<github-user>/<repo-name>.git
git push -u origin main
```

Thay `<github-user>` va `<repo-name>` bang tai khoan va repository GitHub cua ban.

Neu repository da co san remote, chi can:

```bash
git remote set-url origin https://github.com/<github-user>/<repo-name>.git
git push -u origin main
```

Khuyen nghi khong dua anh upload cua nguoi dung len GitHub. File `.gitignore` da bo qua `uploads/avatars/*` va `uploads/places/*`, chi giu thu muc rong bang `.gitkeep`.

## 4. Cai dat tren may moi

### 4.1. Cai goi can thiet tren Ubuntu/Debian

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql php-mbstring php-xml php-gd php-curl unzip git
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 4.2. Clone code ve dung duong dan

```bash
cd /var/www/html
sudo git clone https://github.com/<github-user>/<repo-name>.git miniproject
sudo chown -R $USER:www-data /var/www/html/miniproject
```

Neu da clone o noi khac, co the copy project vao `/var/www/html/miniproject`.

### 4.3. Cau hinh domain local

Them dong nay vao file `/etc/hosts`:

```text
127.0.0.1 localhost.com
```

Lenh nhanh:

```bash
echo "127.0.0.1 localhost.com" | sudo tee -a /etc/hosts
```

### 4.4. Tao database va user MySQL

Dang nhap MySQL:

```bash
sudo mysql
```

Chay SQL:

```sql
CREATE DATABASE IF NOT EXISTS miniproject CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'miniproject_app'@'localhost' IDENTIFIED BY 'Miniproject_Local_2026!';
CREATE USER IF NOT EXISTS 'miniproject_app'@'127.0.0.1' IDENTIFIED BY 'Miniproject_Local_2026!';
GRANT ALL PRIVILEGES ON miniproject.* TO 'miniproject_app'@'localhost';
GRANT ALL PRIVILEGES ON miniproject.* TO 'miniproject_app'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

Cau hinh mac dinh dang nam trong `config/app.php`. Neu muon doi ten database/user/password, sua bien moi truong hoac sua file config cho khop.

### 4.5. Import database

Dung ban dump day du:

```bash
cd /var/www/html/miniproject
mysql -h127.0.0.1 -uminiproject_app -p miniproject < database/miniproject_dump.sql
```

Hoac dung database mau:

```bash
cd /var/www/html/miniproject
mysql -h127.0.0.1 -uminiproject_app -p miniproject < database/schema.sql
mysql -h127.0.0.1 -uminiproject_app -p miniproject < database/seed.sql
```

Mat khau mac dinh khi duoc hoi: `Miniproject_Local_2026!`

### 4.6. Cap quyen thu muc upload

```bash
cd /var/www/html/miniproject
mkdir -p uploads/places uploads/avatars
sudo chown -R $USER:www-data uploads
sudo chmod -R 775 uploads
```

### 4.7. Dam bao Apache cho phep `.htaccess`

Mo file virtual host mac dinh:

```bash
sudo nano /etc/apache2/apache2.conf
```

Tim khoi `/var/www/` va dat:

```apache
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Sau do restart Apache:

```bash
sudo systemctl restart apache2
```

## 5. Kiem thu sau khi cai

Mo trinh duyet:

- `http://localhost.com/miniproject`
- `http://localhost.com/miniproject/admin`

Kiem tra nhanh bang terminal:

```bash
curl -I http://localhost.com/miniproject
curl -I http://localhost.com/miniproject/admin
php -l index.php
find app pages templates config -name "*.php" -print0 | xargs -0 -n1 php -l
```

## 6. Ghi chu ve gui email lien he

Trang lien he dang dung ham `mail()` cua PHP va gui toi `taisaokhong81@gmail.com`.
May moi can co `sendmail`, Postfix hoac SMTP relay duoc cau hinh thi email moi that su duoc gui di.
Du khong gui duoc email, noi dung lien he van duoc luu trong bang `contact_messages` va co the xem trong admin.

## 7. Luu y bao mat truoc khi public repository

- Doi mat khau database neu deploy len may cong khai.
- Doi mat khau tai khoan mau admin/customer.
- Khong commit file `.env`, log, anh upload cua nguoi dung hoac thong tin rieng tu.
- Neu repository public, can xem lai `database/miniproject_dump.sql` de chac chan khong co du lieu ca nhan that.
