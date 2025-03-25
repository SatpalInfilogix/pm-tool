<?php
require_once __DIR__ . '/db.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo isset($page_title) ? $page_title : 'PM Tool'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico">
    <link href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="<?php echo BASE_URL; ?>/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo BASE_URL; ?>/assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/libs/datatable/dataTables.dataTables.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/libs/select2/css/select2.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
    <link href="<?php echo BASE_URL; ?>/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo BASE_URL; ?>/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo BASE_URL; ?>/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo BASE_URL; ?>/assets/libs/summernote/summernote-bs4.min.css" rel="stylesheet">
    <script src="<?php echo BASE_URL; ?>/assets/libs/jquery/jquery.min.js"></script>
</head>
<body data-sidebar="dark">
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <div class="navbar-brand-box">
                        <a href="<?php echo BASE_URL; ?>" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?php echo BASE_URL; ?>/assets/images/small-logo.png" alt="" height="32">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="" height="17">
                            </span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="<?php echo BASE_URL; ?>/assets/images/small-logo.png" alt="" height="32">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="" height="32">
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
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-bell bx-tada"></i>
                            <span class="badge bg-danger rounded-pill">3</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0" key="t-notifications"> Notifications </h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="#!" class="small" key="t-view-all"> View All</a>
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar style="max-height: 230px;">
                                <a href="javascript: void(0);" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-primary rounded-circle font-size-16">
                                                <i class="bx bx-cart"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" key="t-your-order">Your order is placed</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1" key="t-grammer">If several languages coalesce the grammar</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">3 min ago</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="javascript: void(0);" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <img src="<?php echo BASE_URL; ?>/assets/images/default-user.png"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">James Lemire</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1" key="t-simplified">It will seem like simplified English.</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago">1 hours ago</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="javascript: void(0);" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-success rounded-circle font-size-16">
                                                <i class="bx bx-badge-check"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" key="t-shipped">Your item is shipped</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1" key="t-grammer">If several languages coalesce the grammar</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">3 min ago</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="javascript: void(0);" class="text-reset notification-item">
                                    <div class="d-flex">
                                        <img src="<?php echo BASE_URL; ?>/assets/images/default-user.png"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Salena Layfield</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1" key="t-occidental">As a skeptical Cambridge friend of mine occidental.</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-hours-ago">1 hours ago</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2 border-top d-grid">
                                <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> <span key="t-view-more">View More..</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="<?php echo BASE_URL; ?>/assets/images/default-user.png"
                                alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1">Henry</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span>Profile</span></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span>Logout</span></a>
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
                            <a href="<?php echo BASE_URL; ?>" class="waves-effect">
                                <i class="bx bx-home"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-title">Projects</li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/projects" class="waves-effect">
                                <i class="bx bx-rocket"></i>
                                <span>Projects</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/milestones" class="waves-effect">
                                <i class="bx bx-target-lock"></i>
                                <span>Milestones</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/clients" class="waves-effect">
                                <i class="bx bx-user"></i>
                                <span>Clients</span>
                            </a>
                        </li>


                        <li class="menu-title">HR</li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/employees" class="waves-effect">
                                <i class="bx bx-user"></i>
                                <span>Employees</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/holidays" class="waves-effect">
                                <i class="bx bx-calendar"></i>
                                <span>Holidays</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-stats"></i>
                                <span>Expenses</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="<?php echo BASE_URL; ?>/expense-categories/">Expense Categories</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/expenses/">Expenses</a></li>
                            </ul>
                        </li>
                        <li class="menu-title">Administrator</li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bxs-report"></i>
                                <span>Reports</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="#">Expense Report</a></li>
                                <li><a href="#">Invoice Report</a></li>
                                <li><a href="#">Project Report</a></li>
                                <li><a href="#">Employee Report</a></li>
                                <li><a href="#">Attendance Report</a></li>
                                <li><a href="#">Leave Report</a></li>
                                <li><a href="#">Daily Report</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="waves-effect">
                                <i class="bx bx-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">