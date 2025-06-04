<?php
require_once '../includes/db.php';

// Get logged-in user info from session
$userProfile = $_SESSION['user'] ?? null;
$userRole = $userProfile['role'] ?? '';
$currentUserId = (int)($userProfile['id'] ?? 0);

$statusFilter = $_GET['status'] ?? '';
$dateRange = $_GET['dateRange'] ?? '';

// For admin/hr allow employee filter from GET, else force current user ID
if (in_array($userRole, ['admin', 'hr'])) {
    $employeeFilter = $_GET['employee'] ?? '';
} else {
    $employeeFilter = $currentUserId;
}

$filters = [];
$query = "SELECT a.date, u.name AS employee_name, a.in_time, a.out_time, a.note, u.id AS employee_id
          FROM attendance a
          JOIN users u ON a.employee_id = u.id";

// Employee filter
if (!empty($employeeFilter)) {
    $employeeId = (int)$employeeFilter;
    $filters[] = "u.id = $employeeId";
}

// Date range filter
if (!empty($dateRange) && strpos($dateRange, ' to ') !== false) {
    [$startDate, $endDate] = explode(' to ', $dateRange);
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    $filters[] = "a.date BETWEEN '$startDate' AND '$endDate'";
}

if (!empty($filters)) {
    $query .= ' WHERE ' . implode(' AND ', $filters);
}

$query .= " ORDER BY a.date DESC, u.name ASC";

$result = mysqli_query($conn, $query);

// Output CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_export.csv');

$output = fopen("php://output", "w");

// CSV Header
fputcsv($output, ['Index', 'Date', 'Employee Name', 'Status', 'In Time', 'Out Time', 'Worked Hours', 'Note']);

$index = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $inTime = strtotime($row['in_time']);
    $outTime = strtotime($row['out_time']);
    $isInTimeValid = $inTime !== false && $row['in_time'] !== null && $row['in_time'] !== '00:00:00';
    $isOutTimeValid = $outTime !== false && $row['out_time'] !== null && $row['out_time'] !== '00:00:00';

    $derivedStatus = 'Absent';
    $workedHours = '-';

    if ($isInTimeValid && $isOutTimeValid) {
        $seconds = $outTime - $inTime;
        if ($seconds > 0) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $workedHours = "{$hours}h {$minutes}m";

            if ($hours >= 8) {
                $derivedStatus = "Present";
            } elseif ($hours >= 6) {
                $derivedStatus = "Short Leave";
            } elseif ($hours >= 3) {
                $derivedStatus = "Half Day";
            } else {
                $derivedStatus = "Absent";
            }
        }
    }

    // Apply status filter after deriving status
    $normalizedStatus = strtolower(str_replace(' ', '_', $derivedStatus));
    if (empty($statusFilter) || $normalizedStatus === $statusFilter) {
        fputcsv($output, [
            $index++,
            $row['date'],
            $row['employee_name'],
            $derivedStatus,
            $isInTimeValid ? date('h:i A', $inTime) : '',
            $isOutTimeValid ? date('h:i A', $outTime) : '',
            $workedHours,
            $row['note']
        ]);
    }
}

fclose($output);
exit;
