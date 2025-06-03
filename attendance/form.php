<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isset($_POST['submit_attendance'])) {
    $date = $_POST['date'];
    $employee_ids = $_POST['employee_id'];
    $notes = $_POST['note'];
    $in_times = $_POST['in_time'];
    $out_times = $_POST['out_time'];

    if (!empty($date) && is_array($employee_ids)) {
        foreach ($employee_ids as $index => $employee_id) {
            $note = mysqli_real_escape_string($conn, $notes[$index]);

            $in_time_raw = trim($in_times[$index]);
            $out_time_raw = trim($out_times[$index]);

            $in_time_24 = ($in_time_raw === '') ? '00:00:00' : date('H:i:s', strtotime($in_time_raw));
            $out_time_24 = ($out_time_raw === '') ? '00:00:00' : date('H:i:s', strtotime($out_time_raw));

            // Check duplicate
            $checkQuery = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = '$date'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) == 0) {
                $insertQuery = "INSERT INTO attendance (employee_id, note, date, in_time, out_time)
                                VALUES ($employee_id, '$note', '$date', '$in_time_24', '$out_time_24')";
                mysqli_query($conn, $insertQuery);
            } else {
                $updateQuery = "UPDATE attendance SET 
                                note = '$note', 
                                in_time = '$in_time_24',
                                out_time = '$out_time_24'
                                WHERE employee_id = $employee_id AND date = '$date'";
                mysqli_query($conn, $updateQuery);
            }
        }
        header("Location: index.php");
        exit();
    }
}

require_once '../includes/header.php';

// Get user list based on role
if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') {
    $usersQuery = "SELECT id, name FROM users";
} else {
    $userId = $userProfile['id'];
    $usersQuery = "SELECT id, name FROM users WHERE id = $userId";
}

$result = mysqli_query($conn, $usersQuery);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$attendanceRecords = [];

$attendanceQuery = "SELECT * FROM attendance WHERE date = '$selectedDate'";
$attendanceResult = mysqli_query($conn, $attendanceQuery);

while ($row = mysqli_fetch_assoc($attendanceResult)) {
    $attendanceRecords[$row['employee_id']] = $row;
}
?>

<!-- Your form HTML below this point -->


<form method="post" id="attendance-form">
    <div class="col-md-2">
        <div class="mb-3">
            <label for="date">Date:</label>
            <input type="text" class="form-control" name="date" id="date" required
                value="<?php echo htmlspecialchars($selectedDate); ?>" autocomplete="off">

        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="container">
                <table class="table table-sm" id="attendanceTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <!-- <th>Status</th> -->
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $key => $user):
                            $empId = $user['id'];
                            $record = $attendanceRecords[$empId] ?? null;

                            $in_time = $record ? date('h:i A', strtotime($record['in_time'])) : '';
                            $out_time = $record ? date('h:i A', strtotime($record['out_time'])) : '';
                            $note = $record['note'] ?? '';
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                    <input type="hidden" name="employee_id[]" value="<?php echo $empId; ?>">
                                </td>
                                <td><input type="text" class="form-control timepicker" name="in_time[]" placeholder="HH:MM AM/PM" value="<?php echo htmlspecialchars($in_time); ?>"></td>
                                <td><input type="text" class="form-control timepicker" name="out_time[]" placeholder="HH:MM AM/PM" value="<?php echo htmlspecialchars($out_time); ?>"></td>
                                <td><input type="text" class="form-control" name="note[]" placeholder="Optional note" value="<?php echo htmlspecialchars($note); ?>"></td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary" name="submit_attendance">Save</button>
            </div>
        </div>
    </div>
</form>
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css" />
<script src="https://unpkg.com/flatpickr"></script>

<script>
    $(document).ready(function() {
        // Flatpickr for date input with onChange
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            disable: [
                function(date) {
                    // Disable weekends (Sunday = 0, Saturday = 6)
                    return date.getDay() === 0 || date.getDay() === 6;
                }
            ],
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('date', dateStr);
                    window.location.href = url.toString(); // reload page with new date param
                }
            }
        });

        // Flatpickr for time input only (12-hour format with AM/PM)
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K", // 12-hour format
            time_24hr: false,
        });

        // Your jQuery validation stays as is
        $('#attendance-form').validate({
            rules: {
                'date': "required",
            },
            messages: {
                'date': "Please select a date",
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