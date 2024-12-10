<?php
session_start();

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Kiểm tra định dạng email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ!";
        header("Location: forgot_password.php");
        exit;
    }

    // Kiểm tra email trong cơ sở dữ liệu
    $dsn = 'mysql:host=localhost;dbname=duan_project';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Chuyển đến trang reset_password.php với email
            header("Location: reset_password.php?email=" . urlencode($email));
            exit;
        } else {
            $_SESSION['error'] = "Email không tồn tại trong hệ thống!";
            header("Location: forgot_password.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage();
        header("Location: forgot_password.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #6e7c7c, #4a5556);
            /* Nền tương tự như trang đăng nhập */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        /* Container khôi phục mật khẩu */
        .reset-container {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .reset-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .reset-container h2 {
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

        .btn-primary {
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

        .btn-primary:hover {
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
    <div class="reset-container">
        <h2>Khôi phục mật khẩu</h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Nhập email của bạn</label>
                <input type="email" name="email" id="email" required placeholder="Nhập email của bạn" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Tiến hành khôi phục mật khẩu</button>
        </form>

        <div class="footer">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
        </div>
    </div>
</body>

</html>