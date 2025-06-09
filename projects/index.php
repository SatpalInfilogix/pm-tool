      <?php require_once '../includes/header.php';
        $userId = userProfile()['id'];
        $userRole = userProfile()['role'];
        ?>
      <div class="row">
          <div class="col-12">
              <div class="page-title-box  pb-3 d-sm-flex align-items-center justify-content-between">
                  <h4 class="mb-sm-0 font-size-18">Projects</h4>
                  <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>
                      <a href="./create.php" class="btn btn-primary d-flex"><i class="bx bx-plus me-1 fs-5"> </i>Add Project</a>
                  <?php } else  ?>

              </div>
          </div>
      </div>
      <div class="card">
          <div class="card-body">
              <?php
                if ($userRole == 'employee') {
                    $sql = "SELECT p.*, GROUP_CONCAT(e.name SEPARATOR ', ') AS assigned_employees
        FROM projects p 
        LEFT JOIN employee_projects ep ON p.id = ep.project_id 
        LEFT JOIN users e ON ep.employee_id = e.id 
        WHERE ep.employee_id = '$userId' AND e.role = 'employee'
        GROUP BY p.id
        ORDER BY p.id DESC";  // <-- latest projects first
                } else {
                    $sql = "SELECT p.*, GROUP_CONCAT(u.name SEPARATOR ', ') AS assigned_employees
        FROM projects p 
        LEFT JOIN employee_projects ep ON p.id = ep.project_id 
        LEFT JOIN users u ON ep.employee_id = u.id 
        GROUP BY p.id
        ORDER BY p.id DESC";  // <-- latest projects first
                }


                $query = mysqli_query($conn, $sql);
                $projects = mysqli_fetch_all($query, MYSQLI_ASSOC);
                ?>
              <table class="table table-sm" id="employeeTable">
                  <thead>
                      <th>#</th>
                      <th>Project Name</th>
                      <th>Assigned Employees</th>
                      <th>Start Date</th>
                      <th>Due Date</th>
                      <th>Type</th>
                      <th>Action</th>

                  </thead>
                  <tbody>
                      <?php
                        foreach ($projects as $key => $row) {
                        ?>
                          <tr>
                              <td><?php echo  $key + 1 ?></td>
                              <td><?php echo $row['name'] ?></td>
                              <td><?php echo $row['assigned_employees'] ?: 'N/A'; ?></td>
                              <td><?php echo $row['start_date'] ?></td>
                              <td><?php echo $row['due_date'] ?></td>
                              <td>
                                  <span class="badge bg-<?php echo ($row['type'] == 'fixed') ? 'success' : (($row['type'] == 'hourly') ? 'warning' : 'secondary'); ?>">
                                      <?php echo ucfirst(str_replace('_', ' ', $row['type'])); ?>
                                  </span>
                              </td>
                              <td>
                                  <a href='./add-status.php?id=<?php echo $row['id'] ?>' class="btn btn-success btn-sm">Add Status</a>

                                  <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>

                                      <a href='./edit.php?id=<?php echo $row['id'] ?>' class="btn btn-primary btn-sm"><i class="bx bx-edit fs-5"></i></a>
                                      <button class="btn btn-danger delete-btn btn-sm" data-table-name="projects" data-id="<?php echo $row['id'] ?>"><i class="bx bx-trash fs-5"></i></button>
                              </td>
                          <?php } else { ?>
                          <?php  } ?>
                      <?php } ?>
                  </tbody>

          </div>
      </div>
      </table>
      <script>
          $(document).ready(function() {
              $('#employeeTable').DataTable({
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