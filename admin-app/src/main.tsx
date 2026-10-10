import React from "react";
import { createRoot } from "react-dom/client";
import * as ReactDOMClient from "react-dom/client";
import ReactDOM from "react-dom";
import "./components/arc/foundation.css";
import "./index.css";
import App from "./App";

interface ErrorBoundaryProps {
  children: React.ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
  error: Error | null;
}

class ErrorBoundary extends React.Component<ErrorBoundaryProps, ErrorBoundaryState> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error("Crezca Admin Error:", error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div
          style={{
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",
            height: "100vh",
            background: "#090a0f",
            color: "#f87171",
            fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            padding: "24px",
            textAlign: "center",
          }}
        >
          <div
            style={{
              background: "#161b26",
              border: "1px solid #2d3748",
              borderRadius: "12px",
              padding: "32px",
              maxWidth: "540px",
              width: "100%",
              boxShadow: "0 20px 40px rgba(0,0,0,0.5)",
            }}
          >
            <h2 style={{ color: "#ffffff", fontSize: "18px", margin: "0 0 10px", fontWeight: 700 }}>
              Se produjo un problema al cargar el panel
            </h2>
            <p style={{ color: "#94a3b8", fontSize: "13px", lineHeight: "1.5", margin: "0 0 20px" }}>
              {this.state.error?.message || "Error al inicializar la aplicación."}
            </p>
            <button
              type="button"
              onClick={() => window.location.reload()}
              style={{
                padding: "10px 24px",
                background: "#7747ff",
                color: "#ffffff",
                border: "none",
                borderRadius: "8px",
                fontWeight: 600,
                fontSize: "14px",
                cursor: "pointer",
              }}
            >
              Recargar Página
            </button>
          </div>
        </div>
      );
    }
    return this.props.children;
  }
}

console.log("%c🔥 [Crezca Bundle] alezux-dashboard.js script execution started!", "color: #38bdf8; font-weight: bold; font-size: 14px;");

function mountApp() {
  console.log("🔍 [Crezca Bundle] mountApp() triggered. Checking for #crezca-admin-root in DOM...");
  const container =
    document.getElementById("crezca-student-root") ||
    document.getElementById("crezca-admin-root") ||
    document.getElementById("alezux-admin-root") ||
    document.getElementById("root");

  if (!container) {
    console.warn("⚠️ [Crezca Bundle] Target container element (#crezca-admin-root or #crezca-student-root) not found yet.");
    return;
  }

  const isStudent =
    container.id === "crezca-student-root" ||
    container.dataset.mode === "student" ||
    Boolean((window as any).crezca_student_data?.is_student_mode);

  if (!(container as any).__crezcaRootMounted) {
    (container as any).__crezcaRootMounted = true;
    console.log("%c🚀 [Crezca Bundle] Crezca root container detected. Initializing React 18...", "color: #a855f7; font-weight: bold; font-size: 14px;");
    container.innerHTML = "";
    try {
      const getMountFn = () => {
        if (typeof createRoot === "function") return createRoot;
        if (typeof (ReactDOMClient as any)?.createRoot === "function") return (ReactDOMClient as any).createRoot;
        if (typeof (ReactDOMClient as any)?.default?.createRoot === "function") return (ReactDOMClient as any).default.createRoot;
        if (typeof (ReactDOM as any)?.createRoot === "function") return (ReactDOM as any).createRoot;
        return null;
      };

      const mountFn = getMountFn();
      if (mountFn) {
        const root = mountFn(container);
        root.render(
          <React.StrictMode>
            <ErrorBoundary>
              <App initialMode={isStudent ? "student" : "admin"} isStandaloneStudent={isStudent} />
            </ErrorBoundary>
          </React.StrictMode>
        );
        console.log("%c✅ [Crezca Bundle] React dashboard mounted successfully into DOM via createRoot!", "color: #22c55e; font-weight: bold; font-size: 15px;");
      } else if (typeof (ReactDOM as any)?.render === "function") {
        (ReactDOM as any).render(
          <React.StrictMode>
            <ErrorBoundary>
              <App initialMode={isStudent ? "student" : "admin"} isStandaloneStudent={isStudent} />
            </ErrorBoundary>
          </React.StrictMode>,
          container
        );
        console.log("%c✅ [Crezca Bundle] React dashboard mounted with legacy render!", "color: #22c55e; font-weight: bold; font-size: 15px;");
      } else {
        throw new Error("No React mounting function (createRoot or render) found!");
      }
    } catch (err) {
      console.error("❌ [Crezca Bundle] Fatal error during createRoot/render:", err);
    }
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", mountApp);
} else {
  mountApp();
}

setTimeout(mountApp, 50);
setTimeout(mountApp, 250);
setTimeout(mountApp, 800);
