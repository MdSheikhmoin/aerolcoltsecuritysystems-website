import { useEffect, useRef, useState } from "react";

const isReducedMotion =
  typeof window !== "undefined" &&
  window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const isMobile =
  typeof window !== "undefined" &&
  (window.innerWidth < 768 ||
    "ontouchstart" in window ||
    navigator.maxTouchPoints > 0);

/**
 * Adds a class (default: "is-visible") to the element when it enters viewport.
 * One-time reveal.
 */
export function useReveal(options = {}) {
  const ref = useRef(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const el = ref.current;

    if (!el) return;

    // Instantly reveal for reduced-motion users
    if (isReducedMotion) {
      setVisible(true);
      return;
    }

    const obs = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setVisible(true);
            obs.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.15,
        rootMargin: "0px 0px -60px 0px",
        ...options,
      }
    );

    obs.observe(el);

    return () => obs.disconnect();
  }, []);

  return [ref, visible];
}

/**
 * Returns a translateY value for a given element based on scroll position.
 * Disabled on mobile/touch devices for performance.
 */
export function useParallax(speed = 0.15) {
  const ref = useRef(null);
  const [offset, setOffset] = useState(0);

  useEffect(() => {
    const el = ref.current;

    if (!el) return;

    // Disable parallax on mobile/reduced-motion
    if (isMobile || isReducedMotion) {
      setOffset(0);
      return;
    }

    let raf = 0;

    const update = () => {
      const rect = el.getBoundingClientRect();
      const vh = window.innerHeight || 800;

      // Only compute if visible
      if (rect.bottom > 0 && rect.top < vh) {
        const progress = (rect.top - vh) / (vh + rect.height);

        setOffset(progress * speed * 220);
      }

      raf = 0;
    };

    const onScroll = () => {
      if (!raf) {
        raf = requestAnimationFrame(update);
      }
    };

    update();

    window.addEventListener("scroll", onScroll, {
      passive: true,
    });

    window.addEventListener("resize", onScroll);

    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onScroll);

      if (raf) {
        cancelAnimationFrame(raf);
      }
    };
  }, [speed]);

  return [ref, offset];
}