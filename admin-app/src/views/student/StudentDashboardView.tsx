import React from "react";
import { 
  GraduationCap, 
  CheckCircle2, 
  TrendingUp, 
  Clock, 
  ArrowRight, 
  Play, 
  Sparkles, 
  ShieldCheck, 
  MessageCircle, 
  Users, 
  Video,
  BookOpen
} from "lucide-react";
import { Course, StudentProfile } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentDashboardViewProps {
  profile: StudentProfile;
  courses: Course[];
  onSelectCourse: (course: Course) => void;
  onNavigateTab: (tabId: "courses" | "community") => void;
}

export const StudentDashboardView: React.FC<StudentDashboardViewProps> = ({
  profile,
  courses,
  onSelectCourse,
  onNavigateTab,
}) => {
  const enrolledCourses = courses.filter((c) => c.hasAccess);
  const otherCourses = courses.filter((c) => !c.hasAccess);

  // Calcular métricas
  const totalCompleted = profile.completedTopicIds.length;
  
  // Promedio de progreso en cursos inscritos
  const totalProgress = enrolledCourses.reduce((acc, c) => acc + (c.progress || 0), 0);
  const avgProgress = enrolledCourses.length > 0 ? Math.round(totalProgress / enrolledCourses.length) : 0;
  
  // Estimación de horas de aprendizaje (aprox 30 min por lección)
  const estimatedHours = Math.max(1, Math.round(totalCompleted * 0.5));

  return (
    <div className={styles.studentDashboardContainer}>
      {/* 1. HERO BANNER DE BIENVENIDA */}
      <div className={styles.dashboardHeroBanner}>
        <div className={styles.dashboardHeroContent}>
          <div className={styles.dashboardTag}>
            <Sparkles size={14} />
            <span>Panel del Estudiante</span>
          </div>
          <h1 className={styles.dashboardTitle}>
            ¡Hola, {profile.name}! 👋 Continúa aprendiendo hoy
          </h1>
          <p className={styles.dashboardSubtitle}>
            Tienes {enrolledCourses.length} {enrolledCourses.length === 1 ? "curso activo" : "cursos activos"}. Retoma tus lecciones donde las dejaste y alcanza tus metas formativas.
          </p>
          <div className={styles.dashboardHeroActions}>
            <button
              type="button"
              className={styles.dashboardPrimaryBtn}
              onClick={() => onNavigateTab("courses")}
            >
              <BookOpen size={16} />
              <span>Ver mis cursos</span>
            </button>
            <button
              type="button"
              className={styles.dashboardSecondaryBtn}
              onClick={() => onNavigateTab("community")}
            >
              <Users size={16} />
              <span>Ir a la comunidad</span>
            </button>
          </div>
        </div>

        <div className={styles.dashboardHeroCardRight}>
          <div className={styles.studentPlanBadge}>
            <ShieldCheck size={16} className={styles.badgeShieldIcon} />
            <div className={styles.planInfoText}>
              <span className={styles.planLabel}>Membresía</span>
              <strong className={styles.planNameText}>{profile.planName}</strong>
            </div>
          </div>
        </div>
      </div>

      {/* 2. REJILLA DE KPIS / MÉTRICAS DEL ESTUDIANTE */}
      <div className={styles.studentStatsGrid}>
        <div className={styles.statMetricCard}>
          <div className={styles.statIconWrap} style={{ background: "rgba(119, 71, 255, 0.12)", color: "var(--accent, #7747ff)" }}>
            <GraduationCap size={22} />
          </div>
          <div className={styles.statInfo}>
            <span className={styles.statLabel}>Cursos Inscritos</span>
            <span className={styles.statValue}>{enrolledCourses.length}</span>
            <span className={styles.statDesc}>Formaciones habilitadas</span>
          </div>
        </div>

        <div className={styles.statMetricCard}>
          <div className={styles.statIconWrap} style={{ background: "rgba(13, 184, 121, 0.12)", color: "#0db879" }}>
            <CheckCircle2 size={22} />
          </div>
          <div className={styles.statInfo}>
            <span className={styles.statLabel}>Lecciones Finalizadas</span>
            <span className={styles.statValue}>{totalCompleted}</span>
            <span className={styles.statDesc}>Temas completados con éxito</span>
          </div>
        </div>

        <div className={styles.statMetricCard}>
          <div className={styles.statIconWrap} style={{ background: "rgba(5, 98, 239, 0.12)", color: "#0562ef" }}>
            <TrendingUp size={22} />
          </div>
          <div className={styles.statInfo}>
            <span className={styles.statLabel}>Progreso Promedio</span>
            <span className={styles.statValue}>{avgProgress}%</span>
            <span className={styles.statDesc}>Avance en tus formaciones</span>
          </div>
        </div>

        <div className={styles.statMetricCard}>
          <div className={styles.statIconWrap} style={{ background: "rgba(243, 173, 32, 0.12)", color: "#f3ad20" }}>
            <Clock size={22} />
          </div>
          <div className={styles.statInfo}>
            <span className={styles.statLabel}>Tiempo Dedicado</span>
            <span className={styles.statValue}>{estimatedHours}h</span>
            <span className={styles.statDesc}>Horas de estudio estimadas</span>
          </div>
        </div>
      </div>

      {/* 3. SECCIÓN: CONTINUAR APRENDIENDO */}
      <section className={styles.dashboardSection}>
        <div className={styles.sectionHeaderRow}>
          <div>
            <h2 className={styles.sectionHeadingTitle}>Continuar Aprendiendo</h2>
            <p className={styles.sectionHeadingSub}>Retoma tus clases activas y continúa progresando</p>
          </div>
          <button
            type="button"
            className={styles.viewAllLinkBtn}
            onClick={() => onNavigateTab("courses")}
          >
            <span>Ver todos los cursos</span>
            <ArrowRight size={14} />
          </button>
        </div>

        {enrolledCourses.length > 0 ? (
          <div className={styles.continueCardsGrid}>
            {enrolledCourses.map((c) => {
              const prog = c.progress ?? 0;
              return (
                <div key={c.id} className={styles.continueCard}>
                  <div className={styles.continueCardCover}>
                    <img 
                      src={c.thumbnail || c.banner || "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80"} 
                      alt={c.title} 
                      className={styles.continueCardImg} 
                    />
                    <div className={styles.continueCardProgressTag}>
                      <span>{prog}% completado</span>
                    </div>
                  </div>

                  <div className={styles.continueCardBody}>
                    <h3 className={styles.continueCardTitle}>{c.title}</h3>
                    <p className={styles.continueCardDesc}>
                      {c.description ? c.description.slice(0, 90) + (c.description.length > 90 ? "..." : "") : "Continúa viendo los temas y materiales de este curso."}
                    </p>

                    <div className={styles.continueCardProgressWrap}>
                      <div className={styles.continueCardTrack}>
                        <div className={styles.continueCardFill} style={{ width: `${prog}%` }} />
                      </div>
                      <span className={styles.continueCardProgressLabel}>
                        {c.completedTopics || 0} de {c.totalTopics || 0} lecciones
                      </span>
                    </div>

                    <button
                      type="button"
                      className={styles.continueCourseBtn}
                      onClick={() => onSelectCourse(c)}
                    >
                      <Play size={14} fill="currentColor" />
                      <span>{prog > 0 ? "Continuar lección" : "Comenzar curso"}</span>
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className={styles.emptyStateCard}>
            <GraduationCap size={40} className={styles.emptyStateIcon} />
            <h3 className={styles.emptyStateTitle}>Aún no tienes cursos habilitados</h3>
            <p className={styles.emptyStateDesc}>
              Explora el catálogo para acceder a las formaciones de la academia.
            </p>
            <button
              type="button"
              className={styles.dashboardPrimaryBtn}
              onClick={() => onNavigateTab("courses")}
            >
              <BookOpen size={16} />
              <span>Explorar catálogo</span>
            </button>
          </div>
        )}
      </section>

      {/* 4. CANALES RÁPIDOS DE LA COMUNIDAD */}
      <section className={styles.dashboardSection}>
        <div className={styles.sectionHeaderRow}>
          <div>
            <h2 className={styles.sectionHeadingTitle}>Canales de la Comunidad</h2>
            <p className={styles.sectionHeadingSub}>Participa, resuelve dudas con tutores y asiste a las mentorías en vivo</p>
          </div>
        </div>

        <div className={styles.quickChannelsGrid}>
          <div className={styles.quickChannelCard}>
            <div className={styles.quickChannelIconWrap} style={{ background: "rgba(37, 211, 102, 0.15)", color: "#25D366" }}>
              <MessageCircle size={22} />
            </div>
            <div className={styles.quickChannelInfo}>
              <h4 className={styles.quickChannelTitle}>WhatsApp VIP</h4>
              <p className={styles.quickChannelDesc}>Comunidad y networking diario con alumnos.</p>
            </div>
            <button
              type="button"
              className={styles.quickChannelBtn}
              onClick={() => onNavigateTab("community")}
            >
              <span>Acceder</span>
            </button>
          </div>

          <div className={styles.quickChannelCard}>
            <div className={styles.quickChannelIconWrap} style={{ background: "rgba(74, 21, 75, 0.25)", color: "#c084fc" }}>
              <Users size={22} />
            </div>
            <div className={styles.quickChannelInfo}>
              <h4 className={styles.quickChannelTitle}>Slack Workspace</h4>
              <p className={styles.quickChannelDesc}>Canales temáticos y resolución técnica de dudas.</p>
            </div>
            <button
              type="button"
              className={styles.quickChannelBtn}
              onClick={() => onNavigateTab("community")}
            >
              <span>Acceder</span>
            </button>
          </div>

          <div className={styles.quickChannelCard}>
            <div className={styles.quickChannelIconWrap} style={{ background: "rgba(45, 140, 255, 0.15)", color: "#2D8CFF" }}>
              <Video size={22} />
            </div>
            <div className={styles.quickChannelInfo}>
              <h4 className={styles.quickChannelTitle}>Sesiones en Vivo</h4>
              <p className={styles.quickChannelDesc}>Masterminds semanales y clases en directo.</p>
            </div>
            <button
              type="button"
              className={styles.quickChannelBtn}
              onClick={() => onNavigateTab("community")}
            >
              <span>Acceder</span>
            </button>
          </div>
        </div>
      </section>

      {/* 5. SI HAY CURSOS DISPONIBLES PARA ADQUIRIR */}
      {otherCourses.length > 0 && (
        <section className={styles.exploreMoreBanner}>
          <div className={styles.exploreMoreContent}>
            <Sparkles size={24} className={styles.exploreMoreIcon} />
            <div>
              <h3 className={styles.exploreMoreTitle}>
                Descubre más formaciones en la Academia ({otherCourses.length} disponibles)
              </h3>
              <p className={styles.exploreMoreDesc}>
                Amplía tus conocimientos y especialízate con nuevos programas formativos.
              </p>
            </div>
          </div>
          <button
            type="button"
            className={styles.dashboardPrimaryBtn}
            onClick={() => onNavigateTab("courses")}
          >
            <span>Ver catálogo completo</span>
            <ArrowRight size={16} />
          </button>
        </section>
      )}
    </div>
  );
};
