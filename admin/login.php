<?php
session_start();
require '../Config/config.php';
require '../Config/common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and Password are required!";
        header("Location: login.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';

        // --- Load permissions for this user ---
        $permStmt = $pdo->prepare("
            SELECT p.name 
            FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            JOIN roles r ON r.id = rp.role_id
            WHERE r.id = :role_id
        ");
        $permStmt->execute([':role_id' => $user['role']]);
        $permissions = $permStmt->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION['permissions'] = $permissions; // store permissions in session

        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: login.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - ProTech Inventory</title>
    <link href="bootstrap-4.0.0-dist/css/bootstrap.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            background: #f3f4f6;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .container-box {
            display: flex;
            max-width: 400px;
            width: 100%;
            margin: auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .left-side {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }
	
	.left-side img{
       	    margin-left:auto;
            margin-right:auto;
	}

        .left-side h4 {
            text-align: center;
            font-weight: bold;
            color: rgba(12, 77, 73, 1);
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
        }

        .form-control:focus {
            border-color: rgba(105, 173, 31, 1);
            box-shadow: 0 0 0 3px rgba(105, 173, 31, 0.25);
        }

        .btn-login {
            background: rgba(1, 134, 141, 1);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            width: 100%;
            transition: background 0.2s ease-in-out;
        }

        .btn-login:hover {
            background: rgba(12, 77, 73, 1);
        }

        /* Right Side (Welcome) */
        .right-side {
            flex: 1;
            background: linear-gradient(135deg, rgba(105, 173, 31, 0.15), rgba(105, 173, 31, 0.05));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 1.5rem;
        }

        .right-side img {
            max-width: 250px;
            /* 👈 reduced from 200px */
            height: auto;
            margin-bottom: 1rem;
        }

        .right-side h2 {
            color: rgba(105, 173, 31, 1);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .right-side p {
            max-width: 280px;
            color: #374151;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0.8rem 0;
            text-align: left;
        }

        .feature-list li {
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: #1f2937;
            font-size: 0.85rem;
        }

        blockquote {
            font-size: 0.85rem;
            font-style: italic;
            color: #6b7280;
            margin-top: 0.8rem;
            border-left: 3px solid rgba(105, 173, 31, 1);
            padding-left: 0.5rem;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .container-box {
                flex-direction: column;
                max-height: none;
                /* allow full height on mobile */
            }

            .right-side {
                padding: 1rem;
            }

            .right-side img {
                max-width: 120px;
            }
        }
    </style>
</head>

<body>

    <div class="container-box">
        <!-- Left Side (Login Form) -->
        <div class="left-side">
	    <img src="../uploads/zlmnlogo.jpg" width="200" height="200">
            <h4>Business Control System</h4>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger text-center">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-login mt-3">Login</button>
            </form>
        </div>


    </div>

</body>

</html>