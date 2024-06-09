<?php
include 'connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $employeeId = $_POST['employeeId'];
   $date = $_POST['date'];
   $checkInTime = $_POST['checkInTime'];
   $checkOutTime = $_POST['checkOutTime'];
   $status = $_POST['status'];

   // Verify if the employeeId exists in the employees table
   $verifySql = "SELECT * FROM employee WHERE employeeId = ?";
   $stmt = $conn->prepare($verifySql);
   $stmt->bind_param("i", $employeeId);
   $stmt->execute();
   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      // If employeeId exists, insert the attendance record
      $sql = "INSERT INTO attendance (employeeId, date, status, checkInTime, checkOutTime)
                VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("issss", $employeeId, $date, $status, $checkInTime, $checkOutTime);

      if ($stmt->execute()) {
         echo "<script>alert('New record created successfully');</script>";
         echo "<script>window.location.href = 'attendanceForm.html';</script>";  // Redirect to employees tab
         exit();
      } else {
         echo "<script>alert('Error: " . $stmt->error . "');</script>";
      }

   } else {
      // If employeeId does not exist, display an error message
      echo "Error: Employee ID does not exist.";
   }

   $stmt->close();
   $conn->close();
}



?>