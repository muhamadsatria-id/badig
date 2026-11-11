<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Report PDF BADIG</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #334155;
            line-height: 1.45;
            background-color: #ffffff;
        }

        .header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.4px;
        }

        .meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .table th {
            background-color: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }

        .table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            color: #334155;
        }

        .table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .mono {
            font-family: Courier, monospace;
            color: #64748b;
        }

        .bold-slate {
            font-weight: 700;
            color: #0f172a;
        }

        .center {
            text-align: center;
        }

        .badge-category {
            background-color: #f8fafc;
            color: #0f172a;
            padding: 1px 6px;
            border-radius: 999px;
            font-weight: 700;
            border: 1px solid #e2e8f0;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BADIG — {{ $module['title'] }}</div>
        <div class="meta">Basis Data Terintegrasi Sat Intelkam &bull; Waktu Cetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
    </div>

    <div class="footer">
        Dokumen rahasia internal dihasilkan otomatis oleh Sistem Integrasi BADIG Sat Intelkam. Lampiran fisik berkas asli tetap terarsip aman pada File Manager pusat data.
    </div>

    @if(isset($globalAttachments))
        <table class="table">
            <thead>
                <tr>
                    <th class="center" style="width: 5%">No</th>
                    <th style="width: 12%">Klaster Unit</th>
                    <th style="width: 28%">Nama Berkas Lampiran</th>
                    <th style="width: 10%">ID Record</th>
                    <th style="width: 8%">Ekstensi</th>
                    <th style="width: 10%">Ukuran</th>
                    <th style="width: 12%">Uploader</th>
                    <th style="width: 15%">Tanggal Upload</th>
                </tr>
            </thead>
            <tbody>
                @forelse($globalAttachments as $file)
                    <tr>
                        <td class="center mono">{{ $loop->iteration }}</td>
                        <td class="bold-slate">{{ $units[$file->unit] ?? strtoupper($file->unit) }}</td>
                        <td>{{ $file->original_name }}</td>
                        <td class="mono">#{{ $file->record_id }}</td>
                        <td class="center bold-slate">{{ strtoupper($file->extension ?? '-') }}</td>
                        <td>{{ $file->human_file_size }}</td>
                        <td>{{ $file->uploader?->name ?? '-' }}</td>
                        <td class="mono">{{ optional($file->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="center" style="color: #94a3b8; padding: 20px;">Tidak ditemukan rekam data berkas lampiran fisik.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th class="center" style="width: 4%">No</th>
                    <th style="width: 6%">ID</th>
                    <th style="width: 10%">Tgl Buat</th>
                    <th style="width: 12%">Nama Pelapor</th>
                    <th style="width: 10%">NRP</th>
                    <th style="width: 10%">Kategori</th>
                    <th style="width: 22%">Nama / Entitas Subjek</th>
                    <th style="width: 14%">Wilayah Hukum</th>
                    <th class="center" style="width: 4%">File</th>
                    <th style="width: 8%">Operator</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="center mono">{{ $loop->iteration }}</td>
                        <td class="mono">#{{ $item->id }}</td>
                        <td class="mono">{{ optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->pelapor_nama ?? '-' }}</td>
                        <td class="mono">{{ $item->pelapor_nrp ?? '-' }}</td>
                        <td><span class="badge-category">{{ $item->kategori }}</span></td>
                        <td class="bold-slate">{{ $item->{$module['main']} }}</td>
                        <td style="font-size: 8.5px;">{{ $item->village?->name ?? '-' }}, {{ $item->district?->name ?? '-' }}</td>
                        <td class="center mono bold-slate">{{ $attachmentCounts[$item->id] ?? 0 }}</td>
                        <td>{{ $item->operator?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="center" style="color: #94a3b8; padding: 20px;">Tidak ditemukan rekaman berkas arsip data intelijen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>