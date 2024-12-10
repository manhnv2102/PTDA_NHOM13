<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "duan_project";

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy thông tin người dùng giảng viên
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id' AND role = 'giangvien'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Không tìm thấy thông tin người dùng.";
    exit();
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    if ($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['avatar']['tmp_name'];
        $file_name = $_FILES['avatar']['name'];
        $upload_dir = "uploads/avatars/";

        // Tạo tên file duy nhất
        $unique_name = uniqid() . '-' . basename($file_name);
        $file_path = $upload_dir . $unique_name;

        // Kiểm tra loại file
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($file_tmp_name, $file_path)) {
                // Cập nhật ảnh vào cơ sở dữ liệu
                $sql_avatar = "UPDATE users SET avatar = '$file_path' WHERE id = '$user_id'";
                if ($conn->query($sql_avatar) === TRUE) {
                    header("Location: home_giangvien.php");  // Chuyển hướng đúng trang
                    exit();
                } else {
                    $avatar_message = "Lỗi cập nhật ảnh đại diện: " . $conn->error;  // Debug SQL error
                }
            } else {
                $avatar_message = "Lỗi tải lên ảnh!";
            }
        } else {
            $avatar_message = "Chỉ hỗ trợ ảnh JPG, JPEG và PNG!";
        }
    } else {
        $avatar_message = "Chưa chọn ảnh hoặc có lỗi trong quá trình tải lên.";
    }

    // Hiển thị thông báo lỗi
    if (isset($avatar_message)) {
        echo $avatar_message;
    }
}
// Xử lý cập nhật thông tin cá nhân
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $khoa = $_POST['khoa'];
    $dia_chi = $_POST['dia_chi'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Cập nhật vào cơ sở dữ liệu
    $sql_update = "UPDATE users SET name = '$name', khoa = '$khoa', dia_chi = '$dia_chi', phone = '$phone', email = '$email' WHERE id = '$user_id'";

    if ($conn->query($sql_update) === TRUE) {
        header("Location: home_giangvien.php"); // Điều hướng lại trang sau khi cập nhật
        exit();
    } else {
        $update_message = "Lỗi cập nhật thông tin!";
    }
}


// Lấy danh sách đồ án của sinh viên
$sql_doan = "SELECT doan.id, doan.filename, doan.file_path, doan.status, doan.point, doan.comments, users.name, users.msv 
             FROM doan
             INNER JOIN users ON doan.user_id = users.id
             WHERE users.role = 'sinhvien'";

$result_doan = $conn->query($sql_doan);

// Xử lý cập nhật trạng thái đồ án
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doan_id']) && isset($_POST['status'])) {
    $doan_id = $_POST['doan_id'];
    $status = $_POST['status'];

    // Cập nhật trạng thái trong cơ sở dữ liệu
    $query = "UPDATE doan SET status = ? WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('si', $status, $doan_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật trạng thái']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi chuẩn bị câu lệnh']);
    }
}

