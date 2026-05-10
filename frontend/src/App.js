import "@/App.css";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { Toaster } from "sonner";
import Navbar from "@/components/Navbar";
import Hero from "@/components/Hero";
import WhyChooseUs from "@/components/WhyChooseUs";
import Products from "@/components/Products";
import Services from "@/components/Services";
import Proof from "@/components/Proof";
import FinalCTA from "@/components/FinalCTA";
import Careers from "@/components/Careers";
import Contact from "@/components/Contact";
import Footer from "@/components/Footer";
import LogoFilter from "@/components/LogoFilter";

const scrollToAssessment = () => {
  const el = document.querySelector("#assessment-form");

  if (el) {
    el.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
};

const Home = () => (
  <main
    data-testid="home-page"
    className="relative bg-[#05050A] text-white overflow-x-hidden"
  >
    <LogoFilter />
    <Navbar />

    <Hero
      onPrimary={scrollToAssessment}
      onSecondary={scrollToAssessment}
    />

    <WhyChooseUs />

    <Products onRequest={scrollToAssessment} />

    <Services onRequest={scrollToAssessment} />

    <Proof />

    <FinalCTA onClick={scrollToAssessment} />

    <Careers />

    <Contact />

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
          <Route path="/" element={<Home />} />
        </Routes>
      </BrowserRouter>
    </div>
  );
}

export default App;