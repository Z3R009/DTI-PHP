<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="dashboard.php" class="logo d-flex align-items-center">
               <img src="../img/dti_logo.png" alt="DTI Logo">
            <span class="d-none d-lg-block fw-bold">DTI Region 12</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    <!-- search dapat unod ani -->
    <div class="search-bar ms-auto me-4 d-none d-md-flex">
    </div>

    <nav class="header-nav">
        <ul class="d-flex align-items-center">
          
        <!-- change password -->
            <li class="nav-item dropdown pe-3">
               <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-gear fs-4"></i>
              </a>
            </li>
            <!-- logout -->
           <li class="nav-item dropdown pe-3">
                <a class="dropdown-item d-flex align-items-center" href="../logout.php">
                      <i class="bi bi-box-arrow-right text-danger fs-4 me-2"></i>
                 </a>
            </li> 
        </ul>
    </nav>
</header>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="changePasswordForm" class="btn btn-primary">Change Password</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    var modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });
    });

    // Add click event listener to the gear icon
    var gearIcon = document.querySelector('.bi-gear').parentElement;
    gearIcon.addEventListener('click', function(e) {
        e.preventDefault();
        var changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'), {
            backdrop: 'static',
            keyboard: false
        });
        changePasswordModal.show();
    });

    // Handle modal hidden event
    var changePasswordModal = document.getElementById('changePasswordModal');
    changePasswordModal.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        var backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    });
});
</script>


 