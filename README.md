# SQL Injection (SQLi) Laboratuvarı & Güvenlik Demosu

Bu proje; web uygulamalarında sıkça karşılaşılan **SQL Injection (SQLi)** zafiyetlerinin mantığını anlamak, zafiyetlerin nasıl sömürülebileceğini (exploit) görmek ve modern yazılım geliştirmede bu zafiyetlerin **Prepared Statements (Parametreli Sorgular)** ile nasıl engellendiğini uygulamalı olarak deneyimlemek için geliştirilmiş bir eğitim laboratuvarıdır.

---

## 🚀 Özellikler

- **İki Temel SQLi Senaryosu:**
  - **İletişim Formu (`index.php`):** `INSERT` sorgusu üzerinden SQL syntax bozma, yorum satırıyla sorgu manipülasyonu ve Error-Based (hata tabanlı) veri sızdırma.
  - **Login Bypass (`login.php`):** `SELECT` sorgusu üzerinden kimlik doğrulama atlatma (Authentication Bypass).
- **Çift Mod Mekanizması (Güvenli vs. Zayıf):**
  - 🔒 **Güvenli Mod:** `mysqli` Prepared Statements (`prepare` + `bind_param`) kullanarak girdileri ayrıştırır, SQLi saldırılarını tamamen engeller.
  - ⚠️ **Zayıf Mod:** Kullanıcı girdisini doğrudan SQL sorgusuna metin olarak ekler (String Concatenation); SQL enjeksiyonuna açıktır.
- **Canlı Log ve Hata İzleme Paneli:**
  - Form gönderildiğinde arka planda çalışan nihai SQL sorgusunu, aktarılan parametreleri ve MySQL hata mesajlarını canlı olarak ekranda gösterir.
- **Hazır Test Payload Kütüphanesi:**
  - `test.txt` içerisinde adım adım uygulanabilecek payload listesi ve beklenen sonuçlar.

---

## 📁 Proje Dosya Yapısı

```plaintext
bt/
├── db.php              # Veritabanı bağlantı konfigürasyonu (MySQLi)
├── config.example.php  # Örnek yapılandırma şablonu (şifreler için)
├── .gitignore          # Gizli yerel dosyaların GitHub'a gitmesini engeller
├── index.php           # İletişim formu (INSERT odaklı SQLi laboratuvarı)
├── login.php           # Giriş ekranı (SELECT / Login Bypass odaklı laboratuvar)
├── setup.sql           # Veritabanı şeması ve başlangıç demo verileri
├── test.txt            # Adım adım SQL Injection test payload'ları rehberi
└── README.md           # Proje dokümantasyonu
```

---

## 🛠️ Sistem Gereksinimleri

- **PHP:** 7.4 veya 8.x (PHP 8.1+ ile test edilmiştir)
- **Veritabanı:** MySQL veya MariaDB
- **Web Sunucusu:** Apache (XAMPP önerilir) veya PHP Dahili Web Sunucusu

---

## ⚙️ Kurulum ve Yapılandırma

### 1. Veritabanı Ayarları (`db.php`)
`db.php` dosyası varsayılan olarak standart yerel ayarlarla bağlanır:

| Parametre | Varsayılan Değer | Açıklama |
| :--- | :--- | :--- |
| **Host** | `127.0.0.1` | MySQL sunucu adresi (veya `DB_HOST`) |
| **Port** | `3306` | MySQL portu (veya `DB_PORT`) |
| **Kullanıcı** | `root` | Veritabanı kullanıcısı (veya `DB_USER`) |
| **Şifre** | `""` (Boş) | Standart XAMPP MySQL root şifresi (veya `DB_PASS`) |
| **Veritabanı** | `bt_test` | Veritabanı adı (veya `DB_NAME`) |

> 🔒 **Güvenlik Notu (GitHub & Gizlilik):** Kendi yerel MySQL şifreniz varsa, `config.example.php` dosyasını `config.local.php` olarak kopyalayabilir ve şifrenizi oraya girebilirsiniz. `.gitignore` yapılandırması sayesinde `config.local.php` dosyası **asla GitHub'a gönderilmez**.

### 2. Veritabanını Oluşturma
MySQL'e bağlanıp [setup.sql](setup.sql) dosyasını çalıştırın (phpMyAdmin üzerinden veya terminalden):

```sql
mysql -u root -p < setup.sql
```

Bu komut:
- `bt_test` adında bir veritabanı açar.
- `mesajlar` ve `kullanicilar` tablolarını oluşturur.
- Örnek kullanıcıları ekler:
  - `admin` / `sifre123`
  - `kullanici` / `parola456`

---

## ▶️ Projeyi Çalıştırma

### Yöntem A: XAMPP ile Çalıştırma (Önerilen)
1. **XAMPP Control Panel**'i açın.
2. **Apache** ve **MySQL** servislerini **Start** butonuna basarak başlatın.
3. Tarayıcınızda açın:
   - **İletişim Formu (SQLi):** `http://localhost/bt/index.php`
   - **Login Bypass:** `http://localhost/bt/login.php`

### Yöntem B: PHP Dahili Sunucusu ile Çalıştırma
XAMPP Apache'yi kullanmadan hızlıca çalıştırmak için:
1. Terminalde proje dizinine gidin:
   ```powershell
   cd C:\xampp\htdocs\bt
   ```
