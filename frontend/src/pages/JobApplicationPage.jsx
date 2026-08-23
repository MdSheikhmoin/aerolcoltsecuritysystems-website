import { ArrowLeft, Upload, CheckCircle2 } from "lucide-react";
import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

import logo from "@/assets/images/logo.png";

const JOBS_API =
  "https://aerolcoltsecuritysystems.ae/backend/api/jobs.php";

const APPLY_API =
  "https://aerolcoltsecuritysystems.ae/backend/api/apply.php";

/*
|--------------------------------------------------------------------------
| DEFAULT APPLICATION FIELDS
|--------------------------------------------------------------------------
|
| These are used ONLY when the database has NO saved
| application_field_config at all.
|
| Once the admin portal has saved a configuration,
| that configuration becomes the source of truth.
|
*/

const DEFAULT_FIELDS = [
  {
    key: "full_name",
    label: "Full Name",
    enabled: true,
    required: true,
    weight: 0,
  },
  {
    key: "email",
    label: "Email",
    enabled: true,
    required: true,
    weight: 0,
  },
  {
    key: "phone",
    label: "Phone",
    enabled: true,
    required: true,
    weight: 0,
  },
  {
    key: "current_location",
    label: "Current Location",
    enabled: true,
    required: false,
    weight: 0,
  },
  {
    key: "qualification",
    label: "Qualification",
    enabled: true,
    required: false,
    weight: 0,
  },
  {
    key: "years_experience",
    label: "Years of Experience",
    enabled: true,
    required: false,
    weight: 0,
  },
  {
    key: "cover_letter",
    label: "Cover Letter",
    enabled: true,
    required: false,
    weight: 0,
  },
  {
    key: "cv",
    label: "CV / Resume",
    enabled: true,
    required: true,
    weight: 0,
  },
];

/*
|--------------------------------------------------------------------------
| FIELD LABELS
|--------------------------------------------------------------------------
*/

const DEFAULT_LABELS = {
  full_name: "Full Name",
  email: "Email",
  phone: "Phone",
  whatsapp: "WhatsApp",
  current_location: "Current Location",
  qualification: "Qualification",
  years_experience: "Years of Experience",
  languages: "Languages",
  language: "Languages",
  uae_driving_licence: "UAE Driving Licence",
  existing_uae_business_network:
    "Existing UAE Business Network",
  cover_letter: "Cover Letter",
  cv: "CV / Resume",
};

/*
|--------------------------------------------------------------------------
| NORMALIZE APPLICATION CONFIG
|--------------------------------------------------------------------------
|
| The admin portal may return:
|
| [
|   {...},
|   {...}
| ]
|
| or occasionally an object containing fields.
|
| This function makes the frontend tolerant of both.
|
*/

function normalizeFields(rawFields) {
  /*
   * Accept the configuration in every format the API may return:
   * array, JSON string, { fields: [...] }, or keyed object.
   */

  if (
    rawFields === null ||
    rawFields === undefined ||
    rawFields === ""
  ) {
    return null;
  }

  let value = rawFields;

  if (typeof value === "string") {
    try {
      value = JSON.parse(value);
    } catch {
      return null;
    }
  }

  if (
    value &&
    typeof value === "object" &&
    !Array.isArray(value)
  ) {
    if (Array.isArray(value.fields)) {
      value = value.fields;
    } else if (Array.isArray(value.application_fields)) {
      value = value.application_fields;
    }
  }

  if (Array.isArray(value)) {
    return value
      .filter(
        (field) =>
          field &&
          typeof field === "object" &&
          field.key
      )
      .map((field) => ({
        key: String(field.key),

        label: String(
          field.label ||
            DEFAULT_LABELS[field.key] ||
            field.key
        ),

        enabled:
          field.enabled === true ||
          field.enabled === 1 ||
          field.enabled === "1" ||
          field.enabled === "true",

        required:
          field.required === true ||
          field.required === 1 ||
          field.required === "1" ||
          field.required === "true",

        weight:
          Number(field.weight || 0),
      }));
  }

  if (
    value &&
    typeof value === "object"
  ) {
    return Object.entries(value)
      .filter(
        ([, field]) =>
          field &&
          typeof field === "object"
      )
      .map(([key, field]) => ({
        key: String(
          field.key || key
        ),

        label: String(
          field.label ||
            DEFAULT_LABELS[key] ||
            key
        ),

        enabled:
          field.enabled === true ||
          field.enabled === 1 ||
          field.enabled === "1" ||
          field.enabled === "true",

        required:
          field.required === true ||
          field.required === 1 ||
          field.required === "1" ||
          field.required === "true",

        weight:
          Number(field.weight || 0),
      }));
  }

  return null;
}

