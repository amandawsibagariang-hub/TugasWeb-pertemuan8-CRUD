<?php

// =====================================================
// KONEKSI DATABASE
// =====================================================

$host = "localhost";
$dbname = "db_tugas_rutin_8";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal.");
}


// =====================================================
// FUNGSI AMAN
// =====================================================

function e($data)
{
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}


// =====================================================
// PROSES TAMBAH PRODUK
// =====================================================

if (isset($_POST['tambah'])) {

    $nama_produk = trim($_POST['nama_produk']);
    $id_kategori = (int) $_POST['id_kategori'];
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    if (
        $nama_produk !== '' &&
        $id_kategori > 0 &&
        $harga >= 0 &&
        $stok >= 0
    ) {

        $sql = "INSERT INTO produk
                (id_kategori, nama_produk, harga, stok)
                VALUES
                (:id_kategori, :nama_produk, :harga, :stok)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_kategori' => $id_kategori,
            ':nama_produk' => $nama_produk,
            ':harga' => $harga,
            ':stok' => $stok
        ]);

        header("Location: index.php?pesan=tambah");
        exit;
    }
}


// =====================================================
// PROSES UPDATE PRODUK
// =====================================================

if (isset($_POST['update'])) {

    $id_produk = (int) $_POST['id_produk'];
    $nama_produk = trim($_POST['nama_produk']);
    $id_kategori = (int) $_POST['id_kategori'];
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    if (
        $id_produk > 0 &&
        $nama_produk !== '' &&
        $id_kategori > 0 &&
        $harga >= 0 &&
        $stok >= 0
    ) {

        $sql = "UPDATE produk
                SET
                    id_kategori = :id_kategori,
                    nama_produk = :nama_produk,
                    harga = :harga,
                    stok = :stok
                WHERE id_produk = :id_produk";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_kategori' => $id_kategori,
            ':nama_produk' => $nama_produk,
            ':harga' => $harga,
            ':stok' => $stok,
            ':id_produk' => $id_produk
        ]);

        header("Location: index.php?pesan=update");
        exit;
    }
}


// =====================================================
// PROSES DELETE PRODUK
// =====================================================

if (isset($_GET['hapus'])) {

    $id_produk = (int) $_GET['hapus'];

    if ($id_produk > 0) {

        $sql = "DELETE FROM produk
                WHERE id_produk = :id_produk";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':id_produk' => $id_produk
        ]);
    }

    header("Location: index.php?pesan=hapus");
    exit;
}


// =====================================================
// AMBIL DATA KATEGORI
// =====================================================

$stmtKategori = $pdo->prepare(
    "SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori ASC"
);

$stmtKategori->execute();

$kategori = $stmtKategori->fetchAll();


// =====================================================
// AMBIL DATA PRODUK + JOIN KATEGORI
// =====================================================

$sqlProduk = "
    SELECT
        p.id_produk,
        p.nama_produk,
        p.harga,
        p.stok,
        k.id_kategori,
        k.nama_kategori
    FROM produk p
    INNER JOIN kategori k
        ON p.id_kategori = k.id_kategori
    ORDER BY p.id_produk DESC
";

$stmtProduk = $pdo->prepare($sqlProduk);
$stmtProduk->execute();

$produk = $stmtProduk->fetchAll();


// =====================================================
// DATA UNTUK EDIT
// =====================================================

$dataEdit = null;

