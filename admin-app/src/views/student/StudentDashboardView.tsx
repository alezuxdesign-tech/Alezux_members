import React, { useState, useEffect } from "react";
import { 
  GraduationCap, 
  CheckCircle2, 
  TrendingUp, 
  Clock, 
  ArrowRight, 
  Play, 
  Pause,
  RotateCcw,
  SkipForward,
  Sparkles, 
  ShieldCheck, 
  Video,
  BookOpen,
  Calendar,
  Flame,
  ExternalLink,
  Timer
} from "lucide-react";
import { Course, CourseSection, CourseLesson, CourseTopic, StudentProfile, AcademyEvent, api } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentDashboardViewProps {
  profile: StudentProfile;
  courses: Course[];
  onSelectCourse: (course: Course) => void;
  onResumeLesson?: (course: Course, section?: CourseSection | null, lesson?: CourseLesson | null, topic?: CourseTopic | null) => void;
  onNavigateTab: (tabId: "courses" | "community") => void;
}

interface LastLessonItem {
  course: Course;
  section: CourseSection;
  lesson: CourseLesson;
  topic?: CourseTopic;
  isFromStorage: boolean;
}

type PomodoroMode = "focus" | "short_break" | "long_break";

const MODE_DURATIONS: Record<PomodoroMode, number> = {
  focus: 25 * 60,       // 25 minutos
  short_break: 5 * 60,   // 5 minutos
  long_break: 15 * 60,   // 15 minutos
};

