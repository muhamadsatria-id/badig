<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Report PDF BADIG</title>
    <style>
        @page {
            /* Margin standar dokumen resmi dinas kepolisian */
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000000;
            line-height: 1.3;
            background-color: #ffffff;
            position: relative;
        }

        /* --- KOP SURAT / BRANDING DINAS --- */
        .kop-surat {
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        /* --- JUDUL DOKUMEN --- */
        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }
        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }
        .doc-meta {
            font-size: 9.5pt;
            margin-top: 3px;
        }

        /* --- TABEL RESMI DINAS (BORDER HITAM TEGAS) --- */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            background-color: transparent;
            color: #000000;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #000000;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.3px;
        }

        .table td {
            padding: 5px 6px;
            border: 1px solid #000000;
            vertical-align: top;
            color: #000000;
            font-size: 8.5pt;
        }

        .bold-text {
            font-weight: bold;
        }

        .group-row td {
            background-color: #ededed;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .table a {
            color: #000000;
            text-decoration: underline;
        }

        .center {
            text-align: center;
        }

        /* --- FOOTER DOKUMEN DINAS --- */
        .footer {
            margin-top: 25px;
            border-top: 1px solid #000000;
            padding-top: 6px;
            color: #000000;
            font-size: 8pt;
        }
    </style>
</head>
<body>

    <!-- KOP DOKUMEN / BRANDING -->
    <div class="kop-surat">
        <span>BADIG · BASIS DATA TERINTEGRASI SAT INTELKAM</span>
    </div>

    <!-- JUDUL REKAPITULASI DOKUMEN -->
    <div class="title-section">
        <div class="doc-title">REKAPITULASI LAPORAN DATA INTERNAL</div>
        <div class="doc-meta">KLASTER: {{ strtoupper($module['title']) }} · Waktu Cetak: {{ now()->format('d/m/Y H:i') }} WIB</div>
    </div>

    @if(isset($globalAttachments))
        <!-- TABEL REKAPITULASI LAMPIRAN GLOBAL (DIKELOMPOKKAN PER KLASTER UNIT) -->
        <table class="table">
            <thead>
                <tr>
                    <th class="center" style="width: 6%">No</th>
                    <th>Nama Berkas Lampiran</th>
                    <th style="width: 20%">Uploader</th>
                    <th style="width: 22%">Tanggal Upload</th>
                </tr>
            </thead>
            <tbody>
                @forelse($globalAttachments->groupBy('unit') as $unitKey => $files)
                    <tr class="group-row">
                        <td colspan="4">KLASTER {{ strtoupper($units[$unitKey] ?? $unitKey) }}</td>
                    </tr>
                    @foreach($files as $file)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('pimpinan.file-manager.attachments.download', $file->id) }}">{{ $file->original_name }}</a>
                            </td>
                            <td>{{ $file->uploader?->name ?? '-' }}</td>
                            <td>{{ optional($file->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" class="center" style="padding: 15px;">Tidak ditemukan rekam data berkas lampiran fisik.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <!-- TABEL REKAPITULASI ITEMS / DOKUMEN DATA -->
        <table class="table">
            <thead>
                <tr>
                    <th class="center" style="width: 4%">No</th>
                    <th class="center" style="width: 6%">ID</th>
                    <th style="width: 10%">Tgl Buat</th>
                    <th style="width: 13%">Nama Pelapor</th>
                    <th style="width: 9%">NRP</th>
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
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="center">#{{ $item->id }}</td>
                        <td>{{ optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->pelapor_nama ?? '-' }}</td>
                        <td>{{ $item->pelapor_nrp ?? '-' }}</td>
                        <td>{{ $item->kategori }}</td>
                        <td class="bold-text">{{ $item->{$module['main']} }}</td>
                        <td>{{ $item->village?->name ?? '-' }}, {{ $item->district?->name ?? '-' }}</td>
                        <td class="center bold-text">{{ $attachmentCounts[$item->id] ?? 0 }}</td>
                        <td>{{ $item->operator?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="center" style="padding: 15px;">Tidak ditemukan rekaman berkas arsip data intelijen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- FOOTER DOKUMEN DINAS -->
    <div class="footer">
        Dicetak melalui Sistem BADIG Sat Intelkam pada {{ now()->format('d/m/Y H:i') }} WIB. Dokumen ini merupakan arsip rekapitulasi data internal resmi.
    </div>

</body>
</html>