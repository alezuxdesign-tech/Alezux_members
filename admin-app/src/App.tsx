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
  ShieldAlert,
  Lock,
  MessageCircle,
  Award,
  LogOut,
  MessageSquare,
  UserCheck,
  Calendar,
  Bell,
  User,
  Menu,
  X,
  Grid
} from "lucide-react";
import { OverviewView } from "./views/OverviewView";
import { CoursesView } from "./views/CoursesView";
import { StudentsView } from "./views/StudentsView";
import { InstructorsView } from "./views/InstructorsView";
import { EventsView } from "./views/EventsView";
import { FinanceView } from "./views/FinanceView";
import { MarketingView } from "./views/MarketingView";
import { SettingsView } from "./views/SettingsView";
import { StudentDashboardView } from "./views/student/StudentDashboardView";
import { StudentCommunityView } from "./views/student/StudentCommunityView";
import { StudentCatalogView } from "./views/student/StudentCatalogView";
import { StudentCourseNetflixView } from "./views/student/StudentCourseNetflixView";
import { StudentLessonClassroomView } from "./views/student/StudentLessonClassroomView";
import { StudentProfileView } from "./views/student/StudentProfileView";
import { NotificationsPopover } from "./components/NotificationsPopover";
import { api, Course, CourseSection, CourseLesson, CourseTopic, StudentProfile } from "./services/api";
import { hexToRgb, getContrastForeground } from "./components/arc/color-picker/color-utils";
import styles from "./App.module.css";
import studentStyles from "./views/student/StudentPortal.module.css";

