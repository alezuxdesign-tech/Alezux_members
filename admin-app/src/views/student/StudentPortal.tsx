import React, { useEffect, useState, useCallback } from "react";
import { 
  GraduationCap, 
  ArrowLeft, 
  User, 
  BookOpen, 
  LogOut, 
  Eye, 
  ShieldCheck,
  Sparkles,
  Layers
} from "lucide-react";
import { Course, CourseSection, CourseLesson, CourseTopic, StudentProfile, api } from "../../services/api";
import { StudentCatalogView } from "./StudentCatalogView";
import { StudentCourseNetflixView } from "./StudentCourseNetflixView";
import { StudentLessonClassroomView } from "./StudentLessonClassroomView";
import { StudentProfileView } from "./StudentProfileView";
import { ModuleSkeleton } from "../../components/arc/skeleton";
import styles from "./StudentPortal.module.css";

interface StudentPortalProps {
  onExitStudentMode?: () => void;
  isAdminPreview?: boolean;
  academyName?: string;
  academyLogo?: string;
}

type PortalView = "catalog" | "course" | "classroom" | "profile";

export const StudentPortal: React.FC<StudentPortalProps> = ({
  onExitStudentMode,
  isAdminPreview = false,
  academyName: propAcademyName,
  academyLogo: propAcademyLogo,
}) => {
  const [currentView, setCurrentView] = useState<PortalView>("catalog");
  const [courses, setCourses] = useState<Course[]>([]);
  const [profile, setProfile] = useState<StudentProfile | null>(null);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [selectedSection, setSelectedSection] = useState<CourseSection | null>(null);
  const [selectedLesson, setSelectedLesson] = useState<CourseLesson | null>(null);
  const [selectedTopic, setSelectedTopic] = useState<CourseTopic | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const academyName = propAcademyName || localStorage.getItem("crezca_academy_name") || "Academia";
  const academyLogo = propAcademyLogo || localStorage.getItem("crezca_academy_logo") || "";

  const loadData = useCallback(async () => {
    setIsLoading(true);
    try {
      const [profData, coursesData] = await Promise.all([
        api.getStudentProfile(),
        api.getStudentCourses(),
      ]);
      setProfile(profData);
      setCourses(coursesData);
    } catch (err) {
      console.error("Error cargando datos del portal de estudiantes:", err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    loadData();
  }, [loadData]);

  // Manejar marcar / desmarcar topic como completado
  const handleToggleComplete = async (topicId: string) => {
    try {
      const res = await api.toggleCompleteTopic(topicId, selectedCourse?.id);
      if (profile) {
        setProfile({
          ...profile,
          completedTopicIds: res.completedTopicIds,
        });
      }
      // Actualizar progreso en el curso activo
      if (selectedCourse) {
        setCourses((prev) =>
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
      console.error("Error al cambiar estado de lección:", err);
    }
  };

  const handleSelectCourse = (course: Course) => {
    setSelectedCourse(course);
    setCurrentView("course");
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleSelectModule = (sec: CourseSection, les: CourseLesson, top?: CourseTopic) => {
    setSelectedSection(sec);
    setSelectedLesson(les);
    setSelectedTopic(top || null);
    setCurrentView("classroom");
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleBackToCatalog = () => {
    setSelectedCourse(null);
    setSelectedSection(null);
    setSelectedLesson(null);
    setSelectedTopic(null);
    setCurrentView("catalog");
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const handleBackToCourse = () => {
    setSelectedSection(null);
    setSelectedLesson(null);
    setSelectedTopic(null);
    setCurrentView("course");
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  if (isLoading || !profile) {
    return (
      <div className={styles.loadingContainer}>
        <div className={styles.loadingSpinner} />
        <p>Cargando Aula Virtual...</p>
      </div>
    );
  }

  return (
    <div className={styles.portalWrapper}>
      {/* 1. BARRA SUPERIOR PARA ADMINISTRADOR (SI ES PREVIEW) */}
      {isAdminPreview && (
        <div className={styles.adminPreviewBanner}>
          <div className={styles.adminPreviewText}>
            <Eye size={15} />
            <span>Previsualización activa: <strong>Modo Vista de Estudiante</strong></span>
          </div>
          {onExitStudentMode && (
            <button 
              type="button" 
              className={styles.exitPreviewBtn} 
              onClick={onExitStudentMode}
            >
              <ArrowLeft size={14} />
              <span>Volver a Administración</span>
            </button>
          )}
        </div>
      )}

      {/* 2. CABECERA PRINCIPAL DEL CAMPUS (SOLO EN CATÁLOGO Y PERFIL) */}
      {(currentView === "catalog" || currentView === "profile") && (
        <header className={styles.portalHeader}>
          <div className={styles.portalHeaderLeft} onClick={handleBackToCatalog}>
            {academyLogo ? (
              <img src={academyLogo} alt={academyName} className={styles.headerLogoImg} />
            ) : (
              <div className={styles.headerLogoPlaceholder}>
                <GraduationCap size={20} />
              </div>
            )}
            <span className={styles.headerBrandTitle}>{academyName}</span>
          </div>

          <div className={styles.portalHeaderRight}>
            <button
              type="button"
              className={[
                styles.headerNavBtn,
                currentView === "catalog" ? styles.headerNavBtnActive : "",
              ].join(" ")}
              onClick={handleBackToCatalog}
            >
              <BookOpen size={16} />
              <span>Mis Cursos</span>
            </button>

            <button
              type="button"
              className={[
                styles.headerNavBtn,
                currentView === "profile" ? styles.headerNavBtnActive : "",
              ].join(" ")}
              onClick={() => setCurrentView("profile")}
            >
              <img 
                src={profile.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80"} 
                alt={profile.name} 
                className={styles.headerAvatarImg} 
              />
              <span>{profile.name}</span>
            </button>
          </div>
        </header>
      )}

      {/* 3. VISTAS DEL PORTAL */}
      <main className={styles.portalMainContent}>
        {currentView === "catalog" && (
          <StudentCatalogView
            courses={courses}
            profile={profile}
            onSelectCourse={handleSelectCourse}
            onOpenProfile={() => setCurrentView("profile")}
          />
        )}

        {currentView === "course" && selectedCourse && (
          <StudentCourseNetflixView
            course={selectedCourse}
            academyName={academyName}
            academyLogo={academyLogo}
            onBack={handleBackToCatalog}
            onSelectModule={handleSelectModule}
          />
        )}

        {currentView === "classroom" && selectedCourse && selectedSection && selectedLesson && (
          <StudentLessonClassroomView
            course={selectedCourse}
            currentSection={selectedSection}
            currentLesson={selectedLesson}
            initialTopic={selectedTopic || undefined}
            completedTopicIds={profile.completedTopicIds}
            onToggleCompleteTopic={handleToggleComplete}
            onBackToCourse={handleBackToCourse}
            onSwitchModule={(sec, les, top) => {
              setSelectedSection(sec);
              setSelectedLesson(les);
              setSelectedTopic(top || null);
            }}
          />
        )}

        {currentView === "profile" && (
          <StudentProfileView
            profile={profile}
            courses={courses}
            onProfileUpdate={(updated) => setProfile(updated)}
            onBack={handleBackToCatalog}
            onLogout={() => {
              const logoutUrl = (window as any).crezca_student_data?.logout_url || "/wp-login.php?action=logout";
              window.location.href = logoutUrl;
            }}
          />
        )}
      </main>
    </div>
  );
};

export default StudentPortal;
