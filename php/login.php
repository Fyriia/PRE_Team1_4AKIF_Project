<?php
session_start();

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "mysql";
$database = "TicketOnTrack";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Process login form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST["email"];
  $passwort = $_POST["password"];

  // Check if the provided credentials match the admin record in the database
  $sql = "SELECT K_ID, K_passwort FROM kunde WHERE K_Email='$email'";
  $result = $conn->query($sql);

  if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $user_id = $row['K_ID'];
    $hashed_passwort = $row['K_passwort'];

    // Verify the password
    if (password_verify($passwort, $hashed_passwort)) {
      // Authentication successful, set session variable and redirect to dashboard
      $_SESSION['user_id'] = $user_id;
      header("Location: ../php/dashboard.php");
      exit();
    } else {
      // Invalid password
      echo "Invalid password";
    }
  } else {
    // Invalid username or no user found
    echo "Invalid username or password";
  }
}
?>
