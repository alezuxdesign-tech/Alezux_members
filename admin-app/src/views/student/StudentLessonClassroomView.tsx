import React, { useState, useMemo, useEffect } from "react";
import { 
  ArrowLeft, 
  CheckCircle2, 
  Circle, 
  Play, 
  ChevronLeft, 
  ChevronRight, 
  Download, 
  FileText, 
  Sparkles, 
  Clock, 
  BookOpen, 
  Layers,
  ChevronDown,
  ChevronRight as ChevronRightIcon,
  Video,
  HelpCircle,
  Users,
  Check,
  X,
  RotateCcw,
  Award
} from "lucide-react";
import { 
  Course, 
  CourseSection, 
  CourseLesson, 
  CourseTopic, 
  CourseSubtopic, 
  CourseQuestion,
  CourseFileAttachment,
  CourseInstructor,
  api
} from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentLessonClassroomViewProps {
  course: Course;
  currentSection: CourseSection;
  currentLesson: CourseLesson;
  initialTopic?: CourseTopic;
  completedTopicIds: string[];
  studentId?: number;
  onToggleCompleteTopic: (topicId: string) => void;
  onBackToCourse: () => void;
  onSwitchModule: (sec: CourseSection, les: CourseLesson, top?: CourseTopic) => void;
  academyLogo?: string;
}

interface PlayableItem {
  id: string;
  title: string;
  description?: string;
  video_url?: string;
  duration?: string;
  cover?: string;
  files?: CourseFileAttachment[];
  questions?: CourseQuestion[];
  isSubtopic: boolean;
  parentTopicTitle?: string;
  parentTopicId?: string;
}

