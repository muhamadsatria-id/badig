<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Arsip BADIG</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
            margin: 24px;
            line-height: 1.5;
        }

        .header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        .item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            background: #f8fafc;
        }

        .item-title {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .item-meta {
            font-size: 11px;
            color: #475569;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="title">Laporan Arsip BADIG</h2>
        <p class="subtitle">Daftar arsip yang diekspor dalam format PDF.</p>
    </div>

    @foreach($archives as $archive)
        <div class="item">
            <h4 class="item-title">{{ $archive->judul }}</h4>
            <p class="item-meta">Tanggal File: {{ $archive->tanggal_pembuatan_file }}</p>
        </div>
    @endforeach
</body>
</html>

