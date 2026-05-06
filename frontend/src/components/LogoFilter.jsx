/**
 * Global SVG filter mounted once in the DOM.
 * Turns the black background of the Aerol Colt logo PNG into real transparency
 * (pixels where R+G+B == 0 → alpha 0). Use: style={{ filter: "url(#logo-knockout)" }}
 */
export default function LogoFilter() {
  return (
    <svg
      aria-hidden="true"
      width="0"
      height="0"
      style={{ position: "absolute", width: 0, height: 0, overflow: "hidden" }}
    >
      <defs>
        <filter id="logo-knockout" colorInterpolationFilters="sRGB">
          <feColorMatrix
            type="matrix"
            values="1 0 0 0 0
                    0 1 0 0 0
                    0 0 1 0 0
                    4 4 4 0 0"
          />
        </filter>
      </defs>
    </svg>
  );
}
