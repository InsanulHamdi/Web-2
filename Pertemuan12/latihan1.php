<?php
$koneksi = mysqli_connect("localhost", "root", "", "lat_dbase");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$sql = "UPDATE tbl_mhs
        SET Age = 36
        WHERE FirstName = 'Karina'
        AND LastName = 'Suwandi'";

if (mysqli_query($koneksi, $sql)) {
    if (mysqli_affected_rows($koneksi) > 0) {
        echo "Data mahasiswa Karina Suwandi berhasil diperbarui. Umur sekarang 36 tahun.";
    } else {
        echo "Perintah UPDATE berhasil dijalankan, tetapi tidak ada data yang berubah. Pastikan data Karina Suwandi tersedia dan umurnya belum 36 tahun.";
    }
} else {
    echo "Gagal memperbarui data: " . mysqli_error($koneksi);
}

mysqli_close($koneksi);
?>
