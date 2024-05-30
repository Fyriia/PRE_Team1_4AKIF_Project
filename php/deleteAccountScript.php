<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

// Check if delete account button is clicked
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Check if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Delete the user record from the Kunde table
        $delete_sql = "DELETE FROM Kunde WHERE K_ID = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);

        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Account successfully deleted, destroy the session and redirect to the index page
                session_unset(); // Unset all session variables
                session_destroy(); // Destroy the session
                header("Location: index.html"); // Redirect to the index page
                exit();
            } else {
                echo "No account found with the given user ID.";
            }
        } else {
            echo "Error deleting account: " . $delete_stmt->error;
        }
    } else {
        echo "Error: User ID not found in session.";
    }
}

// Close connection
$conn->close();
?>
