<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;

if (isset($_SESSION['user']) && !isset($_GET['logout'])) {
    header("Location: ../views/index.php");
    exit();
}

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope('email');
$client->addScope('profile');

// Tạo CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Biến để lưu trữ thông tin chuyển hướng
$redirect_url = '';
$show_success = false;

// Xử lý đăng nhập Google
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (!isset($token['error']) && $client->getAccessToken()) {
            $client->setAccessToken($token['access_token']);
            $google_service = new Google_Service_Oauth2($client);
            $user_info = $google_service->userinfo->get();

            $email = filter_var($user_info->email, FILTER_SANITIZE_EMAIL);
            $username = htmlspecialchars($user_info->name, ENT_QUOTES, 'UTF-8');

            // Kiểm tra người dùng với email
            $stmt = $conn->prepare("SELECT id, email, username, verified, login_type FROM users WHERE email = ?");
            if ($stmt === false) {
                throw new Exception("Lỗi chuẩn bị truy vấn: " . $conn->error);
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Nếu tài khoản tồn tại nhưng login_type không phải google
            if ($user && $user['login_type'] !== 'google') {
                header("Location: login.php?action=login&error=1&error_type=account-exists");
                exit();
            }

            // Nếu người dùng không tồn tại, tạo mới
            if (!$user) {
                $random_password = bin2hex(random_bytes(16));
                $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
                $verified = 1;
                $stmt = $conn->prepare("INSERT INTO users (email, username, password, login_type, verified) VALUES (?, ?, ?, 'google', ?)");
                if ($stmt === false) {
                    throw new Exception("Lỗi chuẩn bị truy vấn: " . $conn->error);
                }
                $stmt->bind_param("sssi", $email, $username, $hashed_password, $verified);
                $stmt->execute();
                $user_id = $conn->insert_id;
            } else {
                // Kiểm tra verified
                if ($user['verified'] == 0) {
                    header("Location: login.php?action=login&error=1&error_type=not-verified");
                    exit();
                }
                $user_id = $user['id'];
            }

            // Lưu thông tin người dùng vào session
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user_id,
                'email' => $email,
                'username' => $username,
                'login_type' => 'google'
            ];

            // Chuẩn bị hiển thị thông báo thành công và chuyển hướng
            $show_success = true;
            $redirect_url = '../views/index.php';
        } else {
            $error = "Đăng nhập Google thất bại: " . ($token['error_description'] ?? 'Lỗi không xác định');
            error_log($error);
            header("Location: login.php?action=login&error=1&error_type=default");
            exit();
        }
    } catch (Exception $e) {
        $error = "Lỗi đăng nhập Google: " . $e->getMessage();
        error_log($error);
        header("Location: login.php?action=login&error=1&error_type=default");
        exit();
    }
}

// Xử lý đăng nhập email/mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php?action=login&error=1&error_type=default");
        exit();
    } else {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email) {
            header("Location: login.php?action=login&error=1&error_type=invalid-credentials");
            exit();
        } elseif (strlen($password) < 8) {
            header("Location: login.php?action=login&error=1&error_type=invalid-credentials");
            exit();
        } else {
            try {
                $stmt = $conn->prepare("SELECT id, email, username, password, login_type, verified FROM users WHERE email = ?");
                if ($stmt === false) {
                    throw new Exception("Lỗi chuẩn bị truy vấn: " . $conn->error);
                }
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && ($user['login_type'] === 'email' || $user['login_type'] === 'admin')) {
                    if ($user['verified'] == 0) {
                        header("Location: login.php?action=login&error=1&error_type=not-verified");
                        exit();
                    } elseif (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['user'] = [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'username' => $user['username'],
                            'login_type' => $user['login_type']
                        ];
                        error_log("Đăng nhập email thành công: " . print_r($_SESSION['user'], true));
                        // Chuẩn bị hiển thị thông báo thành công và chuyển hướng
                        $show_success = true;
                        $redirect_url = $user['login_type'] === 'admin' ? '../views/admin.php' : '../views/index.php';
                    } else {
                        header("Location: login.php?action=login&error=1&error_type=invalid-credentials");
                        exit();
                    }
                } else {
                    header("Location: login.php?action=login&error=1&error_type=account-not-found");
                    exit();
                }
            } catch (Exception $e) {
                $error = "Lỗi đăng nhập: " . $e->getMessage();
                error_log($error);
                header("Location: login.php?action=login&error=1&error_type=default");
                exit();
            }
        }
    }
}

// Xử lý đăng xuất
if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    header("Location: login.php");
    exit();
}

// Tạo URL đăng nhập Google
$google_login_url = $client->createAuthUrl();

