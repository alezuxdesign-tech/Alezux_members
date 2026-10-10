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
  Minimize2,
  PanelLeftClose,
  PanelLeftOpen,
  ChevronRight,
  ShieldCheck
} from "lucide-react";
import { OverviewView } from "./views/OverviewView";
import { CoursesView } from "./views/CoursesView";
import { StudentsView } from "./views/StudentsView";
import { FinanceView } from "./views/FinanceView";
import { MarketingView } from "./views/MarketingView";
import { SettingsView } from "./views/SettingsView";
import { api } from "./services/api";
import styles from "./App.module.css";

type TabId = "overview" | "courses" | "students" | "finance" | "marketing" | "settings";

interface NavItemConfig {
  id: TabId;
  label: string;
  icon: React.ReactNode;
  badge?: string;
}

export function App() {
  const wpData = (window as any).crezca_admin_data || (window as any).alezux_admin_data || {};

  const [activeTab, setActiveTab] = useState<TabId>("overview");
  const [theme, setTheme] = useState<"dark" | "light">(() => {
    const saved = localStorage.getItem("crezca_theme");
    if (saved === "light" || saved === "dark") return saved;
    if (wpData.theme === "light" || wpData.theme === "dark") return wpData.theme;
    return "dark";
  });
  const [accent, setAccent] = useState<string>(() => {
    return localStorage.getItem("crezca_accent") || wpData.accent || "violet";
  });
  const [academyName, setAcademyName] = useState<string>(() => {
    return localStorage.getItem("crezca_academy_name") || wpData.academy_name || "Crezca";
  });
  const [academyLogo, setAcademyLogo] = useState<string>(() => {
    return localStorage.getItem("crezca_academy_logo") || wpData.academy_logo || "";
  });
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [isCollapsed, setIsCollapsed] = useState<boolean>(() => {
    return localStorage.getItem("crezca_sidebar_collapsed") === "true";
  });

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    document.documentElement.dataset.accent = accent;
    localStorage.setItem("crezca_theme", theme);
    localStorage.setItem("crezca_accent", accent);

    const handleFullscreenChange = () => {
      setIsFullscreen(!!document.fullscreenElement);
    };
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    return () => {
      document.removeEventListener("fullscreenchange", handleFullscreenChange);
    };
  }, [theme, accent]);

  // Sincronizar ajustes de la plataforma en segundo plano desde el servidor
  useEffect(() => {
    api.getPlatformSettings().then((s) => {
      if (s.academy_name && s.academy_name !== "Crezca") {
        setAcademyName(s.academy_name);
        localStorage.setItem("crezca_academy_name", s.academy_name);
      }
      if (s.academy_logo) {
        setAcademyLogo(s.academy_logo);
        localStorage.setItem("crezca_academy_logo", s.academy_logo);
      }
      if (s.theme && !localStorage.getItem("crezca_theme")) {
        setTheme(s.theme);
      }
      if (s.accent && !localStorage.getItem("crezca_accent")) {
        setAccent(s.accent);
      }
    });
  }, []);

  const toggleTheme = () => {
    setTheme((prev) => {
      const next = prev === "dark" ? "light" : "dark";
      localStorage.setItem("crezca_theme", next);
      document.documentElement.dataset.theme = next;
      api.savePlatformSettings({ theme: next });
      return next;
    });
  };

  const handleAccentChange = (newAccent: string) => {
    setAccent(newAccent);
    localStorage.setItem("crezca_accent", newAccent);
    document.documentElement.dataset.accent = newAccent;
    api.savePlatformSettings({ accent: newAccent });
  };

  const handleAcademyNameChange = (name: string) => {
    setAcademyName(name);
    localStorage.setItem("crezca_academy_name", name);
  };

  const handleAcademyLogoChange = (logo: string) => {
    setAcademyLogo(logo);
    localStorage.setItem("crezca_academy_logo", logo);
  };

  const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => {});
    } else {
      document.exitFullscreen().catch(() => {});
    }
  };

  const toggleSidebar = () => {
    setIsCollapsed((prev) => {
      const next = !prev;
      localStorage.setItem("crezca_sidebar_collapsed", String(next));
      return next;
    });
  };

  const wpAdminUrl = (window as any).crezca_admin_data?.wp_admin_url 
    || (window as any).alezux_admin_data?.wp_admin_url 
    || "/wp-admin/";

  const navItems: NavItemConfig[] = [
    { id: "overview", label: "Métricas & Resumen", icon: <BarChart3 size={18} /> },
    { id: "courses", label: "Cursos & Builder", icon: <GraduationCap size={18} /> },
    { id: "students", label: "Estudiantes & Accesos", icon: <Users size={18} /> },
    { id: "finance", label: "Finanzas & Planes", icon: <CreditCard size={18} /> },
    { id: "marketing", label: "Marketing & Emails", icon: <Mail size={18} /> },
    { id: "settings", label: "Configuración", icon: <Sliders size={18} /> },
  ];

  const currentNav = navItems.find((item) => item.id === activeTab) || navItems[0];

  return (
    <div className={styles.appShell}>
      {/* =========================================================
          SIDEBAR VERTICAL ESTILO ARC UI
          ========================================================= */}
      <aside className={`${styles.sidebar} ${isCollapsed ? styles.collapsed : ""}`}>
        {/* Cabecera del Sidebar */}
        <div className={styles.sidebarHeader}>
          {!isCollapsed ? (
            <>
              <div className={styles.brandGroup} title={`${academyName || "Crezca"} - Academia & Membresías`}>
                <div className={styles.logoMark}>
                  {academyLogo ? (
                    <img src={academyLogo} alt={academyName || "Logo"} className={styles.logoImg} />
                  ) : (
                    <Layers size={19} className={styles.logoIcon} />
                  )}
                </div>
                <div className={styles.brandText}>
                  <div className={styles.brandTitleWrap}>
                    <span className={styles.brandName}>{academyName || "Crezca"}</span>
                    <span className={styles.versionBadge}>v2.0 Arc UI</span>
                  </div>
                  <span className={styles.brandSubtitle}>Academia & Membresías</span>
                </div>
              </div>

              <button
                type="button"
                className={styles.collapseBtn}
                onClick={toggleSidebar}
                title="Contraer barra lateral"
                aria-label="Contraer barra lateral"
              >
                <PanelLeftClose size={16} />
              </button>
            </>
          ) : (
            <button
              type="button"
              className={styles.collapsedHeaderBtn}
              onClick={toggleSidebar}
              title="Expandir barra lateral"
              aria-label="Expandir barra lateral"
            >
              <div className={styles.logoMark}>
                {academyLogo ? (
                  <img src={academyLogo} alt={academyName || "Logo"} className={styles.logoImg} />
                ) : (
                  <Layers size={19} className={styles.logoIcon} />
                )}
              </div>
            </button>
          )}
        </div>

        {/* Lista de Navegación Vertical */}
        <nav className={styles.navSection}>
          {!isCollapsed && (
            <div className={styles.navSectionLabel}>Plataforma</div>
          )}
          {navItems.map((item) => {
            const isActive = activeTab === item.id;
            return (
              <button
                key={item.id}
                type="button"
                className={`${styles.navItem} ${isActive ? styles.active : ""}`}
                onClick={() => setActiveTab(item.id)}
                title={isCollapsed ? item.label : undefined}
              >
                {isActive && <span className={styles.activeIndicator} />}
                <span className={styles.navIconWrap}>{item.icon}</span>
                {!isCollapsed && <span className={styles.navLabel}>{item.label}</span>}
                {!isCollapsed && item.badge && (
                  <span className={styles.navBadge}>{item.badge}</span>
                )}
              </button>
            );
          })}
        </nav>

        {/* Pie de la Sidebar */}
        <div className={styles.sidebarFooter}>
          {!isCollapsed && (
            <div className={styles.userCard}>
              <div className={styles.userAvatar}>
                <ShieldCheck size={15} />
              </div>
              <div className={styles.userInfo}>
                <span className={styles.userName}>Administrador</span>
                <span className={styles.userStatus}>
                  <span className={styles.statusDot} />
                  Sesión activa
                </span>
              </div>
            </div>
          )}

          {/* Botones de acción rápida: Tema y Pantalla Completa */}
          <div className={styles.footerActionsGrid}>
            <button
              type="button"
              className={styles.iconBtn}
              onClick={toggleTheme}
              title={`Cambiar a tema ${theme === "dark" ? "claro" : "oscuro"}`}
              aria-label="Cambiar tema de color"
            >
              {theme === "dark" ? <Sun size={15} /> : <Moon size={15} />}
              {!isCollapsed && <span>{theme === "dark" ? "Modo Claro" : "Modo Oscuro"}</span>}
            </button>

            <button
              type="button"
              className={styles.iconBtn}
              onClick={toggleFullscreen}
              title={isFullscreen ? "Salir de pantalla completa" : "Pantalla completa"}
              aria-label="Alternar pantalla completa"
            >
              {isFullscreen ? <Minimize2 size={15} /> : <Maximize2 size={15} />}
              {!isCollapsed && <span>{isFullscreen ? "Reducir" : "Expandir"}</span>}
            </button>
          </div>

          {/* Enlace para volver a WordPress */}
          <a
            href={wpAdminUrl}
            className={styles.backToWpBtn}
            title="Regresar al panel tradicional de WordPress"
          >
            <ArrowLeft size={14} />
            {!isCollapsed && <span>Volver a WordPress</span>}
          </a>
        </div>
      </aside>

      {/* =========================================================
          CONTENIDO PRINCIPAL
          ========================================================= */}
      <div className={styles.mainArea}>
        {/* Cabecera superior del contenido */}
        <header className={styles.mainHeader}>
          <div className={styles.headerBreadcrumb}>
            <span>Crezca</span>
            <ChevronRight size={14} />
            <span className={styles.headerTitle}>{currentNav.label}</span>
          </div>

          <div className={styles.headerActions}>
            {/* Quick status pill */}
            <span className={styles.versionBadge}>Arc Design System</span>
          </div>
        </header>

        {/* Cuerpo del módulo activo */}
        <main className={styles.mainBody}>
          {activeTab === "overview" && <OverviewView onNavigate={(tab) => setActiveTab(tab as TabId)} />}
          {activeTab === "courses" && <CoursesView />}
          {activeTab === "students" && <StudentsView />}
          {activeTab === "finance" && <FinanceView />}
          {activeTab === "marketing" && <MarketingView />}
          {activeTab === "settings" && (
            <SettingsView
              currentAccent={accent}
              onAccentChange={handleAccentChange}
              currentTheme={theme}
              onThemeToggle={toggleTheme}
              academyName={academyName}
              onAcademyNameChange={handleAcademyNameChange}
              academyLogo={academyLogo}
              onAcademyLogoChange={handleAcademyLogoChange}
            />
          )}
        </main>
      </div>
    </div>
  );
}

export default App;
