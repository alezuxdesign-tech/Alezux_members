import React, { useState, useEffect, useRef } from "react";
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
  ShieldCheck,
  Award,
  LogOut
} from "lucide-react";
import { OverviewView } from "./views/OverviewView";
import { CoursesView } from "./views/CoursesView";
import { StudentsView } from "./views/StudentsView";
import { FinanceView } from "./views/FinanceView";
import { MarketingView } from "./views/MarketingView";
import { SettingsView } from "./views/SettingsView";
import { StudentDashboardView } from "./views/student/StudentDashboardView";
import { StudentCommunityView } from "./views/student/StudentCommunityView";
import { StudentCatalogView } from "./views/student/StudentCatalogView";
import { StudentCourseNetflixView } from "./views/student/StudentCourseNetflixView";
import { StudentLessonClassroomView } from "./views/student/StudentLessonClassroomView";
import { api, Course, CourseSection, CourseLesson, CourseTopic, StudentProfile } from "./services/api";
import { hexToRgb, getContrastForeground } from "./components/arc/color-picker/color-utils";
import styles from "./App.module.css";
import studentStyles from "./views/student/StudentPortal.module.css";

type AdminTabId = "overview" | "courses" | "students" | "finance" | "marketing" | "settings";
type StudentTabId = "dashboard" | "courses" | "achievements" | "community";

interface NavItemConfig<T extends string = string> {
  id: T;
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

export interface AppProps {
  initialMode?: "admin" | "student";
  isStandaloneStudent?: boolean;
}

const defaultStudentProfile: StudentProfile = {
  id: 1,
  name: "Estudiante",
  email: "",
  username: "estudiante",
  avatar: "",
  joinedDate: "Hoy",
  planName: "Plan Activo",
  enrolledCourseIds: [],
  completedTopicIds: [],
  isAdmin: false,
};

export function App({ initialMode = "admin", isStandaloneStudent = false }: AppProps = {}) {
  const wpData = (window as any).crezca_admin_data || (window as any).alezux_admin_data || {};
  const mainAreaRef = useRef<HTMLDivElement | null>(null);

  const [appMode, setAppMode] = useState<"admin" | "student">(initialMode);
  const [activeAdminTab, setActiveAdminTab] = useState<AdminTabId>("overview");
  const [activeStudentTab, setActiveStudentTab] = useState<StudentTabId>("dashboard");

  // Estado del Estudiante
  const [studentProfile, setStudentProfile] = useState<StudentProfile | null>(null);
  const [studentCourses, setStudentCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [selectedSection, setSelectedSection] = useState<CourseSection | null>(null);
  const [selectedLesson, setSelectedLesson] = useState<CourseLesson | null>(null);
  const [selectedTopic, setSelectedTopic] = useState<CourseTopic | null>(null);
  const [studentLoading, setStudentLoading] = useState(false);

  // Navegación para el Administrador
  const adminNavItems: NavItemConfig<AdminTabId>[] = [
    { id: "overview", label: "Dashboard", icon: <BarChart3 size={18} /> },
    { id: "courses", label: "Cursos", icon: <GraduationCap size={18} /> },
    { id: "students", label: "Estudiantes", icon: <Users size={18} /> },
    { id: "finance", label: "Finanzas", icon: <CreditCard size={18} /> },
    { id: "marketing", label: "Marketing", icon: <Mail size={18} /> },
    { id: "settings", label: "Configuración", icon: <Sliders size={18} /> },
  ];

  // Navegación para el Estudiante (Dashboard, Cursos, Logros, Comunidad)
  const studentNavItems: NavItemConfig<StudentTabId>[] = [
    { id: "dashboard", label: "Dashboard", icon: <BarChart3 size={18} /> },
    { id: "courses", label: "Cursos", icon: <GraduationCap size={18} /> },
    { id: "achievements", label: "Logros", icon: <Award size={18} /> },
    { id: "community", label: "Comunidad", icon: <Users size={18} /> },
  ];

  const currentAdminNav = adminNavItems.find((item) => item.id === activeAdminTab) || adminNavItems[0];
  const currentStudentNav = studentNavItems.find((item) => item.id === activeStudentTab) || studentNavItems[0];

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
    const currentLabel = appMode === "admin" ? currentAdminNav.label : currentStudentNav.label;
    document.title = `${titleName} | ${currentLabel}`;
  }, [academyName, appMode, currentAdminNav, currentStudentNav]);

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