// Nhãn để nhảy đến phần render giao diện
render_page:
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TD Touris - Đăng Nhập</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- File CSS tùy chỉnh -->
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="background-login position-relative min-vh-100 d-flex justify-content-center align-items-center">
        <img src="../public/images/backgroudlogin.png" alt="background-login" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover">
        <div class="login-form bg-white p-4 rounded shadow w-100" style="max-width: 400px;">
            <h1 class="text-center mb-4">Đăng Nhập</h1>
            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="txtb mb-3">
                    <input id="email" type="email" name="email" class="form-control" required placeholder="Nhập email" autocomplete="email">
                </div>
                <div class="txtb mb-3">
                    <input id="password" type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu" autocomplete="current-password">
                </div>
                <input type="submit" id="logbtn" class="logbtn btn btn-primary w-100" value="Đăng Nhập">
                <div class="bottom-text text-center mt-3">
                    Bạn chưa có tài khoản? <a href="register.php" class="text-primary text-decoration-none">Đăng Ký</a>
                </div>
            </form>

            <div class="social-login mt-4">
                <div class="social-divider d-flex align-items-center mb-3">
                    <hr class="flex-grow-1 border-secondary">
                    <span class="divider-text mx-2 text-muted">Hoặc đăng nhập bằng</span>
                    <hr class="flex-grow-1 border-secondary">
                </div>
                <div class="social-buttons text-center">
                    <a href="<?php echo htmlspecialchars($google_login_url, ENT_QUOTES, 'UTF-8'); ?>" class="google-login btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 mb-2">
                        <svg viewBox="0 0 24 24" class="w-5 h-5" style="width: 20px; height: 20px;">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.20-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.60 3.30-4.53 6.16-4.53z" fill="#EA4335"></path>
                            <path d="M1 1h22v22H1z" fill="none"></path>
                        </svg>
                        Đăng nhập với Google
                    </a>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="login.php?logout=true" class="logout text-danger text-decoration-none d-inline-block mt-2">Đăng Xuất</a>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <p id="user-info" class="text-center mt-3">Xin chào, <?php echo htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($_SESSION['user']['email'], ENT_QUOTES, 'UTF-8') . ")"; ?></p>
                <?php else: ?>
                    <p id="user-info" class="text-center mt-3"></p>
                <?php endif; ?>
            </div>      
        </div>
    </div>

    <!-- SweetAlert2 Messages -->
    <?php
    $action = $_GET['action'] ?? '';
    $success = $_GET['success'] ?? '';
    $error = $_GET['error'] ?? '';
    $error_type = $_GET['error_type'] ?? 'default';
    $success_type = $_GET['success_type'] ?? 'default';

    $messages = [
    'login' => [
        'success' => [
            'default' => [
                'icon' => 'success',
                'title' => 'Đăng nhập thành công!',
                'text' => 'Chào mừng bạn quay trở lại.'
            ]
        ],
        'error' => [
            'invalid-credentials' => [
                'icon' => 'error',
                'title' => 'Sai thông tin!',
                'text' => 'Email hoặc mật khẩu không đúng.'
            ],
            'account-not-found' => [
                'icon' => 'error',
                'title' => 'Không tìm thấy tài khoản!',
                'text' => 'Tài khoản không tồn tại. Vui lòng đăng ký.'
            ],
            'not-verified' => [
                'icon' => 'warning',
                'title' => 'Chưa xác thực!',
                'text' => 'Vui lòng xác thực email trước khi đăng nhập.'
            ],
            'account-exists' => [
                'icon' => 'error',
                'title' => 'Tài khoản đã tồn tại!',
                'text' => 'Email này đã được đăng ký bằng phương thức khác. Vui lòng sử dụng phương thức đăng nhập email.'
            ],
            'default' => [
                'icon' => 'error',
                'title' => 'Lỗi đăng nhập!',
                'text' => 'Có lỗi xảy ra. Vui lòng thử lại.'
            ]
        ]
    ]  
];

    // Hiển thị thông báo
    if ($show_success) {
        $msg = $messages['login']['success']['default'];
        echo "<script>
        Swal.fire({
            icon: '{$msg['icon']}',
            title: '{$msg['title']}',
            text: '{$msg['text']}',
            timer: 2000,
            showConfirmButton: true
        }).then(() => {
            window.location.href = '$redirect_url';
        });
        </script>";
    } elseif (isset($messages[$action])) {
        if ($success === '1') {
            $msg = $messages[$action]['success'][$success_type] ?? $messages[$action]['success']['default'];
        } elseif ($error === '1') {
            $msg = $messages[$action]['error'][$error_type] ?? $messages[$action]['error']['default'];
        }

        if (isset($msg)) {
            echo "<script>
            Swal.fire({
                icon: '{$msg['icon']}',
                title: '{$msg['title']}',
                text: '{$msg['text']}',
                timer: 2000,
                showConfirmButton: true
            });
            </script>";
        }
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>