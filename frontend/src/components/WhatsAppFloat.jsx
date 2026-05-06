import { MessageCircle } from "lucide-react";
import { SITE } from "../lib/site";

export default function WhatsAppFloat() {
  const text = encodeURIComponent("Hi Aerol Colt, I'd like to request a site assessment.");
  return (
    <a
      data-testid="whatsapp-float"
      href={`https://wa.me/${SITE.whatsapp}?text=${text}`}
      target="_blank"
      rel="noreferrer"
      aria-label="Chat on WhatsApp"
      className="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-[#25D366] hover:bg-[#1FB85B] text-[#05050A] font-semibold px-4 py-3 shadow-[0_10px_40px_rgba(37,211,102,0.45)] transition-all hover:scale-105"
    >
      <MessageCircle className="h-5 w-5" strokeWidth={2.5} />
      <span className="hidden sm:inline text-sm">WhatsApp</span>
    </a>
  );
}
