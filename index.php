<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
    /* CSS styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        /* Mengubah height menjadi min-height agar footer selalu di bagian bawah */
        margin: 0;
    }

    .container {
        background-color: #ffffff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 300px;
        text-align: center;
    }

    h2 {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin: 10px 0 5px;
    }

    input[type="username"],
    input[type="password"] {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    input[type="submit"] {
        background-color: #28a745;
        color: #ffffff;
        border: none;
        padding: 10px;
        border-radius: 3px;
        cursor: pointer;
        width: 100%;
    }

    input[type="submit"]:hover {
        background-color: #218838;
    }

    p {
        margin-top: 20px;
    }

    a {
        color: #007BFF;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    footer {
        margin-top: 20px;
        font-size: 12px;
        color: #666;
        position: absolute;
        /* Membuat footer tetap di bawah */
        bottom: 10px;
        /* Jarak dari bawah */
        width: 100%;
        text-align: center;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <form action="login_process.php" method="post">
            <label for="username">Username</label>
            <input type="username" id="username" name="username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
    <!-- Footer -->
    <footer>
        <p> © Program by Devan Ramiro Putra, Tjen - 22.N1.0011</p>
    </footer>
</body>

</html>