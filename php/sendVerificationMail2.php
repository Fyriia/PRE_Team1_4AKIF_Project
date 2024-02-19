<?php
require '../json/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


$host = "localhost";
$username = "root";
$password = "mysql";
$database = "TicketOnTrack";

// Connect to the database (replace with your database credentials)
$mysqli = new mysqli($host, $username, $password, $database);

// Check the connection
if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

// Collect user information (replace with your form input handling)
$email = $_POST['email'];
$passwort = $_POST['password'];
$passwort_hashed = password_hash($passwort, PASSWORD_DEFAULT);
$mail = new PHPMailer();

// Store user information in the database
$sql = "INSERT INTO kunde (k_email, k_passwort) VALUES ('$email', '$passwort_hashed')";
if ($mysqli->query($sql)) {

  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com'; // SMTP server. change to your smtp-host (google: 'smtp.google.com')
  $mail->SMTPAuth = true;
  $mail->Username = 'bartsch.karoline@gmail.com'; // SMTP username. Your email address
  $mail->Password = 'msvy rygt nrwe aevv'; // SMTP password. Set to your password (with google create a App-Password and insert this)
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = 587;
  $mail->setFrom('bartsch.karoline@gmail.com', 'Team @ TicketOnTrack'); // Insert your email address at ''
  $mail->addAddress($email);
  $mail->isHTML(true);
  $mail->Subject = 'Erfolgreich registriert bei Ticket On Track';
  $mail->Body    = 'Sie haben sich erfolgreich bei Ticket On Track registriert! <br><br> Bitte merken Sie sich Ihre Zugangsdaten. <br> E-mail: '.$email.' <br> Passwort: '.$passwort;
}
if(!$mail->send()) {
  echo '<script>
          setTimeout(function() {
            window.location.href = "../html/failed-register.html";
          }, 2000);
        </script>';

} else {
  // Redirect to failed registration page after 3 seconds
  echo '<script>
          setTimeout(function() {
            window.location.href = "../html/Successful-register.html";
          }, 3000);
        </script>';
}
$mysqli->close();
?>
<html lang="de">
<head>
</head>
<body>
<h1>HELLO</h1>
</body>
</html>