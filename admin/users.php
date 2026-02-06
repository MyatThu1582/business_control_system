<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
include 'header.php';

// Pagination setup
$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$numOfrecs = 10;
$offset = ($pageno - 1) * $numOfrecs;

// Search
$searchQuery = '';
if (!empty($_POST['search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE name LIKE :search OR email LIKE :search ";
}

// Count total records
$stmt = $pdo->prepare("SELECT * FROM users $searchQuery ORDER BY id DESC");
if (!empty($_POST['search'])) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$rawResult = $stmt->fetchAll();
$total_pages = ceil(count($rawResult)/$numOfrecs);

// Fetch paginated users
$stmt = $pdo->prepare("SELECT * FROM users $searchQuery ORDER BY id DESC LIMIT $offset, $numOfrecs");
if (!empty($_POST['search'])) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$users = $stmt->fetchAll();
?>

<div class="col-md-12 px-3">
    <div class="d-flex mt-4 mb-4 justify-content-between">
        <h1 class="card-title">Users Listings</h1>
        <div class="d-flex">
            <form action="" method="post" class="me-2">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search Name or Email" name="search">
                    <button type="submit" class="input-group-text">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <a href="user_add.php" class="btn btn-purple text-light ml-2">Create New User</a>
        </div>
    </div>

    <div class="outer">
        <table class="table table-hover">
            <thead class="custom-thead">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($users) {
                    $count = $offset + 1;
                    foreach ($users as $user) {
                        $role_id = $user['role'];
                        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id='$role_id'");
                        $stmt->execute();
                        $role = $stmt->fetch(PDO::FETCH_ASSOC);

                        ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo str_repeat('*', 8); // hide actual password ?></td>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="user_edit.php?id=<?php echo $user['id'];?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="user_delete.php?id=<?php echo $user['id'];?>" class="btn btn-sm delete-user">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                        $count++;
                    }
                } else {
                    echo '<tr><td colspan="5" class="text-center">No users found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.querySelectorAll('.delete-user').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault(); // prevent default link
        const href = this.getAttribute('href');

        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this user!",
            icon: "warning",
            buttons: ["Cancel", "Yes, delete it!"],
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                window.location.href = href;
            }
        });
    });
});
</script>
<?php include 'footer.html'; ?>
