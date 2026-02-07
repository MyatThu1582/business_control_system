<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require '../Config/config.php';
require '../Config/common.php';
?>

<?php include 'header.php'; ?>

<?php
// ===== PAGINATION CONFIG =====
if (!empty($_GET['pageno'])) {
    $pageno = (int)$_GET['pageno'];
} else {
    $pageno = 1;
}

$numOfrecs = 10;
$offset = ($pageno - 1) * $numOfrecs;

// ===== SEARCH & DATA =====
if (empty($_POST['search'])) {

    // total rows
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $total_rows = $stmt->fetchColumn();

    $total_pages = ceil($total_rows / $numOfrecs);

    // paginated data
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY id DESC LIMIT $offset, $numOfrecs");
    $stmt->execute();
    $result = $stmt->fetchAll();

} else {

    $search = $_POST['search'];

    // total rows with search
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE categories_name LIKE '%$search%'");
    $stmt->execute();
    $total_rows = $stmt->fetchColumn();

    $total_pages = ceil($total_rows / $numOfrecs);

    // paginated data with search
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE categories_name LIKE '%$search%' ORDER BY id DESC LIMIT $offset, $numOfrecs");
    $stmt->execute();
    $result = $stmt->fetchAll();
}
?>

<div class="col-md-12 px-3">
  <div class="d-flex mt-4 mb-4 justify-content-between">
    <div>
      <h1 class="card-title">Category Listings</h1>
    </div>
    <div class="d-flex text-right">
      <div class="col">
        <a href="export_excel.php?table=categories&search=<?= urlencode($_POST['search'] ?? '') ?>" class="btn export-btn btn-sm">
          <!-- <img src="images/excel.png" alt="Excel" width="20"> -->
          Export Excel
        </a>
      </div>
      <div class="ml-1">
        <form action="" method="post">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search Category_Name" name="search">
            <button type="submit" class="input-group-text">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search-heart" viewBox="0 0 16 16">
                    <path d="M6.5 4.482c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.69 0-5.018"/>
                    <path d="M13 6.5a6.47 6.47 0 0 1-1.258 3.844q.06.044.115.098l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1-.1-.115h.002A6.5 6.5 0 1 1 13 6.5M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11"/>
                  </svg>
            </button>
          </div>
        </form>
      </div>
      <div class="col">
        <a href="category_add.php" class="btn btn-purple btn-sm text-light">+ Create New Category</a>
      </div>
    </div>
  </div>

  <div class="outer">
    <table class="table table-hover">
      <thead class="custom-thead">
        <tr>
          <th style="width:10px">No</th>
          <th>Category Code</th>
          <th>Category Name</th>
          <th style="width:40px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result) {
            $id = ($pageno - 1) * $numOfrecs + 1;
            foreach ($result as $value) {
        ?>
        <tr>
          <td><?= $id++; ?></td>
          <td><?= $value['categories_code']; ?></td>
          <td><?= $value['categories_name']; ?></td>
          <td>
            <div class="btn-group">
              <a href="category_edit.php?id=<?= $value['id']; ?>" class="btn btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                </svg>
              </a>
              <a href="category_delete.php?id=<?= $value['id']; ?>" class="btn btn-sm delete-category">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                  <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                </svg>
              </a>
            </div>
          </td>
        </tr>
        <?php
            }
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- ===== PAGINATION BUTTONS ===== -->
  <nav>
    <ul class="pagination justify-content-end">

      <li class="page-item <?= ($pageno <= 1) ? 'disabled' : '' ?>">
        <a class="page-link" href="?pageno=<?= $pageno - 1 ?>">Previous</a>
      </li>

      <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
        <li class="page-item <?= ($i == $pageno) ? 'active' : '' ?>">
          <a class="page-link" href="?pageno=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= ($pageno >= $total_pages) ? 'disabled' : '' ?>">
        <a class="page-link" href="?pageno=<?= $pageno + 1 ?>">Next</a>
      </li>

    </ul>
  </nav>
</div>

<script>
document.querySelectorAll('.delete-category').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();
    const href = this.getAttribute('href');

    swal({
      title: "Are you sure?",
      text: "You will not be able to recover this category!",
      icon: "warning",
      buttons: ["Cancel", "Yes, delete it!"],
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) window.location.href = href;
    });
  });
});
</script>

<?php include 'footer.html'; ?>