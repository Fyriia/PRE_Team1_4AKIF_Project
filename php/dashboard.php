<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../App ToT MockUps/ticket6.png">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCUWq0LFNX3NBlA-BbBBlvl1HfZYkv0pNc&callback=initMap" async defer></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Ticket On Track Dashboard</title>
</head>
<body>
<div id="options">
    <a href="options.php" style="margin-left: 1200px; margin-top: 30px"> <img height="40px" width="40px" src="../App%20ToT%20MockUps/settings-svgrepo-com.svg"></a>
    <a style="margin-top: 10px; margin-left: 1200px" href="logoutScript.php"><img src="../App%20ToT%20MockUps/logout-svgrepo-com.svg" width="30px" height="30px"></a>
</div>
<img style="margin: 0; padding: 0; width: 160px" alt="cartoon graphic of a train and tickets" src="../App ToT MockUps/Train with tickets_upscayl_4x_ultrasharp.png">
<h1 style="margin: 0; padding: 0">Ticket On Track</h1>
<main style="margin: 0; padding: 0" id="dashboard">
    <div style="margin: 0; padding: 0" id="fahrtsuchen">
        <label style="margin: 4px; padding: 0">Start:
            <input list="bezeichnungenStart" id="bezeichnungStart" name="bezeichnungStart">
            <datalist id="bezeichnungenStart"></datalist>
        </label>

        <label style="margin: 4px; padding: 0">Ziel:
            <input list="bezeichnungenZiel" id="bezeichnungZiel" name="bezeichnungZiel">
            <datalist id="bezeichnungenZiel"></datalist>
        </label>

        <input style="margin: 5px 0 10px 20px; padding: 0" type="datetime-local" id="TimeChooser">
    </div>

    <div style="margin-left: 50px" id="map" style="height: 500px; width: 100%;"></div>
    <div id="submit">
        <button id="submit_button">Fahrt Starten</button>
    </div>
</main>

<h2>Klimaticket Hinzufügen</h2>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: ../html/login.html");
    exit();
}

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

<script>
    let map;
    let marker;

    // Initialize and add the map
    function initMap() {
        // The location of Vienna
        let vienna = {lat: 48.2082, lng: 16.3738};
        // The map, centered at Vienna
        map = new google.maps.Map(document.getElementById('map'), {zoom: 10, center: vienna});
        // The marker, positioned at Vienna
        marker = new google.maps.Marker({position: vienna, map: map});
    }

    // Function to fetch data from PHP script and populate datalist
    function populateDatalist() {
        fetch('fetch_bezeichnungen_longlat.php')
            .then(response => response.json())
            .then(data => {
                let datalistStart = document.getElementById('bezeichnungenStart');
                let datalistZiel = document.getElementById('bezeichnungenZiel');

                data.forEach(item => {
                    let optionStart = document.createElement('option');
                    optionStart.value = item.bezeichnung;
                    optionStart.setAttribute('data-latLong', item.latLong);
                    datalistStart.appendChild(optionStart);

                    let optionZiel = document.createElement('option');
                    optionZiel.value = item.bezeichnung;
                    optionZiel.setAttribute('data-latLong', item.latLong);
                    datalistZiel.appendChild(optionZiel);
                });

                // Add event listener for datalist selection
                document.getElementById('bezeichnungStart').addEventListener('input', function(e) {
                    let selectedOption = Array.from(datalistStart.options).find(option => option.value === e.target.value);
                    if (selectedOption) {
                        let latLongString = selectedOption.getAttribute('data-latLong');
                        let latLong = JSON.parse(latLongString.replace(/lat/g, '"lat"').replace(/lng/g, '"lng"'));
                        let position = {lat: parseFloat(latLong.lat), lng: parseFloat(latLong.lng)};
                        marker.setPosition(position);
                        map.setCenter(position);
                        map.setZoom(15); // Adjust zoom level here
                    }
                });

                document.getElementById('bezeichnungZiel').addEventListener('input', function(e) {
                    let selectedOption = Array.from(datalistZiel.options).find(option => option.value === e.target.value);
                    if (selectedOption) {
                        let latLongString = selectedOption.getAttribute('data-latLong');
                        let latLong = JSON.parse(latLongString.replace(/lat/g, '"lat"').replace(/lng/g, '"lng"'));
                        let position = {lat: parseFloat(latLong.lat), lng: parseFloat(latLong.lng)};
                        marker.setPosition(position);
                        map.setCenter(position);
                        map.setZoom(15); // Adjust zoom level here
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    // Call the function when the page loads
    window.onload = populateDatalist;
</script>

</body>
</html>