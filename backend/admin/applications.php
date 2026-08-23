<?php

session_start();

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}


/*
|--------------------------------------------------------------------------
| NORMALIZE APPLICATION FIELD CONFIG
|--------------------------------------------------------------------------
*/

function normalizeApplicationFields($config)
{
    if (!is_array($config)) {
        return [];
    }

    $fields = [];

    foreach ($config as $key => $item) {

        if (is_string($item)) {

            $fields[] = [
                "field" => $item,
                "label" => ucwords(
                    str_replace("_", " ", $item)
                ),
                "enabled" => true,
                "required" => false,
                "importance" => 0
            ];

            continue;
        }

        if (!is_array($item)) {
            continue;
        }

        $field =
            $item["field"]
            ?? $item["name"]
            ?? $item["key"]
            ?? (
                is_string($key)
                    ? $key
                    : ""
            );

        $field = trim((string)$field);

        if ($field === "") {
            continue;
        }

        $label =
            $item["label"]
            ?? $item["title"]
            ?? ucwords(
                str_replace("_", " ", $field)
            );

        $enabled =
            array_key_exists("enabled", $item)
                ? (bool)$item["enabled"]
                : true;

        $required =
            array_key_exists("required", $item)
                ? (bool)$item["required"]
                : false;

        $importance =
            isset($item["importance"])
                ? (float)$item["importance"]
                : 0;

        $fields[] = [
            "field" => $field,
            "label" => $label,
            "enabled" => $enabled,
            "required" => $required,
            "importance" => $importance
        ];
    }

    return $fields;
}


/*
|--------------------------------------------------------------------------
| GET STANDARD APPLICATION FIELD
|--------------------------------------------------------------------------
*/

function getApplicationFieldValue(
    array $application,
    string $field
) {

    if (
        array_key_exists(
            $field,
            $application
        )
    ) {
        return $application[$field];
    }

    $aliases = [

        "name" =>
            "full_name",

        "email_address" =>
            "email",

        "phone_number" =>
            "phone",

        "location" =>
            "current_location",

        "experience" =>
            "years_experience",

        "years_of_experience" =>
            "years_experience",

        "driving_license" =>
            "uae_driving_licence",

        "uae_driving_license" =>
            "uae_driving_licence",

        "cv_resume" =>
            "cv_path",

        "resume" =>
            "cv_path"
    ];

    if (
        isset($aliases[$field]) &&
        array_key_exists(
            $aliases[$field],
            $application
        )
    ) {
        return $application[
            $aliases[$field]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | application_data JSON
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $application["application_data"]
            ?? ""
        )
    ) {

        $data = json_decode(
            $application["application_data"],
            true
        );

        if (
            is_array($data) &&
            array_key_exists(
                $field,
                $data
            )
        ) {
            return $data[$field];
        }
    }

    return "";
}


/*
|--------------------------------------------------------------------------
| FORMAT VALUE
|--------------------------------------------------------------------------
*/

