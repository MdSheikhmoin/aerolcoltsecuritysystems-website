<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");


/*
|--------------------------------------------------------------------------
| OPTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {

    http_response_code(204);

    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| RESPONSE HELPER
|--------------------------------------------------------------------------
*/

function response_json(
    bool $success,
    string $message,
    array $extra = [],
    int $status = 200
): void {

    http_response_code($status);

    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| TEXT HELPERS
|--------------------------------------------------------------------------
*/

function normalize_text($value): string
{
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

    return trim(
        (string)$value
    );
}


function lower_text($value): string
{
    return strtolower(
        normalize_text($value)
    );
}


/*
|--------------------------------------------------------------------------
| JSON CONFIG HELPER
|--------------------------------------------------------------------------
*/

function decode_config($value): ?array
{
    if (
        $value === null ||
        trim((string)$value) === ""
    ) {
        return null;
    }

    $decoded =
        json_decode(
            (string)$value,
            true
        );

    return is_array($decoded)
        ? $decoded
        : null;
}


/*
|--------------------------------------------------------------------------
| KEYWORD HELPER
|--------------------------------------------------------------------------
|
| Admin stores keyword rules as:
|
| Business Development
| Sales
| Marketing
|
| or comma/semicolon separated values.
|--------------------------------------------------------------------------
*/

function keyword_list(
    string $keywords
): array {

    if (
        trim($keywords) === ""
    ) {
        return [];
    }

    $parts =
        preg_split(
            "/[,;\n]+/",
            $keywords
        );

    $result = [];

    foreach ($parts as $item) {

        $item =
            strtolower(
                trim(
                    (string)$item
                )
            );

        if ($item !== "") {

            $result[] =
                $item;
        }
    }

    return array_values(
        array_unique(
            $result
        )
    );
}


/*
|--------------------------------------------------------------------------
| TEXT MATCH
|--------------------------------------------------------------------------
*/

function textMatchesKeywords(
    string $text,
    array $keywords
): bool {

    $text =
        strtolower(
            trim($text)
        );

    if ($text === "") {

        return false;
    }

    foreach (
        $keywords
        as $keyword
    ) {

        $keyword =
            strtolower(
                trim(
                    (string)$keyword
                )
            );

        if (
            $keyword !== "" &&
            strpos(
                $text,
                $keyword
            ) !== false
        ) {

            return true;
        }
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| DEFAULT APPLICATION CONFIG
|--------------------------------------------------------------------------
|
| ONLY used if an old job has no saved application_field_config.
|--------------------------------------------------------------------------
*/

function defaultApplicationFields(): array
{
    return [

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
            "required" => false,
            "weight" => 5
        ],

        [
            "key" => "qualification",
            "label" => "Qualification",
            "enabled" => true,
            "required" => false,
            "weight" => 10
        ],

        [
            "key" => "years_experience",
            "label" => "Years of Experience",
            "enabled" => true,
            "required" => false,
            "weight" => 20
        ],

        [
            "key" => "uae_experience",
            "label" => "UAE Experience",
            "enabled" => true,
            "required" => false,
            "weight" => 15
        ],

        [
            "key" => "relevant_experience",
            "label" => "Relevant Experience",
            "enabled" => true,
            "required" => false,
            "weight" => 15
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
            "weight" => 5
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
            "weight" => 10
        ]

    ];
}


/*
|--------------------------------------------------------------------------
| METHOD
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    response_json(
        false,
        "POST request required",
        [],
        405
    );
}


/*
|--------------------------------------------------------------------------
| MAIN
|--------------------------------------------------------------------------
*/

try {


    /*
    |--------------------------------------------------------------------------
    | BASIC INPUT
    |--------------------------------------------------------------------------
    */

    $jobSlug =
        normalize_text(
            $_POST["job_slug"]
            ?? $_POST["slug"]
            ?? ""
        );


    $jobId =
        (int)(
            $_POST["job_id"]
            ?? 0
        );


    /*
    |--------------------------------------------------------------------------
    | FIND JOB
    |--------------------------------------------------------------------------
    */

    $job = null;


    if ($jobId > 0) {

        $stmt =
            $conn->prepare(
                "
                SELECT *
                FROM jobs
                WHERE id = ?
                AND status = 'published'
                LIMIT 1
                "
            );


        if (!$stmt) {

            throw new Exception(
                "Unable to prepare job query: "
                . $conn->error
            );
        }


        $stmt->bind_param(
            "i",
            $jobId
        );


    } elseif ($jobSlug !== "") {

        $stmt =
            $conn->prepare(
                "
                SELECT *
                FROM jobs
                WHERE slug = ?
                AND status = 'published'
                LIMIT 1
                "
            );


        if (!$stmt) {

            throw new Exception(
                "Unable to prepare job query: "
                . $conn->error
            );
        }


        $stmt->bind_param(
            "s",
            $jobSlug
        );


    } else {

        response_json(
            false,
            "Job information is required.",
            [],
            422
        );
    }


    $stmt->execute();


    $result =
        $stmt->get_result();


    $job =
        $result->fetch_assoc();


    $stmt->close();


    if (!$job) {

        response_json(
            false,
            "Job not found.",
            [],
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | APPLICATION FIELD CONFIGURATION
    |--------------------------------------------------------------------------
    |
    | THIS IS THE SOURCE OF TRUTH.
    |
    | If Admin disables a field:
    |
    |     it is ignored.
    |
    | If Admin removes a field:
    |
    |     it is ignored.
    |
    | If Admin makes a field required:
    |
    |     candidate must provide it.
    |
    | If Admin adds a new field:
    |
    |     it is automatically accepted,
    |     stored and used for matching.
    |--------------------------------------------------------------------------
    */

    $fieldConfig =
        decode_config(
            $job[
                "application_field_config"
            ] ?? null
        );


    /*
    |--------------------------------------------------------------------------
    | OLD JOB FALLBACK
    |--------------------------------------------------------------------------
    */

    if ($fieldConfig === null) {

        $fieldConfig =
            defaultApplicationFields();
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE CONFIG
    |--------------------------------------------------------------------------
    */

    $normalizedFields = [];


    foreach (
        $fieldConfig
        as $field
    ) {

        if (!is_array($field)) {

            continue;
        }


        $key =
            normalize_text(
                $field["key"]
                ?? $field["field"]
                ?? ""
            );


        if ($key === "") {

            continue;
        }


        $label =
            normalize_text(
                $field["label"]
                ?? $field["title"]
                ?? $key
            );


        $enabled =
            !empty(
                $field["enabled"]
            );


        $required =
            !empty(
                $field["required"]
            );


        $weight =
            max(
                0,
                (float)(
                    $field["weight"]
                    ?? $field["importance"]
                    ?? 0
                )
            );


        $normalizedFields[] = [

            "key" =>
                $key,

            "label" =>
                $label,

            "enabled" =>
                $enabled,

            "required" =>
                $required,

            "weight" =>
                $weight
        ];
    }


    $fieldConfig =
        $normalizedFields;


    /*
    |--------------------------------------------------------------------------
    | COLLECT ALL ENABLED FORM VALUES
    |--------------------------------------------------------------------------
    |
    | THIS IS THE IMPORTANT FIX.
    |
    | Every enabled application field is collected dynamically.
    |
    | Therefore a field such as:
    |
    |     languages
    |     uae_driving_licence
    |     existing_uae_business_network
    |     mailll
    |     expected_salary
    |     anything_else
    |
    | is saved automatically.
    |--------------------------------------------------------------------------
    */

    $values = [];


    foreach (
        $fieldConfig
        as $field
    ) {

        if (
            empty(
                $field["enabled"]
            )
        ) {

            continue;
        }


        $key =
            $field["key"];


        /*
        |--------------------------------------------------------------------------
        | CV is handled separately.
        |--------------------------------------------------------------------------
        */

        if (
            $key === "cv"
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Normal POST value
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $_POST[$key]
            )
        ) {

            $values[$key] =
                normalize_text(
                    $_POST[$key]
                );

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Language alias
        |--------------------------------------------------------------------------
        */

        if (
            $key === "language"
        ) {

            $values[$key] =
                normalize_text(
                    $_POST[
                        "languages"
                    ] ?? ""
                );

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Missing field
        |--------------------------------------------------------------------------
        */

        $values[$key] =
            "";
    }


    /*
    |--------------------------------------------------------------------------
    | REQUIRED FIELDS
    |--------------------------------------------------------------------------
    */

    foreach (
        $fieldConfig
        as $field
    ) {

        if (
            empty(
                $field["enabled"]
            )
        ) {

            continue;
        }


        if (
            empty(
                $field["required"]
            )
        ) {

            continue;
        }


        $key =
            $field["key"];


        /*
        |--------------------------------------------------------------------------
        | CV handled separately.
        |--------------------------------------------------------------------------
        */

        if (
            $key === "cv"
        ) {

            continue;
        }


        $value =
            $values[$key]
            ?? "";


        if (
            trim(
                (string)$value
            ) === ""
        ) {

            response_json(
                false,
                (
                    $field["label"]
                    ?? $key
                )
                . " is required.",
                [],
                422
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EMAIL
        |--------------------------------------------------------------------------
        */

        if (
            $key === "email"
        ) {

            if (
                !filter_var(
                    $value,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                response_json(
                    false,
                    "A valid email address is required.",
                    [],
                    422
                );
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STANDARD VALUES
    |--------------------------------------------------------------------------
    |
    | These are also kept in their existing database columns
    | for compatibility with the rest of the admin system.
    |--------------------------------------------------------------------------
    */

    $fullName =
        $values[
            "full_name"
        ] ?? "";


    $email =
        $values[
            "email"
        ] ?? "";


    $phone =
        $values[
            "phone"
        ] ?? "";


    $whatsapp =
        $values[
            "whatsapp"
        ] ?? "";


    $currentLocation =
        $values[
            "current_location"
        ] ?? "";


    $qualification =
        $values[
            "qualification"
        ] ?? "";


    $yearsExperienceRaw =
        $values[
            "years_experience"
        ] ?? "";


    $yearsExperience =
        (
            $yearsExperienceRaw !== "" &&
            is_numeric(
                $yearsExperienceRaw
            )
        )
            ? (float)$yearsExperienceRaw
            : null;


    $uaeExperience =
        $values[
            "uae_experience"
        ] ?? "";


    $relevantExperience =
        $values[
            "relevant_experience"
        ] ?? "";


    $coverLetter =
        $values[
            "cover_letter"
        ] ?? "";


    /*
    |--------------------------------------------------------------------------
    | CV CONFIGURATION
    |--------------------------------------------------------------------------
    */

    $cvEnabled =
        false;


    $cvRequired =
        false;


    foreach (
        $fieldConfig
        as $field
    ) {

        if (
            $field["key"] !==
            "cv"
        ) {

            continue;
        }


        if (
            !empty(
                $field["enabled"]
            )
        ) {

            $cvEnabled =
                true;


            if (
                !empty(
                    $field["required"]
                )
            ) {

                $cvRequired =
                    true;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CV VARIABLES
    |--------------------------------------------------------------------------
    */

    $relativeCvPath =
        "";


    $originalName =
        "";


    $storedName =
        "";


    $destination =
        "";


    /*
    |--------------------------------------------------------------------------
    | CV UPLOAD
    |--------------------------------------------------------------------------
    */

    if ($cvEnabled) {


        $hasCv =
            isset(
                $_FILES["cv"]
            ) &&
            is_array(
                $_FILES["cv"]
            ) &&
            isset(
                $_FILES["cv"]["error"]
            ) &&
            $_FILES["cv"]["error"]
                !== UPLOAD_ERR_NO_FILE;


        /*
        |--------------------------------------------------------------------------
        | REQUIRED CV
        |--------------------------------------------------------------------------
        */

        if (
            $cvRequired &&
            !$hasCv
        ) {

            response_json(
                false,
                "CV / Resume is required.",
                [],
                422
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CV PROVIDED
        |--------------------------------------------------------------------------
        */

        if ($hasCv) {

            $cv =
                $_FILES["cv"];


            if (
                !isset(
                    $cv["error"]
                ) ||
                $cv["error"]
                    !== UPLOAD_ERR_OK
            ) {

                response_json(
                    false,
                    "Unable to upload the CV.",
                    [],
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | MAX SIZE
            |--------------------------------------------------------------------------
            */

            $maxSize =
                5 * 1024 * 1024;


            if (
                (int)$cv["size"]
                > $maxSize
            ) {

                response_json(
                    false,
                    "CV must be 5 MB or smaller.",
                    [],
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ORIGINAL NAME
            |--------------------------------------------------------------------------
            */

            $originalName =
                basename(
                    (string)$cv["name"]
                );


            /*
            |--------------------------------------------------------------------------
            | EXTENSION
            |--------------------------------------------------------------------------
            */

            $extension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );


            $allowedExtensions = [

                "pdf",
                "doc",
                "docx"

            ];


            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                response_json(
                    false,
                    "CV must be PDF, DOC or DOCX.",
                    [],
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | MIME
            |--------------------------------------------------------------------------
            */

            $allowedMimeTypes = [

                "pdf" => [
                    "application/pdf"
                ],

                "doc" => [
                    "application/msword"
                ],

                "docx" => [
                    "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                ]

            ];


            $detectedMime =
                "";


            if (
                function_exists(
                    "mime_content_type"
                )
            ) {

                $detectedMime =
                    @mime_content_type(
                        $cv["tmp_name"]
                    );
            }


            if (
                $detectedMime !== "" &&
                isset(
                    $allowedMimeTypes[
                        $extension
                    ]
                ) &&
                !in_array(
                    $detectedMime,
                    $allowedMimeTypes[
                        $extension
                    ],
                    true
                )
            ) {

                response_json(
                    false,
                    "Invalid CV file type.",
                    [],
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | UPLOAD DIRECTORY
            |--------------------------------------------------------------------------
            */

            $uploadDirectory =
                __DIR__
                . "/../uploads/cvs";


            if (
                !is_dir(
                    $uploadDirectory
                )
            ) {

                if (
                    !mkdir(
                        $uploadDirectory,
                        0755,
                        true
                    )
                ) {

                    response_json(
                        false,
                        "Unable to create upload directory.",
                        [],
                        500
                    );
                }
            }


            if (
                !is_writable(
                    $uploadDirectory
                )
            ) {

                response_json(
                    false,
                    "CV upload directory is not writable.",
                    [],
                    500
                );
            }


            /*
            |--------------------------------------------------------------------------
            | RANDOM FILE NAME
            |--------------------------------------------------------------------------
            */

            $storedName =
                bin2hex(
                    random_bytes(16)
                )
                . "."
                . $extension;


            $destination =
                $uploadDirectory
                . "/"
                . $storedName;


            /*
            |--------------------------------------------------------------------------
            | MOVE FILE
            |--------------------------------------------------------------------------
            */

            if (
                !move_uploaded_file(
                    $cv["tmp_name"],
                    $destination
                )
            ) {

                response_json(
                    false,
                    "Unable to save the CV.",
                    [],
                    500
                );
            }


            $relativeCvPath =
                "uploads/cvs/"
                . $storedName;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STORE CV PATH IN VALUES
    |--------------------------------------------------------------------------
    */

    if ($cvEnabled) {

        $values["cv"] =
            $relativeCvPath;
    }


    /*
    |--------------------------------------------------------------------------
    | MATCHING RULES
    |--------------------------------------------------------------------------
    */

    $rules =
        decode_config(
            $job[
                "matching_rules"
            ] ?? null
        );


    if (
        $rules === null
    ) {

        $rules = [];
    }


    $minimumExperience =
        (float)(
            $rules[
                "minimum_experience"
            ] ?? 0
        );


    $maximumPreferredExperience =
        (float)(
            $rules[
                "maximum_preferred_experience"
            ] ?? 0
        );


    $uaeExperienceRequired =
        !empty(
            $rules[
                "uae_experience_required"
            ]
        );


    $qualificationKeywords =
        keyword_list(
            (string)(
                $rules[
                    "qualification_keywords"
                ] ?? ""
            )
        );


    $relevantKeywords =
        keyword_list(
            (string)(
                $rules[
                    "relevant_experience_keywords"
                ] ?? ""
            )
        );


    $locationKeywords =
        keyword_list(
            (string)(
                $rules[
                    "location_keywords"
                ] ?? ""
            )
        );


    /*
    |--------------------------------------------------------------------------
    | MATCHING
    |--------------------------------------------------------------------------
    */

    $totalWeight =
        0.0;


    $earnedWeight =
        0.0;


    $breakdown =
        [];


    foreach (
        $fieldConfig
        as $field
    ) {


        /*
        |--------------------------------------------------------------------------
        | DISABLED FIELD
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $field["enabled"]
            )
        ) {

            continue;
        }


        $key =
            $field["key"];


        $weight =
            max(
                0,
                (float)(
                    $field["weight"]
                    ?? 0
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Zero-weight field
        |--------------------------------------------------------------------------
        |
        | It is stored, but does not affect matching.
        |--------------------------------------------------------------------------
        */

        if (
            $weight <= 0
        ) {

            continue;
        }


        $totalWeight +=
            $weight;


        $passed =
            false;


        $value =
            $values[$key]
            ?? "";


        /*
        |--------------------------------------------------------------------------
        | MATCHING RULE BY FIELD
        |--------------------------------------------------------------------------
        */

        switch ($key) {


            /*
            |------------------------------------------------------------------
            | YEARS EXPERIENCE
            |------------------------------------------------------------------
            */

            case "years_experience":

                $years =
                    is_numeric(
                        $value
                    )
                        ? (float)$value
                        : 0;


                if (
                    $minimumExperience <= 0
                ) {

                    $passed =
                        $value !== "";

                } else {

                    $passed =
                        $years >=
                        $minimumExperience;
                }


                if (
                    $maximumPreferredExperience > 0 &&
                    $years >
                    $maximumPreferredExperience
                ) {

                    $passed =
                        false;
                }

                break;


            /*
            |------------------------------------------------------------------
            | QUALIFICATION
            |------------------------------------------------------------------
            */

            case "qualification":

                if (
                    empty(
                        $qualificationKeywords
                    )
                ) {

                    $passed =
                        trim(
                            (string)$value
                        ) !== "";

                } else {

                    $passed =
                        textMatchesKeywords(
                            (string)$value,
                            $qualificationKeywords
                        );
                }

                break;


            /*
            |------------------------------------------------------------------
            | CURRENT LOCATION
            |------------------------------------------------------------------
            */

            case "current_location":

                if (
                    empty(
                        $locationKeywords
                    )
                ) {

                    $passed =
                        trim(
                            (string)$value
                        ) !== "";

                } else {

                    $passed =
                        textMatchesKeywords(
                            (string)$value,
                            $locationKeywords
                        );
                }

                break;


            /*
            |------------------------------------------------------------------
            | UAE EXPERIENCE
            |------------------------------------------------------------------
            */

            case "uae_experience":

                $uae =
                    lower_text(
                        $value
                    );


                $hasUaeExperience =
                    textMatchesKeywords(
                        $uae,
                        [
                            "yes",
                            "uae",
                            "dubai",
                            "abu dhabi",
                            "sharjah",
                            "ajman",
                            "ras al khaimah",
                            "fujairah",
                            "umm al quwain"
                        ]
                    );


                if (
                    $uaeExperienceRequired
                ) {

                    $passed =
                        $hasUaeExperience;

                } else {

                    $passed =
                        $uae !== "";
                }

                break;


            /*
            |------------------------------------------------------------------
            | RELEVANT EXPERIENCE
            |------------------------------------------------------------------
            */

            case "relevant_experience":

                if (
                    empty(
                        $relevantKeywords
                    )
                ) {

                    $passed =
                        trim(
                            (string)$value
                        ) !== "";

                } else {

                    $passed =
                        textMatchesKeywords(
                            (string)$value,
                            $relevantKeywords
                        );
                }

                break;


            /*
            |------------------------------------------------------------------
            | LANGUAGES
            |------------------------------------------------------------------
            */

            case "languages":

                $passed =
                    trim(
                        (string)$value
                    ) !== "";

                break;


            /*
            |------------------------------------------------------------------
            | UAE DRIVING LICENCE
            |------------------------------------------------------------------
            */

            case "uae_driving_licence":

                $licence =
                    lower_text(
                        $value
                    );


                $passed =
                    in_array(
                        $licence,
                        [
                            "yes",
                            "true",
                            "1",
                            "valid",
                            "uae",
                            "uae driving licence",
                            "uae driving license"
                        ],
                        true
                    );

                break;


            /*
            |------------------------------------------------------------------
            | UAE BUSINESS NETWORK
            |------------------------------------------------------------------
            */

            case "existing_uae_business_network":

                $passed =
                    trim(
                        (string)$value
                    ) !== "";

                break;


            /*
            |------------------------------------------------------------------
            | CV
            |------------------------------------------------------------------
            */

            case "cv":

                $passed =
                    $destination !== "" &&
                    is_file(
                        $destination
                    );

                break;


            /*
            |------------------------------------------------------------------
            | STANDARD TEXT FIELDS
            |------------------------------------------------------------------
            */

            case "full_name":
            case "email":
            case "phone":
            case "whatsapp":
            case "cover_letter":

                $passed =
                    trim(
                        (string)$value
                    ) !== "";

                break;


            /*
            |------------------------------------------------------------------
            | ANY CUSTOM FIELD
            |------------------------------------------------------------------
            |
            | This is what makes newly added Admin fields work automatically.
            |--------------------------------------------------------------------------
            */

            default:

                $passed =
                    trim(
                        (string)$value
                    ) !== "";

                break;
        }


        /*
        |--------------------------------------------------------------------------
        | EARN WEIGHT
        |--------------------------------------------------------------------------
        */

        if (
            $passed
        ) {

            $earnedWeight +=
                $weight;
        }


        /*
        |--------------------------------------------------------------------------
        | BREAKDOWN
        |--------------------------------------------------------------------------
        */

        $breakdown[] = [

            "key" =>
                $key,

            "label" =>
                $field["label"],

            "weight" =>
                $weight,

            "passed" =>
                $passed

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MATCH SCORE
    |--------------------------------------------------------------------------
    */

    $matchScore =
        0;


    if (
        $totalWeight > 0
    ) {

        $matchScore =
            round(
                (
                    $earnedWeight /
                    $totalWeight
                ) * 100,
                2
            );
    }


    /*
    |--------------------------------------------------------------------------
    | JSON DATA TO STORE
    |--------------------------------------------------------------------------
    |
    | THIS contains the candidate's actual answers.
    |
    | Example:
    |
    | {
    |   "full_name": "Moin Khan",
    |   "email": "example@gmail.com",
    |   "languages": "English, Arabic",
    |   "uae_driving_licence": "Yes",
    |   "existing_uae_business_network": "Yes",
    |   "mailll": "candidate answer"
    | }
    |--------------------------------------------------------------------------
    */

    $applicationDataJson =
        json_encode(
            $values,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


    if (
        $applicationDataJson === false
    ) {

        throw new Exception(
            "Unable to encode application data."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MATCH BREAKDOWN JSON
    |--------------------------------------------------------------------------
    */

    $matchBreakdownJson =
        json_encode(
            $breakdown,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );


    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    $status =
        "new";


    /*
    |--------------------------------------------------------------------------
    | STORE APPLICATION
    |--------------------------------------------------------------------------
    |
    | The applications table contains the fixed/legacy fields.
    |
    | IMPORTANT:
    | application_data is NOT assumed to exist in the database.
    |
    | Custom fields from application_field_config are stored in:
    |
    |     job_questions
    |     application_answers
    |
    | This keeps the application form dynamic without requiring a new
    | database column every time Admin adds a field.
    |--------------------------------------------------------------------------
    */

    $transactionStarted = false;

    $conn->begin_transaction();

    $transactionStarted = true;


    /*
    |--------------------------------------------------------------------------
    | INSERT FIXED APPLICATION COLUMNS
    |--------------------------------------------------------------------------
    */

    $sql = "

        INSERT INTO applications (

            job_id,
            full_name,
            email,
            phone,
            whatsapp,
            current_location,
            qualification,
            years_experience,
            uae_experience,
            relevant_experience,
            cover_letter,
            cv_original_name,
            cv_stored_name,
            cv_path,
            match_score,
            match_breakdown,
            status

        )

        VALUES (

            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?

        )

    ";


    $stmt =
        $conn->prepare(
            $sql
        );


    if (!$stmt) {

        if (
            $destination !== ""
        ) {

            @unlink(
                $destination
            );
        }


        throw new Exception(
            "Unable to prepare application insert: "
            . $conn->error
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BIND
    |--------------------------------------------------------------------------
    |
    | 17 database values:
    |
    | i = job_id
    | d = years_experience
    | d = match_score
    | all remaining values = strings
    |--------------------------------------------------------------------------
    */

    $stmt->bind_param(

        "issssssdssssssdss",

        $job["id"],
        $fullName,
        $email,
        $phone,
        $whatsapp,
        $currentLocation,
        $qualification,
        $yearsExperience,
        $uaeExperience,
        $relevantExperience,
        $coverLetter,
        $originalName,
        $storedName,
        $relativeCvPath,
        $matchScore,
        $matchBreakdownJson,
        $status
    );


    /*
    |--------------------------------------------------------------------------
    | EXECUTE APPLICATION INSERT
    |--------------------------------------------------------------------------
    */

    if (
        !$stmt->execute()
    ) {

        if (
            $destination !== ""
        ) {

            @unlink(
                $destination
            );
        }


        $error =
            $stmt->error;


        $stmt->close();


        throw new Exception(
            "Unable to save application: "
            . $error
        );
    }


    /*
    |--------------------------------------------------------------------------
    | APPLICATION ID
    |--------------------------------------------------------------------------
    */

    $applicationId =
        $stmt->insert_id;


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | SAVE DYNAMIC APPLICATION FIELDS
    |--------------------------------------------------------------------------
    |
    | Any enabled field that is NOT already represented by a fixed
    | applications column is saved through:
    |
    |     job_questions
    |     application_answers
    |
    | This includes Admin-added fields such as Languages,
    | UAE Driving Licence, Existing UAE Business Network, mailll,
    | and future custom fields.
    |--------------------------------------------------------------------------
    */

    $fixedApplicationKeys = [

        "full_name",
        "email",
        "phone",
        "whatsapp",
        "current_location",
        "qualification",
        "years_experience",
        "uae_experience",
        "relevant_experience",
        "cover_letter",
        "cv"

    ];


    $questionLookupStmt =
        $conn->prepare(
            "
            SELECT id
            FROM job_questions
            WHERE job_id = ?
            AND question = ?
            LIMIT 1
            "
        );


    if (!$questionLookupStmt) {

        throw new Exception(
            "Unable to prepare dynamic field lookup: "
            . $conn->error
        );
    }


    $questionInsertStmt =
        $conn->prepare(
            "
            INSERT INTO job_questions (
                job_id,
                question,
                question_type,
                options,
                required,
                sort_order
            )
            VALUES (?, ?, 'text', NULL, ?, ?)
            "
        );


    if (!$questionInsertStmt) {

        $questionLookupStmt->close();

        throw new Exception(
            "Unable to prepare dynamic question insert: "
            . $conn->error
        );
    }


    $answerInsertStmt =
        $conn->prepare(
            "
            INSERT INTO application_answers (
                application_id,
                question_id,
                answer
            )
            VALUES (?, ?, ?)
            "
        );


    if (!$answerInsertStmt) {

        $questionLookupStmt->close();
        $questionInsertStmt->close();

        throw new Exception(
            "Unable to prepare application answer insert: "
            . $conn->error
        );
    }


    $dynamicSortOrder = 1000;


    foreach (
        $fieldConfig
        as $field
    ) {

        if (
            empty(
                $field["enabled"]
            )
        ) {

            continue;
        }


        $key =
            normalize_text(
                $field["key"]
                ?? ""
            );


        /*
        |--------------------------------------------------------------------------
        | Skip fixed application columns.
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $key,
                $fixedApplicationKeys,
                true
            )
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Candidate's actual answer.
        |--------------------------------------------------------------------------
        */

        $answer =
            normalize_text(
                $values[$key]
                ?? ""
            );


        /*
        |--------------------------------------------------------------------------
        | Empty optional fields do not need an answer row.
        |--------------------------------------------------------------------------
        */

        if (
            $answer === ""
        ) {

            continue;
        }


        $questionText =
            normalize_text(
                $field["label"]
                ?? $key
            );


        if (
            $questionText === ""
        ) {

            $questionText =
                $key;
        }


        /*
        |--------------------------------------------------------------------------
        | Find existing question.
        |--------------------------------------------------------------------------
        */

        $questionId = 0;


        $questionLookupStmt->bind_param(
            "is",
            $job["id"],
            $questionText
        );


        $questionLookupStmt->execute();


        $questionResult =
            $questionLookupStmt->get_result();


        if (
            $existingQuestion =
                $questionResult->fetch_assoc()
        ) {

            $questionId =
                (int)(
                    $existingQuestion["id"]
                    ?? 0
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Create question automatically for a new Admin field.
        |--------------------------------------------------------------------------
        */

        if (
            $questionId <= 0
        ) {

            $requiredValue =
                !empty(
                    $field["required"]
                )
                    ? 1
                    : 0;


            $sortOrder =
                $dynamicSortOrder;


            $dynamicSortOrder += 1;


            $questionInsertStmt->bind_param(
                "isii",
                $job["id"],
                $questionText,
                $requiredValue,
                $sortOrder
            );


            if (
                !$questionInsertStmt->execute()
            ) {

                throw new Exception(
                    "Unable to create dynamic application field: "
                    . $questionInsertStmt->error
                );
            }


            $questionId =
                $questionInsertStmt->insert_id;
        }


        /*
        |--------------------------------------------------------------------------
        | Save the candidate's actual answer.
        |--------------------------------------------------------------------------
        */

        $answerInsertStmt->bind_param(
            "iis",
            $applicationId,
            $questionId,
            $answer
        );


        if (
            !$answerInsertStmt->execute()
        ) {

            throw new Exception(
                "Unable to save application field answer: "
                . $answerInsertStmt->error
            );
        }
    }


    $questionLookupStmt->close();
    $questionInsertStmt->close();
    $answerInsertStmt->close();


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    $transactionStarted = false;


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    response_json(

        true,

        "Application submitted successfully.",

        [

            "application_id" =>
                $applicationId,

            "match_score" =>
                $matchScore,

            "match_breakdown" =>
                $breakdown

        ]

    );

} catch (Throwable $e) {

    if (!empty($transactionStarted)) {

        try {

            $conn->rollback();

        } catch (Throwable $rollbackError) {

            // Ignore rollback errors while handling the original error.

        }
    }



    /*
    |--------------------------------------------------------------------------
    | LOG
    |--------------------------------------------------------------------------
    */

    error_log(
        "Aerol Colt apply.php: "
        . $e->getMessage()
    );


    /*
    |--------------------------------------------------------------------------
    | ERROR RESPONSE
    |--------------------------------------------------------------------------
    */

    http_response_code(
        500
    );


    echo json_encode(

        [

            "success" =>
                false,

            "message" =>
                "Unable to submit application"

        ],

        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES

    );


    exit;
}


$conn->close();

?>