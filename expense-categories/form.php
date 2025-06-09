<div class="card shadow-sm rounded-3">
    <div class="card-body">
        <h5 class="mb-4 text-primary text-center">Expense Category</h5>

        <form method="POST" action="" name="expense-category-form" id="expense-category-form" novalidate>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control rounded-2"
                            name="name"
                            id="name"
                            required
                            minlength="2"
                            value="<?php echo isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>"
                            placeholder="Enter category name">
                    </div>

                    <input type="hidden" name="expense_categories_id"
                        value="<?php echo isset($row['id']) ? htmlspecialchars($row['id']) : ''; ?>">
                </div>
            </div>

            <div class="mb-3 text-start">
                <button type="submit" class="btn btn-primary px-4 fw-semibold"
                    name="<?php echo isset($row['id']) ? 'edit_expense_categories' : 'add_expense_categories'; ?>">
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
            },
            errorClass: 'invalid-feedback',
            errorElement: 'div',
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
            errorPlacement: function(error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2'));
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
</script>