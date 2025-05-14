<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="dashboard.php" class="logo d-flex align-items-center">
            <img src="../img/DTI_short.png" alt="DTI Logo">
            <span class="d-none d-lg-block fw-bold">DTI Region 12</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar ms-auto me-4 d-none d-md-flex">
        <!-- <form class="search-form d-flex align-items-center" method="POST" action="#">
            <input type="text" name="query" placeholder="Search" title="Enter search keyword">
            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
        </form> -->
    </div><!-- End Search Bar -->

    <nav class="header-nav">
        <ul class="d-flex align-items-center">
            <li class="nav-item d-block d-md-none">
                <a class="nav-link nav-icon search-bar-toggle" href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li><!-- End Search Icon-->

            <!-- <li class="nav-item dropdown">
                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-primary badge-number">3</span>
                </a>End Notification Icon -->

                <!-- <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                    <li class="dropdown-header">
                        You have 3 new notifications
                        <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li class="notification-item">
                        <i class="bi bi-exclamation-circle text-warning"></i>
                        <div>
                            <h4>Budget Update</h4>
                            <p>New budget allocation for Q3 has been approved</p>
                            <p>30 min. ago</p>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li class="notification-item">
                        <i class="bi bi-check-circle text-success"></i>
                        <div>
                            <h4>Report Completed</h4>
                            <p>Monthly financial report has been generated</p>
                            <p>1 hr. ago</p>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li class="notification-item">
                        <i class="bi bi-info-circle text-primary"></i>
                        <div>
                            <h4>System Update</h4>
                            <p>The system will undergo maintenance on Saturday</p>
                            <p>2 hrs. ago</p>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li class="dropdown-footer">
                        <a href="#">Show all notifications</a>
                    </li>

                </ul>End Notification Dropdown Items -->
            </li><!-- End Notification Nav -->

            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="../img/dti_logo.png" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2">Book Keeper</span>
                </a><!-- End Profile Image Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                   
             
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-gear"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                  
                  

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->
        </ul>
    </nav><!-- End Icons Navigation -->
</header><!-- End Header -->

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-gradient">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="bi bi-shield-lock me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm" action="change_password.php" method="post">
                <div class="modal-body">
                    <!-- <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
                    </div> -->
                    
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <div class="password-field position-relative">
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                            <span class="password-toggle-icon position-absolute top-50 end-0 translate-middle-y me-2">
                                <i class="bi bi-eye" data-target="currentPassword"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <div class="password-field position-relative">
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                            <span class="password-toggle-icon position-absolute top-50 end-0 translate-middle-y me-2">
                                <i class="bi bi-eye" data-target="newPassword"></i>
                            </span>
                        </div>
                        <div class="password-strength mt-2"></div>
                        <small class="text-muted">Password must be at least 8 characters with uppercase, lowercase, number, and special character.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <div class="password-field position-relative">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                            <span class="password-toggle-icon position-absolute top-50 end-0 translate-middle-y me-2">
                                <i class="bi bi-eye" data-target="confirmPassword"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="savePasswordBtn">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Dark blue theme for password modal */
    #changePasswordModal .modal-content {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    #changePasswordModal .modal-header {
        background: linear-gradient(135deg, #104e8b, #1e3c72) !important;
        border-bottom: none;
    }
    
    #changePasswordModal .modal-body {
        background-color: #f8f9fa;
        padding: 20px;
    }
    
    #changePasswordModal .form-control {
        border-radius: 6px;
        padding-right: 40px;
    }
    
    #changePasswordModal .form-label {
        font-weight: 600;
        color: #104e8b;
    }
    
    #changePasswordModal .password-toggle-icon {
        cursor: pointer;
        color: #6c757d;
        z-index: 10;
    }
    
    #changePasswordModal .password-toggle-icon:hover {
        color: #104e8b;
    }
    
    #changePasswordModal .btn-primary {
        background: linear-gradient(135deg, #104e8b, #1e3c72);
        border: none;
    }
    
    #changePasswordModal .btn-primary:hover {
        background: linear-gradient(135deg, #1e3c72, #104e8b);
        box-shadow: 0 2px 10px rgba(30, 60, 114, 0.4);
    }
    
    /* Password strength styles */
    #changePasswordModal .password-strength {
        height: 5px;
        transition: all 0.3s ease;
    }
</style>

<!-- Change Password JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility with icon inside input
        const toggleIcons = document.querySelectorAll('.password-toggle-icon i');
        toggleIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const inputField = document.getElementById(targetId);
                
                if (inputField.type === 'password') {
                    inputField.type = 'text';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                } else {
                    inputField.type = 'password';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                }
            });
        });
        
        // Password strength check
        const newPasswordInput = document.getElementById('newPassword');
        const passwordStrengthDiv = document.querySelector('.password-strength');
        
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    feedback = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-danger" style="width: 20%"></div></div><small class="text-danger">Very Weak</small>';
                    break;
                case 2:
                    feedback = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-warning" style="width: 40%"></div></div><small class="text-warning">Weak</small>';
                    break;
                case 3:
                    feedback = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-info" style="width: 60%"></div></div><small class="text-info">Moderate</small>';
                    break;
                case 4:
                    feedback = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-primary" style="width: 80%"></div></div><small class="text-primary">Strong</small>';
                    break;
                case 5:
                    feedback = '<div class="progress" style="height: 5px;"><div class="progress-bar bg-success" style="width: 100%"></div></div><small class="text-success">Very Strong</small>';
                    break;
            }
            
            passwordStrengthDiv.innerHTML = feedback;
        });
        
        // Confirm password validation
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== newPasswordInput.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Form submission
        const changePasswordForm = document.getElementById('changePasswordForm');
        
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if passwords match
            if (newPasswordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.classList.add('is-invalid');
                return;
            }
            
            // Show loading state on button
            const saveButton = document.getElementById('savePasswordBtn');
            const originalButtonText = saveButton.innerHTML;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            saveButton.disabled = true;
            
            // Submit the form using fetch
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                // Create alert based on response
                const alertType = data.success ? 'success' : 'danger';
                const alertMessage = data.message || (data.success ? 'Password changed successfully!' : 'Failed to change password.');
                
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${alertType} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    <i class="bi bi-${data.success ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
                    ${alertMessage}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insert alert at top of modal body
                const modalBody = document.querySelector('.modal-body');
                modalBody.insertBefore(alertDiv, modalBody.firstChild);
                
                // Reset button state
                saveButton.innerHTML = originalButtonText;
                saveButton.disabled = false;
                
                // Reset form if successful
                if (data.success) {
                    changePasswordForm.reset();
                    passwordStrengthDiv.innerHTML = '';
                    
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                        modal.hide();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Show generic error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    An error occurred while processing your request.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                const modalBody = document.querySelector('.modal-body');
                modalBody.insertBefore(alertDiv, modalBody.firstChild);
                
                // Reset button state
                saveButton.innerHTML = originalButtonText;
                saveButton.disabled = false;
            });
        });
    });
</script>