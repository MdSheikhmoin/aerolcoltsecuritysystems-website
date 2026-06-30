import { ArrowUpRight } from "lucide-react";
import { IMAGES } from "../lib/site";
import { useParallax, useReveal } from "../lib/useScrollFx";

const isMobile =
  typeof window !== "undefined" &&
  (window.innerWidth < 768 ||
    "ontouchstart" in window ||
    navigator.maxTouchPoints > 0);

const PRODUCTS = [
  {
    id: "smart-security",
    name: "Smart Security Systems",
    tag: "SIRA Approved",
    desc: "Protect what matters with intelligent CCTV, alarm, and monitoring systems built for uptime and incident-grade evidence.",
    image: IMAGES.smartSecurity,
    span: "md:col-span-8 md:row-span-2",
    height: "min-h-[420px] md:min-h-[560px]",
  },
  {
    id: "anpr-access",
    name: "ANPR & Access Systems",
    tag: "SIRA Approved",
    desc: "Frictionless entry control with number-plate recognition, biometrics, and audited access logs.",
    image: IMAGES.access,
    span: "md:col-span-4",
    height: "min-h-[260px]",
  },
  {
    id: "network",
    name: "Network & IT Infrastructure",
    tag: "Infrastructure",
    desc: "Backbone networks engineered for low-latency, high-availability security and building systems.",
    image: IMAGES.network,
    span: "md:col-span-4",
    height: "min-h-[260px]",
  },
  {
    id: "automation",
    name: "Automation & Smart Building Systems",
    tag: "Smart Buildings",
    desc: "Lighting, HVAC, and access unified in one pane — reduce energy cost and improve occupant experience.",
    image: IMAGES.automation,
    span: "md:col-span-12",
    height: "min-h-[300px]",
  },
];

function ProductCard({ p, onRequest }) {
  const [imgRef, imgOffset] = useParallax(isMobile ? 0 : 0.18);
  const [revealRef, visible] = useReveal();

  return (
    <article
      ref={revealRef}
      data-testid={`product-${p.id}`}
      role="button"
      tabIndex={0}
      onClick={onRequest}
      onKeyDown={(e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          onRequest?.();
        }
      }}
      className={`reveal ${
        visible ? "is-visible" : ""
      } group relative overflow-hidden rounded-2xl border border-[#1E2235] bg-[#0F111A] ${p.span} ${p.height} hover:border-[#0055FF]/50 transition-all duration-500 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#0055FF] focus:ring-offset-2 focus:ring-offset-[#07080F]`}
    >
      <img
        ref={imgRef}
        src={p.image}
        alt={p.name}
        loading="lazy"
        decoding="async"
        style={{
          transform: isMobile
            ? "scale(1.02)"
            : `translate3d(0, ${imgOffset}px, 0) scale(1.08)`,
        }}
        className={`absolute inset-0 w-full object-cover opacity-60 group-hover:opacity-75 transition-opacity duration-700 ${
          isMobile
            ? "h-full"
            : "h-[115%] will-change-transform"
        }`}
      />

      <div className="absolute inset-0 bg-gradient-to-t from-[#05050A] via-[#05050A]/70 to-[#05050A]/10" />

      <div className="relative h-full flex flex-col justify-end p-7 md:p-9">
        <div className="inline-flex self-start items-center gap-1.5 rounded-full border border-[#00E5FF]/30 bg-[#00E5FF]/10 px-2.5 py-1 text-[10px] uppercase tracking-[0.18em] text-[#00E5FF] font-medium">
          {p.tag}
        </div>

        <h3 className="mt-4 font-display font-bold text-2xl md:text-3xl tracking-tight text-white max-w-xl">
          {p.name}
        </h3>

        <p className="mt-3 text-[#cbd5e1] text-[15px] max-w-xl leading-relaxed">
          {p.desc}
        </p>
      </div>
    </article>
  );
}

export default function Products({ onRequest }) {
  return (
    <section
      id="products"
      data-testid="products-section"
      className="relative py-24 md:py-32 bg-[#07080F]"
    >
      <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">
        <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12">
          <div>
            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              — Products
            </div>

            <h2
              data-testid="products-heading"
              className="font-display font-bold text-4xl sm:text-5xl tracking-tighter text-white max-w-3xl"
            >
              One platform. Every system your property needs.
            </h2>
          </div>

          {!isMobile && (
            <button
              data-testid="products-cta"
              onClick={onRequest}
              className="hidden md:inline-flex items-center gap-2 text-sm text-white/80 hover:text-white group"
            >
              Talk to a specialist

              <ArrowUpRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
            </button>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-12 gap-5">
          {PRODUCTS.map((p) => (
            <ProductCard
              key={p.id}
              p={p}
              onRequest={onRequest}
            />
          ))}
        </div>
      </div>
    </section>
  );
}