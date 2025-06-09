<div class="card shadow-sm rounded-3 p-3 mb-4">
    <div class="card-body">
        <form method="POST" name="leave-form" id="leave-form" class="row g-3" enctype="multipart/form-data" novalidate>
            <!-- Leave Type -->
            <?php if ($userRole === 'employee'): ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <!-- Leave Type -->
                        <label for="leave_type" class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="leave_type" name="leave_type" required>
                            <option value="" selected disabled>Select Leave Type</option>
                            <?php
                            $types = ['casual', 'maternity', 'paternity'];
                            foreach ($types as $type) {
                                $selected = (isset($row['leave_type']) && $row['leave_type'] === $type) ? 'selected' : '';
                                echo "<option value=\"$type\" $selected>" . ucfirst($type) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <!-- Start Date -->
                        <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="start_date" id="start_date" required autocomplete="off"
                            value="<?php echo $row['start_date'] ?? ''; ?>" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="col-md-4">
                        <!-- End Date -->
                        <label for="end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="end_date" id="end_date" required autocomplete="off"
                            value="<?php echo $row['end_date'] ?? ''; ?>" placeholder="YYYY-MM-DD">
                    </div>
                </div>
            <?php else: ?>
                <!-- Leave Type and Status side by side -->
                <div class="col-md-6">
                    <label for="leave_type" class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="leave_type" name="leave_type" required>
                        <option value="" selected disabled>Select Leave Type</option>
                        <?php
                        $types = ['casual', 'maternity', 'paternity'];
                        foreach ($types as $type) {
                            $selected = (isset($row['leave_type']) && $row['leave_type'] === $type) ? 'selected' : '';
                            echo "<option value=\"$type\" $selected>" . ucfirst($type) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Status for HR/Admin -->
                <?php if ($userRole === 'admin' || $userRole === 'hr'): ?>
                    <div class="col-md-6">
                        <label for="leave-status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <?php $currentStatus = $row['status'] ?? 'pending'; ?>
                        <select class="form-select" id="leave-status" name="status" required>
                            <option value="" disabled <?php echo $currentStatus == '' ? 'selected' : ''; ?>>Select Status</option>
                            <option value="pending" <?php echo $currentStatus == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $currentStatus == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $currentStatus == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Start Date and End Date side by side -->
                <div class="col-md-6">
                    <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="start_date" id="start_date" required autocomplete="off"
                        value="<?php echo $row['start_date'] ?? ''; ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="end_date" id="end_date" required autocomplete="off"
                        value="<?php echo $row['end_date'] ?? ''; ?>" placeholder="YYYY-MM-DD">
                </div>
            <?php endif; ?>

            <!-- Reason -->
            <div class="col-12">
                <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" name="reason" id="reason" rows="3" required placeholder="Enter reason..."><?php echo $row['reason'] ?? ''; ?></textarea>
            </div>

            <input type="hidden" name="employee_id" value="<?php echo $employee_id ?? ''; ?>">

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary px-4" name="<?php echo isset($row['id']) ? 'edit_leave' : 'add_leave'; ?>">
                    <?php echo isset($row['id']) ? 'Update' : 'Submit'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#start_date, #end_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        // Restrict end_date to be >= start_date
        $('#start_date').on('changeDate', function(e) {
            var startDate = e.format('yyyy-mm-dd');
            $('#end_date').datepicker('setStartDate', startDate);

            var endDate = $('#end_date').val();
            if (endDate && endDate < startDate) {
                $('#end_date').val('');
            }
        });

        // Your existing select2 and validation code below...
        if ($('#leave-status').length) {
            $('#leave-status').select2({
                width: '100%'
            });
        }

        $('#leave_type').select2({
            width: '100%'
        });

        // validation rules ...

        $('#leave-form').validate({
            rules: {
                leave_type: {
                    required: true
                },
                status: {
                    required: true
                },
                start_date: {
                    required: true,
                    date: true
                },
                end_date: {
                    required: true,
                    date: true
                },
                reason: {
                    required: true
                }
            },
            messages: {
                leave_type: {
                    required: "Please select a leave type."
                },
                status: {
                    required: "Please select a status."
                },
                start_date: {
                    required: "Enter start date.",
                    date: "Enter a valid date."
                },
                end_date: {
                    required: "Enter end date.",
                    date: "Enter a valid date."
                },
                reason: {
                    required: "Enter a reason for the leave."
                }
            },
            errorPlacement: function(error, element) {
                if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).next('.select2').find('.select2-selection').addClass('is-invalid');
                } else {
                    $(element).addClass('is-invalid');
                }
            },
            unhighlight: function(element) {
                if ($(element).hasClass('select2-hidden-accessible')) {
                    $(element).next('.select2').find('.select2-selection').removeClass('is-invalid');
                } else {
                    $(element).removeClass('is-invalid');
                }
            }
        });
    });
</script>