import React from "react";
import ReactDOM from "react-dom/client";
import "./components/arc/foundation.css";
import "./index.css";
import App from "./App";

const container = document.getElementById("alezux-admin-root") || document.getElementById("root");

if (container) {
  ReactDOM.createRoot(container).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  );
}
