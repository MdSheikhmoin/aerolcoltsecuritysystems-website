import { ArrowRight } from "lucide-react";
import { Button } from "./ui/button";

export default function FinalCTA({ onClick }) {
  return (
    <section
      data-testid="final-cta-section"
      className="relative py-24 md:py-36 overflow-hidden"
    >
      <div className="absolute inset-0 bg-grid opacity-[0.25] pointer-events-none" />
      <div className="absolute -top-32 left-1/2 -translate-x-1/2 h-[480px] w-[900px] rounded-full bg-[#0055FF]/20 blur-[160px] pointer-events-none" />

      <div className="relative mx-auto max-w-5xl px-6 md:px-10 text-center">
        <h2
          data-testid="final-cta-heading"
          className="font-display font-black text-4xl sm:text-5xl lg:text-6xl tracking-tighter text-white leading-[1.03] max-w-4xl mx-auto"
        >
          Secure Your Property with a System You Can{" "}
          <span className="bg-gradient-to-r from-[#0055FF] via-[#2D85FF] to-[#00E5FF] bg-clip-text text-transparent">
            Rely On
          </span>
          .
        </h2>
        <p className="mt-6 text-[#94A3B8] text-base md:text-lg max-w-2xl mx-auto leading-relaxed">
          Book a complimentary site assessment. We&apos;ll evaluate your property,
          recommend the right SIRA-approved system, and share a transparent estimate.
        </p>
        <div className="mt-9 flex flex-wrap items-center justify-center gap-4">
          <Button
            data-testid="final-cta-button"
            onClick={onClick}
            className="group bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold rounded-full h-13 px-8 py-6 text-base shadow-[0_0_30px_rgba(0,85,255,0.5)] hover:shadow-[0_0_44px_rgba(0,85,255,0.7)] transition-all"
          >
            Request a Free Site Assessment
            <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Button>
        </div>
      </div>
    </section>
  );
}
