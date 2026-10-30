import React from "react";
import { 
  Play, 
  Lock, 
  Unlock, 
  BookOpen, 
  CreditCard,
  GraduationCap
} from "lucide-react";
import { Course, StudentProfile } from "../../services/api";
import { Skeleton } from "../../components/arc/skeleton";
import styles from "./StudentPortal.module.css";

interface StudentCatalogViewProps {
  courses: Course[];
  profile: StudentProfile;
  onSelectCourse: (course: Course) => void;
  onOpenProfile?: () => void;
  loading?: boolean;
}

export const StudentCatalogView: React.FC<StudentCatalogViewProps> = ({
  courses,
  profile,
  onSelectCourse,
  loading = false,
}) => {
  const myCourses = courses.filter((c) => c.hasAccess);
  const catalogCourses = courses.filter((c) => !c.hasAccess);

  const renderSkeletonCards = (count = 3) => (
    <div className={styles.coursesGrid}>
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} className={styles.courseCard} style={{ pointerEvents: "none" }}>
          <div className={styles.courseCoverWrapper}>
            <Skeleton width="100%" height="100%" variant="rectangular" />
          </div>
          <div className={styles.courseBody}>
            <Skeleton width="75%" height={20} />
            <Skeleton width="95%" height={14} style={{ marginTop: 10 }} />
            <Skeleton width="60%" height={14} style={{ marginTop: 6 }} />
            <div style={{ marginTop: 18 }}>
              <Skeleton width="100%" height={6} variant="rounded" />
            </div>
            <div style={{ marginTop: 16 }}>
              <Skeleton width="100%" height={38} variant="rounded" />
            </div>
          </div>
        </div>
      ))}
    </div>
  );

  return (
    <div className={styles.catalogContainer}>
      {/* Encabezado Limpio del Campus Virtual */}
      <div className={styles.catalogCleanHeader}>
        <div className={styles.welcomeTag}>
          <GraduationCap size={15} />
          <span>Campus Virtual</span>
        </div>
        <h1 className={styles.welcomeTitle}>
          Hola, {profile.name}, completa tu aprendizaje hoy
        </h1>
        <p className={styles.welcomeDesc}>
          Accede a tus formaciones habilitadas, visualiza las lecciones y descarga los materiales de estudio.
        </p>
      </div>

      {/* 1. SECCIÓN: MIS CURSOS ACTIVOS (Habilitados) */}
      <section className={styles.catalogSection}>
        <div className={styles.sectionHeader}>
          <div>
            <h2 className={styles.sectionHeading}>Mis Cursos Habilitados</h2>
            <p className={styles.sectionSub}>Formaciones con acceso activo en tu cuenta</p>
          </div>
          <span className={styles.courseCountBadge}>
            {loading ? "Cargando..." : `${myCourses.length} ${myCourses.length === 1 ? "curso" : "cursos"}`}
          </span>
        </div>

        {loading ? (
          renderSkeletonCards(3)
        ) : myCourses.length === 0 ? (
          <div className={styles.emptyCard}>
            <BookOpen size={36} className={styles.emptyIcon} />
            <h3>Aún no tienes cursos activos</h3>
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
                    <div className={`${styles.coverLockCircle} ${styles.unlocked}`} title="Acceso Habilitado">
                      <Unlock size={16} />
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
                        <span>Continuar con el curso</span>
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
      {(loading || catalogCourses.length > 0) && (
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

          {loading ? (
            renderSkeletonCards(3)
          ) : (
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
                    <div className={`${styles.coverLockCircle} ${styles.locked}`} title="Acceso no habilitado">
                      <Lock size={16} />
                    </div>
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
                        Temario disponible
                      </span>
                    </div>

                    {/* Precio del Curso */}
                    <div className={styles.coursePriceContainer}>
                      <span className={styles.coursePriceLabel}>Precio del curso:</span>
                      <span className={styles.coursePriceValue}>
                        ${course.price || 49} USD
                      </span>
                    </div>

                    <div className={styles.courseFooterLocked}>
                      <button
                        type="button"
                        className={styles.buyCourseBtn}
                        onClick={(e) => {
                          e.stopPropagation();
                          if (course.checkoutUrl) {
                            window.open(course.checkoutUrl, "_blank");
                          } else {
                            onSelectCourse(course);
                          }
                        }}
                      >
                        <CreditCard size={14} />
                        <span>Comprar curso</span>
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
          )}
        </section>
      )}
    </div>
  );
};
