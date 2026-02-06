<?php
session_start();
require '../Config/config.php';
require '../Config/common.php';

// Check if company_id is passed (from registration flow)
if(!isset($_GET['company_id'])) {
    header("Location: register_company.php");
    exit;
}

$company_id = (int)$_GET['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password2 = trim($_POST['password2']);

    $errors = [];
    if(empty($name)) $errors[] = "Admin Name is required";
    if(empty($email)) $errors[] = "Admin Email is required";
    if(empty($password)) $errors[] = "Password is required";
    if($password !== $password2) $errors[] = "Passwords do not match";

    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,company_id) 
                               VALUES (:name,:email,:password,1,:company_id)");
        $stmt->execute([
            ':name'=>$name,
            ':email'=>$email,
            ':password'=>$hashed_password,
            ':company_id'=>$company_id
        ]);

        $message = "✅ Your company and admin user have been created. Please wait for approval before logging in.";
    } else {
        $errorText = implode("\\n",$errors);
        echo "<script>swal('Error!', '$errorText', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin User - ProTech Inventory</title>
    <link href="bootstrap-4.0.0-dist/css/bootstrap.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #f3f4f6;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }
        .container-box {
            display: flex;
            max-width: 800px;
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
            max-width: 450px;
        }
        .left-side h4 {
            text-align: center;
            font-weight: bold;
            color: rgba(105, 173, 31, 1);
            margin-bottom: 1.5rem;
        }
        .form-label { font-weight: 500; }
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
            background: rgba(105, 173, 31, 1);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            width: 100%;
            transition: background 0.2s ease-in-out;
        }
        .btn-login:hover { background: rgba(85, 145, 25, 1); }

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
        .right-side img { max-width: 250px; height: auto; margin-bottom: 1rem; }
        .right-side p { max-width: 280px; color: #374151; font-size: 0.9rem; margin-bottom: 1rem; }
        .feature-list { list-style: none; padding: 0; margin: 0.8rem 0; text-align: left; }
        .feature-list li { margin-bottom: 0.4rem; font-weight: 500; color: #1f2937; font-size: 0.85rem; }

        @media (max-width: 768px) {
            .container-box { flex-direction: column; max-height: none; }
            .right-side { padding: 1rem; }
            .right-side img { max-width: 120px; }
        }
    </style>
</head>
<body>
    <div class="mt-4" style="max-width:900px; margin:auto;">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center" style="margin-bottom:1.5rem;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="container-box" style="max-width:900px; margin:auto; display:flex; height:90vh; background:white; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1);">

    <!-- Left Side: Form -->
    <div class="left-side" style="flex:1; padding:2rem; display:flex; flex-direction:column; justify-content:center;">

        <h4 style="text-align:center; color: rgba(105, 173, 31, 1); margin-bottom:1.5rem;">👤 Create Admin User</h4>

        <!-- Alert message -->

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Name <span style="color:red">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email <span style="color:red">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password <span style="color:red">*</span></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password <span style="color:red">*</span></label>
                <input type="password" name="password2" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-login mt-3" style="width:100%;">Create Admin</button>
        </form>
    </div>

    <!-- Right Side: Image -->
    <div class="right-side" style="flex:1; background:linear-gradient(135deg, rgba(105,173,31,0.15), rgba(105,173,31,0.05)); display:flex; flex-direction:column; justify-content:center; align-items:center; padding:1rem;">
        <img src="../images/admin.png" alt="Admin Illustration" style="max-width:90%; max-height:80%; margin-bottom:1rem;">
        <p style="text-align:center; max-width:250px;">This admin will manage your company after approval. Only one admin is required to start.</p>
        <ul class="feature-list" style="list-style:none; padding:0; text-align:left;">
            <li>✅ Secure admin account</li>
            <li>✅ Assign roles later</li>
            <li>✅ Linked with your company</li>
        </ul>
    </div>

</div>
</body>

</html>