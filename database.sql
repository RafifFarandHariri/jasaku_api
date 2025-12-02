SQL schema for Jasaku app
Create database (use phpMyAdmin or mysql cli):

CREATE DATABASE jasaku_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Then import this file into the database.
USE DATABASE jasaku_db;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nrp` VARCHAR(64) DEFAULT NULL,
  `nama` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(32) DEFAULT NULL,
  `profile_image` TEXT DEFAULT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'customer',
  `is_verified_provider` TINYINT(1) NOT NULL DEFAULT 0,
  `provider_since` DATETIME DEFAULT NULL,
  `provider_description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `seller` VARCHAR(191) NOT NULL,
  `price` INT NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `sold` INT NOT NULL DEFAULT 0,
  `rating` DOUBLE NOT NULL DEFAULT 0,
  `reviews` INT NOT NULL DEFAULT 0,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `has_fast_response` TINYINT(1) NOT NULL DEFAULT 1,
  `category` VARCHAR(128) DEFAULT NULL,
  `serviceType` VARCHAR(128) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `orders` (
  `id` VARCHAR(64) PRIMARY KEY,
  `serviceId` INT DEFAULT NULL,
  `serviceTitle` VARCHAR(255) DEFAULT NULL,
  `sellerId` VARCHAR(64) DEFAULT NULL,
  `sellerName` VARCHAR(191) DEFAULT NULL,
  `customerId` VARCHAR(64) DEFAULT NULL,
  `customerName` VARCHAR(191) DEFAULT NULL,
  `price` DOUBLE DEFAULT 0,
  `quantity` INT DEFAULT 1,
  `notes` TEXT DEFAULT NULL,
  `status` INT DEFAULT 0,
  `orderDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `deadline` DATETIME DEFAULT NULL,
  `completedDate` DATETIME DEFAULT NULL,
  `paymentMethod` VARCHAR(64) DEFAULT NULL,
  `isPaid` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `order_progress` (
  `id` VARCHAR(64) PRIMARY KEY,
  `orderId` VARCHAR(64) NOT NULL,
  `percentage` INT NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `imageUrl` TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `payments` (
  `id` VARCHAR(64) PRIMARY KEY,
  `orderId` VARCHAR(64) DEFAULT NULL,
  `amount` DOUBLE DEFAULT 0,
  `paymentMethod` VARCHAR(64) DEFAULT NULL,
  `status` INT DEFAULT 0,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `paidAt` DATETIME DEFAULT NULL,
  `qrCodeUrl` TEXT DEFAULT NULL,
  `paymentReference` VARCHAR(191) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `chats` (
  `id` VARCHAR(64) PRIMARY KEY,
  `conversationId` VARCHAR(64) DEFAULT NULL,
  `text` TEXT DEFAULT NULL,
  `isMe` TINYINT(1) DEFAULT 0,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `type` INT DEFAULT 0,
  `senderName` VARCHAR(191) DEFAULT NULL,
  `serviceId` INT DEFAULT NULL,
  `proposedPrice` DOUBLE DEFAULT NULL,
  `offerId` VARCHAR(64) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `price_offers` (
  `id` VARCHAR(64) PRIMARY KEY,
  `serviceId` INT DEFAULT NULL,
  `originalPrice` DOUBLE DEFAULT 0,
  `proposedPrice` DOUBLE DEFAULT 0,
  `message` TEXT DEFAULT NULL,
  `status` INT DEFAULT 0,
  `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `respondedAt` DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `userId` VARCHAR(64) DEFAULT NULL,
  `serviceId` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Portfolios: optional work samples for a service/seller
CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` VARCHAR(64) PRIMARY KEY,
  `serviceId` VARCHAR(64) DEFAULT NULL,
  `sellerId` VARCHAR(64) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `imageUrl` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- Reviews table: stores user ratings and comments for services
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` VARCHAR(64) PRIMARY KEY,
  `serviceId` INT DEFAULT NULL,
  `userId` VARCHAR(64) DEFAULT NULL,
  `userName` VARCHAR(191) DEFAULT NULL,
  `rating` DOUBLE NOT NULL DEFAULT 0,
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE `services`
  ADD COLUMN `description` TEXT DEFAULT NULL AFTER `price`;

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `seller` VARCHAR(191) NOT NULL,
  `price` INT NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `sold` INT NOT NULL DEFAULT 0,
  `rating` DOUBLE NOT NULL DEFAULT 0,
  `reviews` INT NOT NULL DEFAULT 0,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `has_fast_response` TINYINT(1) NOT NULL DEFAULT 1,
  `category` VARCHAR(128) DEFAULT NULL,
  `serviceType` VARCHAR(128) DEFAULT NULL,
  `imageUrl` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `services`
  ADD COLUMN `imageUrl` TEXT DEFAULT NULL AFTER `serviceType`;

SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'jasaku_db' AND TABLE_NAME = 'services';
INSERT INTO `services`
  (`title`, `seller`, `price`, `description`, `sold`, `rating`, `reviews`, `is_verified`, `has_fast_response`, `category`, `serviceType`, `imageUrl`)
VALUES
  ('Design Logo Murah & Terbaik - Bebas Revisi', 'Fadhil Jofan Syahputra', 50000,
   'Jasa desain logo profesional. Revisi tak terbatas, file vektor disediakan.', 300, 4.9, 266, 1, 1, 'Desain Grafis', 'Desain Grafis', NULL),
  ('Design Website HTML CSS JS PHP', 'Hanan Rasyad Sadad', 500000,
   'Pembuatan website statis/dinamis menggunakan HTML/CSS/JS dan PHP. Responsive & SEO friendly.', 800, 4.9, 760, 1, 1, 'Web Development', 'Pemrograman', NULL),
  ('Logo Minimalis UMKM, FASHION, TOKO ONLINE', 'Alman Wicaksono', 75000,
   'Logo minimalis untuk UMKM dan toko online — cepat & hasil modern.', 250, 4.1, 150, 1, 1, 'Desain Grafis', 'Desain Grafis', NULL),
  ('Professional Motion Graphics Editing', 'Tirta Affandi', 300000,
   'Jasa motion graphics untuk promo, iklan, dan opening video.', 321, 4.1, 150, 1, 1, 'Video & Motion', '3D Modeling', NULL);

USE jasaku_db;

-- Hapus entri dummy lama (opsional)
DELETE FROM services
WHERE seller IN ('Fadhil Jofan Syahputra','Hanan Rasyad Sadad','Alman Wicaksono','Tirta Affandi');

-- Tambahkan sample services
INSERT INTO services
  (title, seller, price, description, sold, rating, reviews, is_verified, has_fast_response, category, serviceType, imageUrl)
VALUES
  ('Design Logo Murah & Terbaik - Bebas Revisi', 'Fadhil Jofan Syahputra', 50000,
   'Jasa desain logo profesional. Revisi tak terbatas, file vektor disediakan.', 300, 4.9, 266, 1, 1, 'Desain Grafis', 'Desain Grafis', NULL),
  ('Design Website HTML CSS JS PHP', 'Hanan Rasyad Sadad', 500000,
   'Pembuatan website statis/dinamis menggunakan HTML/CSS/JS dan PHP. Responsive & SEO friendly.', 800, 4.9, 760, 1, 1, 'Web Development', 'Pemrograman', NULL),
  ('Logo Minimalis UMKM, FASHION, TOKO ONLINE', 'Alman Wicaksono', 75000,
   'Logo minimalis untuk UMKM dan toko online — cepat & hasil modern.', 250, 4.1, 150, 1, 1, 'Desain Grafis', 'Desain Grafis', NULL),
  ('Professional Motion Graphics Editing', 'Tirta Affandi', 300000,
   'Jasa motion graphics untuk promo, iklan, dan opening video.', 321, 4.1, 150, 1, 1, 'Video & Motion', '3D Modeling', NULL);

-- Jika tabel reviews belum ada, buat dulu (jika sudah ada, abaikan)
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` VARCHAR(64) PRIMARY KEY,
  `serviceId` INT DEFAULT NULL,
  `userId` VARCHAR(64) DEFAULT NULL,
  `userName` VARCHAR(191) DEFAULT NULL,
  `rating` DOUBLE NOT NULL DEFAULT 0,
  `comment` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Tambahkan beberapa review sample (ganti serviceId sesuai id yang ada setelah INSERT)
-- Cara cepat: ambil id service yang baru, lalu jalankan INSERT review dengan serviceId = id
-- Contoh (asumsikan service IDs 1..4 — sesuaikan dengan database Anda):
INSERT INTO reviews (id, serviceId, userId, userName, rating, comment)
VALUES
  (CONCAT('r', LEFT(UUID(),8)), 1, 'u1', 'Pandji Prakoso', 5, 'Hasilnya memuaskan! Desain logo sesuai dengan yang saya inginkan.'),
  (CONCAT('r', LEFT(UUID(),8)), 1, 'u2', 'Siti Rahma', 5, 'Pelayanan sangat profesional. Hasil kerja rapi.'),
  (CONCAT('r', LEFT(UUID(),8)), 2, 'u3', 'Andi', 4.5, 'Website bagus, responsive.'),
  (CONCAT('r', LEFT(UUID(),8)), 3, 'u4', 'Maya', 4, 'Logo sesuai permintaan, proses cepat.');

-- (Opsional) Rekalkulasi agregat rating/reviews untuk semua services setelah insert review:
UPDATE services s
SET s.rating = COALESCE((
    SELECT AVG(r.rating) FROM reviews r WHERE r.serviceId = s.id
), 0),
s.reviews = COALESCE((
    SELECT COUNT(*) FROM reviews r WHERE r.serviceId = s.id
), 0);

USE jasaku_db;

-- add columns if they don't exist (safe to run)
ALTER TABLE services ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS imageUrl TEXT DEFAULT NULL;
ALTER TABLE services ADD COLUMN IF NOT EXISTS serviceType VARCHAR(128) DEFAULT NULL;

-- create reviews table
CREATE TABLE IF NOT EXISTS reviews (
  id VARCHAR(64) PRIMARY KEY,
  serviceId INT DEFAULT NULL,
  userId VARCHAR(64) DEFAULT NULL,
  userName VARCHAR(191) DEFAULT NULL,
  rating DOUBLE NOT NULL DEFAULT 0,
  comment TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- remove old demo entries (optional)
DELETE FROM services
WHERE seller IN ('Fadhil Jofan Syahputra','Hanan Rasyad Sadad','Alman Wicaksono','Tirta Affandi');

-- insert sample services (4 banners)
INSERT INTO services
  (title, seller, price, description, sold, rating, reviews, is_verified, has_fast_response, category, serviceType, imageUrl)
VALUES
  ('Design Logo Murah & Terbaik - Bebas Revisi', 'Fadhil Jofan Syahputra', 50000,
   'Jasa desain logo profesional. Revisi tak terbatas, file vektor disediakan.', 300, 4.9, 266, 1, 1, 'Desain Grafis', 'Desain Grafis', NULL),
  ('Design Website HTML CSS JS PHP', 'Hanan Rasyad Sadad', 500000,
   'Pembuatan website statis/dinamis menggunakan HTML/CSS/JS dan PHP. Responsive & SEO friendly.', 800, 4.9, 760, 1, 1, 'Web Development', 'Pemrograman', NULL),
  ('Logo Minimalis UMKM, FASHION, TOKO ONLINE', 'Alman Wicaksono', 75000,
   'Logo minimalis untuk UMKM dan toko online — cepat & hasil modern.', 250, 4.1, 150, 1, 1, 'Desain Grafis', 'Desain Grafis', NULL),
  ('Professional Motion Graphics Editing', 'Tirta Affandi', 300000,
   'Jasa motion graphics untuk promo, iklan, dan opening video.', 321, 4.1, 150, 1, 1, 'Video & Motion', '3D Modeling', NULL);

-- sample reviews (adjust serviceId values if your insert produced different ids)
INSERT INTO reviews (id, serviceId, userId, userName, rating, comment)
VALUES
  (CONCAT('r', LEFT(UUID(),8)), 1, 'u1', 'Pandji Prakoso', 5, 'Hasilnya memuaskan!'),
  (CONCAT('r', LEFT(UUID(),8)), 1, 'u2', 'Siti Rahma', 5, 'Pelayanan sangat profesional.'),
  (CONCAT('r', LEFT(UUID(),8)), 2, 'u3', 'Andi', 4.5, 'Website bagus, responsive.'),
  (CONCAT('r', LEFT(UUID(),8)), 3, 'u4', 'Maya', 4, 'Logo sesuai permintaan.');

-- recompute aggregates
UPDATE services s
SET s.rating = COALESCE((SELECT AVG(r.rating) FROM reviews r WHERE r.serviceId = s.id), 0),
    s.reviews = COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.serviceId = s.id), 0);