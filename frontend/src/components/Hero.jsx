import {
  ArrowRight,
  ShieldCheck,
  CircleCheck,
} from "lucide-react";

import { Button } from "./ui/button";
import { IMAGES } from "../lib/site";
import { useParallax } from "../lib/useScrollFx";

const isMobile =
  typeof window !== "undefined" &&
  (window.innerWidth < 768 ||
    "ontouchstart" in window ||
    navigator.maxTouchPoints > 0);

export default function Hero({ onPrimary, onSecondary }) {
  const [heroImgRef, heroOffset] = useParallax(isMobile ? 0 : 0.25);

  return (
    <section
      id="home"
      data-testid="hero-section"
      className="relative pt-32 md:pt-40 pb-20 md:pb-28 overflow-hidden"
    >
      {/* Background */}
      {!isMobile && (
        <>
          <div className="absolute inset-0 bg-grid opacity-[0.35] pointer-events-none" />

          <div className="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full bg-[#0055FF]/20 blur-[140px] pointer-events-none" />

          <div className="absolute top-1/3 -right-20 h-[420px] w-[420px] rounded-full bg-[#00E5FF]/10 blur-[140px] pointer-events-none" />
        </>
      )}

      <div className="relative mx-auto max-w-7xl px-6 md:px-10 lg:px-12 grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">
        
        {/* LEFT SIDE */}
        <div className="lg:col-span-7 fade-up">
          
          {/* UAE Badge */}
          <div className="inline-flex items-center gap-2 rounded-full border border-[#1E2235] bg-[#0F111A]/70 px-4 py-2 text-xs font-medium text-[#cbd5e1]">
            <span className="text-sm">🇦🇪</span>
            PROUDLY UAE • EST. IN DUBAI
          </div>

          {/* Heading */}
          <h1
            data-testid="hero-heading"
            className="mt-8 font-display font-black text-5xl sm:text-6xl lg:text-7xl leading-[0.98] tracking-tighter text-white"
          >
            Together We Stand.
            <br />

            <span className="bg-gradient-to-r from-[#FF4D57] via-white to-[#3ECF6D] bg-clip-text text-transparent">
              With the UAE.
            </span>
          </h1>

          {/* Points */}
          <div className="mt-8 flex flex-wrap gap-x-10 gap-y-4 text-white">
            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-[#FF4D57]" />
              SIRA-Aligned Operations
            </div>

            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-[#3ECF6D]" />
              UAE Vision 2031 Aligned
            </div>

            <div className="flex items-center gap-2">
              <span className="h-2 w-2 rounded-full bg-white" />
              Local Team • Local Support
            </div>
          </div>

          {/* Request Box */}
          <div className="mt-10 rounded-[34px] border border-white/10 bg-white p-3 shadow-[0_0_40px_rgba(0,85,255,0.18)]">
            <div className="flex flex-col sm:flex-row items-center gap-3">
              
              <div className="flex-1 w-full px-4">
                <div className="text-[11px] uppercase tracking-[0.22em] text-[#64748B]">
                  Get Started
                </div>

                <div className="text-black text-lg font-semibold mt-1">
                  Request for site assistance
                </div>
              </div>

              <Button
                onClick={onPrimary}
                className="w-full sm:w-auto rounded-full bg-[#0055FF] hover:bg-[#0033CC] text-white px-8 py-6 text-base font-semibold"
              >
                Request
                <ArrowRight className="ml-2 h-4 w-4" />
              </Button>
            </div>
          </div>

          {/* Old Buttons */}
          <div className="mt-10 flex flex-wrap items-center gap-4">
            <Button
              data-testid="hero-cta-primary"
              onClick={onPrimary}
              className="group bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold rounded-full h-13 px-7 py-6 text-base shadow-[0_0_30px_rgba(0,85,255,0.5)] hover:shadow-[0_0_44px_rgba(0,85,255,0.7)] transition-all"
            >
              Request a Free Site Assessment

              <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
            </Button>

            <Button
              data-testid="hero-cta-secondary"
              onClick={onSecondary}
              variant="outline"
              className="rounded-full h-13 px-7 py-6 border border-white/15 bg-white/5 hover:bg-white/10 text-white font-medium text-base"
            >
              Get a Custom Quote
            </Button>
          </div>

          {/* Subtext */}
          <p className="mt-5 text-sm text-[#94A3B8] flex items-center gap-2">
            <CircleCheck className="h-4 w-4 text-[#00E5FF]" />
            On-site evaluation with system recommendations and cost estimate.
          </p>

          {/* Stats */}
          <div className="mt-10 grid grid-cols-3 max-w-lg divide-x divide-[#1E2235] border-y border-[#1E2235]">
            {[
              { k: "6,000+", v: "Systems Installed" },
              { k: "100%", v: "SIRA-Compliant" },
              { k: "24/7", v: "Support" },
            ].map((s) => (
              <div key={s.v} className="py-4 pl-4 pr-2 first:pl-0">
                <div className="font-display font-black text-2xl text-white tracking-tight">
                  {s.k}
                </div>

                <div className="text-[11px] uppercase tracking-[0.18em] text-[#94A3B8] mt-1">
                  {s.v}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* RIGHT IMAGE */}
        <div
          className="lg:col-span-5 relative fade-up"
          style={{ animationDelay: "120ms" }}
        >
          <div className="relative rounded-[34px] overflow-hidden border border-[#1E2235] glow-ring">
            
            <img
              ref={heroImgRef}
              src={IMAGES.hero}
              alt="UAE Security Systems"
              decoding="async"
              fetchpriority="high"
              loading="eager"
              style={{
                transform: isMobile
                  ? "scale(1.01)"
                  : `translate3d(0, ${heroOffset}px, 0) scale(1.06)`,
              }}
              className={`w-full object-cover ${
                isMobile
                  ? "h-[480px]"
                  : "h-[650px] will-change-transform"
              }`}
            />

            {/* Overlay */}
            <div className="absolute inset-0 bg-gradient-to-t from-[#05050A]/80 via-transparent to-transparent" />

            {/* Together We Stand */}
            <div className="absolute top-6 right-6 text-right">
              <div className="text-white font-black text-3xl md:text-5xl leading-none">
                TOGETHER
              </div>

              <div className="text-white font-black text-3xl md:text-5xl leading-none">
                WE STAND
              </div>
            </div>

            {/* UAE Strip */}
            <div className="absolute bottom-0 left-0 right-0 h-2 flex">
              <div className="flex-1 bg-[#FF4D57]" />
              <div className="flex-1 bg-white" />
              <div className="flex-1 bg-[#3ECF6D]" />
            </div>
          </div>

          {!isMobile && (
            <div className="absolute -z-10 inset-0 blur-3xl bg-[#0055FF]/20" />
          )}
        </div>
      </div>
    </section>
  );
}