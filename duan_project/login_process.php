<?php
include('db_connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'sinhvien') {
                header("Location: home_sinhvien.php");
                exit();
            } else {
                header("Location: home_giangvien.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Mật khẩu không chính xác!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Không tìm thấy người dùng!";
        header("Location: login.php");
        exit();
    }
}
