<div class="card">
    <form method="POST" name="expense-form" id="expense-form" class="p-3" enctype="multipart/form-data">
        <input type="hidden" name="expense_id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="title">Title</label>
                    <input type="text"  class="form-control" id="title" name="title" value="<?php echo isset($row['title']) ? $row['title'] : ''; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="amount">Amount</label>
                    <input type="number" class="form-control"  id="amount" name="amount" required min="0" step="0.01" value="<?php echo isset($row['amount']) ? $row['amount'] : ''; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="expense_date">Expense Date</label>
                    <input type="text"  class="form-control" id="expense_date"  name="expense_date" required value="<?php echo isset($row['expense_date']) ? $row['expense_date'] : ''; ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="attachment">Attachment</label>
                    <input type="text" class="form-control"  id="attachment" name="attachment" value="<?php echo isset($row['attachment']) ? $row['attachment'] : ''; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="status">Status</label>
                    <select id="expense-status"  class="form-select"  name="status" required>
                        <option value="" selected disabled>Select Status</option>
                        <option value="pending" <?php echo (isset($row['status']) && $row['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo (isset($row['status']) && $row['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo (isset($row['status']) && $row['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
            </div>
            <?php $categories = mysqli_query($conn, "SELECT * FROM `expense_categories`"); ?>
            <div class="col-md-6">
                <label>Category</label>
                <select class="form-control" name="category_id" id="category_id">
                    <option value="" selected disabled>Select Category</option>
                    <?php
                    while ($category = mysqli_fetch_assoc($categories)) {
                        $selected = ($row['category_id'] == $category['id']) ? 'selected' : '';
                        echo '<option value="' . $category['id'] . '" '.$selected.' >' . $category['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="description">Description</label>
                    <textarea id="description" class="form-control"  name="description" rows="4" required><?php echo isset($row['description']) ? $row['description'] : ''; ?></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" name="<?php echo isset($row['id']) ? 'edit_expense' : 'add_expense'; ?>">
            <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
        </button>
    </form>
</div>
<script>
    $(document).ready(function() {
        $("#expense_date").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#expense-status').select2();
        $('#category_id').select2();
        $("#expense-form").validate({
            rules: {
                title: {
                    required: true
                },
                amount: {
                    required: true,
                    number: true,
                    min: 0
                },
                expense_date: {
                    required: true,
                    date: true
                },
                description: {
                    required: true
                },
                status: {
                    required: true
                }
            },
            messages: {
                title: {
                    required: "Please enter a valid title."
                },
                amount: {
                    required: "Please enter an amount.",
                    number: "Please enter a valid number.",
                    min: "Amount must be greater than or equal to 0."
                },
                expense_date: {
                    required: "Please select an expense date.",
                    date: "Please enter a valid date."
                },
                description: {
                    required: "Please enter a description."
                },
                status: {
                    required: "Please select a status."
                }
            }
        });
    });
</script>