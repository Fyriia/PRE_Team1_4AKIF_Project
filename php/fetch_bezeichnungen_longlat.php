<?php
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "mysql";
$database = "TicketOnTrack";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT Bezeichnung, LatLong FROM Strasse";
$result = $conn->query($sql);

$bezeichnungen = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $bezeichnungen[] = array(
            'bezeichnung' => $row['Bezeichnung'],
            'latLong' => $row['LatLong']
        );
    }
}

$conn->close();

echo json_encode($bezeichnungen);