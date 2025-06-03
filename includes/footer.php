</div>
</div>
</div>
</div>
<!-- jQuery (load first) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core Plugins -->
<script src="<?php echo BASE_URL; ?>/assets/libs/metismenu/metisMenu.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/libs/node-waves/waves.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>

<!-- Date Range Picker -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- Bootstrap Datepicker (if used separately) -->
<script src="<?php echo BASE_URL; ?>/assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

<!-- SweetAlert2 -->
<script src="<?php echo BASE_URL; ?>/assets/libs/sweetalert2/sweetalert2.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Validation -->
<script src="<?php echo BASE_URL; ?>/assets/libs/jquery-validation/jquery.validate.min.js"></script>

<!-- Select2 -->
<script src="<?php echo BASE_URL; ?>/assets/libs/select2/js/select2.min.js"></script>

<!-- Summernote -->
<script src="<?php echo BASE_URL; ?>/assets/libs/summernote/summernote-bs4.min.js"></script>

<!-- Timepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

<!-- Echarts -->
<script src="<?php echo BASE_URL; ?>/assets/libs/echarts/echarts.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/pages/echarts.init.js"></script>





<script>
    $(document).ready(function() {
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var tablename = $(this).data('table-name');
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>/api/delete.php',
                        type: 'POST',
                        data: {
                            id: id,
                            tablename: tablename,
                        },
                        success: function(data) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Record has been deleted successfully.",
                                icon: "success",
                                showConfirmButton: false
                            });
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    });
                }
            });
        })
    });
</script>
</body>

</html>