import { useReveal } from "../lib/useScrollFx";

const SERVICES = [
  {
    id: "installation",
    no: "01",
    title: "Installation & Deployment",
    desc: "Fast, clean rollouts with minimum disruption — cabled, tested, and handed over.",
  },
  {
    id: "commissioning",
    no: "02",
    title: "Testing & Commissioning",
    desc: "Every device verified and signed off so inspections pass the first time.",
  },
  {
    id: "maintenance",
    no: "03",
    title: "Maintenance & Support",
    desc: "Preventive maintenance and responsive support to keep systems always-on.",
  },
  {
    id: "design",
    no: "04",
    title: "Design & Engineering",
    desc: "SIRA-aligned drawings, BoQs, and system architecture before a single cable is pulled.",
  },
  {
    id: "integration",
    no: "05",
    title: "System Integration",
    desc: "Unify CCTV, access, ANPR, and automation into one secure operator experience.",
  },
];

function ServiceRow({ s, onRequest }) {
  const [ref, visible] = useReveal();
  return (
    <li
      ref={ref}
      data-testid={`service-${s.id}`}
      role="button"
      tabIndex={0}
      onClick={onRequest}
      onKeyDown={(e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          onRequest?.();
        }
      }}
      className={`reveal ${visible ? "is-visible" : ""} group relative py-7 md:py-8 flex items-start gap-6 md:gap-10 hover:bg-[#0F111A]/60 transition-colors px-2 md:px-4 cursor-pointer focus:outline-none focus:bg-[#0F111A]/80`}
    >
      <div className="font-mono text-[12px] tracking-widest text-[#94A3B8] pt-1 w-10 shrink-0">
        {s.no}
      </div>
      <div className="flex-1">
        <h3 className="font-display font-semibold text-xl md:text-2xl tracking-tight text-white group-hover:text-[#00E5FF] transition-colors">
          {s.title}
        </h3>
        <p className="mt-2 text-[#94A3B8] text-[15px] leading-relaxed max-w-2xl">
          {s.desc}
        </p>
      </div>
    </li>
  );
}

export default function Services({ onRequest }) {
  return (
    <section
      id="services"
      data-testid="services-section"
      className="relative py-24 md:py-32"
    >
      <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">
        <div className="grid lg:grid-cols-12 gap-12">
          <div className="lg:col-span-4">
            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              — Services
            </div>
            <h2
              data-testid="services-heading"
              className="font-display font-bold text-4xl sm:text-5xl tracking-tighter text-white"
            >
              Engineered to deliver. Supported to last.
            </h2>
            <p className="mt-6 text-[#94A3B8] leading-relaxed">
              A single accountable team across the full lifecycle — design,
              deployment, and long-term support.
            </p>
          </div>

          <ul className="lg:col-span-8 divide-y divide-[#1E2235] border-y border-[#1E2235]">
            {SERVICES.map((s) => (
              <ServiceRow key={s.id} s={s} onRequest={onRequest} />
            ))}
          </ul>
        </div>
      </div>
    </section>
  );
}
