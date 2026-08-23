<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "GET request required"
    ]);

    exit;
}

try {

    $result = $conn->query("
        SELECT
            id,
            title,
            slug,
            location,
            employment_type,
            experience,
            qualification,
            description,
            responsibilities,
            requirements,
            compensation,
            performance_expectations,
            why_join,
            status,
            section_config,
            application_field_config,
            matching_rules,
            created_at,
            updated_at
        FROM jobs
        WHERE status = 'published'
        ORDER BY created_at DESC
    ");

    if (!$result) {
        throw new Exception($conn->error);
    }

    $jobs = [];

    while ($row = $result->fetch_assoc()) {

        $row["section_config"] =
            !empty($row["section_config"])
                ? (
                    json_decode(
                        $row["section_config"],
                        true
                    ) ?: null
                )
                : null;

        $row["application_field_config"] =
            !empty($row["application_field_config"])
                ? (
                    json_decode(
                        $row["application_field_config"],
                        true
                    ) ?: null
                )
                : null;

        $row["matching_rules"] =
            !empty($row["matching_rules"])
                ? (
                    json_decode(
                        $row["matching_rules"],
                        true
                    ) ?: null
                )
                : null;

        $jobs[] = $row;
    }

    echo json_encode([
        "success" => true,
        "jobs" => $jobs
    ]);

} catch (Throwable $e) {

    error_log(
        "Aerol Colt API jobs.php: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load jobs"
    ]);
}

$conn->close();

?>