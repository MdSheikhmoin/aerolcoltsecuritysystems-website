import { useState } from "react";
import axios from "axios";
import { Phone, Smartphone, Mail, MapPin, ArrowRight, Loader2 } from "lucide-react";
import { toast } from "sonner";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Textarea } from "./ui/textarea";
import { SITE } from "../lib/site";

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

export default function Contact({ defaultSource = "site_assessment" }) {
  const [form, setForm] = useState({ name: "", phone: "", email: "", message: "" });
  const [loading, setLoading] = useState(false);

  const update = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

  const onSubmit = async (e) => {
    e.preventDefault();
    if (!form.name.trim() || !form.phone.trim()) {
      toast.error("Please provide your name and phone.");
      return;
    }
    setLoading(true);
    try {
      await axios.post(`${API}/leads`, {
        name: form.name.trim(),
        phone: form.phone.trim(),
        email: form.email.trim() || null,
        message: form.message.trim() || null,
        source: defaultSource,
      });
      toast.success("Thank you! Our team will reach out shortly.");
      setForm({ name: "", phone: "", email: "", message: "" });
    } catch (err) {
      console.error(err);
      toast.error("Could not submit. Please try again or call us.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <section
      id="contact"
      data-testid="contact-section"
      className="relative py-24 md:py-32 bg-[#07080F]"
    >
      <div className="mx-auto max-w-7xl px-6 md:px-10 lg:px-12">
        <div className="grid lg:grid-cols-12 gap-10 lg:gap-16">
          {/* Left */}
          <div className="lg:col-span-5">
            <div className="text-[11px] uppercase tracking-[0.28em] text-[#00E5FF] mb-4 font-medium">
              — Contact
            </div>
            <h2
              data-testid="contact-heading"
              className="font-display font-bold text-4xl sm:text-5xl tracking-tighter text-white"
            >
              Let&apos;s secure your property.
            </h2>
            <p className="mt-5 text-[#94A3B8] leading-relaxed max-w-md">
              Talk to our SIRA-certified engineers. We respond within one business day.
            </p>

            <div className="mt-10 space-y-4">
              <a
                data-testid="contact-phone"
                href={`tel:${SITE.phoneTel}`}
                className="flex items-center gap-4 rounded-2xl border border-[#1E2235] bg-[#0F111A]/60 p-5 hover:border-[#0055FF]/50 transition-colors"
              >
                <div className="h-11 w-11 rounded-xl bg-[#0055FF]/15 border border-[#0055FF]/30 flex items-center justify-center">
                  <Phone className="h-5 w-5 text-[#00E5FF]" />
                </div>
                <div>
                  <div className="text-[10px] uppercase tracking-[0.22em] text-[#94A3B8]">Office Phone</div>
                  <div className="text-white font-medium mt-0.5">{SITE.phone}</div>
                </div>
              </a>

              <a
                data-testid="contact-mobile"
                href={`tel:${SITE.phoneMobileTel}`}
                className="flex items-center gap-4 rounded-2xl border border-[#1E2235] bg-[#0F111A]/60 p-5 hover:border-[#0055FF]/50 transition-colors"
              >
                <div className="h-11 w-11 rounded-xl bg-[#0055FF]/15 border border-[#0055FF]/30 flex items-center justify-center">
                  <Smartphone className="h-5 w-5 text-[#00E5FF]" />
                </div>
                <div>
                  <div className="text-[10px] uppercase tracking-[0.22em] text-[#94A3B8]">Mobile</div>
                  <div className="text-white font-medium mt-0.5">{SITE.phoneMobile}</div>
                </div>
              </a>

              <a
                data-testid="contact-email"
                href={`mailto:${SITE.email}`}
                className="flex items-center gap-4 rounded-2xl border border-[#1E2235] bg-[#0F111A]/60 p-5 hover:border-[#0055FF]/50 transition-colors"
              >
                <div className="h-11 w-11 rounded-xl bg-[#0055FF]/15 border border-[#0055FF]/30 flex items-center justify-center">
                  <Mail className="h-5 w-5 text-[#00E5FF]" />
                </div>
                <div className="min-w-0">
                  <div className="text-[10px] uppercase tracking-[0.22em] text-[#94A3B8]">Email</div>
                  <div className="text-white font-medium mt-0.5 break-all">{SITE.email}</div>
                </div>
              </a>

              <a
                data-testid="contact-address"
                href={SITE.mapsUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-start gap-4 rounded-2xl border border-[#1E2235] bg-[#0F111A]/60 p-5 hover:border-[#0055FF]/50 transition-colors group cursor-pointer"
              >
                <div className="h-11 w-11 rounded-xl bg-[#0055FF]/15 border border-[#0055FF]/30 flex items-center justify-center shrink-0">
                  <MapPin className="h-5 w-5 text-[#00E5FF]" />
                </div>
                <div>
                  <div className="text-[10px] uppercase tracking-[0.22em] text-[#94A3B8] flex items-center gap-2">
                    Office Address
                    <span className="text-[#00E5FF] opacity-0 group-hover:opacity-100 transition-opacity normal-case tracking-normal text-[11px]">
                      Open in Maps ↗
                    </span>
                  </div>
                  <div className="text-white font-medium mt-1 leading-relaxed text-[15px]">
                    {SITE.address}
                  </div>
                  <div className="text-[#94A3B8] text-[13px] mt-1">{SITE.poBox}</div>
                </div>
              </a>
            </div>
          </div>

          {/* Right: form */}
          <div className="lg:col-span-7">
            <form
              data-testid="contact-form"
              onSubmit={onSubmit}
              className="rounded-3xl border border-[#1E2235] bg-gradient-to-br from-[#0F111A] to-[#0A0C14] p-7 md:p-10"
            >
              <h3 className="font-display font-bold text-2xl md:text-3xl tracking-tight text-white">
                Request a free site assessment
              </h3>
              <p className="mt-2 text-[#94A3B8] text-sm">
                Share a few details and we&apos;ll get back to you promptly.
              </p>

              <div className="mt-7 grid md:grid-cols-2 gap-5">
                <div>
                  <label className="text-[11px] uppercase tracking-[0.22em] text-[#94A3B8]">
                    Name *
                  </label>
                  <Input
                    data-testid="contact-form-name"
                    value={form.name}
                    onChange={update("name")}
                    placeholder="Full name"
                    className="mt-2 h-12 bg-[#05050A] border-[#1E2235] text-white placeholder:text-[#64748b] focus-visible:ring-[#0055FF] focus-visible:ring-offset-0"
                  />
                </div>
                <div>
                  <label className="text-[11px] uppercase tracking-[0.22em] text-[#94A3B8]">
                    Phone *
                  </label>
                  <Input
                    data-testid="contact-form-phone"
                    value={form.phone}
                    onChange={update("phone")}
                    placeholder="+971 ..."
                    className="mt-2 h-12 bg-[#05050A] border-[#1E2235] text-white placeholder:text-[#64748b] focus-visible:ring-[#0055FF] focus-visible:ring-offset-0"
                  />
                </div>
              </div>

              <div className="mt-5">
                <label className="text-[11px] uppercase tracking-[0.22em] text-[#94A3B8]">
                  Email (optional)
                </label>
                <Input
                  data-testid="contact-form-email"
                  type="email"
                  value={form.email}
                  onChange={update("email")}
                  placeholder="you@company.com"
                  className="mt-2 h-12 bg-[#05050A] border-[#1E2235] text-white placeholder:text-[#64748b] focus-visible:ring-[#0055FF] focus-visible:ring-offset-0"
                />
              </div>

              <div className="mt-5">
                <label className="text-[11px] uppercase tracking-[0.22em] text-[#94A3B8]">
                  Message
                </label>
                <Textarea
                  data-testid="contact-form-message"
                  value={form.message}
                  onChange={update("message")}
                  placeholder="Tell us about your property, systems needed, or any questions..."
                  rows={5}
                  className="mt-2 bg-[#05050A] border-[#1E2235] text-white placeholder:text-[#64748b] focus-visible:ring-[#0055FF] focus-visible:ring-offset-0"
                />
              </div>

              <div className="mt-8">
                <Button
                  data-testid="contact-form-submit"
                  type="submit"
                  disabled={loading}
                  className="group bg-[#0055FF] hover:bg-[#0033CC] text-white font-semibold rounded-full h-12 px-7 text-base shadow-[0_0_24px_rgba(0,85,255,0.45)] hover:shadow-[0_0_36px_rgba(0,85,255,0.65)] transition-all"
                >
                  {loading ? (
                    <>
                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                      Sending...
                    </>
                  ) : (
                    <>
                      Send request
                      <ArrowRight className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </>
                  )}
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  );
}
