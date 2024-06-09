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


$attendanceRecords = [];

// Fetch all attendance records along with employee and user details from the database
$selectQuery = "
    SELECT 
        SELECT
    a.attendanceId,
    u.employeeId,
    u.firstName,
    u.lastName,
    u.email,
    e.positionName,
    e.departmentName,
    a.date,
    a.checkInTime,
    a.checkOutTime,
    a.status
FROM
    Attendance a
JOIN
    User u ON a.userId = u.userId
JOIN
    Employee e ON u.employeeId = e.employeeId";

$result = $conn->query($selectQuery);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $attendanceRecords[] = $row;
  }
} else {
  echo "No attendance records found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Records</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2>Attendance Records</h2>
    <?php if (!empty($attendanceRecords)) { ?>
    <table>
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
            <?php foreach ($attendanceRecords as $record) { ?>
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
            <?php } ?>
        </tbody>
    </table>
    <?php } else { ?>
    <p>No attendance records found.</p>
    <?php } ?>
</body>
</html>
