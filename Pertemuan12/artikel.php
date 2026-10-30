<?php
require_once "koneksi.php";

// Fungsi untuk mengamankan teks saat ditampilkan
function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, "UTF-8");
}

$error = "";

// Proses tambah artikel
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tambah"])) {
    $judul = trim($_POST["judul"] ?? "");
    $isi = trim($_POST["isi"] ?? "");

    if ($judul === "" || $isi === "") {
        $error = "Judul dan isi artikel wajib diisi.";
    } else {
        $stmt = mysqli_prepare(
            $connection,
            "INSERT INTO artikel (judul, isi) VALUES (?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "ss", $judul, $isi);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: artikel.php?pesan=tambah");
            exit;
        }

        $error = "Gagal menambahkan artikel.";
        mysqli_stmt_close($stmt);
    }
}

// Proses edit artikel
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit"])) {
    $id = (int)($_POST["id"] ?? 0);
    $judul = trim($_POST["judul"] ?? "");
    $isi = trim($_POST["isi"] ?? "");

    if ($id <= 0 || $judul === "" || $isi === "") {
        $error = "Data artikel tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $connection,
            "UPDATE artikel SET judul = ?, isi = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $judul, $isi, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: artikel.php?pesan=edit");
            exit;
        }

        $error = "Gagal mengedit artikel.";
        mysqli_stmt_close($stmt);
    }
}

// Proses hapus artikel
if (isset($_GET["hapus"])) {
    $id = (int)$_GET["hapus"];

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $connection,
            "DELETE FROM artikel WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: artikel.php?pesan=hapus");
            exit;
        }

        $error = "Gagal menghapus artikel.";
        mysqli_stmt_close($stmt);
    }
}

// Ambil data untuk diedit
$editData = null;

if (isset($_GET["edit"])) {
    $idEdit = (int)$_GET["edit"];

    $stmt = mysqli_prepare(
        $connection,
        "SELECT id, judul, isi FROM artikel WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $idEdit);
    mysqli_stmt_execute($stmt);

    $resultEdit = mysqli_stmt_get_result($stmt);
    $editData = mysqli_fetch_assoc($resultEdit) ?: null;

    mysqli_stmt_close($stmt);
}

// Ambil seluruh artikel
$hasil = mysqli_query(
    $connection,
    "SELECT id, judul, isi FROM artikel ORDER BY id DESC"
);

if (!$hasil) {
    die("Gagal mengambil data artikel: " . mysqli_error($connection));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Artikel</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 30px 15px;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button, .btn {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            margin: 3px;
            font-size: 14px;
        }

        button {
            background: #2563eb;
            color: white;
        }

        .edit {
            background: #f59e0b;
            color: black;
        }

        .hapus {
            background: #dc2626;
            color: white;
        }

        .batal {
            background: #6b7280;
            color: white;
        }

        .pesan {
            background: #dcfce7;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .error {
            background: #fee2e2;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e5e7eb;
        }

        .isi {
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Manajemen Artikel</h2>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="pesan">
            <?php
            $pesan = [
                "tambah" => "Artikel berhasil ditambahkan.",
                "edit" => "Artikel berhasil diperbarui.",
                "hapus" => "Artikel berhasil dihapus."
            ];

            echo aman($pesan[$_GET["pesan"]] ?? "");
            ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error"><?= aman($error) ?></div>
    <?php endif; ?>

    <h3><?= $editData ? "Edit Artikel" : "Tambah Artikel" ?></h3>

    <form method="post" action="artikel.php">
        <?php if ($editData): ?>
            <input type="hidden" name="id"
                   value="<?= (int)$editData["id"] ?>">
        <?php endif; ?>

        <label for="judul">Judul Artikel</label>
        <input
            type="text"
            id="judul"
            name="judul"
            maxlength="150"
            required
            value="<?= aman($editData["judul"] ?? "") ?>"
        >

        <label for="isi">Isi Artikel</label>
        <textarea id="isi" name="isi" required><?= aman($editData["isi"] ?? "") ?></textarea>

        <?php if ($editData): ?>
            <button type="submit" name="edit">Simpan Perubahan</button>
            <a class="btn batal" href="artikel.php">Batal</a>
        <?php else: ?>
            <button type="submit" name="tambah">Tambah Artikel</button>
        <?php endif; ?>
    </form>

    <hr>

    <h3>Daftar Artikel</h3>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Isi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($hasil) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><?= (int)$row["id"] ?></td>
                        <td><?= aman($row["judul"]) ?></td>
                        <td class="isi"><?= aman($row["isi"]) ?></td>
                        <td>
                            <a class="btn edit"
                               href="artikel.php?edit=<?= (int)$row["id"] ?>">
                                Edit
                            </a>

                            <a class="btn hapus"
                               href="artikel.php?hapus=<?= (int)$row["id"] ?>"
                               onclick="return confirm('Yakin ingin menghapus artikel ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center">
                        Belum ada artikel.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>