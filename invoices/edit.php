<?php
ob_start();
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit-invoices'])) {
        $id = intval($_GET['id']);
        
        // --- Invoice fields ---
        $invoiceId = $conn->real_escape_string($_POST['invoiceId']);
        $invoiceDate = $conn->real_escape_string($_POST['invoiceDate']);
        $billedByName = $conn->real_escape_string($_POST['billedByName']);
        $billedByPan = $conn->real_escape_string($_POST['billedByPan']);
        $billedByAddress = $conn->real_escape_string($_POST['billedByAddress']);
        $billedToName = $conn->real_escape_string($_POST['billedToName']);
        $billedToPan = $conn->real_escape_string($_POST['billedToPan']);
        $billedToAddress = $conn->real_escape_string($_POST['billedToAddress']);

        // --- Update the invoice record ---
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

        mysqli_query($conn, $sql);

        // --- Update invoice items ---
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $existingItemIds = [];

            // Loop through each item in the posted data
            foreach ($_POST['items'] as $item) {
                $title = $conn->real_escape_string($item['title']);
                $hours = floatval($item['hours']);
                $rate = floatval($item['rate']);
                $itemId = isset($item['id']) ? intval($item['id']) : null;

                if ($itemId) {
                    // Update existing item
                    $existingItemIds[] = $itemId;
                    $updateItem = "UPDATE invoice_items 
                                   SET task_title = '$title', hours = $hours, rate = $rate 
                                   WHERE id = $itemId AND invoice_id = $id";
                    mysqli_query($conn, $updateItem);
                } else {
                    // Insert new item if no ID exists
                    $insertItem = "INSERT INTO invoice_items (invoice_id, task_title, hours, rate) 
                                   VALUES ($id, '$title', $hours, $rate)";
                    mysqli_query($conn, $insertItem);
                }
            }

            // Delete items that are not in the POST data (removed items)
            if (!empty($existingItemIds)) {
                $idsToKeep = implode(',', $existingItemIds);
                $deleteQuery = "DELETE FROM invoice_items 
                                WHERE invoice_id = $id AND id NOT IN ($idsToKeep)";
                mysqli_query($conn, $deleteQuery);
            } else {
                // If no items exist, delete all
                $deleteAll = "DELETE FROM invoice_items WHERE invoice_id = $id";
                mysqli_query($conn, $deleteAll);
            }
        }

        // Redirect after success
        header("Location: index.php?updated=1");
        exit;
    }
}


// Fetch invoice + items
$invoice = [];
if (isset($_GET['id'])) {
    $invoiceId = intval($_GET['id']);
    $sql = "SELECT * FROM invoices WHERE id = $invoiceId";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $invoice = $result->fetch_assoc();
        $invoiceStrId = $invoice['invoice_id'];

        $itemsSql = "SELECT * FROM invoice_items WHERE invoice_id = '$invoiceStrId'";
        $itemsResult = $conn->query($itemsSql);
        if ($itemsResult && $itemsResult->num_rows > 0) {
            $invoice['invoice_items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>

<!-- Alert message -->
<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success" id="update-success-msg">Invoice updated successfully.</div>
<?php endif; ?>

<script>
    setTimeout(() => {
        document.getElementById('update-success-msg')?.remove();
    }, 3000);
</script>

<!-- Title & Back Button -->
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

<!-- Load items into JS -->
<script>
    var invoiceItems = <?php echo json_encode($invoice['invoice_items'] ?? []); ?>;
</script>

<?php include './form.php'; ?>
<?php require_once '../includes/footer.php'; ?>