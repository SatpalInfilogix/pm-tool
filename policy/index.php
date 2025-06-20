<?php require_once '../includes/header.php';
require_once '../includes/db.php';

$userProfile = userProfile();
$userRole = $userProfile['role']; ?>
<div class="row">
    <div class="col-12">
        <div class="page-title-box pb-2 d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Policies</h4>
            <?php if ($userRole === 'admin' || $userRole === 'hr'): ?>
                <a href="./create.php" class="btn btn-primary">Add Policy</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive"> <!-- Added for responsiveness -->
            <?php
            $sql = "SELECT * FROM policies  ORDER BY id DESC";
            $query = mysqli_query($conn, $sql);
            $num = mysqli_num_rows($query);
            $policies = mysqli_fetch_all($query, MYSQLI_ASSOC);

            ?>

            <table class="table table-bordered table-striped" id="policyTable">
                <thead>
                    <th>#</th>
                    <th>Name</th>
                    <th>Document</th>
                    <th>Description</th>
                    <?php if ($userRole === 'admin' || $userRole === 'hr'): ?>
                        <th>Action</th>
                    <?php endif; ?>

                </thead>
                <tbody>
                    <?php
                    foreach ($policies as $key => $row) {
                    ?>
                        <tr>
                            <td><?php echo  $key + 1 ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>
                                <?php
                                $files = explode(',', $row['file']);
                                foreach ($files as $file) {
                                    $file = trim($file);
                                    if (!empty($file)) {
                                        $fileUrl = htmlspecialchars($file);
                                        $fileName = basename($file);
                                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        echo "<a href='$fileUrl' target='_blank'>$fileName</a><br>";
                                    }
                                }

                                ?>
                            </td>

                            <td><?php echo $row['description'] ?></td>
                            <?php if ($userRole === 'admin' || $userRole === 'hr'): ?>
                                <td>
                                    <a href='./edit.php?id=<?php echo $row['id'] ?>' class="btn btn-primary btn-sm"><i class="bx bx-edit fs-5"></i></a>
                                    <button class="btn btn-danger btn-sm delete-btn" data-table-name="policies" data-id="<?php echo $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>
                                </td>
                            <?php endif; ?>

                        <?php  } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#policyTable').DataTable({
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