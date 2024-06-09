<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Include your database connection or establish a connection here
   include_once 'connect.php'; // Update with your actual database connection file

   // Get leave ID from POST data
   $leaveId = $_POST['leaveId'];

   // Check which action is submitted
   if (isset($_POST['approve'])) {
      // Approve leave request
      $sql = "UPDATE employee_leave SET status = 'Approved' WHERE leaveId = $leaveId";
      $action = "approved";
   } elseif (isset($_POST['disapprove'])) {
      // Disapprove leave request
      $sql = "UPDATE employee_leave SET status = 'Disapproved' WHERE leaveId = $leaveId";
      $action = "disapproved";
   } else {
      // If neither approve nor disapprove is set, handle error
      echo "Invalid action";
      exit;
   }

   // Execute the SQL query
   if ($conn->query($sql) === TRUE) {
      echo "<script>alert('Leave request {$action} successfully');</script>";
      echo "<script>window.location.href = 'leave.php';</script>";
   } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
   }

   // Close database connection
   $conn->close();
} else {
   echo "Method not allowed";
}
?>