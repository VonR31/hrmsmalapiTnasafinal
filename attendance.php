<?php
session_start(); // Start the session

// Check if the user is not logged in, redirect them to the sign-in page
if (!isset($_SESSION['userId'])) {
  header("Location: index.php");
  exit();
}

include 'connect.php';

// Retrieve user information from session variables
$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
$employeeId = $_SESSION['employeeId'];

$attendanceRecords = [];


// Fetch all attendance records along with employee details from the database
$selectQuery = "
    SELECT 
        a.attendanceId, 
        u.employeeId, 
        u.firstName, 
        u.lastName, 
        u.email, 
        u.positionName, 
        u.departmentName, 
        a.date, 
        a.checkInTime, 
        a.checkOutTime, 
        a.status 
    FROM 
        attendance a 
    JOIN 
        employee u 
    ON 
        a.employeeId = u.employeeId";

$result = $conn->query($selectQuery);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $attendanceRecords[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0" />
  <title>HR Management System</title>

  <link rel="shortcut icon" href="assets/img/icon.png" />
  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css" />
  <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css" />
  <link rel="stylesheet" href="assets/plugins/fullcalendar/fullcalendar.min.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
</head>

<body>
  <div class="main-wrapper">
    <div class="header">
      <div class="header-left">
        <a href="dashboard.php" class="logo">
          <img src="assets/img/hrlogo.png" alt="Logo" />
        </a>
        <a href="dashboard.php" class="logo logo-small">
          <img src="assets/img/hrlogo-small.png" alt="Logo" width="30" height="30" />
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
            <a class="dropdown-item" href="profile.php"><i data-feather="user" class="mr-1"></i> Profile</a>
            <a class="dropdown-item" href="settings.php"><i data-feather="settings" class="mr-1"></i> Settings</a>
            <a class="dropdown-item" href="logout.php" onclick="return confirmLogout();"><i data-feather="log-out"
                class="mr-1"></i> Logout</a>
          </div>
        </li>
      </ul>
      <div class="dropdown mobile-user-menu show">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i
            class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
          <a class="dropdown-item" href="profile.php">My Profile</a>
          <a class="dropdown-item" href="settings.php">Settings</a>
          <a class="dropdown-item" href="logout.php">Logout</a>
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
                  <span class="lnr lnr-cross text-white" id="mobile_btn_close">X</span>
                  <a href="javascript:void(0)" class="d-block menu-style text-white">
                    <div class="user-avatar d-inline-block mr-3">
                      <img src="assets/img/user.jpg" alt="user avatar" class="rounded-circle" width="50" />
                    </div>
                  </a>
                </div>
              </div>
              <div class="sidebar-input">
                <div class="top-nav-search">
                  <form>
                    <input type="text" class="form-control" placeholder="Search here" />
                    <button class="btn" type="submit">
                      <i class="fas fa-search"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <ul>
              <li>
                <a href="dashboard.php"><img src="assets/img/home.svg" alt="sidebar_img" />
                  <span>Dashboard</span></a>
              </li>
              <li>
                <a href="employee.php"><img src="assets/img/employee.svg" alt="sidebar_img" /><span>
                    Employees</span></a>
              </li>
              <li>
                <a href="company.php"><img src="assets/img/company.svg" alt="sidebar_img" />
                  <span> Departments</span></a>
              </li>
              <li class="active">
                <a href="attendance.php"><img src="assets/img/calendar.svg" alt="sidebar_img" />
                  <span>Attendance</span></a>
              </li>
              <li>
                <a href="leave.php"><img src="assets/img/leave.svg" alt="sidebar_img" />
                  <span>Leave</span></a>
              </li>
              <li>
                <a href="profile.php"><img src="assets/img/profile.svg" alt="sidebar_img" />
                  <span>Profile</span></a>
              </li>
            </ul>
            <ul class="logout">
              <li>
                <a href="logout.php" onclick="return confirmLogout();"><img src="assets/img/logout.svg"
                    alt="sidebar_img"><span>Log out</span></a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="page-wrapper calendar_page">
      <div class="content container-fluid">
        <div class="row">
          <div class="col-xl-12 col-sm-12 col-12 mb-4">
            <div class="breadcrumb-path">
              <ul class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="/home/dashboard.html"><img src="./assets/img/dash.png" class="mr-2"
                      alt="breadcrumb" />Home</a>
                </li>
                <li class="breadcrumb-item active">Attendance</li>
              </ul>
              <h3>Attendance</h3>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12 col-lg-8 col-md-12">
            <div class="card">
              <div class="card-body">
                <h2 class="page-title">Employee Attendance Records</h2>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped" id="tableEmployee">
                    <thead>
                      <tr>
                        <th>Attendance ID</th>
                        <th>Employee ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Check-in Time</th>
                        <th>Check-out Time</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($attendanceRecords) > 0): ?>
                        <?php foreach ($attendanceRecords as $record): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($record['attendanceId']); ?></td>
                            <td><?php echo htmlspecialchars($record['employeeId']); ?></td>
                            <td><?php echo htmlspecialchars($record['firstName']); ?></td>
                            <td><?php echo htmlspecialchars($record['lastName']); ?></td>
                            <td><?php echo htmlspecialchars($record['email']); ?></td>
                            <td><?php echo htmlspecialchars($record['positionName']); ?></td>
                            <td><?php echo htmlspecialchars($record['departmentName']); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo htmlspecialchars($record['checkInTime']); ?></td>
                            <td><?php echo htmlspecialchars($record['checkOutTime']); ?></td>
                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <td colspan="11">No attendance records found.</td>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>





          <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
          <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
          <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

          <script>
            $(document).ready(function () {
              $('#tableEmployee').DataTable({
                "dom": '<"top"lf>rt<"bottom"ip><"clear">'
              });
            });

            function confirmLogout() {
              return confirm("Are you sure you want to log out?");
            }
          </script>

          <script src="/assets/js/popper.min.js"></script>
          <script src="/assets/js/bootstrap.min.js"></script>
          <script src="/assets/js/feather.min.js"></script>
          <script src="/assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
          <script src="/assets/js/script.js"></script>
</body>

</html>