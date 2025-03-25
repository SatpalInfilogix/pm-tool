<div class="card w-50">
    <div class="card-body">
        <form method="POST" action="" name="expense-category-form" id="expense-category-form">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" required minlength="2"
                            value="<?php echo isset($row['name']) ? $row['name'] : ''; ?>">
                    </div>
                    <input type="hidden" name="expense_categories_id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>">
                </div>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary" name="<?php echo isset($row['id']) ? 'edit_expense_categories' : 'add_expense_categories'; ?>">
                    <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
                $("#expense-category-form").validate({
                            rules: {
                                name: {
                                    required: true,
                                    minlength: 2
                                }
                            },
                            messages: {
                                name: {
                                    required: "Please enter a name.",
                                    minlength: "Your name must be at least 2 characters long."
                                }
                            }
                        });
                    });
</script>