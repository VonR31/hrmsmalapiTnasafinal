<?php
session_start();

if (isset($_SESSION['userId'])) {
  header("Location: dashboard.php");
  exit();
}

include 'connect.php';

$firstName = '';
$lastName = '';
$email = '';
$employeeId = '';

if (isset($_POST['signIn'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $sql = "SELECT userId, firstName, lastName, email, roles, employeeId FROM user WHERE email = ? AND password = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $email, $password);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $firstName = htmlspecialchars($row["firstName"]);
      $lastName = htmlspecialchars($row["lastName"]);
      $email = htmlspecialchars($row["email"]);

      $_SESSION['userId'] = $row["userId"];
      $_SESSION['firstName'] = $firstName;
      $_SESSION['lastName'] = $lastName;
      $_SESSION['email'] = $email; // Store email in session
      $_SESSION['role'] = $row["roles"];
      $_SESSION['employeeId'] = $row["employeeId"];

      if ($row["roles"] == "Admin") {
        header("Location: dashboard.php");
      } else {
        header("Location: employeeDashboard.php");
      }
      exit();
    }
  } else {
    echo "Invalid email or password. Please try again.";
  }

  $stmt->close();
}



$conn->close();
?>







<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="./assets/css/login.css">
  <title>HRMS</title>
</head>

<body>
  <div class="container" id="container">
    <div class="form-container sign-up">

    </div>
    <div class="form-container sign-in">
      <form method="post" action="index.php">
        <h1>HRMS</h1>
        <h2>Sign In</h2>
        <input type="text" placeholder="Email" name="email" required>
        <input type="password" placeholder="Password" name="password" required>
        <a href="#">Forget Your Password?</a>
        <input type="submit" class="btn" value="Sign In" name="signIn">
      </form>
    </div>
    
      <div class="toggle-container">
        <h1>Hello!</h1>
        <p>Welcome to HRMS</p>
        
      </div>
    </div>
  </div>
</body>

</html>
