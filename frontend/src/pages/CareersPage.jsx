import { ArrowRight } from "lucide-react";
import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";

import logo from "@/assets/images/logo.png";

const API_URL =
  "https://aerolcoltsecuritysystems.ae/backend/api/jobs.php";

export default function CareersPage() {
  const navigate = useNavigate();

  const [jobs, setJobs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const loadJobs = async () => {
      try {
        setLoading(true);
        setError("");

        const response = await fetch(API_URL);
        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error("Unable to load jobs");
        }

        setJobs(Array.isArray(data.jobs) ? data.jobs : []);
      } catch (err) {
        console.error(err);
        setError("Unable to load current openings.");
      } finally {
        setLoading(false);
      }
    };

    loadJobs();
  }, []);

  return (
    <main className="min-h-screen bg-[#05050A] text-white">

      {/* HEADER */}
      <header className="border-b border-[#1E2235]">
        <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">

          <div className="flex items-center py-6">

            {/* LOGO + COMPANY NAME */}
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

        {/* LEFT-ALIGNED CONTENT */}
        <div className="relative w-full px-6 md:px-10 lg:px-16 xl:px-20">


          {/* BACK TO HOME */}
          <div className="pt-8">

            <button
              type="button"
              onClick={() => navigate("/")}
              className="inline-flex items-center gap-2 text-[#94A3B8] hover:text-white transition-colors font-medium"
            >

              <ArrowRight className="h-4 w-4 rotate-180" />

              <span>
                Back to Home
              </span>

            </button>

          </div>


          {/* PAGE HEADING */}
          <div className="pt-16 pb-12 md:pt-20 md:pb-14 max-w-5xl">

            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              Open Positions
            </div>

            <h1 className="font-display font-bold text-3xl md:text-4xl tracking-tight">
              Current opportunities
            </h1>

            <p className="mt-4 max-w-3xl text-[#94A3B8] text-lg leading-relaxed">
              Explore our current openings and find a role that matches your
              experience and skills.
            </p>

          </div>


          {/* LOADING */}
          {loading && (
            <div className="max-w-5xl py-8 border-t border-[#242936]">

              <p className="text-[#94A3B8]">
                Loading current openings...
              </p>

            </div>
          )}


          {/* ERROR */}
          {!loading && error && (
            <div className="max-w-5xl py-8 border-t border-red-500/30">

              <p className="text-red-300">
                {error}
              </p>

            </div>
          )}


          {/* NO JOBS */}
          {!loading && !error && jobs.length === 0 && (
            <div className="max-w-5xl py-8 border-t border-[#242936]">

              <p className="text-[#94A3B8]">
                There are currently no open positions.
              </p>

            </div>
          )}


          {/* JOB LIST */}
          {!loading && !error && jobs.length > 0 && (

            <div className="max-w-5xl border-t border-[#242936]">

              {jobs.map((job) => (

                <button
                  key={job.id}
                  type="button"
                  onClick={() =>
                    navigate(`/careers/job/${job.slug}`)
                  }
                  className="
                    group
                    relative
                    w-full
                    text-left
                    border-b
                    border-[#242936]
                    py-6
                    md:py-7
                    pr-12
                    transition-all
                    duration-200
                    hover:border-[#3B4558]
                  "
                >

                  {/* JOB TITLE */}
                  <h2
                    className="
                      font-sans
                      font-semibold
                      text-lg
                      md:text-xl
                      leading-snug
                      text-white
                      transition-colors
                      duration-200
                      group-hover:text-[#00E5FF]
                    "
                  >
                    {job.title}
                  </h2>


                  {/* JOB META */}
                  <div
                    className="
                      mt-2
                      flex
                      flex-wrap
                      items-center
                      gap-x-2
                      gap-y-1
                      text-sm
                      text-[#94A3B8]
                    "
                  >

                    {job.location && (
                      <span>
                        {job.location}
                      </span>
                    )}

                    {job.location && job.employment_type && (
                      <span className="text-[#4B5563]">
                        •
                      </span>
                    )}

                    {job.employment_type && (
                      <span>
                        {job.employment_type}
                      </span>
                    )}

                    {job.employment_type && job.experience && (
                      <span className="text-[#4B5563]">
                        •
                      </span>
                    )}

                    {job.experience && (
                      <span>
                        {job.experience}
                      </span>
                    )}

                  </div>


                  {/* HOVER ARROW */}
                  <ArrowRight
                    className="
                      absolute
                      right-2
                      top-1/2
                      h-5
                      w-5
                      -translate-y-1/2
                      text-[#4B5563]
                      opacity-0
                      -translate-x-2
                      transition-all
                      duration-200
                      group-hover:translate-x-0
                      group-hover:text-[#00E5FF]
                      group-hover:opacity-100
                    "
                  />

                </button>

              ))}

            </div>

          )}

        </div>

      </section>

    </main>
  );
}