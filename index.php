<?php include("db.php"); ?>
<?php
// Mod: güvenli (varsayılan) veya zayıf
$mode = 'secure';
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'weak') {
    $mode = 'weak';
}

$log = [];
$log['mode'] = $mode;
$mesaj_kaydedildi = false;
$hata_mesaji = '';

if (isset($_POST['gonder'])) {
    $isim  = $_POST['isim'];
    $mesaj = $_POST['mesaj'];

    if ($mode === 'secure') {
        // GÜVENLİ SÜRÜM: Prepared Statement
        $sql_template = "INSERT INTO mesajlar (isim, mesaj) VALUES (?, ?)";
        $log['query'] = $sql_template;
        $log['params'] = [$isim, $mesaj];

        try {
            $stmt = $baglanti->prepare($sql_template);
            $stmt->bind_param("ss", $isim, $mesaj);
            if ($stmt->execute()) {
                $mesaj_kaydedildi = true;
                $log['result'] = 'success';
            } else {
                $hata_mesaji = $stmt->error;
                $log['result'] = 'error';
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $hata_mesaji = $e->getMessage();
            $log['result'] = 'error';
        }
    } else {
        // ZAYIF SÜRÜM: Doğrudan string birleştirme (Eğitim amaçlıdır!)
        $sql = "INSERT INTO mesajlar (isim, mesaj) VALUES ('$isim', '$mesaj')";
        $log['query'] = $sql;
        $log['params'] = [$isim, $mesaj];

        $sonuc = mysqli_query($baglanti, $sql);
        if ($sonuc) {
            $mesaj_kaydedildi = true;
            $log['result'] = 'success';
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
    <title>SQL Injection Laboratuvarı</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #217469 0%, #e04da8 100%);
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
            background: rgba(59, 218, 246, 0.2);
        }
        .container {
            max-width: 520px;
            margin: auto;
            background: #216660;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            border: 1px solid #334155;
        }
        h1 {
            text-align: center;
            margin: 0 0 10px;
            color: #024b45;
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
        label {
            display: block;
            margin-top: 15px;
            font-size: 0.9rem;
            color: #cbd5e1;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #cdd5db;
            color: #000000;
            font-size: 0.95rem;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #38bdf8;
        }
        textarea { min-height: 100px; resize: vertical; }
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
        <a href="index.php" class="active">İletişim Formu</a>
        <a href="login.php">Login Bypass</a>
    </nav>

    <div class="container">
        <h1>SQL Injection Laboratuvarı</h1>
        <div class="subtitle">İletişim Formu — Güvenli / Zayıf Mod Karşılaştırması</div>

        <div class="mode-switcher">
            <a href="?mode=secure" class="secure <?php echo $mode === 'secure' ? 'active' : ''; ?>">🔒 Güvenli Mod</a>
            <a href="?mode=weak" class="weak <?php echo $mode === 'weak' ? 'active' : ''; ?>">⚠️ Zayıf Mod</a>
        </div>

        <div class="warning">
            <strong>Uyarı:</strong> Zayıf mod yalnızca eğitim amaçlıdır. Kullanıcı girdisi doğrudan SQL sorgusuna eklenir.
        </div>

        <form method="POST">
            <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
            <label for="isim">Adınız:</label>
            <input type="text" id="isim" name="isim" placeholder="örn. Ahmet Yılmaz" required>

            <label for="mesaj">Mesajınız:</label>
            <textarea id="mesaj" name="mesaj" placeholder="Mesajınızı yazın..." required></textarea>

            <button type="submit" name="gonder">Mesajı Gönder</button>
        </form>

        <?php if ($mesaj_kaydedildi): ?>
            <div class="message success">
                ✅ Mesaj başarıyla kaydedildi!
                <?php if ($mode === 'secure'): ?>
                    (Girdi parametre olarak bağlandı, SQL Injection mümkün değil.)
                <?php endif; ?>
            </div>
        <?php elseif ($hata_mesaji): ?>
            <div class="message error">
                ❌ <strong>Hata:</strong> <?php echo htmlspecialchars($hata_mesaji); ?>
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
                <span class="log-value">isim=<?php echo htmlspecialchars($log['params'][0]); ?> | mesaj=<?php echo htmlspecialchars($log['params'][1]); ?></span>
            </div>
            <div class="log-row">
                <span class="log-label">Sonuç:</span>
                <span class="log-value" style="color: <?php echo $log['result'] === 'success' ? '#22c55e' : '#ef4444'; ?>;">
                    <?php echo $log['result'] === 'success' ? 'Başarılı' : 'Başarısız'; ?>
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
