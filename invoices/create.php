<?php
ob_start();
require_once '../includes/header.php';
$user_values = userProfile();

$result = $conn->query("SELECT id FROM invoices ORDER BY id DESC LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    $nextId = $row['id'] + 1;
    $invoicev['id'] = 'INV-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
} else {
    $invoicev['id'] = 'INV-00001';
}

if (isset($_POST['add-invoices'])) {
    // ... your existing update code for invoices ...

    $conn->begin_transaction();

    try {
        // (2) Get invoice data from POST and sanitize
        $invoiceId = $conn->real_escape_string($_POST['invoiceId']);
        $invoiceDate = $conn->real_escape_string($_POST['invoiceDate']);
        $billedToName = $conn->real_escape_string($_POST['billedToName']);
        $billedToPan = $conn->real_escape_string($_POST['billedToPan']);
        $billedToAddress = $conn->real_escape_string($_POST['billedToAddress']);
        $billedByName = $conn->real_escape_string($_POST['billedByName']);
        $billedByPan = $conn->real_escape_string($_POST['billedByPan']);
        $billedByAddress = $conn->real_escape_string($_POST['billedByAddress']);

        // (3) Insert or Update invoice
        $checkExisting = $conn->query("SELECT id FROM invoices WHERE invoice_id = '$invoiceId'");
        if ($checkExisting->num_rows > 0) {
            // Update existing invoice
            $updateInvoiceSql = "UPDATE invoices SET 
        invoice_date = '$invoiceDate',
        billed_to_client_company_name = '$billedToName',
        billed_to_pan = '$billedToPan',
        billed_to_address = '$billedToAddress',
        billed_by_name = '$billedByName',
        billed_by_pan = '$billedByPan',
        billed_by_address = '$billedByAddress'
        WHERE invoice_id = '$invoiceId'";
            if (!$conn->query($updateInvoiceSql)) {
                throw new Exception("Error updating invoice: " . $conn->error);
            }
        } else {
            // Insert new invoice
            $insertInvoiceSql = "INSERT INTO invoices 
        (invoice_id, invoice_date, billed_to_client_company_name, billed_to_pan, billed_to_address,
         billed_by_name, billed_by_pan, billed_by_address) 
        VALUES (
            '$invoiceId', '$invoiceDate', '$billedToName', '$billedToPan', '$billedToAddress',
            '$billedByName', '$billedByPan', '$billedByAddress'
        )";
            if (!$conn->query($insertInvoiceSql)) {
                throw new Exception("Error inserting invoice: " . $conn->error);
            }
        }

        // (3) Get existing item IDs from DB
        $existingItemIds = [];
        $existingItemsQuery = $conn->query("SELECT id FROM invoice_items WHERE invoice_id = '$invoiceId'");
        while ($row = $existingItemsQuery->fetch_assoc()) {
            $existingItemIds[] = $row['id'];
        }

        // (4) Track submitted item IDs
        $submittedItemIds = [];

        // (5) Loop through posted items
        if (!empty($_POST['items'])) {
            foreach ($_POST['items'] as $item) {
                $itemId = isset($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : null;
                $title = $conn->real_escape_string($item['title']);
                $hours = $conn->real_escape_string($item['hours']);
                $rate = $conn->real_escape_string($item['rate']);

                if ($itemId) {
                    // Update existing item
                    $submittedItemIds[] = $itemId;
                    $updateSql = "UPDATE invoice_items SET 
                                    task_title = '$title', 
                                    hours = '$hours', 
                                    rate = '$rate' 
                                  WHERE id = $itemId AND invoice_id = '$invoiceId'";
                    if (!$conn->query($updateSql)) {
                        throw new Exception("Error updating item ID $itemId: " . $conn->error);
                    }
                } else {
                    // Insert new item
                    $insertSql = "INSERT INTO invoice_items (invoice_id, task_title, hours, rate) 
                                  VALUES ('$invoiceId', '$title', '$hours', '$rate')";
                    if (!$conn->query($insertSql)) {
                        throw new Exception("Error inserting new item: " . $conn->error);
                    }
                }
            }
        }

        // (6) Delete removed items
        $toDelete = array_diff($existingItemIds, $submittedItemIds);
        if (!empty($toDelete)) {
            $deleteIds = implode(',', array_map('intval', $toDelete));
            $deleteSql = "DELETE FROM invoice_items WHERE id IN ($deleteIds) AND invoice_id = '$invoiceId'";
            if (!$conn->query($deleteSql)) {
                throw new Exception("Error deleting items: " . $conn->error);
            }
        }

        $conn->commit();
        header("Location: " . BASE_URL . "/invoices/index.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add New Invoice</h4>
            <a href="./index.php" class="btn btn-primary d-flex">
                <i class="bx bx-left-arrow-alt me-1 fs-4"></i>Go Back
            </a>
        </div>
    </div>
</div>

<div class="card">
    <?php include './form.php'; ?>
</div>

<?php require_once '../includes/footer.php'; ?>