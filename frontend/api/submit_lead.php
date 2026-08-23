<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$host = "localhost";
$dbname = "aerolcol_leads";
$username = "aerolcol_user";
$password = "Aerolcolt7572@";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

$data = json_decode(file_get_contents("php://input"), true);

$name = $conn->real_escape_string($data['name'] ?? '');
$phone = $conn->real_escape_string($data['phone'] ?? '');
$email = $conn->real_escape_string($data['email'] ?? '');
$message = $conn->real_escape_string($data['message'] ?? '');
$source = $conn->real_escape_string($data['source'] ?? 'contact_form');

if (!$name || !$phone) {
    echo json_encode([
        "success" => false,
        "message" => "Name and phone required"
    ]);
    exit;
}

$sql = "INSERT INTO leads (name, phone, email, message, source)
VALUES ('$name', '$phone', '$email', '$message', '$source')";

if ($conn->query($sql) === TRUE) {

    $to = "info@aerolcoltsecuritysystems.ae";
    $subject = "New Website Lead";

    $body = "
Name: $name

Phone: $phone

Email: $email

Message:
$message

Source:
$source
";

    $headers = "From: admin@aerolcoltsecuritysystems.ae";

    mail($to, $subject, $body, $headers);

    echo json_encode([
        "success" => true,
        "message" => "Lead submitted successfully"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Insert failed"
    ]);
}

$conn->close();

?>