export type AppMode = "admin" | "student" | "instructor";
export type InstructorTabId = "courses" | "students" | "community";
type AdminTabId = "overview" | "courses" | "students" | "instructors" | "events" | "finance" | "marketing" | "community" | "settings";
type StudentTabId = "dashboard" | "courses" | "community" | "profile";

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
  const wpStudentData = (window as any).crezca_student_data || {};
  const mainAreaRef = useRef<HTMLDivElement | null>(null);

  // Determinar pestaña inicial para el estudiante ('courses' o 'dashboard')
  const defaultStudentTab = ((): StudentTabId => {
    if (typeof window !== "undefined") {
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get("tab") || urlParams.get("view");
      if (tabParam === "courses" || tabParam === "dashboard" || tabParam === "community" || tabParam === "profile") {
        return tabParam as StudentTabId;
      }
    }
    const configuredTab = wpStudentData.initial_tab || wpData.student_default_view;
    if (configuredTab === "dashboard" || configuredTab === "courses") {
      return configuredTab as StudentTabId;
    }
    return "courses";
  })();

  const isActualInstructor = Boolean(
    wpData?.is_instructor ||
    wpStudentData?.is_instructor ||
    wpStudentData?.user_role === "instructor" ||
    (typeof window !== "undefined" && new URLSearchParams(window.location.search).get("mode") === "instructor")
  );

  const calculatedInitialMode: AppMode = (() => {
    if (typeof window !== "undefined") {
      const urlParams = new URLSearchParams(window.location.search);
      const modeParam = urlParams.get("mode");
      if (modeParam === "instructor") return "instructor";
      if (modeParam === "student") return "student";
      if (modeParam === "admin") return "admin";
    }
    if (isActualInstructor && !wpData?.is_admin && !wpStudentData?.is_admin) {
      return "instructor";
    }
    return initialMode;
  })();

  const [appMode, setAppMode] = useState<AppMode>(calculatedInitialMode);
  const [activeAdminTab, setActiveAdminTab] = useState<AdminTabId>("overview");
  const [activeStudentTab, setActiveStudentTab] = useState<StudentTabId>(defaultStudentTab);

  // Estado del Estudiante
  const [studentProfile, setStudentProfile] = useState<StudentProfile | null>(null);
  const [studentCourses, setStudentCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [selectedSection, setSelectedSection] = useState<CourseSection | null>(null);
  const [selectedLesson, setSelectedLesson] = useState<CourseLesson | null>(null);
  const [selectedTopic, setSelectedTopic] = useState<CourseTopic | null>(null);
  const [studentLoading, setStudentLoading] = useState(false);

  // Detección de Cuenta de Estudiante Bloqueada
  const isStudentBlocked =
    appMode === "student" &&
    (Boolean(wpStudentData?.is_blocked) ||
      Boolean(studentProfile?.isBlocked) ||
      studentProfile?.status === "blocked") &&
    !studentProfile?.isAdmin &&
    !wpStudentData?.is_admin;

  const supportWhatsapp =
    wpStudentData?.support_whatsapp ||
    (window as any).crezca_admin_data?.support_whatsapp ||
    "";
  const supportEmail =
    wpStudentData?.support_email ||
    (window as any).crezca_admin_data?.support_email ||
    "administracion@alezux.com";
  const cleanWhatsapp = supportWhatsapp ? String(supportWhatsapp).replace(/[^0-9]/g, "") : "";

  const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
  const [unreadNotificationsCount, setUnreadNotificationsCount] = useState<number>(() => {
    try {
      const stored = localStorage.getItem("crezca_notifications");
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed)) {
          return parsed.filter((n: any) => !n.read).length;
        }
      }
    } catch (e) {}
    return 3;
  });

  // Navegación para el Administrador
  const adminNavItems: NavItemConfig<AdminTabId>[] = [
    { id: "overview", label: "Dashboard", icon: <BarChart3 size={18} /> },
    { id: "courses", label: "Cursos", icon: <GraduationCap size={18} /> },
    { id: "students", label: "Estudiantes", icon: <Users size={18} /> },
    { id: "instructors", label: "Docentes", icon: <UserCheck size={18} /> },
    { id: "events", label: "Eventos", icon: <Calendar size={18} /> },
    { id: "finance", label: "Finanzas", icon: <CreditCard size={18} /> },
    { id: "marketing", label: "Marketing", icon: <Mail size={18} /> },
    { id: "community", label: "Comunidad", icon: <MessageSquare size={18} /> },
    { id: "settings", label: "Configuración", icon: <Sliders size={18} /> },
  ];

  // Navegación para el Estudiante (Dashboard, Cursos, Comunidad)
  const studentNavItems: NavItemConfig<StudentTabId>[] = [
    { id: "dashboard", label: "Dashboard", icon: <BarChart3 size={18} /> },
    { id: "courses", label: "Cursos", icon: <GraduationCap size={18} /> },
    { id: "community", label: "Comunidad", icon: <Users size={18} /> },
  ];

  // Navegación para el Docente / Profesor
  const instructorNavItems: NavItemConfig<InstructorTabId>[] = [
    { id: "courses", label: "Mis Cursos", icon: <GraduationCap size={18} /> },
    { id: "students", label: "Mis Alumnos", icon: <Users size={18} /> },
    { id: "community", label: "Comunidad", icon: <MessageSquare size={18} /> },
  ];

  const [activeInstructorTab, setActiveInstructorTab] = useState<InstructorTabId>("courses");

  const currentAdminNav = adminNavItems.find((item) => item.id === activeAdminTab) || adminNavItems[0];
  const currentStudentNav = studentNavItems.find((item) => item.id === activeStudentTab) || studentNavItems[0];
  const currentInstructorNav = instructorNavItems.find((item) => item.id === activeInstructorTab) || instructorNavItems[0];

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
  const [isMobile, setIsMobile] = useState<boolean>(() => {
    return typeof window !== "undefined" ? window.innerWidth <= 768 : false;
  });
  const [isTablet, setIsTablet] = useState<boolean>(() => {
    return typeof window !== "undefined" ? window.innerWidth > 768 && window.innerWidth <= 1024 : false;
  });
  const [isDrawerOpen, setIsDrawerOpen] = useState(false);

  useEffect(() => {
    const handleResize = () => {
      const width = window.innerWidth;
      const mobile = width <= 768;
      const tablet = width > 768 && width <= 1024;
      setIsMobile(mobile);
      setIsTablet(tablet);
      if (tablet) {
        setIsCollapsed(true);
      }
      if (!mobile && isDrawerOpen) {
        setIsDrawerOpen(false);
      }
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, [isDrawerOpen]);

  useEffect(() => {
    document.documentElement.dataset.theme = theme;
    applyAccentColor(accent, theme);
    localStorage.setItem("crezca_theme", theme);
    localStorage.setItem("crezca_accent", accent);

    const isTransparent = localStorage.getItem("crezca_transparent_sidebar") === "true";
    document.body.classList.toggle("transparent-sidebar", isTransparent);

    const tableDensity = localStorage.getItem("crezca_table_density");
    if (tableDensity) {
      document.body.dataset.tableDensity = tableDensity;
    }

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
    const currentLabel = appMode === "admin"
      ? currentAdminNav.label
      : (appMode === "instructor" ? currentInstructorNav.label : currentStudentNav.label);
    document.title = `${titleName} | ${currentLabel}`;
  }, [academyName, appMode, currentAdminNav, currentStudentNav, currentInstructorNav]);

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

  // Cargar datos de Estudiante cuando está en modo estudiante (sincronización en tiempo real)
  useEffect(() => {
    if (appMode === "student") {
      setStudentLoading(true);
      Promise.all([api.getStudentProfile(), api.getStudentCourses()])
        .then(([prof, crs]) => {
          setStudentProfile(prof);
          setStudentCourses(crs);
          setSelectedCourse((prev) => {
            if (!prev) return null;
            const updated = crs.find((c) => c.id === prev.id);
            return updated || prev;
          });
        })
        .catch((err) => console.error("Error cargando datos de estudiante:", err))
        .finally(() => setStudentLoading(false));
    }
  }, [appMode]);

  // Scroll al inicio y cerrar notificaciones al cambiar de pestaña o modo
  useEffect(() => {
    setIsNotificationsOpen(false);
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

  const handleResumeLesson = (course: Course, sec?: CourseSection | null, les?: CourseLesson | null, top?: CourseTopic | null) => {
    setSelectedCourse(course);
    setSelectedSection(sec || null);
    setSelectedLesson(les || null);
    setSelectedTopic(top || null);
    setActiveStudentTab("courses");
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

  const logoutUrl = (window as any).crezca_student_data?.logout_url || "/?crezca_logout=1";

  // Determinar inequívocamente si el usuario actual es administrador
  const isActualAdmin = Boolean(
    wpData?.is_admin ||
    wpStudentData?.is_admin ||
    studentProfile?.isAdmin ||
    (!isStandaloneStudent && initialMode === "admin")
  );

  // Determinar si la vista actual requiere pantalla completa sin padding
  const isCourseFluid = appMode === "student" && activeStudentTab === "courses" && Boolean(selectedCourse);
  const isCommunityFluid = (appMode === "student" && activeStudentTab === "community") || 
    (appMode === "admin" && activeAdminTab === "community") ||
    (appMode === "instructor" && activeInstructorTab === "community");
  const isFluidMain = isCourseFluid || isCommunityFluid;

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
                title={`${academyName || "Crezca"} - ${appMode === "admin" ? "Academia & Membresías" : (appMode === "instructor" ? "Panel del Docente" : "Campus del Estudiante")}`}
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
              <div 
                className={`${styles.logoMark} ${academyLogo ? styles.hasCustomLogo : ""}`}
                title={academyName || "Crezca"}
              >
                {academyLogo ? (
                  <img src={academyLogo} alt={academyName || "Logo"} className={styles.logoImg} />
                ) : (
                  appMode === "admin" ? <Layers size={18} className={styles.logoIcon} /> : <GraduationCap size={18} className={styles.logoIcon} />
                )}
              </div>
            </button>
          )}
        </div>

        {/* Lista de Navegación Vertical */}
        <nav className={styles.navSection}>
          {!isCollapsed && (
            <div className={styles.navSectionLabel}>
              {appMode === "admin" ? "Plataforma" : (appMode === "instructor" ? "Panel Docente" : "Campus")}
            </div>
          )}

          {/* Menú para Docente / Instructor */}
          {appMode === "instructor" && instructorNavItems.map((item) => {
            const isActive = activeInstructorTab === item.id;
            return (
              <button
                key={item.id}
                type="button"
                className={`${styles.navItem} ${isActive ? styles.active : ""}`}
                onClick={() => setActiveInstructorTab(item.id)}
                title={isCollapsed ? item.label : undefined}
              >
                {isActive && <span className={styles.activeIndicator} />}
                <span className={styles.navIconWrap}>{item.icon}</span>
                {!isCollapsed && <span className={styles.navLabel}>{item.label}</span>}
              </button>
            );
          })}

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
                ) : appMode === "instructor" ? (
                  <UserCheck size={15} />
                ) : studentProfile?.avatar ? (
                  <img src={studentProfile.avatar} alt="Avatar" className={styles.userAvatarImg} />
                ) : (
                  <GraduationCap size={15} />
                )}
              </div>
              <div className={styles.userInfo}>
                <span className={styles.userName}>
                  {appMode === "admin" ? "Administrador" : (appMode === "instructor" ? (studentProfile?.name || "Docente Titular") : (studentProfile?.name || "Estudiante"))}
                </span>
                <span className={styles.userStatus}>
                  <span className={styles.statusDot} />
                  {appMode === "admin" ? "Sesión activa" : (appMode === "instructor" ? "Docente activo" : "Estudiante activo")}
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

          {/* Acción inferior: Volver a WordPress o Alternar Modo - EXCLUSIVO PARA ADMINISTRADORES */}
          {isActualAdmin ? (
            appMode === "admin" ? (
              <a
                href={wpAdminUrl}
                className={styles.backToWpBtn}
                title="Regresar al panel tradicional de WordPress"
              >
                <ArrowLeft size={14} />
                {!isCollapsed && <span>Volver a WordPress</span>}
              </a>
            ) : (
              <button
                type="button"
                onClick={() => setAppMode("admin")}
                className={styles.backToWpBtn}
                title="Regresar a la administración"
              >
                <ShieldCheck size={14} />
                {!isCollapsed && <span>Modo Administrador</span>}
              </button>
            )
          ) : (
            <a
              href={logoutUrl}
              className={styles.backToWpBtn}
              title="Cerrar sesión del campus virtual"
            >
              <LogOut size={14} />
              {!isCollapsed && <span>Cerrar sesión</span>}
            </a>
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
        {/* =========================================================
            HEADER MÓVIL NATIVO (Sticky 56px para Smartphones)
            ========================================================= */}
        <header className={styles.mobileHeader}>
          <div className={styles.mobileHeaderLeft}>
            {appMode === "student" && selectedCourse ? (
              !selectedLesson ? (
                <button 
                  type="button" 
                  className={styles.mobileBackBtn} 
                  onClick={handleBackToCatalog}
                  aria-label="Volver a cursos"
                >
                  <ArrowLeft size={18} />
                  <span className={styles.mobileBackBtnLabel}>Cursos</span>
                </button>
              ) : (
                <button 
                  type="button" 
                  className={styles.mobileBackBtn} 
                  onClick={handleBackToCourse}
                  aria-label={`Volver a ${selectedCourse.title}`}
                >
                  <ArrowLeft size={18} />
                  <span className={styles.mobileBackBtnLabel}>{selectedCourse.title}</span>
                </button>
              )
            ) : (
              <div className={styles.mobileHeaderLogoWrap}>
                {academyLogo ? (
                  <img src={academyLogo} alt={academyName || "Logo"} className={styles.mobileHeaderLogoImg} />
                ) : (
                  <div className={styles.mobileHeaderLogoIcon}>
                    {appMode === "admin" ? <Layers size={16} /> : <GraduationCap size={16} />}
                  </div>
                )}
                <span className={styles.mobileHeaderName}>{academyName || "Crezca"}</span>
              </div>
            )}
          </div>

          <div className={styles.mobileHeaderRight}>
            {/* Si es Admin, botón rápido para alternar modo */}
            {isActualAdmin && (
              <button
                type="button"
                className={styles.headerIconButton}
                onClick={() => setAppMode(appMode === "admin" ? "student" : "admin")}
                title={appMode === "admin" ? "Previsualizar Modo Estudiante" : "Regresar a Modo Administrador"}
                aria-label="Alternar rol"
              >
                {appMode === "admin" ? <GraduationCap size={16} /> : <ShieldCheck size={16} />}
              </button>
            )}

            {/* Notificaciones Globales Mobile */}
            <div className={styles.headerNotificationWrapper}>
              <button
                type="button"
                className={styles.headerIconButton}
                onClick={() => {
                  setIsNotificationsOpen((prev) => !prev);
                  try {
                    const stored = localStorage.getItem("crezca_notifications");
                    if (stored) {
                      const parsed = JSON.parse(stored);
                      setUnreadNotificationsCount(parsed.filter((n: any) => !n.read).length);
                    }
                  } catch (e) {}
                }}
                title="Notificaciones"
                aria-label="Ver notificaciones"
              >
                <Bell size={16} />
                {unreadNotificationsCount > 0 && (
                  <span className={styles.headerNotificationBadge}>
                    {unreadNotificationsCount > 9 ? "9+" : unreadNotificationsCount}
                  </span>
                )}
              </button>
            </div>

            {/* Perfil de Usuario */}
            <button
              type="button"
              className={styles.headerProfileBtn}
              onClick={() => {
                if (appMode === "student") {
                  setActiveStudentTab("profile");
                } else {
                  setActiveAdminTab("settings");
                }
              }}
              title={appMode === "student" ? "Mi Perfil" : "Ajustes"}
              aria-label="Perfil de usuario"
            >
              <div className={styles.headerAvatarCircle}>
                {appMode === "student" && studentProfile?.avatar ? (
                  <img src={studentProfile.avatar} alt="Avatar" className={styles.headerAvatarImg} />
                ) : (
                  <User size={14} />
                )}
              </div>
            </button>
          </div>
        </header>

        {/* Cabecera superior del contenido (Desktop & Tablet) */}
        <header className={styles.mainHeader}>
          <div className={styles.headerBreadcrumb}>
            {isCollapsed && (
              <button
                type="button"
                className={styles.topbarToggleBtn}
                onClick={toggleSidebar}
                title="Expandir barra lateral"
                aria-label="Expandir barra lateral"
              >
                <PanelLeftOpen size={16} />
              </button>
            )}
            <span>{academyName || "Crezca"}</span>
            <ChevronRight size={14} />

            {appMode === "admin" ? (
              <span className={styles.headerTitle}>{currentAdminNav.label}</span>
            ) : (
              <>
                {activeStudentTab === "dashboard" && <span className={styles.headerTitle}>Dashboard</span>}
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
            {isActualAdmin ? (
              <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                <button
                  type="button"
                  className={`${styles.studentModeBtn} ${appMode === "admin" ? styles.active : ""}`}
                  onClick={() => setAppMode("admin")}
                  title="Panel de Administrador"
                >
                  <ShieldCheck size={14} />
                  <span>Admin</span>
                </button>
                <button
                  type="button"
                  className={`${styles.studentModeBtn} ${appMode === "instructor" ? styles.active : ""}`}
                  onClick={() => setAppMode("instructor")}
                  title="Previsualizar Modo Docente"
                >
                  <UserCheck size={14} />
                  <span>Docente</span>
                </button>
                <button
                  type="button"
                  className={`${styles.studentModeBtn} ${appMode === "student" ? styles.active : ""}`}
                  onClick={() => setAppMode("student")}
                  title="Previsualizar Modo Estudiante"
                >
                  <GraduationCap size={14} />
                  <span>Estudiante</span>
                </button>
              </div>
            ) : appMode === "instructor" ? (
              <div className={styles.studentHeaderBadge}>
                <UserCheck size={14} />
                <span>Docente Titular</span>
              </div>
            ) : (
              <div className={styles.studentHeaderBadge}>
                <ShieldCheck size={14} />
                <span>{studentProfile?.planName || "Membresía Activa"}</span>
              </div>
            )}

            {/* Notificaciones Globales */}
            <div className={styles.headerNotificationWrapper}>
              <button
                type="button"
                className={styles.headerIconButton}
                onClick={() => {
                  setIsNotificationsOpen((prev) => !prev);
                  try {
                    const stored = localStorage.getItem("crezca_notifications");
                    if (stored) {
                      const parsed = JSON.parse(stored);
                      setUnreadNotificationsCount(parsed.filter((n: any) => !n.read).length);
                    }
                  } catch (e) {}
                }}
                title="Notificaciones"
                aria-label="Ver notificaciones"
              >
                <Bell size={16} />
                {unreadNotificationsCount > 0 && (
                  <span className={styles.headerNotificationBadge}>
                    {unreadNotificationsCount > 9 ? "9+" : unreadNotificationsCount}
                  </span>
                )}
              </button>

              {!isMobile && (
                <NotificationsPopover
                  isOpen={isNotificationsOpen}
                  onClose={() => {
                    setIsNotificationsOpen(false);
                    try {
                      const stored = localStorage.getItem("crezca_notifications");
                      if (stored) {
                        const parsed = JSON.parse(stored);
                        setUnreadNotificationsCount(parsed.filter((n: any) => !n.read).length);
                      }
                    } catch (e) {}
                  }}
                  onNavigate={(tab) => {
                    setIsNotificationsOpen(false);
                    if (appMode === "admin") {
                      if (tab === "events" || tab === "courses" || tab === "students" || tab === "instructors" || tab === "community" || tab === "settings") {
                        setActiveAdminTab(tab as AdminTabId);
                      }
                    } else {
                      if (tab === "dashboard" || tab === "courses" || tab === "community" || tab === "profile") {
                        setActiveStudentTab(tab as StudentTabId);
                      }
                    }
                  }}
                  appMode={appMode}
                  onUpdateCount={setUnreadNotificationsCount}
                />
              )}
            </div>

            {/* Foto de Perfil de Usuario */}
            <button
              type="button"
              className={styles.headerProfileBtn}
              onClick={() => {
                setIsNotificationsOpen(false);
                if (appMode === "student") {
                  setActiveStudentTab("profile");
                } else {
                  setActiveAdminTab("settings");
                }
              }}
              title={appMode === "student" ? "Ver mi perfil de estudiante" : "Configuración y perfil del administrador"}
            >
              <div className={styles.headerAvatarCircle}>
                {appMode === "student" && studentProfile?.avatar ? (
                  <img src={studentProfile.avatar} alt="Avatar" className={styles.headerAvatarImg} />
                ) : (
                  <User size={15} />
                )}
              </div>
            </button>
          </div>
        </header>

        {/* Cuerpo del módulo activo */}
        <main className={`${styles.mainBody} ${isFluidMain ? styles.mainBodyNoPadding : ""}`}>
          {/* MODO DOCENTE / PROFESOR */}
          {appMode === "instructor" && (
            <>
              {activeInstructorTab === "courses" && (
                <CoursesView onNavigateToInstructors={() => {}} isInstructor={true} />
              )}
              {activeInstructorTab === "students" && (
                <StudentsView isInstructor={true} />
              )}
              {activeInstructorTab === "community" && (
                <StudentCommunityView
                  courses={studentCourses}
                  academyName={academyName}
                  isAdmin={false}
                  isInstructor={true}
                  currentUser={{
                    id: wpStudentData?.user_id || 0,
                    name: studentProfile?.name || "Docente",
                    role: "instructor",
                  }}
                  onNavigateToCourse={() => {
                    setActiveInstructorTab("courses");
                  }}
                />
              )}
            </>
          )}

          {/* MODO ADMINISTRADOR */}
          {appMode === "admin" && (
            <>
              {activeAdminTab === "overview" && <OverviewView onNavigate={(tab) => setActiveAdminTab(tab as AdminTabId)} />}
              {activeAdminTab === "courses" && <CoursesView onNavigateToInstructors={() => setActiveAdminTab("instructors")} />}
              {activeAdminTab === "students" && <StudentsView />}
              {activeAdminTab === "instructors" && <InstructorsView />}
              {activeAdminTab === "events" && <EventsView />}
              {activeAdminTab === "finance" && <FinanceView />}
              {activeAdminTab === "marketing" && <MarketingView currentAccent={accent} />}
              {activeAdminTab === "community" && (
                <StudentCommunityView
                  courses={studentCourses}
                  academyName={academyName}
                  isAdmin={true}
                  currentUser={{
                    id: 1,
                    name: "Administrador",
                    role: "admin",
                  }}
                  onNavigateToCourse={() => {
                    setActiveAdminTab("courses");
                  }}
                />
              )}
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
          {appMode === "student" && !isStudentBlocked && (
            <>
              {/* 1. Dashboard del Estudiante */}
              {activeStudentTab === "dashboard" && (
                <StudentDashboardView
                  profile={studentProfile || defaultStudentProfile}
                  courses={studentCourses}
                  onSelectCourse={handleSelectCourse}
                  onResumeLesson={handleResumeLesson}
                  loading={studentLoading}
                  onNavigateTab={(tab) => {
                    if (tab === "courses" || tab === "community" || tab === "dashboard") {
                      setActiveStudentTab(tab);
                    }
                  }}
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
                      onOpenProfile={() => setActiveStudentTab("profile")}
                      loading={studentLoading}
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
                      studentId={studentProfile?.id}
                      onToggleCompleteTopic={handleToggleCompleteTopic}
                      onBackToCourse={handleBackToCourse}
                      onSwitchModule={(sec, les, top) => {
                        setSelectedSection(sec);
                        setSelectedLesson(les);
                        setSelectedTopic(top || null);
                      }}
                      academyLogo={academyLogo}
                    />
                  )}
                </>
              )}

              {/* 3. Comunidad del Estudiante */}
              {activeStudentTab === "community" && (
                <StudentCommunityView
                  courses={studentCourses}
                  academyName={academyName}
                  isAdmin={Boolean(studentProfile?.isAdmin)}
                  currentUser={studentProfile ? {
                    id: studentProfile.id,
                    name: studentProfile.name,
                    avatar: studentProfile.avatar,
                    role: studentProfile.isAdmin ? "admin" : "student",
                  } : undefined}
                  onNavigateToCourse={(courseId) => {
                    const crs = studentCourses.find((c) => c.id === courseId);
                    if (crs) {
                      handleSelectCourse(crs);
                    }
                  }}
                />
              )}

              {/* 4. Perfil del Estudiante */}
              {activeStudentTab === "profile" && (
                <StudentProfileView
                  profile={studentProfile || defaultStudentProfile}
                  courses={studentCourses}
                  onSelectCourse={handleSelectCourse}
                  onProfileUpdate={(updated) => setStudentProfile(updated)}
                  onBack={() => setActiveStudentTab("dashboard")}
                  onLogout={() => { window.location.href = logoutUrl; }}
                />
              )}
            </>
          )}
        </main>
      </div>

      {/* PopUp No Descartable de Estudiante Bloqueado */}
      {isStudentBlocked && (
        <div className={studentStyles.blockedOverlay}>
          <div className={studentStyles.blockedCard}>
            <div className={studentStyles.blockedIconWrap}>
              <ShieldAlert size={38} />
            </div>

            <div className={studentStyles.blockedBadge}>
              <Lock size={12} />
              <span>Acceso Suspendido</span>
            </div>

            <h2 className={studentStyles.blockedTitle}>Tu cuenta se encuentra bloqueada</h2>

            <p className={studentStyles.blockedText}>
              Hola <strong>{studentProfile?.name || "Estudiante"}</strong>, tu acceso a los cursos y contenidos formativos de la academia ha sido suspendido temporalmente por la administración.
            </p>

            <p className={studentStyles.blockedSubtext}>
              Para consultar el estado de tu cuenta o reactivar tu acceso de inmediato, por favor comunícate con nuestro equipo de administración:
            </p>

            <div className={studentStyles.blockedContactBox}>
              {cleanWhatsapp ? (
                <a
                  href={`https://wa.me/${cleanWhatsapp}?text=${encodeURIComponent(
                    `Hola, mi nombre es ${studentProfile?.name || ""} (${studentProfile?.email || ""}) y solicito asistencia sobre el estado de mi cuenta en ${academyName}.`
                  )}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={studentStyles.blockedWhatsappBtn}
                >
                  <MessageCircle size={17} />
                  <span>Contactar a Administración por WhatsApp</span>
                </a>
              ) : null}

              <a
                href={`mailto:${supportEmail}?subject=${encodeURIComponent(
                  `Reactivación de Cuenta - ${studentProfile?.name || "Estudiante"}`
                )}&body=${encodeURIComponent(
                  `Hola administración,\n\nMi cuenta (${studentProfile?.email || ""}) se encuentra bloqueada y deseo reactivar mi acceso a la plataforma.`
                )}`}
                className={studentStyles.blockedEmailBtn}
              >
                <Mail size={16} />
                <span>Escribir a Administración por Correo</span>
              </a>

              <button
                type="button"
                className={studentStyles.blockedLogoutBtn}
                onClick={() => {
                  window.location.href = logoutUrl;
                }}
              >
                <LogOut size={15} />
                <span>Cerrar sesión</span>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* =========================================================
          MOBILE BOTTOM NAVIGATION BAR (Sensación de App Nativa)
          ========================================================= */}
      {!isStudentBlocked && (
      <nav className={styles.mobileBottomBar}>
        {appMode === "student" ? (
          <>
            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeStudentTab === "dashboard" ? styles.active : ""}`}
              onClick={() => setActiveStudentTab("dashboard")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <BarChart3 size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Dashboard</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeStudentTab === "courses" ? styles.active : ""}`}
              onClick={() => setActiveStudentTab("courses")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <GraduationCap size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Cursos</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeStudentTab === "community" ? styles.active : ""}`}
              onClick={() => setActiveStudentTab("community")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <Users size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Comunidad</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeStudentTab === "profile" ? styles.active : ""}`}
              onClick={() => setActiveStudentTab("profile")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <User size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Perfil</span>
            </button>
          </>
        ) : (
          <>
            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeAdminTab === "overview" ? styles.active : ""}`}
              onClick={() => setActiveAdminTab("overview")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <BarChart3 size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Dashboard</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeAdminTab === "courses" ? styles.active : ""}`}
              onClick={() => setActiveAdminTab("courses")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <GraduationCap size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Cursos</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeAdminTab === "students" ? styles.active : ""}`}
              onClick={() => setActiveAdminTab("students")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <Users size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Alumnos</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${activeAdminTab === "finance" ? styles.active : ""}`}
              onClick={() => setActiveAdminTab("finance")}
            >
              <div className={styles.mobileBottomIconWrap}>
                <CreditCard size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Finanzas</span>
            </button>

            <button
              type="button"
              className={`${styles.mobileBottomItem} ${isDrawerOpen ? styles.active : ""}`}
              onClick={() => setIsDrawerOpen(true)}
            >
              <div className={styles.mobileBottomIconWrap}>
                <Menu size={18} />
              </div>
              <span className={styles.mobileBottomLabel}>Más</span>
            </button>
          </>
        )}
      </nav>
      )}

      {/* =========================================================
          DRAWER / SLIDE-OVER LATERAL PARA EL ADMINISTRADOR EN MÓVIL
          ========================================================= */}
      {isDrawerOpen && appMode === "admin" && (
        <div 
          className={styles.drawerOverlay} 
          onClick={() => setIsDrawerOpen(false)}
        >
          <div 
            className={styles.drawerSheet} 
            onClick={(e) => e.stopPropagation()}
          >
            <div className={styles.drawerHeader}>
              <div className={styles.drawerBrand}>
                {academyLogo ? (
                  <img src={academyLogo} alt={academyName} style={{ maxHeight: 26, maxWidth: 80, objectFit: "contain" }} />
                ) : (
                  <Layers size={18} style={{ color: "var(--accent, #7747ff)" }} />
                )}
                <span>{academyName || "Crezca"}</span>
              </div>
              <button 
                type="button" 
                className={styles.drawerCloseBtn} 
                onClick={() => setIsDrawerOpen(false)}
                aria-label="Cerrar menú"
              >
                <X size={16} />
              </button>
            </div>

            <div className={styles.drawerSectionTitle}>Módulos Adicionales</div>
            <div className={styles.drawerNavList}>
              <button
                type="button"
                className={`${styles.drawerNavItem} ${activeAdminTab === "instructors" ? styles.active : ""}`}
                onClick={() => { setActiveAdminTab("instructors"); setIsDrawerOpen(false); }}
              >
                <UserCheck size={17} />
                <span>Docentes & Mentores</span>
              </button>

              <button
                type="button"
                className={`${styles.drawerNavItem} ${activeAdminTab === "events" ? styles.active : ""}`}
                onClick={() => { setActiveAdminTab("events"); setIsDrawerOpen(false); }}
              >
                <Calendar size={17} />
                <span>Eventos & Clases en Vivo</span>
              </button>

              <button
                type="button"
                className={`${styles.drawerNavItem} ${activeAdminTab === "marketing" ? styles.active : ""}`}
                onClick={() => { setActiveAdminTab("marketing"); setIsDrawerOpen(false); }}
              >
                <Mail size={17} />
                <span>Marketing & Correos</span>
              </button>

              <button
                type="button"
                className={`${styles.drawerNavItem} ${activeAdminTab === "community" ? styles.active : ""}`}
                onClick={() => { setActiveAdminTab("community"); setIsDrawerOpen(false); }}
              >
                <MessageSquare size={17} />
                <span>Comunidad & Foro</span>
              </button>

              <button
                type="button"
                className={`${styles.drawerNavItem} ${activeAdminTab === "settings" ? styles.active : ""}`}
                onClick={() => { setActiveAdminTab("settings"); setIsDrawerOpen(false); }}
              >
                <Sliders size={17} />
                <span>Ajustes & Personalización</span>
              </button>
            </div>

            <div className={styles.drawerSectionTitle}>Acciones Rápidas</div>
            <div className={styles.drawerNavList}>
              <button
                type="button"
                className={styles.drawerNavItem}
                onClick={() => { setAppMode("student"); setIsDrawerOpen(false); }}
              >
                <GraduationCap size={17} />
                <span>Previsualizar Modo Estudiante</span>
              </button>

              <button
                type="button"
                className={styles.drawerNavItem}
                onClick={toggleTheme}
              >
                {theme === "dark" ? <Sun size={17} /> : <Moon size={17} />}
                <span>{theme === "dark" ? "Cambiar a Modo Claro" : "Cambiar a Modo Oscuro"}</span>
              </button>

              <button
                type="button"
                className={styles.drawerNavItem}
                onClick={toggleFullscreen}
              >
                {isFullscreen ? <Minimize2 size={17} /> : <Maximize2 size={17} />}
                <span>{isFullscreen ? "Salir de Pantalla Completa" : "Pantalla Completa"}</span>
              </button>
            </div>

            {isActualAdmin && (
              <div className={styles.drawerFooter}>
                <a
                  href={wpAdminUrl}
                  className={styles.backToWpBtn}
                  style={{ width: "100%", justifyContent: "center" }}
                >
                  <ArrowLeft size={15} />
                  <span>Volver a WordPress</span>
                </a>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Popover Global de Notificaciones para Móvil */}
      {isMobile && (
        <NotificationsPopover
          isOpen={isNotificationsOpen}
          onClose={() => setIsNotificationsOpen(false)}
          onNavigate={(tab) => {
            setIsNotificationsOpen(false);
            if (appMode === "admin") {
              if (tab === "events" || tab === "courses" || tab === "students" || tab === "instructors" || tab === "community" || tab === "settings") {
                setActiveAdminTab(tab as AdminTabId);
              }
            } else {
              if (tab === "dashboard" || tab === "courses" || tab === "community" || tab === "profile") {
                setActiveStudentTab(tab as StudentTabId);
              }
            }
          }}
          appMode={appMode}
          onUpdateCount={setUnreadNotificationsCount}
        />
      )}
    </div>
  );
}

export default App;
