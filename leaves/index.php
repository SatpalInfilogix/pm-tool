<?php require_once '../includes/header.php';
$userProfile = userProfile();
$userId = $userProfile['id'];
$userRole = $userProfile['role'];
?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Leaves</h4>
            <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Apply Leave</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php
        if (in_array($userRole, ['admin', 'hr'])) {
            // Admins and HR can see all leaves
            $leavesQuery = "SELECT leaves.*, users.name AS employee_name
                    FROM leaves
                    JOIN users ON users.id = leaves.employee_id";
        } else {
            // Employees see only their own leaves
            $leavesQuery = "SELECT leaves.*, users.name AS employee_name
                    FROM leaves
                    JOIN users ON users.id = leaves.employee_id
                    WHERE leaves.employee_id = $userId";
        }

        $leavesResult = mysqli_query($conn, $leavesQuery);
        $leaves = [];
        while ($row = mysqli_fetch_assoc($leavesResult)) {
            $leaves[] = $row;
        }
        ?>
        <table class="table" id="leavesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                        <th>Name</th>
                    <?php endif; ?>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <?php
                    $userProfile = userProfile();
                    $userId = $userProfile['id'];
                    $userRole = $userProfile['role'];
                    ?>
                    <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($leaves as $key => $row): ?>
                    <tr>
                        <td><?php echo $key + 1 ?></td>
                        <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                            <td><?php echo $row['employee_name'] ?></td>
                        <?php endif; ?>
                        <td><?php echo $row['leave_type'] ?></td>
                        <td><?php echo $row['start_date'] ?></td>
                        <td><?php echo $row['end_date'] ?></td>
                        <td>
                            <?php
                            $status = ucfirst(strtolower(trim($row['status'])));
                            $badgeClass = match ($status) {
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                'Pending' => 'secondary',
                                default => 'dark'
                            };
                            ?>
                            <span class="badge bg-<?php echo $badgeClass; ?>">
                                <?php echo $status; ?>
                            </span>

                        </td>

                        <td><?php echo $row['reason'] ?></td>
                        <?php if (in_array($userRole, ['admin', 'hr'])): ?>
                            <td>
                                <a href='./edit.php?id=<?php echo $row['id'] ?>' class="btn btn-primary btn-sm"><i class="bx bx-edit fs-5"></i></a>
                                <button class="btn btn-danger btn-sm delete-btn" data-table-name="leaves" data-id="<?php echo $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>


        </table>

    </div>
</div>

<script>
    $(document).ready(function() {
        $('#leavesTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthMenu": [10, 25, 50, 100],
            "autoWidth": false
        });
    });
</script>
<?php require_once '../includes/footer.php'; ?>