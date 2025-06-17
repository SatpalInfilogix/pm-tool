<?php require_once '../includes/header.php';
$userRole = $userProfile['role']; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Invoices</h4>
            <a href="./create.php" class="btn btn-primary d-flex">
                <i class="bx bx-plus me-1 fs-5"></i>Add Invoice
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success" id="update-success-msg">Invoice updated successfully.</div>
<?php elseif (isset($_GET['created']) && $_GET['created'] == 1): ?>
    <div class="alert alert-success" id="update-success-msg">Invoice created successfully.</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive"> <!-- Added for responsiveness -->
            <table id="invoiceTable" class="table table table-bordered table-stripedd">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice ID</th>
                        <th>Date</th>
                        <th>Billed By</th>
                        <th>Billed To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM invoices ORDER BY invoice_date DESC";
                    $result = $conn->query($sql);

                    $invoices = $result->fetch_all(MYSQLI_ASSOC);
                    $count = 1;
                    foreach ($invoices as $row) {
                        echo "<tr>";
                        echo "<td>" . $count++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['invoice_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['invoice_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['billed_by_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['billed_to_client_company_name']) . "</td>";
                        echo "<td>";
                        echo "<a href='./edit.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'><i class='bx bx-edit fs-5'></i></a> ";

                        if ($userRole === 'admin' || $userRole === 'hr') {
                            echo "<button class='btn btn-danger btn-sm delete-btn' data-table-name='invoices' data-id='" . htmlspecialchars($row['id']) . "'><i class='bx bx-trash fs-5'></i></button> ";
                        }

                        echo "<a href='./download.php?id=" . $row['id'] . "' class='btn btn-success btn-sm'><i class='bx bx-download fs-5'></i></a>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    ?>
                </tbody>

            </table>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#invoiceTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthMenu": [10, 25, 50, 100],
            "autoWidth": false
        });
        setTimeout(() => {
            document.getElementById('update-success-msg')?.remove();
        }, 3000);
    });
</script>


<?php require_once '../includes/footer.php'; ?>