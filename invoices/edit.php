<?php
ob_start();
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['edit-invoices'])) {
        $id = intval($_GET['id']);
        $invoiceId = $conn->real_escape_string($_POST['invoiceId']);
        $invoiceDate = $conn->real_escape_string($_POST['invoiceDate']);
        $billedByName = $conn->real_escape_string($_POST['billedByName']);
        $billedByPan = $conn->real_escape_string($_POST['billedByPan']);
        $billedByAddress = $conn->real_escape_string($_POST['billedByAddress']);
        $billedToName = $conn->real_escape_string($_POST['billedToName']);
        $billedToPan = $conn->real_escape_string($_POST['billedToPan']);
        $billedToAddress = $conn->real_escape_string($_POST['billedToAddress']);

        $sql = "UPDATE invoices 
                SET invoice_id = '$invoiceId', 
                    invoice_date = '$invoiceDate', 
                    billed_by_name = '$billedByName', 
                    billed_by_pan = '$billedByPan', 
                    billed_by_address = '$billedByAddress', 
                    billed_to_client_company_name = '$billedToName', 
                    billed_to_pan = '$billedToPan', 
                    billed_to_address = '$billedToAddress' 
                WHERE id = $id";

        $result = mysqli_query($conn, $sql);
    }

    $items = $_POST['items'] ?? [];

    foreach ($items as $item) {
        if (!empty($item['id'])) {
            $itemId = intval($item['id']);
            $task_title = $conn->real_escape_string($item['title']);
            $hours = floatval($item['hours']);
            $rate = floatval($item['rate']);

            $sql = "UPDATE invoice_items 
                    SET task_title = '$task_title', 
                        hours = $hours, 
                        rate = $rate
                    WHERE id = $itemId";

            mysqli_query($conn, $sql);
        }
    }

   
    header('Location: ' . BASE_URL . '/invoices/index.php?updated=1');
    exit();
}


if (isset($_GET['id'])) {
    $invoiceId = intval($_GET['id']);
    $sql = "SELECT * FROM invoices WHERE id = '$invoiceId'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $invoice = $result->fetch_assoc();

        $itemsSql = "SELECT * FROM invoice_items WHERE invoice_id = '$invoiceId'";
        $itemsResult = $conn->query($itemsSql);

        if ($itemsResult && $itemsResult->num_rows > 0) {
            $invoice['invoice_items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        echo "<div class='alert alert-danger'>Invoice not found!</div>";
        exit;
    }
}

?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success" id="update-success-msg">
        Invoice updated successfully.
    </div>
<?php endif; ?>

<script>
    setTimeout(() => {
        document.getElementById('update-success-msg')?.remove();
    }, 3000);
</script>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Edit Invoice</h4>
            <a href="./index.php" class="btn btn-primary d-flex">
                <i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back
            </a>
        </div>
    </div>
</div>

<script>
    var invoiceItems = <?php echo json_encode($invoice['invoice_items'] ?? []); ?>;
</script>

<?php include './form.php'; ?>
<?php require_once '../includes/footer.php'; ?>
