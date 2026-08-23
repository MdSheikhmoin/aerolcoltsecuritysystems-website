<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "POST request required"
    ]);

    exit;
}

function respond(
    bool $success,
    string $message,
    int $status = 200
): void {

    http_response_code($status);

    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit;
}

$name =
    trim(
        $_POST["name"]
        ?? $_POST["full_name"]
        ?? ""
    );

$phone =
    trim(
        $_POST["phone"]
        ?? ""
    );

$email =
    trim(
        $_POST["email"]
        ?? ""
    );

$company =
    trim(
        $_POST["company"]
        ?? ""
    );

$message =
    trim(
        $_POST["message"]
        ?? ""
    );


if ($name === "") {

    respond(
        false,
        "Name is required.",
        422
    );
}

if ($phone === "") {

    respond(
        false,
        "Phone is required.",
        422
    );
}


if (
    $email !== "" &&
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    respond(
        false,
        "Please enter a valid email address.",
        422
    );
}


/*
|--------------------------------------------------------------------------
| INSERT LEAD
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT INTO leads (
        name,
        phone,
        email,
        company,
        message
    )
    VALUES (?, ?, ?, ?, ?)
");


if (!$stmt) {

    error_log(
        "Aerol Colt submit_lead.php prepare error: "
        . $conn->error
    );

    respond(
        false,
        "Unable to submit your request.",
        500
    );
}


$stmt->bind_param(
    "sssss",
    $name,
    $phone,
    $email,
    $company,
    $message
);


if (!$stmt->execute()) {

    error_log(
        "Aerol Colt submit_lead.php execute error: "
        . $stmt->error
    );

    $stmt->close();

    respond(
        false,
        "Unable to submit your request.",
        500
    );
}


$leadId =
    $stmt->insert_id;


$stmt->close();

$conn->close();


respond(
    true,
    "Thank you. Your request has been submitted."
);

?>