<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BADIG Export Terpilih</title>
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
            margin: 0;
        }

        .page {
            page-break-after: always;
        }

        .last {
            page-break-after: auto;
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
            text-decoration: underline;
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
            text-decoration: underline;
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

        /* --- LAMPIRAN (BERPINDAH KE HALAMAN BARU) --- */
        .attachment-section {
            page-break-before: always;
            break-before: page;
            margin-top: 0;
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
    @foreach($items as $item)
        <div class="page {{ $loop->last ? 'last' : '' }}">
            <!-- KOP DOKUMEN / BRANDING -->
            <div class="kop-surat">
                <span>BADIG · BASIS DATA TERINTEGRASI SAT INTELKAM</span>
            </div>

            <!-- JUDUL REKAM DATA -->
            <div class="title-section">
                <div class="doc-title">DOKUMEN REKAM DATA INTERNAL</div>
                <div class="doc-number">{{ strtoupper($module['title']) }} · Nomor Rekam #{{ $item->id }}</div>
            </div>

            <!-- HERO / RINGKASAN DATA (BORDER KOTAK LUAR SAJA) -->
            <table class="top-box">
                <tr>
                    <td>
                        <div class="hero-title">{{ $item->{$module['main']} ?: 'Rekam Data #'.$item->id }}</div>
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

            <!-- SEKSI IDENTITAS & ATRIBUT -->
            <div class="section-header">IDENTITAS DAN ATRIBUT REKAM DATA</div>

            <!-- BINGKAI KOTAK PALING LUAR SAJA -->
            <div class="outer-border-box">
                <table class="info-table">
                    @foreach($module['fields'] as $field)
                    @php
                        $value = match ($field) {
                            'district_id' => $item->district?->name ?? '-',
                            'village_id' => $item->village?->name ?? '-',
                            'tanggal_pembuatan' => optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-',
                            'created_at', 'updated_at' => optional($item->{$field})->format('d/m/Y H:i') ?? '-',
                            default => $item->{$field} ?? '-',
                        };
                    @endphp
                    <tr>
                        <td class="col-label">{{ ucwords(str_replace('_', ' ', $field)) }}</td>
                        <td class="col-colon">:</td>
                        <td class="col-val">{!! nl2br(e($value)) !!}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="col-label">Operator Registrasi</td>
                        <td class="col-colon">:</td>
                        <td class="col-val">{{ $item->operator?->name ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- SEKSI LAMPIRAN (OTOMATIS DI HALAMAN BARU) -->
            <div class="attachment-section">
                <div class="section-header" style="margin-top: 0;">LAMPIRAN DOKUMEN PENDUKUNG</div>

                <div class="sub-heading">BERKAS DOKUMEN PENDUKUNG (PDF / WORD / EXCEL / DLL)</div>
                
                @php
                    $itemList = $attachments[$item->id] ?? collect();
                @endphp

                @if($itemList->isNotEmpty())
                    <ul class="doc-list">
                        @foreach($itemList as $file)
                            <li>
                                <a href="{{ route('units.attachments.download', [$unit ?? $item->unit_id, $file->id]) }}" class="doc-link" target="_blank">{{ $file->original_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p style="font-size: 9pt; color: #000000; font-style: italic; margin-top: 6px;">
                        Tidak ada berkas fisik yang dilampirkan pada rekam data ini.
                    </p>
                @endif
            </div>

            <!-- FOOTER DOKUMEN -->
            <div class="footer">
                Dicetak melalui BADIG pada {{ now()->format('d/m/Y H:i') }}. Tautan lampiran memerlukan sesi BADIG yang aktif dan otorisasi unit yang sesuai.
            </div>
        </div>
    @endforeach
</body>
</html>