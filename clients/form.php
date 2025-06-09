<div class="card shadow-sm">
    <form method="POST" name="client-form" id="client-form" class="p-4" enctype="multipart/form-data" novalidate>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control"
                    name="name"
                    id="name"
                    required
                    minlength="2"
                    value="<?php echo isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>"
                    placeholder="Enter client name" />
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    name="email"
                    id="email"
                    value="<?php echo isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?>"
                    placeholder="Enter email address" />
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">Phone Number</label>
                <input
                    type="tel"
                    class="form-control"
                    name="phone"
                    id="phone"
                    pattern="\d{10}"
                    maxlength="10"
                    value="<?php echo isset($row['phone']) ? htmlspecialchars($row['phone']) : ''; ?>"
                    placeholder="10-digit phone number" />
            </div>

            <div class="col-md-6">
                <label for="cname" class="form-label">Company Name</label>
                <input
                    type="text"
                    class="form-control"
                    name="cname"
                    id="cname"
                    value="<?php echo isset($row['cname']) ? htmlspecialchars($row['cname']) : ''; ?>"
                    placeholder="Enter company name" />
            </div>

            <!-- Address moved to the end with full width -->
            <div class="col-12">
                <label for="address" class="form-label">Address</label>
                <textarea
                    class="form-control"
                    name="address"
                    id="address"
                    rows="3"
                    placeholder="Enter address"><?php echo isset($row['address']) ? htmlspecialchars($row['address']) : ''; ?></textarea>
            </div>
        </div>

        <input type="hidden" name="client_id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>" />

        <div class="mt-4">
            <button
                type="submit"
                class="btn btn-primary"
                name="<?php echo isset($row['id']) ? 'edit_client' : 'add_client'; ?>">
                <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#client-form').validate({
            rules: {
                name: "required"
            },
            messages: {
                name: "Please enter client name"
            },
            errorElement: 'div',
            errorClass: 'invalid-feedback',
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