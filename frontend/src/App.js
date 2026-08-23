import "@/App.css";
import { lazy, Suspense } from "react";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { Toaster } from "sonner";

import Navbar from "@/components/Navbar";
import WhyChooseUs from "@/components/WhyChooseUs";
import Products from "@/components/Products";
import Services from "@/components/Services";
import Footer from "@/components/Footer";
import LogoFilter from "@/components/LogoFilter";
import Solidarity from "@/components/Solidarity";

import CareersPage from "@/pages/CareersPage";
import JobDetailsPage from "@/pages/JobDetailsPage";
import JobApplicationPage from "@/pages/JobApplicationPage";

// Lazy-loaded sections
const Proof = lazy(() => import("@/components/Proof"));
const FinalCTA = lazy(() => import("@/components/FinalCTA"));
const Contact = lazy(() => import("@/components/Contact"));

const scrollToAssessment = () => {
  const el = document.querySelector("#assessment-form");

  if (el) {
    el.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
};

const SectionLoader = () => (
  <div className="w-full py-16" />
);

const Home = () => (
  <main
    data-testid="home-page"
    className="relative bg-[#05050A] text-white overflow-x-hidden"
  >
    <LogoFilter />

    <Navbar />

    <Solidarity onCtaClick={scrollToAssessment} />

    <WhyChooseUs />

    <Products onRequest={scrollToAssessment} />

    <Services onRequest={scrollToAssessment} />

    <Suspense fallback={<SectionLoader />}>
      <Proof />
    </Suspense>

    <Suspense fallback={<SectionLoader />}>
      <FinalCTA onClick={scrollToAssessment} />
    </Suspense>

    <Suspense fallback={<SectionLoader />}>
      <Contact />
    </Suspense>

    <Footer />
  </main>
);

function App() {
  return (
    <div className="App">
      <Toaster
        position="top-right"
        theme="dark"
        toastOptions={{
          style: {
            background: "#0F111A",
            border: "1px solid #1E2235",
            color: "#F8FAFC",
          },
        }}
      />

      <BrowserRouter>
        <Routes>

          {/* HOME */}
          <Route
            path="/"
            element={<Home />}
          />

          {/* CAREERS */}
          <Route
            path="/careers"
            element={<CareersPage />}
          />

          {/* DYNAMIC JOB DETAILS */}
          <Route
            path="/careers/job/:slug"
            element={<JobDetailsPage />}
          />

          {/* JOB APPLICATION */}
          <Route
            path="/careers/job/:slug/apply"
            element={<JobApplicationPage />}
          />

        </Routes>
      </BrowserRouter>
    </div>
  );
}

export default App;