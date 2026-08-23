import {
  ArrowLeft,
  ArrowRight,
  MapPin,
  Clock,
  Briefcase,
} from "lucide-react";
import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

import logo from "@/assets/images/logo.png";

const API_URL =
  "https://aerolcoltsecuritysystems.ae/backend/api/jobs.php";

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function splitBullets(text) {
  if (!text) {
    return [];
  }

  return String(text)
    .split(/\n+/)
    .map((item) =>
      item
        .replace(/^[•●▪◦]\s*/, "")
        .replace(/^\u2022\s*/, "")
        .trim()
    )
    .filter(Boolean);
}


function renderParagraphs(text) {
  if (!text) {
    return null;
  }

  return String(text)
    .split(/\n\s*\n/)
    .map((paragraph, index) => (
      <p
        key={index}
        className="text-[#94A3B8] leading-relaxed whitespace-pre-line"
      >
        {paragraph.trim()}
      </p>
    ));
}


/*
|--------------------------------------------------------------------------
| SECTION COMPONENT
|--------------------------------------------------------------------------
*/

function Section({ title, children }) {
  if (!title || !children) {
    return null;
  }

  return (
    <section className="rounded-2xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#080A11] p-7 md:p-9">

      <h2 className="font-display font-bold text-xl md:text-2xl text-white">
        {title}
      </h2>

      <div className="mt-6">
        {children}
      </div>

    </section>
  );
}


/*
|--------------------------------------------------------------------------
| CUSTOM JOB BASICS
|--------------------------------------------------------------------------
|
| These are the additional fields created under
| Job Basics in the Admin portal.
|
| They appear publicly only when:
|
| show_on_careers !== false
|
|--------------------------------------------------------------------------
*/

function getCustomBasicFields(job) {
  const config = job?.section_config;

  if (!Array.isArray(config)) {
    return [];
  }

  return config.filter(
    (section) =>
      section &&
      section.type === "basic" &&
      section.show_on_careers !== false &&
      String(section.title || "").trim() !== "" &&
      String(section.content || "").trim() !== ""
  );
}


/*
|--------------------------------------------------------------------------
| JOB INFORMATION
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Job Description comes from:
|
| job.description
|
| Additional Job Information sections come from:
|
| job.section_config
|
| Job Basics sections are excluded here.
|
|--------------------------------------------------------------------------
*/

function getJobInformationSections(job) {
  const sections = [];

  /*
  |--------------------------------------------------------------------------
  | JOB DESCRIPTION
  |--------------------------------------------------------------------------
  |
  | The API returns Job Description twice:
  |
  | 1. jobs.description
  | 2. section_config item with key "description"
  |
  | We render it ONCE from section_config when available, because
  | section_config is the source of truth for the Job Information order.
  |
  */

  const config = Array.isArray(job?.section_config)
    ? job.section_config
    : [];

  let descriptionAdded = false;

  /*
  |--------------------------------------------------------------------------
  | Render every saved Job Information content section in API order.
  |--------------------------------------------------------------------------
  |
  | The actual API uses:
  |
  | type: "content"
  |
  | Job Basics uses:
  |
  | type: "basic_meta"
  |
  | Therefore ONLY "basic" / "basic_meta" are excluded here.
  | Every content section saved by Admin is rendered.
  |
  */

  config.forEach((section, index) => {
    if (!section || typeof section !== "object") {
      return;
    }

    const type = String(section.type || "").trim().toLowerCase();

    /*
    |--------------------------------------------------------------------------
    | Job Basics fields must NOT appear as Job Information sections.
    |--------------------------------------------------------------------------
    */

    if (type === "basic" || type === "basic_meta") {
      return;
    }

    const title = String(section.title || "").trim();
    const content = String(section.content || "").trim();

    if (!title || !content) {
      return;
    }

    /*
    |--------------------------------------------------------------------------
    | Description from section_config.
    |--------------------------------------------------------------------------
    */

    if (
      section.key === "description" ||
      section.field === "description" ||
      type === "description"
    ) {
      if (descriptionAdded) {
        return;
      }

      descriptionAdded = true;

      sections.push({
        ...section,
        key: "job-description",
        title: "Job Description",
        content,
        type: "description",
      });

      return;
    }

    /*
    |--------------------------------------------------------------------------
    | All other saved Job Information sections.
    |--------------------------------------------------------------------------
    */

    sections.push({
      ...section,
      key:
        section.key ||
        `job-information-${index}`,
      title,
      content,
    });
  });

  /*
  |--------------------------------------------------------------------------
  | Backward compatibility:
  |
  | If an older job has a description in jobs.description but does NOT have
  | a description section inside section_config, still show it.
  |--------------------------------------------------------------------------
  */

  if (!descriptionAdded) {
    const description = String(job?.description || "").trim();

    if (description) {
      sections.unshift({
        key: "job-description",
        title: "Job Description",
        content: description,
        type: "description",
      });
    }
  }

  return sections;
}


