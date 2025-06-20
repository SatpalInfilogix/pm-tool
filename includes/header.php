<?php
require_once 'db.php';
require_once __DIR__ . '/functions.php';

if (!isAuth()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$userProfile = userProfile();
$userId = $userProfile['id'];


$sql = "SELECT * FROM notifications";
if ($userProfile['role'] == 'admin' || $userProfile['role'] == 'hr') {
    $sql .= ' ORDER BY created_at DESC';
} else {
    $sql .= ' WHERE user_id="' . $userProfile['id'] . '" ORDER BY created_at DESC';
}

$result = $conn->query($sql);
// Get user notifications
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Fetch live settings
$logoPath = getSetting('site_logo') ?? 'uploads/my_logo.png';
$mobLogoPath = getSetting('site_small_logo') ?? 'uploads/my_logo.png';
$favicon_path = getSetting('site_favicon') ?? 'assets/images/default-favicon.ico';
$page_title = getSetting('site_title') ?? 'PM Tool';

// Add cache-buster to force image refresh
$cacheBuster = '?v=' . time();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" href="<?= BASE_URL . '/settings/' . $favicon_path . $cacheBuster ?>" type="image/x-icon">

    <!-- CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/css/icons.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/libs/bootstrap-daterangepicker/css/daterangepicker.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/libs/select2/css/select2.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
    <link href="<?= BASE_URL ?>/assets/libs/summernote/summernote-bs4.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/libs/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet">

    <!-- JS -->
    <script src="<?= BASE_URL ?>/assets/libs/jquery/jquery.min.js"></script>
</head>

<body data-sidebar="dark">
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <div class="navbar-brand-box">
                        <a href="<?= BASE_URL ?>" class="logo">
                            <span class="logo-sm">
                                <img src="<?= BASE_URL . '/settings/' . $mobLogoPath . $cacheBuster ?>" id="mobile-logo" alt="Small Logo" height="32">
                            </span>
                            <span class="logo-lg">
                                <img src="<?= BASE_URL . '/settings/' . $logoPath . $cacheBuster ?>" alt="Logo" id="web-logo" height="32">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                </div>
                <div class="d-flex">
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                            <i class="bx bx-fullscreen"></i>
                        </button>
                    </div>

                    <?php
                    // Fetch notifications for dropdown with unread count
                    function getUnreadNotificationsCount($userProfile)
                    {
                        global $conn;
                        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND read_status = 0");
                        $stmt->bind_param("i", $userProfile['id']);

                        $count = 0;
                        if ($stmt && $stmt->execute()) {
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $count = $row['cnt'] ?? 0;
                        }

                        return $count;
                    }


                    function getNotifications($userProfile)
                    {
                        global $conn;
                        $notifications = [];

                        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $userProfile['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $notifications = $result->fetch_all(MYSQLI_ASSOC);

                        return $notifications;
                    }



                    function getLatestNotifications($userProfile, $limit = 5)
                    {
                        global $conn;
                        $notifications = [];

                        // Always show only notifications for the logged-in user
                        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
                        $stmt->bind_param("ii", $userProfile['id'], $limit);

                        if ($stmt && $stmt->execute()) {
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $notifications[] = $row;
                            }
                        }

                        return $notifications;
                    }


                    $unreadCount = getUnreadNotificationsCount($userProfile);
                    $latestNotifications = getLatestNotifications($userProfile, 5);
                    ?>


                    <style>
                        .unread-notification {
                            background-color: rgba(13, 110, 253, 0.08);
                            /* Soft primary bg */
                            border-left: 4px solid #0d6efd;
                        }

                        .unread-notification h6 {
                            color: rgba(0, 0, 0, 0.74);
                            font-weight: 600;
                        }

                        .notification-item {
                            padding: 1px 1px;
                        }

                        .notification-item:hover {
                            background-color: #f8f9fa;
                        }

                        .new-badge {
                            background-color: #0d6efd;
                            color: white;
                            font-size: 10px;
                            padding: 2px 6px;
                            border-radius: 10px;
                            margin-left: 6px;
                        }
                    </style>



                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-bell bx-tada"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0" key="t-notifications">Notification</h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="<?php echo BASE_URL; ?>/notifications.php" class="btn btn-sm btn-link font-size-14 text-center">View More</a>
                                    </div>
                                </div>
                            </div>

                            <?php foreach ($latestNotifications as $noti): ?>
                                <?php
                                $link = $noti['link'] ?? '#';

                                if ($userProfile['role'] === 'employee' && strpos($noti['message'], 'assigned to a new project') !== false) {
                                    $link = BASE_URL . '/projects/index.php';
                                } else {
                                    if (strpos($link, 'http') === 0) {
                                        // full URL
                                    } elseif (strpos($link, '/') === 0) {
                                        $link = BASE_URL . $link;
                                    } else {
                                        $link = BASE_URL . '/' . $link;
                                    }
                                }
                                ?>
                                <a href="<?php echo BASE_URL . '/notification-redirect.php?noti_id=' . $noti['id']; ?>"
                                    class="text-reset notification-item d-block <?php echo $noti['read_status'] == 0 ? 'unread-notification' : ''; ?>">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-info text-white rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 30px; height: 30px;">
                                                <i class="bx bxs-bell"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($noti['message']); ?>
                                                <?php if ($noti['read_status'] == 0): ?>
                                                    <span class="new-badge">NEW</span>
                                                <?php endif; ?>
                                            </h6>
                                            <div class="font-size-12 text-muted">
                                                <i class="mdi mdi-clock-outline"></i>
                                                <?php echo htmlspecialchars($noti['created_at']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>


                            <?php if (count($latestNotifications) >= 5): ?>
                                <div class="p-2 border-top d-grid">
                                    <a class="btn btn-sm btn-link font-size-14 text-center" href="<?php echo BASE_URL; ?>/notifications.php">
                                        <i class="mdi mdi-arrow-right-circle me-1"></i> <span key="t-view-more">View More..</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>






                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="<?php echo BASE_URL . '/';
                                                                                    echo $userProfile['profile_pic'] ?? 'assets/images/default-user.png'; ?>"
                                alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1"><?php echo userProfile()['name']; ?></span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile.php"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span>Profile</span></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span>Logout</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">Menu</li>
                        <li>
                            <a href="<?php echo BASE_URL ?>/index.php" class="waves-effect">
                                <i class="bx bx-home"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="menu-title">Daily Report</li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/daily-report/index.php" class="waves-effect">
                                <i class="bx bxs-report"></i>
                                <span>Daily Report</span>
                            </a>
                        </li>
                        <li class="menu-title">Projects</li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/projects/index.php" class="waves-effect">
                                <i class="bx bx-rocket"></i>
                                <span>Projects</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/milestones/index.php" class="waves-effect">
                                <i class="bx bx-target-lock"></i>
                                <span>Milestones</span>
                            </a>
                        </li>
                        <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>

                            <li>
                                <a href="<?php echo BASE_URL; ?>/clients/index.php" class="waves-effect">
                                    <i class="bx bx-user"></i>
                                    <span>Clients</span>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/invoices/index.php" class="waves-effect">
                                <i class="fas fa-file"></i>
                                <span>Invoice</span>
                            </a>
                        </li>
                        <li class="menu-title">HR</li>
                        <?php
                        if (isset($userProfile['role']) && ($userProfile['role'] == 'hr' || $userProfile['role'] == 'admin')) {
                        ?>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/employees/index.php" class="waves-effect">
                                    <i class="bx bx-user"></i>
                                    <span>Employees</span>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/holidays/index.php" class="waves-effect">
                                <i class="bx bx-calendar"></i>
                                <span>Holidays</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/attendance/index.php" class="waves-effect">
                                <i class="bx bx-calendar-check"></i>
                                <span>Attendance</span>
                            </a>
                        </li>
                        <?php
                        if (isset($userProfile['role']) && ($userProfile['role'] == 'hr' || $userProfile['role'] == 'admin')) {
                        ?>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-stats"></i>
                                    <span>Expenses</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="<?php echo BASE_URL; ?>/expense-categories//index.php">Expense Categories</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/expenses/">Expenses</a></li>
                                </ul>
                            </li>
                        <?php
                        }
                        ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/leaves/index.php" class="waves-effect">
                                <i class="mdi mdi-login"></i>
                                <span>leaves</span>
                            </a>
                        </li>
                        <li class="menu-title">Administrator</li>

                        <li>
                            <a href="<?php echo BASE_URL; ?>/policy/index.php" class="waves-effect">
                                <i class="bx bx-note"></i>
                                <span>Policies</span>
                            </a>
                        </li>
                        <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr') { ?>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bxs-report"></i>
                                    <span>Reports</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="<?php echo BASE_URL; ?>/reports/expense-report.php">Expense Report</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/reports/attendance-report.php">Attendance Report</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/reports/leaves-report.php">Leave Report</a></li>
                                </ul>
                            </li>
                        <?php } ?>
                        <?php if ($userProfile['role'] === 'admin') { ?>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-cog"></i>
                                    <span>Settings</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="<?php echo BASE_URL; ?>/settings/site-setting.php">Site Setting</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/settings/billing-setting.php">Billing</a></li>
                                </ul>
                            </li>
                        <?php } ?>
                        <?php if ($userProfile['role'] === 'admin' || $userProfile['role'] === 'hr' || $userProfile['role'] === 'team leader') { ?>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-question-mark"></i>
                                    <span>Interview</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="<?php echo BASE_URL; ?>/add-question.php">Interview Questions</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/submitted-answers.php">Submitted Answers</a></li>
                                </ul>
                            </li>
                        <?php } ?>

                    </ul>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">