<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 20px;
            line-height: 1.4;
        }

        .header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #0f172a;
        }

        .subtitle {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }

        .summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .summary p {
            margin: 0 0 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
        }

        tr:nth-child(even) td {
            background: #fcfdff;
        }
    </style>
</head>
<body>
    @php
        $rows = $people ?? [];
        $totalPeople = count($rows);
    @endphp

    <div class="header">
        <h1 class="title">Daftar Data Orang BADIG</h1>
        <p class="subtitle">Daftar data orang yang diekspor secara resmi pada {{ now()->format('d-m-Y H:i') }}.</p>
    </div>

    <div class="summary">
        <p><strong>Total Data:</strong> {{ $totalPeople }}</p>
        <p><strong>Dicetak:</strong> {{ now()->format('d-m-Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Unit</th>
                <th>NIK</th>
                <th>NRP</th>
                <th>Kontak</th>
                <th>Alamat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $orang)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $orang->nama ?? '-' }}</td>
                    <td>{{ $orang->unit?->nama_unit ?? '-' }}</td>
                    <td>{{ $orang->nik ?? '-' }}</td>
                    <td>{{ $orang->nrp ?? '-' }}</td>
                    <td>{{ $orang->phone ?? '-' }}<br>{{ $orang->email ?? '-' }}</td>
                    <td>{{ $orang->alamat ?? '-' }}</td>
                    <td>{{ $orang->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
