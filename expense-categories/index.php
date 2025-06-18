<?php require_once '../includes/header.php';
$user_values = userProfile();

if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
    $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
    $_SESSION['toast'] = "Access denied. Employees only.";
    header("Location: " . $redirectUrl);
    exit();
}
?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box  pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Expense Categories</h4>
            <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Add Expense Category</a>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive"> <!-- Added for responsiveness -->

            <?php
            $sql = "SELECT * FROM expense_categories ORDER BY id DESC";
            $query = mysqli_query($conn, $sql);
            if ($num = mysqli_num_rows($query) > 0) {
                $expense_categories = mysqli_fetch_all($query, MYSQLI_ASSOC);
            ?>
                <table class="table table-sm" id="expense_categories_table">
                    <thead>
                        <th>#</th>
                        <th>Name</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($expense_categories as $key => $row) {
                        ?>
                            <tr>
                                <td><?php echo  $key + 1 ?></td>
                                <td><?php echo $row['name'] ?></td>
                                <td>
                                    <a href='./edit.php?id=<?php echo $row['id'] ?>' class="btn btn-primary btn-sm"><i class="bx bx-edit fs-5"></i></a>
                                    <button class="btn btn-danger delete-btn btn-sm" data-table-name="expense_categories" data-id="<?php echo $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>
                                </td>
                            <?php  } ?>
                    </tbody>
        </div>
    </div>

<?php } ?>
</div>
<script>
    $(document).ready(function() {
        $('#expense_categories_table').DataTable({
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