2. PHP sunucusunu başlatın:
   ```powershell
   php -S localhost:8000
   ```
3. Tarayıcınızda `http://localhost:8000/index.php` adresine gidin.

---

## 🧪 Uygulamalı Test Senaryoları

### 1. Login Bypass Demosu (`login.php`)
- **Mod:** ⚠️ **Zayıf Mod**
- **Hedef:** Kullanıcı şifresini bilmeden sisteme yönetici olarak giriş yapmak.
- **Kullanıcı Adı:**
  ```text
  admin' -- -
  ```
  *(veya `' OR 1=1 -- -`)*
- **Şifre:** Herhangi bir şey (ör. `123`)
- **Sonuç:**
  - **Zayıf Modda:** SQL sorgusu `SELECT * FROM kullanicilar WHERE kullanici_adi = 'admin' -- -' AND sifre = '...'` şekline dönüşür. Şifre kontrolü yorum satırına düştüğü için **Giriş Başarılı** olur.
  - **Güvenli Modda:** Girdi parametre olarak değerlendirilir, bypass **başarısız** olur.

---

### 2. Syntax Bozma ve Yorum Kesme (`index.php`)
- **Mod:** ⚠️ **Zayıf Mod**
- **Test 1 (Tek Tırnak):**
  - **Adınız:** `O'Brien`
  - **Sonuç:** Zayıf modda `syntax error` hatası fırlatır; güvenli modda ise sorunsuz şekilde veri tabanına metin olarak kaydedilir.
- **Test 2 (Sorgu Kesme):**
  - **Adınız:** `hacker', 'pwned')-- -`
  - **Mesajınız:** Boş bırakılabilir.
  - **Sonuç:** Sorgu erken kapanır ve ikinci kolon saldırganın istediği içerikle doldurulur.

---

### 3. Error-Based (Hata Tabanlı) Bilgi Sızdırma (`index.php`)
MySQL'in `extractvalue()` veya `updatexml()` fonksiyonları hatalı XPath girdisi aldığında hata mesajı içinde çalıştırılan alt sorgunun (subquery) sonucunu döndürür.

Zayıf modda **Adınız** alanına aşağıdaki payload'ları yapıştırarak deneyebilirsiniz:

| No | Payload (Adınız alanına yazılacak) | Sızdırılan Bilgi |
| :---: | :--- | :--- |
| **1** | `a', (SELECT extractvalue(1, concat(0x7e, version()))))-- -` | MySQL Versiyonu (örn. `~8.0.36`) |
| **2** | `a', (SELECT extractvalue(1, concat(0x7e, (SELECT database())))))-- -` | Aktif Veritabanı Adı (`~bt_test`) |
| **3** | `a', (SELECT extractvalue(1, concat(0x7e, current_user()))))-- -` | Veritabanı Kullanıcısı (`~root@localhost`) |
| **4** | `a', (SELECT extractvalue(1, concat(0x7e, @@hostname))))-- -` | Sunucu Hostname |
| **5** | `a', (SELECT extractvalue(1, concat(0x7e, (SELECT GROUP_CONCAT(table_name) FROM information_schema.tables WHERE table_schema=database())))))-- -` | Veritabanındaki tüm tablolar |
| **6** | `a', (SELECT extractvalue(1, concat(0x7e, (SELECT GROUP_CONCAT(column_name) FROM information_schema.columns WHERE table_name='mesajlar')))))-- -` | `mesajlar` tablosunun sütunları |

---

## 📊 Güvenli vs. Zayıf Mod Karşılaştırması

| Senaryo | Zayıf Sürüm (String Concatenation) | Güvenli Sürüm (Prepared Statements) |
| :--- | :---: | :---: |
| **Normal Giriş** (`Ahmet Yılmaz`) | ✅ Kaydedilir | ✅ Kaydedilir |
| **Özel Karakter / Tek Tırnak** (`O'Brien`) | ❌ SQL Sözdizimi Hatası | ✅ Güvenle Düz Metin Olarak Kaydedilir |
| **Yorum Satırı Ekleme** (`'-- -`) | ⚠️ Sorgu manipüle edilir | ✅ Güvenle Düz Metin Olarak Kaydedilir |
| **Error-Based Sızdırma** (`extractvalue`) | 🔥 Veritabanı bilgileri ekrana sızar | ✅ Hiçbir bilgi sızmaz, girdi zararsız metin kalır |
| **Login Bypass** (`' OR 1=1 -- -`) | 🚨 Yetkisiz oturum açılır | 🛑 Giriş reddedilir |

---

## 🔒 Güvenlik Notu ve Sorumluluk Reddi

> **UYARI:** Bu projedeki **Zayıf Mod** kodları ve test payload'ları sadece siber güvenlik eğitimi, akademik çalışma ve güvenlik bilincini artırma amacıyla tasarlanmıştır. Buradaki yöntemlerin izinsiz sistemler üzerinde kullanılması yasal suç teşkil edebilir. Gerçek hayat projelerinde her zaman **Prepared Statements / PDO / ORM** mekanizmaları kullanılmalıdır.
