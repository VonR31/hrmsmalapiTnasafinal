<?php
session_start(); // Start the session
include 'connect.php';

// Check if the user is not logged in, redirect them to the sign-in page
if (!isset($_SESSION['userId'])) {
  header("Location: index.php");
  exit();
}

// Retrieve user information from session variables
$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];

$sql = "SELECT * FROM employee_leave";
$result = $conn->query($sql);
$leaveRecords = $result->fetch_all(MYSQLI_ASSOC);

// Handle approve/disapprove actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $leaveId = $_POST['leaveId'];
  $action = $_POST['action'];
  $status = $action == 'approve' ? 'Approved' : 'Disapproved';

  $stmt = $conn->prepare("UPDATE employee_leave SET status = ? WHERE leaveId = ?");
  $stmt->bind_param("si", $status, $leaveId);
  $stmt->execute();
  $stmt->close();

  // Reload the page to reflect changes
  header("Location: leave.php");
  exit();
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=0"
    />
    <title>HR Management System</title>

    <link rel="shortcut icon" href="assets/img/icon.png" />

    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

    <link rel="stylesheet" href="assets/plugins/select2/css/select2.min.css" />

    <link
      rel="stylesheet"
      href="assets/plugins/fontawesome/css/fontawesome.min.css"
    />
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />

    <link rel="stylesheet" href="assets/css/style.css" />
    <!--[if lt IE 9]>
      <script src="assets/js/html5shiv.min.js"></script>
      <script src="assets/js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="main-wrapper">
      <div class="header">
        <div class="header-left">
          <a href="dashboard.php" class="logo">
            <img src="assets/img/hrlogo.png" alt="Logo" />
          </a>
          <a href="dashboard.php" class="logo logo-small">
            <img
              src="assets/img/hrlogo-small.png"
              alt="Logo"
              width="30"
              height="30"
            />
          </a>
          <a href="javascript:void(0);" id="toggle_btn">
            <span class="bar-icon">
              <span></span>
              <span></span>
              <span></span>
            </span>
          </a>
        </div>

        <div class="top-nav-search">
          <form>
            <input type="text" class="form-control" placeholder="" />
            <button class="btn" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>

        <a class="mobile_btn" id="mobile_btn">
          <i class="fas fa-bars"></i>
        </a>

        <ul class="nav user-menu">
          <li class="nav-item dropdown">
            <a href="#" class="dropdown-toggle nav-link pr-0" data-toggle="dropdown">
            <i data-feather="bell"></i> <span class="badge badge-pill"></span>
            </a>
            <div class="dropdown-menu notifications">
              <div class="topnav-dropdown-header">
                <span class="notification-title">Notifications</span>
                <a href="javascript:void(0)" class="clear-noti"> Clear All</a>
              </div>
          </li>

          <li class="nav-item dropdown has-arrow main-drop">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
              <span class="user-img">
                <img src="assets/img/user.jpg" alt="" />
                <span class="status online"></span>
              </span>
              <span><?php echo $firstName . " " . $lastName; ?></span>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="profile.php"
                ><i data-feather="user" class="mr-1"></i> Profile</a
              >
              <a class="dropdown-item" href="settings.php"
                ><i data-feather="settings" class="mr-1"></i> Settings</a
              >
              <a class="dropdown-item" href="index.php"
                ><i data-feather="log-out" class="mr-1"></i> Logout</a
              >
            </div>
          </li>
        </ul>
        <div class="dropdown mobile-user-menu show">
          <a
            href="#"
            class="nav-link dropdown-toggle"
            data-toggle="dropdown"
            aria-expanded="false"
            ><i class="fa fa-ellipsis-v"></i
          ></a>
          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="profile.php">My Profile</a>
            <a class="dropdown-item" href="settings.php">Settings</a>
            <a class="dropdown-item" href="index.php">Logout</a>
          </div>
        </div>
      </div>

      <div class="sidebar" id="sidebar">
        <div class="sidebar-inner slimscroll">
          <div class="sidebar-contents">
            <div id="sidebar-menu" class="sidebar-menu">
              <div class="mobile-show">
                <div class="offcanvas-menu">
                  <div class="user-info align-center bg-theme text-center">
                    <span class="lnr lnr-cross text-white" id="mobile_btn_close"
                      >X</span
                    >
                    <a
                      href="javascript:void(0)"
                      class="d-block menu-style text-white"
                    >
                      <div class="user-avatar d-inline-block mr-3">
                        <img
                          src="assets/img/user.jpg"
                          alt="user avatar"
                          class="rounded-circle"
                          width="50"
                        />
                      </div>
                    </a>
                  </div>
                </div>
                <div class="sidebar-input">
                  <div class="top-nav-search">
                    <form>
                      <input
                        type="text"
                        class="form-control"
                        placeholder="Search here"
                      />
                      <button class="btn" type="submit">
                        <i class="fas fa-search"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
              <ul>
                <li>
                  <a href="dashboard.php"
                    ><img src="assets/img/home.svg" alt="sidebar_img" />
                    <span>Dashboard</span></a
                  >
                </li>
                <li>
                  <a href="employee.php"
                    ><img
                      src="assets/img/employee.svg"
                      alt="sidebar_img"
                    /><span> Employees</span></a
                  >
                </li>
                <li>
                  <a href="company.php"
                    ><img src="assets/img/company.svg" alt="sidebar_img" />
                    <span> Departments</span></a
                  >
                </li>
                <li>
                  <a href="attendance.php"
                    ><img src="assets/img/calendar.svg" alt="sidebar_img" />
                    <span>Attendance</span></a
                  >
                </li>
                <li class="active">
                  <a href="#"
                    ><img src="assets/img/leave.svg" alt="sidebar_img" />
                    <span>Leave</span></a
                  >
                </li>
                <li>
                  <a href="profile.php"
                    ><img src="assets/img/profile.svg" alt="sidebar_img" />
                    <span>Profile</span></a
                  >
                </li>
              </ul>
              <ul class="logout">
                <li>
                  <a href="logout.php" onclick="return confirmLogout();"><img src="assets/img/logout.svg" alt="sidebar_img"><span>Log out</span></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="page-wrapper">
        <div class="content container-fluid">
          <div class="row">
            <div class="col-xl-12 col-sm-12 col-12 mb-4">
              <div class="breadcrumb-path">
                <ul class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="dashboard.php"
                      ><img
                        src="assets/img/dash.png"
                        class="mr-2"
                        alt="breadcrumb"
                      />Home</a
                    >
                  </li>
                  <li class="breadcrumb-item active">Leave</li>
                </ul>
                <h3>Leave</h3>
              </div>
            </div>
          </div>
          <div class="row">
                    <div class="col-xl-12 col-sm-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-titles">Employee Leave Details</h2>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Employee Name</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($leaveRecords as $leave): ?>
                                                        <tr>
                                                        <td><?php echo $leave['employeeId']; ?></td>
                                                        <td><?php echo $leave['firstName'] . ' ' . $leave['lastName']; ?></td>
                                                            <td><?php echo $leave['startDate']; ?></td>
                                                        <td><?php echo $leave['endDate']; ?></td>
                                                            <td><?php echo $leave['leaveType']; ?></td>
                                                        <td><?php echo $leave['status']; ?></td>
                                                            <td>
                                                            <?php if ($leave['status'] == 'Pending'): ?>
                                                                      <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveModal<?php echo $leave['leaveId']; ?>">Approve</button>
                                                                  <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#disapproveModal<?php echo $leave['leaveId']; ?>">Disapprove</button>
                 
                                                       <?php else: ?>
                                                                      <span><?php echo $leave['status']; ?></span>
                                                      <?php endif; ?>
                                                  </td>
                                              </tr>

                                              <!-- Approve Modal -->
                                              <div class="modal fade" id="approveModal<?php echo $leave['leaveId']; ?>" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
                                                  <div class="modal-dialog" role="document">
                                                      <div class="modal-content">
                                                          <div class="modal-header">
                                                              <h5 class="modal-title" id="approveModalLabel">Approve Leave</h5>
                                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                  <span aria-hidden="true">&times;</span>
                                                              </button>
                                                          </div>
                                                          <form method="post" action="leave.php">
                                                              <div class="modal-body">
                                                                  <p>Are you sure you want to approve this leave?</p>
                                                                  <input type="hidden" name="leaveId" value="<?php echo $leave['leaveId']; ?>">
                                                                  <input type="hidden" name="action" value="approve">
                                                              </div>
                                                              <div class="modal-footer">
                                                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                  <button type="submit" class="btn btn-success">Approve</button>
                                                              </div>
                                                          </form>
                                                      </div>
                                                  </div>
                                              </div>

                                              <!-- Disapprove Modal -->
                                              <div class="modal fade" id="disapproveModal<?php echo $leave['leaveId']; ?>" tabindex="-1" role="dialog" aria-labelledby="disapproveModalLabel" aria-hidden="true">
                                                  <div class="modal-dialog" role="document">
                                                      <div class="modal-content">
                                                          <div class="modal-header">
                                                              <h5 class="modal-title" id="disapproveModalLabel">Disapprove Leave</h5>
                                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                  <span aria-hidden="true">&times;</span>
                                                              </button>
                                                          </div>
                                                          <form method="post" action="leave.php">
                                                              <div class="modal-body">
                                                                  <p>Are you sure you want to disapprove this leave?</p>
                                                                  <input type="hidden" name="leaveId" value="<?php echo $leave['leaveId']; ?>">
                                                                  <input type="hidden" name="action" value="disapprove">
                                                              </div>
                                                              <div class="modal-footer">
                                                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                  <button type="submit" class="btn btn-danger">Disapprove</button>
                                                              </div>
                                                          </form>
                                                      </div>
                                                  </div>
                                              </div>

                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                
    <script>
    function confirmLogout() {
      return confirm("Are you sure you want to log out?");
    }
   </script>

    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/js/script.js"></script>
  </body>
</html>
