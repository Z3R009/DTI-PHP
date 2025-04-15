    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 Elements
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
            
            // Initialize datatable
            const datatablesSimple = document.getElementsByClassName('datatable');
            if (datatablesSimple.length > 0) {
                for (let i = 0; i < datatablesSimple.length; i++) {
                    new simpleDatatables.DataTable(datatablesSimple[i], {
                        perPageSelect: [5, 10, 15, 20, 25],
                        perPage: 10,
                    });
                }
            }
        });
    </script>
</body>

</html> 