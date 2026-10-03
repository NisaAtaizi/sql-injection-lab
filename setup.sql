

CREATE DATABASE IF NOT EXISTS bt_test
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE bt_test;

-- index.php icin iletisim formu tablosu
CREATE TABLE IF NOT EXISTS mesajlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isim VARCHAR(100) NOT NULL,
    mesaj TEXT NOT NULL,
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- login.php icin demo kullanici tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL,
    sifre VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo kullanicilar (login bypass testi icin)
INSERT IGNORE INTO kullanicilar (kullanici_adi, sifre) VALUES ('admin', 'sifre123');
INSERT IGNORE INTO kullanicilar (kullanici_adi, sifre) VALUES ('kullanici', 'parola456');
