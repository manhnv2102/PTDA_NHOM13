<?php
session_start();

// Kiểm tra nếu có email trong URL và email hợp lệ
if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $email = $_GET['email'];
} else {
    $_SESSION['error'] = "Yêu cầu không hợp lệ!";
    header("Location: forgot_password.php");
    exit;
}

// Xử lý khi người dùng cập nhật mật khẩu
if (isset($_POST['new_password'], $_POST['confirm_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới và mật khẩu xác nhận khớp nhau
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu mới và mật khẩu xác nhận không khớp!";
    } else {
        // Kết nối cơ sở dữ liệu và cập nhật mật khẩu
        $dsn = 'mysql:host=localhost;dbname=duan_project';
        $username = 'root';
        $password = '';
        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cập nhật mật khẩu vào cơ sở dữ liệu
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $new_password);  // Chưa mã hóa mật khẩu
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $_SESSION['success'] = "Mật khẩu đã được cập nhật thành công.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #6e7c7c, #4a5556);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .reset-container {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .btn-primary {
            margin-top: 15px;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="reset-container">
        <h2>Đặt lại mật khẩu</h2>
        <?php
        // Hiển thị thông báo lỗi nếu có
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }

        // Hiển thị thông báo thành công nếu có
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
            echo '<a href="login.php" class="btn btn-primary">Đăng nhập ngay</a>';
        } else {
        ?>
            <form method="POST" action="reset_password.php?email=<?php echo urlencode($email); ?>">
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" name="new_password" id="new_password" required placeholder="Nhập mật khẩu mới" class="form-control">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Nhập lại mật khẩu mới</label>
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Nhập lại mật khẩu mới" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
            </form>
        <?php
        }
        ?>
    </div>
</body>

</html>