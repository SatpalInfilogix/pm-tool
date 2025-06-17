<?php
ob_start();
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['billing_setting'])) {
    $billedByName = $conn->real_escape_string(trim($_POST['billedByName']));
    $billedByPan = $conn->real_escape_string(trim($_POST['billedByPan']));
    $billedByAddress = $conn->real_escape_string(trim($_POST['billedByAddress']));

    $settingsToUpdate = [
        'billed_by_name' => $billedByName,
        'billed_by_pan' => $billedByPan,
        'billed_by_address' => $billedByAddress
    ];

    foreach ($settingsToUpdate as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
        $stmt->close();
    }
    echo '<div class="alert alert-success">Billing Settings saved successfully.</div>';
}

$settings = [
    'billed_by_name' => '',
    'billed_by_pan' => '',
    'billed_by_address' => '',
];

$result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('billed_by_name', 'billed_by_pan', 'billed_by_address')");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded">
                <div class="card-header text-dark">
                    <h5 class="mb-0">Billing Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <h6 class="mb-3"><strong>Billed By Details</strong></h6>

                        <div class="mb-3">
                            <label for="billedByName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="billedByName" name="billedByName"
                                value="<?= htmlspecialchars($settings['billed_by_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="billedByPan" class="form-label">PAN Code</label>
                            <input type="text" class="form-control" id="billedByPan" name="billedByPan"
                                value="<?= htmlspecialchars($settings['billed_by_pan']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="billedByAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="billedByAddress" name="billedByAddress" rows="3"><?= htmlspecialchars($settings['billed_by_address']) ?></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success" name="billing_setting">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>