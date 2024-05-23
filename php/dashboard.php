<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../App ToT MockUps/ticket6.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Ticket On Track Dashboard</title>
</head>
<body>
<h1>Ticket On Track</h1>



<main id="dashboard">
<div id="fahrtsuchen" >
    <label>
        <input type="search" id="fahrtsuchen_suchbox">
    </label>

    <label>
        <input type="search" id="fahrtsuchen_suchbox">
    </label>
    <input type="datetime-local">
</div>

    <div id="MapMockup">
        <img src="" alt="working map">
    </div>
    <div id="submit">
        <label>
        <button id="submit_button">
            Fahrt Starten
        </button>
        </label>
    </div>
</main>





<img alt="cartoon graphic of a train and tickets" src="../App ToT MockUps/Train with tickets_upscayl_4x_ultrasharp.png">
<h2>Klimaticket Hinzufügen</h2>
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
                echo "Ticket erfolgreich hinzugefügt.";

                // Retrieve the ID of the recently inserted ticket
                $kt_id = $stmt->insert_id;

                // Update the K_KlimaticketID column in the Kunde table for the logged-in user
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $update_sql = "UPDATE Kunde SET K_KlimaticketID = ? WHERE K_ID = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $kt_id, $user_id);
                    if ($update_stmt->execute()) {
                        echo "";
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
    echo '<input type="submit" name="submit" value="Ticket hinzufügen">';

    // End form
    echo '</form>';
} else {
    echo "No ticket types found";
}


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT KA_Bez FROM Klimaticket_Art 
            INNER JOIN Klimaticket ON Klimaticket_Art.KA_ID = Klimaticket.KT_KlimaticketArtID
            INNER JOIN Kunde ON Klimaticket.KT_ID = Kunde.K_KlimaticketID
            WHERE K_ID = $user_id";
    $result = $conn->query($sql); // No need for prepared statement since there are no placeholders

    if ($result) {
        if ($result->num_rows > 0) {
            echo "<h2>Bereits hinzugefügte Tickets</h2>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . $row['KA_Bez'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "No tickets added yet.";
        }
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "User ID not found in session.";
}
// Close connection
$conn->close();
?>




</body>
</html>
