<div class="card shadow-sm p-4">
    <form method="POST" name="holiday-form" id="holiday-form" enctype="multipart/form-data" novalidate>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="name" required minlength="2"
                    value="<?php echo isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>"
                    placeholder="Enter holiday name" />
            </div>
            <div class="col-md-6">
                <label for="date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="date" id="date" required
                    value="<?php echo isset($row['date']) ? htmlspecialchars($row['date']) : ''; ?>"
                    placeholder="YYYY-MM-DD" autocomplete="off" />
            </div>
        </div>

        <div class="row g-3 align-items-center mb-3">
            <div class="col-md-3">
                <label for="type" class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select class="form-select text-uppercase" name="type" id="type" required>
                    <option value="" disabled <?php echo (!isset($row['type']) || $row['type'] == '') ? 'selected' : ''; ?>>Select Type</option>
                    <option value="Public" <?php echo (isset($row['type']) && $row['type'] == 'Public') ? 'selected' : ''; ?>>Public</option>
                    <option value="Company" <?php echo (isset($row['type']) && $row['type'] == 'Company') ? 'selected' : ''; ?>>Company</option>
                    <option value="Regional" <?php echo (isset($row['type']) && $row['type'] == 'Regional') ? 'selected' : ''; ?>>Regional</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-center mt-4 mt-md-0">
                <div class="form-check">
                    <input type="hidden" name="recurring" value="0" />
                    <input class="form-check-input" type="checkbox" id="recurring" name="recurring" value="1"
                        <?php echo isset($row['recurring']) && $row['recurring'] ? 'checked' : ''; ?> />
                    <label class="form-check-label fw-semibold ms-2" for="recurring">
                        <i class="bi bi-check-square"></i> Every Year
                    </label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" name="description" id="description" rows="5"
                placeholder="Enter holiday description"><?php echo isset($row['description']) ? htmlspecialchars($row['description']) : ''; ?></textarea>
        </div>

        <input type="hidden" name="id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>" />

        <button type="submit" class="btn btn-primary" name="<?php echo isset($row['id']) ? 'edit-holiday' : 'add_holiday'; ?>">
            <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
        </button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();

        $("#date").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        $('select[name="type"]').select2({
            width: '100%',
            placeholder: 'Select Type',
            allowClear: true,
            minimumResultsForSearch: -1 // disables search box
        });

        $('#description').summernote({
            height: 150,
            toolbar: [
                // customize toolbar if needed
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });

        $('#holiday-form').validate({
            rules: {
                name: "required",
                date: "required",
                type: "required",
                description: "required"
            },
            messages: {
                name: "Please enter holiday name",
                date: "Please select a date",
                type: "Please select a holiday type",
                description: "Please enter a description"
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function(element) {
                $(element).addClass("is-invalid");
                if ($(element).hasClass("form-select")) {
                    $(element).next('.select2-container').find('.select2-selection').addClass('is-invalid');
                }
            },
            unhighlight: function(element) {
                $(element).removeClass("is-invalid");
                if ($(element).hasClass("form-select")) {
                    $(element).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                }
            },
            errorPlacement: function(error, element) {
                if (element.hasClass("form-select")) {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $('select[name="type"]').on('change', function() {
            $(this).valid();
        });

        $("#recurring").on("change", function() {
            if ($(this).is(":checked")) {
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });
    });
</script>