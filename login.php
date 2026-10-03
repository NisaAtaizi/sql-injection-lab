<?php include("db.php"); ?>
<?php
// Login bypass demosu için örnek kullanıcı tablosu oluştur (yoksa)
$baglanti->query("CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50),
    sifre VARCHAR(50)
)");
$baglanti->query("INSERT IGNORE INTO kullanicilar (kullanici_adi, sifre) VALUES ('admin', 'sifre123')");
$baglanti->query("INSERT IGNORE INTO kullanicilar (kullanici_adi, sifre) VALUES ('kullanici', 'parola456')");

// Mod: güvenli (varsayılan) veya zayıf
$mode = 'secure';
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'weak') {
    $mode = 'weak';
}

$log = [];
$log['mode'] = $mode;
$giris_basarili = false;
$hata_mesaji = '';
$kullanici_adi = '';
$sifre = '';

if (isset($_POST['giris'])) {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    if ($mode === 'secure') {
        // GÜVENLİ SÜRÜM: Prepared Statement
        $sql_template = "SELECT * FROM kullanicilar WHERE kullanici_adi = ? AND sifre = ?";
        $log['query'] = $sql_template;
        $log['params'] = [$kullanici_adi, $sifre];

        try {
            $stmt = $baglanti->prepare($sql_template);
            $stmt->bind_param("ss", $kullanici_adi, $sifre);
            $stmt->execute();
            $sonuc = $stmt->get_result();
            $giris_basarili = $sonuc && $sonuc->num_rows > 0;
            $log['result'] = $giris_basarili ? 'success' : 'error';
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $hata_mesaji = $e->getMessage();
            $log['result'] = 'error';
        }
    } else {
        // ZAYIF SÜRÜM: Doğrudan string birleştirme (Eğitim amaçlıdır!)
        $sql = "SELECT * FROM kullanicilar WHERE kullanici_adi = '$kullanici_adi' AND sifre = '$sifre'";
        $log['query'] = $sql;
        $log['params'] = [$kullanici_adi, $sifre];

        $sonuc = mysqli_query($baglanti, $sql);
        if ($sonuc) {
            $giris_basarili = mysqli_num_rows($sonuc) > 0;
            $log['result'] = $giris_basarili ? 'success' : 'error';
        } else {
            $hata_mesaji = mysqli_error($baglanti);
            $log['result'] = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Laboratuvarı — Login Bypass</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d2f77c 0%, #1ec7dd 100%);
            color: #e2e8f0;
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
        }
        nav {
            text-align: center;
            margin-bottom: 30px;
        }
        nav a {
            color: #94a3b8;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 6px;
            transition: 0.3s;
        }
        nav a:hover, nav a.active {
            color: #fff;
            background: rgba(59, 130, 246, 0.2);
        }
        .container {
            max-width: 520px;
            margin: auto;
            background: #2954a3;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            border: 1px solid #334155;
        }
        h1 {
            text-align: center;
            margin: 0 0 10px;
            color: #38bdf8;
            font-size: 1.8rem;
        }
        .subtitle {
            text-align: center;
            color: #94a3b8;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        .mode-switcher {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .mode-switcher a {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            border: 1px solid #334155;
            color: #cbd5e1;
        }
        .mode-switcher a.secure.active {
            background: rgba(34, 197, 94, 0.15);
            border-color: #22c55e;
            color: #22c55e;
        }
        .mode-switcher a.weak.active {
            background: rgba(239, 68, 68, 0.15);
            border-color: #ef4444;
            color: #ef4444;
        }
        .warning {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid #f59e0b;
            color: #fbbf24;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: <?php echo $mode === 'weak' ? 'block' : 'none'; ?>;
        }
        .hint {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid #3b82f6;
            color: #93c5fd;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
        .hint code {
            background: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
            color: #38bdf8;
        }
        label {
            display: block;
            margin-top: 15px;
            font-size: 0.9rem;
            color: #cbd5e1;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #0f172a;
            color: #f1f5f9;
            font-size: 0.95rem;
        }
        input:focus {
            outline: none;
            border-color: #38bdf8;
        }
        button {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #2563eb, #38bdf8);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { opacity: 0.9; transform: translateY(-1px); }
        .message {
            padding: 14px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .success {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid #22c55e;
            color: #86efac;
        }
        .error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid #ef4444;
            color: #fca5a5;
        }
        .live-log {
            margin-top: 25px;
            background: #0b1120;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 18px;
        }
        .live-log h3 {
            margin: 0 0 12px;
            color: #38bdf8;
            font-size: 0.95rem;
        }
        .log-row {
            margin-bottom: 10px;
            font-size: 0.85rem;
        }
        .log-label {
            color: #64748b;
            display: inline-block;
            width: 90px;
        }
        .log-value {
            color: #e2e8f0;
            font-family: 'Courier New', monospace;
        }
        .query-box {
            background: #020617;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #a5f3fc;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">İletişim Formu</a>
        <a href="login.php" class="active">Login Bypass</a>
    </nav>

    <div class="container">
        <h1>SQL Injection Laboratuvarı</h1>
        <div class="subtitle">Login Bypass — Klasik SQL Injection Senaryosu</div>

        <div class="mode-switcher">
            <a href="?mode=secure" class="secure <?php echo $mode === 'secure' ? 'active' : ''; ?>">🔒 Güvenli Mod</a>
            <a href="?mode=weak" class="weak <?php echo $mode === 'weak' ? 'active' : ''; ?>">⚠️ Zayıf Mod</a>
        </div>

        <div class="warning">
            <strong>Uyarı:</strong> Zayıf mod yalnızca eğitim amaçlıdır. Kullanıcı girdisi doğrudan SQL sorgusuna eklenir.
        </div>

        <div class="hint">
            <strong>İpucu:</strong> Zayıf modda <code>' OR 1=1 -- -</code> payload’ı ile şifre alanını atlayarak giriş yapmayı dene.
        </div>

        <form method="POST">
            <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
            <label for="kullanici_adi">Kullanıcı Adı:</label>
            <input type="text" id="kullanici_adi" name="kullanici_adi" placeholder="örn. admin" required>

            <label for="sifre">Şifre:</label>
            <input type="text" id="sifre" name="sifre" placeholder="örn. sifre123" required>

            <button type="submit" name="giris">Giriş Yap</button>
        </form>

        <?php if ($giris_basarili): ?>
            <div class="message success">
                ✅ Giriş başarılı! Hoş geldiniz.
                <?php if ($mode === 'secure'): ?>
                    (Prepared statement ile doğrulandı.)
                <?php else: ?>
                    (SQL Injection ile bypass edildi!)
                <?php endif; ?>
            </div>
        <?php elseif (isset($_POST['giris']) && !$giris_basarili): ?>
            <div class="message error">
                ❌ <strong>Giriş başarısız.</strong>
                <?php if ($hata_mesaji): ?>
                    <?php echo htmlspecialchars($hata_mesaji); ?>
                <?php else: ?>
                    Kullanıcı adı veya şifre hatalı.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($log['query'])): ?>
        <div class="live-log">
            <h3>🖥️ Canlı Log</h3>
            <div class="log-row">
                <span class="log-label">Mod:</span>
                <span class="log-value"><?php echo $mode === 'secure' ? 'Güvenli (Prepared Statement)' : 'Zayıf (String Birleştirme)'; ?></span>
            </div>
            <div class="log-row">
                <span class="log-label">Parametreler:</span>
                <span class="log-value">kullanici_adi=<?php echo htmlspecialchars($log['params'][0]); ?> | sifre=<?php echo htmlspecialchars($log['params'][1]); ?></span>
            </div>
            <div class="log-row">
                <span class="log-label">Sonuç:</span>
                <span class="log-value" style="color: <?php echo $log['result'] === 'success' ? '#22c55e' : '#ef4444'; ?>;">
                    <?php echo $log['result'] === 'success' ? 'Giriş Başarılı' : 'Giriş Başarısız'; ?>
                </span>
            </div>
            <div class="log-row" style="margin-top:12px;">
                <span class="log-label">Çalışan Sorgu:</span>
            </div>
            <div class="query-box"><?php echo htmlspecialchars($log['query']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
