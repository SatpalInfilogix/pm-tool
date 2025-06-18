<form method="POST" name="employee-form" id="employee-form" enctype="multipart/form-data">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" placeholder="Enter full name" required minlength="2"
                value="<?php echo isset($row['name']) ? $row['name'] : ''; ?>">
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" placeholder="example@domain.com" autocomplete="off"
                value="<?php echo isset($row['email']) ? $row['email'] : ''; ?>" required>
        </div>

        <div class="col-md-6">
            <label for="phoneno" class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="phoneno" placeholder="10-digit mobile number"
                value="<?php echo isset($row['phone_number']) ? $row['phone_number'] : ''; ?>"
                required pattern="\d{10}">
        </div>

        <div class="col-md-6">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-select" name="gender" required>
                <option value="" disabled <?php echo !isset($row['gender']) ? 'selected' : ''; ?>>Select Gender</option>
                <option value="Male" <?php echo (isset($row['gender']) && $row['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($row['gender']) && $row['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
            <textarea class="form-control" name="address" id="address" placeholder="Enter full address" required><?php echo isset($row['address']) ? $row['address'] : ''; ?></textarea>
        </div>

        <div class="col-md-3">
            <label for="role" class="form-label">Role</label>
            <?php $role = isset($row['role']) ? $row['role'] : ''; ?>
            <select name="role" id="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="hr" <?php echo ($role === 'hr') ? 'selected' : ''; ?>>HR</option>
                <option value="team leader" <?php echo ($role === 'team leader') ? 'selected' : ''; ?>>Team Leader</option>
                <option value="employee" <?php echo ($role === 'employee') ? 'selected' : ''; ?>>Employee</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="jobt" class="form-label">Job Title</label>
            <select class="form-select" name="jobt">
                <option value="" disabled selected>Select a Job Title</option>
                <option value="PHP Developer" <?php echo (isset($row['job_title']) && $row['job_title'] == 'PHP Developer') ? 'selected' : ''; ?>>PHP Developer</option>
                <option value="Frontend Developer" <?php echo (isset($row['job_title']) && $row['job_title'] == 'Frontend Developer') ? 'selected' : ''; ?>>Frontend Developer</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="assigned_leader_id" class="form-label">Assign Team Leader</label>
            <select class="form-select select2" name="assigned_leader_id" id="assigned_leader_id" style="width: 100%;">
                <option value="">Select Team Leader (optional)</option>
                <?php
                $result = $conn->query("SELECT id, name FROM users WHERE role = 'team leader'");
                if ($result && $result->num_rows > 0):
                    while ($leader = $result->fetch_assoc()):
                        $selected = (isset($row['assigned_leader_id']) && $row['assigned_leader_id'] == $leader['id']) ? 'selected' : '';
                ?>
                        <option value="<?= $leader['id'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($leader['name']) ?>
                        </option>
                <?php endwhile;
                else:
                    echo '<
                             disabled>No team leaders found</option>';
                endif;
                ?>
            </select>
        </div>


        <div class="col-md-3">
            <label for="dob" class="form-label">Date Of Birth <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="dob" id="dob" placeholder="YYYY-MM-DD"
                value="<?php echo isset($row['date_of_birth']) ? $row['date_of_birth'] : ''; ?>" required autocomplete="off">
        </div>

        <div class="col-md-3">
            <label for="doj" class="form-label">Date Of Joining <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="doj" id="doj" placeholder="YYYY-MM-DD"
                value="<?php echo isset($row['date_of_joining']) ? $row['date_of_joining'] : ''; ?>" required autocomplete="off">
        </div>

        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" required>
                <option value="Active" <?php echo (isset($row['status']) && $row['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo (isset($row['status']) && $row['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="Terminated" <?php echo (($row['status'] ?? '') == 'terminated') ? 'selected' : ''; ?>>Terminated</option>
            </select>
        </div>

        <?php $isEdit = isset($_GET['id']); ?>
        <div class="col-md-3">
            <label for="password" class="form-label">Password
                <?php if (!$isEdit): ?><span class="text-danger">*</span><?php endif; ?>
            </label>
            <input type="password" class="form-control" name="password" id="password"
                <?= !$isEdit ? 'required' : '' ?>
                placeholder="<?= $isEdit ? 'Leave blank to keep current password' : 'Enter password' ?>" autocomplete="new-password">
        </div>
    </div>

    <input type="hidden" name="employee_id" value="<?php echo isset($row['id']) ? $row['id'] : ''; ?>">

    <div class="mt-4">
        <button type="submit" class="btn btn-primary"
            name="<?php echo isset($row['id']) ? 'edit-employee' : 'add_employee'; ?>">
            <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
        </button>
    </div>
</form>




<script>
    $(document).ready(function() {
        // Initialize Select2 for all select fields
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true
        });

        // Initialize datepickers
        $('#dob, #doj').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            endDate: '0d'
        });

        // Enable/disable team leader based on role
        $('select[name="role"]').on('change', function() {
            var role = $(this).val();
            var leaderSelect = $('select[name="assigned_leader_id"]');

            if (role === 'employee') {
                leaderSelect.prop('disabled', false).trigger('change.select2');
                $('select[name="jobt"]').prop('required', true);
            } else {
                leaderSelect.val('').prop('disabled', true).trigger('change.select2');
                $('select[name="jobt"]').prop('required', false);
            }
        }).trigger('change');
    });

    // Validation
    $('#employee-form').validate({
    rules: {
        name: "required",
        email: {
            required: true,
            email: true
        },
        phoneno: {
            required: true,
            minlength: 10,
            maxlength: 10,
            digits: true
        },
        address: "required",
        dob: "required",
        doj: "required",
        jobt: {
            required: function() {
                return $('select[name="role"]').val() === 'employee';
            }
        },
        password: {
            <?php if (!isset($row['id'])): ?>
                required: true,
            <?php endif; ?>
            minlength: 6
        }
    },
    messages: {
        name: "Please enter employee name",
        email: "Please enter a valid email address",
        phoneno: {
            required: "Please enter a 10-digit phone number",
            minlength: "Phone number must be exactly 10 digits",
            maxlength: "Phone number must be exactly 10 digits",
            digits: "Phone number must contain only digits"
        },
        address: "Please enter address",
        dob: "Please enter Date of Birth",
        doj: "Please enter Date of Joining",
        jobt: "Please select Job Title (required for employees)",
        password: {
            required: "Please enter password",
            minlength: "Password must be at least 6 characters"
        }
    },
    errorPlacement: function(error, element) {
        error.addClass("invalid-feedback");
        if (element.hasClass("form-select")) {
            error.insertAfter(element.next('.select2'));
        } else {
            error.insertAfter(element);
        }
    },
    highlight: function(element) {
        $(element).addClass("is-invalid");
        if ($(element).hasClass("form-select")) {
            $(element).next('.select2').find('.select2-selection').addClass('is-invalid');
        }
    },
    unhighlight: function(element) {
        $(element).removeClass("is-invalid");
        if ($(element).hasClass("form-select")) {
            $(element).next('.select2').find('.select2-selection').removeClass('is-invalid');
        }
    }
    });
 
</script>