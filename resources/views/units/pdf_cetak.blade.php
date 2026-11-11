<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            /* Margin standar dokumen resmi dinas */
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background-color: #ffffff;
            font-size: 10pt;
            line-height: 1.35;
            position: relative;
        }

        /* --- KOP / HEADER DOKUMEN --- */
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
            text-decoration: underline; /* Garis bawah pada DOKUMEN REKAM DATA INTERNAL */
        }
        .doc-number {
            font-size: 10pt;
            margin-top: 2px;
        }

        /* --- HERO / RINGKASAN ATAS (BORDER KOTAK LUAR SAJA) --- */
        .top-box {
            width: 100%;
            border: 1px solid #000000;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .top-box td {
            padding: 8px 10px;
            font-size: 9.5pt;
            border: none;
        }
        .top-box .hero-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 6px;
        }

        /* --- TABEL TITIK DUA TANPA BORDER DALAM --- */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 9.5pt;
            border: none;
        }
        .col-label {
            width: 32%;
        }
        .col-colon {
            width: 3%;
            text-align: center;
        }
        .col-val {
            width: 65%;
        }

        /* --- HEADER SEKSI DOKUMEN --- */
        .section-header {
            text-align: center;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-decoration: underline; /* Garis bawah header seksi */
            padding: 6px 0;
            margin-top: 15px;
            margin-bottom: 4px;
            border: none;
            background-color: transparent;
        }

        /* --- CONTAINER KOTAK DENGAN BORDER PALING LUAR SAJA --- */
        .outer-border-box {
            border: 1px solid #000000;
            padding: 8px 10px;
            margin-bottom: 15px;
        }

        /* --- SUB HEADING LAMPIRAN --- */
        .sub-heading {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        /* --- LAMPIRAN (BERPINDAH KE HALAMAN BARU & GRID FIX) --- */
        .attachment-section {
            page-break-before: always;
            break-before: page;
            margin-top: 0;
        }
        
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Mencegah sel membengkak melebihi halaman */
            margin-top: 6px;
        }
        .grid-table td {
            width: 50%;
            padding: 6px;
            vertical-align: top;
            box-sizing: border-box;
        }
        .attachment-box {
            border: 1px solid #000000;
            padding: 8px;
            text-align: center;
            background-color: transparent;
            page-break-inside: avoid;
            break-inside: avoid;
            box-sizing: border-box;
            width: 100%;
        }
        
        .img-link {
            display: block;
            width: 100%;
            text-align: center;
            text-decoration: none;
        }
        .img-responsive {
            max-width: 100% !important;
            height: auto !important;
            display: block;
            margin: 0 auto 6px auto;
        }
        
        .doc-link {
            color: #0000ee;
            text-decoration: underline;
            font-size: 8.5pt;
        }

        /* LIST BERKAS DOKUMEN POLOS */
        .doc-list {
            font-size: 9pt;
            padding-left: 20px;
            margin-top: 6px;
            line-height: 1.5;
        }
        .doc-list li {
            margin-bottom: 4px;
        }

        /* --- FOOTER DOKUMEN --- */
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

    <!-- JUDUL REKAM DATA -->
    <div class="title-section">
        <div class="doc-title">DOKUMEN REKAM DATA INTERNAL</div>
        <div class="doc-number">{{ strtoupper($module['title']) }} · Nomor Rekam {{ $module['code'] ?? '-' }}/{{ $item->id }}</div>
    </div>

    <!-- HERO / RINGKASAN DATA (BORDER KOTAK LUAR SAJA) -->
    <table class="top-box">
        <tr>
            <td>
                <div class="hero-title">{{ $item->{$module['main']} ?: 'Rekam Data '.($module['code'] ?? '-').'/'.$item->id }}</div>
                <table class="info-table">
                    <tr>
                        <td class="col-label">Kategori</td>
                        <td class="col-colon">:</td>
                        <td class="col-val"><strong>{{ $item->kategori ?: '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="col-label">Tanggal Pembuatan</td>
                        <td class="col-colon">:</td>
                        <td class="col-val"><strong>{{ optional($item->tanggal_pembuatan)->format('d/m/Y') ?: '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="col-label">Operator Registrasi</td>
                        <td class="col-colon">:</td>
                        <td class="col-val"><strong>{{ $item->operator?->name ?: '-' }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- SEKSI A: IDENTITAS & ATRIBUT (A. DIHAPUS, DIGARISBAWAHI) -->
    <div class="section-header">IDENTITAS DAN ATRIBUT REKAM DATA</div>

    <!-- BINGKAI KOTAK PALING LUAR SAJA -->
    <div class="outer-border-box">
        <table class="info-table">
            @foreach($module['fields'] as $field)
            @php
                $value = $item->{$field} ?? null;
                if ($field === 'district_id') $value = $item->district?->name;
                elseif ($field === 'village_id') $value = $item->village?->name;
                elseif ($field === 'tanggal_pembuatan') $value = optional($item->tanggal_pembuatan)->format('d/m/Y');
                elseif (in_array($field, ['created_at', 'updated_at'], true)) $value = optional($item->{$field})->format('d/m/Y H:i');
            @endphp
            <tr>
                <td class="col-label">{{ ucwords(str_replace('_', ' ', $field)) }}</td>
                <td class="col-colon">:</td>
                <td class="col-val">{!! nl2br(e($value ?: '-')) !!}</td>
            </tr>
            @endforeach
            <tr>
                <td class="col-label">Operator Registrasi</td>
                <td class="col-colon">:</td>
                <td class="col-val">{{ $item->operator?->name ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- SEKSI B: LAMPIRAN (B. DIHAPUS, DIGARISBAWAHI, OTOMATIS DI HALAMAN BARU) -->
    @if($attachmentImages->isNotEmpty() || $documentAttachments->isNotEmpty())
    <div class="attachment-section">
        <div class="section-header" style="margin-top: 0;">LAMPIRAN DOKUMEN PENDUKUNG</div>

        <!-- LAMPIRAN FOTO / GAMBAR -->
        @if($attachmentImages->isNotEmpty())
        <div class="sub-heading">DOKUMENTASI FOTO / GAMBAR VISUAL</div>
        <table class="grid-table">
            @foreach($attachmentImages->chunk(2) as $chunk)
            <tr>
                @foreach($chunk as $row)
                <td>
                    <div class="attachment-box">
                        <a href="{{ route('units.attachments.preview', [$unit, $row['file']->id]) }}" target="_blank" class="img-link">
                            <img src="{{ $row['data_uri'] }}" alt="Gambar Lampiran" class="img-responsive" width="100%">
                        </a>
                        <div style="margin-top: 4px; font-size: 8pt; text-align: center;">
                            <a href="{{ route('units.attachments.preview', [$unit, $row['file']->id]) }}" class="doc-link" target="_blank">Lihat</a>
                            &nbsp;&nbsp;|&nbsp;&nbsp;
                            <a href="{{ route('units.attachments.download', [$unit, $row['file']->id]) }}" class="doc-link">Unduh</a>
                        </div>
                    </div>
                </td>
                @endforeach
                @if($chunk->count() === 1)
                <td></td>
                @endif
            </tr>
            @endforeach
        </table>
        @endif

        <!-- LAMPIRAN BERKAS DOKUMEN (LIST POLOS TANPA KOTAK) -->
        @if($documentAttachments->isNotEmpty())
        <div class="sub-heading">BERKAS DOKUMEN PENDUKUNG (PDF / WORD / EXCEL / DLL)</div>
        <ul class="doc-list">
            @foreach($documentAttachments as $file)
                <li>
                    <a href="{{ route('units.attachments.download', [$unit, $file->id]) }}" class="doc-link" target="_blank">{{ $file->original_name }}</a>
                </li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

    <!-- FOOTER DOKUMEN -->
    <div class="footer">
        Dicetak melalui BADIG pada {{ now()->format('d/m/Y H:i') }}. Tautan lampiran memerlukan sesi BADIG yang aktif dan otorisasi unit yang sesuai.
    </div>

</body>
</html>