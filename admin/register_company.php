<?php
session_start();
require '../config/config.php';
require '../config/common.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $street_name = trim($_POST['street_name']);
    $building_no = trim($_POST['building_no']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $bank_account = trim($_POST['bank_account']);
    $logo = $_POST['logo'] ?? ''; // later you can add file upload

    // Simple validation
    $errors = [];
    if (empty($name)) $errors[] = "Company Name is required";
    if (empty($email)) $errors[] = "Email is required";

    if (empty($errors)) {
        // Insert company, set status = 0 (waiting approval)
        $stmt = $pdo->prepare("INSERT INTO company (name, street_name, building_no, phone, email, city, country, bank_account, logo, status) 
                               VALUES (:name, :street_name, :building_no, :phone, :email, :city, :country, :bank_account, :logo, 'pending')");
        $result = $stmt->execute([
            ':name' => $name,
            ':street_name' => $street_name,
            ':building_no' => $building_no,
            ':phone' => $phone,
            ':email' => $email,
            ':city' => $city,
            ':country' => $country,
            ':bank_account' => $bank_account,
            ':logo' => $logo
        ]);

        if ($result) {
             // Store company_id in session
            $_SESSION['new_company_id'] = $pdo->lastInsertId();

            // Redirect to create admin user page
            header("Location: create_admin_user.php?company_id=" . $_SESSION['new_company_id']);
            exit;
        }
    } else {
         $errorText = implode("\\n", $errors);
        echo "<script>
            swal('Error!', '$errorText', 'error');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Company - ProTech Inventory</title>
    <link href="bootstrap-4.0.0-dist/css/bootstrap.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    body {
        margin:0;
        font-family:"Segoe UI", Tahoma, sans-serif;
        background:#f3f4f6;
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
    }
    .form-control {
        border-radius:10px;
        border:1px solid #d1d5db;
        padding:0.6rem;
    }
    .form-control:focus {
        border-color: rgba(105,173,31,1);
        box-shadow:0 0 0 3px rgba(105,173,31,0.25);
    }
    .btn-login {
        background: rgba(105, 173, 31, 1);
        color:white;
        font-weight:600;
        border:none;
        border-radius:12px;
        padding:0.7rem;
        transition: background 0.2s ease-in-out;
    }
    .btn-login:hover { background: rgba(85,145,25,1); }
    @media (max-width: 992px) {
        .container-box { flex-direction:column; height:auto; max-width:90%; }
        .right-side { margin-top:1.5rem; }
    }
</style>
</head>
<body>
<div class="container-box" style="max-width:900px; margin:auto; display:flex; height:90vh; background:white; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
    
    <!-- Left Side: Form -->
    <div class="left-side" style="flex:1; padding:2rem; display:flex; flex-direction:column; justify-content:center;">
        <h4 style="text-align:center; color: rgba(105, 173, 31, 1); margin-bottom:1.5rem;">🏢 Register Your Company</h4>

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company Name <span style="color:red">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span style="color:red">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Street Name</label>
                    <input type="text" name="street_name" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Building No</label>
                    <input type="text" name="building_no" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Account</label>
                    <input type="text" name="bank_account" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-login mt-2" style="width:100%;">Register Company</button>
        </form>

        <!-- <p class="text-center mt-3">
            <a href="login.php" class="btn btn-login" style="background: rgba(0, 150, 136, 1); width:100%;">🔑 Back to Login</a>
        </p> -->
    </div>

    <!-- Right Side: Image -->
    <div class="right-side" style="flex:1; background:linear-gradient(135deg, rgba(105,173,31,0.15), rgba(105,173,31,0.05)); display:flex; justify-content:center; align-items:center;">
        <img src="../images/inventory2.png" alt="Inventory Illustration" style="max-width:90%; max-height:80%;">
    </div>
</div>
</body>
</html>
