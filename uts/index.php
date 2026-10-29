
<?php
// KONEKSI DATABASE XAMPP
$conn = new mysqli(
    "127.0.0.1",
    "root",
    "",
    "db_pendaftaran_mahasiswa",
    3307
);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$pesan = "";
$hasil_data = null;

// PROSES SIMPAN DATA
if (isset($_POST['simpan'])) {

    $tgl_input = $_POST['tgl_input'];
    $kode = strtoupper(trim($_POST['kode']));
    $nama = trim($_POST['nama']);
    $jk = $_POST['jk'];
    $asal_sekolah = trim($_POST['asal_sekolah']);
    $pekerjaan_ortu = trim($_POST['pekerjaan_ortu']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tgl_lahir = $_POST['tgl_lahir'];

    $mat = (float) $_POST['mat'];
    $bing = (float) $_POST['bing'];
    $umum = (float) $_POST['umum'];

    // HITUNG RATA-RATA
    $rata = ($mat + $bing + $umum) / 3;

    // MENENTUKAN KETERANGAN
    if ($rata >= 70) {
        $keterangan = "Lulus";
    } elseif ($rata >= 60) {
        $keterangan = "Cadangan";
    } else {
        $keterangan = "Tidak Lulus";
    }

    // MENENTUKAN TEMPAT TES
    $kode_awal = substr($kode, 0, 1);

    if ($kode_awal == "A") {
        $tempat_tes = "Gedung A";
    } elseif ($kode_awal == "B") {
        $tempat_tes = "Gedung B";
    } elseif ($kode_awal == "V") {
        $tempat_tes = "Viktor";
    } else {
        $tempat_tes = "Tidak Diketahui";
    }

    // SIMPAN KE DATABASE
    $stmt = $conn->prepare(
        "INSERT INTO pendaftar
        (tgl_input, kode, nama, jk, asal_sekolah,
        pekerjaan_ortu, tempat_lahir, tgl_lahir,
        mat, bing, umum, rata_rata, keterangan, tempat_tes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssssssddddss",
        $tgl_input,
        $kode,
        $nama,
        $jk,
        $asal_sekolah,
        $pekerjaan_ortu,
        $tempat_lahir,
        $tgl_lahir,
        $mat,
        $bing,
        $umum,
        $rata,
        $keterangan,
        $tempat_tes
    );

    if ($stmt->execute()) {
        $pesan = "Data berhasil disimpan ke database!";

        $hasil_data = [
            'kode' => $kode,
            'nama' => $nama,
            'jk' => $jk,
            'tempat_lahir' => $tempat_lahir,
            'tgl_lahir' => $tgl_lahir,
            'asal_sekolah' => $asal_sekolah,
            'pekerjaan_ortu' => $pekerjaan_ortu,
            'tempat_tes' => $tempat_tes,
            'mat' => $mat,
            'bing' => $bing,
            'umum' => $umum,
            'rata' => $rata,
            'keterangan' => $keterangan
        ];
    } else {
        $pesan = "Gagal menyimpan data: " . $stmt->error;
    }

    $stmt->close();
}

// REKAP SELURUH DATA DI DATABASE
$rekap = $conn->query(
    "SELECT
        COUNT(*) AS jumlah,
        COALESCE(SUM(keterangan = 'Lulus'), 0) AS lulus,
        COALESCE(SUM(keterangan = 'Cadangan'), 0) AS cadangan,
        COALESCE(SUM(keterangan = 'Tidak Lulus'), 0) AS tidak_lulus
     FROM pendaftar"
);

$jumlah_pendaftar = 0;
$jumlah_lulus = 0;
$jumlah_cadangan = 0;
$jumlah_tidak_lulus = 0;

if ($rekap) {
    $rekap_data = $rekap->fetch_assoc();
    $jumlah_pendaftar = $rekap_data['jumlah'];
    $jumlah_lulus = $rekap_data['lulus'];
    $jumlah_cadangan = $rekap_data['cadangan'];
    $jumlah_tidak_lulus = $rekap_data['tidak_lulus'];
}

// AMANKAN OUTPUT HTML
function aman($nilai) {
    return htmlspecialchars((string)$nilai, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTS PHP - Pendaftaran Mahasiswa</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 30px;
            background: #f4f4f4;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        input[type=text],
        input[type=number],
        input[type=date] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .btn {
            background: blue;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        .btn:hover {
            background: darkblue;
        }

        .info {
            margin-top: 20px;
            padding: 10px;
            background: #eef;
        }

        .pesan {
            margin: 15px 0;
            padding: 12px;
            background: #e6f5e9;
        }

        .tabel {
            overflow-x: auto;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>FORM PENDAFTARAN MAHASISWA BARU</h2>

    <?php if ($pesan != ""): ?>
        <div class="pesan">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Tanggal Input</label>
        <input type="date" name="tgl_input" required>

        <label>Kode Pendaftaran</label>
        <input type="text" name="kode"
               placeholder="Contoh : A2" required>

        <label>Nama Pendaftar</label>
        <input type="text" name="nama" required>

        <label>Jenis Kelamin</label><br>
        <input type="radio" name="jk"
               value="Laki-Laki" required> Laki-Laki

        <input type="radio" name="jk"
               value="Perempuan"> Perempuan

        <br><br>

        <label>Asal Sekolah</label>
        <input type="text" name="asal_sekolah" required>

        <label>Pekerjaan Orang Tua</label>
        <input type="text" name="pekerjaan_ortu" required>

        <label>Tempat Lahir</label>
        <input type="text" name="tempat_lahir" required>

        <label>Tanggal Lahir</label>
        <input type="date" name="tgl_lahir" required>

        <label>Matematika</label>
        <input type="number" name="mat"
               min="0" max="100" step="0.01" required>

        <label>Bahasa Inggris</label>
        <input type="number" name="bing"
               min="0" max="100" step="0.01" required>

        <label>Umum</label>
        <input type="number" name="umum"
               min="0" max="100" step="0.01" required>

        <button type="submit" name="simpan" class="btn">
            SIMPAN
        </button>

    </form>

    <?php if ($hasil_data !== null): ?>
    <div class="tabel">
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>JK</th>
                <th>Tgl Lahir</th>
                <th>Asal Sekolah</th>
                <th>Pekerjaan Ortu</th>
                <th>Tempat Tes</th>
                <th>MAT</th>
                <th>B. Inggris</th>
                <th>Umum</th>
                <th>Rata-rata</th>
                <th>Keterangan</th>
            </tr>

            <tr>
                <td><?= aman($hasil_data['kode']) ?></td>
                <td><?= aman($hasil_data['nama']) ?></td>
                <td><?= aman($hasil_data['jk']) ?></td>
                <td>
                    <?= aman($hasil_data['tempat_lahir']) ?>,
                    <?= aman($hasil_data['tgl_lahir']) ?>
                </td>
                <td><?= aman($hasil_data['asal_sekolah']) ?></td>
                <td><?= aman($hasil_data['pekerjaan_ortu']) ?></td>
                <td><?= aman($hasil_data['tempat_tes']) ?></td>
                <td><?= aman($hasil_data['mat']) ?></td>
                <td><?= aman($hasil_data['bing']) ?></td>
                <td><?= aman($hasil_data['umum']) ?></td>
                <td><?= number_format($hasil_data['rata'], 2) ?></td>
                <td><?= aman($hasil_data['keterangan']) ?></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <div class="info">
        <h3>Rekap Data:</h3>
        <p>Jumlah Pendaftar:
            <b><?= aman($jumlah_pendaftar) ?></b>
        </p>
        <p>Jumlah Peserta Lulus:
            <b><?= aman($jumlah_lulus) ?></b>
        </p>
        <p>Jumlah Peserta Cadangan:
            <b><?= aman($jumlah_cadangan) ?></b>
        </p>
        <p>Jumlah Tidak Lulus:
            <b><?= aman($jumlah_tidak_lulus) ?></b>
        </p>
    </div>

    <div class="info">
        <h3>Keterangan:</h3>

        <p><b>Kode Tempat Tes:</b></p>
        <ul>
            <li>A = Gedung A</li>
            <li>B = Gedung B</li>
            <li>V = Viktor</li>
        </ul>

        <p><b>Hasil Seleksi:</b></p>
        <ul>
            <li>Rata-rata ≥ 70 = Lulus</li>
            <li>Rata-rata 60 sampai kurang dari 70 = Cadangan</li>
            <li>Rata-rata kurang dari 60 = Tidak Lulus</li>
        </ul>
    </div>

</div>
</body>
</html>