function formatApplicationValue(
    $value,
    string $field = ""
) {

    if (
        $value === null ||
        $value === ""
    ) {
        return "-";
    }

    if (is_array($value)) {

        return implode(
            ", ",
            array_map(
                function ($item) {

                    return is_scalar($item)
                        ? (string)$item
                        : json_encode($item);

                },
                $value
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | JSON VALUE
    |--------------------------------------------------------------------------
    */

    if (is_string($value)) {

        $trimmed = trim($value);

        if (
            str_starts_with($trimmed, "[") ||
            str_starts_with($trimmed, "{")
        ) {

            $decoded = json_decode(
                $trimmed,
                true
            );

            if (is_array($decoded)) {

                return implode(
                    ", ",
                    array_map(
                        function ($item) {

                            return is_scalar($item)
                                ? (string)$item
                                : json_encode($item);

                        },
                        $decoded
                    )
                );
            }
        }
    }

    return (string)$value;
}


/*
|--------------------------------------------------------------------------
| LOAD CUSTOM ANSWERS FOR ONE APPLICATION
|--------------------------------------------------------------------------
|
| application_answers contains:
|
| application_id
| question_id
| answer
|
| job_questions contains:
|
| id
| job_id
| question
| question_type
| required
| sort_order
|
| We load everything once and group it by application ID.
|--------------------------------------------------------------------------
*/

$applicationAnswers = [];


$answersSql = "

    SELECT

        aa.application_id,

        aa.question_id,

        aa.answer,

        jq.job_id,

        jq.question,

        jq.question_type,

        jq.required,

        jq.sort_order

    FROM application_answers aa

    INNER JOIN job_questions jq
        ON jq.id = aa.question_id

    ORDER BY
        aa.application_id ASC,
        jq.sort_order ASC,
        jq.id ASC

";


$answersResult =
    $conn->query($answersSql);


if ($answersResult) {

    while (
        $answerRow =
            $answersResult->fetch_assoc()
    ) {

        $applicationId =
            (int)$answerRow[
                "application_id"
            ];

        if (
            !isset(
                $applicationAnswers[
                    $applicationId
                ]
            )
        ) {

            $applicationAnswers[
                $applicationId
            ] = [];
        }

        $applicationAnswers[
            $applicationId
        ][] = $answerRow;
    }
}


/*
|--------------------------------------------------------------------------
| POST STATUS UPDATE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $application_id =
        (int)(
            $_POST["application_id"]
            ?? 0
        );

    $status =
        $_POST["status"]
        ?? "";

    $allowed_statuses = [

        "new",
        "review",
        "shortlisted",
        "interview",
        "rejected",
        "hired"

    ];

    if (
        $application_id > 0 &&
        in_array(
            $status,
            $allowed_statuses,
            true
        )
    ) {

        $stmt = $conn->prepare(
            "
            UPDATE applications
            SET status = ?
            WHERE id = ?
            "
        );

        if ($stmt) {

            $stmt->bind_param(
                "si",
                $status,
                $application_id
            );

            $stmt->execute();

            $stmt->close();
        }
    }

    header(
        "Location: applications.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| LOAD APPLICATIONS
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        a.*,

        j.title AS job_title,

        j.application_field_config,

        j.matching_rules

    FROM applications a

    INNER JOIN jobs j
        ON a.job_id = j.id

    ORDER BY

        a.match_score DESC,

        a.created_at DESC

";


$result = $conn->query($sql);


if (!$result) {

    error_log(
        "Aerol Colt applications.php SQL error: " .
        $conn->error
    );

    die(
        "Unable to load applications."
    );
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
Applications - Aerol Colt Admin
</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    background: #05050A;

    color: #F8FAFC;

    font-family:
        Arial,
        sans-serif;
}

.container {

    max-width: 1500px;

    margin: auto;

    padding: 30px;
}

.topbar {

    display: flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap: 20px;

    margin-bottom: 30px;
}

h1 {

    margin: 0;

    font-size: 30px;
}

.subtitle {

    margin-top: 8px;

    color: #9CA3AF;
}

.back {

    color: #CBD5E1;

    text-decoration: none;

    border:
        1px solid
        #303548;

    padding:
        10px 15px;

    border-radius:
        8px;
}

.table-wrapper {

    overflow: auto;

    background:
        #0F111A;

    border:
        1px solid
        #1E2235;

    border-radius:
        14px;
}

table {

    width: 100%;

    border-collapse:
        collapse;

    min-width:
        1450px;
}

th {

    text-align: left;

    padding:
        15px;

    color:
        #94A3B8;

    font-size:
        13px;

    text-transform:
        uppercase;

    border-bottom:
        1px solid
        #1E2235;
}

td {

    padding:
        15px;

    border-bottom:
        1px solid
        #1E2235;

    vertical-align:
        top;
}

.candidate {

    font-weight:
        bold;
}

.small {

    font-size:
        13px;

    color:
        #94A3B8;

    margin-top:
        4px;
}

.score {

    font-weight:
        bold;

    font-size:
        17px;
}

.strong {

    color:
        #4ADE80;
}

.good {

    color:
        #FACC15;
}

.weak {

    color:
        #F87171;
}

.badge {

    display:
        inline-block;

    padding:
        4px 8px;

    border-radius:
        999px;

    font-size:
        11px;

    background:
        #1E2235;

    color:
        #CBD5E1;
}

select {

    background:
        #05050A;

    color:
        white;

    border:
        1px solid
        #303548;

    padding:
        8px 10px;

    border-radius:
        7px;
}

button {

    background:
        #2563EB;

    color:
        white;

    border:
        none;

    padding:
        8px 12px;

    border-radius:
        7px;

    cursor:
        pointer;
}

.cv {

    color:
        #60A5FA;

    text-decoration:
        none;
}

.cv:hover {

    text-decoration:
        underline;
}

.details {

    max-width:
        430px;

    line-height:
        1.4;
}

.details summary {

    cursor:
        pointer;

    color:
        #60A5FA;

    padding:
        3px 0;
}

.application-fields {

    margin-top:
        10px;
}

.application-field {

    padding:
        8px 0;

    border-bottom:
        1px solid
        #1E2235;
}

.application-field:last-child {

    border-bottom:
        none;
}

.application-label {

    color:
        #CBD5E1;

    font-weight:
        600;

    font-size:
        13px;
}

.required-label {

    color:
        #F87171;

    font-size:
        10px;

    margin-left:
        5px;

    font-weight:
        600;
}

.application-value {

    color:
        #94A3B8;

    margin-top:
        3px;

    white-space:
        pre-wrap;

    word-break:
        break-word;
}

.custom-answer {

    color:
        #94A3B8;

    margin-top:
        3px;

    white-space:
        pre-wrap;

    word-break:
        break-word;
}

.custom-question {

    padding:
        8px 0;

    border-bottom:
        1px solid
        #1E2235;
}

.custom-question:last-child {

    border-bottom:
        none;
}

.custom-question-label {

    color:
        #CBD5E1;

    font-weight:
        600;

    font-size:
        13px;
}

.breakdown {

    margin-top:
        10px;

    padding:
        10px;

    background:
        #080A11;

    border:
        1px solid
        #1E2235;

    border-radius:
        8px;
}

.breakdown-row {

    display:
        flex;

    justify-content:
        space-between;

    gap:
        10px;

    font-size:
        12px;

    padding:
        4px 0;
}

.pass {

    color:
        #4ADE80;
}

.fail {

    color:
        #F87171;
}

.empty {

    padding:
        50px;

    text-align:
        center;

    color:
        #94A3B8;
}

.no-answer {

    color:
        #64748B;

    font-style:
        italic;
}

</style>

</head>


<body>


<div class="container">


<div class="topbar">

<div>

<h1>
Applications
</h1>

<div class="subtitle">
All applications are stored.
Higher-match candidates are shown first.
</div>

</div>


<a
    href="jobs.php"
    class="back"
>
← Back to Jobs
</a>

</div>


<div class="table-wrapper">


<?php if ($result->num_rows > 0): ?>


<table>


<thead>

<tr>

<th>
Candidate
</th>

<th>
Position
</th>

<th>
Experience
</th>

<th>
Match
</th>

<th>
Candidate Details
</th>

<th>
CV
</th>

<th>
Status
</th>

<th>
Applied
</th>

</tr>

</thead>


<tbody>


<?php while (
    $application =
        $result->fetch_assoc()
): ?>


<?php

$applicationId =
    (int)$application["id"];


/*
|--------------------------------------------------------------------------
| JOB APPLICATION CONFIG
|--------------------------------------------------------------------------
*/

$fieldConfig = [];

$fieldConfigRaw =
    $application[
        "application_field_config"
    ] ?? "";


if (
    !empty($fieldConfigRaw)
) {

    $decoded =
        json_decode(
            $fieldConfigRaw,
            true
        );

    if (
        is_array($decoded)
    ) {

        $fieldConfig =
            normalizeApplicationFields(
                $decoded
            );
    }
}


/*
|--------------------------------------------------------------------------
| ONLY ENABLED CONFIGURED FIELDS
|--------------------------------------------------------------------------
*/

$enabledFields =
    array_values(
        array_filter(
            $fieldConfig,
            function ($field) {

                return !empty(
                    $field["enabled"]
                );

            }
        )
    );


/*
|--------------------------------------------------------------------------
| CUSTOM ANSWERS FOR THIS APPLICATION
|--------------------------------------------------------------------------
*/

$customAnswers =
    $applicationAnswers[
        $applicationId
    ] ?? [];


/*
|--------------------------------------------------------------------------
| CREATE QUICK LOOKUP BY QUESTION ID
|--------------------------------------------------------------------------
*/

$customAnswersByQuestionId = [];

foreach (
    $customAnswers
    as $answer
) {

    $questionId =
        (int)(
            $answer["question_id"]
            ?? 0
        );

    if ($questionId > 0) {

        $customAnswersByQuestionId[
            $questionId
        ] = $answer;
    }
}


/*
|--------------------------------------------------------------------------
| SCORE
|--------------------------------------------------------------------------
*/

$score =
    (float)(
        $application[
            "match_score"
        ] ?? 0
    );


$scoreClass =
    $score >= 80
        ? "strong"
        : (
            $score >= 60
                ? "good"
                : "weak"
        );


/*
|--------------------------------------------------------------------------
| MATCH BREAKDOWN
|--------------------------------------------------------------------------
*/

$breakdown =
    json_decode(
        $application[
            "match_breakdown"
        ] ?? "",
        true
    );


/*
|--------------------------------------------------------------------------
| STANDARD FIELD HELPERS
|--------------------------------------------------------------------------
*/

$fullName =
    getApplicationFieldValue(
        $application,
        "full_name"
    );

$email =
    getApplicationFieldValue(
        $application,
        "email"
    );

$phone =
    getApplicationFieldValue(
        $application,
        "phone"
    );

$yearsExperience =
    getApplicationFieldValue(
        $application,
        "years_experience"
    );

$currentLocation =
    getApplicationFieldValue(
        $application,
        "current_location"
    );

$uaeExperience =
    getApplicationFieldValue(
        $application,
        "uae_experience"
    );


/*
|--------------------------------------------------------------------------
| ENABLED STANDARD FIELD CHECKS
|--------------------------------------------------------------------------
*/

$yearsExperienceEnabled = false;
$currentLocationEnabled = false;
$uaeExperienceEnabled = false;
$emailEnabled = false;
$phoneEnabled = false;

foreach (
    $enabledFields
    as $field
) {

    $fieldName =
        $field["field"]
        ?? "";

    if (
        $fieldName ===
        "years_experience"
    ) {
        $yearsExperienceEnabled = true;
    }

    if (
        $fieldName ===
        "current_location"
    ) {
        $currentLocationEnabled = true;
    }

    if (
        $fieldName ===
        "uae_experience"
    ) {
        $uaeExperienceEnabled = true;
    }

    if (
        $fieldName ===
        "email"
    ) {
        $emailEnabled = true;
    }

    if (
        $fieldName ===
        "phone"
    ) {
        $phoneEnabled = true;
    }
}

?>


<tr>


<!-- CANDIDATE -->

<td>

<div class="candidate">

<?= e(
    $fullName ?: "-"
) ?>

</div>


<?php if ($emailEnabled): ?>

<div class="small">

<?= e(
    $email ?: "-"
) ?>

</div>

<?php endif; ?>


<?php if ($phoneEnabled): ?>

<div class="small">

<?= e(
    $phone ?: "-"
) ?>

</div>

<?php endif; ?>

</td>


<!-- POSITION -->

<td>

<?= e(
    $application[
        "job_title"
    ] ?? "-"
) ?>

</td>


<!-- EXPERIENCE -->

<td>

<?php if (
    $yearsExperienceEnabled
): ?>

<?= e(
    $yearsExperience ?: "-"
) ?>

years

<?php endif; ?>


<?php if (
    $currentLocationEnabled
): ?>

<div class="small">

<?= e(
    $currentLocation ?: "-"
) ?>

</div>

<?php endif; ?>


<?php if (
    $uaeExperienceEnabled
): ?>

<div class="small">

<?= e(
    $uaeExperience ?: "-"
) ?>

</div>

<?php endif; ?>

</td>


<!-- MATCH -->

<td>

<span
    class="score <?= e(
        $scoreClass
    ) ?>"
>

<?= number_format(
    $score,
    0
) ?>%

</span>


<?php if (
    $score >= 50
): ?>

<div class="small">

<span class="badge">
Notification threshold
</span>

</div>

<?php endif; ?>


<?php if (
    is_array($breakdown)
): ?>

<details class="breakdown">

<summary>
View score breakdown
</summary>


<?php foreach (
    $breakdown
    as $item
): ?>

<div class="breakdown-row">

<span>

<?= e(
    $item["label"]
    ?? "Field"
) ?>

</span>


<span
    class="<?= !empty(
        $item["passed"]
    )
        ? "pass"
        : "fail" ?>"
>

<?= !empty(
    $item["passed"]
)

    ? "Matched"

    : "Not matched"
?>

·

<?= number_format(
    (float)(
        $item["weight"]
        ?? 0
    ),
    0
) ?>%

</span>

</div>

<?php endforeach; ?>


</details>

<?php endif; ?>

</td>


<!-- CANDIDATE DETAILS -->

<td class="details">

<details>

<summary>
View application
</summary>


<div class="application-fields">


<?php

/*
|--------------------------------------------------------------------------
| STANDARD CONFIGURED FIELDS
|--------------------------------------------------------------------------
*/

$displayedStandardFields = [];


foreach (
    $enabledFields
    as $field
):

    $fieldName =
        trim(
            (string)(
                $field["field"]
                ?? ""
            )
        );

    $fieldLabel =
        trim(
            (string)(
                $field["label"]
                ?? ucwords(
                    str_replace(
                        "_",
                        " ",
                        $fieldName
                    )
                )
            )
        );


    /*
    |--------------------------------------------------------------------------
    | CV is displayed separately.
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $fieldName,
            [
                "cv",
                "cv_resume",
                "resume"
            ],
            true
        )
    ) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Check whether this is a normal database field.
    |--------------------------------------------------------------------------
    */

    $standardValue =
        getApplicationFieldValue(
            $application,
            $fieldName
        );


    /*
    |--------------------------------------------------------------------------
    | If the field exists directly in applications,
    | display it normally.
    |--------------------------------------------------------------------------
    */

    $isStandardField =
        array_key_exists(
            $fieldName,
            $application
        );


    /*
    |--------------------------------------------------------------------------
    | Aliases are also standard fields.
    |--------------------------------------------------------------------------
    */

    $standardAliases = [

        "name",
        "email_address",
        "phone_number",
        "location",
        "experience",
        "years_of_experience",
        "driving_license",
        "uae_driving_license",
        "cv_resume",
        "resume"

    ];


    if (
        $isStandardField ||
        in_array(
            $fieldName,
            $standardAliases,
            true
        )
    ) {

        $displayedStandardFields[
            $fieldName
        ] = true;

        $formattedValue =
            formatApplicationValue(
                $standardValue,
                $fieldName
            );

        ?>

        <div class="application-field">

            <div class="application-label">

                <?= e(
                    $fieldLabel
                ) ?>

                <?php if (
                    !empty(
                        $field["required"]
                    )
                ): ?>

                    <span
                        class="required-label"
                    >
                        Required
                    </span>

                <?php endif; ?>

            </div>

            <div class="application-value">

                <?= nl2br(
                    e(
                        $formattedValue
                    )
                ) ?>

            </div>

        </div>

        <?php
    }

endforeach;


/*
|--------------------------------------------------------------------------
| CUSTOM QUESTION ANSWERS
|--------------------------------------------------------------------------
|
| THIS IS THE IMPORTANT FIX.
|
| The candidate's custom answers are NOT stored in applications.
|
| They are stored in application_answers.
|
| We therefore display them here using the actual question definition
| and the actual submitted answer.
|--------------------------------------------------------------------------
*/

$displayedQuestionIds = [];


if (
    !empty($customAnswers)
):

    foreach (
        $customAnswers
        as $answer
    ):

        $questionId =
            (int)(
                $answer["question_id"]
                ?? 0
            );

        $questionText =
            trim(
                (string)(
                    $answer["question"]
                    ?? ""
                )
            );

        $answerValue =
            $answer["answer"]
            ?? "";


        if (
            $questionId <= 0
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate display
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $displayedQuestionIds[
                    $questionId
                ]
            )
        ) {
            continue;
        }


        $displayedQuestionIds[
            $questionId
        ] = true;


        /*
        |--------------------------------------------------------------------------
        | If question is empty, still show a safe label.
        |--------------------------------------------------------------------------
        */

        if (
            $questionText === ""
        ) {

            $questionText =
                "Application Question";
        }


        $formattedAnswer =
            formatApplicationValue(
                $answerValue
            );

        ?>


        <div class="custom-question">

            <div
                class="
                    custom-question-label
                "
            >

                <?= e(
                    $questionText
                ) ?>


                <?php if (
                    !empty(
                        $answer["required"]
                    )
                ): ?>

                    <span
                        class="required-label"
                    >
                        Required
                    </span>

                <?php endif; ?>

            </div>


            <div class="custom-answer">

                <?php if (
                    $formattedAnswer === "-"
                ): ?>

                    <span
                        class="no-answer"
                    >
                        No answer provided
                    </span>

                <?php else: ?>

                    <?= nl2br(
                        e(
                            $formattedAnswer
                        )
                    ) ?>

                <?php endif; ?>

            </div>

        </div>


        <?php

    endforeach;

