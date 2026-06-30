import { ArrowRight } from "lucide-react";
import { Button } from "./ui/button";

import uaePhoto from "../assets/images/UAE.webp";
import heroCamera from "../assets/images/hero.webp";
import siraLogo from "../assets/images/Sira.png";

export default function Solidarity({ onCtaClick }) {
  return (
    <section
      id="home"
      data-testid="solidarity-section"
      className="relative overflow-hidden bg-[#05050A]"
    >
      {/* TOP UAE HERO */}
      <div className="relative min-h-[760px] md:min-h-[880px] overflow-hidden">

        {/* BACKGROUND IMAGE */}
        <img
          src={uaePhoto}
          alt="UAE Solidarity"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          className="absolute inset-0 w-full h-full object-cover object-[32%_center]"
        />

        {/* DARK OVERLAY */}
        <div className="absolute inset-0 bg-[#05050A]/60" />

        {/* LEFT GRADIENT */}
        <div className="absolute inset-y-0 left-0 w-full md:w-[58%] bg-gradient-to-r from-[#05050A] via-[#05050A]/92 to-transparent" />

        {/* CONTENT */}
        <div className="relative z-10 mx-auto max-w-7xl px-6 md:px-10 lg:px-12 pt-24 md:pt-32">

          {/* UAE LABEL */}
          <div className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-black/30 backdrop-blur px-4 py-2 text-[11px] uppercase tracking-[0.2em] text-white/90">

            <span className="inline-flex h-3 w-5 overflow-hidden rounded-[2px] border border-white/20">
              <span className="w-1/3 bg-[#EF3340]" />

              <span className="flex-1 flex flex-col">
                <span className="flex-1 bg-white" />
                <span className="flex-1 bg-[#00732F]" />
                <span className="flex-1 bg-black" />
              </span>
            </span>

            Proudly UAE • Est. in Dubai
          </div>

          {/* HEADING */}
          <h1 className="mt-8 font-display font-black text-5xl sm:text-6xl lg:text-7xl tracking-tighter leading-[0.95] text-white max-w-3xl">
            Together We Stand.
            <br />

            <span className="bg-gradient-to-r from-[#EF3340] via-white to-[#00732F] bg-clip-text text-transparent">
              With the UAE.
            </span>
          </h1>

          {/* POINTS */}
          <div className="mt-10 space-y-4 text-lg">

            <div className="flex items-center gap-3 text-white">
              <span className="h-2 w-2 rounded-full bg-[#EF3340]" />
              SIRA-Aligned Operations
            </div>

            <div className="flex items-center gap-3 text-white">
              <span className="h-2 w-2 rounded-full bg-[#00732F]" />
              UAE Vision 2031 Aligned
            </div>

            <div className="flex items-center gap-3 text-white">
              <span className="h-2 w-2 rounded-full bg-white" />
              Local Team • Local Support
            </div>

            <div className="flex items-center gap-3 text-white">
              <span className="h-2 w-2 rounded-full bg-black border border-white/30" />
              Trusted Security Systems
            </div>
          </div>
        </div>

        {/* UAE STRIPE */}
        <div className="absolute bottom-0 left-0 right-0 h-[8px] flex z-20">
  <span className="flex-1 bg-[#EF3340]" />
  <span className="flex-1 bg-[#00732F]" />
  <span className="flex-1 bg-white" />
  <span className="flex-1 bg-black" />
</div>
      </div>

      {/* SECOND CAMERA SECTION */}
      <div className="relative bg-[#05050A] py-20 md:py-24">

        <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12 grid lg:grid-cols-2 gap-14 items-center">

          {/* LEFT CONTENT */}
          <div>

            {/* SIRA BADGE */}
            <div className="inline-flex items-center gap-4 rounded-full border border-[#0055FF]/40 bg-[#0055FF]/10 px-5 py-3 mb-7 shadow-[0_0_40px_rgba(0,85,255,0.15)]">

              {/* LOGO */}
              <div className="relative h-16 w-16 shrink-0 flex items-center justify-center -ml-1">

  <img
    src={siraLogo}
    alt="SIRA Approved"
    className="h-full w-full object-contain scale-[1.45]"
  />
</div>

              {/* TEXT */}
              <div className="flex flex-col leading-none">

                <span className="text-white font-black tracking-[0.18em] text-[13px] md:text-[14px]">
                  100% SIRA
                </span>

                <span className="text-[#7DD3FC] font-semibold tracking-[0.22em] text-[10px] md:text-[11px] mt-1">
                  APPROVED SECURITY
                </span>
              </div>
            </div>

            {/* HEADING */}
            <h2 className="font-display font-black text-4xl md:text-6xl tracking-tighter leading-[1.02] text-white">
              Most security systems fail when it matters.
              <br />

              <span className="text-[#0055FF]">
                Ours are SIRA-approved.
              </span>
            </h2>

            {/* DESCRIPTION */}
            <p className="mt-7 text-[#CBD5E1] text-lg leading-relaxed max-w-xl">
              Secure, automate, and control your residential and commercial
              property with fully integrated smart systems designed for
              real-world reliability.
            </p>

            {/* WHITE CTA */}
            <div className="mt-10 flex items-center justify-between gap-4 rounded-full bg-white p-2 pl-6 md:pl-7 shadow-[0_20px_60px_-15px_rgba(0,85,255,0.45)] max-w-xl">

              <div>
                <div className="text-[10px] uppercase tracking-[0.22em] text-[#64748b] font-semibold">
                  Get Started
                </div>

                <div className="text-[#05050A] font-display font-bold text-base tracking-tight">
                  Request for site assistance
                </div>
              </div>

              <Button
                onClick={onCtaClick}
                className="group shrink-0 bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold rounded-full h-12 px-6 text-base"
              >
                Request

                <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
              </Button>
            </div>

            {/* GET QUOTE */}
            <div className="mt-5">

              <Button
                onClick={onCtaClick}
                variant="outline"
                className="rounded-full border border-white/15 bg-transparent hover:bg-white/5 text-white px-7 h-12 text-base"
              >
                Get a Quote
              </Button>
            </div>
          </div>

          {/* CAMERA IMAGE */}
          <div className="relative">

            <div className="rounded-3xl overflow-hidden border border-white/10 shadow-[0_30px_80px_-20px_rgba(0,85,255,0.45)]">

              <img
                src={heroCamera}
                alt="Security Camera"
                loading="lazy"
                decoding="async"
                className="w-full h-[520px] object-cover"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}