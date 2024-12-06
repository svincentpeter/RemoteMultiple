<?php
session_start();
// Check if the session exists, otherwise redirect to login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

require_once 'dbconfig.php'; // Pastikan nama file dan lokasi sudah benar

// Initialize default dates
$start_date = '';
$end_date = '';

// Check if form is submitted
if (isset($_POST['submit'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

// Fetch data from transaksi table and detail_transaksi table with date filter
$query = "SELECT transaksi.id_transaksi, transaksi.tgl_transaksi, transaksi.nama_transaksi, SUM(detail_transaksi.nilai) as total_nilai
          FROM transaksi
          INNER JOIN detail_transaksi ON transaksi.id_transaksi = detail_transaksi.id_transaksi
          WHERE transaksi.tgl_transaksi BETWEEN ? AND ?
          AND transaksi.posting = 0 AND transaksi.hapus = 0
          GROUP BY transaksi.id_transaksi, transaksi.tgl_transaksi, transaksi.nama_transaksi
          ORDER BY transaksi.id_transaksi";

$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posting</title>
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
    }

    table {
        width: 100%;
        border-collapse: collapse;
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
        position: relative;
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

    .filter-form {
        text-align: center;
        margin-bottom: 20px;
    }

    .filter-form input {
        padding: 8px 12px;
        font-size: 16px;
        margin: 5px;
    }

    .filter-form input[type="submit"] {
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .filter-form input[type="submit"]:hover {
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
    </div>
    <a class="logout-btn" href="logout.php">Logout</a>
    <h2>Posting</h2>
    <div class="container">
        <div class="filter-form">
            <form method="post" action="">
                <label for="start_date">Mulai Tanggal:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                <label for="end_date">Sampai Tanggal:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                <input type="submit" name="submit" value="Filter">
            </form>
        </div>
        <form method="post" action="">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID Transaksi</th>
                        <th>Tanggal Transaksi</th>
                        <th>Nama Transaksi</th>
                        <th>Nominal</th>
                        <th>Action</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $row_no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row_no . "</td>";
                            echo "<td>" . $row["id_transaksi"] . "</td>";
                            echo "<td>" . $row["tgl_transaksi"] . "</td>";
                            echo "<td>" . $row["nama_transaksi"] . "</td>";
                            echo "<td>Rp. " . number_format($row["total_nilai"], 0, ',', '.') . "</td>";
                            echo "<td class='action-buttons'>";
                            echo "<button type='button' class='edit-btn' onclick='window.location.href=\"edit.php?id=" . $row["id_transaksi"] . "\"'>Edit</button>";
                            echo "<button type='button' class='delete-btn' onclick='window.location.href=\"delete.php?id=" . $row["id_transaksi"] . "\"'>Delete</button>";
                            echo "</td>";
                            echo "<td><input type='checkbox' name='options[]' value='" . $row["id_transaksi"] . "'></td>";
                            echo "</tr>";
                            $row_no++;
                        }
                    } else {
                        echo "<tr><td colspan='7'>Tidak ada data</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php if ($result->num_rows > 0) : ?>
            <button type="submit">Submit</button>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>