<?php
require_once '../includes/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=holidays_export.csv');

$output = fopen("php://output", "w");
fputcsv($output, ['Index', 'Name', 'Date', 'Description', 'Type']);

$query = "SELECT * FROM holidays";
$result = mysqli_query($conn, $query);

$index = 1;
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $index++,
        $row['name'],
        $row['date'],
        $row['description'],
        $row['type']
    ]);
}
fclose($output);
exit;
