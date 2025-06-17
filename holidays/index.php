<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['toast'])):
?>
    <script>
        // alert("<?= $_SESSION['toast'] ?>");
    </script>
    <?php
    unset($_SESSION['toast']);
    ?>
<?php endif; ?>

<?php require_once '../includes/header.php'; ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Holidays</h4>
            <div class="div">
                <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr'): ?>
                    <a href="./create.php" class="btn btn-primary">Add Holiday</a>
                    <a href="import.php" class="btn btn-info">Import</a>
                    <a href="export.php" class="btn btn-success">Export</a>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive"> <!-- Added for responsiveness -->
            <?php
            $sql = "SELECT * FROM holidays";
            $query = mysqli_query($conn, $sql);
            $holidays = mysqli_fetch_all($query, MYSQLI_ASSOC);
            ?>
            <table class="table table-bordered table-striped" id="employeeTable">
                <thead>
                    <th>#</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr'): ?>
                        <th>Action</th>
                    <?php endif; ?>

                </thead>
                <tbody>
                    <?php
                    foreach ($holidays as $key => $row) {
                    ?>
                        <tr>
                            <td><?php echo  $key + 1 ?></td>
                            <td><?php echo $row['name'] ?></td>
                            <td><?php echo $row['date'] ?></td>
                            <td><?php echo $row['description'] ?></td>
                            <td class="text-capitalize"><?php echo $row['type'] ?></td>
                            <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr'): ?>

                                <td>
                                    <a href='./edit.php?id=<?php echo $row['id'] ?>' class="btn btn-success btn-sm"><i class="bx bx-edit fs-5"></i></a>
                                    <button class="btn btn-danger btn-sm delete-btn" data-table-name="holidays" data-id="<?php echo $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>
                                </td>
                            <?php endif; ?>

                        <?php  } ?>
                </tbody>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#employeeTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthMenu": [10, 25, 50, 100],
            "autoWidth": false
        });
    });
</script>
<?php require_once '../includes/footer.php'; ?>