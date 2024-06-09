<?php
session_start();

if (!isset($_SESSION['userId']) || $_SESSION['role'] != 'Employee') {
   header("Location: index.php");
   exit();
}

include 'connect.php';

$userId = $_SESSION['userId'];
$attendanceRecords = [];

// Retrieve employeeId using userId
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $date = $_POST['date'];
   $checkInTime = $_POST['checkInTime'];
   $checkOutTime = $_POST['checkOutTime'];
   $status = $_POST['status'];

   if ($checkInTime) {
      // Insert a new record for check-in
      $insertQuery = "INSERT INTO attendance (employeeId, userId, date, status, checkInTime) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($insertQuery);
      $stmt->bind_param("iisss", $employeeId, $userId, $date, $status, $checkInTime);
   } elseif ($checkOutTime) {
      // Update the existing record for check-out
      $updateQuery = "UPDATE attendance SET status = ?, checkOutTime = ? WHERE employeeId = ? AND date = ?";
      $stmt = $conn->prepare($updateQuery);
      $stmt->bind_param("ssis", $status, $checkOutTime, $employeeId, $date);
   } elseif ($status) {
      // Insert a new record for absent or leave
      $insertQuery = "INSERT INTO attendance (employeeId, userId, date, status) VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($insertQuery);
      $stmt->bind_param("iiss", $employeeId, $userId, $date, $status);
   }
   if ($stmt->execute()) {
      $successMessage = "Attendance recorded successfully.";
   } else {
      $errorMessage = "Error recording attendance.";
   }
   $stmt->close();

   header("Location: employeeAttendance.php");
   exit();
}

// Fetch attendance records for the logged-in employee
$selectQuery = "SELECT date, checkInTime, checkOutTime, status FROM attendance WHERE employeeId = ?";
$stmt = $conn->prepare($selectQuery);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
   $attendanceRecords[] = $row;
}
$stmt->close();
$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Employee Attendance</title>
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
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
         margin-bottom: 20px;
         color: #343a40;
      }
      .btn {
         margin: 5rem;
         
      }
      .form-group {
         
         display: flex;
         
         padding: 10px;
      }

      .table {
         border: none;
         margin-top: 5px;
         margin-bottom: 5px;
         background-color: white;
         border-radius: 25px;
         box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
         
         
      }

      .table th,
      .table td {
         border: none;
         vertical-align: middle;
         border-radius: 25px;
         background-color: white;
      }

      .table th {
         font-weight: bold;
         background-color: white;
      }

      .table tbody tr:nth-child(even) {
         background-color: white;
      }

      .table-responsive .card {
         border-radius: 25px;
         background-color: navy;
         padding: 9px;
      }

      .btn-primary {
         background-color: #007bff;
         border-color: #007bff;
         font-weight: bold;
         padding: 30px;
      }

      .btn-primary:hover {
         background-color: #0069d9;
         border-color: #0062cc;
      }

      .btn-danger {
         background-color: #dc3545;
         border-color: #dc3545;
         font-weight: bold;
         padding: 30px;
      }

      .btn-danger:hover {
         background-color: #c82333;
         border-color: #bd2130;
      }

      .btn-warning {
         background-color: #ffc107;
         border-color: #ffc107;
         font-weight: bold;
         padding: 30px;
      }

      .btn-warning:hover {
         background-color: #e0a800;
         border-color: #d39e00;
      }

      .alert {
         margin-top: 20px;
         padding: 10px;
         border-radius: 8px;
      }

      .alert-success {
         background-color: #d4edda;
         color: #155724;
         border-color: #c3e6cb;
      }

      .alert-danger {
         background-color: #f8d7da;
         color: #721c24;
         border-color: #f5c6cb;
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
                  <a class="nav-link active" href="employeeAttendance.php">Attendance</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="employeeLeave.php">Leave</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="logout.php">Logout</a>
               </li>
            </ul>
         </div>
      </div>
   </nav>

   <div class="container">
      <h2 class="page-title">Attendance</h2>

      <?php if (isset($successMessage)): ?>
         <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
         </div>
      <?php endif; ?>

      <?php if (isset($errorMessage)): ?>
         <div class="alert alert-danger" role="alert">
            <?php echo $errorMessage; ?>
         </div>
      <?php endif; ?>

      <form id="attendanceForm" action="employeeAttendance.php" method="POST">
         <input type="hidden" id="date" name="date">
         <input type="hidden" id="checkInTime" name="checkInTime">
         <input type="hidden" id="checkOutTime" name="checkOutTime">
         <input type="hidden" id="status" name="status">
      </form>

      <div class="form-group">
         <button id="checkInBtn" class="btn btn-primary">Check In</button>
         <button id="checkOutBtn" class="btn btn-primary">Check Out</button>
         <button id="absentBtn" class="btn btn-danger">Absent</button>
         <button id="leaveBtn" class="btn btn-warning">Ongoing Leave</button>
      </div>

      <div class="table-responsive">
         <div class="card">
         <table class="table table-bordered table-striped">
            <thead>
               <tr>
                  <th>Date</th>
                  <th>Check-in Time</th>
                  <th>Check-out Time</th>
                  <th>Status</th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($attendanceRecords as $record): ?>
                  <tr>
                     <td><?php echo htmlspecialchars($record['date']); ?></td>
                     <td><?php echo htmlspecialchars($record['checkInTime']); ?></td>
                     <td><?php echo htmlspecialchars($record['checkOutTime']); ?></td>
                     <td><?php echo htmlspecialchars($record['status']); ?></td>
                  </tr>
               <?php endforeach; ?>
            </tbody>
         </table>
         </div>
      </div>
   </div>

   <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

   <script>
      function formatDate(date) {
         let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

         if (month.length < 2) month = '0' + month;
         if (day.length < 2) day = '0' + day;

         return [year, month, day].join('-');
      }

      function formatTime(date) {
         let hours = date.getHours();
         let minutes = date.getMinutes();
         let seconds = date.getSeconds();

         if (hours < 10) hours = '0' + hours;
         if (minutes < 10) minutes = '0' + minutes;
         if (seconds < 10) seconds = '0' + seconds;

         return hours + ':' + minutes + ':' + seconds;
      }

      document.getElementById('checkInBtn').addEventListener('click', function() {
         document.getElementById('date').value = formatDate(new Date());
         document.getElementById('checkInTime').value = formatTime(new Date());
         document.getElementById('checkOutTime').value = '';
         document.getElementById('status').value = 'Present';
         document.getElementById('attendanceForm').submit();
      });

      document.getElementById('checkOutBtn').addEventListener('click', function() {
         document.getElementById('date').value = formatDate(new Date());
         document.getElementById('checkInTime').value = '';
         document.getElementById('checkOutTime').value = formatTime(new Date());
         document.getElementById('status').value = 'Present';
         document.getElementById('attendanceForm').submit();
      });

      document.getElementById('absentBtn').addEventListener('click', function() {
         document.getElementById('date').value = formatDate(new Date());
         document.getElementById('checkInTime').value = '00:00:00';
         document.getElementById('checkOutTime').value = '00:00:00';
         document.getElementById('status').value = 'Absent';
         document.getElementById('attendanceForm').submit();
      });

      document.getElementById('leaveBtn').addEventListener('click', function() {
         document.getElementById('date').value = formatDate(new Date());
         document.getElementById('checkInTime').value = '00:00:00';
         document.getElementById('checkOutTime').value = '00:00:00';
         document.getElementById('status').value = 'On Going Leave';
         document.getElementById('attendanceForm').submit();
      });
   </script>
</body>
</html>
