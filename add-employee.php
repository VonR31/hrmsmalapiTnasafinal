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

$positions_query = "SELECT * FROM position";
$positions_result = mysqli_query($conn, $positions_query);

// Fetch departments from database
$departments_query = "SELECT * FROM department";
$departments_result = mysqli_query($conn, $departments_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Collect form data
  $empFirstName = $_POST['first_name'];
  $empLastName = $_POST['last_name'];
  $empEmail = $_POST['email'];
  $empPassword = $_POST['password']; // Plain text password
  $empPosition = $_POST['position_name'];
  $empDepartment = $_POST['department_name'];
  $empDateOfHire = $_POST['date_of_hire'];
  $empRole = 'Employee';

  // Start transaction
  $conn->begin_transaction();

  try {
    // Insert data into employee table
    $insert_employee_query = "INSERT INTO employee (firstName, lastName, email, positionName, departmentName, dateOfHire) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_employee = $conn->prepare($insert_employee_query);
    if (!$stmt_employee) {
      throw new Exception("Employee statement preparation failed: " . $conn->error);
    }
    $stmt_employee->bind_param("ssssss", $empFirstName, $empLastName, $empEmail, $empPosition, $empDepartment, $empDateOfHire);
    if (!$stmt_employee->execute()) {
      throw new Exception("Employee insertion failed: " . $stmt_employee->error);
    }
    $employeeId = $stmt_employee->insert_id; // Get the last inserted employee ID

    // Insert data into user table
    $insert_user_query = "INSERT INTO user (firstName, lastName, email, password, roles, employeeId) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_user = $conn->prepare($insert_user_query);
    if (!$stmt_user) {
      throw new Exception("User statement preparation failed: " . $conn->error);
    }
    $stmt_user->bind_param("sssssi", $empFirstName, $empLastName, $empEmail, $empPassword, $empRole, $employeeId);
    if (!$stmt_user->execute()) {
      throw new Exception("User insertion failed: " . $stmt_user->error);
    }

    // Commit transaction
    $conn->commit();

    // Close statements
    $stmt_employee->close();
    $stmt_user->close();

    // Redirect or display success message
    header("Location: employee.php");
    exit();
  } catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
  }
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
            <a
              href="#"
              class="dropdown-toggle nav-link pr-0"
              data-toggle="dropdown"
            >
              <i data-feather="bell"></i> <span class="badge badge-pill"></span>
            </a>
            <div class="dropdown-menu notifications">
              <div class="topnav-dropdown-header">
                <span class="notification-title">Notifications</span>
                <a href="javascript:void(0)" class="clear-noti"> Clear All</a>
              </div>
              <div class="noti-content">
                <ul class="notification-list">
                  <li class="notification-message">
                    <a href="activities.php">
                      <div class="media">
                        <span class="avatar avatar-sm">
                          <img
                            class="avatar-img rounded-circle"
                            alt=""
                            src="assets/img/profiles/avatar-02.jpg"
                          />
                        </span>
                </ul>
              </div>
              <div class="topnav-dropdown-footer">
                <a href="activities.php">View all Notifications</a>
              </div>
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
                <li class="active">
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
                <li>
                  <a href="profile.php"
                    ><img src="assets/img/profile.svg" alt="sidebar_img" />
                    <span>Profile</span></a
                  >
                </li>
              </ul>
              <ul class="logout">
                <li>
                  <a href="index.php"
                    ><img src="assets/img/logout.svg" alt="sidebar_img" /><span
                      >Log out</span
                    ></a
                  >
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
              <div class="card">
                <div class="card-header">
                  <h2 class="card-titles">
                    Basic Details <span>Organized and secure.</span>
                  </h2>
                </div>
                <div class="card-body">
                  <form method="post" action="">
                    <div class="row">
                      <div class="col-xl-6 col-sm-12 col-12">
                        <div class="form-group">
                          <input type="text" name="first_name" placeholder="First Name" required/>
                        </div>
                      </div>
                      <div class="col-xl-6 col-sm-12 col-12">
                        <div class="form-group">
                          <input type="text" name="last_name" placeholder="Last Name" required/>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-xl-6 col-sm-12 col-12">
                        <div class="form-group">
                          <input type="text" name="email" placeholder="Email" required/>
                        </div>
                      </div>
                      <div class="col-xl-6 col-sm-12 col-12">
                        <div class="form-group">
                          <input type="text" name="password" placeholder="Password" required/>
                        </div> 
                      </div>
                    </div>
                </div>
              </div>
            </div>
            <div class="col-xl-12 col-sm-12 col-12">
              <div class="card">
                <div class="card-header">
                  <h2 class="card-titles">
                    Employment Details <span>Let everyone know the essentials so they're fully prepared.</span>
                  </h2>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-xl-6 col-sm-12 col-12">
                      <div class="form-group">
                        <select name="position_name" class="form-control" required>
                          <option value="">Select Position</option>
                          <?php while ($row = mysqli_fetch_assoc($positions_result)) { ?>
                              <option value="<?php echo $row['positionName']; ?>"><?php echo $row['positionName']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-xl-6 col-sm-12 col-12">
                      <div class="form-group">
                        <select name="department_name" class="form-control" required>
                          <option value="">Select Department</option>
                          <?php while ($row = mysqli_fetch_assoc($departments_result)) { ?>
                              <option value="<?php echo $row['departmentName']; ?>"><?php echo $row['departmentName']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-xl-6 col-sm-12 col-12">
                      <div class="form-group">
                        <label>Hiring Date</label>
                        <input type="date" name="date_of_hire" id="date_of_hire">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-xl-12 col-sm-12 col-12">
                <div class="form-btn">
                  <input type="submit" class="btn btn-apply" value="Add Employee" name="submit">
                </div>
              </div>
            </div>
          </form>
          </div>
        </div>
      </div>
      
    
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/js/script.js"></script>
  </body>
</html>
