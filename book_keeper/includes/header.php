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
            <li class="nav-item pe-3">
                <a class="nav-link d-flex align-items-center" href="settings.php">
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


 