/*
|--------------------------------------------------------------------------
| GET SAVED APPLICATION FIELDS
|--------------------------------------------------------------------------
*/

function getApplicationFields(job) {
  /*
   * Admin-saved application_field_config is the source
   * of truth. Defaults are used only when the database
   * has no saved configuration at all.
   */

  const savedConfig =
    job?.application_field_config;

  if (
    savedConfig === null ||
    savedConfig === undefined ||
    savedConfig === ""
  ) {
    return DEFAULT_FIELDS;
  }

  const normalized =
    normalizeFields(savedConfig);

  /*
   * IMPORTANT:
   * A saved configuration remains authoritative even
   * when every field is disabled.
   */

  if (normalized !== null) {
    return normalized;
  }

  return [];
}

/*
|--------------------------------------------------------------------------
| INITIAL FORM
|--------------------------------------------------------------------------
*/

function getInitialForm(fields) {
  const form = {};

  fields.forEach((field) => {
    form[field.key] = "";
  });

  return form;
}

/*
|--------------------------------------------------------------------------
| LABEL
|--------------------------------------------------------------------------
*/

function getFieldLabel(field) {
  if (
    field?.label &&
    String(field.label).trim()
  ) {
    return String(field.label);
  }

  return (
    DEFAULT_LABELS[field?.key] ||
    field?.key ||
    "Field"
  );
}

/*
|--------------------------------------------------------------------------
| FIELD TYPES
|--------------------------------------------------------------------------
*/

function isTextareaField(key) {
  return [
    "cover_letter",
    "existing_uae_business_network",
  ].includes(key);
}

function isSelectField(key) {
  return key === "uae_driving_licence";
}

/*
|--------------------------------------------------------------------------
| COMPONENT
|--------------------------------------------------------------------------
*/

