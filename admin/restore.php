<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
require 'permission.php';
if (!hasPermission('restore.manage')) {
    die('Access Denied');
}

require '../Config/config.php';
require '../Config/common.php';
?>
<?php include 'header.php'; ?>

<style>
.restore-card {
    max-width: 520px;
    margin: 2rem auto;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: none;
}
.restore-card .card-header {
    background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
    color: #fff;
    font-weight: 600;
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.25rem;
    border: none;
}
.restore-card .card-body {
    padding: 1.5rem 1.75rem;
}
.restore-upload-wrap {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    background: #f8f9fa;
    margin-bottom: 1.25rem;
}
.restore-upload-wrap input[type="file"] {
    display: block;
    margin: 0 auto 1rem;
}
.restore-upload-wrap .upload-hint {
    font-size: 13px;
    color: #6c757d;
}
.btn-restore {
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
    color: #fff;
}
.btn-restore:hover {
    opacity: 0.92;
    color: #fff;
}
.btn-restore:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.restore-progress-wrap {
    display: none;
    margin-top: 1.25rem;
}
.restore-progress-wrap.active {
    display: block;
}
.restore-progress-wrap .progress {
    height: 22px;
    border-radius: 11px;
    background-color: #e9ecef;
}
#restoreResult {
    margin-top: 1.25rem;
}
#restoreResult .alert {
    border-radius: 8px;
}
.swal-title,
.swal-text {
  text-align: center !important;
}
/* FULL SCREEN BLOCKER */
.restore-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.45);
  z-index: 9999;
  display: none;
  align-items: center;
  justify-content: center;
}

/* PROGRESS BOX */
.restore-overlay .restore-box {
  background: #fff;
  padding: 25px 30px;
  border-radius: 12px;
  text-align: center;
  width: 320px;
  box-shadow: 0 10px 30px rgba(0,0,0,.2);
}

.restore-overlay .progress {
  height: 22px;
  border-radius: 11px;
}

.restore-overlay.active {
  display: flex;
}

body.locked {
  overflow: hidden;
  pointer-events: none;
}

.restore-overlay,
.restore-overlay * {
  pointer-events: auto;
}
</style>


<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card restore-card">
          <div class="card-header">
            <i class="fas fa-database mr-2"></i> Database Restore
          </div>
          <div class="card-body">
            <p class="text-muted mb-4">Upload a backup (.sql) file to restore the database. This will replace all current data.</p>

            <form id="restoreForm">
              <div class="restore-upload-wrap">
                <label for="sqlFile" class="d-block mb-2 font-weight-bold">Choose SQL backup file</label>
                <input type="file" id="sqlFile" name="sql_file" accept=".sql" required class="form-control-file">
                <p class="upload-hint mb-0">Only .sql files (e.g. backup_YYYY-MM-DD_HH-ii-ss.sql)</p>
              </div>
              <button type="submit" class="btn btn-restore" id="restoreBtn">
                <i class="fas fa-upload mr-1"></i> Upload & Restore
              </button>
            </form>

            <div class="restore-overlay" id="progressWrap">
              <div class="restore-box">
                <h5 class="mb-3">Restoring Database...</h5>
                <div class="progress">
                  <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                      style="width:100%">Please wait</div>
                </div>
                <small class="text-muted d-block mt-2">Do not refresh or leave this page</small>
              </div>
            </div>


            <div id="restoreResult"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var form = document.getElementById('restoreForm');
  var fileInput = document.getElementById('sqlFile');
  var restoreBtn = document.getElementById('restoreBtn');
  var progressWrap = document.getElementById('progressWrap');

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!fileInput.files || fileInput.files.length === 0) {
      swal('Warning!', 'Please select a .sql file.', 'warning');
      return;
    }

    var file = fileInput.files[0];
    if (!/\.sql$/i.test(file.name)) {
      swal('Invalid File!', 'Please select a valid .sql file.', 'warning');
      return;
    }

    swal({
      title: "Are you sure?",
      text: "This will replace all current database data with the uploaded backup!",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willRestore) => {
      if (!willRestore) return;

      progressWrap.classList.add('active');
      restoreBtn.disabled = true;

      var formData = new FormData();
      formData.append('sql_file', file);

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'restore_run.php', true);

      xhr.onload = function() {
        progressWrap.classList.remove('active');
        restoreBtn.disabled = false;

        try {
          var res = JSON.parse(xhr.responseText);

          if (res.status === 'success') {
            swal("Success!", res.message || "Restore completed successfully.", "success")
              .then(() => {
                window.location.reload(true);
              });
          } else {
            swal("Restore Failed!", res.message || "Unknown error.", "error");
          }

        } catch (e) {
          swal("Error!", "Invalid response from server.", "error");
        }
      };

      xhr.onerror = function() {
        progressWrap.classList.remove('active');
        restoreBtn.disabled = false;
        swal("Network Error!", "Please try again.", "error");
      };

      xhr.send(formData);
    });
  });
})();
</script>


<?php include 'footer.html'; ?>
