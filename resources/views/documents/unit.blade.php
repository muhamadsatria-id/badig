<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen {{ $module['title'] }} {{ $module['code'] ?? '-' }}/{{ $item->id }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 0;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
        }

        @page {
            size: A4;
            margin: 0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 24px auto;
            background: #ffffff;
            padding: 20mm 16mm;
            box-sizing: border-box;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            position: relative;
        }

        .actions {
            position: sticky;
            top: 0;
            background: #0f172a;
            padding: 10px 24px;
            display: table;
            width: 100%;
            box-sizing: border-box;
            z-index: 9999;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
        }

        .actions-inner {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }

        .actions a,
        .actions button {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.16);
            padding: 7px 14px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 11px;
            text-decoration: none;
            cursor: pointer;
            margin: 0 3px;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .actions a:hover,
        .actions button:hover {
            background: #ffffff;
            color: #0f172a;
            transform: translateY(-1px);
        }

        .actions a.btn-back {
            background: transparent;
            border-color: transparent;
            color: #cbd5e1;
        }

        .actions a.btn-back:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .kop-table {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop-row {
            display: table-row;
        }

        .kop-logo-cell {
            display: table-cell;
            width: 54px;
            vertical-align: middle;
        }

        .kop-text-cell {
            display: table-cell;
            vertical-align: middle;
            padding-left: 12px;
        }

        .logo-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            text-align: center;
            line-height: 44px;
            font-size: 18px;
            font-weight: 700;
        }

        h1 {
            font-size: 14px;
            margin: 0;
            color: #0f172a;
            letter-spacing: 0.3px;
            line-height: 1.3;
            font-weight: 700;
        }

        .sub-header {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 3px;
            font-weight: 500;
            letter-spacing: 0.2px;
        }

        .meta-container {
            margin-bottom: 16px;
        }

        .main-title {
            color: #0f172a;
            margin: 0 0 4px;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.3;
        }

        .entity-sub {
            font-size: 10px;
            color: #475569;
        }

        .badge-category {
            display: inline-block;
            background-color: #f8fafc;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
            table-layout: fixed;
        }

        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        table.data-table td.label-column {
            width: 28%;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        table.data-table td.value-column {
            color: #334155;
            line-height: 1.45;
        }

        .mono {
            font-family: Courier, monospace;
            color: #475569;
        }

        .foot-table {
            display: table;
            width: 100%;
            margin-top: 32px;
            font-size: 9.5px;
            color: #64748b;
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
        }

        .foot-row {
            display: table-row;
        }

        .foot-left {
            display: table-cell;
            text-align: left;
            vertical-align: top;
            width: 60%;
            font-style: italic;
            padding-right: 20px;
            line-height: 1.45;
        }

        .foot-right {
            display: table-cell;
            text-align: right;
            vertical-align: top;
            width: 40%;
            font-weight: 500;
            color: #334155;
            line-height: 1.45;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .actions {
                display: none !important;
            }

            .page {
                margin: 0;
                box-shadow: none;
                padding: 12mm 12mm;
                width: auto;
                min-height: auto;
            }
        }
    </style>
</head>
<body>

@if(!request()->boolean('embed'))
<div class="actions">
    <div class="actions-inner">
        <button data-action="print-page">Cetak / Simpan PDF</button>
        <a href="{{ route('units.show', [$unit, $item->id]) }}" class="btn-back">Kembali Ke Detail</a>
    </div>
</div>
@endif

<div class="page">
    <div class="kop-table">
        <div class="kop-row">
            <div class="kop-logo-cell">
                <div class="logo-box">★</div>
            </div>
            <div class="kop-text-cell">
                <h1>BASIS DATA TERINTEGRASI SAT INTELKAM</h1>
                <div class="sub-header">DOKUMEN REKAM DATA INTERNAL &bull; CLUSTER {{ strtoupper($module['title']) }} &bull; REKORD {{ strtoupper($module['code'] ?? '-') }}/{{ $item->id }}</div>
            </div>
        </div>
    </div>

    <div class="meta-container">
        <h2 class="main-title">{{ $item->{$module['main']} }}</h2>
        <div class="entity-sub">
            Kategori: <span class="badge-category">{{ $item->kategori }}</span> &nbsp;&bull;&nbsp;
            Wilayah Hukum: <b>{{ $item->village?->name ?? '-' }}</b>, {{ $item->district?->name ?? '-' }}
        </div>
    </div>

    <table class="data-table">
        @foreach($module['fields'] as $field)
            @php
                $value = match($field) {
                    'district_id' => $item->district?->name ?? '-',
                    'village_id' => $item->village?->name ?? '-',
                    'created_at' => optional($item->created_at)->format('d/m/Y H:i') ?? '-',
                    'updated_at' => optional($item->updated_at)->format('d/m/Y H:i') ?? '-',
                    default => $item->{$field} ?? '-'
                };
                $isMono = in_array($field, ['pelapor_nrp', 'koordinat_lat', 'koordinat_lng', 'tanggal_pembuatan', 'created_at', 'updated_at']);
            @endphp
            <tr>
                <td class="label-column">{{ str_replace('_', ' ', $field) }}</td>
                <td class="value-column {{ $isMono ? 'mono' : '' }}">{!! nl2br(e($value)) !!}</td>
            </tr>
        @endforeach
        <tr>
            <td class="label-column">Operator Penanggung Jawab</td>
            <td class="value-column">{{ $item->operator?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-column">Waktu Sinkronisasi Cetak</td>
            <td class="value-column mono">{{ now()->format('d/m/Y H:i') }} WIB</td>
        </tr>
    </table>

    <div class="foot-table">
        <div class="foot-row">
            <div class="foot-left">
                Catatan Keamanan: Lembar dokumen ini bersifat terbatas dan dihasilkan secara sistematis oleh subsistem BADIG Sat Intelkam. Segala bentuk penyebaran rekam data tanpa kewenangan tunduk pada hukum regulasi kedinasan yang berlaku.
            </div>
            <div class="foot-right">
                Sukabumi Kota, <span class="mono">{{ now()->format('d/m/Y') }}</span><br>
                <span style="display:block; margin-top:24px; font-weight:700; text-decoration:underline; text-transform:uppercase; font-size:9px; letter-spacing:0.3px;">Dokumen Riwayat Digital</span>
                <span class="mono" style="font-size:9px; color:#94a3b8; display:block; margin-top:2px;">ID-SEC: {{ md5($item->id . now()->format('Ymd')) }}</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>