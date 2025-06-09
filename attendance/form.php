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

            $in_time_24 = ($in_time_raw === '') ? null : date('H:i:s', strtotime($in_time_raw));

            $out_time_24 = null;
            if ($out_time_raw === '') {
                // Check existing out_time if left empty
                $checkQuery = "SELECT out_time FROM attendance WHERE employee_id = $employee_id AND date = '$date'";
                $checkResult = mysqli_query($conn, $checkQuery);
                if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                    $row = mysqli_fetch_assoc($checkResult);
                    $out_time_24 = $row['out_time'];
                } else {
                    $out_time_24 = '00:00:00';
                }
            } else {
                $out_time_24 = date('H:i:s', strtotime($out_time_raw));
            }

            // Check if record exists
            $checkQuery = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = '$date'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) == 0) {
                // Insert new
                $insertQuery = "INSERT INTO attendance (employee_id, note, date, in_time, out_time)
                                VALUES ($employee_id, '$note', '$date', '$in_time_24', '$out_time_24')";
                mysqli_query($conn, $insertQuery);
            } else {
                // Update existing
                $updateFields = "note = '$note'";
                if ($in_time_raw === '') {
                    $updateFields .= ", in_time = NULL";
                } else {
                    $updateFields .= ", in_time = '$in_time_24'";
                }

                if ($out_time_raw === '') {
                    $updateFields .= ", out_time = NULL";
                } else {
                    $updateFields .= ", out_time = '$out_time_24'";
                }


                $updateQuery = "UPDATE attendance SET $updateFields
                                WHERE employee_id = $employee_id AND date = '$date'";
                mysqli_query($conn, $updateQuery);
            }
        }

        header("Location: index.php");
        exit();
    }
}
require_once '../includes/header.php';


if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') {
    $usersQuery = "SELECT id, name FROM users WHERE role != 'admin'";
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

<form method="post" id="attendance-form">
    <div class="page-title-box pb-4 d-sm-flex align-items-center justify-content-between">
        <h4>Add Attendance</h4>
    </div>
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
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $key => $user):
                            $empId = $user['id'];
                            $record = $attendanceRecords[$empId] ?? null;

                            $in_time_raw = $record['in_time'] ?? '00:00:00';
                            $out_time_raw = $record['out_time'] ?? '00:00:00';

                            $in_time_display = ($in_time_raw && $in_time_raw !== '00:00:00') ? date('h:i A', strtotime($in_time_raw)) : '';
                            $out_time_display = ($out_time_raw && $out_time_raw !== '00:00:00') ? date('h:i A', strtotime($out_time_raw)) : '';
                            $note = $record['note'] ?? '';

                            // Calculate status and total hours
                            $statusLabel = 'Absent';
                            $badgeClass = 'danger';
                            $workedHours = '-';

                            $in_time = ($in_time_raw !== '00:00:00') ? strtotime($in_time_raw) : false;
                            $out_time = ($out_time_raw !== '00:00:00') ? strtotime($out_time_raw) : false;

                            if ($in_time && $out_time) {
                                if ($out_time >= $in_time) {
                                    $seconds = $out_time - $in_time;
                                    $hours = floor($seconds / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    $workedHours = "{$hours}h {$minutes}m";

                                    if ($hours >= 8) {
                                        $statusLabel = "Present";
                                        $badgeClass = 'success';
                                    } elseif ($hours >= 6) {
                                        $statusLabel = "Short Leave";
                                        $badgeClass = 'secondary';
                                    } elseif ($hours >= 3) {
                                        $statusLabel = "Half Day";
                                        $badgeClass = 'info';
                                    } else {
                                        $statusLabel = "Absent";
                                        $badgeClass = 'danger';
                                    }
                                } else {
                                    $statusLabel = "Invalid Time";
                                    $badgeClass = 'warning';
                                    $workedHours = "-";
                                }
                            } elseif ($in_time && !$out_time) {
                                $statusLabel = "In Progress";
                                $badgeClass = "primary";
                            }
                        ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                    <input type="hidden" name="employee_id[]" value="<?php echo $empId; ?>">
                                </td>
                                <td><input type="text" class="form-control timepicker" name="in_time[]" placeholder="HH:MM AM/PM" value="<?php echo htmlspecialchars($in_time_display); ?>"></td>
                                <td><input type="text" class="form-control timepicker" name="out_time[]" placeholder="HH:MM AM/PM" value="<?php echo htmlspecialchars($out_time_display); ?>"></td>
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

<!-- Flatpickr Assets -->
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css" />
<script src="https://unpkg.com/flatpickr"></script>

<!-- jQuery validation and flatpickr logic -->
<script>
    $(document).ready(function() {
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            disable: [
                function(date) {
                    return date.getDay() === 0 || date.getDay() === 6;
                }
            ],
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('date', dateStr);
                    window.location.href = url.toString();
                }
            }
        });

        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            time_24hr: false,
        });

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
<?php require_once '../includes/footer.php'; ?>
