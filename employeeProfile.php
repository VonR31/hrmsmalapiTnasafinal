<?php
session_start();

if (!isset($_SESSION['userId']) || $_SESSION['role'] != 'Employee') {
   header("Location: index.php");
   exit();
}

include 'connect.php';

$userId = $_SESSION['userId'];
$firstName = $lastName = $email = '';

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateProfile'])) {
   $firstName = $_POST['firstName'];
   $lastName = $_POST['lastName'];
   $email = $_POST['email'];

   $updateQuery = "UPDATE user SET firstName = ?, lastName = ?, email = ? WHERE userId = ?";
   $stmt = $conn->prepare($updateQuery);
   $stmt->bind_param("sssi", $firstName, $lastName, $email, $userId);
   if ($stmt->execute()) {
      $successMessage = "Profile updated successfully.";
   } else {
      $errorMessage = "Error updating profile.";
   }
   $stmt->close();
} else {
   // Fetch user profile data
   $selectQuery = "SELECT firstName, lastName, email FROM user WHERE userId = ?";
   $stmt = $conn->prepare($selectQuery);
   $stmt->bind_param("i", $userId);
   $stmt->execute();
   $stmt->bind_result($firstName, $lastName, $email);
   $stmt->fetch();
   $stmt->close();
}

// Change password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['changePassword'])) {
   $currentPassword = $_POST['currentPassword'];
   $newPassword = $_POST['newPassword'];

   // Verify current password
   $verifyQuery = "SELECT password FROM user WHERE userId = ?";
   $stmt = $conn->prepare($verifyQuery);
   $stmt->bind_param("i", $userId);
   $stmt->execute();
   $stmt->bind_result($storedPassword);
   $stmt->fetch();
   $stmt->close();

   if ($currentPassword === $storedPassword) {
      // Update password in plain text
      $updatePasswordQuery = "UPDATE user SET password = ? WHERE userId = ?";
      $stmt = $conn->prepare($updatePasswordQuery);
      $stmt->bind_param("si", $newPassword, $userId);
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Employee Profile</title>
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

      .form-group {
         margin-bottom: 20px;
      }

      .form-group label {
         font-weight: bold;
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
                  <a class="nav-link active" href="employeeProfile.php">Profile</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="employeeAttendance.php">Attendance</a>
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

   <div class="container mt-4">
      <div class="row">
         <div class="col-md-12">
            <h2 class="page-title">Profile</h2>
         </div>
      </div>

      <div class="row">
         <!-- Update Profile -->
         <div class="col-md-6">
            <div class="card">
               <div class="card-body">
                  <h5 class="card-title">Update Profile</h5>
                  <?php if (isset($successMessage)): ?>
                     <div class="alert alert-success" role="alert">
                        <?php echo $successMessage; ?>
                     </div>
                  <?php elseif (isset($errorMessage)): ?>
                     <div class="alert alert-danger" role="alert">
                        <?php echo $errorMessage; ?>
                     </div>
                  <?php endif; ?>
                  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                     <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName"
                           value="<?php echo htmlspecialchars($firstName); ?>" required>
                     </div>
                     <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName"
                           value="<?php echo htmlspecialchars($lastName); ?>" required>
                     </div>
                     <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                           value="<?php echo htmlspecialchars($email); ?>" required>
                     </div>
                     <input type="submit" class="btn btn-primary btn-block" name="updateProfile" value="Update Profile">
                  </form>
               </div>
            </div>
         </div>

         <!-- Change Password -->
          <!-- Change Password -->
         <div class="col-md-6">
            <div class="card">
               <div class="card-body">
                  <h5 class="card-title">Change Password</h5>
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
                     <input type="submit" class="btn btn-primary btn-block" name="changePassword"
                        value="Change Password">
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   
   <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   <!-- Font Awesome JS -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>



</body>
</html>