if (isset($_GET['edit'])) {

    $id_edit = (int) $_GET['edit'];

    $stmtEdit = $pdo->prepare(
        "SELECT
            id_produk,
            id_kategori,
            nama_produk,
            harga,
            stok
         FROM produk
         WHERE id_produk = :id_produk"
    );

    $stmtEdit->execute([
        ':id_produk' => $id_edit
    ]);

    $dataEdit = $stmtEdit->fetch();
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>CRUD Katalog Produk</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button,
        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-tambah {
            background: #198754;
            color: white;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-hapus {
            background: #dc3545;
            color: white;
        }

        .btn-batal {
            background: #6c757d;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #212529;
            color: white;
        }

        tr:nth-child(even) {
            background: #f8f8f8;
        }

        .alert {
            padding: 12px;
            background: #d1e7dd;
            color: #0f5132;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .aksi {
            white-space: nowrap;
        }

        .info {
            background: #e7f1ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        @media (max-width: 700px) {

            .container {
                width: 95%;
            }

            table {
                font-size: 13px;
            }

            .card {
                padding: 15px;
                overflow-x: auto;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <h1>CRUD Katalog Produk</h1>

    <p class="subtitle">
        Tugas Rutin 8 - PHP & MySQL
    </p>


    <?php if (isset($_GET['pesan'])): ?>

        <div class="alert">

            <?php if ($_GET['pesan'] === 'tambah'): ?>

                Data produk berhasil ditambahkan.

            <?php elseif ($_GET['pesan'] === 'update'): ?>

                Data produk berhasil diperbarui.

            <?php elseif ($_GET['pesan'] === 'hapus'): ?>

                Data produk berhasil dihapus.

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <!-- ==========================================
         FORM TAMBAH / EDIT
         ========================================== -->

    <div class="card">

        <?php if ($dataEdit): ?>

            <h2>Edit Produk</h2>

            <form method="POST">

                <input
                    type="hidden"
                    name="id_produk"
                    value="<?= e($dataEdit['id_produk']) ?>"
                >

                <div class="form-group">

                    <label>Nama Produk</label>

                    <input
                        type="text"
                        name="nama_produk"
                        value="<?= e($dataEdit['nama_produk']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Kategori</label>

                    <select name="id_kategori" required>

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        <?php foreach ($kategori as $k): ?>

                            <option
                                value="<?= e($k['id_kategori']) ?>"
                                <?= ($k['id_kategori'] == $dataEdit['id_kategori'])
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= e($k['nama_kategori']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Harga</label>

                    <input
                        type="number"
                        name="harga"
                        min="0"
                        step="0.01"
                        value="<?= e($dataEdit['harga']) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Stok</label>

                    <input
                        type="number"
                        name="stok"
                        min="0"
                        value="<?= e($dataEdit['stok']) ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="update"
                    class="btn btn-tambah"
                >
                    Simpan Perubahan
                </button>


                <a
                    href="index.php"
                    class="btn btn-batal"
                >
                    Batal
                </a>

            </form>


        <?php else: ?>

            <h2>Tambah Produk</h2>

            <form method="POST">

                <div class="form-group">

                    <label>Nama Produk</label>

                    <input
                        type="text"
                        name="nama_produk"
                        placeholder="Masukkan nama produk"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Kategori</label>

                    <select name="id_kategori" required>

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        <?php foreach ($kategori as $k): ?>

                            <option value="<?= e($k['id_kategori']) ?>">

                                <?= e($k['nama_kategori']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Harga</label>

                    <input
                        type="number"
                        name="harga"
                        min="0"
                        step="0.01"
                        placeholder="Masukkan harga"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Stok</label>

                    <input
                        type="number"
                        name="stok"
                        min="0"
                        placeholder="Masukkan stok"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="tambah"
                    class="btn btn-tambah"
                >
                    Tambah Produk
                </button>

            </form>

        <?php endif; ?>

    </div>


    <!-- ==========================================
         INFORMASI JOIN
         ========================================== -->

    <div class="info">

        <strong>Relasi Database:</strong>

        Data produk ditampilkan menggunakan
        <strong>INNER JOIN</strong> antara tabel
        <strong>produk</strong> dan
        <strong>kategori</strong>.

    </div>


    <!-- ==========================================
         TABEL DATA
         ========================================== -->

    <div class="card">

        <h2>Data Produk</h2>

        <table>

            <thead>

                <tr>

                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

                <?php if (count($produk) > 0): ?>

                    <?php $no = 1; ?>

                    <?php foreach ($produk as $p): ?>

                        <tr>

                            <td>
                                <?= $no++ ?>
                            </td>

                            <td>
                                <?= e($p['nama_produk']) ?>
                            </td>

                            <td>
                                <?= e($p['nama_kategori']) ?>
                            </td>

                            <td>
                                Rp <?= number_format(
                                    $p['harga'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                            <td>
                                <?= e($p['stok']) ?>
                            </td>

                            <td class="aksi">

                                <a
                                    href="index.php?edit=<?= e($p['id_produk']) ?>"
                                    class="btn btn-edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="index.php?hapus=<?= e($p['id_produk']) ?>"
                                    class="btn btn-hapus"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')"
                                >
                                    Hapus
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6" style="text-align:center;">
                            Belum ada data produk.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>