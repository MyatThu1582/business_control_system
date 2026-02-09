<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
require 'header.php';

$user_id = $_SESSION['user_id'];

// Fetch company data for current user
$stmt = $pdo->prepare("SELECT * FROM company LIMIT 1");
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

$id = $company['id'] ?? null;
// Handle form submission
if ($_POST) {
    $name = trim($_POST['name']);
    $street_name = trim($_POST['street_name']);
    $building_no = trim($_POST['building_no']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $bank_account = trim($_POST['bank_account']);
    $logoName = $company['logo'] ?? '';
    // Handle logo upload
    if (!empty($_FILES['logo']['name'])) {
        $image = $_FILES['logo']['name'];
        $file = 'uploads/' . $image;
        $imageType = pathinfo($file, PATHINFO_EXTENSION);

        if ($imageType == 'jpg' || $imageType == 'jpeg' || $imageType == 'png') {
            move_uploaded_file($_FILES['logo']['tmp_name'], $file);
            $logoName = $image;
        } else {
            echo "<script>alert('Only JPG, JPEG, PNG files are allowed for logo.');</script>";
        }
    }

    if ($company) {
        // Update existing company
        $sql = "UPDATE company SET 
                    name=:name,
                    street_name=:street_name,
                    building_no=:building_no,
                    phone=:phone,
                    email=:email,
                    city=:city,
                    country=:country,
                    bank_account=:bank_account,
                    logo=:logo
                WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'=>$name,
            ':street_name'=>$street_name,
            ':building_no'=>$building_no,
            ':phone'=>$phone,
            ':email'=>$email,
            ':city'=>$city,
            ':country'=>$country,
            ':bank_account'=>$bank_account,
            ':logo'=>$logoName,
            ':id'=>$id
        ]);
    } else {
        // Insert new company for current user
        $stmt = $pdo->prepare("INSERT INTO company 
            (name, street_name, building_no, phone, email, city, country, bank_account, logo) 
            VALUES (:name, :street_name, :building_no, :phone, :email, :city, :country, :bank_account, :logo)");
        $stmt->execute([
            ':name'=>$name,
            ':street_name'=>$street_name,
            ':building_no'=>$building_no,
            ':phone'=>$phone,
            ':email'=>$email,
            ':city'=>$city,
            ':country'=>$country,
            ':bank_account'=>$bank_account,
            ':logo'=>$logoName
        ]);
    }

    echo "<script>
            swal('Success!', 'Company info saved successfully!', 'success').then(() => {
                window.location.href='company.php';
            });
          </script>";
}
?>

<style>
form{
    margin: 0px 100px;
}
.form-control:focus {
    border-color: rgba(105,173,31,1);
    box-shadow: 0 0 3px rgba(105,173,31,0.5);
}
.form-label{
    font-size: 16px;
    margin-top: 10px;
}
.btn-save {
    background-color: rgba(105,173,31,1);
    color: #fff;
    border: none;
    min-width: 120px;
    float: right;
}
.logo-preview {
    height: 70px;
    border-radius: 4px;
    margin-bottom: 10px;
}
</style>

<div class="container mt-4">
    <h3 class="mb-4 text-center">Company Information</h3>

    <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Street Name</label>
                <input type="text" class="form-control" name="street_name" value="<?php echo htmlspecialchars($company['street_name'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Building No</label>
                <input type="text" class="form-control" name="building_no" value="<?php echo htmlspecialchars($company['building_no'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">City</label>
                <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($company['city'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Country</label>
                <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($company['country'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Account</label>
                <input type="text" class="form-control" name="bank_account" value="<?php echo htmlspecialchars($company['bank_account'] ?? ''); ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Logo</label>
                <input type="file" class="form-control" name="logo">
                <?php if (!empty($company['logo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($company['logo']); ?>" class="logo-preview mt-4" alt="Logo">
                <?php endif; ?>
            <button type="submit" class="btn btn-save mt-5">Save</button>
            </div>
        </div>
    </form>
</div>

<?php include 'footer.html'; ?>
