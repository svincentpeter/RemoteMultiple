<?php
session_start();
require_once 'dbconfig.php'; // File koneksi database Anda

if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Ganti index.php dengan halaman login Anda
    exit();
}

$nama_akun = $_SESSION['username'];

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data tahun dari tabel transaksi untuk bagian Validasi Closing
$years_query_validasi = "SELECT DISTINCT YEAR(tglpost) as tahun FROM transaksi WHERE posting = 1 ORDER BY tahun ASC";
$years_result_validasi = $conn->query($years_query_validasi);
$years_validasi = [];
if ($years_result_validasi->num_rows > 0) {
    while ($row = $years_result_validasi->fetch_assoc()) {
        $years_validasi[] = $row['tahun'];
    }
}

// Ambil data tahun dari tabel transaksi untuk bagian Closing
$years_query = "SELECT DISTINCT YEAR(tglpost) as tahun FROM transaksi ORDER BY tahun ASC";
$years_result = $conn->query($years_query);
$years = [];
if ($years_result->num_rows > 0) {
    while ($row = $years_result->fetch_assoc()) {
        $years[] = $row['tahun'];
    }
}

$display_results_validasi = false;
$display_results = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'Tampilkan Validasi' && isset($_POST['bulan_validasi']) && isset($_POST['tahun_validasi'])) {
        $bulan_validasi = intval($_POST['bulan_validasi']);
        $tahun_validasi = intval($_POST['tahun_validasi']);
        $tanggal_validasi = date("Y-m-d");

        // Query untuk menampilkan data validasi transaksi
        $sql_display_validasi = "SELECT detail_transaksi.id_transaksi, detail_transaksi.id_akun, '$tanggal_validasi' as tglclosing, detail_transaksi.nilai
                                 FROM transaksi 
                                 JOIN detail_transaksi USING(id_transaksi)
                                 WHERE month(transaksi.tglpost) = $bulan_validasi 
                                 AND year(transaksi.tglpost) = $tahun_validasi 
                                 AND transaksi.posting = 1";

        $result_validasi = $conn->query($sql_display_validasi);
        $display_results_validasi = true;
    } elseif ($action == 'Submit Validasi' && isset($_POST['id_akun_validasi'])) {
        $tanggal_validasi = date("Y-m-d");

        // Proses penyimpanan data ke tabel closing
        foreach ($_POST['id_akun_validasi'] as $index => $id_akun) {
            $nilai = $_POST['nilai_validasi'][$index];
            $sql_closing_validasi = "INSERT INTO closing (id_akun, tglclosing, saldo) VALUES ('$id_akun', '$tanggal_validasi', '$nilai')";
            $conn->query($sql_closing_validasi);
        }
        $validasi_message = "Data validasi closing berhasil ditambahkan.";

        // Query untuk menampilkan data closing setelah submit validasi
        $sql_display_closing_validasi = "SELECT id_akun, tglclosing, saldo FROM closing ORDER BY tglclosing DESC";
        $result_closing_validasi = $conn->query($sql_display_closing_validasi);
    } elseif ($action == 'Tampilkan' && isset($_POST['bulan']) && isset($_POST['tahun'])) {
        $bulan = intval($_POST['bulan']);
        $tahun = intval($_POST['tahun']);
        $tanggal = date("Y-m-d");

        // Query untuk menampilkan data transaksi
        $sql_display = "SELECT detail_transaksi.id_transaksi, detail_transaksi.id_akun, '$tanggal' as tglclosing, detail_transaksi.nilai
                        FROM transaksi 
                        JOIN detail_transaksi USING(id_transaksi)
                        WHERE month(transaksi.tglpost) = $bulan 
                        AND year(transaksi.tglpost) = $tahun 
                        AND transaksi.posting = 1";

        $result = $conn->query($sql_display);
        $display_results = true;
    } elseif ($action == 'Submit' && isset($_POST['id_akun'])) {
        $tanggal = date("Y-m-d");

        // Proses penyimpanan data ke tabel closing
        foreach ($_POST['id_akun'] as $index => $id_akun) {
            $nilai = $_POST['nilai'][$index];
            $sql_closing = "INSERT INTO closing (id_akun, tglclosing, saldo) VALUES ('$id_akun', '$tanggal', '$nilai')";
            $conn->query($sql_closing);
        }
        $closing_message = "Data closing berhasil ditambahkan.";

        // Query untuk menampilkan data closing setelah submit
        $sql_display_closing = "SELECT id_akun, tglclosing, saldo FROM closing ORDER BY tglclosing DESC";
        $result_closing = $conn->query($sql_display_closing);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closing</title>
    <style>
    h2 {
        text-align: center;
    }

    body {
        font-family: 'Open Sans', sans-serif;
        margin: 0;
        padding: 0;
        background: #f4f4f4;
        font-weight: normal;
    }

    .container {
        max-width: 1100px;
        margin: 30px auto 20px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
        text-align: left;
    }

    tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tbody tr:hover {
        background-color: #ddd;
    }

    .navbar {
        background-color: #333;
        overflow: hidden;
    }

    .navbar a {
        float: left;
        display: block;
        color: #f2f2f2;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
    }

    .navbar a:hover {
        background-color: #ddd;
        color: black;
    }

    .dropdown {
        float: left;
        overflow: hidden;
    }

    .dropdown .dropbtn {
        font-size: 16px;
        border: none;
        outline: none;
        color: white;
        padding: 14px 16px;
        background-color: inherit;
        font-family: inherit;
        margin: 0;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        float: none;
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        text-align: left;
    }

    .dropdown-content a:hover {
        background-color: #ddd;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .filter-form {
        text-align: center;
        margin-bottom: 20px;
    }

    .filter-form select,
    .filter-form button {
        padding: 8px 12px;
        font-size: 16px;
        margin: 5px;
    }

    .filter-form button {
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .filter-form button:hover {
        background-color: #555;
    }

    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }

    .logout-btn {
        display: block;
        position: absolute;
        top: 5px;
        right: 20px;
        color: #f2f2f2;
        background-color: #f44336;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 16px;
        text-decoration: none;
    }

    .logout-btn:hover {
        background-color: #cc0000;
    }

    .action-buttons {
        display: flex;
    }

    .action-buttons button {
        background-color: #f44336;
        color: white;
        border: none;
        padding: 5px 10px;
        margin: 2px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .action-buttons button.edit-btn {
        background-color: #4CAF50;
    }

    .action-buttons button:hover {
        opacity: 0.8;
    }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="beranda.php">Input Data</a>
        <a href="jurnal.php">Jurnal</a>
        <a href="bukubesar.php">Buku Besar</a>
        <a href="neraca_saldo.php">Neraca Saldo</a>
        <a href="laba_rugi.php">Laba Rugi</a>
        <a href="neraca.php">Neraca</a>
        <a href="posting.php">Posting</a>
        <div class="dropdown">
            <button class="dropbtn">Closing
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="closing_bukubesar.php">Closing Buku Besar</a>
                <a href="closing_neraca.php">Closing Neraca</a>
                <a href="closing_rugilaba.php">Closing Rugi Laba</a>
            </div>
        </div>
    </div>
    <a class="logout-btn" href="logout.php">Logout</a>
    <h2>Validasi Closing</h2>
    <div class="container">
        <form method="POST" action="" class="filter-form">
            <label for="bulan_validasi">Pilih Bulan:</label>
            <select id="bulan_validasi" name="bulan_validasi" required>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo date("F", mktime(0, 0, 0, $i, 10)); ?></option>
                <?php endfor; ?>
            </select>
            <label for="tahun_validasi">Pilih Tahun:</label>
            <select id="tahun_validasi" name="tahun_validasi" required>
                <?php foreach ($years_validasi as $year_validasi): ?>
                <option value="<?php echo $year_validasi; ?>"><?php echo $year_validasi; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="action" value="Tampilkan Validasi">Tampilkan Validasi</button>
        </form>
        <div>
            <?php
            if (isset($validasi_message)) {
                echo "<p>$validasi_message</p>";
            }

            if ($display_results_validasi && isset($result_validasi) && $result_validasi->num_rows > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="bulan_validasi" value="<?php echo $bulan_validasi; ?>">
                <input type="hidden" name="tahun_validasi" value="<?php echo $tahun_validasi; ?>">
                <h2>Data Validasi Transaksi</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>ID Akun</th>
                            <th>Tanggal Posting</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_validasi->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_transaksi']; ?></td>
                            <td><?php echo $row['id_akun']; ?></td>
                            <td><?php echo $row['tglclosing']; ?></td>
                            <td><?php echo $row['nilai']; ?></td>
                            <input type="hidden" name="id_akun_validasi[]" value="<?php echo $row['id_akun']; ?>">
                            <input type="hidden" name="nilai_validasi[]" value="<?php echo $row['nilai']; ?>">
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="action" value="Submit Validasi">Submit Validasi</button>
            </form>
            <?php elseif (isset($result_closing_validasi) && $result_closing_validasi->num_rows > 0): ?>
            <h2>Data Closing Validasi</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID Akun</th>
                        <th>Tanggal Closing</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_closing_validasi->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_akun']; ?></td>
                        <td><?php echo $row['tglclosing']; ?></td>
                        <td><?php echo $row['saldo']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php elseif ($display_results_validasi): ?>
            <p>Tidak ada data</p>
            <?php endif; ?>
        </div>
    </div>
    <h2>Closing</h2>
    <div class="container">
        <form method="POST" action="" class="filter-form">
            <label for="bulan">Pilih Bulan:</label>
            <select id="bulan" name="bulan" required>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo date("F", mktime(0, 0, 0, $i, 10)); ?></option>
                <?php endfor; ?>
            </select>
            <label for="tahun">Pilih Tahun:</label>
            <select id="tahun" name="tahun" required>
                <?php foreach ($years as $year): ?>
                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="action" value="Tampilkan">Tampilkan</button>
        </form>
        <div>
            <?php
            if (isset($closing_message)) {
                echo "<p>$closing_message</p>";
            }

            if ($display_results && isset($result) && $result->num_rows > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="bulan" value="<?php echo $bulan; ?>">
                <input type="hidden" name="tahun" value="<?php echo $tahun; ?>">
                <h2>Data Transaksi</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>ID Akun</th>
                            <th>Tanggal Closing</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_transaksi']; ?></td>
                            <td><?php echo $row['id_akun']; ?></td>
                            <td><?php echo $row['tglclosing']; ?></td>
                            <td><?php echo $row['nilai']; ?></td>
                            <input type="hidden" name="id_akun[]" value="<?php echo $row['id_akun']; ?>">
                            <input type="hidden" name="nilai[]" value="<?php echo $row['nilai']; ?>">
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="action" value="Submit">Submit</button>
            </form>
            <?php elseif (isset($result_closing) && $result_closing->num_rows > 0): ?>
            <h2>Data Closing</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID Akun</th>
                        <th>Tanggal Closing</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_closing->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_akun']; ?></td>
                        <td><?php echo $row['tglclosing']; ?></td>
                        <td><?php echo $row['saldo']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php elseif ($display_results): ?>
            <p>Tidak ada data</p>
            <?php endif; ?>

            <?php $conn->close(); ?>
        </div>
    </div>
</body>

</html>