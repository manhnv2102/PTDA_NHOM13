<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pzjw8f+ua7Kw1TIq0Sy2zB0M67v0Rgvv4mw1cM5XZgCp3zA0zdGeZgpoF5M24gFe" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #6e7c7c, #4a5556);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            background-image: url(https://toplist.vn/images/800px/dai-hoc-cong-nghiep-ha-noi-250485.jpg);
        }

        /* Container đăng nhập */
        .login-container {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .login-container h2 {
            text-align: center;
            font-family: 'Roboto', sans-serif;
            color: #4b4b4b;
            margin-bottom: 30px;
        }

        .form-group label {
            font-weight: 600;
            color: #4b4b4b;
        }

        .form-group input {
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* đăng nhập */
        .btn-login {
            background-color: #007bff;
            color: white;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .alert-danger {
            color: white;
            background-color: #d9534f;
            border-color: #c9302c;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: all 0.3s ease;
        }

        .alert-danger:hover {
            background-color: #c9302c;
            border-color: #ac2925;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Đăng Nhập</h2>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Tên người dùng</label>
                <input type="text" name="username" id="username" required placeholder="Nhập tên người dùng">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password" required placeholder="Nhập mật khẩu">
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
        <div class="footer">
            <p>Quên mật khẩu? <a href="forgot_password.php">Khôi phục ngay</a></p>
        </div>

    </div>

    <!-- Liên kết tới jQuery và Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zybcpAd6L+X+Hp7Xq7b4/l4tn+6AWLvFlZW+Cp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0Sy2zB0M67v0Rgvv4mw1cM5XZgCp3zA0zdGeZgpoF5M24gFe" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0Sy2zB0M67v0Rgvv4mw1cM5XZgCp3zA0zdGeZgpoF5M24gFe" crossorigin="anonymous"></script>

</body>

</html>