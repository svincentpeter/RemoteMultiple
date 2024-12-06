<?php
session_start();
require_once 'dbconfig.php'; // File koneksi database Anda

if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Ganti index.php dengan halaman login Anda
    exit();
}

$nama_akun = $_SESSION['username'];

// Retrieve assets, liabilities, and equity from the database
$sql_assets = "SELECT a.id_akun, a.nama_akun, c.saldo
               FROM closing c
               JOIN akun a ON c.id_akun = a.id_akun
               WHERE a.aktiva_pasiva = 'A'";
$query_assets = $conn->query($sql_assets) or die(mysqli_error($conn));
$assets = [];
while ($row = mysqli_fetch_assoc($query_assets)) {
    $assets[] = $row;
}

$sql_liabilities_equity = "SELECT a.id_akun, a.nama_akun, c.saldo
                           FROM closing c
                           JOIN akun a ON c.id_akun = a.id_akun
                           WHERE a.aktiva_pasiva = 'P'";
$query_liabilities_equity = $conn->query($sql_liabilities_equity) or die(mysqli_error($conn));
$liabilities = [];
$equity = [];
while ($row = mysqli_fetch_assoc($query_liabilities_equity)) {
    if (strpos($row['nama_akun'], 'Modal') !== false) {
        $equity[] = $row;
    } else {
        $liabilities[] = $row;
    }
}

// Calculate totals
$total_assets = array_sum(array_column($assets, 'saldo'));
$total_liabilities = array_sum(array_column($liabilities, 'saldo'));
$total_equity = array_sum(array_column($equity, 'saldo'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closing Neraca</title>
    <style>
    body {
        font-family: 'Open Sans', sans-serif;
        margin: 0;
        padding: 0;
        background: #f4f4f4;
        font-weight: normal;
    }

    .container {
        max-width: 1100px;
        margin: 30px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }

    h2 {
        text-align: center;
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

    .section {
        margin-top: 40px;
    }

    .section h3 {
        background-color: #333;
        color: white;
        padding: 10px;
        border-radius: 4px;
        text-align: center;
    }

    .total {
        font-weight: bold;
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

    <div class="container">
        <h2>Data Closing Neraca</h2>
        <div class="section">
            <h3>Aset</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Akun</th>
                        <th>Nama Akun</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $asset): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($asset['id_akun']); ?></td>
                        <td><?php echo htmlspecialchars($asset['nama_akun']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($asset['saldo'], 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td colspan="2">Total Aset</td>
                        <td><?php echo htmlspecialchars(number_format($total_assets, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section">
            <h3>Kewajiban</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Akun</th>
                        <th>Nama Akun</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($liabilities as $liability): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($liability['id_akun']); ?></td>
                        <td><?php echo htmlspecialchars($liability['nama_akun']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($liability['saldo'], 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td colspan="2">Total Kewajiban</td>
                        <td><?php echo htmlspecialchars(number_format($total_liabilities, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section">
            <h3>Ekuitas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Akun</th>
                        <th>Nama Akun</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equity as $equities): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($equities['id_akun']); ?></td>
                        <td><?php echo htmlspecialchars($equities['nama_akun']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($equities['saldo'], 2)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td colspan="2">Total Ekuitas</td>
                        <td><?php echo htmlspecialchars(number_format($total_equity, 2)); ?></td>
                    </tr>
                    <tr class="total">
                        <td colspan="2">Total Kewajiban dan Ekuitas</td>
                        <td><?php echo htmlspecialchars(number_format($total_liabilities + $total_equity, 2)); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>