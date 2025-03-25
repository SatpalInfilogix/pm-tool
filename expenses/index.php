<?php require_once '../includes/header.php'; ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box  pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Expenses</h4>
            <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Add Expense</a>
        </div>
    </div>
</div>
<?php $categories = mysqli_query($conn, "SELECT * FROM `expense_categories`"); ?>
<form>
    <div class="row mb-3">
        <div class="col-md-3">
            <label>Category</label>
            <select class="form-control" name="category_id" id="category_id">
                <option value="" selected>All Categories</option>
                <?php
                while ($category = mysqli_fetch_assoc($categories)) {
                    $selected = ($_GET['category_id'] == $category['id']) ? 'selected' : '';
                    echo '<option value="' . $category['id'] . '" ' . $selected . ' >' . $category['name'] . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="status">Status</label>
                <select id="expense-status" class="form-select" name="status" required>
                    <option value="" selected disabled>Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
        <label for="expense_date"> Date</label>
        <input type="text"  class="form-control" id="date"  name="date" required  autocomplete="off">
        </div>
    </div>
</form>
<div class="card">
    <div class="card-body">
        <?php
        $sql = "SELECT * FROM expenses INNER JOIN expense_categories
                    ON expenses.category_id = expense_categories.id";
        if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
            $sql .= ' WHERE category_id=' . $_GET['category_id'];
        }
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $sql .= " WHERE status='" . $_GET['status']. "'";
        }
        $query = mysqli_query($conn, $sql);
        $expenses = mysqli_fetch_all($query, MYSQLI_ASSOC);
        ?>
        <table class="table table-sm" id="expensesTable">
            <thead>
                <th>#</th>
                <th>Title</th>
                <th>Amount</th>
                <th>Category</th>
                <th>Expense Date</th>
                <th>Status</th>
                <th>Action</th>
            </thead>
            <tbody>
                <?php
                foreach ($expenses as $key => $row) {
                ?>
                    <tr>
                        <td><?php echo  $key + 1 ?></td>
                        <td><?php echo $row['title'] ?></td>
                        <td><?php echo $row['amount'] ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['expense_date'] ?></td>
                        <td><?php echo $row['status'] ?></td>
                        <td>
                            <a href='./edit.php?id=<?php echo $row['id'] ?>' class="btn btn-primary btn-sm"><i class="bx bx-edit fs-5"></i></a>
                            <button class="btn btn-danger delete-btn btn-sm" data-table-name="expenses" data-id="<?php echo $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>
                        </td>
                    <?php  } ?>
            </tbody>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        })
        $("#category_id, #expense-status").change(function() {
            var category_id = $('#category_id').val();
            var status = $('#expense-status').val();
            let parameters = '';
            if(category_id){
                parameters += 'category_id='+category_id;
            }
            if(status){
                parameters += '&status='+status;
            }
            window.location.href = '?' + parameters;
        });
        $('#category_id,#expense-status').select2();
        $('#expensesTable').DataTable({
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