export const StudentDashboardView: React.FC<StudentDashboardViewProps> = ({
  profile,
  courses,
  onSelectCourse,
  onResumeLesson,
  onNavigateTab,
}) => {
  const enrolledCourses = courses.filter((c) => c.hasAccess);
  const otherCourses = courses.filter((c) => !c.hasAccess);

  // --- CÁLCULO DE MÉTRICAS (KPIS) ---
  const totalCompleted = profile.completedTopicIds.length;
  const totalProgress = enrolledCourses.reduce((acc, c) => acc + (c.progress || 0), 0);
  const avgProgress = enrolledCourses.length > 0 ? Math.round(totalProgress / enrolledCourses.length) : 0;
  const estimatedHours = Math.max(1, Math.round(totalCompleted * 0.5));

  // --- DETECCIÓN DE LA ÚLTIMA CLASE QUE VIO ---
  const [lastLesson, setLastLesson] = useState<LastLessonItem | null>(null);

  useEffect(() => {
    // 1. Revisar si hay registro de última lección en localStorage
    const saved = localStorage.getItem(`crezca_last_lesson_${profile.id}`);
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        const crs = enrolledCourses.find((c) => c.id === parsed.courseId);
        if (crs && crs.sections) {
          for (const sec of crs.sections) {
            const les = sec.lessons?.find((l) => l.id === parsed.lessonId);
            if (les) {
              const top = les.subtopics?.find((sub) => sub.id === parsed.topicId);
              setLastLesson({
                course: crs,
                section: sec,
                lesson: les,
                topic: top,
                isFromStorage: true,
              });
              return;
            }
          }
        }
      } catch (e) {
        console.warn("Could not parse saved last lesson", e);
      }
    }

    // 2. Si no hay en storage, encontrar la primera lección no completada del primer curso
    for (const crs of enrolledCourses) {
      if (crs.sections && crs.sections.length > 0) {
        for (const sec of crs.sections) {
          if (sec.lessons && sec.lessons.length > 0) {
            for (const les of sec.lessons) {
              const isDone = profile.completedTopicIds.includes(les.id);
              if (!isDone) {
                setLastLesson({
                  course: crs,
                  section: sec,
                  lesson: les,
                  isFromStorage: false,
                });
                return;
              }
            }
          }
        }
      }
    }

    // 3. Fallback a la primera lección del primer curso
    if (enrolledCourses.length > 0 && enrolledCourses[0].sections?.[0]?.lessons?.[0]) {
      const crs = enrolledCourses[0];
      const sec = crs.sections[0];
      const les = sec.lessons[0];
      setLastLesson({
        course: crs,
        section: sec,
        lesson: les,
        isFromStorage: false,
      });
    }
  }, [enrolledCourses, profile]);

  const handleResumeLastLesson = () => {
    if (!lastLesson) return;
    if (onResumeLesson) {
      onResumeLesson(lastLesson.course, lastLesson.section, lastLesson.lesson, lastLesson.topic);
    } else {
      onSelectCourse(lastLesson.course);
    }
  };

  // --- MODO POMODORO (CONCENTRACIÓN & ENFOQUE) ---
  const [pomodoroMode, setPomodoroMode] = useState<PomodoroMode>("focus");
  const [timeLeft, setTimeLeft] = useState<number>(MODE_DURATIONS.focus);
  const [isRunning, setIsRunning] = useState<boolean>(false);
  const [pomodoroNotice, setPomodoroNotice] = useState<string | null>(null);

  const [completedPomodoros, setCompletedPomodoros] = useState<number>(() => {
    const today = new Date().toDateString();
    const saved = localStorage.getItem(`crezca_pomodoro_count_${profile.id}_${today}`);
    return saved ? parseInt(saved, 10) : 0;
  });

  const playPomodoroChime = () => {
    try {
      const AudioCtx = window.AudioContext || (window as any).webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = "sine";
      osc.frequency.setValueAtTime(587.33, ctx.currentTime);
      osc.frequency.setValueAtTime(880, ctx.currentTime + 0.18);
      gain.gain.setValueAtTime(0.2, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + 0.6);
    } catch {}
  };

  useEffect(() => {
    let interval: NodeJS.Timeout | null = null;
    if (isRunning && timeLeft > 0) {
      interval = setInterval(() => {
        setTimeLeft((prev) => prev - 1);
      }, 1000);
    } else if (isRunning && timeLeft === 0) {
      setIsRunning(false);
      playPomodoroChime();

      if (pomodoroMode === "focus") {
        const nextCount = completedPomodoros + 1;
        setCompletedPomodoros(nextCount);
        const today = new Date().toDateString();
        localStorage.setItem(`crezca_pomodoro_count_${profile.id}_${today}`, String(nextCount));
        setPomodoroNotice("¡Tiempo de enfoque cumplido! Descansa 5 minutos para recargar energías.");
        setPomodoroMode("short_break");
        setTimeLeft(MODE_DURATIONS.short_break);
      } else {
        setPomodoroNotice("¡Descanso terminado! ¿Listo para sumergirte en otra sesión?");
        setPomodoroMode("focus");
        setTimeLeft(MODE_DURATIONS.focus);
      }
    }
    return () => {
      if (interval) clearInterval(interval);
    };
  }, [isRunning, timeLeft, pomodoroMode, completedPomodoros, profile.id]);

  const handleSwitchPomodoroMode = (mode: PomodoroMode) => {
    setPomodoroMode(mode);
    setTimeLeft(MODE_DURATIONS[mode]);
    setIsRunning(false);
    setPomodoroNotice(null);
  };

  const handleTogglePomodoro = () => {
    setIsRunning(!isRunning);
    setPomodoroNotice(null);
  };

  const handleResetPomodoro = () => {
    setIsRunning(false);
    setTimeLeft(MODE_DURATIONS[pomodoroMode]);
    setPomodoroNotice(null);
  };

  const handleSkipPomodoroPhase = () => {
    setIsRunning(false);
    setPomodoroNotice(null);
    if (pomodoroMode === "focus") {
      setPomodoroMode("short_break");
      setTimeLeft(MODE_DURATIONS.short_break);
    } else {
      setPomodoroMode("focus");
      setTimeLeft(MODE_DURATIONS.focus);
    }
  };

  const formatTimerNumber = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${String(mins).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
  };

  const pomodoroProgressPercent = Math.round(
    ((MODE_DURATIONS[pomodoroMode] - timeLeft) / MODE_DURATIONS[pomodoroMode]) * 100
  );

  // --- PRÓXIMOS EVENTOS DE LA ACADEMIA ---
  const [events, setEvents] = useState<AcademyEvent[]>([]);

  useEffect(() => {
    api.getAcademyEvents()
      .then((data) => setEvents(data || []))
      .catch(() => setEvents([]));
  }, []);

  const formatEventDate = (dateStr: string, timeStr: string) => {
    try {
      const [year, month, day] = dateStr.split("-").map(Number);
      const d = new Date(year, month - 1, day);
      const dayName = d.toLocaleDateString("es-ES", { weekday: "short" });
      const monthName = d.toLocaleDateString("es-ES", { month: "short" });
      const capitalizedDay = dayName.charAt(0).toUpperCase() + dayName.slice(1);
      return `${capitalizedDay} ${day} ${monthName} · ${timeStr} hrs`;
    } catch {
      return `${dateStr} · ${timeStr}`;
    }
  };

  return (
    <div className={styles.studentDashboardContainer}>
      {/* 1. MENSAJE DE BIENVENIDA (Limpio y directo, sin contenedor pesado) */}
      <div className={styles.cleanGreetingHeader}>
        <div className={styles.cleanGreetingLeft}>
          <div className={styles.dashboardTag}>
            <Sparkles size={14} />
            <span>Panel del Estudiante</span>
          </div>
          <h1 className={styles.cleanGreetingTitle}>
            ¡Hola, {profile.name}! 👋
          </h1>
          <p className={styles.cleanGreetingSubtitle}>
            Tienes {enrolledCourses.length} {enrolledCourses.length === 1 ? "curso activo" : "cursos activos"}. Retoma tus lecciones donde las dejaste y alcanza tus metas formativas hoy.
          </p>
        </div>

        <div className={styles.cleanGreetingRight}>
          <div className={styles.studentPlanCleanBadge}>
            <ShieldCheck size={18} className={styles.badgeShieldIcon} />
            <div className={styles.planInfoText}>
              <span className={styles.planLabel}>Membresía</span>
              <strong className={styles.planNameText}>{profile.planName || "Activa"}</strong>
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
          <div className={styles.statIconWrap} style={{ background: "rgba(5, 98, 239, 0.12)", color: "#0562ef" }}>
            <TrendingUp size={22} />
          </div>
          <div className={styles.statInfo}>
            <span className={styles.statLabel}>Progreso Promedio</span>
            <span className={styles.statValue}>{avgProgress}%</span>
            <span className={styles.statDesc}>Avance general en tus cursos</span>
          </div>
        </div>

        <div className={styles.statMetricCard}>
          <div className={styles.statIconWrap} style={{ background: "rgba(243, 173, 32, 0.12)", color: "#f3ad20" }}>
            <Clock size={22} />
          </div>
          <div className={styles.statInfo}>
            <span className={styles.statLabel}>Tiempo Dedicado</span>
            <span className={styles.statValue}>{estimatedHours}h</span>
            <span className={styles.statDesc}>Horas estimadas de formación</span>
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
      </div>

      {/* 3. ÚLTIMA CLASE QUE VIO / CONTINUAR DONDE LO DEJASTE */}
      {lastLesson && (
        <div className={styles.lastLessonCard}>
          <div className={styles.lastLessonMain}>
            <div className={styles.lastLessonCoverWrap}>
              <img
                src={lastLesson.lesson.cover || lastLesson.course.thumbnail || lastLesson.course.banner || "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80"}
                alt={lastLesson.lesson.title}
                className={styles.lastLessonCoverImg}
              />
            </div>
            <div className={styles.lastLessonInfo}>
              <div className={styles.lastLessonTag}>
                <Play size={12} fill="currentColor" />
                <span>{lastLesson.isFromStorage ? "Última clase vista" : "Siguiente clase sugerida"}</span>
              </div>
              <h3 className={styles.lastLessonTitle}>
                {lastLesson.topic?.title || lastLesson.lesson.title || "Clase actual"}
              </h3>
              <span className={styles.lastLessonCourseName}>
                Curso: <strong>{lastLesson.course.title}</strong> · Módulo: {lastLesson.section.title}
              </span>
            </div>
          </div>

          <button
            type="button"
            className={styles.lastLessonActionBtn}
            onClick={handleResumeLastLesson}
          >
            <Play size={15} fill="currentColor" />
            <span>Reanudar clase</span>
          </button>
        </div>
      )}

      {/* 4. BLOQUE INTERACTIVO: MODO POMODORO & PRÓXIMOS EVENTOS DE LA ACADEMIA */}
      <div className={styles.dashboardSplitGrid}>
        {/* WIDGET: MODO POMODORO */}
        <div className={styles.pomodoroCard}>
          <div className={styles.pomodoroHeader}>
            <div className={styles.pomodoroTitleRow}>
              <div className={styles.pomodoroIconWrap}>
                <Timer size={20} />
              </div>
              <div>
                <h3 className={styles.pomodoroTitle}>Modo Pomodoro · Enfoque</h3>
                <p className={styles.pomodoroSubtitle}>Bloques de estudio concentrado para maximizar tu aprendizaje</p>
              </div>
            </div>

            <div className={styles.pomodoroTabs}>
              <button
                type="button"
                className={`${styles.pomodoroTabBtn} ${pomodoroMode === "focus" ? styles.pomodoroTabBtnActive : ""}`}
                onClick={() => handleSwitchPomodoroMode("focus")}
              >
                Enfoque (25m)
              </button>
              <button
                type="button"
                className={`${styles.pomodoroTabBtn} ${pomodoroMode === "short_break" ? styles.pomodoroTabBtnActive : ""}`}
                onClick={() => handleSwitchPomodoroMode("short_break")}
              >
                Descanso (5m)
              </button>
              <button
                type="button"
                className={`${styles.pomodoroTabBtn} ${pomodoroMode === "long_break" ? styles.pomodoroTabBtnActive : ""}`}
                onClick={() => handleSwitchPomodoroMode("long_break")}
              >
                Largo (15m)
              </button>
            </div>
          </div>

          <div className={styles.pomodoroBody}>
            <div className={styles.pomodoroTimerBox}>
              <span className={styles.pomodoroClockNumber}>{formatTimerNumber(timeLeft)}</span>
              <span className={styles.pomodoroPhaseTag}>
                <Sparkles size={13} />
                {pomodoroMode === "focus"
                  ? "Bloque de Concentración Máxima"
                  : pomodoroMode === "short_break"
                  ? "Descanso Corto (Pausa activa)"
                  : "Descanso Extendido"}
              </span>
            </div>

            <div className={styles.pomodoroControls}>
              <button
                type="button"
                className={styles.pomodoroBtnPlay}
                onClick={handleTogglePomodoro}
                title={isRunning ? "Pausar temporizador" : "Iniciar temporizador"}
              >
                {isRunning ? (
                  <>
                    <Pause size={16} />
                    <span>Pausar</span>
                  </>
                ) : (
                  <>
                    <Play size={16} fill="currentColor" />
                    <span>Iniciar</span>
                  </>
                )}
              </button>

              <button
                type="button"
                className={styles.pomodoroBtnSecondary}
                onClick={handleResetPomodoro}
                title="Reiniciar temporizador"
              >
                <RotateCcw size={15} />
              </button>

              <button
                type="button"
                className={styles.pomodoroBtnSecondary}
                onClick={handleSkipPomodoroPhase}
                title="Saltar a la siguiente fase"
              >
                <SkipForward size={15} />
              </button>
            </div>
          </div>

          <div className={styles.pomodoroTrack}>
            <div className={styles.pomodoroFill} style={{ width: `${pomodoroProgressPercent}%` }} />
          </div>

          <div className={styles.pomodoroStatsRow}>
            <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
              <Flame size={14} color="#f97316" />
              <span>{completedPomodoros} {completedPomodoros === 1 ? "sesión completada" : "sesiones completadas"} hoy</span>
            </span>
            <span>{pomodoroNotice || "¡Sin distracciones, enfócate en tu clase!"}</span>
          </div>
        </div>

        {/* WIDGET: PRÓXIMOS EVENTOS DE LA ACADEMIA */}
        <div className={styles.eventsCard}>
          <div className={styles.eventsHeader}>
            <div className={styles.eventsTitleRow}>
              <div className={styles.eventsIconWrap}>
                <Calendar size={20} />
              </div>
              <div>
                <h3 className={styles.eventsTitle}>Próximos Eventos & Clases en Vivo</h3>
                <p className={styles.eventsSubtitle}>Mentorías grupales, talleres y sesiones con docentes</p>
              </div>
            </div>
          </div>

          <div className={styles.eventsList}>
            {events.length > 0 ? (
              events.map((evt) => (
                <div key={evt.id} className={styles.eventItem}>
                  <div className={styles.eventItemInfo}>
                    <div className={styles.eventItemMeta}>
                      <span className={styles.eventDateText}>
                        <Clock size={12} />
                        {formatEventDate(evt.date, evt.time)}
                      </span>
                      {evt.badge && (
                        <span style={{ fontSize: 10, padding: "2px 6px", borderRadius: 4, background: "rgba(119,71,255,0.15)", color: "var(--accent, #7747ff)", fontWeight: 700 }}>
                          {evt.badge}
                        </span>
                      )}
                    </div>
                    <h4 className={styles.eventItemTitle}>{evt.title}</h4>
                    {evt.instructor && (
                      <div className={styles.eventItemInstructor}>
                        {evt.instructor_avatar ? (
                          <img src={evt.instructor_avatar} alt={evt.instructor} className={styles.eventItemInstructorImg} />
                        ) : null}
                        <span>Anfitrión: <strong>{evt.instructor}</strong></span>
                      </div>
                    )}
                  </div>

                  {evt.meeting_url ? (
                    <a
                      href={evt.meeting_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className={styles.eventJoinBtn}
                      title="Unirse a la sesión en vivo"
                    >
                      <Video size={13} />
                      <span>Unirse</span>
                    </a>
                  ) : null}
                </div>
              ))
            ) : (
              <div style={{ textAlign: "center", padding: "24px 12px", color: "var(--text-muted)" }}>
                <Calendar size={32} style={{ opacity: 0.5, marginBottom: 8 }} />
                <p style={{ margin: 0, fontSize: 13 }}>No hay eventos próximos agendados para esta semana.</p>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* 5. SECCIÓN: CONTINUAR APRENDIENDO (CURSOS INSCRITOS) */}
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

      {/* 6. SI HAY CURSOS DISPONIBLES PARA ADQUIRIR */}
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
