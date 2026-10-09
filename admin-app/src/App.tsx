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
  Sparkles
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

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    document.documentElement.dataset.accent = accent;
  }, [theme, accent]);

  const toggleTheme = () => {
    setTheme((prev) => (prev === "dark" ? "light" : "dark"));
  };

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
              <span className={styles.brandName}>Alezux Members</span>
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
          <button
            type="button"
            className={styles.themeToggleBtn}
            onClick={toggleTheme}
            aria-label="Cambiar tema"
            title={`Cambiar a tema ${theme === "dark" ? "claro" : "oscuro"}`}
          >
            {theme === "dark" ? <Sun size={17} /> : <Moon size={17} />}
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
