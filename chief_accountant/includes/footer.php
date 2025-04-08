    </main><!-- End #main -->

    <!-- Back to top button -->
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Footer -->
    <footer class="footer">
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> <strong><span>Department of Trade and Industry</span></strong>. All Rights Reserved
        </div>
    </footer>

    <!-- Vendor JS Files -->
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="../assets/vendor/datatables/responsive.bootstrap5.min.js"></script>

    <!-- Template Main JS File -->
    <script src="../assets/js/main.js"></script>

    <style>
        /* Footer Styles */
        .footer {
            padding: 20px 30px;
            font-size: 14px;
            transition: all 0.3s;
            border-top: 1px solid var(--border-color);
            margin-left: 280px;
            background: white;
        }

        @media (max-width: 1199px) {
            .footer {
                margin-left: 0;
            }
            
            .toggle-sidebar .footer {
                margin-left: 280px;
            }
        }

        .footer .copyright {
            text-align: center;
            color: var(--text-color);
        }

        .footer .copyright span {
            color: var(--primary-color);
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            visibility: hidden;
            opacity: 0;
            right: 30px;
            bottom: 30px;
            z-index: 99;
            background: var(--primary-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.4s;
            color: white;
        }

        .back-to-top i {
            font-size: 24px;
            margin-top: -3px;
        }

        .back-to-top:hover {
            background: var(--secondary-color);
            color: white;
        }

        .back-to-top.active {
            visibility: visible;
            opacity: 1;
        }

        /* Loading Spinner */
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: all 0.3s;
        }

        #loading.show {
            visibility: visible;
            opacity: 1;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--accent-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Loading Spinner -->
    <div id="loading">
        <div class="spinner"></div>
    </div>

    <script>
        // Back to top button
        const backToTop = document.querySelector('.back-to-top');
        if (backToTop) {
            const toggleBacktotop = () => {
                if (window.scrollY > 100) {
                    backToTop.classList.add('active');
                } else {
                    backToTop.classList.remove('active');
                }
            }
            window.addEventListener('load', toggleBacktotop);
            document.addEventListener('scroll', toggleBacktotop);
            backToTop.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize all popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Loading spinner
        const loading = document.getElementById('loading');
        
        // Show loading spinner before page load
        document.onreadystatechange = function() {
            if (document.readyState !== "complete") {
                loading.classList.add('show');
            } else {
                loading.classList.remove('show');
            }
        };

        // Show loading spinner before AJAX requests
        $(document).ajaxStart(function() {
            loading.classList.add('show');
        }).ajaxStop(function() {
            loading.classList.remove('show');
        });

        // DataTables default configuration
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 10,
            stateSave: true
        });
    </script>
</body>
</html> 