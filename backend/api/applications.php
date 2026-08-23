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

    $sql = "
        SELECT
            a.id,
            a.job_id,
            a.full_name,
            a.email,
            a.phone,
            a.whatsapp,
            a.current_location,
            a.qualification,
            a.years_experience,
            a.uae_experience,
            a.relevant_experience,
            a.cover_letter,
            a.cv_original_name,
            a.cv_path,
            a.match_score,
            a.match_breakdown,
            a.status,
            a.created_at,
            j.title AS job_title,
            j.slug AS job_slug
        FROM applications a
        LEFT JOIN jobs j
            ON j.id = a.job_id
        ORDER BY
            a.match_score DESC,
            a.created_at DESC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception(
            $conn->error
        );
    }

    $applications = [];

    while ($row = $result->fetch_assoc()) {

        if (
            !empty($row["match_breakdown"])
        ) {

            $decoded =
                json_decode(
                    $row["match_breakdown"],
                    true
                );

            $row["match_breakdown"] =
                is_array($decoded)
                    ? $decoded
                    : null;

        } else {

            $row["match_breakdown"] = null;
        }

        $row["match_score"] =
            (float)(
                $row["match_score"] ?? 0
            );

        $applications[] = $row;
    }

    echo json_encode([
        "success" => true,
        "applications" => $applications
    ]);

} catch (Throwable $e) {

    error_log(
        "Aerol Colt applications.php: "
        . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load applications"
    ]);
}

$conn->close();

?>