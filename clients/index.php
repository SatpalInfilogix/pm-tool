    <?php require_once '../includes/header.php';
    $user_values = userProfile();

    if ($user_values['role'] && ($user_values['role'] !== 'hr' && $user_values['role'] !== 'admin')) {
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/pm-tool';
        $_SESSION['toast'] = "Access denied. Employees only.";
        header("Location: " . $redirectUrl);
        exit();
    }
    ?>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Clients</h4>
                <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>
                    <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Add Client</a>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive"> <!-- Added for responsiveness -->

                <?php
                $sql = "SELECT * FROM clients ORDER BY id DESC";
                $query = mysqli_query($conn, $sql);
                $clients = mysqli_fetch_all($query, MYSQLI_ASSOC);
                ?>
                <div class="container">
                    <table class="table table-sm" id="clientTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Address</th>
                                <th>Company Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $key => $row): ?>
                                <tr>
                                    <td><?= $key + 1; ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['phone']); ?></td>
                                    <td><?= htmlspecialchars($row['address']); ?></td>
                                    <td><?= isset($row['cname']) ? htmlspecialchars($row['cname']) : ''; ?></td>
                                    <td>
                                        <a href="./edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="bx bx-edit fs-5"></i>
                                        </a>
                                        <button class="btn btn-danger delete-btn btn-sm" data-table-name="clients" data-id="<?= $row['id']; ?>">
                                            <i class="bx bx-trash fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#clientTable').DataTable({
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