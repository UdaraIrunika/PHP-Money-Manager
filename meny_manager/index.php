<?php
require_once __DIR__ . '/functions.php';
if (is_logged_in()) {
    header('Location: dashboard.php'); exit;
}
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $err = 'Provide email and password.';
    } else {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php'); exit;
        } else {
            $err = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login - Money Manager</title>
    <style>
        /* Same CSS as index.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 400px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .nav {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .container > h2 {
            text-align: center;
            padding: 30px 20px 20px;
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 500;
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 0 20px 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .success-message {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 0 20px 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        form {
            padding: 0 20px 20px;
        }

        .form-row {
            margin-bottom: 20px;
        }

        .col {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 500;
            margin-bottom: 6px;
            color: #4a5568;
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        .small {
            font-size: 0.8rem;
            color: #718096;
            text-align: center;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            margin-top: 20px;
            line-height: 1.5;
            background: #f8fafc;
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                margin: 20px auto;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .header h1 {
                font-size: 1.6rem;
            }
            
            form {
                padding: 0 15px 15px;
            }
            
            .nav {
                position: static;
                margin-top: 10px;
            }
            
            .nav a {
                display: inline-block;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h1>Money Manager</h1><div class="nav"><a href="register.php">Register</a></div></div>
    <h2>Login</h2>
    <?php if ($err): ?><div style="color:#b91c1c;margin-bottom:10px"><?=h($err)?></div><?php endif; ?>
    <form method="post">
        <div class="form-row"><div class="col"><label>Email</label><input type="text" name="email"></div></div>
        <div class="form-row"><div class="col"><label>Password</label><input type="password" name="password"></div></div>
        <div><button type="submit">Login</button></div>
    </form>
    <p class="small">Demo: register a new user or import sample data via `sql/schema.sql` and set a password manually.</p>
</div>
</body>
</html>