/*
|--------------------------------------------------------------------------
| SECTION CONTENT RENDERER
|--------------------------------------------------------------------------
*/

function renderSectionContent(section) {

  const content = String(
    section?.content || ""
  ).trim();

  if (!content) {
    return null;
  }


  /*
  |--------------------------------------------------------------------------
  | Detect bullet content.
  |--------------------------------------------------------------------------
  */

  const hasBulletMarkers =
    content
      .split(/\n+/)
      .some((line) =>
        /^[•●▪◦]\s*/.test(
          line.trim()
        )
      );


  /*
  |--------------------------------------------------------------------------
  | BULLET LIST
  |--------------------------------------------------------------------------
  */

  if (hasBulletMarkers) {

    const lines =
      splitBullets(content);

    return (
      <ul className="space-y-4">

        {lines.map(
          (item, index) => (

            <li
              key={index}
              className="flex items-start gap-3 text-[#94A3B8] leading-relaxed"
            >

              <span className="mt-2 h-1.5 w-1.5 rounded-full bg-[#00E5FF] shrink-0" />

              <span>
                {item}
              </span>

            </li>

          )
        )}

      </ul>
    );
  }


  /*
  |--------------------------------------------------------------------------
  | NORMAL PARAGRAPHS
  |--------------------------------------------------------------------------
  */

  return (
    <div className="space-y-5">
      {renderParagraphs(content)}
    </div>
  );
}


/*
|--------------------------------------------------------------------------
| COMPONENT
|--------------------------------------------------------------------------
*/

