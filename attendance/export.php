<?php
require_once '../includes/db.php';

$statusFilter = $_GET['status'] ?? '';
$employeeFilter = $_GET['employee_id'] ?? '';
$dateRange = $_GET['dateRange'] ?? '';
$validStatuses = ['present', 'absent', 'late', 'half_day', 'short_leave'];

$filters = [];
$query = "SELECT a.date, u.name AS employee_name, a.status, a.note 
          FROM attendance a 
          JOIN users u ON a.employee_id = u.id";

// Employee filter by ID
if (!empty($employeeFilter)) {
    $employeeId = (int)$employeeFilter;
    $filters[] = "u.id = $employeeId";
}

// Status filter
if (!empty($statusFilter) && in_array($statusFilter, $validStatuses)) {
    $filters[] = "a.status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}

// Date range filter
if (!empty($dateRange) && strpos($dateRange, ' to ') !== false) {
    [$startDate, $endDate] = explode(' to ', $dateRange);
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    $filters[] = "a.date BETWEEN '$startDate' AND '$endDate'";
}

// Apply filters to the query
if (!empty($filters)) {
    $query .= ' WHERE ' . implode(' AND ', $filters);
}

$query .= " ORDER BY a.date DESC, u.name ASC";

$result = mysqli_query($conn, $query);

// Output CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_export.csv');

$output = fopen("php://output", "w");
fputcsv($output, ['Index', 'Date', 'Employee Name', 'Status', 'Note']);

$index = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $index++,
        $row['date'],
        $row['employee_name'],
        ucfirst(str_replace('_', ' ', $row['status'])),
        $row['note']
    ]);
}

fclose($output);
exit;

