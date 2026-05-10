import { useEffect, useState } from "react";
import { SITE } from "../lib/site";
import { Button } from "./ui/button";

const NAV_LINKS = [
  { label: "Home", href: "#home", id: "nav-home" },
  { label: "Products", href: "#products", id: "nav-products" },
  { label: "Services", href: "#services", id: "nav-services" },
  { label: "About", href: "#about", id: "nav-about" },
  { label: "Careers", href: "#careers", id: "nav-careers" },
  { label: "Contact", href: "#contact", id: "nav-contact" },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20);

    onScroll();

    window.addEventListener("scroll", onScroll);

    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const scrollTo = (href) => {
    const el = document.querySelector(href);

    if (el) {
      el.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  };

  return (
    <header
      data-testid="site-navbar"
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        scrolled
          ? "backdrop-blur-xl bg-[#05050A]/80 border-b border-white/5"
          : "bg-transparent"
      }`}
    >
      <div className="mx-auto max-w-7xl px-4 md:px-10 lg:px-12">
        
        {/* DESKTOP NAVBAR */}
        <div className="hidden md:flex py-4 items-center justify-between">
          
          <a
            href="#home"
            data-testid="navbar-logo"
            onClick={(e) => {
              e.preventDefault();
              scrollTo("#home");
            }}
            className="flex items-center gap-3 group"
          >
            <img
              src={SITE.logo}
              alt="Aerol Colt Security Systems"
              className="h-14 w-14 object-contain drop-shadow-[0_0_18px_rgba(0,85,255,0.7)]"
              style={{ filter: "url(#logo-knockout)" }}
            />

            <div className="leading-tight">
              <div className="font-display font-bold text-[15px] tracking-tight text-white">
                Aerol Colt
              </div>

              <div className="text-[10px] uppercase tracking-[0.22em] text-[#94A3B8]">
                Security Systems
              </div>
            </div>
          </a>

          <nav className="flex items-center gap-8">
            {NAV_LINKS.map((l) => (
              <a
                key={l.id}
                href={l.href}
                onClick={(e) => {
                  e.preventDefault();
                  scrollTo(l.href);
                }}
                className="text-sm text-[#cbd5e1] hover:text-white transition-colors relative after:absolute after:left-0 after:-bottom-1.5 after:h-[2px] after:w-0 after:bg-[#00E5FF] hover:after:w-full after:transition-all after:duration-300"
              >
                {l.label}
              </a>
            ))}
          </nav>

          <Button
            onClick={() => scrollTo("#contact")}
            className="bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold rounded-full px-5 h-10 shadow-[0_0_24px_rgba(0,85,255,0.45)] hover:shadow-[0_0_32px_rgba(0,85,255,0.65)] transition-all"
          >
            Request Site Assessment
          </Button>
        </div>

        {/* MOBILE NAVBAR */}
        <div className="md:hidden flex items-center gap-4 py-4 overflow-hidden">
          
          {/* LOGO */}
          <a
            href="#home"
            onClick={(e) => {
              e.preventDefault();
              scrollTo("#home");
            }}
            className="shrink-0"
          >
            <img
              src={SITE.logo}
              alt="Aerol Colt Security Systems"
              className="h-11 w-11 object-contain drop-shadow-[0_0_18px_rgba(0,85,255,0.7)]"
              style={{ filter: "url(#logo-knockout)" }}
            />
          </a>

          {/* SLIDING NAV */}
          <div className="flex-1 overflow-x-auto scrollbar-hide">
            <div className="flex items-center gap-6 min-w-max pr-4">
              {NAV_LINKS.map((l) => (
                <a
                  key={l.id}
                  href={l.href}
                  onClick={(e) => {
                    e.preventDefault();
                    scrollTo(l.href);
                  }}
                  className="text-sm whitespace-nowrap text-[#cbd5e1] hover:text-white transition-colors"
                >
                  {l.label}
                </a>
              ))}
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}