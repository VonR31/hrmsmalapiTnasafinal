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

// Fetch the employeeId using userId from the session
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

// Function to fetch tasks for the logged-in employee
function fetchTasks($conn, $employeeId)
{
  $tasks = [];
  $stmt = $conn->prepare("SELECT tasksId, task, status, created_at FROM tasks WHERE employeeId = ?");
  $stmt->bind_param("i", $employeeId);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
  }

  $stmt->close();
  return $tasks;
}

// Function to update task completion status
function updateTaskCompletion($conn, $taskId)
{
  $stmt = $conn->prepare("UPDATE tasks SET status = 'completed' WHERE tasksId = ?");
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $stmt->close();
}

// Function to add a new task for the logged-in employee
function addTask($conn, $employeeId, $task)
{
  $stmt = $conn->prepare("INSERT INTO tasks (employeeId, task, created_at) VALUES (?, ?, NOW())");
  $stmt->bind_param("is", $employeeId, $task);
  $stmt->execute();
  $stmt->close();
}

// Function to clear all tasks for the logged-in employee
function clearTasks($conn, $employeeId)
{
  $stmt = $conn->prepare("DELETE FROM tasks WHERE employeeId = ?");
  $stmt->bind_param("i", $employeeId);
  $stmt->execute();
  $stmt->close();
}

// Fetch tasks for the current employee
$tasks = fetchTasks($conn, $employeeId);

// Handle task completion form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
  $taskId = $_POST['task_id'];
  updateTaskCompletion($conn, $taskId);

  // Refresh tasks array after updating completion status
  $tasks = fetchTasks($conn, $employeeId);
}

// Handle add task form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task'])) {
  $task = $_POST['task'];
  try {
    addTask($conn, $employeeId, $task);
    // Refresh tasks array after adding a new task
    $tasks = fetchTasks($conn, $employeeId);
  } catch (Exception $e) {
    // Handle the exception (e.g., display an error message)
    echo "Error: " . $e->getMessage();
  }
}

// Handle clear tasks button submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_tasks'])) {
  clearTasks($conn, $employeeId);

  // Refresh tasks array after clearing all tasks
  $tasks = [];
}

function applyLeave($conn, $employeeId, $firstName, $lastName, $leaveType, $startDate, $endDate)
{
  $status = 'Pending'; // Default status for a new leave application
  $stmt = $conn->prepare("INSERT INTO employee_leave (employeeId, firstName, lastName, leaveType, startDate, endDate, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("issssss", $employeeId, $firstName, $lastName, $leaveType, $startDate, $endDate, $status);
  $stmt->execute();
  $stmt->close();
}

// Handle apply leave form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['applyLeave'])) {
  $leaveType = $_POST['leaveType'];
  $startDate = $_POST['startDate'];
  $endDate = $_POST['endDate'];

  applyLeave($conn, $employeeId, $firstName, $lastName, $leaveType, $startDate, $endDate);
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <style>
body {
  background-color: #e3f2fd;
  font-family: 'Montserrat', sans-serif;
}

.navbar {
  background-color: navy;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
  font-weight: bold;
  color: #ffffff;
}

.navbar-nav .nav-link {
  color: #ffffff;
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
  background-color: #007bff !important;
  border-color: #007bff;
  font-weight: bold;
}

.btn-primary:hover {
  background-color: #0069d9;
  border-color: #0062cc;
}

.task-item {
  margin-bottom: 10px;
}

.task-item .task-text {
  display: inline-block;
  width: calc(100% - 40px);
  vertical-align: middle;
}

.task-item .task-check {
  float: right;
  vertical-align: middle;
}

.task-item.completed .task-text {
  text-decoration: line-through;
  color: #6c757d;
}

.add-task-form {
  margin-top: 20px;
}

.clear-tasks-btn {
  margin-top: 10px;
}

  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark ">
    <div class="container-fluid">
      <a class="navbar-brand" href="employeeDashboard.php">HRMS</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link active" href="employeeDashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="employeeProfile.php">Profile</a>
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
        <h2 class="page-title text-center">Welcome, <?php echo $firstName . ' ' . $lastName; ?>!</h2>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <h5 class="card-title">Profile</h5>
            <p class="card-text">View and update your profile information.</p>
            <a href="employeeProfile.php" class="btn btn-primary">Go to Profile</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <h5 class="card-title">Attendance</h5>
            <p class="card-text">View and manage your attendance records.</p>
            <a href="employeeAttendance.php" class="btn btn-primary">Go to Attendance</a>
          </div>
        </div>
      </div>
     <div class="col-md-4">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Tasks</h5>
      <?php if (empty($tasks)): ?>
            <p class="card-text">No tasks assigned.</p>
          <?php else: ?>
            <?php foreach ($tasks as $task): ?>
              <div class="task-item <?php echo $task['status'] == 'completed' ? 'completed' : ''; ?>">
                <div class="task-text"><?php echo htmlspecialchars($task['task']); ?></div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="task-check">
                  <?php if ($task['status'] != 'completed'): ?>
                    <input type="hidden" name="task_id" value="<?php echo $task['tasksId']; ?>">
                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Mark this task as completed?')">
                      <i class="fas fa-check"></i>
                    </button>
                  <?php endif; ?>
                </form>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
    
          <!-- Add Task Button - Trigger Modal -->
          <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#addTaskModal">
            Add Task
          </button>
          <!-- Clear Tasks Button -->
          <?php if (!empty($tasks)): ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
              <input type="hidden" name="clear_tasks" value="true">
              <button type="submit" class="btn btn-danger clear-tasks-btn mt-3"
                onclick="return confirm('Are you sure you want to clear all tasks? This action cannot be undone.')">
                Clear Tasks
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="addTaskModalLabel">Add Task</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="task">Task Description</label>
                <input type="text" class="form-control" id="task" name="task" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Add Task</button>
            </div>
          </form>
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