export default function JobDetailsPage() {

  const navigate =
    useNavigate();

  const { slug } =
    useParams();


  const [job, setJob] =
    useState(null);

  const [loading, setLoading] =
    useState(true);

  const [error, setError] =
    useState("");


  /*
  |--------------------------------------------------------------------------
  | LOAD JOB
  |--------------------------------------------------------------------------
  */

  useEffect(() => {

    const loadJob =
      async () => {

        try {

          setLoading(true);

          setError("");


          const response =
            await fetch(
              API_URL,
              {
                method: "GET",

                headers: {
                  Accept:
                    "application/json",
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
            Array.isArray(
              data.jobs
            )
              ? data.jobs
              : [];


          const foundJob =
            jobs.find(
              (item) =>
                String(
                  item.slug
                ) ===
                String(slug)
            );


          if (!foundJob) {

            throw new Error(
              "Job not found."
            );

          }


          setJob(
            foundJob
          );

        } catch (err) {

          console.error(
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
  | LOADING
  |--------------------------------------------------------------------------
  */

  if (loading) {

    return (

      <main className="min-h-screen bg-[#05050A] text-white">

        <div className="mx-auto max-w-5xl px-6 py-20">

          <p className="text-[#94A3B8]">
            Loading position...
          </p>

        </div>

      </main>

    );
  }


  /*
  |--------------------------------------------------------------------------
  | ERROR / NOT FOUND
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
            className="mt-6 inline-flex items-center gap-2 text-[#94A3B8] hover:text-white transition-colors"
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
  | DYNAMIC DATA
  |--------------------------------------------------------------------------
  */

  const customBasicFields =
    getCustomBasicFields(
      job
    );


  const jobInformationSections =
    getJobInformationSections(
      job
    );


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


      {/* PAGE CONTENT */}

      <section className="relative">

        <div className="absolute inset-0 bg-grid opacity-[0.15] pointer-events-none" />


        <div className="relative mx-auto max-w-5xl px-6 md:px-10 py-10 md:py-16">

          {/* BACK */}

          <button
            type="button"
            onClick={() =>
              navigate(
                "/careers"
              )
            }
            className="inline-flex items-center gap-2 text-[#94A3B8] hover:text-white transition-colors font-medium"
          >

            <ArrowLeft className="h-4 w-4" />

            Back to Careers

          </button>


          {/* HERO */}

          <div className="mt-10">

            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              Open Position
            </div>


            <h1 className="font-display font-bold text-3xl md:text-5xl tracking-tight text-white">
              {job.title}
            </h1>


            {/* CORE JOB META */}

            <div className="flex flex-wrap items-center gap-x-7 gap-y-4 mt-7 text-[#94A3B8]">

              {/* LOCATION */}

              {job.location && (

                <div className="flex items-center gap-2">

                  <MapPin className="h-4 w-4 text-[#00E5FF]" />

                  <span>
                    {job.location}
                  </span>

                </div>

              )}


              {/* EMPLOYMENT TYPE */}

              {job.employment_type && (

                <div className="flex items-center gap-2">

                  <Clock className="h-4 w-4 text-[#00E5FF]" />

                  <span>
                    {job.employment_type}
                  </span>

                </div>

              )}


              {/* EXPERIENCE */}

              {job.experience && (

                <div className="flex items-center gap-2">

                  <Briefcase className="h-4 w-4 text-[#00E5FF]" />

                  <span>
                    {job.experience}
                  </span>

                </div>

              )}

            </div>


            {/* CUSTOM JOB BASICS */}

            {customBasicFields.length > 0 && (

              <div className="mt-7 flex flex-wrap gap-3">

                {customBasicFields.map(
                  (field, index) => {

                    const title =
                      String(
                        field.title ||
                          ""
                      ).trim();


                    const value =
                      String(
                        field.content ||
                          ""
                      ).trim();


                    if (
                      !title ||
                      !value
                    ) {
                      return null;
                    }


                    return (

                      <div
                        key={
                          field.key ||
                          `custom-basic-${index}`
                        }
                        className="rounded-xl border border-[#1E2235] bg-[#0F111A] px-4 py-3"
                      >

                        <div className="text-[11px] uppercase tracking-[0.16em] text-[#64748B]">
                          {title}
                        </div>


                        <div className="mt-1 text-sm font-medium text-white">
                          {value}
                        </div>

                      </div>

                    );

                  }
                )}

              </div>

            )}


            {/* APPLY BUTTON */}

            <div className="mt-9">

              <button
                type="button"
                onClick={() =>
                  navigate(
                    `/careers/job/${job.slug}/apply`
                  )
                }
                className="inline-flex items-center justify-center rounded-full bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold h-12 px-8 shadow-[0_0_24px_rgba(0,85,255,0.35)] hover:shadow-[0_0_32px_rgba(0,85,255,0.55)] transition-all"
              >

                Apply Now

                <ArrowRight className="ml-2 h-4 w-4" />

              </button>

            </div>

          </div>


          {/* JOB INFORMATION */}

          <div className="mt-12 space-y-6">

            {jobInformationSections.length >
            0 ? (

              jobInformationSections.map(
                (section, index) => (

                  <Section
                    key={
                      section.key ||
                      `job-information-${index}`
                    }
                    title={
                      section.title
                    }
                  >

                    {renderSectionContent(
                      section
                    )}

                  </Section>

                )
              )

            ) : (

              /*
              |--------------------------------------------------------------------------
              | If there is no Job Description and
              | no custom Job Information sections.
              |--------------------------------------------------------------------------
              */

              <div className="rounded-2xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#080A11] p-7 md:p-9">

                <p className="text-[#94A3B8] leading-relaxed">
                  No additional information is available for this position.
                </p>

              </div>

            )}


            {/* FINAL CTA */}

            <div className="rounded-2xl border border-[#0055FF]/30 bg-gradient-to-br from-[#0F1628] to-[#080A11] p-7 md:p-9">

              <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                <div>

                  <h2 className="font-display font-bold text-xl md:text-2xl text-white">
                    Ready to apply?
                  </h2>


                  <p className="mt-2 text-[#94A3B8]">
                    Submit your application and CV for consideration.
                  </p>

                </div>


                <button
                  type="button"
                  onClick={() =>
                    navigate(
                      `/careers/job/${job.slug}/apply`
                    )
                  }
                  className="shrink-0 inline-flex items-center justify-center rounded-full bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold h-12 px-8 transition-all"
                >

                  Apply Now

                  <ArrowRight className="ml-2 h-4 w-4" />

                </button>

              </div>

            </div>

          </div>

        </div>

      </section>

    </main>

  );
}