export default function JobApplicationPage() {
  const navigate = useNavigate();
  const { slug } = useParams();

  const [job, setJob] = useState(null);
  const [fields, setFields] = useState([]);
  const [form, setForm] = useState({});
  const [cvFile, setCvFile] = useState(null);

  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] =
    useState(false);

  const [error, setError] = useState("");
  const [success, setSuccess] =
    useState(false);

  /*
  |--------------------------------------------------------------------------
  | LOAD JOB
  |--------------------------------------------------------------------------
  */

  useEffect(() => {
    const loadJob = async () => {
      try {
        setLoading(true);
        setError("");
        setSuccess(false);

        const response = await fetch(
          `${JOBS_API}?_=${Date.now()}`,
          {
            method: "GET",
            headers: {
              Accept:
                "application/json",
              "Cache-Control": "no-cache",
              Pragma: "no-cache",
            },
            cache: "no-store",
          }
        );

        const data =
          await response.json();

        if (
          !response.ok ||
          !data.success
        ) {
          throw new Error(
            data.message ||
              "Unable to load job."
          );
        }

        const jobs =
          Array.isArray(data.jobs)
            ? data.jobs
            : [];

        const foundJob =
          jobs.find(
            (item) =>
              String(item.slug) ===
              String(slug)
          );

        if (!foundJob) {
          throw new Error(
            "Job not found."
          );
        }

        /*
         * Get saved application configuration.
         */

        const configuredFields =
          getApplicationFields(
            foundJob
          );

        /*
         * ONLY enabled fields
         * appear on the live form.
         */

        const enabledFields =
          configuredFields.filter(
            (field) =>
              field.enabled === true
          );

        setJob(foundJob);

        setFields(
          enabledFields
        );

        setForm(
          getInitialForm(
            enabledFields
          )
        );
      } catch (err) {
        console.error(
          "Job application load error:",
          err
        );

        setError(
          err.message ||
            "Unable to load job."
        );
      } finally {
        setLoading(false);
      }
    };

    if (slug) {
      loadJob();
    }
  }, [slug]);

  /*
  |--------------------------------------------------------------------------
  | INPUT CHANGE
  |--------------------------------------------------------------------------
  */

  const handleChange = (event) => {
    const {
      name,
      value,
    } = event.target;

    setForm((previous) => ({
      ...previous,
      [name]: value,
    }));
  };

  /*
  |--------------------------------------------------------------------------
  | CV CHANGE
  |--------------------------------------------------------------------------
  */

  const handleCvChange = (event) => {
    const file =
      event.target.files?.[0] ||
      null;

    setCvFile(file);
    setError("");
  };

  /*
  |--------------------------------------------------------------------------
  | SUBMIT
  |--------------------------------------------------------------------------
  */

  const handleSubmit = async (
    event
  ) => {
    event.preventDefault();

    setError("");
    setSuccess(false);

    /*
     * Validate every enabled required
     * application field.
     */

    for (const field of fields) {
      if (!field.required) {
        continue;
      }

      /*
       * CV is validated separately.
       */

      if (field.key === "cv") {
        continue;
      }

      const value =
        String(
          form[field.key] || ""
        ).trim();

      if (!value) {
        setError(
          `${getFieldLabel(
            field
          )} is required.`
        );

        return;
      }
    }

    /*
     * CV validation.
     */

    const cvField =
      fields.find(
        (field) =>
          field.key === "cv"
      );

    if (
      cvField &&
      cvField.required &&
      !cvFile
    ) {
      setError(
        "CV / Resume is required."
      );

      return;
    }

    /*
     * If CV is optional but uploaded,
     * it is still validated.
     */

    if (cvFile) {
      const maxSize =
        5 * 1024 * 1024;

      if (
        cvFile.size >
        maxSize
      ) {
        setError(
          "CV / Resume must be 5 MB or smaller."
        );

        return;
      }

      const allowedTypes = [
        "pdf",
        "doc",
        "docx",
      ];

      const extension =
        cvFile.name
          .split(".")
          .pop()
          ?.toLowerCase();

      if (
        !extension ||
        !allowedTypes.includes(
          extension
        )
      ) {
        setError(
          "Only PDF, DOC or DOCX files are allowed."
        );

        return;
      }
    }

    try {
      setSubmitting(true);

      const formData =
        new FormData();

      /*
       * JOB
       */

      formData.append(
        "job_id",
        String(job.id)
      );

      formData.append(
        "job_slug",
        job.slug || ""
      );

      /*
       * IMPORTANT:
       *
       * Send ONLY enabled fields.
       *
       * Disabled fields are not sent.
       */

      fields.forEach((field) => {
        if (field.key === "cv") {
          return;
        }

        formData.append(
          field.key,
          form[field.key] ?? ""
        );
      });

      /*
       * CV
       */

      if (cvFile) {
        formData.append(
          "cv",
          cvFile
        );
      }

      /*
       * SUBMIT
       */

      const response =
        await fetch(
          APPLY_API,
          {
            method: "POST",
            body: formData,
          }
        );

      let data = null;

      try {
        data =
          await response.json();
      } catch {
        throw new Error(
          "The server returned an invalid response."
        );
      }

      if (
        !response.ok ||
        !data?.success
      ) {
        throw new Error(
          data?.message ||
            "Unable to submit application."
        );
      }

      /*
       * SUCCESS
       */

      setSuccess(true);

      setForm(
        getInitialForm(
          fields
        )
      );

      setCvFile(null);

      const fileInput =
        document.getElementById(
          "cv-upload"
        );

      if (fileInput) {
        fileInput.value = "";
      }
    } catch (err) {
      console.error(
        "Application submit error:",
        err
      );

      setError(
        err.message ||
          "Unable to submit application."
      );
    } finally {
      setSubmitting(false);
    }
  };

  /*
  |--------------------------------------------------------------------------
  | LOADING
  |--------------------------------------------------------------------------
  */

  if (loading) {
    return (
      <main className="min-h-screen bg-[#05050A] text-white">
        <div className="mx-auto max-w-5xl px-6 py-20">
          <p className="text-[#94A3B8]">
            Loading application...
          </p>
        </div>
      </main>
    );
  }

  /*
  |--------------------------------------------------------------------------
  | JOB ERROR
  |--------------------------------------------------------------------------
  */

  if (!job) {
    return (
      <main className="min-h-screen bg-[#05050A] text-white">
        <div className="mx-auto max-w-5xl px-6 py-20">

          <div className="rounded-2xl border border-red-500/30 bg-red-500/5 p-8">
            <p className="text-red-300">
              {error ||
                "Job not found."}
            </p>
          </div>

          <button
            type="button"
            onClick={() =>
              navigate(
                "/careers"
              )
            }
            className="mt-6 inline-flex items-center gap-2 text-[#94A3B8] hover:text-white"
          >
            <ArrowLeft className="h-4 w-4" />
            Back to Careers
          </button>

        </div>
      </main>
    );
  }

  /*
  |--------------------------------------------------------------------------
  | PAGE
  |--------------------------------------------------------------------------
  */

  return (
    <main className="min-h-screen bg-[#05050A] text-white">

      {/* HEADER */}

      <header className="border-b border-[#1E2235]">

        <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">

          <div className="flex items-center py-6">

            <div className="flex items-center gap-4">

              <img
                src={logo}
                alt="Aerol Colt Security Systems"
                className="h-14 w-14 object-contain"
              />

              <div className="flex flex-col">

                <span className="text-lg md:text-xl font-bold leading-tight text-white">
                  Aerol Colt
                </span>

                <span className="mt-1 text-[11px] md:text-xs tracking-[0.22em] text-[#94A3B8]">
                  SECURITY SYSTEMS
                </span>

              </div>

            </div>

          </div>

        </div>

      </header>

      {/* PAGE */}

      <section className="relative">

        <div className="absolute inset-0 bg-grid opacity-[0.15] pointer-events-none" />

        <div className="relative mx-auto max-w-5xl px-6 md:px-10 py-10 md:py-16">

          {/* BACK */}

          <button
            type="button"
            onClick={() =>
              navigate(
                `/careers/job/${job.slug}`
              )
            }
            className="inline-flex items-center gap-2 text-[#94A3B8] hover:text-white transition-colors font-medium"
          >
            <ArrowLeft className="h-4 w-4" />
            Back to Position
          </button>

          {/* TITLE */}

          <div className="mt-10 mb-10">

            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              Application
            </div>

            <h1 className="font-display font-bold text-3xl md:text-4xl tracking-tight">
              Apply for {job.title}
            </h1>

            <p className="mt-4 text-[#94A3B8] text-lg">
              Complete the form below and upload your CV.
            </p>

          </div>

          {/* SUCCESS */}

          {success && (
            <div className="mb-8 rounded-2xl border border-green-500/30 bg-green-500/5 p-6">

              <div className="flex items-start gap-4">

                <CheckCircle2 className="h-6 w-6 text-green-400 shrink-0" />

                <div>

                  <h2 className="font-semibold text-green-300 text-lg">
                    Application submitted successfully
                  </h2>

                  <p className="mt-2 text-[#94A3B8]">
                    Thank you for applying. Your application has been received.
                  </p>

                </div>

              </div>

            </div>
          )}

          {/* ERROR */}

          {error && (
            <div className="mb-8 rounded-2xl border border-red-500/30 bg-red-500/5 p-6">
              <p className="text-red-300">
                {error}
              </p>
            </div>
          )}

          {/* FORM */}

          {!success && (
            <form
              onSubmit={handleSubmit}
              encType="multipart/form-data"
              className="space-y-8"
            >

              <div className="rounded-2xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#080A11] p-6 md:p-8">

                {fields.length === 0 ? (

                  <div className="rounded-xl border border-[#263044] bg-[#080A11] p-6">

                    <p className="text-[#94A3B8]">
                      No application fields are currently enabled for this position.
                    </p>

                  </div>

                ) : (

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {fields.map(
                      (field) => {

                        const key =
                          field.key;

                        const label =
                          getFieldLabel(
                            field
                          );

                        const required =
                          field.required;

                        /*
                        |--------------------------------------------------------------------------
                        | CV
                        |--------------------------------------------------------------------------
                        */

                        if (
                          key === "cv"
                        ) {
                          return (
                            <div
                              key={key}
                              className="md:col-span-2"
                            >

                              <label
                                htmlFor="cv-upload"
                                className="block text-sm font-semibold text-white mb-3"
                              >

                                {label}

                                {required && (
                                  <span className="text-red-400 ml-1">
                                    *
                                  </span>
                                )}

                              </label>

                              <label
                                htmlFor="cv-upload"
                                className="flex flex-col items-center justify-center rounded-xl border border-dashed border-[#334155] bg-[#080A11] px-6 py-10 cursor-pointer hover:border-[#0055FF] transition-colors"
                              >

                                <Upload className="h-8 w-8 text-[#00E5FF] mb-3" />

                                <span className="text-white font-medium">

                                  {cvFile
                                    ? cvFile.name
                                    : "Choose your CV / Resume"}

                                </span>

                                <span className="mt-2 text-sm text-[#64748B]">
                                  PDF, DOC or DOCX — maximum 5 MB
                                </span>

                                <input
                                  id="cv-upload"
                                  name="cv"
                                  type="file"
                                  accept=".pdf,.doc,.docx"
                                  onChange={
                                    handleCvChange
                                  }
                                  className="hidden"
                                />

                              </label>

                            </div>
                          );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | TEXTAREA
                        |--------------------------------------------------------------------------
                        */

                        if (
                          isTextareaField(
                            key
                          )
                        ) {
                          return (
                            <div
                              key={key}
                              className="md:col-span-2"
                            >

                              <label
                                htmlFor={key}
                                className="block text-sm font-semibold text-white mb-3"
                              >

                                {label}

                                {required && (
                                  <span className="text-red-400 ml-1">
                                    *
                                  </span>
                                )}

                              </label>

                              <textarea
                                id={key}
                                name={key}
                                value={
                                  form[key] ||
                                  ""
                                }
                                onChange={
                                  handleChange
                                }
                                required={
                                  required
                                }
                                rows={6}
                                className="w-full rounded-xl border border-[#263044] bg-[#080A11] px-4 py-3 text-white outline-none focus:border-[#0055FF] transition-colors resize-y"
                              />

                            </div>
                          );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SELECT
                        |--------------------------------------------------------------------------
                        */

                        if (
                          isSelectField(
                            key
                          )
                        ) {
                          return (
                            <div key={key}>

                              <label
                                htmlFor={key}
                                className="block text-sm font-semibold text-white mb-3"
                              >

                                {label}

                                {required && (
                                  <span className="text-red-400 ml-1">
                                    *
                                  </span>
                                )}

                              </label>

                              <select
                                id={key}
                                name={key}
                                value={
                                  form[key] ||
                                  ""
                                }
                                onChange={
                                  handleChange
                                }
                                required={
                                  required
                                }
                                className="w-full rounded-xl border border-[#263044] bg-[#080A11] px-4 py-3 text-white outline-none focus:border-[#0055FF] transition-colors"
                              >

                                <option value="">
                                  Select
                                </option>

                                <option value="Yes">
                                  Yes
                                </option>

                                <option value="No">
                                  No
                                </option>

                              </select>

                            </div>
                          );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NORMAL INPUT
                        |--------------------------------------------------------------------------
                        */

                        let inputType =
                          "text";

                        if (
                          key ===
                          "email"
                        ) {
                          inputType =
                            "email";
                        }

                        if (
                          key ===
                          "phone"
                        ) {
                          inputType =
                            "tel";
                        }

                        /*
                         * Keep years_experience
                         * compatible with both
                         * numeric and text data.
                         */

                        if (
                          key ===
                          "years_experience"
                        ) {
                          inputType =
                            "text";
                        }

                        return (
                          <div key={key}>

                            <label
                              htmlFor={key}
                              className="block text-sm font-semibold text-white mb-3"
                            >

                              {label}

                              {required && (
                                <span className="text-red-400 ml-1">
                                  *
                                </span>
                              )}

                            </label>

                            <input
                              id={key}
                              name={key}
                              type={inputType}
                              value={
                                form[key] ||
                                ""
                              }
                              onChange={
                                handleChange
                              }
                              required={
                                required
                              }
                              className="w-full rounded-xl border border-[#263044] bg-[#080A11] px-4 py-3 text-white outline-none focus:border-[#0055FF] transition-colors"
                            />

                          </div>
                        );
                      }
                    )}

                  </div>

                )}

              </div>

              {/* SUBMIT */}

              {fields.length > 0 && (
                <div className="flex justify-end">

                  <button
                    type="submit"
                    disabled={
                      submitting
                    }
                    className="inline-flex items-center justify-center rounded-full bg-[#0055FF] hover:bg-[#0033CC] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold h-12 px-8 shadow-[0_0_24px_rgba(0,85,255,0.35)] transition-all"
                  >

                    {submitting
                      ? "Submitting..."
                      : "Submit Application"}

                  </button>

                </div>
              )}

            </form>
          )}

        </div>

      </section>

    </main>
  );
}