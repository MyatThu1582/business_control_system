<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
    }
require 'permission.php';
if (!hasPermission('backup.manage')) {
    die('Access Denied');
}

require '../config/config.php';
require '../config/common.php';
?>
<?php include 'header.php'; ?>

<style>
.backup-card {
    max-width: 520px;
    margin: 2rem auto;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: none;
}
.backup-card .card-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
    font-weight: 600;
    border-radius: 12px 12px 0 0;
    padding: 1rem 1.25rem;
    border: none;
}
.backup-card .card-body {
    padding: 1.5rem 1.75rem;
}
.backup-progress-wrap {
    display: none;
    margin-top: 1.25rem;
}
.backup-progress-wrap.active {
    display: block;
}
.backup-progress-wrap .progress {
    height: 22px;
    border-radius: 11px;
    background-color: #e9ecef;
}
.backup-progress-wrap .progress-bar {
    font-size: 12px;
    line-height: 22px;
    transition: width 0.4s ease;
}
.backup-status {
    margin-top: 0.5rem;
    font-size: 13px;
    color: #6c757d;
}
#backupResult {
    margin-top: 1.25rem;
}
#backupResult .alert {
    border-radius: 8px;
}
#backupResult a.download-link {
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
}
.btn-backup {
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #fff;
}
.btn-backup:hover {
    opacity: 0.92;
    color: #fff;
}
.btn-backup:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card backup-card">
          <div class="card-header">
            <i class="fas fa-database mr-2"></i> Database Backup
          </div>
          <div class="card-body">
            <p class="text-muted mb-4">Create a full backup of your database. The file will be generated and you can download it when ready.</p>

            <button type="button" class="btn btn-backup" id="backupBtn">
              <i class="fas fa-download mr-1"></i> Start Backup
            </button>

            <div class="backup-progress-wrap" id="progressWrap">
              <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" id="progressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
              </div>
              <div class="backup-status" id="progressStatus">Preparing backup...</div>
            </div>

            <div id="backupResult"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  var backupBtn = document.getElementById('backupBtn');
  var progressWrap = document.getElementById('progressWrap');
  var progressBar = document.getElementById('progressBar');
  var progressStatus = document.getElementById('progressStatus');
  var resultDiv = document.getElementById('backupResult');
  var progressInterval = null;

  function setProgress(percent, text) {
    progressBar.style.width = percent + '%';
    progressBar.setAttribute('aria-valuenow', percent);
    progressBar.textContent = percent + '%';
    if (text) progressStatus.textContent = text;
  }

  function stopFakeProgress() {
    if (progressInterval) {
      clearInterval(progressInterval);
      progressInterval = null;
    }
  }

  backupBtn.addEventListener('click', function() {
    backupBtn.disabled = true;
    resultDiv.innerHTML = '';
    progressWrap.classList.add('active');
    setProgress(5, 'Starting backup...');

    // Animate progress while waiting (fake progress up to 85%)
    var p = 5;
    progressInterval = setInterval(function() {
      p += Math.random() * 8 + 4;
      if (p >= 85) {
        p = 85;
        clearInterval(progressInterval);
        progressInterval = null;
      }
      setProgress(Math.min(85, Math.floor(p)), 'Backing up database...');
    }, 400);

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'backup_run.php', true);

    xhr.onload = function() {
      stopFakeProgress();
      setProgress(100, 'Complete.');
      progressBar.classList.remove('progress-bar-animated');

      try {
        var res = JSON.parse(xhr.responseText);
        if (res.status === 'success') {
          resultDiv.innerHTML =
            '<div class="alert alert-success">' +
            '<i class="fas fa-check-circle mr-1"></i> Backup completed successfully. ' +
            '<a class="download-link alert-link" href="backups/' + res.file + '" download><i class="fas fa-file-download"></i> Download Backup</a>' +
            '</div>';
        } else {
          resultDiv.innerHTML =
            '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> Backup failed: ' + (res.message || 'Unknown error') + '</div>';
        }
      } catch (e) {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> Invalid response from server.</div>';
      }

      backupBtn.disabled = false;
      setTimeout(function() {
        progressWrap.classList.remove('active');
        progressBar.classList.add('progress-bar-animated');
        setProgress(0, '');
      }, 1500);
    };

    xhr.onerror = function() {
      stopFakeProgress();
      progressWrap.classList.remove('active');
      resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> Network error. Please try again.</div>';
      backupBtn.disabled = false;
    };

    xhr.send();
  });
})();
</script>

<?php include 'footer.html'; ?>
