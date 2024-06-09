<?php
session_start(); // Start the session

// Check if the user is not logged in, redirect them to the sign-in page
if (!isset($_SESSION['userId'])) {
  header("Location: index.php");
  exit();
}
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['changePassword'])) {
  $currentPassword = $_POST['currentPassword'];
  $newPassword = $_POST['newPassword'];

  // Verify current password
  $verifyQuery = "SELECT password FROM user WHERE userId = ?";
  $stmt = $conn->prepare($verifyQuery);
  $stmt->bind_param("i", $_SESSION['userId']);
  $stmt->execute();
  $stmt->bind_result($storedPassword);
  $stmt->fetch();
  $stmt->close();

  // Check if current password matches
  if ($currentPassword === $storedPassword) {
    // Update password
    $updatePasswordQuery = "UPDATE user SET password = ? WHERE userId = ?";
    $stmt = $conn->prepare($updatePasswordQuery);
    $stmt->bind_param("si", $newPassword, $_SESSION['userId']);
    if ($stmt->execute()) {
      $passwordSuccessMessage = "Password changed successfully.";
    } else {
      $passwordErrorMessage = "Error changing password.";
    }
    $stmt->close();
  } else {
    $passwordErrorMessage = "Current password is incorrect.";
  }
}

// Retrieve user information from session variables
$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];
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
              <a class="dropdown-item" href="logout.php" onclick="return confirmLogout();"><i data-feather="log-out" class="mr-1"></i> Logout</a>
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
                <li>
                  <a href="leave.php"
                    ><img src="assets/img/leave.svg" alt="sidebar_img" />
                    <span>Leave</span></a
                  >
                </li>
                <li class="active">
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
            <div class="col-xl-12 col-sm-12 col-12">
              <div class="breadcrumb-path mb-4">
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
                  <li class="breadcrumb-item active">Profile</li>
                </ul>
                <h3>Profile</h3>
              </div>
            </div>
            <div class="col-xl-12 col-sm-12 col-12 mb-4">
              <div class="head-link-set">
                <ul>
                  <li><a href="profile.php">Details</a></li>
                  <li><a class="active" href="#">Settings</a></li>
                </ul>
              </div>
            </div>
            <div class="col-xl-12 col-sm-12 col-12 mb-4">
              <div class="row">
                <div class="col-xl-6 col-sm-12 col-12 d-flex">
                  <div class="card flex-fill">
                    <div class="card-header">
                      <h2 class="card-titles">
                        Change Password<span
                          >Your password needs to be at least 8 characters
                          long.</span
                        >
                      </h2>
                    </div>
                    <div class="card-body">
                      <?php if (isset($passwordSuccessMessage)): ?>
                        <div class="alert alert-success" role="alert">
                          <?php echo $passwordSuccessMessage; ?>
                        </div>
                      <?php elseif (isset($passwordErrorMessage)): ?>
                        <div class="alert alert-danger" role="alert">
                          <?php echo $passwordErrorMessage; ?>
                        </div>
                      <?php endif; ?>
                      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <div class="form-group">
                          <label for="currentPassword">Current Password</label>
                          <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                        </div>
                        <div class="form-group">
                          <label for="newPassword">New Password</label>
                          <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        </div>
                        <input type="submit" class="btn btn-primary btn-block" name="changePassword" value="Change Password">
                      </form>
                    </div>
                  </div>
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

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/js/script.js"></script>
  </body>
</html>
