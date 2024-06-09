<?php
session_start();

if (!isset($_SESSION['userId']) || $_SESSION['role'] != 'Employee') {
   header("Location: index.php");
   exit();
}

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];


// Include your database connection
include 'connect.php';

$userId = $_SESSION['userId'];
$employeeIdQuery = "SELECT employeeId FROM user WHERE userId = ?";
$stmt = $conn->prepare($employeeIdQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
   $employeeId = $row['employeeId'];
} else {
   die("Employee ID not found for the given user ID.");
}
$stmt->close();

// Function to apply for leave
function applyLeave($conn, $employeeId, $firstName, $lastName, $leaveType, $startDate, $endDate)
{
   $status = 'Pending'; // Default status for a new leave application
   $stmt = $conn->prepare("INSERT INTO employee_leave (employeeId, firstName, lastName, leaveType, startDate, endDate, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
   $stmt->bind_param("issssss", $employeeId, $firstName, $lastName, $leaveType, $startDate, $endDate, $status);
   $stmt->execute();
   $stmt->close();
}

// Function to fetch leave history
function fetchLeaveHistory($conn, $employeeId)
{
   $leaveHistory = [];
   $stmt = $conn->prepare("SELECT leaveId, leaveType, startDate, endDate, status FROM employee_leave WHERE employeeId = ?");
   $stmt->bind_param("i", $employeeId);
   $stmt->execute();
   $result = $stmt->get_result();

   while ($row = $result->fetch_assoc()) {
      $leaveHistory[] = $row;
   }

   $stmt->close();
   return $leaveHistory;
}

// Handle apply leave form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['applyLeave'])) {
   $leaveType = $_POST['leaveType'];
   $startDate = $_POST['startDate'];
   $endDate = $_POST['endDate'];

   applyLeave($conn, $employeeId, $firstName, $lastName, $leaveType, $startDate, $endDate);
}

// Fetch leave history for the current employee
$leaveHistory = fetchLeaveHistory($conn, $employeeId);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Leave Application</title>
   <!-- Bootstrap CSS -->
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
   <!-- Font Awesome -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
   <!-- Custom CSS -->
   <style>
      body {
         background-color: #e3f2fd;
         font-family: Arial, sans-serif;
      }

      .navbar {
         background-color: navy;
         box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .navbar-brand {
         font-weight: bold;
         color: #343a40;
      }

      .navbar-nav .nav-link {
         color: #343a40;
      }

      .navbar-nav .nav-link.active {
         font-weight: bold;
      }

      .page-title {
         font-size: 24px;
         font-weight: bold;
         margin-top: 20px;
         color: #343a40;
      }

      .card {
         border: none;
         border-radius: 8px;
         background-color: #ffffff;
         box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
         transition: transform 0.3s ease-in-out;
      }

      .card:hover {
         transform: translateY(-5px);
      }

      .card-body {
         padding: 20px;
      }

      .card-title {
         font-size: 20px;
         font-weight: bold;
         color: #343a40;
      }

      .card-text {
         color: #6c757d;
      }

      .btn-primary {
         background-color: #007bff;
         border-color: #007bff;
         font-weight: bold;
      }

      .btn-primary:hover {
         background-color: #0069d9;
         border-color: #0062cc;
      }

      .table {
         margin-top: 20px;
      }
   </style>
</head>

<body>
   <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container-fluid">
         <a class="navbar-brand" href="#">HRMS</a>
         <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
               <li class="nav-item">
                  <a class="nav-link" href="employeeDashboard.php">Dashboard</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="employeeProfile.php">Profile</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="employeeAttendance.php">Attendance</a>
               </li>
               <li class="nav-item ">
                  <a class="nav-link active" href="employeeLeave.php">Leave</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </div>
   </nav>

   <div class="container mt-4">
      <div class="row">
         <div class="col-md-12">
            <h2 class="page-title text-center">Leave Application</h2>
         </div>
      </div>

      <div class="row mt-4">
         <div class="col-md-6">
            <div class="card">
               <div class="card-body">
                  <h5 class="card-title">Apply for Leave</h5>
                  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                     <div class="form-group">
                        <label for="leaveType">Leave Type</label>
                        <select class="form-control" id="leaveType" name="leaveType" required>
                          <option value="Select leave">Select leave</option>
                          <option value="Vacation leave">Vacation leave</option>
                          <option value="Family and Medical leave">Family and Medical leave</option>
                          <option value="Parental leave">Parental leave</option>
                          <option value="Sick leave">Sick leave</option>
                          <option value="Unpaid leave">Unpaid leave</option>
                          <option value="Public Holidays">Public Holidays</option>
                          <option value="Religious observance leave">Religious observance leave</option>
                          <option value="Sabbatical leave">Sabbatical leave</option>
                          <option value="Bereavement leave">Bereavement leave</option>
                          <option value="Military leave">Military leave</option>
                          <option value="Jury Duty leave">Jury Duty leave</option>
                          <option value="Study leave">Study leave</option>
                          <option value="Adverse weather leave">Adverse weather leave</option>
                        </select>
                     </div>
                     <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input type="date" class="form-control" id="startDate" name="startDate" required>
                     </div>
                     <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input type="date" class="form-control" id="endDate" name="endDate" required>
                     </div>
                     <button type="submit" class="btn btn-primary" name="applyLeave">Apply</button>
                  </form>
               </div>
            </div>
         </div>

         <div class="col-md-6">
            <div class="card">
               <div class="card-body">
                  <h5 class="card-title">Leave History</h5>
                  <?php if (empty($leaveHistory)): ?>
                     <p class="card-text">No leave records found.</p>
                  <?php else: ?>
                     <table class="table table-bordered">
                        <thead>
                           <tr>
                              <th scope="col">Leave Type</th>
                              <th scope="col">Start Date</th>
                              <th scope="col">End Date</th>
                              <th scope="col">Status</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($leaveHistory as $leave): ?>
                              <tr>
                                 <td><?php echo htmlspecialchars($leave['leaveType']); ?></td>
                                 <td><?php echo htmlspecialchars($leave['startDate']); ?></td>
                                 <td><?php echo htmlspecialchars($leave['endDate']); ?></td>
                                 <td><?php echo htmlspecialchars($leave['status']); ?></td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>