import { IMAGES } from "../lib/site";
import { useParallax, useReveal } from "../lib/useScrollFx";

import lemeridien from "../assets/images/lemeridien.png";
import marriott from "../assets/images/marriott.png";
import whotels from "../assets/images/w-hotels.png";
import grosvenor from "../assets/images/grosvenor.png";
import westin from "../assets/images/westin.png";
import aloft from "../assets/images/aloft.png";

const isMobile =
  typeof window !== "undefined" &&
  (window.innerWidth < 768 ||
    "ontouchstart" in window ||
    navigator.maxTouchPoints > 0);

const STATS = [
  { k: "6,000+", v: "Systems Installed" },
  { k: "100%", v: "SIRA-Compliant" },
  { k: "24/7", v: "Support" },
  { k: "80+", v: "Clients Served" },
];

const LOGOS = [
  {
    name: "Le Méridien",
    src: lemeridien,
  },
  {
    name: "Marriott International",
    src: marriott,
  },
  {
    name: "W Hotels",
    src: whotels,
  },
  {
    name: "Grosvenor House Dubai",
    src: grosvenor,
  },
  {
    name: "Westin Hotels & Resorts",
    src: westin,
  },
  {
    name: "Aloft Hotels",
    src: aloft,
  },
];

const PROJECTS = [
  {
    id: "marina-tower",
    name: "Marina Residences",
    system: "CCTV + Access Control",
    outcome:
      "Fully integrated 240-camera deployment with centralized monitoring and SIRA sign-off on first inspection.",
    image: IMAGES.project1,
  },
  {
    id: "logistics-park",
    name: "Jebel Ali Logistics Park",
    system: "ANPR + Perimeter Security",
    outcome:
      "Automated gate control and 24/7 ANPR logs reduced entry times by 68% across 6 checkpoints.",
    image: IMAGES.project2,
  },
  {
    id: "corporate-hq",
    name: "Business Bay Corporate HQ",
    system: "Smart Building + Integration",
    outcome:
      "Unified security, access, and automation cut operating overhead and delivered a single operator dashboard.",
    image: IMAGES.project3,
  },
];

function ProjectCard({ p }) {
  const [imgRef, imgOffset] = useParallax(isMobile ? 0 : 0.03);
  const [ref, visible] = useReveal();

  return (
    <article
      ref={ref}
      data-testid={`project-${p.id}`}
      className={`reveal ${
        visible ? "is-visible" : ""
      } group relative overflow-hidden rounded-2xl border border-[#1E2235] bg-[#0F111A] hover:border-[#0055FF]/50 transition-all`}
    >
      <div className="relative h-56 overflow-hidden">
        <img
          ref={imgRef}
          src={p.image}
          alt={p.name}
          loading="lazy"
          decoding="async"
          style={{
            transform: isMobile
              ? "scale(1.02)"
              : `translate3d(0, ${imgOffset}px, 0) scale(1.1)`,
          }}
          className={`absolute inset-0 w-full object-cover opacity-70 group-hover:opacity-90 transition-opacity duration-700 ${
            isMobile
              ? "h-full"
              : "h-[115%] will-change-transform"
          }`}
        />

        <div className="absolute inset-0 bg-gradient-to-t from-[#0F111A] via-[#0F111A]/20 to-transparent" />
      </div>

      <div className="p-7">
        <div className="text-[10px] uppercase tracking-[0.22em] text-[#00E5FF] font-medium">
          {p.system}
        </div>

        <h4 className="mt-3 font-display font-bold text-xl text-white tracking-tight">
          {p.name}
        </h4>

        <p className="mt-3 text-[14px] text-[#94A3B8] leading-relaxed">
          {p.outcome}
        </p>
      </div>
    </article>
  );
}

export default function Proof() {
  return (
    <section
      data-testid="proof-section"
      className="relative py-24 md:py-32 bg-[#07080F]"
    >
      <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">
        
        {/* STATS */}
        <div
          data-testid="stats-bar"
          className="grid grid-cols-2 md:grid-cols-4 border border-[#1E2235] rounded-2xl overflow-hidden divide-x divide-[#1E2235]"
        >
          {STATS.map((s) => (
            <div
              key={s.v}
              className="p-6 md:p-10 bg-gradient-to-br from-[#0F111A] to-[#0A0C14]"
            >
              <div className="font-display font-black text-3xl md:text-5xl tracking-tighter text-white">
                {s.k}
              </div>

              <div className="mt-2 text-[10px] md:text-[11px] uppercase tracking-[0.22em] text-[#94A3B8]">
                {s.v}
              </div>
            </div>
          ))}
        </div>

        {/* LOGOS */}
        <div className="mt-20 overflow-hidden">
          <div className="text-center text-[11px] uppercase tracking-[0.28em] text-[#94A3B8] mb-10">
            — Trusted by leading hotels, developers & operators
          </div>

          <div className="relative overflow-hidden border-y border-[#1E2235] py-6">
            <div className="flex w-max animate-marquee gap-5 md:gap-8">
              {[...LOGOS, ...LOGOS].map((logo, i) => (
                <div
                  key={i}
                  data-testid={`client-logo-${i}`}
                  className="shrink-0 flex items-center justify-center rounded-xl bg-white/[0.04] border border-white/10 px-5 md:px-6 py-3 h-20 md:h-24 min-w-[170px] md:min-w-[220px]"
                >
                  <img
                    src={logo.src}
                    alt={logo.name}
                    loading="lazy"
                    decoding="async"
                    className="h-10 md:h-14 w-auto max-w-full object-contain"
                    style={{
                      mixBlendMode: "screen",
                      filter: isMobile
                        ? "brightness(1.45)"
                        : "brightness(1.8) contrast(1.15)",
                    }}
                  />
                </div>
              ))}
            </div>

            {!isMobile && (
              <>
                <div className="pointer-events-none absolute inset-y-0 left-0 w-24 bg-gradient-to-r from-[#07080F] to-transparent" />

                <div className="pointer-events-none absolute inset-y-0 right-0 w-24 bg-gradient-to-l from-[#07080F] to-transparent" />
              </>
            )}
          </div>
        </div>

        {/* PROJECTS */}
        <div className="mt-24">
          <div className="flex items-end justify-between mb-10">
            <h3
              data-testid="project-highlights-heading"
              className="font-display font-bold text-3xl md:text-4xl tracking-tighter text-white"
            >
              Project highlights
            </h3>

            {!isMobile && (
              <div className="text-sm text-[#94A3B8]">
                Recent deployments across Dubai & the UAE
              </div>
            )}
          </div>

          <div className="grid md:grid-cols-3 gap-5">
            {PROJECTS.map((p) => (
              <ProjectCard key={p.id} p={p} />
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}