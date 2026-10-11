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
  CourseInstructor 
} from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentLessonClassroomViewProps {
  course: Course;
  currentSection: CourseSection;
  currentLesson: CourseLesson;
  initialTopic?: CourseTopic;
  completedTopicIds: string[];
  onToggleCompleteTopic: (topicId: string) => void;
  onBackToCourse: () => void;
  onSwitchModule: (sec: CourseSection, les: CourseLesson, top?: CourseTopic) => void;
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
  onToggleCompleteTopic,
  onBackToCourse,
  onSwitchModule,
}) => {
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

  // Tabs de contenido: descripción, archivos, preguntas/quiz o docentes
  const [contentTab, setContentTab] = useState<"description" | "files" | "quiz" | "instructors">("description");

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

    const url = currentItem.video_url.trim();

    // YouTube
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://www.youtube.com/embed/${ytMatch[1]}?autoplay=0&rel=0&modestbranding=1`}
            title={currentItem.title}
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // Vimeo
    const vimeoMatch = url.match(/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)/);
    if (vimeoMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://player.vimeo.com/video/${vimeoMatch[1]}?title=0&byline=0&portrait=0`}
            title={currentItem.title}
            allow="autoplay; fullscreen; picture-in-picture"
            allowFullScreen
            className={styles.videoIframe}
          />
        </div>
      );
    }

    // MP4 Directo
    if (url.endsWith(".mp4") || url.includes(".mp4?")) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <video
            controls
            src={url}
            poster={currentItem.cover}
            className={styles.videoHtmlTag}
          />
        </div>
      );
    }

    // Fallback Iframe genérico
    return (
      <div className={styles.videoResponsiveWrapper}>
        <iframe
          src={url}
          title={currentItem.title}
          allowFullScreen
          className={styles.videoIframe}
        />
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
              <span className={styles.topicModuleSubtitle}>
                {currentSection.title} • {currentLesson.title}
                {currentItem?.isSubtopic && currentItem.parentTopicTitle && ` • ${currentItem.parentTopicTitle}`}
              </span>
              <h1 className={styles.activeTopicTitle}>
                {currentItem?.title || "Lección del módulo"}
              </h1>
              {currentItem?.duration && (
                <div className={styles.topicDurationBadge}>
                  <Clock size={13} />
                  <span>{currentItem.duration}</span>
                  {currentItem.isSubtopic && (
                    <span className={styles.subtopicCountBadge}>Sub-clase</span>
                  )}
                </div>
              )}
            </div>

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
                <span>{isCurrentCompleted ? "✓ Clase Completada" : "Marcar como Completada"}</span>
              </button>
            )}
          </div>

          {/* Navegación Anterior / Siguiente Lección */}
          <div className={styles.prevNextNavRow}>
            {prevItem ? (
              <button
                type="button"
                className={styles.prevNextBtn}
                onClick={() => setActiveItemId(prevItem.id)}
              >
                <ChevronLeft size={16} />
                <div className={styles.prevNextBtnInfo}>
                  <small>Anterior</small>
                  <span>{prevItem.title}</span>
                </div>
              </button>
            ) : <div />}

            {nextItem && (
              <button
                type="button"
                className={[styles.prevNextBtn, styles.nextBtn].join(" ")}
                onClick={() => setActiveItemId(nextItem.id)}
              >
                <div className={styles.prevNextBtnInfo}>
                  <small>Siguiente</small>
                  <span>{nextItem.title}</span>
                </div>
                <ChevronRight size={16} />
              </button>
            )}
          </div>

          {/* Pestañas de Descripción, Archivos, Quiz y Docentes */}
          <div className={styles.topicTabsContainer}>
            <div className={styles.topicTabsList}>
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
              <button
                type="button"
                className={[
                  styles.topicTabBtn,
                  contentTab === "files" ? styles.topicTabBtnActive : "",
                ].join(" ")}
                onClick={() => setContentTab("files")}
              >
                <Download size={15} />
                <span>Recursos & Descargas ({currentItem?.files?.length || 0})</span>
              </button>
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
          <div className={styles.sidebarHeaderCard}>
            <div className={styles.sidebarHeaderTop}>
              <BookOpen size={16} className={styles.sidebarBookIcon} />
              <h3 className={styles.sidebarModuleTitle}>Temario del Módulo</h3>
            </div>
            <p className={styles.sidebarModuleSub}>{currentLesson.title}</p>
            <div className={styles.sidebarProgressBarTrack}>
              <div 
                className={styles.sidebarProgressBarFill} 
                style={{ width: `${modulePercentage}%` }} 
              />
            </div>
          </div>

          {/* Lista de Topics y Subtopics */}
          <div className={styles.topicsScrollList}>
            {topics.map((top, idx) => {
              const hasSubtopics = top.subtopics && top.subtopics.length > 0;
              const numberStr = String(idx + 1).padStart(2, "0");
              const isExpanded = !!expandedTopicIds[top.id];

              if (hasSubtopics) {
                const subtopics = top.subtopics!;
                const completedSubs = subtopics.filter((s) => completedTopicIds.includes(s.id)).length;
                const allSubsDone = completedSubs === subtopics.length;
                const isParentActive = currentItem?.id === top.id;

                return (
                  <div key={top.id} className={styles.topicGroupWrapper}>
                    {/* Cabecera del Topic con Sub-clases */}
                    <div
                      className={[
                        styles.topicListItem,
                        styles.topicItemWithSubs,
                        isParentActive ? styles.topicListItemActive : "",
                        allSubsDone ? styles.topicListItemCompleted : "",
                      ].join(" ")}
                      onClick={() => {
                        if (top.video_url && top.video_url.trim() !== "") {
                          setActiveItemId(top.id);
                        } else if (subtopics.length > 0) {
                          setActiveItemId(subtopics[0].id);
                        }
                        toggleTopicExpanded(top.id);
                      }}
                    >
                      <div className={styles.topicItemLeft}>
                        <button
                          type="button"
                          className={styles.topicItemCheckBtn}
                          onClick={(e) => {
                            e.stopPropagation();
                            onToggleCompleteTopic(top.id);
                          }}
                          title={allSubsDone ? "Todas las sub-clases completadas" : "Progreso de sub-clases"}
                        >
                          {allSubsDone ? (
                            <CheckCircle2 size={16} className={styles.checkDoneIcon} />
                          ) : (
                            <Circle size={16} className={styles.checkPendingIcon} />
                          )}
                        </button>

                        <div className={styles.topicItemMeta}>
                          <span className={styles.topicIndexNumber}>{numberStr}.</span>
                          <span className={styles.topicItemTitle}>{top.title}</span>
                          <span className={styles.subtopicCountBadge}>
                            {completedSubs}/{subtopics.length}
                          </span>
                        </div>
                      </div>

                      <button
                        type="button"
                        className={styles.topicChevronBtn}
                        onClick={(e) => toggleTopicExpanded(top.id, e)}
                        title={isExpanded ? "Colapsar sub-clases" : "Desplegar sub-clases"}
                      >
                        {isExpanded ? <ChevronDown size={14} /> : <ChevronRightIcon size={14} />}
                      </button>
                    </div>

                    {/* Lista desplegable de Subtopics */}
                    {isExpanded && (
                      <div className={styles.subtopicsTreeContainer}>
                        {subtopics.map((sub, sIdx) => {
                          const isSubActive = currentItem?.id === sub.id;
                          const isSubDone = completedTopicIds.includes(sub.id);

                          return (
                            <div
                              key={sub.id}
                              className={[
                                styles.subtopicListItem,
                                isSubActive ? styles.subtopicListItemActive : "",
                                isSubDone ? styles.subtopicListItemCompleted : "",
                              ].join(" ")}
                              onClick={() => setActiveItemId(sub.id)}
                            >
                              <div className={styles.topicItemLeft}>
                                <button
                                  type="button"
                                  className={styles.topicItemCheckBtn}
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    onToggleCompleteTopic(sub.id);
                                  }}
                                  title={isSubDone ? "Sub-clase completada" : "Marcar completada"}
                                >
                                  {isSubDone ? (
                                    <CheckCircle2 size={14} className={styles.checkDoneIcon} />
                                  ) : (
                                    <Circle size={14} className={styles.checkPendingIcon} />
                                  )}
                                </button>

                                <div className={styles.subtopicPlayIcon}>
                                  <Play size={10} />
                                </div>

                                <span className={styles.subtopicTitle}>
                                  {numberStr}.{sIdx + 1} {sub.title}
                                </span>
                              </div>

                              {sub.duration && (
                                <span className={styles.subtopicDuration}>{sub.duration}</span>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    )}
                  </div>
                );
              }

              // Topic simple sin sub-clases
              const isActive = currentItem?.id === top.id;
              const isCompleted = completedTopicIds.includes(top.id);

              return (
                <div
                  key={top.id}
                  className={[
                    styles.topicListItem,
                    isActive ? styles.topicListItemActive : "",
                    isCompleted ? styles.topicListItemCompleted : "",
                  ].join(" ")}
                  onClick={() => setActiveItemId(top.id)}
                >
                  <div className={styles.topicItemLeft}>
                    {/* Indicador de Estado */}
                    <button
                      type="button"
                      className={styles.topicItemCheckBtn}
                      onClick={(e) => {
                        e.stopPropagation();
                        onToggleCompleteTopic(top.id);
                      }}
                      title={isCompleted ? "Completada" : "Marcar completada"}
                    >
                      {isCompleted ? (
                        <CheckCircle2 size={16} className={styles.checkDoneIcon} />
                      ) : (
                        <Circle size={16} className={styles.checkPendingIcon} />
                      )}
                    </button>

                    <div className={styles.topicItemMeta}>
                      <span className={styles.topicIndexNumber}>{numberStr}.</span>
                      <span className={styles.topicItemTitle}>{top.title}</span>
                    </div>
                  </div>

                  {top.duration && (
                    <span className={styles.topicItemDuration}>{top.duration}</span>
                  )}
                </div>
              );
            })}
          </div>

          {/* Acordeón para saltar a otros módulos del mismo curso sin salir */}
          {course.sections && course.sections.length > 1 && (
            <div className={styles.otherModulesSection}>
              <h4 className={styles.otherModulesHeading}>Otros Módulos del Curso</h4>
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
        </aside>
      </div>
    </div>
  );
};
