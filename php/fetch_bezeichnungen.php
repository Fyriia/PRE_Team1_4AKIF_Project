<?php
$host = "localhost";
$username = "root";
$password = "mysql";
$database = "TicketOnTrack";

// Start output buffering
ob_start();

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT Bezeichnung FROM Strasse";
$result = $conn->query($sql);

$bezeichnungen = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $bezeichnungen[] = $row['Bezeichnung'];
    }
}

$conn->close();

// Clean the output buffer and output JSON
ob_end_clean();
echo json_encode($bezeichnungen);
?>