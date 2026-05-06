import {
  BadgeCheck,
  Workflow,
  ShieldCheck,
  Cpu,
  Zap,
} from "lucide-react";
import { useReveal } from "../lib/useScrollFx";

const FEATURES = [
  {
    id: "sira-compliant",
    title: "SIRA-Approved & Fully Compliant",
    desc: "Systems designed to meet UAE regulations without delays or rework.",
    Icon: BadgeCheck,
  },
  {
    id: "end-to-end",
    title: "End-to-End Execution",
    desc: "From design to installation and support, handled by one team.",
    Icon: Workflow,
  },
  {
    id: "real-world",
    title: "Built for Real-World Conditions",
    desc: "Reliable performance in actual operating environments.",
    Icon: ShieldCheck,
  },
  {
    id: "integrated",
    title: "Integrated Smart Systems",
    desc: "Security, access, and automation in one unified system.",
    Icon: Cpu,
  },
  {
    id: "fast-response",
    title: "Fast Response & Support",
    desc: "Quick installation and ongoing maintenance support.",
    Icon: Zap,
  },
];

function FeatureCard({ f, i }) {
  const [ref, visible] = useReveal();
  return (
    <div
      ref={ref}
      data-testid={`feature-${f.id}`}
      style={{ transitionDelay: `${i * 60}ms` }}
      className={`reveal ${
        visible ? "is-visible" : ""
      } group relative overflow-hidden rounded-2xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#0A0C14] p-7 md:p-9 hover:border-[#0055FF]/45 transition-all duration-500`}
    >
      {/* corner glow */}
      <div className="pointer-events-none absolute -top-24 -right-24 h-48 w-48 rounded-full bg-[#0055FF]/15 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
      <div className="pointer-events-none absolute inset-x-0 bottom-0 h-[2px] w-0 bg-gradient-to-r from-[#0055FF] to-[#00E5FF] group-hover:w-full transition-all duration-500" />

      <div className="relative h-12 w-12 rounded-xl bg-[#0055FF]/10 border border-[#0055FF]/30 flex items-center justify-center mb-5 group-hover:border-[#00E5FF]/60 group-hover:bg-[#00E5FF]/10 transition-colors">
        <f.Icon className="h-5 w-5 text-[#00E5FF]" strokeWidth={2} />
      </div>
      <h3 className="font-display font-bold text-lg md:text-xl text-white tracking-tight leading-snug">
        {f.title}
      </h3>
      <p className="mt-3 text-[14.5px] text-[#94A3B8] leading-relaxed">
        {f.desc}
      </p>
    </div>
  );
}

export default function WhyChooseUs() {
  return (
    <section
      id="about"
      data-testid="why-choose-us-section"
      className="relative py-24 md:py-32"
    >
      <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">
        <div className="max-w-4xl mb-14 md:mb-16">
          <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
            — Why Aerol Colt
          </div>
          <h2
            data-testid="why-heading"
            className="font-display font-bold text-3xl sm:text-4xl lg:text-5xl tracking-tighter text-white leading-[1.1]"
          >
            Compliance, reliability, and one team accountable end-to-end.
          </h2>
          <p className="mt-5 text-[#94A3B8] text-base leading-relaxed max-w-2xl">
            We engineer and deploy security and smart systems that pass inspection
            the first time — with the resilience commercial and residential
            properties demand across the UAE.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {FEATURES.map((f, i) => (
            <FeatureCard key={f.id} f={f} i={i} />
          ))}
        </div>
      </div>
    </section>
  );
}
