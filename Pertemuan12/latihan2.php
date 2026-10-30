<?php
$koneksi = mysqli_connect("localhost", "root", "", "lat_dbase");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$sql = "DELETE FROM tbl_mhs WHERE LastName = 'Prabowo'";

if (mysqli_query($koneksi, $sql)) {
    if (mysqli_affected_rows($koneksi) > 0) {
        echo "Data mahasiswa dengan nama belakang Prabowo berhasil dihapus.";
    } else {
        echo "Tidak ada data dengan nama belakang Prabowo yang ditemukan.";
    }
} else {
    echo "Gagal menghapus data: " . mysqli_error($koneksi);
}

mysqli_close($koneksi);
?>