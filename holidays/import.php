<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, 'r')) !== false) {
        $headerSkipped = false;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Skip the header row
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            // Read each column assuming order: ID, Name, Date, Description, Type
            $id = (int)$data[0]; // ID column
            $name = mysqli_real_escape_string($conn, $data[1]);
            $dateRaw = trim($data[2]);
            $date = date('Y-m-d', strtotime($dateRaw));
            $description = mysqli_real_escape_string($conn, $data[3]);
            $type = ucfirst(strtolower(trim($data[4])));

            // Insert into database (use REPLACE to overwrite if ID already exists)
            $sql = "REPLACE INTO holidays (id, name, date, description, type) 
                    VALUES ($id, '$name', '$date', '$description', '$type')";
            mysqli_query($conn, $sql);
        }
        fclose($handle);
        $_SESSION['toast'] = 'Holiday data imported successfully!';
        header("Location: index.php");
        exit;
    } else {
        echo "Failed to open file.";
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h4 class="mb-3">Import Holidays</h4>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="csv_file" class="form-label">Upload CSV File</label>
            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-success">Import</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>