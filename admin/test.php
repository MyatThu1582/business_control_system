<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <link rel="stylesheet" href="chosenselect/jquery-ui.css">
  <link rel="stylesheet" href="chosenselect/chosen.css">
  <script src="jquery.resc.js"></script>
  <script src="chosenselect/jquery-1.9.3.js"></script>
  <script src="chosenselect/jquery-ui.js"></script>
  <script src="chosenselect/chosen.jquery.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      var selects = document.querySelectorAll(".chzn-select");
      selects.forEach(function(select) {
        $(select).chosen(); // Chosen itself still requires jQuery
      });
    });
  </script>
  <style>
    .chosen-container {
      width: 100% !important; /* force chosen box to fit parent */
    }
  </style>
</head>
<body>

  <div class="col-6">
    <label class="form-label">Category</label>
    <select id="filterCategory" 
            class="chzn-select" 
            <?php echo $filterCategory ? "disabled" : ""; ?> 
            name="category_id">
      <option value="all">All</option>
      <?php 
      $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY id DESC");
      $stmt->execute();
      $result = $stmt->fetchAll();
      foreach ($result as $value) {
        ?>
        <option value="<?php echo $value['categories_code']; ?>">
          <?php echo $value['categories_name']; ?>
        </option>
        <?php
      }
      ?>
    </select>
  </div>

</body>
</html>