endif;


/*
|--------------------------------------------------------------------------
| If absolutely nothing exists
|--------------------------------------------------------------------------
*/

if (
    empty($enabledFields) &&
    empty($customAnswers)
):

?>

<div class="small">

No application information
was submitted.

</div>

<?php endif; ?>


</div>


</details>

</td>


<!-- CV -->

<td>

<?php

$cvPath =
    $application[
        "cv_path"
    ] ?? "";


if (
    !empty($cvPath)
):

?>

<a
    class="cv"
    href="../<?= e(
        $cvPath
    ) ?>"
    target="_blank"
>

View CV

</a>

<?php else: ?>

<span class="small">
No CV
</span>

<?php endif; ?>

</td>


<!-- STATUS -->

<td>

<form
    method="POST"
    style="
        display:flex;
        gap:6px
    "
>

<input
    type="hidden"
    name="application_id"
    value="<?= (int)(
        $application["id"]
    ) ?>"
>


<select name="status">

<?php

$statuses = [

    "new" =>
        "New",

    "review" =>
        "Review",

    "shortlisted" =>
        "Shortlisted",

    "interview" =>
        "Interview",

    "rejected" =>
        "Rejected",

    "hired" =>
        "Hired"

];


foreach (
    $statuses
    as $value => $label
):

?>

<option
    value="<?= e(
        $value
    ) ?>"
    <?= (
        $application[
            "status"
        ] === $value
    )
        ? "selected"
        : "" ?>
>

<?= e(
    $label
) ?>

</option>

<?php endforeach; ?>

</select>


<button type="submit">

Save

</button>

</form>

</td>


<!-- APPLIED -->

<td>

<div class="small">

<?= e(
    date(
        "d M Y",
        strtotime(
            $application[
                "created_at"
            ]
        )
    )
) ?>

</div>

</td>


</tr>


<?php endwhile; ?>


</tbody>

</table>


<?php else: ?>


<div class="empty">

No applications received yet.

</div>


<?php endif; ?>


</div>

</div>

</body>

</html>


<?php

$conn->close();

?>