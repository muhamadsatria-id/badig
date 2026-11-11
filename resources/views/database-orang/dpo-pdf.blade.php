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
            font-size: 10pt;
            line-height: 1.25;
            position: relative; /* Penting untuk penempatan watermark */
        }
        
        /* --- WATERMARK SETTINGS --- */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px; /* Atur ukuran lebar logo watermark di sini */
            height: auto;
            opacity: 0.12; /* Tingkat transparansi (0.1 = sangat samar, 0.2 = jelas) */
            z-index: -1000; /* Memastikan watermark berada di belakang teks dan tabel */
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
        .pro-justitia {
            margin-top: 3px;
            font-weight: bold;
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
            background-color: transparent; /* Memastikan background transparan */
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

        /* --- LAMPIRAN (PAGE BREAK) --- */
        .attachment-section {
            page-break-before: always;
            margin-top: 20px;
        }
        .image-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .image-grid td {
            width: 50%;
            padding: 8px;
            text-align: center;
            vertical-align: top;
        }
        .attachment-thumb {
            border: 1px solid #000000;
            max-width: 100%;
        }
        .attachment-caption {
            margin-top: 6px;
            font-size: 8pt;
            word-wrap: break-word;
        }
        .attachment-actions {
            margin-top: 4px;
            font-size: 8pt;
        }
        .attachment-actions a {
            color: #0000ee;
            text-decoration: underline;
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

    @if($attachmentImages->isNotEmpty() || $documentAttachments->isNotEmpty())
        <div class="attachment-section">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 10px;">LAMPIRAN DOKUMENTASI & BERKAS PENDUKUNG</div>
            
            @if($attachmentImages->isNotEmpty())
                <table class="image-grid">
                    @foreach($attachmentImages->chunk(2) as $chunk)
                        <tr>
                            @foreach($chunk as $row)
                                <td>
                                    <a href="{{ route('database-orang.attachments.preview', [$orang, $row['file']->id]) }}">
                                        <img src="{{ $row['data_uri'] }}" alt="Lampiran" class="attachment-thumb" @if(($row['width'] ?? 0) > 0) width="{{ $row['width'] }}" height="{{ $row['height'] }}" @endif>
                                    </a>
                                    <div class="attachment-caption">{{ $row['file']->original_name }}</div>
                                    <div class="attachment-actions">
                                        <a href="{{ route('database-orang.attachments.preview', [$orang, $row['file']->id]) }}">Lihat</a>
                                        &nbsp;&nbsp;|&nbsp;&nbsp;
                                        <a href="{{ route('database-orang.attachments.download', [$orang, $row['file']->id]) }}">Unduh</a>
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

            @if($documentAttachments->isNotEmpty())
                <div style="margin-top: 15px; font-weight: bold; font-size: 9pt;">Daftar Berkas Lampiran:</div>
                <ul style="font-size: 9pt; padding-left: 20px; margin-top: 5px;">
                    @foreach($documentAttachments as $file)
                        <li>
                            <a href="{{ route('database-orang.attachments.download', [$orang, $file->id]) }}" style="color: #0000ee; text-decoration: underline;">{{ $file->original_name }}</a>
                            ({{ strtoupper($file->extension) }} - {{ $file->human_file_size }}) &mdash; klik untuk lihat/unduh
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

</body>
</html>