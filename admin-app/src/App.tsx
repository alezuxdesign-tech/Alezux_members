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
import { hexToRgb, getContrastForeground } from "./components/arc/color-picker/color-utils";
import styles from "./App.module.css";

type TabId = "overview" | "courses" | "students" | "finance" | "marketing" | "settings";

interface NavItemConfig {
  id: TabId;
  label: string;
  icon: React.ReactNode;
  badge?: string;
}

function applyAccentColor(accent: string, theme: "dark" | "light") {
  const root = document.documentElement;
  const presets: Record<string, { hex: string; fg: string }> = {
    violet: { hex: "#7747ff", fg: "#ffffff" },
    blue: { hex: "#0562ef", fg: "#ffffff" },
    green: { hex: "#0db879", fg: "#ffffff" },
    amber: { hex: "#f3ad20", fg: "#090a0f" },
    coral: { hex: "#f15f55", fg: "#ffffff" },
    neutral: { hex: "#71717a", fg: "#ffffff" },
  };

  if (presets[accent]) {
    root.dataset.accent = accent;
    root.style.removeProperty("--accent");
    root.style.removeProperty("--accent-strong");
    root.style.removeProperty("--accent-subtle");
    root.style.removeProperty("--control-on");
    root.style.removeProperty("--control-fill");
    root.style.setProperty("--accent-foreground", presets[accent].fg);
    root.style.setProperty("--control-glyph", presets[accent].fg);
  } else {
    // Es un color hexadecimal personalizado (ej: #C7F804 o #0db879)
    root.dataset.accent = "custom";
    const hex = accent.startsWith("#") ? accent : `#${accent}`;
    const { r, g, b } = hexToRgb(hex);
    const fg = getContrastForeground(hex);

    const subtleAlpha = theme === "dark" ? 0.22 : 0.13;
    const strongHex = theme === "dark"
      ? `color-mix(in srgb, ${hex} 82%, #ffffff)`
      : `color-mix(in srgb, ${hex} 82%, #000000)`;

    root.style.setProperty("--accent", hex);
    root.style.setProperty("--control-on", hex);
    root.style.setProperty("--control-fill", hex);
    root.style.setProperty("--accent-strong", strongHex);
    root.style.setProperty("--accent-subtle", `rgba(${r}, ${g}, ${b}, ${subtleAlpha})`);
    root.style.setProperty("--accent-foreground", fg);
    root.style.setProperty("--control-glyph", fg);
  }
}

function setBrowserFavicon(url: string) {
  if (!url) return;
  // Eliminar favicons anteriores de WP para asegurar que el navegador actualice de inmediato
  const existingLinks = document.querySelectorAll<HTMLLinkElement>("link[rel*='icon']");
  existingLinks.forEach((el) => el.remove());

  const linkShortcut = document.createElement("link");
  linkShortcut.rel = "shortcut icon";
  linkShortcut.href = url;

  const linkIcon = document.createElement("link");
  linkIcon.rel = "icon";
  linkIcon.href = url;

  document.head.appendChild(linkShortcut);
  document.head.appendChild(linkIcon);
}

export function App() {
  const wpData = (window as any).crezca_admin_data || (window as any).alezux_admin_data || {};

  const [activeTab, setActiveTab] = useState<TabId>("overview");

  const navItems: NavItemConfig[] = [
    { id: "overview", label: "Dashboard", icon: <BarChart3 size={18} /> },
    { id: "courses", label: "Cursos", icon: <GraduationCap size={18} /> },
    { id: "students", label: "Estudiantes", icon: <Users size={18} /> },
    { id: "finance", label: "Finanzas", icon: <CreditCard size={18} /> },
    { id: "marketing", label: "Marketing", icon: <Mail size={18} /> },
    { id: "settings", label: "Configuración", icon: <Sliders size={18} /> },
  ];

  const currentNav = navItems.find((item) => item.id === activeTab) || navItems[0];
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
    applyAccentColor(accent, theme);
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

  // Actualizar título y favicon de la pestaña del navegador
  useEffect(() => {
    const titleName = academyName || "Crezca";
    document.title = `${titleName} | ${currentNav.label}`;
  }, [academyName, currentNav]);

  useEffect(() => {
    if (academyLogo) {
      setBrowserFavicon(academyLogo);
    }
  }, [academyLogo]);

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
        setBrowserFavicon(s.academy_logo);
      }
      if (s.theme && !localStorage.getItem("crezca_theme")) {
        setTheme(s.theme);
      }
      if (s.accent && !localStorage.getItem("crezca_accent")) {
        setAccent(s.accent);
        applyAccentColor(s.accent, theme);
      }
    });
  }, []);

  const toggleTheme = () => {
    setTheme((prev) => {
      const next = prev === "dark" ? "light" : "dark";
      localStorage.setItem("crezca_theme", next);
      document.documentElement.dataset.theme = next;
      applyAccentColor(accent, next);
      api.savePlatformSettings({ theme: next });
      return next;
    });
  };

  const handleAccentChange = (newAccent: string) => {
    setAccent(newAccent);
    localStorage.setItem("crezca_accent", newAccent);
    applyAccentColor(newAccent, theme);
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
                <div className={`${styles.logoMark} ${academyLogo ? styles.hasCustomLogo : ""}`}>
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
              <div className={`${styles.logoMark} ${academyLogo ? styles.hasCustomLogo : ""}`}>
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
            <span>{academyName || "Crezca"}</span>
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
