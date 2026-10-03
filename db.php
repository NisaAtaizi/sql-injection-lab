<?php
// PHP 8.1+ varsayılanı exception fırlatmak; lab amaçlı kapatıyoruz ki
// mysqli_query false dönsün ve mysqli_error() ile hatayı ekrana basabilelim.
mysqli_report(MYSQLI_REPORT_OFF);

// Yerel yapılandırma dosyası varsa yükle (.gitignore ile GitHub'a yüklenmesi engellenir)
if (file_exists(__DIR__ . '/config.local.php')) {
    include_once __DIR__ . '/config.local.php';
}

$host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: '127.0.0.1');
$user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
$pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
$db   = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: 'bt_test');
$port = defined('DB_PORT') ? DB_PORT : (getenv('DB_PORT') ?: '3306');

$baglanti = mysqli_connect($host, $user, $pass, $db, $port);

if (!$baglanti) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}
?>