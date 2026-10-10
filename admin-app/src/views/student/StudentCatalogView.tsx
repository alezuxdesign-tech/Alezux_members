import React, { useState } from "react";
import { 
  Play, 
  Lock, 
  Search, 
  BookOpen, 
  CheckCircle2, 
  Sparkles, 
  ArrowRight,
  ShieldCheck,
  Eye,
  GraduationCap
} from "lucide-react";
import { Course, StudentProfile } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentCatalogViewProps {
  courses: Course[];
  profile: StudentProfile;
  onSelectCourse: (course: Course) => void;
  onOpenProfile: () => void;
}

export const StudentCatalogView: React.FC<StudentCatalogViewProps> = ({
  courses,
  profile,
  onSelectCourse,
  onOpenProfile,
}) => {
  const [search, setSearch] = useState("");

  const filteredCourses = courses.filter((c) =>
    c.title.toLowerCase().includes(search.toLowerCase()) ||
    c.description?.toLowerCase().includes(search.toLowerCase())
  );

  const myCourses = filteredCourses.filter((c) => c.hasAccess);
  const catalogCourses = filteredCourses.filter((c) => !c.hasAccess);

  return (
    <div className={styles.catalogContainer}>
      {/* Banner de Bienvenida del Campus */}
      <div className={styles.heroWelcomeCard}>
        <div className={styles.heroWelcomeContent}>
          <div className={styles.welcomeTag}>
            <GraduationCap size={15} />
            <span>Campus Virtual</span>
          </div>
          <h1 className={styles.welcomeTitle}>
            ¡Hola, {profile.name}! Continúa tu aprendizaje hoy
          </h1>
          <p className={styles.welcomeDesc}>
            Accede a tus formaciones habilitadas, visualiza las lecciones y descarga los materiales de estudio.
          </p>

          <div className={styles.searchBarWrapper}>
            <Search size={16} className={styles.searchIcon} />
            <input
              type="text"
              placeholder="Buscar curso, temario o lección..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className={styles.catalogSearchInput}
            />
          </div>
        </div>

        <div className={styles.heroWelcomeBadge}>
          <div className={styles.membershipPill}>
            <ShieldCheck size={16} className={styles.shieldIcon} />
            <span>{profile.planName}</span>
          </div>
          <button 
            type="button" 
            className={styles.profileShortcutBtn}
            onClick={onOpenProfile}
          >
            <img 
              src={profile.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80"} 
              alt={profile.name} 
              className={styles.shortcutAvatar} 
            />
            <span>Mi Perfil</span>
          </button>
        </div>
      </div>

      {/* 1. SECCIÓN: MIS CURSOS ACTIVOS (Habilitados) */}
      <section className={styles.catalogSection}>
        <div className={styles.sectionHeader}>
          <div>
            <h2 className={styles.sectionHeading}>Mis Cursos Habilitados</h2>
            <p className={styles.sectionSub}>Formaciones con acceso activo en tu cuenta</p>
          </div>
          <span className={styles.courseCountBadge}>
            {myCourses.length} {myCourses.length === 1 ? "curso" : "cursos"}
          </span>
        </div>

        {myCourses.length === 0 ? (
          <div className={styles.emptyCard}>
            <BookOpen size={36} className={styles.emptyIcon} />
            <h3>No tienes cursos habilitados con este criterio de búsqueda</h3>
            <p>Revisa el catálogo disponible a continuación para inscribirte en nuevas formaciones.</p>
          </div>
        ) : (
          <div className={styles.coursesGrid}>
            {myCourses.map((course) => {
              const progress = course.progress ?? 0;
              return (
                <div key={course.id} className={styles.courseCard}>
                  <div className={styles.courseCoverWrapper}>
                    <img
                      src={course.thumbnail || course.banner || "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80"}
                      alt={course.title}
                      className={styles.courseCoverImg}
                    />
                    <div className={styles.coverBadgeUnlocked}>
                      <CheckCircle2 size={13} />
                      <span>Acceso Habilitado</span>
                    </div>
                  </div>

                  <div className={styles.courseBody}>
                    <h3 className={styles.courseTitle}>{course.title}</h3>
                    <p className={styles.courseDescription}>
                      {course.description ? course.description.slice(0, 110) + "..." : "Aprende con las mejores clases y metodologías prácticas."}
                    </p>

                    {/* Barra de Progreso */}
                    <div className={styles.progressBarWrapper}>
                      <div className={styles.progressTextRow}>
                        <span>Progreso del Curso</span>
                        <strong>{progress}%</strong>
                      </div>
                      <div className={styles.progressTrack}>
                        <div
                          className={styles.progressFill}
                          style={{ width: `${progress}%` }}
                        />
                      </div>
                    </div>

                    <div className={styles.courseFooter}>
                      <button
                        type="button"
                        className={styles.enterCourseBtn}
                        onClick={() => onSelectCourse(course)}
                      >
                        <Play size={14} fill="currentColor" />
                        <span>Continuar Curso</span>
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </section>

      {/* 2. SECCIÓN: CATÁLOGO & DISPONIBLES (No habilitados) */}
      {catalogCourses.length > 0 && (
        <section className={styles.catalogSection}>
          <div className={styles.sectionHeader}>
            <div>
              <h2 className={styles.sectionHeading}>Explorar Más Formaciones</h2>
              <p className={styles.sectionSub}>
                Explora el temario y adquiere acceso a nuevos programas educativos
              </p>
            </div>
            <span className={styles.catalogBadge}>Catálogo General</span>
          </div>

          <div className={styles.coursesGrid}>
            {catalogCourses.map((course) => {
              const moduleCount = course.sections?.length || 0;
              return (
                <div key={course.id} className={[styles.courseCard, styles.courseCardLocked].join(" ")}>
                  <div className={styles.courseCoverWrapper}>
                    <img
                      src={course.thumbnail || course.banner || "https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80"}
                      alt={course.title}
                      className={styles.courseCoverImg}
                    />
                    <div className={styles.coverBadgeLocked}>
                      <Lock size={13} />
                      <span>Acceso Disponible</span>
                    </div>
                    {course.price ? (
                      <span className={styles.pricePill}>
                        ${course.price} USD
                      </span>
                    ) : null}
                  </div>

                  <div className={styles.courseBody}>
                    <h3 className={styles.courseTitle}>{course.title}</h3>
                    <p className={styles.courseDescription}>
                      {course.description ? course.description.slice(0, 110) + "..." : "Especialización avanzada con temario completo y materiales."}
                    </p>

                    <div className={styles.metaRow}>
                      <span className={styles.metaItem}>
                        <BookOpen size={13} />
                        <span>{moduleCount} {moduleCount === 1 ? "módulo" : "módulos"}</span>
                      </span>
                      <span className={styles.previewNotice}>
                        Temario visible para explorar
                      </span>
                    </div>

                    <div className={styles.courseFooterLocked}>
                      <button
                        type="button"
                        className={styles.exploreCurriculumBtn}
                        onClick={() => onSelectCourse(course)}
                      >
                        <Eye size={14} />
                        <span>Ver contenido</span>
                      </button>
                      {course.checkoutUrl && (
                        <a
                          href={course.checkoutUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className={styles.buyShortcutBtn}
                          onClick={(e) => e.stopPropagation()}
                        >
                          <span>Comprar</span>
                          <ArrowRight size={13} />
                        </a>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </section>
      )}
    </div>
  );
};