export const StudentLessonClassroomView: React.FC<StudentLessonClassroomViewProps> = ({
  course,
  currentSection,
  currentLesson,
  initialTopic,
  completedTopicIds,
  studentId,
  onToggleCompleteTopic,
  onBackToCourse,
  onSwitchModule,
  academyLogo,
}) => {
  const effectiveLogo = academyLogo || localStorage.getItem("crezca_academy_logo") || "";
  const authorName = course.instructors?.[0]?.name || "Docente Titular";
  const [isOtherModulesOpen, setIsOtherModulesOpen] = useState(false);
  // Obtener lista completa de topics de la lección/módulo actual
  const topics: CourseTopic[] = useMemo(() => {
    if (currentLesson.topics && currentLesson.topics.length > 0) {
      return currentLesson.topics;
    }
    // Si la lección es el propio topic
    return [
      {
        id: currentLesson.id,
        title: currentLesson.title,
        description: currentLesson.description,
        video_url: currentLesson.video_url,
        duration: currentLesson.duration || "15m",
        cover: currentLesson.cover,
        files: currentLesson.files || [],
        questions: currentLesson.questions || [],
        subtopics: currentLesson.subtopics || [],
      },
    ];
  }, [currentLesson]);

  // Lista plana de todos los elementos reproducibles (topics principales y subtopics)
  const allPlayableItems = useMemo<PlayableItem[]>(() => {
    const list: PlayableItem[] = [];

    topics.forEach((top) => {
      const subs = top.subtopics && top.subtopics.length > 0 ? top.subtopics : [];

      if (subs.length > 0) {
        // Si el topic padre tiene video propio, lo incluimos como clase introductoria
        if (top.video_url && top.video_url.trim() !== "") {
          list.push({
            id: top.id,
            title: top.title,
            description: top.description,
            video_url: top.video_url,
            duration: top.duration,
            cover: top.cover,
            files: top.files,
            questions: top.questions,
            isSubtopic: false,
          });
        }
        // Agregamos todas las sub-clases hijas
        subs.forEach((sub) => {
          list.push({
            id: sub.id,
            title: sub.title,
            description: sub.description,
            video_url: sub.video_url,
            duration: sub.duration,
            cover: sub.cover,
            files: sub.files,
            questions: sub.questions,
            isSubtopic: true,
            parentTopicTitle: top.title,
            parentTopicId: top.id,
          });
        });
      } else {
        list.push({
          id: top.id,
          title: top.title,
          description: top.description,
          video_url: top.video_url,
          duration: top.duration,
          cover: top.cover,
          files: top.files,
          questions: top.questions,
          isSubtopic: false,
        });
      }
    });

    return list;
  }, [topics]);

  // ID del elemento activo actualmente (puede ser topic o subtopic)
  const [activeItemId, setActiveItemId] = useState<string>(() => {
    if (initialTopic) {
      const found = allPlayableItems.find((it) => it.id === initialTopic.id);
      if (found) return found.id;
      if (initialTopic.subtopics && initialTopic.subtopics.length > 0) {
        return initialTopic.subtopics[0].id;
      }
    }
    return allPlayableItems[0]?.id || "";
  });

  // Al cambiar de lección / topic, hacer scroll arriba suavemente
  useEffect(() => {
    const scrollEl = document.querySelector('[data-scroll-container="main"]') as HTMLElement | null;
    if (scrollEl) {
      scrollEl.scrollTo({ top: 0, behavior: "smooth" });
    } else {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }, [activeItemId]);

  // Sincronizar ID activo al cambiar módulo o initialTopic
  useEffect(() => {
    if (initialTopic) {
      const match = allPlayableItems.find((it) => it.id === initialTopic.id);
      if (match) {
        setActiveItemId(match.id);
        return;
      }
      if (initialTopic.subtopics && initialTopic.subtopics.length > 0) {
        setActiveItemId(initialTopic.subtopics[0].id);
        return;
      }
    }
    if (allPlayableItems.length > 0 && !allPlayableItems.some((it) => it.id === activeItemId)) {
      setActiveItemId(allPlayableItems[0].id);
    }
  }, [currentLesson.id, initialTopic?.id, allPlayableItems]);

  // Item seleccionado actualmente
  const currentItem = useMemo<PlayableItem | null>(() => {
    const found = allPlayableItems.find((t) => t.id === activeItemId);
    if (found) return found;
    // Si activeItemId apunta a un topic padre con subtopics
    const parentTopic = topics.find((t) => t.id === activeItemId);
    if (parentTopic?.subtopics && parentTopic.subtopics.length > 0) {
      const firstSub = allPlayableItems.find((it) => it.id === parentTopic.subtopics![0].id);
      if (firstSub) return firstSub;
    }
    return allPlayableItems[0] || null;
  }, [allPlayableItems, activeItemId, topics]);

  // Persistir en tiempo real la última clase vista en localStorage
  useEffect(() => {
    if (!course || !currentLesson) return;
    try {
      const payload = {
        courseId: course.id,
        sectionId: currentSection?.id,
        lessonId: currentLesson.id,
        topicId: activeItemId || initialTopic?.id,
        updatedAt: Date.now(),
      };
      if (studentId) {
        localStorage.setItem(`crezca_last_lesson_${studentId}`, JSON.stringify(payload));
      }
      localStorage.setItem("crezca_last_lesson_1", JSON.stringify(payload));
      localStorage.setItem("crezca_last_viewed_lesson", JSON.stringify(payload));
    } catch (e) {
      console.warn("Error guardando última clase vista:", e);
    }
  }, [course.id, currentSection?.id, currentLesson.id, activeItemId, initialTopic?.id, studentId]);

  // Estado del acordeón de subtopics en el sidebar
  const [expandedTopicIds, setExpandedTopicIds] = useState<Record<string, boolean>>(() => {
    const map: Record<string, boolean> = {};
    topics.forEach((t) => {
      if (t.subtopics && t.subtopics.length > 0) {
        map[t.id] = true;
      }
    });
    return map;
  });

  const toggleTopicExpanded = (topicId: string, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    setExpandedTopicIds((prev) => ({
      ...prev,
      [topicId]: !prev[topicId],
    }));
  };

  // Navegación secuencial Anterior / Siguiente
  const activeIndex = allPlayableItems.findIndex((t) => t.id === currentItem?.id);
  const prevItem = activeIndex > 0 ? allPlayableItems[activeIndex - 1] : null;
  const nextItem = activeIndex < allPlayableItems.length - 1 ? allPlayableItems[activeIndex + 1] : null;

  const isCurrentCompleted = currentItem ? completedTopicIds.includes(currentItem.id) : false;

  // Tabs de contenido: temario, descripción, archivos, preguntas/quiz o docentes
  const [contentTab, setContentTab] = useState<"syllabus" | "description" | "files" | "quiz" | "instructors">("description");

  // Estado del Quiz interactivo
  const currentQuestions = currentItem?.questions || [];
  const [quizAnswers, setQuizAnswers] = useState<Record<string, number>>({});
  const [quizSubmitted, setQuizSubmitted] = useState<boolean>(false);
  const [quizScore, setQuizScore] = useState<{ correct: number; total: number; percentage: number } | null>(null);

  // Reiniciar estado del quiz al cambiar de lección/sub-clase
  useEffect(() => {
    setQuizAnswers({});
    setQuizSubmitted(false);
    setQuizScore(null);
  }, [currentItem?.id]);

  const handleSelectQuizOption = (questionId: string, optionIndex: number) => {
    if (quizSubmitted) return;
    setQuizAnswers((prev) => ({
      ...prev,
      [questionId]: optionIndex,
    }));
  };

  const handleCheckQuiz = () => {
    if (!currentQuestions.length) return;
    let correctCount = 0;
    currentQuestions.forEach((q) => {
      if (quizAnswers[q.id] === q.correctIndex) {
        correctCount++;
      }
    });
    const total = currentQuestions.length;
    const percentage = Math.round((correctCount / total) * 100);
    setQuizScore({ correct: correctCount, total, percentage });
    setQuizSubmitted(true);

    // Si aprueba con >= 70% y no estaba completada, marcarla como completada automáticamente
    if (percentage >= 70 && currentItem && !completedTopicIds.includes(currentItem.id)) {
      onToggleCompleteTopic(currentItem.id);
    }
  };

  const handleResetQuiz = () => {
    setQuizAnswers({});
    setQuizSubmitted(false);
    setQuizScore(null);
  };

  // Lista de docentes del curso
  const instructors: CourseInstructor[] = useMemo(() => {
    if (course.instructors && course.instructors.length > 0) {
      return course.instructors;
    }
    return [
      {
        id: "default-docente",
        name: "Docente Titular",
        role: "Instructor del Curso",
        bio: "Especialista a cargo de la metodología y del contenido formativo de este programa.",
      },
    ];
  }, [course.instructors]);

  // Cache de embeds dinámicos (VdoCipher shortcodes, iframes dinámicos, etc.)
  const [dynamicEmbeds, setDynamicEmbeds] = useState<Record<string, {
    loading: boolean;
    html?: string;
    iframe_url?: string;
    error?: string;
  }>>({});

  // Detectar si el video_url actual es un shortcode o ID protegido de VdoCipher
  const vdoDetection = useMemo(() => {
    if (!currentItem?.video_url) return { isVdo: false, cleanRaw: "" };
    // Limpiar entidades HTML por si vienen codificadas
    const raw = currentItem.video_url
      .replace(/&quot;/g, '"')
      .replace(/&#39;/g, "'")
      .replace(/&amp;/g, '&')
      .trim();

    // 1. Shortcode [vdo id="..."]
    const vdoShortcodeMatch = raw.match(/\[vdo\s+id=["']?([a-zA-Z0-9_-]+)["']?[^\]]*\]/i);
    if (vdoShortcodeMatch) {
      return { isVdo: true, cleanRaw: raw, vdoId: vdoShortcodeMatch[1] };
    }

    // 2. Cualquier shortcode de WordPress entre corchetes [ ... ]
    if (raw.startsWith('[') && raw.endsWith(']')) {
      return { isVdo: true, cleanRaw: raw };
    }

    // 3. ID directo de VdoCipher (32 caracteres alfanuméricos)
    if (/^[a-f0-9]{32}$/i.test(raw)) {
      return { isVdo: true, cleanRaw: raw, vdoId: raw };
    }

    // 4. vdo:ID
    if (/^vdo:[a-zA-Z0-9_-]+$/i.test(raw)) {
      return { isVdo: true, cleanRaw: raw };
    }

    return { isVdo: false, cleanRaw: raw };
  }, [currentItem?.video_url]);

  // Cargar dinámicamente embed de VdoCipher / WordPress cuando sea necesario
  useEffect(() => {
    if (!vdoDetection.isVdo || !vdoDetection.cleanRaw) return;
    const key = vdoDetection.cleanRaw;

    // Si ya existe y está cargando o ya resolvió (con o sin error), no re-solicitar
    if (dynamicEmbeds[key]) {
      return;
    }

    setDynamicEmbeds((prev) => ({
      ...prev,
      [key]: { loading: true },
    }));

    api.renderVideoEmbed(key)
      .then((res) => {
        if (res && res.success && (res.iframe_url || res.html)) {
          setDynamicEmbeds((prev) => ({
            ...prev,
            [key]: {
              loading: false,
              iframe_url: res.iframe_url,
              html: res.html,
            },
          }));
        } else {
          setDynamicEmbeds((prev) => ({
            ...prev,
            [key]: {
              loading: false,
              error: res?.message || "No se pudo cargar el video protegido. Verifica la configuración de VdoCipher.",
            },
          }));
        }
      })
      .catch(() => {
        setDynamicEmbeds((prev) => ({
          ...prev,
          [key]: {
            loading: false,
            error: "Error de conexión al cargar el reproductor de video.",
          },
        }));
      });
  }, [vdoDetection, dynamicEmbeds]);

  // Formateador de Embed de Video
  const renderVideoPlayer = () => {
    if (!currentItem || !currentItem.video_url || currentItem.video_url.trim() === "") {
      return (
        <div className={styles.videoPlaceholderContainer}>
          <div className={styles.videoPlaceholderContent}>
            <div className={styles.videoPlaceholderPlayIcon}>
              <Video size={42} />
            </div>
            <h3 className={styles.videoPlaceholderTitle}>{currentItem?.title || "Lección del módulo"}</h3>
            <p className={styles.videoPlaceholderSubtitle}>
              Video explicativo disponible para reproducción • Duración estimada: {currentItem?.duration || "15m"}
            </p>
          </div>
        </div>
      );
    }

    const rawUrl = currentItem.video_url
      .replace(/&quot;/g, '"')
      .replace(/&#39;/g, "'")
      .trim();

    // 1. YouTube (Embed, Watch, Shorts, YouTu.be)
    const ytMatch = rawUrl.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/);
    if (ytMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://www.youtube.com/embed/${ytMatch[1]}?autoplay=0&rel=0&modestbranding=1`}
            title={currentItem.title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 2. Vimeo
    const vimeoMatch = rawUrl.match(/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)|player\.vimeo\.com\/video\/(\d+))/);
    const vimeoId = vimeoMatch ? (vimeoMatch[1] || vimeoMatch[2]) : null;
    if (vimeoId) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://player.vimeo.com/video/${vimeoId}?title=0&byline=0&portrait=0`}
            title={currentItem.title}
            allow="autoplay; fullscreen; picture-in-picture"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 3. Loom
    const loomMatch = rawUrl.match(/loom\.com\/(?:share|embed)\/([a-zA-Z0-9_-]+)/);
    if (loomMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://www.loom.com/embed/${loomMatch[1]}`}
            title={currentItem.title}
            allow="autoplay; fullscreen; picture-in-picture"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 4. Wistia
    const wistiaMatch = rawUrl.match(/(?:wistia\.com\/medias|fast\.wistia\.net\/embed\/iframe)\/([a-zA-Z0-9_-]+)/);
    if (wistiaMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://fast.wistia.net/embed/iframe/${wistiaMatch[1]}`}
            title={currentItem.title}
            allow="autoplay; fullscreen; picture-in-picture"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 5. Bunny.net Stream
    const bunnyMatch = rawUrl.match(/(?:iframe\.mediadelivery\.net\/embed|video\.bunnycdn\.com\/play)\/([a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+)/);
    if (bunnyMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://iframe.mediadelivery.net/embed/${bunnyMatch[1]}?autoplay=false&preload=true`}
            title={currentItem.title}
            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 6. VdoCipher Direct Player URL (si la URL directa fue provista)
    if (rawUrl.includes("player.vdocipher.com/")) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={rawUrl}
            title={currentItem.title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 7. Video HTML5 Directo (MP4, WebM, OGG, MOV)
    const lowerUrl = rawUrl.toLowerCase();
    if (
      lowerUrl.endsWith(".mp4") || lowerUrl.includes(".mp4?") ||
      lowerUrl.endsWith(".webm") || lowerUrl.includes(".webm?") ||
      lowerUrl.endsWith(".ogg") || lowerUrl.includes(".ogg?") ||
      lowerUrl.endsWith(".mov") || lowerUrl.includes(".mov?")
    ) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <video
            controls
            src={rawUrl}
            poster={currentItem.cover}
            className={styles.videoHtmlTag}
          />
        </div>
      );
    }

    // 8. Raw Iframe tag en el campo de texto (ej: <iframe src="..."/>)
    if (rawUrl.startsWith("<iframe") || rawUrl.includes("<iframe")) {
      const iframeSrcMatch = rawUrl.match(/src=["']([^"']+)["']/i);
      if (iframeSrcMatch) {
        return (
          <div className={styles.videoResponsiveWrapper}>
            <iframe
              src={iframeSrcMatch[1]}
              title={currentItem.title}
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
              allowFullScreen
              className={styles.videoIframe}
            />
          </div>
        );
      }
      return (
        <div
          className={styles.videoResponsiveWrapper}
          dangerouslySetInnerHTML={{ __html: rawUrl }}
        />
      );
    }

    // 9. Shortcode VdoCipher [vdo id="..."], shortcodes WordPress o ID hexadecimal
    if (vdoDetection.isVdo) {
      const embedState = dynamicEmbeds[vdoDetection.cleanRaw];

      if (!embedState || embedState.loading) {
        return (
          <div className={styles.videoLoadingContainer}>
            <div className={styles.videoLoadingSpinner} />
            <h4 className={styles.videoLoadingTitle}>Cargando reproductor protegido...</h4>
            <p className={styles.videoLoadingSubtitle}>Conectando con el servidor seguro de VdoCipher</p>
          </div>
        );
      }

      if (embedState.error) {
        return (
          <div className={styles.videoErrorContainer}>
            <div className={styles.videoErrorIcon}>
              <Video size={36} />
            </div>
            <h4 className={styles.videoErrorTitle}>No se pudo reproducir este video</h4>
            <p className={styles.videoErrorSubtitle}>{embedState.error}</p>
            <button
              type="button"
              className={styles.videoRetryBtn}
              onClick={() => {
                setDynamicEmbeds((prev) => {
                  const copy = { ...prev };
                  delete copy[vdoDetection.cleanRaw];
                  return copy;
                });
              }}
            >
              <RotateCcw size={14} /> Reintentar carga
            </button>
          </div>
        );
      }

      if (embedState.iframe_url) {
        return (
          <div className={styles.videoResponsiveWrapper}>
            <iframe
              src={embedState.iframe_url}
              title={currentItem.title}
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
              allowFullScreen
              className={styles.videoIframe}
            />
          </div>
        );
      }

      if (embedState.html) {
        return (
          <div
            className={styles.videoEmbedHtmlContainer}
            dangerouslySetInnerHTML={{ __html: embedState.html }}
          />
        );
      }
    }

    // 10. Fallback Iframe genérico: SÓLO si es una URL válida con http://, https:// o //
    if (rawUrl.startsWith("http://") || rawUrl.startsWith("https://") || rawUrl.startsWith("//")) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={rawUrl}
            title={currentItem.title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // 11. Formato no reconocido (evita enviar rutas relativas a iframe y causar 404)
    return (
      <div className={styles.videoErrorContainer}>
        <div className={styles.videoErrorIcon}>
          <Video size={36} />
        </div>
        <h4 className={styles.videoErrorTitle}>Formato de video no compatible</h4>
        <p className={styles.videoErrorSubtitle}>
          El texto registrado no corresponde a una URL válida ni a un shortcode de video soportado.
        </p>
      </div>
    );
  };

  const completedInModule = allPlayableItems.filter((t) => completedTopicIds.includes(t.id)).length;
  const modulePercentage = allPlayableItems.length > 0 ? Math.round((completedInModule / allPlayableItems.length) * 100) : 0;

  return (
    <div className={styles.classroomContainer}>
      {/* 1. BARRA SUPERIOR DE NAVEGACIÓN DEL AULA */}
      <header className={styles.classroomHeader}>
        <div className={styles.classroomHeaderLeft}>
          <button 
            type="button" 
            className={styles.classroomBackBtn}
            onClick={onBackToCourse}
          >
            <ArrowLeft size={16} />
            <span>Volver al curso</span>
          </button>
          <div className={styles.classroomBreadcrumb}>
            <span className={styles.classroomCourseName}>{course.title}</span>
            <span className={styles.classroomDivider}>/</span>
            <span className={styles.classroomModuleName}>{currentLesson.title}</span>
            {currentItem?.isSubtopic && currentItem.parentTopicTitle && (
              <>
                <span className={styles.classroomDivider}>/</span>
                <span className={styles.classroomCourseName}>{currentItem.parentTopicTitle}</span>
              </>
            )}
          </div>
        </div>

        <div className={styles.classroomHeaderRight}>
          <div className={styles.classroomModuleBadge}>
            <CheckCircle2 size={14} className={styles.badgeCheckIcon} />
            <span>
              {completedInModule} de {allPlayableItems.length} completadas ({modulePercentage}%)
            </span>
          </div>
        </div>
      </header>

      {/* 2. REJILLA PRINCIPAL DEL AULA: REPRODUCTOR (IZQ) Y LISTA DE TOPICS (DER) */}
      <div className={styles.classroomLayout}>
        {/* COLUMNA IZQUIERDA: REPRODUCTOR Y CONTENIDO */}
        <main className={styles.classroomMainColumn}>
          {/* Contenedor del Video */}
          <div className={styles.playerWrapper}>
            {renderVideoPlayer()}
          </div>

          {/* Fila de Título y Acciones de la Lección */}
          <div className={styles.topicActionHeader}>
            <div className={styles.topicMetaInfo}>
              <div className={styles.topicMetaModuleHint}>
                <span className={styles.metaModuleName}>{currentLesson.title}</span>
                <span className={styles.metaDot}>•</span>
                <span className={styles.metaProgressText}>{completedInModule}/{allPlayableItems.length} completadas ({modulePercentage}%)</span>
              </div>
              <h1 className={styles.activeTopicTitle}>
                {currentItem?.title || "Lección del módulo"}
              </h1>
            </div>

            {/* Grupo unificado de botones de acción */}
            <div className={styles.topicActionsButtonGroup}>
              {/* Botón Anterior */}
              {prevItem && (
                <button
                  type="button"
                  className={styles.classroomNavBtn}
                  onClick={() => {
                    setActiveItemId(prevItem.id);
                    window.scrollTo({ top: 0, behavior: "smooth" });
                  }}
                  title={`Clase anterior: ${prevItem.title}`}
                >
                  <ChevronLeft size={16} />
                  <span>Anterior</span>
                </button>
              )}

              {/* Botón de Marcar como Completado */}
              {currentItem && (
                <button
                  type="button"
                  className={[
                    styles.completeToggleBtn,
                    isCurrentCompleted ? styles.completeToggleBtnDone : "",
                  ].join(" ")}
                  onClick={() => onToggleCompleteTopic(currentItem.id)}
                >
                  <CheckCircle2 size={16} />
                  <span>{isCurrentCompleted ? "✓ Completada" : "Completar clase"}</span>
                </button>
              )}

              {/* Botón Siguiente */}
              {nextItem && (
                <button
                  type="button"
                  className={[styles.classroomNavBtn, styles.classroomNavBtnNext].join(" ")}
                  onClick={() => {
                    setActiveItemId(nextItem.id);
                    window.scrollTo({ top: 0, behavior: "smooth" });
                  }}
                  title={`Siguiente clase: ${nextItem.title}`}
                >
                  <span>Siguiente clase</span>
                  <ChevronRight size={16} />
                </button>
              )}
            </div>
          </div>

          {/* Pestañas de Temario (Móvil), Descripción, Archivos, Quiz y Docentes */}
          <div className={styles.topicTabsContainer}>
            <div className={styles.topicTabsList}>
              {/* Pestaña Temario (visible en móvil y tablet) */}
              <button
                type="button"
                className={[
                  styles.topicTabBtn,
                  styles.syllabusTabBtn,
                  contentTab === "syllabus" ? styles.topicTabBtnActive : "",
                ].join(" ")}
                onClick={() => setContentTab("syllabus")}
              >
                <Layers size={15} />
                <span>Temario ({allPlayableItems.length})</span>
              </button>

              <button
                type="button"
                className={[
                  styles.topicTabBtn,
                  contentTab === "description" ? styles.topicTabBtnActive : "",
                ].join(" ")}
                onClick={() => setContentTab("description")}
              >
                <FileText size={15} />
                <span>Descripción & Notas</span>
              </button>
              {(() => {
                const filesCount = currentItem?.files?.length || 0;
                const hasFiles = filesCount > 0;
                return (
                  <button
                    type="button"
                    className={[
                      styles.topicTabBtn,
                      contentTab === "files" ? styles.topicTabBtnActive : "",
                      hasFiles ? styles.topicTabBtnFilesHighlight : "",
                    ].join(" ")}
                    onClick={() => setContentTab("files")}
                    title={hasFiles ? `¡Esta clase tiene ${filesCount} recurso(s) descargable(s)!` : "Recursos de la clase"}
                  >
                    <Download size={15} style={hasFiles ? { color: "var(--accent, #7747ff)" } : undefined} />
                    <span>Recursos & Descargas</span>
                    {hasFiles ? (
                      <span className={styles.filesHighlightBadge}>
                        ¡{filesCount} {filesCount === 1 ? "recurso" : "recursos"}!
                      </span>
                    ) : (
                      <span>(0)</span>
                    )}
                  </button>
                );
              })()}
              <button
                type="button"
                className={[
                  styles.topicTabBtn,
                  contentTab === "quiz" ? styles.topicTabBtnActive : "",
                ].join(" ")}
                onClick={() => setContentTab("quiz")}
              >
                <HelpCircle size={15} />
                <span>Preguntas / Quiz ({currentQuestions.length})</span>
              </button>
              <button
                type="button"
                className={[
                  styles.topicTabBtn,
                  contentTab === "instructors" ? styles.topicTabBtnActive : "",
                ].join(" ")}
                onClick={() => setContentTab("instructors")}
              >
                <Users size={15} />
                <span>Docente(s) ({instructors.length})</span>
              </button>
            </div>

            <div className={styles.topicTabBody}>
              {/* TAB 0: TEMARIO (Visible en móvil y tablet) */}
              {contentTab === "syllabus" && (
                <div className={styles.mobileSyllabusTabContent}>
                  {/* Selector rápido de otros módulos si hay más de 1 */}
                  {course.sections && course.sections.length > 1 && (
                    <div className={styles.mobileModuleSelectorWrap}>
                      <span className={styles.mobileModuleSelectorLabel}>Módulo:</span>
                      <select
                        className={styles.mobileModuleSelect}
                        value={currentLesson.id}
                        onChange={(e) => {
                          const targetLesId = e.target.value;
                          for (const sec of course.sections || []) {
                            const foundLes = sec.lessons?.find((l) => l.id === targetLesId);
                            if (foundLes) {
                              const first = foundLes.topics && foundLes.topics.length > 0 ? foundLes.topics[0] : (foundLes as unknown as CourseTopic);
                              onSwitchModule(sec, foundLes, first);
                              break;
                            }
                          }
                        }}
                      >
                        {course.sections.map((sec) => (
                          <optgroup key={sec.id} label={sec.title}>
                            {sec.lessons?.map((les) => (
                              <option key={les.id} value={les.id}>
                                {les.title}
                              </option>
                            ))}
                          </optgroup>
                        ))}
                      </select>
                    </div>
                  )}

                  <div className={styles.topicsScrollList}>
                    {topics.map((top) => {
                      const hasSubtopics = top.subtopics && top.subtopics.length > 0;
                      const isExpanded = !!expandedTopicIds[top.id];

                      if (hasSubtopics) {
                        const subtopics = top.subtopics!;
                        const completedSubs = subtopics.filter((s) => completedTopicIds.includes(s.id)).length;
                        const isParentActive = currentItem?.id === top.id || subtopics.some((s) => s.id === currentItem?.id);

                        return (
                          <div key={top.id} className={styles.topicAccordionWrapper}>
                            <div
                              className={[
                                styles.topicAccordionHeader,
                                isParentActive ? styles.topicAccordionHeaderActive : "",
                              ].join(" ")}
                              onClick={() => toggleTopicExpanded(top.id)}
                            >
                              <div className={styles.topicAccordionMeta}>
                                <h4 className={styles.topicAccordionTitle}>{top.title}</h4>
                                <span className={styles.topicAccordionCounter}>
                                  {completedSubs}/{subtopics.length} terminado
                                </span>
                              </div>
                              <div className={styles.topicAccordionChevron}>
                                {isExpanded ? <ChevronDown size={16} /> : <ChevronRightIcon size={16} />}
                              </div>
                            </div>

                            {isExpanded && (
                              <div className={styles.topicAccordionBody}>
                                {top.video_url && top.video_url.trim() !== "" && (
                                  <ClassCardItem
                                    id={top.id}
                                    title={top.title}
                                    cover={top.cover}
                                    author={authorName}
                                    isActive={currentItem?.id === top.id}
                                    isCompleted={completedTopicIds.includes(top.id)}
                                    effectiveLogo={effectiveLogo}
                                    courseTitle={course.title}
                                    onClick={() => {
                                      setActiveItemId(top.id);
                                      window.scrollTo({ top: 0, behavior: "smooth" });
                                    }}
                                    onToggleComplete={() => onToggleCompleteTopic(top.id)}
                                  />
                                )}

                                {subtopics.map((sub) => (
                                  <ClassCardItem
                                    key={sub.id}
                                    id={sub.id}
                                    title={sub.title}
                                    cover={sub.cover}
                                    author={authorName}
                                    isActive={currentItem?.id === sub.id}
                                    isCompleted={completedTopicIds.includes(sub.id)}
                                    effectiveLogo={effectiveLogo}
                                    courseTitle={course.title}
                                    onClick={() => {
                                      setActiveItemId(sub.id);
                                      window.scrollTo({ top: 0, behavior: "smooth" });
                                    }}
                                    onToggleComplete={() => onToggleCompleteTopic(sub.id)}
                                  />
                                ))}
                              </div>
                            )}
                          </div>
                        );
                      }

                      const isActive = currentItem?.id === top.id;
                      const isCompleted = completedTopicIds.includes(top.id);

                      return (
                        <ClassCardItem
                          key={top.id}
                          id={top.id}
                          title={top.title}
                          cover={top.cover}
                          author={authorName}
                          isActive={isActive}
                          isCompleted={isCompleted}
                          effectiveLogo={effectiveLogo}
                          courseTitle={course.title}
                          onClick={() => {
                            setActiveItemId(top.id);
                            window.scrollTo({ top: 0, behavior: "smooth" });
                          }}
                          onToggleComplete={() => onToggleCompleteTopic(top.id)}
                        />
                      );
                    })}
                  </div>
                </div>
              )}

              {/* TAB 1: DESCRIPCIÓN */}
              {contentTab === "description" && (
                <div className={styles.topicDescriptionBox}>
                  {currentItem?.description ? (
                    <p>{currentItem.description}</p>
                  ) : (
                    <p className={styles.noContentText}>
                      No hay notas o descripciones adicionales registradas para esta lección.
                    </p>
                  )}
                </div>
              )}

              {/* TAB 2: RECURSOS Y DESCARGAS */}
              {contentTab === "files" && (
                <div className={styles.topicFilesBox}>
                  {currentItem?.files && currentItem.files.length > 0 ? (
                    <div className={styles.filesList}>
                      {currentItem.files.map((file, idx) => (
                        <a
                          key={file.id || idx}
                          href={file.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className={styles.fileDownloadItem}
                          download
                        >
                          <div className={styles.fileIconWrapper}>
                            <Download size={16} />
                          </div>
                          <div className={styles.fileDetails}>
                            <span className={styles.fileName}>{file.name}</span>
                            {file.size && <span className={styles.fileSize}>{file.size}</span>}
                          </div>
                        </a>
                      ))}
                    </div>
                  ) : (
                    <p className={styles.noContentText}>
                      Esta lección no incluye archivos adjuntos para descargar.
                    </p>
                  )}
                </div>
              )}

              {/* TAB 3: PREGUNTAS / QUIZ INTERACTIVO */}
              {contentTab === "quiz" && (
                <div className={styles.quizWrapper}>
                  {currentQuestions.length > 0 ? (
                    <>
                      <div className={styles.quizHeader}>
                        <div>
                          <h4 className={styles.quizHeaderTitle}>Cuestionario de Autoevaluación</h4>
                          <p className={styles.quizHeaderSub}>
                            Responde las preguntas para comprobar lo aprendido en esta clase.
                          </p>
                        </div>
                        <span className={styles.quizBadge}>
                          <HelpCircle size={12} />
                          {currentQuestions.length} {currentQuestions.length === 1 ? "Pregunta" : "Preguntas"}
                        </span>
                      </div>

                      {currentQuestions.map((q, qIdx) => {
                        const selectedIdx = quizAnswers[q.id];
                        const isAnswered = selectedIdx !== undefined;

                        return (
                          <div key={q.id || qIdx} className={styles.quizCard}>
                            <div className={styles.quizQuestionTop}>
                              <span className={styles.quizQuestionIndex}>{qIdx + 1}</span>
                              <p className={styles.quizQuestionPrompt}>{q.question}</p>
                            </div>

                            <div className={styles.quizOptionsList}>
                              {q.options.map((opt, oIdx) => {
                                const isSelected = selectedIdx === oIdx;
                                let btnClasses = [styles.quizOptionBtn];

                                if (quizSubmitted) {
                                  if (oIdx === q.correctIndex) {
                                    btnClasses.push(styles.quizOptionBtnCorrect);
                                  } else if (isSelected && oIdx !== q.correctIndex) {
                                    btnClasses.push(styles.quizOptionBtnIncorrect);
                                  }
                                } else if (isSelected) {
                                  btnClasses.push(styles.quizOptionBtnSelected);
                                }

                                return (
                                  <button
                                    key={oIdx}
                                    type="button"
                                    className={btnClasses.join(" ")}
                                    onClick={() => handleSelectQuizOption(q.id, oIdx)}
                                    disabled={quizSubmitted}
                                  >
                                    <div className={styles.quizOptionIndicator}>
                                      {quizSubmitted && oIdx === q.correctIndex ? (
                                        <Check size={12} color="#0db879" />
                                      ) : quizSubmitted && isSelected && oIdx !== q.correctIndex ? (
                                        <X size={12} color="#ef4444" />
                                      ) : isSelected ? (
                                        <div className={styles.quizOptionIndicatorDot} />
                                      ) : null}
                                    </div>
                                    <span>{opt}</span>
                                  </button>
                                );
                              })}
                            </div>

                            {quizSubmitted && q.explanation && (
                              <div className={styles.quizOptionExplanation}>
                                <strong>Explicación: </strong>{q.explanation}
                              </div>
                            )}
                          </div>
                        );
                      })}

                      {/* Resultado / Calificación del Cuestionario */}
                      {quizSubmitted && quizScore && (
                        <div className={[
                          styles.quizFeedbackBox,
                          quizScore.percentage >= 70 ? styles.quizFeedbackSuccess : styles.quizFeedbackWarning
                        ].join(" ")}>
                          <div className={styles.quizFeedbackContent}>
                            <div className={styles.quizFeedbackIcon}>
                              {quizScore.percentage >= 70 ? <Award size={30} /> : <Sparkles size={30} />}
                            </div>
                            <div>
                              <h5 className={styles.quizFeedbackTitle}>
                                {quizScore.percentage >= 70 ? "¡Excelente trabajo!" : "¡Sigue practicando!"}
                              </h5>
                              <p className={styles.quizFeedbackSub}>
                                Acertaste {quizScore.correct} de {quizScore.total} preguntas ({quizScore.percentage}%).
                                {quizScore.percentage >= 70 
                                  ? " Has aprobado este cuestionario con éxito." 
                                  : " Te recomendamos repasar los temas del video y volver a intentarlo."}
                              </p>
                            </div>
                          </div>
                        </div>
                      )}

                      {/* Botones de acción del Quiz */}
                      <div className={styles.quizActionsRow}>
                        {!quizSubmitted ? (
                          <button
                            type="button"
                            className={styles.quizSubmitBtn}
                            onClick={handleCheckQuiz}
                            disabled={Object.keys(quizAnswers).length === 0}
                          >
                            <Check size={16} />
                            <span>Comprobar Respuestas</span>
                          </button>
                        ) : (
                          <button
                            type="button"
                            className={styles.quizRetryBtn}
                            onClick={handleResetQuiz}
                          >
                            <RotateCcw size={15} />
                            <span>Reintentar Cuestionario</span>
                          </button>
                        )}
                      </div>
                    </>
                  ) : (
                    <div className={styles.topicDescriptionBox}>
                      <p className={styles.noContentText}>
                        Esta clase no contiene preguntas de autoevaluación o cuestionario.
                      </p>
                    </div>
                  )}
                </div>
              )}

              {/* TAB 4: DOCENTES DEL CURSO */}
              {contentTab === "instructors" && (
                <div className={styles.classroomInstructorsGrid}>
                  {instructors.map((inst, iIdx) => (
                    <div key={inst.id || iIdx} className={styles.classroomInstructorCard}>
                      <div className={styles.classroomInstructorHeader}>
                        {inst.avatar ? (
                          <img
                            src={inst.avatar}
                            alt={inst.name}
                            className={styles.classroomInstructorAvatar}
                          />
                        ) : (
                          <div className={styles.classroomInstructorAvatarFallback}>
                            {inst.name.charAt(0).toUpperCase()}
                          </div>
                        )}
                        <div className={styles.classroomInstructorMeta}>
                          <h5 className={styles.classroomInstructorName}>{inst.name}</h5>
                          <span className={styles.classroomInstructorRole}>
                            {inst.role || "Docente"}
                          </span>
                        </div>
                      </div>
                      {inst.bio && (
                        <p className={styles.classroomInstructorBio}>{inst.bio}</p>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </main>

        {/* COLUMNA DERECHA: SIDEBAR DE TOPICS DEL MÓDULO */}
        <aside className={styles.classroomSidebar}>
          {/* Cabecera del Temario / Módulo Activo */}
          <div className={styles.sidebarHeaderCard}>
            <div className={styles.sidebarHeaderTop}>
              <div className={styles.sidebarHeaderTitleWrap}>
                <h3 className={styles.sidebarModuleTitle}>{currentLesson.title}</h3>
                <span className={styles.sidebarModuleCounter}>
                  {completedInModule}/{allPlayableItems.length} terminado
                </span>
              </div>
              {course.sections && course.sections.length > 1 && (
                <button
                  type="button"
                  className={styles.sidebarModuleToggleBtn}
                  onClick={() => setIsOtherModulesOpen(!isOtherModulesOpen)}
                  title="Cambiar de módulo"
                >
                  <ChevronDown
                    size={18}
                    style={{
                      transform: isOtherModulesOpen ? "rotate(180deg)" : "rotate(0deg)",
                      transition: "transform 0.2s ease",
                    }}
                  />
                </button>
              )}
            </div>
            <div className={styles.sidebarProgressBarTrack}>
              <div 
                className={styles.sidebarProgressBarFill} 
                style={{ width: `${modulePercentage}%` }} 
              />
            </div>
          </div>

          {/* Menú desplegable para saltar a otros módulos del curso */}
          {isOtherModulesOpen && course.sections && course.sections.length > 1 && (
            <div className={styles.otherModulesSection}>
              <h4 className={styles.otherModulesHeading}>Módulos del Curso</h4>
              <div className={styles.otherModulesList}>
                {course.sections.map((sec) => {
                  const secLessons = sec.lessons || [];
                  return secLessons.map((les) => {
                    const isCurrent = les.id === currentLesson.id;
                    return (
                      <button
                        key={les.id}
                        type="button"
                        className={[
                          styles.otherModuleBtn,
                          isCurrent ? styles.otherModuleBtnActive : "",
                        ].join(" ")}
                        onClick={() => {
                          if (!isCurrent) {
                            const first = les.topics && les.topics.length > 0 ? les.topics[0] : (les as unknown as CourseTopic);
                            onSwitchModule(sec, les, first);
                            setIsOtherModulesOpen(false);
                          }
                        }}
                      >
                        <Layers size={13} />
                        <span>{les.title}</span>
                      </button>
                    );
                  });
                })}
              </div>
            </div>
          )}

          {/* Lista de Topics y Subtopics */}
          <div className={styles.topicsScrollList}>
            {topics.map((top) => {
              const hasSubtopics = top.subtopics && top.subtopics.length > 0;
              const isExpanded = !!expandedTopicIds[top.id];

              if (hasSubtopics) {
                const subtopics = top.subtopics!;
                const completedSubs = subtopics.filter((s) => completedTopicIds.includes(s.id)).length;
                const isParentActive = currentItem?.id === top.id || subtopics.some((s) => s.id === currentItem?.id);

                return (
                  <div key={top.id} className={styles.topicAccordionWrapper}>
                    {/* Cabecera del Acordeón para temas con sub-clases */}
                    <div
                      className={[
                        styles.topicAccordionHeader,
                        isParentActive ? styles.topicAccordionHeaderActive : "",
                      ].join(" ")}
                      onClick={() => toggleTopicExpanded(top.id)}
                    >
                      <div className={styles.topicAccordionMeta}>
                        <h4 className={styles.topicAccordionTitle}>{top.title}</h4>
                        <span className={styles.topicAccordionCounter}>
                          {completedSubs}/{subtopics.length} terminado
                        </span>
                      </div>
                      <div className={styles.topicAccordionChevron}>
                        {isExpanded ? <ChevronDown size={16} /> : <ChevronRightIcon size={16} />}
                      </div>
                    </div>

                    {/* Contenido expandible del acordeón */}
                    {isExpanded && (
                      <div className={styles.topicAccordionBody}>
                        {/* Si el topic padre tiene video propio */}
                        {top.video_url && top.video_url.trim() !== "" && (
                          <ClassCardItem
                            id={top.id}
                            title={top.title}
                            cover={top.cover}
                            author={authorName}
                            isActive={currentItem?.id === top.id}
                            isCompleted={completedTopicIds.includes(top.id)}
                            effectiveLogo={effectiveLogo}
                            courseTitle={course.title}
                            onClick={() => setActiveItemId(top.id)}
                            onToggleComplete={() => onToggleCompleteTopic(top.id)}
                          />
                        )}

                        {/* Subtopics hijos renderizados como class cards */}
                        {subtopics.map((sub) => (
                          <ClassCardItem
                            key={sub.id}
                            id={sub.id}
                            title={sub.title}
                            cover={sub.cover}
                            author={authorName}
                            isActive={currentItem?.id === sub.id}
                            isCompleted={completedTopicIds.includes(sub.id)}
                            effectiveLogo={effectiveLogo}
                            courseTitle={course.title}
                            onClick={() => setActiveItemId(sub.id)}
                            onToggleComplete={() => onToggleCompleteTopic(sub.id)}
                          />
                        ))}
                      </div>
                    )}
                  </div>
                );
              }

              // Tema SIN subtopics
              const isActive = currentItem?.id === top.id;
              const isCompleted = completedTopicIds.includes(top.id);

              return (
                <ClassCardItem
                  key={top.id}
                  id={top.id}
                  title={top.title}
                  cover={top.cover}
                  author={authorName}
                  isActive={isActive}
                  isCompleted={isCompleted}
                  effectiveLogo={effectiveLogo}
                  courseTitle={course.title}
                  onClick={() => setActiveItemId(top.id)}
                  onToggleComplete={() => onToggleCompleteTopic(top.id)}
                />
              );
            })}
          </div>
        </aside>
      </div>
    </div>
  );
};

interface ClassCardItemProps {
  id: string;
  title: string;
  cover?: string;
  author: string;
  isActive: boolean;
  isCompleted: boolean;
  effectiveLogo: string;
  courseTitle: string;
  onClick: () => void;
  onToggleComplete: () => void;
}

function ClassCardItem({
  title,
  cover,
  author,
  isActive,
  isCompleted,
  effectiveLogo,
  courseTitle,
  onClick,
  onToggleComplete,
}: ClassCardItemProps) {
  return (
    <div
      className={[
        styles.classItemCard,
        isActive ? styles.classItemCardActive : "",
      ].join(" ")}
      onClick={onClick}
    >
      {/* Barra vertical de acento para la clase activa */}
      {isActive && <div className={styles.classActiveBar} />}

      {/* Miniatura horizontal 16:9 */}
      <div className={styles.classThumbWrap}>
        {/* Checkmark circular */}
        <button
          type="button"
          className={[
            styles.classCheckCircle,
            isCompleted ? styles.classCheckCircleDone : "",
          ].join(" ")}
          onClick={(e) => {
            e.stopPropagation();
            onToggleComplete();
          }}
          title={isCompleted ? "Clase completada" : "Marcar como completada"}
        >
          <Check size={11} strokeWidth={2.5} />
        </button>

        {cover && cover.trim() !== "" ? (
          <img src={cover} alt={title} className={styles.classThumbImg} />
        ) : (
          <div className={styles.classThumbLogoFallback}>
            {effectiveLogo ? (
              <img
                src={effectiveLogo}
                alt="Academy Logo"
                className={styles.classThumbLogoImg}
              />
            ) : (
              <span className={styles.classThumbInitial}>
                {courseTitle ? courseTitle.charAt(0).toUpperCase() : "C"}
              </span>
            )}
          </div>
        )}
      </div>

      {/* Textos: Título de la clase y Autor */}
      <div className={styles.classInfoWrap}>
        <h4 className={styles.classItemTitle} title={title}>
          {title}
        </h4>
        <span className={styles.classItemAuthor}>
          {author}
        </span>
      </div>
    </div>
  );
}
