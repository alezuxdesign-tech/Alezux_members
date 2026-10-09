import { useState, useEffect } from "react";
import { 
  BarChart3, 
  GraduationCap, 
  Users, 
  CreditCard, 
  Mail, 
  Sliders, 
  Moon, 
  Sun, 
  Layers,
  ArrowLeft,
  Maximize2,
  Minimize2
} from "lucide-react";
import { SegmentedControl } from "./components/arc/segmented-control/segmented-control";
import { OverviewView } from "./views/OverviewView";
import { CoursesView } from "./views/CoursesView";
import { StudentsView } from "./views/StudentsView";
import { FinanceView } from "./views/FinanceView";
import { MarketingView } from "./views/MarketingView";
import { SettingsView } from "./views/SettingsView";
import styles from "./App.module.css";

type TabId = "overview" | "courses" | "students" | "finance" | "marketing" | "settings";

export function App() {
  const [activeTab, setActiveTab] = useState<TabId>("overview");
  const [theme, setTheme] = useState<"dark" | "light">("dark");
  const [accent, setAccent] = useState<string>("violet");
  const [isFullscreen, setIsFullscreen] = useState(false);

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    document.documentElement.dataset.accent = accent;

    const handleFullscreenChange = () => {
      setIsFullscreen(!!document.fullscreenElement);
    };
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    return () => {
      document.removeEventListener("fullscreenchange", handleFullscreenChange);
    };
  }, [theme, accent]);

  const toggleTheme = () => {
    setTheme((prev) => (prev === "dark" ? "light" : "dark"));
  };

  const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => {});
    } else {
      document.exitFullscreen().catch(() => {});
    }
  };

  const wpAdminUrl = (window as any).crezca_admin_data?.wp_admin_url 
    || (window as any).alezux_admin_data?.wp_admin_url 
    || "/wp-admin/";

  const navOptions = [
    { value: "overview" as const, label: "Métricas & Resumen", icon: <BarChart3 size={15} /> },
    { value: "courses" as const, label: "Cursos & Builder", icon: <GraduationCap size={15} /> },
    { value: "students" as const, label: "Estudiantes & Accesos", icon: <Users size={15} /> },
    { value: "finance" as const, label: "Finanzas & Planes", icon: <CreditCard size={15} /> },
    { value: "marketing" as const, label: "Marketing", icon: <Mail size={15} /> },
    { value: "settings" as const, label: "Ajustes", icon: <Sliders size={15} /> },
  ];

  return (
    <div className={styles.appShell}>
      {/* Barra de Navegación Superior */}
      <header className={styles.topbar}>
        <div className={styles.brandGroup}>
          <div className={styles.logoMark}>
            <Layers size={18} className={styles.logoIcon} />
          </div>
          <div>
            <div className={styles.brandTitleWrap}>
              <span className={styles.brandName}>Crezca</span>
              <span className={styles.versionBadge}>v2.0 Arc UI</span>
            </div>
            <span className={styles.brandSubtitle}>Academia & Membresías</span>
          </div>
        </div>

        {/* Selector de Pestañas Estilo Arc SegmentedControl */}
        <div className={styles.navWrapper}>
          <SegmentedControl
            options={navOptions}
            value={activeTab}
            onChange={(val) => setActiveTab(val)}
            size="md"
          />
        </div>

        {/* Acciones de la barra */}
        <div className={styles.topActions}>
          <a
            href={wpAdminUrl}
            className={styles.backToWpBtn}
            title="Volver al panel tradicional de WordPress"
          >
            <ArrowLeft size={14} />
            <span>Volver a WordPress</span>
          </a>

          <button
            type="button"
            className={styles.iconBtn}
            onClick={toggleFullscreen}
            aria-label="Pantalla completa"
            title={isFullscreen ? "Salir de pantalla completa" : "Pantalla completa"}
          >
            {isFullscreen ? <Minimize2 size={16} /> : <Maximize2 size={16} />}
          </button>

          <button
            type="button"
            className={styles.iconBtn}
            onClick={toggleTheme}
            aria-label="Cambiar tema"
            title={`Cambiar a tema ${theme === "dark" ? "claro" : "oscuro"}`}
          >
            {theme === "dark" ? <Sun size={16} /> : <Moon size={16} />}
          </button>
        </div>
      </header>

      {/* Contenido Principal */}
      <main className={styles.mainContent}>
        {activeTab === "overview" && <OverviewView onNavigate={(tab) => setActiveTab(tab as TabId)} />}
        {activeTab === "courses" && <CoursesView />}
        {activeTab === "students" && <StudentsView />}
        {activeTab === "finance" && <FinanceView />}
        {activeTab === "marketing" && <MarketingView />}
        {activeTab === "settings" && (
          <SettingsView
            currentAccent={accent}
            onAccentChange={setAccent}
            currentTheme={theme}
            onThemeToggle={toggleTheme}
          />
        )}
      </main>
    </div>
  );
}

export default App;
