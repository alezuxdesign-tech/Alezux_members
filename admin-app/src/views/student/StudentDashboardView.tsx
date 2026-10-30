import React, { useState, useEffect, useRef } from "react";
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
  Plus,
  Minus,
  Sliders,
  Check
} from "lucide-react";
import { MetricCard } from "../../components/arc/metric-card/metric-card";
import { Button } from "../../components/arc/button/button";
import { Badge } from "../../components/arc/badge/badge";
import { Skeleton } from "../../components/arc/skeleton";
import { Course, CourseSection, CourseLesson, CourseTopic, StudentProfile, AcademyEvent, api } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentDashboardViewProps {
  profile: StudentProfile;
  courses: Course[];
  onSelectCourse: (course: Course) => void;
  onResumeLesson?: (course: Course, section?: CourseSection | null, lesson?: CourseLesson | null, topic?: CourseTopic | null) => void;
  onNavigateTab: (tabId: "courses" | "community") => void;
  loading?: boolean;
}

interface LastLessonItem {
  course: Course;
  section: CourseSection;
  lesson: CourseLesson;
  topic?: CourseTopic;
  isFromStorage: boolean;
}

type PomodoroMode = "focus" | "short_break" | "long_break";

export const StudentDashboardView: React.FC<StudentDashboardViewProps> = ({
  profile,
  courses,
  onSelectCourse,
  onResumeLesson,
  onNavigateTab,
  loading = false,
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
    const saved = localStorage.getItem(`crezca_last_lesson_${profile.id}`)
      || localStorage.getItem("crezca_last_lesson_1")
      || localStorage.getItem("crezca_last_viewed_lesson");
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        const crs = enrolledCourses.find((c) => c.id === parsed.courseId) || courses.find((c) => c.id === parsed.courseId);
        if (crs && crs.sections) {
          for (const sec of crs.sections) {
            const les = sec.lessons?.find((l) => l.id === parsed.lessonId);
            if (les) {
              const top = les.subtopics?.find((sub) => sub.id === parsed.topicId)
                || les.topics?.find((t) => t.id === parsed.topicId);
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

  // --- MODO POMODORO (PERSONALIZABLE & CONCENTRACIÓN) ---
  const [customFocusMinutes, setCustomFocusMinutes] = useState<number>(() => {
    const saved = localStorage.getItem(`crezca_pomodoro_focus_min_${profile.id}`);
    return saved ? Math.max(1, parseInt(saved, 10)) : 25;
  });

  const [customBreakMinutes, setCustomBreakMinutes] = useState<number>(() => {
    const saved = localStorage.getItem(`crezca_pomodoro_break_min_${profile.id}`);
    return saved ? Math.max(1, parseInt(saved, 10)) : 5;
  });

  const [customLongBreakMinutes, setCustomLongBreakMinutes] = useState<number>(() => {
    const saved = localStorage.getItem(`crezca_pomodoro_long_break_min_${profile.id}`);
    return saved ? Math.max(1, parseInt(saved, 10)) : 15;
  });

  const getDurationForMode = (mode: PomodoroMode) => {
    if (mode === "focus") return customFocusMinutes * 60;
    if (mode === "short_break") return customBreakMinutes * 60;
    return customLongBreakMinutes * 60;
  };

  const [pomodoroMode, setPomodoroMode] = useState<PomodoroMode>("focus");
  const [timeLeft, setTimeLeft] = useState<number>(() => customFocusMinutes * 60);
  const [isRunning, setIsRunning] = useState<boolean>(false);
  const [pomodoroNotice, setPomodoroNotice] = useState<string | null>(null);

  // Modo edición de tiempo
  const [isEditingTime, setIsEditingTime] = useState<boolean>(false);
  const [customInputVal, setCustomInputVal] = useState<string>(String(customFocusMinutes));

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

  const targetEndTimeRef = useRef<number | null>(null);

  useEffect(() => {
    if (!isRunning) return;

    const checkTime = () => {
      if (!targetEndTimeRef.current) return;
      const diffMs = targetEndTimeRef.current - Date.now();
      const remaining = Math.max(0, Math.ceil(diffMs / 1000));
      setTimeLeft(remaining);

      if (remaining <= 0) {
        setIsRunning(false);
        targetEndTimeRef.current = null;
        playPomodoroChime();

        if (pomodoroMode === "focus") {
          const nextCount = completedPomodoros + 1;
          setCompletedPomodoros(nextCount);
          const today = new Date().toDateString();
          localStorage.setItem(`crezca_pomodoro_count_${profile.id}_${today}`, String(nextCount));
          setPomodoroNotice("¡Tiempo de enfoque cumplido! Descansa unos minutos para recargar energías.");
          setPomodoroMode("short_break");
          setTimeLeft(customBreakMinutes * 60);
        } else {
          setPomodoroNotice("¡Descanso terminado! ¿Listo para sumergirte en otra sesión?");
          setPomodoroMode("focus");
          setTimeLeft(customFocusMinutes * 60);
        }
      }
    };

    const interval = setInterval(checkTime, 1000);

    const handleVisibilityChange = () => {
      if (document.visibilityState === "visible") {
        checkTime();
      }
    };

    document.addEventListener("visibilitychange", handleVisibilityChange);
    window.addEventListener("focus", handleVisibilityChange);

    return () => {
      clearInterval(interval);
      document.removeEventListener("visibilitychange", handleVisibilityChange);
      window.removeEventListener("focus", handleVisibilityChange);
    };
  }, [isRunning, pomodoroMode, completedPomodoros, customBreakMinutes, customFocusMinutes, profile.id]);

  const handleSwitchPomodoroMode = (mode: PomodoroMode) => {
    setPomodoroMode(mode);
    setIsRunning(false);
    targetEndTimeRef.current = null;
    setPomodoroNotice(null);
    setIsEditingTime(false);
    const dur = mode === "focus" 
      ? customFocusMinutes 
      : mode === "short_break" 
      ? customBreakMinutes 
      : customLongBreakMinutes;
    setTimeLeft(dur * 60);
    setCustomInputVal(String(dur));
  };

  const handleTogglePomodoro = () => {
    if (!isRunning) {
      targetEndTimeRef.current = Date.now() + timeLeft * 1000;
      setIsRunning(true);
    } else {
      setIsRunning(false);
      targetEndTimeRef.current = null;
    }
    setPomodoroNotice(null);
    setIsEditingTime(false);
  };

  const handleResetPomodoro = () => {
    setIsRunning(false);
    targetEndTimeRef.current = null;
    setPomodoroNotice(null);
    setIsEditingTime(false);
    setTimeLeft(getDurationForMode(pomodoroMode));
  };

  const handleSkipPomodoroPhase = () => {
    setIsRunning(false);
    setPomodoroNotice(null);
    setIsEditingTime(false);
    if (pomodoroMode === "focus") {
      setPomodoroMode("short_break");
      setTimeLeft(customBreakMinutes * 60);
      setCustomInputVal(String(customBreakMinutes));
    } else {
      setPomodoroMode("focus");
      setTimeLeft(customFocusMinutes * 60);
      setCustomInputVal(String(customFocusMinutes));
    }
  };

  // Ajuste rápido de tiempo (+5 min / -5 min / +1 min / -1 min)
  const handleAdjustMinutes = (delta: number) => {
    setPomodoroNotice(null);
    if (pomodoroMode === "focus") {
      const next = Math.max(1, Math.min(180, customFocusMinutes + delta));
      setCustomFocusMinutes(next);
      setCustomInputVal(String(next));
      localStorage.setItem(`crezca_pomodoro_focus_min_${profile.id}`, String(next));
      if (!isRunning) {
        setTimeLeft(next * 60);
      } else {
        setTimeLeft((prev) => Math.max(1, prev + delta * 60));
      }
    } else if (pomodoroMode === "short_break") {
      const next = Math.max(1, Math.min(60, customBreakMinutes + delta));
      setCustomBreakMinutes(next);
      setCustomInputVal(String(next));
      localStorage.setItem(`crezca_pomodoro_break_min_${profile.id}`, String(next));
      if (!isRunning) {
        setTimeLeft(next * 60);
      } else {
        setTimeLeft((prev) => Math.max(1, prev + delta * 60));
      }
    } else {
      const next = Math.max(1, Math.min(90, customLongBreakMinutes + delta));
      setCustomLongBreakMinutes(next);
      setCustomInputVal(String(next));
      localStorage.setItem(`crezca_pomodoro_long_break_min_${profile.id}`, String(next));
      if (!isRunning) {
        setTimeLeft(next * 60);
      } else {
        setTimeLeft((prev) => Math.max(1, prev + delta * 60));
      }
    }
  };

  // Aplicar minutos desde presets rápidos
  const handleSetPresetMinutes = (mins: number) => {
    setPomodoroNotice(null);
    setIsEditingTime(false);
    if (pomodoroMode === "focus") {
      setCustomFocusMinutes(mins);
      setCustomInputVal(String(mins));
      localStorage.setItem(`crezca_pomodoro_focus_min_${profile.id}`, String(mins));
    } else if (pomodoroMode === "short_break") {
      setCustomBreakMinutes(mins);
      setCustomInputVal(String(mins));
      localStorage.setItem(`crezca_pomodoro_break_min_${profile.id}`, String(mins));
    } else {
      setCustomLongBreakMinutes(mins);
      setCustomInputVal(String(mins));
      localStorage.setItem(`crezca_pomodoro_long_break_min_${profile.id}`, String(mins));
    }
    setTimeLeft(mins * 60);
    setIsRunning(false);
  };

  // Aplicar minutos personalizados desde el input numérico
  const handleSaveCustomMinutes = () => {
    const val = parseInt(customInputVal, 10);
    if (!isNaN(val) && val >= 1 && val <= 180) {
      handleSetPresetMinutes(val);
    }
  };

  const formatTimerNumber = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${String(mins).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
  };

  const totalModeDuration = getDurationForMode(pomodoroMode);
  const pomodoroProgressPercent = Math.min(
    100,
    Math.max(0, Math.round(((totalModeDuration - timeLeft) / totalModeDuration) * 100))
  );

  // --- PRÓXIMOS EVENTOS DE LA ACADEMIA ---
  const [events, setEvents] = useState<AcademyEvent[]>([]);

  useEffect(() => {
    api.getAcademyEvents()
      .then((data) => {
        const activeEvents = (data || []).filter((e) => e.is_active !== false);
        setEvents(activeEvents);
      })
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
      {/* 1. ENCABEZADO LIMPIO DE BIENVENIDA (Idéntico a PageHeader del Admin) */}
      <div className={styles.cleanGreetingHeader}>
        <div className={styles.cleanGreetingLeft}>
          <h1 className={styles.cleanGreetingTitle}>
            ¡Hola, {profile.name}! 👋
          </h1>
          <p className={styles.cleanGreetingSubtitle}>
            Tienes {enrolledCourses.length} {enrolledCourses.length === 1 ? "curso activo" : "cursos activos"}. Retoma tus lecciones donde las dejaste y alcanza tus metas formativas hoy.
          </p>
        </div>

        <div className={styles.studentPlanCleanBadge}>
          <ShieldCheck size={16} className={styles.badgeShieldIcon} />
          <div className={styles.planInfoText}>
            <span className={styles.planLabel}>Membresía</span>
            <strong className={styles.planNameText}>{profile.planName || "Activa"}</strong>
          </div>
        </div>
      </div>

      {/* 2. REJILLA DE KPIS (100% IDÉNTICOS A LOS METRICCARD DEL ADMINISTRADOR) */}
      <div className={styles.metricsGrid}>
        <MetricCard
          label="Cursos Inscritos"
          value={enrolledCourses.length}
          context="Formaciones habilitadas con acceso"
          icon={<GraduationCap size={16} />}
        />

        <MetricCard
          label="Progreso Promedio"
          value={avgProgress}
          suffix="%"
          context="Avance acumulado en tus formaciones"
          icon={<TrendingUp size={16} />}
        />

        <MetricCard
          label="Tiempo Dedicado"
          value={estimatedHours}
          suffix="h"
          context="Horas estimadas de formación"
          icon={<Clock size={16} />}
        />

        <MetricCard
          label="Lecciones Finalizadas"
          value={totalCompleted}
          context="Temas completados con éxito"
          icon={<CheckCircle2 size={16} />}
        />
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

          <Button
            variant="primary"
            size="md"
            onClick={handleResumeLastLesson}
          >
            <Play size={15} fill="currentColor" />
            <span>Reanudar clase</span>
          </Button>
        </div>
      )}

      {/* 4. BLOQUE INTERACTIVO: MODO POMODORO PERSONALIZABLE & PRÓXIMOS EVENTOS */}
      <div className={styles.dashboardSplitGrid}>
        {/* WIDGET: MODO POMODORO CON PERSONALIZACIÓN DE TIEMPO */}
        <div className={styles.pomodoroCard}>
          <div className={styles.pomodoroHeader}>
            <div className={styles.pomodoroTitleRow}>
              <div className={styles.pomodoroIconWrap}>
                <Clock size={18} />
              </div>
              <div>
                <h3 className={styles.pomodoroTitle}>Modo Pomodoro</h3>
                <p className={styles.pomodoroSubtitle}>Enfoque y descansos para maximizar tu retención</p>
              </div>
            </div>

            <div className={styles.pomodoroTabs}>
              <button
                type="button"
                className={`${styles.pomodoroTabBtn} ${pomodoroMode === "focus" ? styles.pomodoroTabBtnActive : ""}`}
                onClick={() => handleSwitchPomodoroMode("focus")}
              >
                Enfoque
              </button>
              <button
                type="button"
                className={`${styles.pomodoroTabBtn} ${pomodoroMode === "short_break" ? styles.pomodoroTabBtnActive : ""}`}
                onClick={() => handleSwitchPomodoroMode("short_break")}
              >
                Descanso
              </button>
              <button
                type="button"
                className={`${styles.pomodoroTabBtn} ${pomodoroMode === "long_break" ? styles.pomodoroTabBtnActive : ""}`}
                onClick={() => handleSwitchPomodoroMode("long_break")}
              >
                Largo
              </button>
            </div>
          </div>

          <div className={styles.pomodoroBody}>
            <div className={styles.pomodoroTimerBox}>
              <div className={styles.pomodoroClockRow}>
                <span className={styles.pomodoroClockNumber}>{formatTimerNumber(timeLeft)}</span>
              </div>
            </div>

            <div className={styles.pomodoroControls}>
              <Button
                variant={isRunning ? "secondary" : "primary"}
                size="md"
                onClick={handleTogglePomodoro}
                title={isRunning ? "Pausar sesión" : "Iniciar sesión"}
              >
                {isRunning ? (
                  <>
                    <Pause size={15} />
                    <span>Pausar</span>
                  </>
                ) : (
                  <>
                    <Play size={15} fill="currentColor" />
                    <span>Iniciar</span>
                  </>
                )}
              </Button>

              <Button
                variant="secondary"
                size="md"
                onClick={handleResetPomodoro}
                title="Reiniciar temporizador"
              >
                <RotateCcw size={15} />
              </Button>

              <Button
                variant="secondary"
                size="md"
                onClick={handleSkipPomodoroPhase}
                title="Saltar a la siguiente fase"
              >
                <SkipForward size={15} />
              </Button>
            </div>
          </div>

          <div className={styles.pomodoroTrack}>
            <div className={styles.pomodoroFill} style={{ width: `${pomodoroProgressPercent}%` }} />
          </div>

          <div className={styles.pomodoroStatsRow}>
            <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
              <Flame size={14} color="var(--accent)" />
              <span>{completedPomodoros} {completedPomodoros === 1 ? "sesión completada" : "sesiones completadas"} hoy</span>
            </span>
            <span>{pomodoroNotice || "Estudia concentrado y sin distracciones"}</span>
          </div>
        </div>

        {/* WIDGET: PRÓXIMOS EVENTOS DE LA ACADEMIA */}
        <div className={styles.eventsCard}>
          <div className={styles.eventsHeader}>
            <div className={styles.eventsTitleRow}>
              <div className={styles.eventsIconWrap}>
                <Calendar size={18} />
              </div>
              <div>
                <h3 className={styles.eventsTitle}>Próximos Eventos</h3>
                <p className={styles.eventsSubtitle}>Mentorías, talleres y clases en vivo de la academia</p>
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
                        <Badge variant="accent" size="sm">
                          {evt.badge}
                        </Badge>
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
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => window.open(evt.meeting_url, "_blank")}
                      title="Unirse a la sesión en vivo"
                    >
                      <Video size={13} />
                      <span>Unirse</span>
                    </Button>
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

        {loading ? (
          <div className={styles.continueCardsGrid}>
            {[1, 2].map((i) => (
              <div key={i} className={styles.continueCard} style={{ pointerEvents: "none" }}>
                <div className={styles.continueCardCover}>
                  <Skeleton width="100%" height="100%" variant="rectangular" />
                </div>
                <div className={styles.continueCardBody}>
                  <Skeleton width="75%" height={18} />
                  <Skeleton width="90%" height={12} style={{ marginTop: 8 }} />
                  <Skeleton width="100%" height={6} variant="rounded" style={{ marginTop: 14 }} />
                  <div style={{ marginTop: 12 }}>
                    <Skeleton width={130} height={32} variant="rounded" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : enrolledCourses.length > 0 ? (
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

                    <Button
                      variant="primary"
                      size="sm"
                      onClick={() => onSelectCourse(c)}
                    >
                      <Play size={14} fill="currentColor" />
                      <span>{prog > 0 ? "Continuar lección" : "Comenzar curso"}</span>
                    </Button>
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
            <Button
              variant="primary"
              size="md"
              onClick={() => onNavigateTab("courses")}
            >
              <BookOpen size={16} />
              <span>Explorar catálogo</span>
            </Button>
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
          <Button
            variant="primary"
            size="md"
            onClick={() => onNavigateTab("courses")}
          >
            <span>Ver catálogo completo</span>
            <ArrowRight size={16} />
          </Button>
        </section>
      )}
    </div>
  );
};