// Xử lý cập nhật điểm và nhận xét
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doan_id']) && isset($_POST['point']) && isset($_POST['comments'])) {
    $doan_id = $_POST['doan_id'];
    $point = $_POST['point'];
    $comments = $_POST['comments'];

    // Kiểm tra đầu vào điểm (phải trong khoảng từ 0-10)
    if ($point < 0 || $point > 10) {
        echo json_encode(['status' => 'error', 'message' => 'Điểm phải nằm trong khoảng 0-10']);
        exit;
    }

    // Cập nhật điểm và nhận xét trong cơ sở dữ liệu
    $query = "UPDATE doan SET point = ?, comments = ? WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('dsi', $point, $comments, $doan_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật điểm và nhận xét thành công']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi chuẩn bị câu lệnh']);
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chính - Giảng viên</title>
    <!-- Tải Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Cải tiến toàn bộ giao diện */
        body {
            font-family: 'Arial', sans-serif;
            background-color: lightblue;
            color: #333;
            line-height: 1.6;
        }

        h1 {
            font-size: 2.5rem;
            color: #007bff;
            font-weight: 600;
            text-align: center;
            margin-top: 30px;
        }

        .container {
            margin-top: 50px;
        }

        img.img-thumbnail {
            border-radius: 50%;
            border: 5px solid #007bff;
            width: 200px;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        img.img-thumbnail:hover {
            transform: scale(1.1);
        }

        /* Cải thiện section thông tin cá nhân */
        .col-md-8 {
            padding-left: 30px;
            padding-right: 30px;
        }

        h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1rem;
            margin: 8px 0;
        }

        /* Cải thiện giao diện modal */
        .modal-dialog {
            max-width: 80%;
            margin: 30px auto;
        }

        /* Modal content */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        /* Modal header */
        .modal-header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        /* Modal body */
        .modal-body {
            padding: 30px;
            max-height: 500px;
            overflow-y: auto;
        }

        /* Modal footer */
        .modal-footer {
            padding: 15px 20px;
            text-align: center;
            background-color: #f1f1f1;
        }

        /* Button styling */
        button.btn-primary {
            background-color: #007bff;
            border: 2px solid #007bff;
            transition: all 0.3s ease;
        }

        button.btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        button.btn-danger {
            background-color: #dc3545;
            border: 2px solid #dc3545;
            padding: 10px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        button.btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        /* Thông báo */
        .alert {
            display: none;
            /* Ban đầu ẩn thông báo */
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            font-size: 1rem;
            color: white;
            transition: opacity 0.3s ease-in-out;
        }

        /* Thông báo thành công */
        .alert-success {
            background-color: #28a745;
        }

        /* Thông báo lỗi */
        .alert-danger {
            background-color: #dc3545;
        }

        /* Vị trí thông báo trong modal */
        .modal .alert {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            max-width: 400px;
            z-index: 999;
            text-align: center;
        }

        /* Cải thiện các trường select và input */
        .select-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .select-wrapper select,
        .select-wrapper input {
            width: 48%;
            padding: 8px 12px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease;
        }

        .select-wrapper select:focus,
        .select-wrapper input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Modal dropdown */
        select.form-control {
            background-color: #f1f1f1;
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 8px 12px;
        }

        select.form-control:focus {
            border-color: #007bff;
        }

        /* Chỉnh sửa hover và focus */
        button.btn:hover,
        .btn-danger:hover,
        .btn-primary:hover {
            transform: translateY(-2px);
        }

        button.btn:active,
        .btn-danger:active,
        .btn-primary:active {
            transform: translateY(1px);
        }
    </style>
</head>


<body>
    <div class="container">
        <h1>Chào mừng, <?php echo $user['name']; ?>!</h1>

        <div class="row">
            <div class="col-md-4">
                <!-- Hiển thị ảnh đại diện, nếu không có thì hiển thị ảnh mặc định -->
                <img src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'uploads/avatars/default_avatar.png'; ?>" alt="Avatar" class="img-thumbnail" style="width: 200px; height: 200px;">
                <br><br>
            </div>
            <div class="col-md-8">
                <h2>Thông tin cá nhân:</h2>
                <p><strong>Mã giảng viên:</strong> <?php echo $user['msv']; ?></p>
                <p><strong>Khoa:</strong> <?php echo $user['khoa']; ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo $user['dia_chi']; ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo $user['phone']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>

                <button class="btn btn-primary" data-toggle="modal" data-target="#updateModal">Cập nhật thông tin</button>

                <!-- Modal Cập nhật thông tin -->
                <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateModalLabel">Cập nhật thông tin cá nhân</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">

                                <div id="modal-alert-box" class="alert" style="display:none;">
                                </div>

                                <form action="home_giangvien.php" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="name">Họ tên:</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="khoa">Khoa:</label>
                                        <input type="text" class="form-control" id="khoa" name="khoa" value="<?php echo $user['khoa']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="dia_chi">Địa chỉ:</label>
                                        <input type="text" class="form-control" id="dia_chi" name="dia_chi" value="<?php echo $user['dia_chi']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Số điện thoại:</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email:</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="avatar">Chọn ảnh đại diện mới:</label>
                                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <button class="btn btn-info" data-toggle="modal" data-target="#viewProjectsModal">DANH SÁCH ĐỒ ÁN</button>
        <a href="logout.php" class="btn btn-danger">Đăng xuất</a>

        <!-- Modal Danh Sách Đồ Án -->
        <div class="modal fade" id="viewProjectsModal" tabindex="-1" role="dialog" aria-labelledby="viewProjectsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewProjectsModalLabel">Danh sách đồ án</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Thông báo trong modal -->
                        <div id="modal-alert-box" class="alert" style="display:none;">
                            <!-- Thông báo sẽ hiển thị ở đây -->
                        </div>

                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Họ và tên</th>
                                    <th>Mã đồ án</th>
                                    <th>File</th>
                                    <th>Trạng thái</th>
                                    <th>Điểm</th>
                                    <th>Nhận xét</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result_doan->num_rows > 0) {
                                    while ($doan = $result_doan->fetch_assoc()) {
                                        echo "<tr data-doan-id='" . $doan['id'] . "'>";
                                        echo "<td>" . $doan['name'] . "</td>";
                                        echo "<td>" . $doan['id'] . "</td>";
                                        echo "<td><a href='" . $doan['file_path'] . "' target='_blank'>" . $doan['filename'] . "</a></td>";

                                        // Trạng thái (Dropdown)
                                        echo "<td>
                                            <select class='form-control update-status' data-doan-id='" . $doan['id'] . "'>
                                                <option value='Chờ duyệt' " . ($doan['status'] == 'Chờ duyệt' ? 'selected' : '') . ">Chờ duyệt</option>
                                                <option value='Đã duyệt' " . ($doan['status'] == 'Đã duyệt' ? 'selected' : '') . ">Đã duyệt</option>
                                            </select>
                                        </td>";

                                        // Điểm (Input number)
                                        echo "<td><input type='number' class='form-control update-point' data-doan-id='" . $doan['id'] . "' value='" . ($doan['point'] ? $doan['point'] : '') . "' min='0' max='10'></td>";

                                        // Nhận xét (Textarea)
                                        echo "<td><textarea class='form-control update-comments' data-doan-id='" . $doan['id'] . "'>" . ($doan['comments'] ? $doan['comments'] : '') . "</textarea></td>";

                                        // Thao tác (Cập nhật)
                                        echo "<td>
                                            <button class='btn btn-warning update-doan' data-doan-id='" . $doan['id'] . "'>Cập nhật</button>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>Chưa có đồ án nào.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Hiển thị thông báo trong modal
            function showModalAlert(message, type) {
                var alertBox = $('#modal-alert-box');
                alertBox.removeClass('alert-success alert-danger'); // Xóa các lớp trước đó
                alertBox.addClass(type === 'success' ? 'alert-success' : 'alert-danger'); // Thêm lớp mới
                alertBox.text(message);
                alertBox.show();

                // Ẩn thông báo sau 5 giây
                setTimeout(function() {
                    alertBox.hide();
                }, 5000);
            }

            // Khi trạng thái thay đổi
            $('.update-status').on('change', function() {
                var doan_id = $(this).data('doan-id');
                var status = $(this).val();

                $.ajax({
                    url: 'home_giangvien.php',
                    type: 'POST',
                    data: {
                        doan_id: doan_id,
                        status: status
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'success') {
                            showModalAlert('Cập nhật trạng thái thành công!', 'success');
                        } else {
                            showModalAlert('Lỗi khi cập nhật trạng thái', 'error');
                        }
                    },
                    error: function() {
                        showModalAlert('Lỗi kết nối đến server', 'error');
                    }
                });
            });

            // Cập nhật điểm và nhận xét
            $('.update-doan').on('click', function() {
                var doan_id = $(this).data('doan-id');
                var point = $(this).closest('tr').find('.update-point').val();
                var comments = $(this).closest('tr').find('.update-comments').val();

                if (point < 0 || point > 10) {
                    alert('Điểm phải nằm trong khoảng 0-10');
                    return;
                }

                $.ajax({
                    url: 'home_giangvien.php',
                    type: 'POST',
                    data: {
                        doan_id: doan_id,
                        point: point,
                        comments: comments
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'success') {
                            showModalAlert('Cập nhật điểm và nhận xét thành công', 'success');
                        } else {
                            showModalAlert('Lỗi cập nhật đồ án', 'error');
                        }
                    },
                    error: function() {
                        showModalAlert('Lỗi kết nối đến server', 'error');
                    }
                });
            });
        });
    </script>

</body>


</html>