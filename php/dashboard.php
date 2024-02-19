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

// Check if a ticket type is selected and the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Check if ticketType is set
    if (isset($_POST['ticketType'])) {
        // Get the selected ticket type from the form
        $selectedTicketType = $_POST['ticketType'];

        // Fetch the corresponding KA_ID from Klimaticket_Art
        $sql = "SELECT KA_ID FROM Klimaticket_Art WHERE KA_Bez = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selectedTicketType);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a matching KA_ID was found
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $ka_id = $row['KA_ID'];

            // Insert the selected ticket into the Klimaticket table
            $sql = "INSERT INTO Klimaticket (KT_KlimaticketArtID) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $ka_id);
            if ($stmt->execute()) {
                echo "Ticket added successfully.";

                // Retrieve the ID of the recently inserted ticket
                $kt_id = $stmt->insert_id;

                // Update the K_KlimaticketID column in the Kunde table for the logged-in user
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $update_sql = "UPDATE Kunde SET K_KlimaticketID = ? WHERE K_ID = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $kt_id, $user_id);
                    if ($update_stmt->execute()) {
                        echo "User ticket ID updated successfully.";
                    } else {
                        echo "Error updating user ticket ID: " . $conn->error;
                    }
                } else {
                    echo "Error: User ID not found in session.";
                }
            } else {
                echo "Error adding ticket: " . $conn->error;
            }
        } else {
            echo "Error: No matching ticket type found.";
        }
    } else {
        echo "Error: Ticket type not selected.";
    }
}

// Fetch ticket types from the database
$sql = "SELECT KA_Bez FROM Klimaticket_Art";
$result = $conn->query($sql);

// Check if any ticket types were retrieved
if ($result->num_rows > 0) {
    // Start form
    echo '<form method="post">';

    // Start dropdown menu
    echo '<select name="ticketType">';

    // Output each ticket type as an option in the dropdown
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['KA_Bez'] . '">' . $row['KA_Bez'] . '</option>';
    }

    // End dropdown menu
    echo '</select>';

    // Submit button
    echo '<input type="submit" name="submit" value="Add Ticket">';

    // End form
    echo '</form>';
} else {
    echo "No ticket types found";
}

// Close connection
$conn->close();
?>