  // Cargar datos de Estudiante cuando está en modo estudiante
  useEffect(() => {
    if (appMode === "student" && !studentProfile) {
      setStudentLoading(true);
      Promise.all([api.getStudentProfile(), api.getStudentCourses()])
        .then(([prof, crs]) => {
          setStudentProfile(prof);
          setStudentCourses(crs);
        })
        .catch((err) => console.error("Error cargando datos de estudiante:", err))
        .finally(() => setStudentLoading(false));
    }
  }, [appMode, studentProfile]);

  // Scroll al inicio al cambiar de pestaña o modo
  useEffect(() => {
    if (mainAreaRef.current) {
      mainAreaRef.current.scrollTo({ top: 0, behavior: "smooth" });
    }
  }, [appMode, activeAdminTab, activeStudentTab]);

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

  // Manejar marcar / desmarcar topic como completado por el estudiante
  const handleToggleCompleteTopic = async (topicId: string) => {
    try {
      const res = await api.toggleCompleteTopic(topicId, selectedCourse?.id);
      if (studentProfile) {
        setStudentProfile({
          ...studentProfile,
          completedTopicIds: res.completedTopicIds,
        });
      }
      if (selectedCourse) {
        setStudentCourses((prev) =>
          prev.map((c) => {
            if (c.id !== selectedCourse.id) return c;
            let total = 0;
            let done = 0;
            c.sections?.forEach((s) => {
              const items = s.lessons || s.topics || [];
              items.forEach((top) => {
                total++;
                if (res.completedTopicIds.includes(top.id)) {
                  done++;
                }
              });
            });
            const progress = total > 0 ? Math.round((done / total) * 100) : 0;
            const updated = { ...c, progress, completedTopics: done, totalTopics: total };
            setSelectedCourse(updated);
            return updated;
          })
        );
      }
    } catch (err) {
      console.error("Error al actualizar estado del topic:", err);
    }
  };

