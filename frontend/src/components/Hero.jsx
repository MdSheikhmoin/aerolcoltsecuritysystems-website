import { ArrowRight, ShieldCheck, CircleCheck } from "lucide-react";
import { Button } from "./ui/button";
import { IMAGES } from "../lib/site";
import { useParallax } from "../lib/useScrollFx";

export default function Hero({ onPrimary, onSecondary }) {
  const [heroImgRef, heroOffset] = useParallax(0.25);
  return (
    <section
      id="home"
      data-testid="hero-section"
      className="relative pt-32 md:pt-40 pb-20 md:pb-28 overflow-hidden"
    >
      {/* Background ambient */}
      <div className="absolute inset-0 bg-grid opacity-[0.35] pointer-events-none" />
      <div className="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full bg-[#0055FF]/20 blur-[140px] pointer-events-none" />
      <div className="absolute top-1/3 -right-20 h-[420px] w-[420px] rounded-full bg-[#00E5FF]/10 blur-[140px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-6 md:px-10 lg:px-12 grid lg:grid-cols-12 gap-10 lg:gap-16 items-center">
        {/* Left: copy */}
        <div className="lg:col-span-7 fade-up">
          <div
            data-testid="hero-badge"
            className="inline-flex items-center gap-2 rounded-full border border-[#1E2235] bg-[#0F111A]/70 px-3.5 py-1.5 text-xs font-medium text-[#cbd5e1] backdrop-blur"
          >
            <span className="h-1.5 w-1.5 rounded-full bg-[#00E5FF] shadow-[0_0_12px_#00E5FF]" />
            SIRA Approved · Dubai, UAE
          </div>

          <h1
            data-testid="hero-heading"
            className="mt-6 font-display font-black text-4xl sm:text-5xl lg:text-6xl leading-[1.02] tracking-tighter text-white"
          >
            Most security systems fail when it matters.{" "}
            <span className="bg-gradient-to-r from-[#0055FF] via-[#2D85FF] to-[#00E5FF] bg-clip-text text-transparent">
              Ours are SIRA-approved.
            </span>
          </h1>

          <p
            data-testid="hero-subheading"
            className="mt-6 text-base md:text-lg text-[#94A3B8] max-w-2xl leading-relaxed"
          >
            Secure, automate, and control your residential and commercial property
            with fully integrated smart systems designed for real-world reliability.
          </p>

          <div className="mt-9 flex flex-wrap items-center gap-4">
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

          <p
            data-testid="hero-cta-subtext"
            className="mt-5 text-sm text-[#94A3B8] flex items-center gap-2"
          >
            <CircleCheck className="h-4 w-4 text-[#00E5FF]" />
            On-site evaluation with system recommendations and cost estimate.
          </p>

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

        {/* Right: image */}
        <div className="lg:col-span-5 relative fade-up" style={{ animationDelay: "120ms" }}>
          <div className="relative rounded-3xl overflow-hidden border border-[#1E2235] glow-ring">
            <img
              ref={heroImgRef}
              src={IMAGES.hero}
              alt="SIRA approved CCTV camera with electric blue illumination"
              style={{ transform: `translate3d(0, ${heroOffset}px, 0) scale(1.06)` }}
              className="w-full h-[520px] lg:h-[600px] object-cover will-change-transform"
              loading="eager"
            />
            <div className="absolute inset-0 bg-gradient-to-tr from-[#05050A] via-[#05050A]/20 to-transparent" />
            <div className="absolute bottom-5 left-5 right-5 flex items-center gap-3 rounded-2xl border border-white/10 bg-[#05050A]/70 backdrop-blur-xl px-4 py-3">
              <div className="h-9 w-9 rounded-lg bg-[#0055FF]/20 border border-[#0055FF]/40 flex items-center justify-center">
                <ShieldCheck className="h-5 w-5 text-[#00E5FF]" />
              </div>
              <div className="leading-tight">
                <div className="text-white font-semibold text-sm">
                  SIRA Certified Installations
                </div>
                <div className="text-[#94A3B8] text-xs">
                  Compliant with UAE security regulations
                </div>
              </div>
            </div>
          </div>
          <div className="absolute -z-10 inset-0 blur-3xl bg-[#0055FF]/20" />
        </div>
      </div>
    </section>
  );
}
