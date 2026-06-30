import { SITE } from "../lib/site";

export default function Footer() {
  return (
    <footer
      data-testid="site-footer"
      className="relative border-t border-[#1E2235] bg-[#05050A] py-12"
    >
      <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div className="flex items-center gap-3">
          <img
  src={SITE.logo}
  alt="Aerol Colt Security Systems"
  width="320"
  height="90"
  className="h-14 w-auto object-contain"
  style={{ filter: "url(#logo-knockout)" }}
/>
          <div>
            <div className="font-display font-bold text-white tracking-tight">
              {SITE.company}
            </div>
            <div className="text-xs text-[#94A3B8]">{SITE.addressShort} · {SITE.phone}</div>
          </div>
        </div>

        <div className="text-xs text-[#94A3B8]">
          © {new Date().getFullYear()} Aerol Colt Security Systems LLC. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
