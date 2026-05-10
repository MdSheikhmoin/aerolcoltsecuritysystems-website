import { ArrowRight, Briefcase, Users, Rocket } from "lucide-react";
import { Button } from "./ui/button";
import { SITE } from "../lib/site";
import { useReveal } from "../lib/useScrollFx";

const PERKS = [
  {
    id: "impact",
    title: "Real impact, real projects",
    desc: "Work on SIRA-grade deployments across Dubai and the UAE — not slides.",
    Icon: Rocket,
  },
  {
    id: "team",
    title: "One accountable team",
    desc: "Design, installation, and support — collaborate end-to-end, not in silos.",
    Icon: Users,
  },
  {
    id: "growth",
    title: "Certifications & growth",
    desc: "Manufacturer training, SIRA alignment, and a clear path to senior engineering roles.",
    Icon: Briefcase,
  },
];

function PerkCard({ p, i }) {
  const [ref, visible] = useReveal();

  return (
    <div
      ref={ref}
      data-testid={`career-perk-${p.id}`}
      style={{ transitionDelay: `${i * 80}ms` }}
      className={`reveal ${
        visible ? "is-visible" : ""
      } group relative overflow-hidden rounded-2xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#0A0C14] p-7 md:p-9 hover:border-[#0055FF]/45 transition-all duration-500`}
    >
      <div className="h-12 w-12 rounded-xl bg-[#0055FF]/10 border border-[#0055FF]/30 flex items-center justify-center mb-5 group-hover:border-[#00E5FF]/60 group-hover:bg-[#00E5FF]/10 transition-colors">
        <p.Icon className="h-5 w-5 text-[#00E5FF]" strokeWidth={2} />
      </div>

      <h3 className="font-display font-bold text-lg md:text-xl text-white tracking-tight">
        {p.title}
      </h3>

      <p className="mt-3 text-[14.5px] text-[#94A3B8] leading-relaxed">
        {p.desc}
      </p>
    </div>
  );
}

export default function Careers() {
  const [headRef, headVisible] = useReveal();

  const handleApply = () => {
    const subject = encodeURIComponent("Careers — Application");

    window.location.href = `mailto:${SITE.email}?subject=${subject}`;
  };

  return (
    <section
      id="careers"
      data-testid="careers-section"
      className="relative py-24 md:py-32 overflow-hidden"
    >
      <div className="absolute inset-0 bg-grid opacity-[0.2] pointer-events-none" />

      <div className="absolute -top-24 left-1/3 h-[360px] w-[720px] rounded-full bg-[#0055FF]/15 blur-[140px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-6 md:px-10 lg:px-12">
        <div
          ref={headRef}
          className={`reveal ${
            headVisible ? "is-visible" : ""
          } grid lg:grid-cols-12 gap-10 items-end mb-14`}
        >
          <div className="lg:col-span-7">
            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              — Careers
            </div>

            <h2
              data-testid="careers-heading"
              className="font-display font-bold text-3xl sm:text-4xl lg:text-5xl tracking-tighter text-white leading-[1.08]"
            >
              Build systems that protect what matters.
            </h2>
          </div>

          <div className="lg:col-span-5">
            <p className="text-[#94A3B8] leading-relaxed">
              We&apos;re hiring engineers, installers, project managers, and
              support technicians across Dubai. If you care about doing the job
              right the first time, we&apos;d like to hear from you.
            </p>
          </div>
        </div>

        <div className="grid md:grid-cols-3 gap-5">
          {PERKS.map((p, i) => (
            <PerkCard key={p.id} p={p} i={i} />
          ))}
        </div>

        <div className="mt-12 md:mt-14 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 rounded-2xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#0A0C14] p-7 md:p-9">
          <div>
            <h3 className="font-display font-bold text-xl md:text-2xl text-white tracking-tight">
              Interested? Send us your CV.
            </h3>

            <p className="mt-2 text-[#94A3B8] text-[15px]">
              Email us at{" "}
              <span className="text-white">{SITE.email}</span> with the role
              you&apos;re applying for.
            </p>
          </div>

          <Button
            onClick={handleApply}
            data-testid="careers-apply-button"
            className="group bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold rounded-full h-12 px-7 text-base shadow-[0_0_24px_rgba(0,85,255,0.45)] hover:shadow-[0_0_36px_rgba(0,85,255,0.65)] transition-all"
          >
            Apply now

            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Button>
        </div>
      </div>
    </section>
  );
}