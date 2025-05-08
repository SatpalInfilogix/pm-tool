    <?php
    require '../includes/header.php';
    $userProfile = userProfile();
    $userId = $userProfile['id'];
    $userRole = $userProfile['role'];
    $date_filter = isset($_GET['date-filter']) ? $_GET['date-filter'] : date('Y-m-d');
    $date = date('Y-m-d', strtotime($date_filter));


    // Core query
    // Core query
    $sql = "SELECT ps.id, p.name, p.type, p.name AS project_name, ps.chargable_hours, ps.non_chargable_hours, 
                ps.created_at, ps.updated_at, u.name AS employee_name
            FROM project_status ps
            INNER JOIN projects p ON ps.project_id = p.id
            LEFT JOIN employee_projects ep ON p.id = ep.project_id
            LEFT JOIN users u ON ep.employee_id = u.id
            ";

    // Role-based filtering
    if ($userRole === 'team leader' || $userRole === 'employee') {
        $sql .= " WHERE ep.employee_id = '$userId'";
    } elseif ($userRole === 'admin' || $userRole === 'hr') {
        $sql .= " WHERE 1";
    } elseif ($userRole === 'employee') {
        $sql .= " WHERE ps.employee_id = '$userId'";
    }

    // Apply date filter if needed
    if ($date_filter) {
        $sql .= " AND (DATE(ps.created_at) = '$date' OR DATE(ps.updated_at) = '$date')";
    }

    $query = mysqli_query($conn, $sql);

    // Fetch results
    $projects = mysqli_fetch_all($query, MYSQLI_ASSOC);

    ?>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box pb-3 d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Daily Report</h4>
                <div class="col-md-4">
                    <label for="date-filter">Date</label>
                    <input type="text" id="date-filter" name="date-filter" class="form-control"
                        value="<?php echo htmlspecialchars($date_filter); ?>" placeholder="Select Date" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="card p-3">
            <div class="card-body">
                <table class="table table-sm" id="daily-report">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>Employee Name</th>
                            <th>Chargable Hours</th>
                            <th>Non Chargable Hours</th>
                            <th>Created Time</th>
                            <?php if ($userRole === 'admin' || $userRole === 'hr') { ?>
                                <th>Updated Time</th>
                            <?php } ?>
                            <?php if ($userRole === 'admin') { ?>
                                <th>Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $key => $row) { ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                                <td><?php echo $row['chargable_hours']; ?></td>
                                <td>
                                    <?php
                                    echo ($row['type'] === 'hourly') ? $row['non_chargable_hours'] : '-';
                                    ?>
                                </td>

                                <td><?php echo $row['created_at']; ?></td>
                                <?php if ($userRole === 'admin' || $userRole === 'hr') { ?>
                                    <td><?php echo $row['updated_at']; ?></td>
                                <?php } ?>
                                <?php if ($userRole === 'admin') { ?>
                                    <td>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const inputVal = $('#date-filter').val();

            $('#date-filter').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            }).datepicker('setDate', inputVal); // Set datepicker to match input value

            $('#date-filter').on('changeDate', function() {
                const selectedDate = $(this).val();
                window.location.href = '?date-filter=' + selectedDate;
            });

            $('#daily-report').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthMenu: [10, 25, 50, 100],
                autoWidth: false
            });
        });
    </script>


    <?php require '../includes/footer.php'; ?>