<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BADIG Export Terpilih</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            line-height: 1.45;
            background-color: #ffffff;
            margin: 0;
        }

        .page {
            page-break-after: always;
            padding: 8mm;
        }

        .last {
            page-break-after: auto;
        }

        .kop {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .kop h1 {
            font-size: 15px;
            color: #0f172a;
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .muted {
            color: #64748b;
            font-size: 9px;
            margin-top: 2px;
            font-weight: 500;
        }

        .record-title {
            font-size: 12px;
            margin: 10px 0 8px;
            color: #0f172a;
            font-weight: 700;
        }

        .section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin: 16px 0 6px;
            font-weight: 700;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .table td,
        .table th {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table th {
            background-color: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        .label {
            width: 28%;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .value {
            color: #334155;
        }

        .mono {
            font-family: Courier, monospace;
            color: #475569;
        }

        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    @foreach($items as $item)
        <div class="page {{ $loop->last ? 'last' : '' }}">
            <div class="kop">
                <h1>BADIG</h1>
                <div class="muted">
                    Basis Data Terintegrasi Sat Intelkam &bull; Dokumen Ekspor Terpilih &bull; Cluster: {{ strtoupper($module['title']) }}
                </div>
            </div>

            <div class="record-title">
                <span class="mono">#{{ $item->id }}</span> &mdash; {{ $item->{$module['main']} }}
            </div>

            <table class="table">
                @foreach($module['fields'] as $field)
                    @php
                        $value = match ($field) {
                            'district_id' => $item->district?->name ?? '-',
                            'village_id' => $item->village?->name ?? '-',
                            'tanggal_pembuatan' => optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-',
                            default => $item->{$field} ?? '-',
                        };
                        $isMono = in_array($field, ['pelapor_nrp', 'koordinat_lat', 'koordinat_lng', 'tanggal_pembuatan']);
                    @endphp
                    <tr>
                        <td class="label">{{ str_replace('_', ' ', $field) }}</td>
                        <td class="value {{ $isMono ? 'mono' : '' }}">{!! nl2br(e($value)) !!}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="label">Operator Registrasi</td>
                    <td class="value">{{ $item->operator?->name ?? '-' }}</td>
                </tr>
            </table>

            <div class="section-title">Dokumentasi Lampiran Berkas</div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="center" style="width: 6%">No</th>
                        <th style="width: 64%">Nama File Dokumen</th>
                        <th style="width: 15%">Format</th>
                        <th style="width: 15%">Ukuran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($attachments[$item->id] ?? collect()) as $file)
                        <tr>
                            <td class="center mono">{{ $loop->iteration }}</td>
                            <td style="font-weight: 500; color: #0f172a;">{{ $file->original_name }}</td>
                            <td class="mono"><b>{{ strtoupper($file->extension) }}</b></td>
                            <td>{{ $file->human_file_size }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="center" style="color: #94a3b8; padding: 12px 0;">Tidak ada berkas fisik yang dilampirkan pada rekam data ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>