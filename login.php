<?php
/**
 * GV Florida Fleet Management System
 * Login Page
 */

require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check user credentials
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE username = ? AND status = 'Active'",
            [$username]
        );
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Update last login
            $db->update(
                'users',
                ['last_login' => date('Y-m-d H:i:s')],
                'user_id = ?',
                [$user['user_id']]
            );
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Set remember me cookie if requested
            if ($remember) {
                setcookie('remember_user', $username, time() + (86400 * 30), '/'); // 30 days
            }
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// Get remembered username if exists
$rememberedUser = $_COOKIE['remember_user'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://placehold.co/800x400?text=Modern+fleet+management+dashboard+interface+with+buses+and+transportation+icons') center/cover;
            opacity: 0.1;
        }
        
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .login-form {
            padding: 3rem 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .fleet-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .form-check {
            margin: 1.5rem 0;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .login-header {
                padding: 2rem 1rem;
            }
            
            .login-form {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="login-container">
                    <div class="login-header">
                        <div class="fleet-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <h1><?= APP_NAME ?></h1>
                        <p><?= COMPANY_NAME ?></p>
                    </div>
                    
                    <div class="login-form">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= $success ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-floating">
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Username"
                                       value="<?= htmlspecialchars($rememberedUser) ?>"
                                       required>
                                <label for="username">
                                    <i class="fas fa-user me-2"></i>Username
                                </label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Password"
                                       required>
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="remember" 
                                       name="remember"
                                       <?= $rememberedUser ? 'checked' : '' ?>>
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In to Dashboard
                            </button>
                        </form>
                        
                        <div class="demo-credentials">
                            <h6 class="mb-2">
                                <i class="fas fa-info-circle me-2"></i>Demo Credentials
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Admin:</strong><br>
                                    Username: admin<br>
                                    Password: password
                                </div>
                                <div class="col-6">
                                    <strong>Staff:</strong><br>
                                    Username: maria.rodriguez<br>
                                    Password: password
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus on username field
        document.getElementById('username').focus();
        
        // Show loading state on form submission
        document.querySelector('form').addEventListener('submit', function() {
            const btn = this.querySelector('.btn-login');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            btn.disabled = true;
        });
    </script>
</body>
</html>