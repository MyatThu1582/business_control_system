<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../config/config.php';
require '../config/common.php';
include 'header.php';

// Pagination setup
$pageno = !empty($_GET['pageno']) ? $_GET['pageno'] : 1;
$numOfrecs = 10;
$offset = ($pageno - 1) * $numOfrecs;

// Search
$searchQuery = '';
if (!empty($_POST['search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE role_name LIKE :search ";
}

// Count total records
$stmt = $pdo->prepare("SELECT * FROM roles $searchQuery ORDER BY id DESC");
if (!empty($_POST['search'])) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$rawResult = $stmt->fetchAll();
$total_pages = ceil(count($rawResult)/$numOfrecs);

// Fetch paginated roles
$stmt = $pdo->prepare("SELECT * FROM roles $searchQuery ORDER BY id DESC LIMIT $offset, $numOfrecs");
if (!empty($_POST['search'])) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$roles = $stmt->fetchAll();
?>

<div class="col-md-12 px-3">
    <div class="d-flex mt-4 mb-4 justify-content-between">
        <h1 class="card-title">Roles Listing</h1>
        <div class="d-flex">
            <form action="" method="post" class="me-2">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search Role Name" name="search">
                    <button type="submit" class="input-group-text">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <a href="role_add.php" class="btn btn-purple text-light ml-2">Create New Role</a>
        </div>
    </div>

    <div class="outer">
        <table class="table table-hover">
            <thead class="custom-thead">
                <tr>
                    <th>#</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($roles) {
                    $count = $offset + 1;
                    foreach ($roles as $role) {
                        ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td><?php echo htmlspecialchars($role['description']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="role_edit.php?id=<?php echo $role['id'];?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="role_delete.php?id=<?php echo $role['id'];?>" class="btn btn-sm delete-role">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                        $count++;
                    }
                } else {
                    echo '<tr><td colspan="4" class="text-center">No roles found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.delete-role').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault(); 
        const href = this.getAttribute('href');

        swal({
            title: "Are you sure?",
            text: "This role will be deleted!",
            icon: "warning",
            buttons: ["Cancel", "Yes, delete it!"],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                window.location.href = href;
            }
        });
    });
});
</script>

<?php include 'footer.html'; ?>
