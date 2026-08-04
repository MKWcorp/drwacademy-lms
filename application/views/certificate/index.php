<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .toolbar {
            background: white;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-print { background: #2563eb; color: white; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-download { background: #059669; color: white; }
        .btn-download:hover { background: #047857; }
        .btn-back { background: #6b7280; color: white; }
        .btn-back:hover { background: #4b5563; }
        .certificate-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        .certificate-wrapper img {
            width: 100%;
            height: auto;
            display: block;
        }
        .info-bar {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 1000px;
            width: 100%;
        }
        .info-bar h3 {
            margin-bottom: 8px;
            color: #1f2937;
        }
        .info-bar p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }
        @media print {
            body { background: white; padding: 0; }
            .toolbar, .info-bar { display: none; }
            .certificate-wrapper {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <button class="btn btn-print" onclick="window.print()">
        Cetak Sertifikat
    </button>
    <a class="btn btn-download" href="<?php echo $gambar_sertifikat; ?>" download>
        Unduh Gambar
    </a>
    <a class="btn btn-back" href="<?php echo site_url('home/my_courses'); ?>">
        Kembali ke Kursus Saya
    </a>
</div>

<div class="certificate-wrapper">
    <img src="<?php echo $gambar_sertifikat; ?>" alt="Sertifikat">
</div>

<div class="info-bar">
    <h3>Sertifikat Kelulusan</h3>
    <p>
        Diberikan kepada <strong><?php echo $siswa['first_name'] . ' ' . $siswa['last_name']; ?></strong>
        atas penyelesaian kursus <strong>"<?php echo $kursus['title']; ?>"</strong>
        pada tanggal <?php echo $tanggal_selesai; ?>.
    </p>
</div>

</body>
</html>