  const handleSelectCourse = (course: Course) => {
    setSelectedCourse(course);
    setSelectedSection(null);
    setSelectedLesson(null);
    setSelectedTopic(null);
    setActiveStudentTab("courses");
    if (mainAreaRef.current) {
      mainAreaRef.current.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const handleSelectModule = (sec: CourseSection, les: CourseLesson, top?: CourseTopic) => {
    setSelectedSection(sec);
    setSelectedLesson(les);
    setSelectedTopic(top || null);
    if (mainAreaRef.current) {
      mainAreaRef.current.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const handleBackToCatalog = () => {
    setSelectedCourse(null);
    setSelectedSection(null);
    setSelectedLesson(null);
    setSelectedTopic(null);
    if (mainAreaRef.current) {
      mainAreaRef.current.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const handleBackToCourse = () => {
    setSelectedSection(null);
    setSelectedLesson(null);
    setSelectedTopic(null);
    if (mainAreaRef.current) {
      mainAreaRef.current.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const wpAdminUrl = (window as any).crezca_admin_data?.wp_admin_url 
    || (window as any).alezux_admin_data?.wp_admin_url 
    || "/wp-admin/";

  const logoutUrl = (window as any).crezca_student_data?.logout_url || "/wp-login.php?action=logout";

  // Determinar si la vista actual de cursos requiere pantalla completa sin padding
  const isCourseFluid = appMode === "student" && activeStudentTab === "courses" && Boolean(selectedCourse);

  return (
    <div className={styles.appShell}>
      {/* =========================================================
          SIDEBAR VERTICAL ESTILO ARC UI (COMÚN A ADMIN Y ESTUDIANTE)
          ========================================================= */}
      <aside className={`${styles.sidebar} ${isCollapsed ? styles.collapsed : ""}`}>
        {/* Cabecera del Sidebar */}
        <div className={styles.sidebarHeader}>
          {!isCollapsed ? (
            <>
              <div 
                className={styles.brandGroup} 
                title={`${academyName || "Crezca"} - ${appMode === "admin" ? "Academia & Membresías" : "Campus del Estudiante"}`}
              >
                <div className={`${styles.logoMark} ${academyLogo ? styles.hasCustomLogo : ""}`}>
                  {academyLogo ? (
                    <img src={academyLogo} alt={academyName || "Logo"} className={styles.logoImg} />
                  ) : (
                    appMode === "admin" ? <Layers size={19} className={styles.logoIcon} /> : <GraduationCap size={19} className={styles.logoIcon} />
                  )}
                </div>
                <div className={styles.brandText}>
                  <div className={styles.brandTitleWrap}>
                    <span className={styles.brandName}>{academyName || "Crezca"}</span>
                    <span className={styles.versionBadge}>v2.0 Arc</span>
                  </div>
                  <span className={styles.brandSubtitle}>
                    {appMode === "admin" ? "Academia & Membresías" : "Campus del Estudiante"}
                  </span>
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
                  appMode === "admin" ? <Layers size={19} className={styles.logoIcon} /> : <GraduationCap size={19} className={styles.logoIcon} />
                )}
              </div>
            </button>
          )}
        </div>

        {/* Lista de Navegación Vertical */}
        <nav className={styles.navSection}>
          {!isCollapsed && (
            <div className={styles.navSectionLabel}>
              {appMode === "admin" ? "Plataforma" : "Campus"}
            </div>
          )}

          {/* Menú para Administrador */}
          {appMode === "admin" && adminNavItems.map((item) => {
            const isActive = activeAdminTab === item.id;
            return (
              <button
                key={item.id}
                type="button"
                className={`${styles.navItem} ${isActive ? styles.active : ""}`}
                onClick={() => setActiveAdminTab(item.id)}
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

          {/* Menú para Estudiante (Dashboard, Cursos, Logros, Comunidad) */}
          {appMode === "student" && studentNavItems.map((item) => {
            const isActive = activeStudentTab === item.id;
            return (
              <button
                key={item.id}
                type="button"
                className={`${styles.navItem} ${isActive ? styles.active : ""}`}
                onClick={() => {
                  setActiveStudentTab(item.id);
                  if (item.id === "courses" && !selectedCourse) {
                    // Mantener catálogo
                  }
                }}
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
                {appMode === "admin" ? (
                  <ShieldCheck size={15} />
                ) : studentProfile?.avatar ? (
                  <img src={studentProfile.avatar} alt="Avatar" className={styles.userAvatarImg} />
                ) : (
                  <GraduationCap size={15} />
                )}
              </div>
              <div className={styles.userInfo}>
                <span className={styles.userName}>
                  {appMode === "admin" ? "Administrador" : (studentProfile?.name || "Estudiante")}
                </span>
                <span className={styles.userStatus}>
                  <span className={styles.statusDot} />
                  {appMode === "admin" ? "Sesión activa" : "Estudiante activo"}
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

          {/* Acción inferior: Volver a WordPress o Alternar Modo */}
          {appMode === "admin" ? (
            <a
              href={wpAdminUrl}
              className={styles.backToWpBtn}
              title="Regresar al panel tradicional de WordPress"
            >
              <ArrowLeft size={14} />
              {!isCollapsed && <span>Volver a WordPress</span>}
            </a>
          ) : (
            !isStandaloneStudent ? (
              <button
                type="button"
                onClick={() => setAppMode("admin")}
                className={styles.backToWpBtn}
                title="Regresar a la administración"
              >
                <ShieldCheck size={14} />
                {!isCollapsed && <span>Modo Administrador</span>}
              </button>
            ) : (
              <a
                href={logoutUrl}
                className={styles.backToWpBtn}
                title="Cerrar sesión del campus virtual"
              >
                <LogOut size={14} />
                {!isCollapsed && <span>Cerrar sesión</span>}
              </a>
            )
          )}
        </div>
      </aside>

      {/* =========================================================
          CONTENIDO PRINCIPAL CON SCROLL FLUIDO
          ========================================================= */}
      <div 
        ref={mainAreaRef} 
        data-scroll-container="main" 
        className={styles.mainArea}
      >
        {/* Cabecera superior del contenido */}
        <header className={styles.mainHeader}>
          <div className={styles.headerBreadcrumb}>
            <span>{academyName || "Crezca"}</span>
            <ChevronRight size={14} />

            {appMode === "admin" ? (
              <span className={styles.headerTitle}>{currentAdminNav.label}</span>
            ) : (
              <>
                {activeStudentTab === "dashboard" && <span className={styles.headerTitle}>Dashboard</span>}
                {activeStudentTab === "achievements" && <span className={styles.headerTitle}>Logros</span>}
                {activeStudentTab === "community" && <span className={styles.headerTitle}>Comunidad</span>}
                {activeStudentTab === "courses" && (
                  !selectedCourse ? (
                    <span className={styles.headerTitle}>Cursos</span>
                  ) : !selectedLesson ? (
                    <>
                      <span 
                        style={{ cursor: "pointer", color: "var(--text-muted)" }} 
                        onClick={handleBackToCatalog}
                      >
                        Cursos
                      </span>
                      <ChevronRight size={14} />
                      <span className={styles.headerTitle}>{selectedCourse.title}</span>
                    </>
                  ) : (
                    <>
                      <span 
                        style={{ cursor: "pointer", color: "var(--text-muted)" }} 
                        onClick={handleBackToCourse}
                      >
                        {selectedCourse.title}
                      </span>
                      <ChevronRight size={14} />
                      <span className={styles.headerTitle}>{selectedLesson.title}</span>
                    </>
                  )
                )}
              </>
            )}
          </div>

          <div className={styles.headerActions}>
            {appMode === "admin" ? (
              <button
                type="button"
                className={styles.studentModeBtn}
                onClick={() => setAppMode("student")}
                title="Previsualizar el campus virtual como un estudiante"
              >
                <GraduationCap size={15} />
                <span>Modo Estudiante</span>
              </button>
            ) : (
              !isStandaloneStudent ? (
                <button
                  type="button"
                  className={styles.studentModeBtn}
                  onClick={() => setAppMode("admin")}
                  title="Regresar a la administración"
                >
                  <ShieldCheck size={15} />
                  <span>Modo Administrador</span>
                </button>
              ) : (
                <div className={styles.studentHeaderBadge}>
                  <ShieldCheck size={14} />
                  <span>{studentProfile?.planName || "Membresía Activa"}</span>
                </div>
              )
            )}

            <span className={styles.versionBadge}>Arc Design System</span>
          </div>
        </header>

        {/* Cuerpo del módulo activo */}
        <main className={`${styles.mainBody} ${isCourseFluid ? styles.mainBodyNoPadding : ""}`}>
          {/* MODO ADMINISTRADOR */}
          {appMode === "admin" && (
            <>
              {activeAdminTab === "overview" && <OverviewView onNavigate={(tab) => setActiveAdminTab(tab as AdminTabId)} />}
              {activeAdminTab === "courses" && <CoursesView />}
              {activeAdminTab === "students" && <StudentsView />}
              {activeAdminTab === "finance" && <FinanceView />}
              {activeAdminTab === "marketing" && <MarketingView />}
              {activeAdminTab === "settings" && (
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
            </>
          )}

          {/* MODO ESTUDIANTE */}
          {appMode === "student" && (
            <>
              {/* 1. Dashboard del Estudiante */}
              {activeStudentTab === "dashboard" && (
                <StudentDashboardView
                  profile={studentProfile || defaultStudentProfile}
                  courses={studentCourses}
                  onSelectCourse={handleSelectCourse}
                  onNavigateTab={(tab) => setActiveStudentTab(tab)}
                />
              )}

              {/* 2. Cursos del Estudiante (Catálogo -> Netflix -> Aula) */}
              {activeStudentTab === "courses" && (
                <>
                  {!selectedCourse && (
                    <StudentCatalogView
                      courses={studentCourses}
                      profile={studentProfile || defaultStudentProfile}
                      onSelectCourse={handleSelectCourse}
                      onOpenProfile={() => {}}
                    />
                  )}

                  {selectedCourse && !selectedLesson && (
                    <StudentCourseNetflixView
                      course={selectedCourse}
                      academyName={academyName}
                      academyLogo={academyLogo}
                      onBack={handleBackToCatalog}
                      onSelectModule={handleSelectModule}
                    />
                  )}

                  {selectedCourse && selectedSection && selectedLesson && (
                    <StudentLessonClassroomView
                      course={selectedCourse}
                      currentSection={selectedSection}
                      currentLesson={selectedLesson}
                      initialTopic={selectedTopic || undefined}
                      completedTopicIds={studentProfile?.completedTopicIds || []}
                      onToggleCompleteTopic={handleToggleCompleteTopic}
                      onBackToCourse={handleBackToCourse}
                      onSwitchModule={(sec, les, top) => {
                        setSelectedSection(sec);
                        setSelectedLesson(les);
                        setSelectedTopic(top || null);
                      }}
                    />
                  )}
                </>
              )}

              {/* 3. Logros del Estudiante (Requerimiento explícito: Dejar la vista en blanco por ahora) */}
              {activeStudentTab === "achievements" && (
                <div className={studentStyles.blankAchievementsView} />
              )}

              {/* 4. Comunidad del Estudiante */}
              {activeStudentTab === "community" && (
                <StudentCommunityView
                  courses={studentCourses}
                  academyName={academyName}
                />
              )}
            </>
          )}
        </main>
      </div>
    </div>
  );
}

export default App;
