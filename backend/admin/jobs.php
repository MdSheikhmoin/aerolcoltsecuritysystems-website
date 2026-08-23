<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . "/../config/database.php";

if (
    !isset($_SESSION["admin_id"]) &&
    !isset($_SESSION["admin_user"]) &&
    !isset($_SESSION["admin_username"])
) {
    header("Location: login.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));

    $value = preg_replace(
        "/[^a-z0-9]+/",
        "_",
        $value
    );

    return trim((string)$value, "_");
}

function jsonArray(?string $value, array $fallback = []): array
{
    if (!$value) {
        return $fallback;
    }

    $decoded = json_decode($value, true);

    return is_array($decoded)
        ? $decoded
        : $fallback;
}

function flash(string $type, string $message): void
{
    $_SESSION["job_flash"] = [
        "type" => $type,
        "message" => $message
    ];
}


/*
|--------------------------------------------------------------------------
| DEFAULT APPLICATION FIELDS
|--------------------------------------------------------------------------
*/

$defaultFields = [

    [
        "key" => "full_name",
        "label" => "Full Name",
        "enabled" => true,
        "required" => true,
        "weight" => 0
    ],

    [
        "key" => "email",
        "label" => "Email",
        "enabled" => true,
        "required" => true,
        "weight" => 0
    ],

    [
        "key" => "phone",
        "label" => "Phone",
        "enabled" => true,
        "required" => true,
        "weight" => 0
    ],

    [
        "key" => "current_location",
        "label" => "Current Location",
        "enabled" => true,
        "required" => true,
        "weight" => 5
    ],

    [
        "key" => "qualification",
        "label" => "Qualification",
        "enabled" => true,
        "required" => true,
        "weight" => 10
    ],

    [
        "key" => "years_experience",
        "label" => "Years of Experience",
        "enabled" => true,
        "required" => true,
        "weight" => 25
    ],

    [
        "key" => "cover_letter",
        "label" => "Cover Letter",
        "enabled" => true,
        "required" => false,
        "weight" => 0
    ],

    [
        "key" => "cv",
        "label" => "CV / Resume",
        "enabled" => true,
        "required" => true,
        "weight" => 35
    ],

    [
        "key" => "languages",
        "label" => "Languages",
        "enabled" => true,
        "required" => false,
        "weight" => 5
    ],

    [
        "key" => "uae_driving_licence",
        "label" => "UAE Driving Licence",
        "enabled" => true,
        "required" => false,
        "weight" => 5
    ],

    [
        "key" => "existing_uae_business_network",
        "label" => "Existing UAE Business Network",
        "enabled" => true,
        "required" => false,
        "weight" => 15
    ]

];


/*
|--------------------------------------------------------------------------
| DEFAULT MATCHING RULES
|--------------------------------------------------------------------------
*/

$defaultRules = [

    "minimum_experience" => "2",

    "maximum_preferred_experience" => "5",

    "uae_experience_required" => false,

    "qualification_keywords" =>
        "Business Development, Business Administration, Marketing, Sales, Business Management, Commerce",

    "relevant_experience_keywords" =>
        "business development, sales, CCTV, ELV, security systems, access control, SIRA, IT, telecom, facility management",

    "location_keywords" =>
        "Dubai, UAE",

    "notification_threshold" => 50

];


/*
|--------------------------------------------------------------------------
| LOAD JOBS
|--------------------------------------------------------------------------
*/

$jobs = [];

$result = $conn->query(
    "SELECT * FROM jobs ORDER BY id DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| LOAD EDITING JOB
|--------------------------------------------------------------------------
*/

$editingJob = null;

if (
    isset($_GET["edit"]) &&
    ctype_digit((string)$_GET["edit"])
) {

    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT *
         FROM jobs
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "i",
        $id
    );

    $stmt->execute();

    $editingJob =
        $stmt
            ->get_result()
            ->fetch_assoc();

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| DEFAULT JOB BASICS
|--------------------------------------------------------------------------
|
| These are the five permanent Job Basics fields.
|
*/

$basicMeta = [

    "title" => [
        "label" => "Job Title",
        "required" => true
    ],

    "slug" => [
        "label" => "Slug",
        "required" => true
    ],

    "location" => [
        "label" => "Location",
        "required" => true
    ],

    "employment_type" => [
        "label" => "Employment Type",
        "required" => true
    ],

    "experience" => [
        "label" => "Experience",
        "required" => true
    ]

];


/*
|--------------------------------------------------------------------------
| JOB INFORMATION
|--------------------------------------------------------------------------
|
| ONLY Job Description is permanent.
| Other sections are custom and removable.
|
*/

$sections = [

    [
        "key" => "description",
        "title" => "Job Description",
        "content" => "",
        "field" => "description",
        "type" => "content",
        "deletable" => false
    ]

];

$basicCustomFields = [];


/*
|--------------------------------------------------------------------------
| APPLICATION FIELDS
|--------------------------------------------------------------------------
*/

$fields = $defaultFields;


/*
|--------------------------------------------------------------------------
| MATCHING RULES
|--------------------------------------------------------------------------
*/

$rules = $defaultRules;


/*
|--------------------------------------------------------------------------
| LOAD SAVED CONFIGURATION
|--------------------------------------------------------------------------
*/

if ($editingJob) {

    /*
    |--------------------------------------------------------------------------
    | BASIC VALUES
    |--------------------------------------------------------------------------
    */

    $savedConfig = jsonArray(
        $editingJob["section_config"] ?? null
    );


    /*
    |--------------------------------------------------------------------------
    | LOAD CUSTOM BASIC FIELDS
    |--------------------------------------------------------------------------
    */

    foreach ($savedConfig as $item) {

        if (!is_array($item)) {
            continue;
        }

        if (
            ($item["type"] ?? "") !== "basic"
        ) {
            continue;
        }

        $key =
            trim(
                (string)(
                    $item["key"] ?? ""
                )
            );

        if ($key === "") {
            continue;
        }

        $basicCustomFields[] = [

            "key" => $key,

            "label" =>
                trim(
                    (string)(
                        $item["title"]
                        ?? $item["label"]
                        ?? "New Field"
                    )
                ),

            "value" =>
                (string)(
                    $item["content"] ?? ""
                ),

            "required" =>
                !empty(
                    $item["required"]
                ),

            "show_on_careers" =>
                !empty(
                    $item["show_on_careers"]
                )

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD CUSTOM JOB INFORMATION SECTIONS
    |--------------------------------------------------------------------------
    */

    foreach ($savedConfig as $item) {

        if (!is_array($item)) {
            continue;
        }

        $type =
            (string)(
                $item["type"] ?? "content"
            );

        if ($type === "basic_meta") {
            continue;
        }

        if ($type === "basic") {
            continue;
        }

        if (
            ($item["key"] ?? "") === "description"
        ) {
            continue;
        }

        $key =
            trim(
                (string)(
                    $item["key"] ?? ""
                )
            );

        if ($key === "") {
            continue;
        }

        $title =
            trim(
                (string)(
                    $item["title"]
                    ?? "New Section"
                )
            );

        if ($title === "") {
            $title = "New Section";
        }

        $sections[] = [

            "key" => $key,

            "title" => $title,

            "content" =>
                (string)(
                    $item["content"] ?? ""
                ),

            "field" =>
                $item["field"] ?? null,

            "type" => "content",

            "required" => false,

            "deletable" => true

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DESCRIPTION CONTENT
    |--------------------------------------------------------------------------
    */

    foreach ($sections as &$section) {

        if (
            $section["key"] === "description"
        ) {

            $section["content"] =
                (string)(
                    $editingJob["description"]
                    ?? ""
                );
        }
    }

    unset($section);


    /*
    |--------------------------------------------------------------------------
    | APPLICATION FIELDS
    |--------------------------------------------------------------------------
    */

    $savedFields = jsonArray(
        $editingJob["application_field_config"] ?? null
    );

    if ($savedFields) {

        $fields = [];

        foreach ($savedFields as $field) {

            if (!is_array($field)) {
                continue;
            }

            $key =
                trim(
                    (string)(
                        $field["key"] ?? ""
                    )
                );

            if ($key === "") {
                continue;
            }

            $fields[] = [

                "key" => $key,

                "label" =>
                    trim(
                        (string)(
                            $field["label"]
                            ?? $key
                        )
                    ),

                "enabled" =>
                    !empty(
                        $field["enabled"]
                    ),

                "required" =>
                    !empty(
                        $field["required"]
                    ),

                "weight" =>
                    max(
                        0,
                        min(
                            100,
                            (float)(
                                $field["weight"]
                                ?? 0
                            )
                        )
                    )

            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MATCHING RULES
    |--------------------------------------------------------------------------
    */

    $savedRules = jsonArray(
        $editingJob["matching_rules"] ?? null
    );

    if ($savedRules) {

        $rules = array_merge(
            $rules,
            $savedRules
        );
    }
}


/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action =
        $_POST["action"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | DELETE JOB
    |--------------------------------------------------------------------------
    */

    if ($action === "delete") {

        $id =
            (int)(
                $_POST["id"] ?? 0
            );

        if ($id > 0) {

            $stmt = $conn->prepare(
                "DELETE FROM jobs WHERE id = ?"
            );

            $stmt->bind_param(
                "i",
                $id
            );

            $stmt->execute();

            $stmt->close();

            flash(
                "success",
                "Job deleted."
            );
        }

        header(
            "Location: jobs.php"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE JOB
    |--------------------------------------------------------------------------
    */

    if ($action === "save") {

        $id =
            (int)(
                $_POST["id"] ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | BASIC VALUES
        |--------------------------------------------------------------------------
        */

        $title =
            trim(
                $_POST["title"] ?? ""
            );

        $slug =
            trim(
                $_POST["slug"] ?? ""
            );

        $location =
            trim(
                $_POST["location"] ?? ""
            );

        $employmentType =
            trim(
                $_POST["employment_type"] ?? ""
            );

        $experience =
            trim(
                $_POST["experience"] ?? ""
            );

        $qualification =
            trim(
                $_POST["qualification"] ?? ""
            );

        $status =
            $_POST["status"] ?? "draft";


        if (
            !in_array(
                $status,
                [
                    "draft",
                    "published",
                    "closed"
                ],
                true
            )
        ) {

            $status = "draft";
        }


        if ($title === "") {

            flash(
                "error",
                "Job title is required."
            );

            header(
                "Location: jobs.php" .
                (
                    $id
                        ? "?edit=" . $id
                        : ""
                )
            );

            exit;
        }

        if ($experience === "") {

            flash(
                "error",
                "Experience is required."
            );

            header(
                "Location: jobs.php" .
                (
                    $id
                        ? "?edit=" . $id
                        : ""
                )
            );

            exit;
        }


        if ($slug === "") {
            $slug = slugify($title);
        }


        /*
        |--------------------------------------------------------------------------
        | READ POSTED JSON
        |--------------------------------------------------------------------------
        */

        $sectionsPosted =
            jsonArray(
                $_POST["sections_json"] ?? null
            );

        $fieldsPosted =
            jsonArray(
                $_POST["fields_json"] ?? null
            );

        $rulesPosted =
            jsonArray(
                $_POST["rules_json"] ?? null
            );

        $basicMetaPosted =
            jsonArray(
                $_POST["basic_meta_json"] ?? null
            );

        $basicCustomPosted =
            jsonArray(
                $_POST["basic_custom_json"] ?? null
            );


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE BASIC REQUIRED SETTINGS
        |--------------------------------------------------------------------------
        */

        foreach ($basicMeta as $key => &$meta) {

            if (
                array_key_exists(
                    $key,
                    $basicMetaPosted
                )
            ) {

                $meta["required"] =
                    !empty(
                        $basicMetaPosted[$key]
                    );
            }
        }

        unset($meta);


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE CUSTOM BASIC FIELDS
        |--------------------------------------------------------------------------
        */

        $normalizedBasicCustom = [];

        foreach ($basicCustomPosted as $item) {

            if (!is_array($item)) {
                continue;
            }

            $key =
                trim(
                    (string)(
                        $item["key"] ?? ""
                    )
                );

            if ($key === "") {
                $key =
                    "basic_" .
                    uniqid();
            }

            $label =
                trim(
                    (string)(
                        $item["label"]
                        ?? "New Field"
                    )
                );

            if ($label === "") {
                $label = "New Field";
            }

            $normalizedBasicCustom[] = [

                "key" => $key,

                "title" => $label,

                "content" =>
                    (string)(
                        $item["value"] ?? ""
                    ),

                "field" => null,

                "type" => "basic",

                "required" =>
                    !empty(
                        $item["required"]
                    ),

                "show_on_careers" =>
                    !empty(
                        $item["show_on_careers"]
                    ),

                "deletable" => true

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE JOB INFORMATION SECTIONS
        |--------------------------------------------------------------------------
        */

        $normalizedSections = [

            [
                "key" => "description",

                "title" => "Job Description",

                "content" => "",

                "field" => "description",

                "type" => "content",

                "required" => false,

                "deletable" => false
            ]

        ];


        foreach ($sectionsPosted as $section) {

            if (!is_array($section)) {
                continue;
            }

            if (
                ($section["type"] ?? "")
                === "basic"
            ) {
                continue;
            }

            if (
                ($section["key"] ?? "")
                === "description"
            ) {

                $normalizedSections[0]["content"] =
                    (string)(
                        $section["content"]
                        ?? ""
                    );

                continue;
            }


            $key =
                trim(
                    (string)(
                        $section["key"] ?? ""
                    )
                );

            if ($key === "") {
                $key =
                    "section_" .
                    uniqid();
            }


            $sectionTitle =
                trim(
                    (string)(
                        $section["title"]
                        ?? "New Section"
                    )
                );

            if ($sectionTitle === "") {
                $sectionTitle =
                    "New Section";
            }


            $normalizedSections[] = [

                "key" => $key,

                "title" =>
                    $sectionTitle,

                "content" =>
                    (string)(
                        $section["content"]
                        ?? ""
                    ),

                "field" => null,

                "type" => "content",

                "required" => false,

                "deletable" => true

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD COMPLETE SECTION CONFIG
        |--------------------------------------------------------------------------
        */

        $sectionConfig = [];


        foreach ($basicMeta as $key => $meta) {

            $sectionConfig[] = [

                "key" => $key,

                "title" =>
                    $meta["label"],

                "content" => "",

                "field" => $key,

                "type" => "basic_meta",

                "required" =>
                    !empty(
                        $meta["required"]
                    ),

                "deletable" => false

            ];
        }


        foreach (
            $normalizedBasicCustom
            as $customBasic
        ) {

            $sectionConfig[] =
                $customBasic;
        }


        foreach (
            $normalizedSections
            as $section
        ) {

            $sectionConfig[] =
                $section;
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE APPLICATION FIELDS
        |--------------------------------------------------------------------------
        */

        $normalizedFields = [];

        foreach ($fieldsPosted as $field) {

            if (!is_array($field)) {
                continue;
            }

            $key =
                trim(
                    (string)(
                        $field["key"] ?? ""
                    )
                );

            if ($key === "") {
                continue;
            }

            $label =
                trim(
                    (string)(
                        $field["label"]
                        ?? $key
                    )
                );

            $normalizedFields[] = [

                "key" => $key,

                "label" =>
                    $label !== ""
                        ? $label
                        : $key,

                "enabled" =>
                    !empty(
                        $field["enabled"]
                    ),

                "required" =>
                    !empty(
                        $field["required"]
                    ),

                "weight" =>
                    max(
                        0,
                        min(
                            100,
                            (float)(
                                $field["weight"]
                                ?? 0
                            )
                        )
                    )

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | DO NOT RESTORE DEFAULT FIELDS HERE.
        |
        | An empty application-field configuration is now
        | treated as an intentional saved configuration.
        |--------------------------------------------------------------------------
        */


        /*
        |--------------------------------------------------------------------------
        | MATCHING RULES
        |--------------------------------------------------------------------------
        */

        $normalizedRules = [

            "minimum_experience" =>
                trim(
                    (string)(
                        $rulesPosted[
                            "minimum_experience"
                        ] ?? ""
                    )
                ),

            "maximum_preferred_experience" =>
                trim(
                    (string)(
                        $rulesPosted[
                            "maximum_preferred_experience"
                        ] ?? ""
                    )
                ),

            "uae_experience_required" =>
                !empty(
                    $rulesPosted[
                        "uae_experience_required"
                    ]
                ),

            "qualification_keywords" =>
                trim(
                    (string)(
                        $rulesPosted[
                            "qualification_keywords"
                        ] ?? ""
                    )
                ),

            "relevant_experience_keywords" =>
                trim(
                    (string)(
                        $rulesPosted[
                            "relevant_experience_keywords"
                        ] ?? ""
                    )
                ),

            "location_keywords" =>
                trim(
                    (string)(
                        $rulesPosted[
                            "location_keywords"
                        ] ?? ""
                    )
                ),

            "notification_threshold" =>
                max(
                    0,
                    min(
                        100,
                        (float)(
                            $rulesPosted[
                                "notification_threshold"
                            ] ?? 50
                        )
                    )
                )

        ];


        /*
        |--------------------------------------------------------------------------
        | DATABASE CONTENT
        |--------------------------------------------------------------------------
        */

        $description = "";

        foreach (
            $normalizedSections
            as $section
        ) {

            if (
                $section["key"] ===
                "description"
            ) {

                $description =
                    (string)(
                        $section["content"]
                        ?? ""
                    );

                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */

        $sectionJson =
            json_encode(
                $sectionConfig,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );

        $fieldJson =
            json_encode(
                $normalizedFields,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );

        $rulesJson =
            json_encode(
                $normalizedRules,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE / CREATE
        |--------------------------------------------------------------------------
        */

        if ($id > 0) {

            $stmt = $conn->prepare(
                "UPDATE jobs SET
                    title = ?,
                    slug = ?,
                    location = ?,
                    employment_type = ?,
                    experience = ?,
                    qualification = ?,
                    description = ?,
                    responsibilities = ?,
                    requirements = ?,
                    compensation = ?,
                    performance_expectations = ?,
                    why_join = ?,
                    status = ?,
                    section_config = ?,
                    application_field_config = ?,
                    matching_rules = ?,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?"
            );

            $empty = "";

            $stmt->bind_param(
                "ssssssssssssssssi",
                $title,
                $slug,
                $location,
                $employmentType,
                $experience,
                $qualification,
                $description,
                $empty,
                $empty,
                $empty,
                $empty,
                $empty,
                $status,
                $sectionJson,
                $fieldJson,
                $rulesJson,
                $id
            );

            $ok =
                $stmt->execute();

            if (!$ok) {

                error_log(
                    "Aerol Colt jobs update error: "
                    . $stmt->error
                );
            }

            $stmt->close();

        } else {

            $empty = "";

            $stmt = $conn->prepare(
                "INSERT INTO jobs (
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
                    matching_rules
                )
                VALUES (
                    ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
                )"
            );

            $stmt->bind_param(
                "ssssssssssssssss",
                $title,
                $slug,
                $location,
                $employmentType,
                $experience,
                $qualification,
                $description,
                $empty,
                $empty,
                $empty,
                $empty,
                $empty,
                $status,
                $sectionJson,
                $fieldJson,
                $rulesJson
            );

            $ok =
                $stmt->execute();

            if (!$ok) {

                error_log(
                    "Aerol Colt jobs create error: "
                    . $stmt->error
                );
            }

            $stmt->close();
        }


        flash(
            $ok
                ? "success"
                : "error",

            $ok
                ? (
                    $id
                        ? "Job updated successfully."
                        : "Job created successfully."
                )
                : "Unable to save job."
        );


        header(
            "Location: jobs.php" .
            (
                $id
                    ? "?edit=" . $id
                    : ""
            )
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| FLASH
|--------------------------------------------------------------------------
*/

$flash =
    $_SESSION["job_flash"]
    ?? null;

unset(
    $_SESSION["job_flash"]
);


/*
|--------------------------------------------------------------------------
| FORM VALUES
|--------------------------------------------------------------------------
*/

if ($editingJob) {

    $titleValue =
        $editingJob["title"] ?? "";

    $slugValue =
        $editingJob["slug"] ?? "";

    $locationValue =
        $editingJob["location"] ?? "";

    $employmentValue =
        $editingJob["employment_type"]
        ?? "Full-Time";

    $experienceValue =
        $editingJob["experience"]
        ?? "";

    $qualificationValue =
        $editingJob["qualification"]
        ?? "";

    $statusValue =
        $editingJob["status"]
        ?? "draft";

} else {

    $titleValue = "";

    $slugValue = "";

    $locationValue = "";

    $employmentValue =
        "Full-Time";

    $experienceValue = "";

    $qualificationValue = "";

    $statusValue = "draft";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Aerol Colt - Manage Jobs
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #05050A;
    color: #F8FAFC;
    font-family: Arial, Helvetica, sans-serif;
}

.wrap {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

.top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

h1 {
    margin: 0;
    font-size: 30px;
}

h2 {
    margin: 0;
    font-size: 18px;
}

.muted {
    color: #94A3B8;
    font-size: 14px;
}

.top .muted {
    margin-top: 7px;
}

.panel {
    background: #0F111A;
    border: 1px solid #1E2235;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 20px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.field {
    min-width: 0;
}

.field.full {
    grid-column: 1 / -1;
}

label {
    display: block;
    margin-bottom: 7px;
    color: #CBD5E1;
    font-size: 13px;
    font-weight: 600;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #303548;
    border-radius: 9px;
    background: #05050A;
    color: #F8FAFC;
    font-size: 14px;
    outline: none;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #087FFF;
}

textarea {
    min-height: 150px;
    resize: vertical;
    line-height: 1.55;
}

.rowTitle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 18px;
}

.add {
    margin-top: 15px;
    border: 1px solid #303548;
    border-radius: 9px;
    padding: 10px 14px;
    background: #182033;
    color: #DBEAFE;
    cursor: pointer;
    font-weight: 700;
}

.add:hover {
    background: #202A40;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    padding: 11px 17px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
}

.primary {
    background: #087FFF;
    color: white;
}

.secondary {
    background: #182033;
    color: #DBEAFE;
}

.danger {
    background: #3A1117;
    color: #FECACA;
}

.flash {
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 18px;
}

.flash.success {
    background: #082C20;
    border: 1px solid #145C43;
    color: #86EFAC;
}

.flash.error {
    background: #321017;
    border: 1px solid #7F1D1D;
    color: #FCA5A5;
}


/*
|--------------------------------------------------------------------------
| JOB BASICS
|--------------------------------------------------------------------------
*/

.basic-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.basic-item {
    position: relative;
    padding: 14px;
    background: #090D17;
    border: 1px solid #20283B;
    border-radius: 11px;
}

.basic-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}

.basic-title span {
    color: #CBD5E1;
    font-size: 13px;
    font-weight: 700;
}

.required-star {
    color: #F87171;
    font-weight: 800;
}

.basic-actions {
    display: flex;
    gap: 5px;
}

.icon-btn {
    width: 29px;
    height: 27px;
    padding: 0;
    border: 1px solid #303548;
    border-radius: 7px;
    background: #182033;
    color: #CBD5E1;
    cursor: pointer;
    font-size: 13px;
}

.icon-btn:hover {
    background: #263149;
}

.required-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 9px;
    color: #94A3B8;
    font-size: 12px;
}

.required-row label {
    margin: 0;
    color: #94A3B8;
    font-size: 12px;
    font-weight: 500;
}

.basic-add {
    margin-top: 14px;
}


/*
|--------------------------------------------------------------------------
| JOB INFORMATION SECTIONS
|--------------------------------------------------------------------------
*/

.section-card {
    margin-bottom: 13px;
    padding: 15px;
    background: #090D17;
    border: 1px solid #20283B;
    border-radius: 11px;
}

.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.section-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-name strong {
    font-size: 14px;
}

.section-actions {
    display: flex;
    gap: 5px;
}

.section-edit,
.section-delete {
    border-radius: 7px;
    padding: 5px 8px;
    cursor: pointer;
    font-size: 12px;
}

.section-edit {
    border: 1px solid #303548;
    background: #182033;
    color: #CBD5E1;
}

.section-delete {
    border: 1px solid #5A2028;
    background: #301117;
    color: #FCA5A5;
}

.section-content {
    min-height: 180px;
}


/*
|--------------------------------------------------------------------------
| APPLICATION FIELDS
|--------------------------------------------------------------------------
*/

.field-table-wrap {
    overflow-x: auto;
}

.fieldTable {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.fieldTable th,
.fieldTable td {
    padding: 11px 9px;
    border-bottom: 1px solid #20283B;
    text-align: left;
    vertical-align: middle;
}

.fieldTable th {
    color: #94A3B8;
    font-size: 12px;
    text-transform: uppercase;
}

.fieldTable td {
    font-size: 13px;
}

.field-key {
    color: #94A3B8;
    font-family: monospace;
    font-size: 12px;
}

.small-input {
    width: 100%;
    min-width: 130px;
}

.weight-input {
    width: 90px;
}

.center {
    text-align: center !important;
}

.remove-field {
    border: 1px solid #5A2028;
    background: #301117;
    color: #FCA5A5;
    border-radius: 7px;
    padding: 6px 9px;
    cursor: pointer;
}


/*
|--------------------------------------------------------------------------
| SWITCH
|--------------------------------------------------------------------------
*/

.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    inset: 0;
    background: #283247;
    border-radius: 30px;
    cursor: pointer;
}

.slider:before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.2s;
}

.switch input:checked + .slider {
    background: #06B6D4;
}

.switch input:checked + .slider:before {
    transform: translateX(20px);
}


/*
|--------------------------------------------------------------------------
| CURRENT JOBS
|--------------------------------------------------------------------------
*/

.jobs {
    display: grid;
    gap: 10px;
}

.jobRow {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 16px;
    background: #090D17;
    border: 1px solid #20283B;
    border-radius: 13px;
}

.jobMeta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 7px;
}

.badge {
    font-size: 12px;
    padding: 5px 8px;
    border-radius: 999px;
    background: #172033;
    color: #CBD5E1;
}

.badge.published {
    background: #063C2A;
    color: #86EFAC;
}

.badge.closed {
    background: #3B1218;
    color: #FDA4AF;
}

.job-actions {
    display: flex;
    gap: 7px;
}

.buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

@media (max-width: 760px) {

    .wrap {
        padding: 18px;
    }

    .top {
        align-items: flex-start;
        flex-direction: column;
    }

    .grid {
        grid-template-columns: 1fr;
    }

    .field.full {
        grid-column: auto;
    }

    .basic-grid {
        grid-template-columns: 1fr;
    }

    .jobRow {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>

</head>

<body>

<div class="wrap">

<div class="top">

<div>

<h1>
<?= $editingJob
    ? "Edit Job"
    : "Create New Job" ?>
</h1>

<div class="muted">
Build the position, application fields and matching rules in one screen.
</div>

</div>

<div>

<a
    class="btn secondary"
    href="index.php"
>
← Back
</a>

<a
    class="btn secondary"
    href="logout.php"
>
Logout
</a>

</div>

</div>


<?php if ($flash): ?>

<div class="flash <?= e($flash["type"]) ?>">
<?= e($flash["message"]) ?>
</div>

<?php endif; ?>


<form
    method="post"
    id="jobForm"
>

<input
    type="hidden"
    name="action"
    value="save"
>

<input
    type="hidden"
    name="id"
    value="<?= e(
        $editingJob["id"] ?? 0
    ) ?>"
>

<input
    type="hidden"
    name="sections_json"
    id="sections_json"
>

<input
    type="hidden"
    name="fields_json"
    id="fields_json"
>

<input
    type="hidden"
    name="rules_json"
    id="rules_json"
>

<input
    type="hidden"
    name="basic_meta_json"
    id="basic_meta_json"
>

<input
    type="hidden"
    name="basic_custom_json"
    id="basic_custom_json"
>


<!-- JOB BASICS -->

<div class="panel">

<div class="rowTitle">

<h2>
JOB BASICS
</h2>

</div>


<div
    id="basicFields"
    class="basic-grid"
>


<!-- TITLE -->

<div class="basic-item">

<div class="basic-title">

<span>
Job Title
<span class="required-star">*</span>
</span>

</div>

<input
    id="title"
    name="title"
    required
    value="<?= e($titleValue) ?>"
    oninput="autoSlug()"
>

</div>


<!-- SLUG -->

<div class="basic-item">

<div class="basic-title">

<span>
Slug
<span class="required-star">*</span>
</span>

</div>

<input
    id="slug"
    name="slug"
    required
    value="<?= e($slugValue) ?>"
>

</div>


<!-- LOCATION -->

<div class="basic-item">

<div class="basic-title">

<span>
Location
<span class="required-star">*</span>
</span>

</div>

<input
    id="location"
    name="location"
    required
    value="<?= e($locationValue) ?>"
>

</div>


<!-- EMPLOYMENT TYPE -->

<div class="basic-item">

<div class="basic-title">

<span>
Employment Type
<span class="required-star">*</span>
</span>

</div>

<input
    id="employment_type"
    name="employment_type"
    required
    value="<?= e($employmentValue) ?>"
>

</div>


<!-- EXPERIENCE -->

<div class="basic-item">

<div class="basic-title">

<span>
Experience
<span class="required-star">*</span>
</span>

</div>

<input
    id="experience"
    name="experience"
    required
    value="<?= e($experienceValue) ?>"
    placeholder="e.g. 2-5 years"
>

</div>


</div>


<button
    type="button"
    class="add basic-add"
    onclick="addBasicSection()"
>
＋ Add Section
</button>

</div>


<!-- JOB INFORMATION -->

<div class="panel">

<div class="rowTitle">

<h2>
JOB INFORMATION
</h2>

</div>

<div id="sections"></div>

<button
    type="button"
    class="add"
    onclick="addSection()"
>
＋ Add Section
</button>

</div>


<!-- APPLICATION FIELDS -->

<div class="panel">

<div class="rowTitle">

<div>

<h2>
APPLICATION FIELDS
</h2>

<div
    class="muted"
    style="margin-top:5px"
>
Enable, require and set importance for matching.
</div>

</div>

</div>


<div class="field-table-wrap">

<table class="fieldTable">

<thead>

<tr>

<th>Field</th>
<th>Label</th>
<th>Enabled</th>
<th>Required</th>
<th>Importance</th>
<th></th>

</tr>

</thead>

<tbody id="fields"></tbody>

</table>

</div>


<button
    type="button"
    class="add"
    onclick="addField()"
>
＋ Add Application Field
</button>

</div>


<!-- MATCHING RULES -->

<div class="panel">

<div class="rowTitle">

<div>

<h2>
MATCHING RULES
</h2>

<div
    class="muted"
    style="margin-top:5px"
>
These rules guide the application matching engine.
</div>

</div>

</div>


<div
    class="grid"
    style="margin-top:16px"
>

<div class="field">

<label>
Minimum Experience (years)
</label>

<input
    id="minExp"
    type="number"
    min="0"
    step="0.5"
    value="<?= e(
        $rules["minimum_experience"]
    ) ?>"
>

</div>


<div class="field">

<label>
Maximum Preferred Experience (years)
</label>

<input
    id="maxExp"
    type="number"
    min="0"
    step="0.5"
    value="<?= e(
        $rules["maximum_preferred_experience"]
    ) ?>"
>

</div>


<div class="field">

<label>
UAE Experience Required
</label>

<label class="switch">

<input
    id="uaeReq"
    type="checkbox"
    <?= !empty(
        $rules[
            "uae_experience_required"
        ]
    )
        ? "checked"
        : "" ?>
>

<span class="slider"></span>

</label>

</div>


<div class="field">

<label>
Notification Threshold (%)
</label>

<input
    id="notify"
    type="number"
    min="0"
    max="100"
    value="<?= e(
        $rules["notification_threshold"]
    ) ?>"
>

</div>


<div class="field full">

<label>
Qualification Keywords
</label>

<textarea
    id="qualKeys"
    style="min-height:90px"
><?= e(
    $rules["qualification_keywords"]
) ?></textarea>

</div>


<div class="field full">

<label>
Relevant Experience Keywords
</label>

<textarea
    id="relKeys"
    style="min-height:90px"
><?= e(
    $rules["relevant_experience_keywords"]
) ?></textarea>

</div>


<div class="field full">

<label>
Location Keywords
</label>

<textarea
    id="locKeys"
    style="min-height:90px"
><?= e(
    $rules["location_keywords"]
) ?></textarea>

</div>

</div>

</div>


<input
    type="hidden"
    name="qualification"
    value="<?= e($qualificationValue) ?>"
>


<div class="buttons">

<select
    name="status"
    style="width:150px"
>

<option
    value="draft"
    <?= $statusValue === "draft"
        ? "selected"
        : "" ?>
>
Save as Draft
</option>

<option
    value="published"
    <?= $statusValue === "published"
        ? "selected"
        : "" ?>
>
Publish Job
</option>

<option
    value="closed"
    <?= $statusValue === "closed"
        ? "selected"
        : "" ?>
>
Closed
</option>

</select>


<button
    class="btn primary"
    type="submit"
>
Save Job
</button>

</div>

</form>


<!-- CURRENT JOBS -->

<div
    class="panel"
    style="margin-top:40px"
>

<div class="rowTitle">

<h2>
CURRENT JOBS
</h2>

</div>


<div
    class="jobs"
    style="margin-top:14px"
>

<?php if (!$jobs): ?>

<div class="muted">
No jobs created yet.
</div>

<?php endif; ?>


<?php foreach ($jobs as $job): ?>

<div class="jobRow">

<div>

<strong>
<?= e(
    $job["title"]
) ?>
</strong>

<div class="jobMeta">

<span class="badge">
<?= e(
    $job["location"]
) ?>
</span>

<span
    class="badge <?= e(
        $job["status"]
    ) ?>"
>
<?= e(
    ucfirst(
        $job["status"]
    )
) ?>
</span>

</div>

</div>


<div class="job-actions">

<a
    class="btn secondary"
    href="?edit=<?= (int)$job["id"] ?>"
>
Edit
</a>


<button
    class="btn danger"
    type="button"
    onclick="deleteJob(
        <?= (int)$job["id"] ?>
    )"
>
Delete
</button>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</div>


<form
    id="deleteForm"
    method="post"
    style="display:none"
>

<input
    name="action"
    value="delete"
>

<input
    name="id"
    id="deleteId"
>

</form>


<script>

/*
|--------------------------------------------------------------------------
| INITIAL DATA
|--------------------------------------------------------------------------
*/

const initialSections =
    <?= json_encode(
        $sections,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;

const initialFields =
    <?= json_encode(
        $fields,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;

const initialBasicCustomFields =
    <?= json_encode(
        $basicCustomFields,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;

const initialBasicMeta =
    <?= json_encode(
        $basicMeta,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;


let sections =
    Array.isArray(initialSections)
        ? initialSections
        : [];


let fields =
    Array.isArray(initialFields)
        ? initialFields
        : [];


let basicCustomFields =
    Array.isArray(initialBasicCustomFields)
        ? initialBasicCustomFields
        : [];


let basicMeta =
    initialBasicMeta &&
    typeof initialBasicMeta === "object"
        ? initialBasicMeta
        : {};


/*
|--------------------------------------------------------------------------
| ESCAPE
|--------------------------------------------------------------------------
*/

function esc(value) {

    return String(
        value ?? ""
    ).replace(
        /[&<>"']/g,
        function(match) {

            return {

                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;"

            }[match];

        }
    );
}


/*
|--------------------------------------------------------------------------
| SLUG
|--------------------------------------------------------------------------
*/

function autoSlug() {

    const slug =
        document.getElementById("slug");

    const title =
        document.getElementById("title");

    if (
        !slug.dataset.edited
    ) {

        slug.value =
            title.value
                .toLowerCase()
                .trim()
                .replace(
                    /[^a-z0-9]+/g,
                    "_"
                )
                .replace(
                    /^_|_$/g,
                    ""
                );
    }
}


document
    .getElementById("slug")
    .addEventListener(
        "input",
        function(event) {

            event.target.dataset.edited =
                "1";

        }
    );


/*
|--------------------------------------------------------------------------
| BASIC CUSTOM FIELDS
|--------------------------------------------------------------------------
*/

function addBasicSection() {

    const name =
        prompt(
            "Enter the new Job Basics field title:"
        );

    if (!name || !name.trim()) {
        return;
    }

    basicCustomFields.push({

        key:
            "basic_" +
            Date.now(),

        label:
            name.trim(),

        value:
            "",

        required:
            false,

        show_on_careers:
            true

    });

    renderBasicFields();
}


function renderBasicFields() {

    const root =
        document.getElementById(
            "basicFields"
        );

    root
        .querySelectorAll(
            ".custom-basic"
        )
        .forEach(
            element =>
                element.remove()
        );

    basicCustomFields.forEach(
        function(item, index) {

            const wrapper =
                document.createElement(
                    "div"
                );

            wrapper.className =
                "basic-item custom-basic";

            wrapper.innerHTML = `

                <div class="basic-title">

                    <span>

                        ${esc(item.label)}

                        <span class="required-star">

                            ${
                                item.required
                                    ? "*"
                                    : ""
                            }

                        </span>

                    </span>

                    <div class="basic-actions">

                        <button
                            type="button"
                            class="icon-btn"
                            title="Edit field title"
                            onclick="editBasic(${index})"
                        >
                            ✎
                        </button>

                        <button
                            type="button"
                            class="icon-btn"
                            title="Delete"
                            onclick="deleteBasic(${index})"
                        >
                            🗑
                        </button>

                    </div>

                </div>

                <input
                    type="text"
                    value="${esc(item.value)}"
                    ${
                        item.required
                            ? "required"
                            : ""
                    }
                    oninput="
                        basicCustomFields[
                            ${index}
                        ].value=this.value
                    "
                >

                <div class="required-row">

                    <span>
                        Required / Mandatory
                    </span>

                    <label class="switch">

                        <input
                            type="checkbox"
                            ${
                                item.required
                                    ? "checked"
                                    : ""
                            }
                            onchange="
                                basicCustomFields[
                                    ${index}
                                ].required=this.checked;
                                renderBasicFields();
                            "
                        >

                        <span class="slider"></span>

                    </label>

                </div>

                <div class="required-row">

                    <span>
                        Show on Careers Page
                    </span>

                    <label class="switch">

                        <input
                            type="checkbox"
                            ${
                                item.show_on_careers !== false
                                    ? "checked"
                                    : ""
                            }
                            onchange="
                                basicCustomFields[
                                    ${index}
                                ].show_on_careers=this.checked;
                            "
                        >

                        <span class="slider"></span>

                    </label>

                </div>

            `;

            root.appendChild(wrapper);

        }
    );
}


function editBasic(index) {

    const item =
        basicCustomFields[index];

    if (!item) {
        return;
    }

    const newTitle =
        prompt(
            "Edit field title:",
            item.label
        );

    if (
        newTitle &&
        newTitle.trim()
    ) {

        item.label =
            newTitle.trim();

        renderBasicFields();
    }
}


function deleteBasic(index) {

    if (
        !confirm(
            "Delete this Job Basics field?"
        )
    ) {
        return;
    }

    basicCustomFields.splice(
        index,
        1
    );

    renderBasicFields();
}


/*
|--------------------------------------------------------------------------
| JOB INFORMATION
|--------------------------------------------------------------------------
*/

function renderSections() {

    const root =
        document.getElementById(
            "sections"
        );

    root.innerHTML = "";


    sections.forEach(
        function(section, index) {

            if (
                section.type === "basic" ||
                section.type === "basic_meta"
            ) {
                return;
            }


            const card =
                document.createElement(
                    "div"
                );

            card.className =
                "section-card";


            const canDelete =
                section.deletable !== false;


            card.innerHTML = `

                <div class="section-head">

                    <div class="section-name">

                        <strong>
                            ${esc(
                                section.title ||
                                "New Section"
                            )}
                        </strong>

                    </div>

                    <div class="section-actions">

                        ${
                            section.key !== "description"
                                ? `
                                    <button
                                        type="button"
                                        class="section-edit"
                                        onclick="editSection(${index})"
                                    >
                                        Edit
                                    </button>
                                  `
                                : ""
                        }

                        ${
                            canDelete
                                ? `
                                    <button
                                        type="button"
                                        class="section-delete"
                                        onclick="deleteSection(${index})"
                                    >
                                        Delete
                                    </button>
                                  `
                                : ""
                        }

                    </div>

                </div>

                <textarea
                    class="section-content"
                    data-index="${index}"
                >${esc(
                    section.content || ""
                )}</textarea>

            `;


            const textarea =
                card.querySelector(
                    "textarea"
                );


            textarea.addEventListener(
                "input",
                function(event) {

                    sections[index].content =
                        event.target.value;

                }
            );


            root.appendChild(
                card
            );

        }
    );
}


function addSection() {

    const title =
        prompt(
            "Enter the new Job Information section title:"
        );


    if (
        !title ||
        !title.trim()
    ) {
        return;
    }


    sections.push({

        key:
            "section_" +
            Date.now(),

        title:
            title.trim(),

        content:
            "",

        field:
            null,

        type:
            "content",

        required:
            false,

        deletable:
            true

    });


    renderSections();
}


function editSection(index) {

    const section =
        sections[index];

    if (!section) {
        return;
    }


    if (
        section.key === "description"
    ) {

        alert(
            "Job Description is the permanent section."
        );

        return;
    }


    const title =
        prompt(
            "Edit section title:",
            section.title
        );


    if (
        title &&
        title.trim()
    ) {

        section.title =
            title.trim();

        renderSections();
    }
}


function deleteSection(index) {

    const section =
        sections[index];

    if (!section) {
        return;
    }


    if (
        section.key === "description"
    ) {

        alert(
            "Job Description cannot be deleted."
        );

        return;
    }


    if (
        !confirm(
            "Delete this section?"
        )
    ) {
        return;
    }


    sections.splice(
        index,
        1
    );


    renderSections();
}


/*
|--------------------------------------------------------------------------
| APPLICATION FIELDS
|--------------------------------------------------------------------------
*/

function renderFields() {

    const root =
        document.getElementById(
            "fields"
        );

    root.innerHTML = "";


    fields.forEach(
        function(field, index) {

            const row =
                document.createElement(
                    "tr"
                );


            row.innerHTML = `

                <td>

                    <div class="field-key">
                        ${esc(field.key)}
                    </div>

                </td>

                <td>

                    <input
                        class="small-input"
                        type="text"
                        value="${esc(
                            field.label
                        )}"
                        oninput="
                            fields[
                                ${index}
                            ].label=this.value
                        "
                    >

                </td>

                <td class="center">

                    <label class="switch">

                        <input
                            type="checkbox"
                            ${
                                field.enabled
                                    ? "checked"
                                    : ""
                            }
                            onchange="
                                fields[
                                    ${index}
                                ].enabled=this.checked
                            "
                        >

                        <span class="slider"></span>

                    </label>

                </td>

                <td class="center">

                    <label class="switch">

                        <input
                            type="checkbox"
                            ${
                                field.required
                                    ? "checked"
                                    : ""
                            }
                            onchange="
                                fields[
                                    ${index}
                                ].required=this.checked
                            "
                        >

                        <span class="slider"></span>

                    </label>

                </td>

                <td>

                    <input
                        class="weight-input"
                        type="number"
                        min="0"
                        max="100"
                        step="1"
                        value="${Number(
                            field.weight || 0
                        )}"
                        oninput="
                            fields[
                                ${index}
                            ].weight=Math.max(
                                0,
                                Math.min(
                                    100,
                                    Number(this.value || 0)
                                )
                            )
                        "
                    >

                </td>

                <td>

                    <button
                        type="button"
                        class="remove-field"
                        onclick="deleteField(${index})"
                    >
                        🗑
                    </button>

                </td>

            `;


            root.appendChild(
                row
            );

        }
    );
}


function addField() {

    const key =
        prompt(
            "Enter the field name/key:"
        );


    if (
        !key ||
        !key.trim()
    ) {
        return;
    }


    const cleanKey =
        key
            .trim()
            .toLowerCase()
            .replace(
                /[^a-z0-9_]+/g,
                "_"
            );


    const exists =
        fields.some(
            field =>
                field.key ===
                cleanKey
        );


    if (exists) {

        alert(
            "This field already exists."
        );

        return;
    }


    const label =
        prompt(
            "Enter the field label:",
            key.trim()
        );


    fields.push({

        key:
            cleanKey,

        label:
            label &&
            label.trim()
                ? label.trim()
                : key.trim(),

        enabled:
            true,

        required:
            false,

        weight:
            0

    });


    renderFields();
}


function deleteField(index) {

    if (!fields[index]) {
        return;
    }


    if (
        !confirm(
            "Delete this application field?"
        )
    ) {
        return;
    }


    fields.splice(
        index,
        1
    );


    renderFields();
}


/*
|--------------------------------------------------------------------------
| DELETE JOB
|--------------------------------------------------------------------------
*/

function deleteJob(id) {

    if (
        !confirm(
            "Are you sure you want to delete this job?"
        )
    ) {
        return;
    }


    document.getElementById(
        "deleteId"
    ).value = id;


    document.getElementById(
        "deleteForm"
    ).submit();
}


/*
|--------------------------------------------------------------------------
| FORM SUBMIT
|--------------------------------------------------------------------------
*/

document
    .getElementById("jobForm")
    .addEventListener(
        "submit",
        function() {

            const metaPayload = {
                title: true,
                slug: true,
                location: true,
                employment_type: true,
                experience: true
            };


            document.getElementById(
                "basic_meta_json"
            ).value =
                JSON.stringify(
                    metaPayload
                );


            document.getElementById(
                "basic_custom_json"
            ).value =
                JSON.stringify(
                    basicCustomFields
                );


            document.getElementById(
                "sections_json"
            ).value =
                JSON.stringify(
                    sections
                );


            document.getElementById(
                "fields_json"
            ).value =
                JSON.stringify(
                    fields
                );


            const matchingRules = {

                minimum_experience:
                    document.getElementById(
                        "minExp"
                    ).value,

                maximum_preferred_experience:
                    document.getElementById(
                        "maxExp"
                    ).value,

                uae_experience_required:
                    document.getElementById(
                        "uaeReq"
                    ).checked,

                qualification_keywords:
                    document.getElementById(
                        "qualKeys"
                    ).value,

                relevant_experience_keywords:
                    document.getElementById(
                        "relKeys"
                    ).value,

                location_keywords:
                    document.getElementById(
                        "locKeys"
                    ).value,

                notification_threshold:
                    document.getElementById(
                        "notify"
                    ).value

            };


            document.getElementById(
                "rules_json"
            ).value =
                JSON.stringify(
                    matchingRules
                );

        }
    );


/*
|--------------------------------------------------------------------------
| INITIAL RENDER
|--------------------------------------------------------------------------
*/

renderSections();

renderFields();

renderBasicFields();

</script>

</body>

</html>

<?php

$conn->close();

?>