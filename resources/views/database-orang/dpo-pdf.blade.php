<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            /* Margin standar surat dinas kepolisian */
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            background-color: #ffffff;
            font-size: 10pt;
            line-height: 1.25;
            position: relative;
        }
        
        /* --- WATERMARK SETTINGS --- */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            height: auto;
            opacity: 0.12;
            z-index: -1000;
            text-align: center;
        }
        .watermark img {
            width: 100%;
            height: auto;
        }

        /* --- KOP SURAT KIRI ATAS --- */
        .kop-surat {
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.15;
            margin-bottom: 15px;
        }
        .kop-surat .underline {
            text-decoration: underline;
        }

        /* --- JUDUL DPO / DATA ORANG --- */
        .title-section {
            text-align: center;
            margin-bottom: 12px;
        }
        .emblem-placeholder {
            font-size: 14pt;
            margin-bottom: 2px;
            letter-spacing: 3px;
        }
        .dpo-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }
        .dpo-number {
            font-size: 10pt;
            margin-top: 2px;
        }

        /* --- TABEL ATAS (FOTO & INSTRUKSI) --- */
        .top-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            background-color: transparent;
        }
        .top-box td {
            border: 1px solid #000000;
            padding: 8px;
            vertical-align: top;
            font-size: 9.5pt;
            background-color: transparent;
        }
        .photo-cell {
            width: 22%;
            text-align: center;
            vertical-align: middle !important;
        }
        .photo-frame {
            width: 110px;
            height: 140px;
            margin: 0 auto;
            text-align: center;
            overflow: hidden;
        }
        .photo-frame img {
            max-width: 110px;
            max-height: 140px;
        }

        /* --- TABEL KETERANGAN --- */
        .keterangan-header {
            text-align: center;
            font-weight: bold;
            letter-spacing: 3px;
            text-decoration: underline;
            padding: 6px 0;
            border-left: 1px solid #000000;
            border-right: 1px solid #000000;
            background-color: transparent;
        }
        .keterangan-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
            border-top: none;
            background-color: transparent;
        }
        .keterangan-table td {
            padding: 3px 6px;
            vertical-align: top;
            border: none;
            font-size: 9.5pt;
            background-color: transparent;
        }
        .col-num {
            width: 4%;
            text-align: left;
        }
        .col-label {
            width: 38%;
        }
        .col-colon {
            width: 3%;
            text-align: center;
        }
        .col-val {
            width: 55%;
        }

        /* Tabel khusus untuk poin (Ciri-ciri) */
        .sub-table {
            width: 100%;
            border-collapse: collapse;
            background-color: transparent;
        }
        .sub-table td {
            padding: 1px 0;
            font-size: 9.5pt;
            background-color: transparent;
        }
        .sub-num {
            width: 20px;
        }

        .underline {
            text-decoration: underline;
        }

        /* --- LAMPIRAN (PAGE BREAK & GRID FIX) --- */
        .attachment-section {
            page-break-before: always;
            break-before: page;
            margin-top: 20px;
        }
        .attachment-title {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            text-decoration: underline;
            margin-bottom: 15px;
        }
        .sub-heading {
            font-size: 9.5pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        /* Grid Tabel dengan Lebar Tetap */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
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
        
        /* Pembungkus Link & Gambar yang Ketat */
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
            margin: 0 auto 8px auto;
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
    </style>
</head>
<body>

    <div class="watermark">
        <img src="{{ asset('logo-badig.png') }}" alt="Watermark BADIG">
    </div>

    <div class="kop-surat">
        KEPOLISIAN NEGARA REPUBLIK INDONESIA<br>
        DAERAH {{ strtoupper($orang->polda ?: 'JAWA BARAT') }}<br>
        RESOR {{ strtoupper($orang->instansi ?: 'KOTA BANDUNG') }}<br>
        <span class="underline">Jl. Perintis Kemerdekaan No.10, Gunungparang <br> Kec. Cikole, Kota Sukabumi, Jawa Barat </span>
    </div>

    <div class="title-section">
        <div class="emblem-placeholder"></div>
        <div class="dpo-title">DAFTAR ORANG</div>
        <div class="dpo-number">Nomor : {{ $orang->nomor_dpo ?: 'ORANG/80/VII/RES.1.11./2025/Sat Reskrim' }}</div>
    </div>

    <table class="top-box">
        <tr>
            <td class="photo-cell">
                <div class="photo-frame">
                    @if($photoDataUri)
                        <img src="{{ $photoDataUri }}" alt="Foto Orang">
                    @else
                        <div style="font-weight: bold; color: #555; padding-top: 55px;">FOTO</div>
                    @endif
                </div>
            </td>
            <td style="width: 43%;">
                Untuk diawasi/ ditangkap/ diserahkan/ diinformasikan keberadaannya kepada Agen/ Agen Pembantu pada kantor Kepolisian tersebut di atas, dengan nomor Hp. <strong>{{ $orang->no_hp_penyidik ?: '-' }}</strong>.<br><br>
                di<br>
                <strong>{{ $orang->tempat_dikeluarkan ?: 'Soreang' }}</strong>
            </td>
            <td style="width: 35%;">
                Tersebut dalam surat permintaan dari :<br>
                {{ $orang->instansi ?: 'Kepala Resor Kepolisian Kota Bandung' }}<br>
                Nomor &nbsp; : {{ $orang->no_reg_kejahatan ?: '-' }}<br>
                Tanggal : {{ optional($orang->tanggal_dikeluarkan)->format('d F Y') ?: '-' }}
            </td>
        </tr>
    </table>

    <div class="keterangan-header">K E T E R A N G A N</div>

    <table class="keterangan-table">
        <tr>
            <td class="col-num">1.</td>
            <td class="col-label">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ strtoupper($orang->nama ?: '-') }}</td>
        </tr>
        <tr>
            <td class="col-num">2.</td>
            <td class="col-label">Nomor identitas [KTP/ SIM/ Paspor/ dll]</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num">3.</td>
            <td class="col-label">Kewarganegaraan</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->kewarganegaraan ?: 'Indonesia' }}</td>
        </tr>
        <tr>
            <td class="col-num">4.</td>
            <td class="col-label">Jenis kelamin</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->jenis_kelamin ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num">5.</td>
            <td class="col-label">Tempat tanggal lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->tempat_lahir ?: '-' }}, {{ optional($orang->tanggal_lahir)->format('d F Y') ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num">6.</td>
            <td class="col-label">Pekerjaan terakhir / dahulu</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->pekerjaan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num">7.</td>
            <td class="col-label">Alamat tempat tinggal</td>
            <td class="col-colon">:</td>
            <td class="col-val">
                {{ $orang->alamat ?: '-' }}
                @if($orang->alamat_alternatif)
                    <br><em>Alt: {{ $orang->alamat_alternatif }}</em>
                @endif
            </td>
        </tr>
        <tr>
            <td class="col-num">8.</td>
            <td class="col-label">[No. (Telp./Hp.)/ email / Fax / Akun Medsos]</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num">9.</td>
            <td class="col-label">Catatan pernah dihukum dan sebagainya</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->catatan_hukum ?: 'Tidak Pernah menjalani hukuman;' }}</td>
        </tr>
        <tr>
            <td class="col-num">10.</td>
            <td class="col-label">Ciri &ndash; ciri khusus</td>
            <td class="col-colon">:</td>
            <td class="col-val">
                <table class="sub-table">
                    <tr><td class="sub-num">a.</td><td>Tinggi/ Berat : {{ $orang->tinggi_badan ?: '-' }} cm/ {{ $orang->berat_badan ?: '-' }} kg;</td></tr>
                    <tr><td class="sub-num">b.</td><td>Rambut {{ $orang->rambut ?: '-' }};</td></tr>
                    <tr><td class="sub-num">c.</td><td>Bentuk tubuh {{ $orang->bentuk_tubuh ?: '-' }};</td></tr>
                    <tr><td class="sub-num">d.</td><td>Warna kulit {{ $orang->warna_kulit ?: '-' }};</td></tr>
                    <tr><td class="sub-num">e.</td><td>Mata {{ $orang->warna_mata ?: '-' }};</td></tr>
                    <tr><td class="sub-num">f.</td><td>Hidung {{ $orang->hidung ?: '-' }};</td></tr>
                    <tr><td class="sub-num">g.</td><td>Bibir {{ $orang->bibir ?: '-' }};</td></tr>
                    <tr><td class="sub-num">h.</td><td>Lainnya {{ $orang->ciri_lainnya ?: '-' }}.</td></tr>
                </table>
            </td>
        </tr>
        
        <tr>
            <td class="col-num" style="padding-top: 6px;">11.</td>
            <td colspan="3" style="padding-top: 6px; padding-right: 8px; text-align: justify;">
                Subjek diduga terkait {{ $orang->dugaan_tindak_pidana ?: '-' }} 
                @if($orang->analisis_deskripsi)
                    berupa {{ $orang->analisis_deskripsi }}
                @endif
                @if($orang->melanggar_pasal)
                    , sebagaimana dimaksud dalam pasal {{ $orang->melanggar_pasal }}.
                @endif
            </td>
        </tr>

        <tr>
            <td class="col-num">12.</td>
            <td class="col-label">Melanggar Pasal</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->melanggar_pasal ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num">13.</td>
            <td class="col-label">No. Reg. Kejahatan/ Pelanggaran</td>
            <td class="col-colon">:</td>
            <td class="col-val">{{ $orang->no_reg_kejahatan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-num" style="padding-bottom: 8px;">14.</td>
            <td class="col-label" style="padding-bottom: 8px;">Keterangan lain yang penting</td>
            <td class="col-colon" style="padding-bottom: 8px;">:</td>
            <td class="col-val" style="padding-bottom: 8px;">{{ $orang->keterangan_lain ?: '-' }}</td>
        </tr>
    </table>

    <!-- SEKSI B: LAMPIRAN DOKUMEN PENDUKUNG -->
    @if($attachmentImages->isNotEmpty() || $documentAttachments->isNotEmpty())
        <div class="attachment-section">
            <div class="attachment-title">LAMPIRAN DOKUMENTASI &amp; BERKAS PENDUKUNG</div>
            
            <!-- LAMPIRAN FOTO / GAMBAR VISUAL -->
            @if($attachmentImages->isNotEmpty())
                <div class="sub-heading">DOKUMENTASI FOTO / GAMBAR VISUAL</div>
                <table class="grid-table">
                    @foreach($attachmentImages->chunk(2) as $chunk)
                        <tr>
                            @foreach($chunk as $row)
                                <td>
                                    <div class="attachment-box">
                                        <a href="{{ route('database-orang.attachments.preview', [$orang, $row['file']->id]) }}" target="_blank" class="img-link">
                                            <img src="{{ $row['data_uri'] }}" alt="Gambar Lampiran" class="img-responsive" width="100%">
                                        </a>
                                        <div style="margin-top: 4px; font-size: 8pt; text-align: center;">
                                            <a href="{{ route('database-orang.attachments.preview', [$orang, $row['file']->id]) }}" class="doc-link" target="_blank">Lihat</a>
                                            &nbsp;&nbsp;|&nbsp;&nbsp;
                                            <a href="{{ route('database-orang.attachments.download', [$orang, $row['file']->id]) }}" class="doc-link">Unduh</a>
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

            <!-- LAMPIRAN BERKAS DOKUMEN (POLOS) -->
            @if($documentAttachments->isNotEmpty())
                <div class="sub-heading">BERKAS DOKUMEN PENDUKUNG (PDF / WORD / EXCEL / DLL)</div>
                <ul class="doc-list">
                    @foreach($documentAttachments as $file)
                        <li>
                            <a href="{{ route('database-orang.attachments.download', [$orang, $file->id]) }}" class="doc-link" target="_blank">{{ $file->original_name }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

</body>
</html>