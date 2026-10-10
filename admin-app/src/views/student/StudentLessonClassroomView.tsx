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
  Video
} from "lucide-react";
import { Course, CourseSection, CourseLesson, CourseTopic, api } from "../../services/api";
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
      },
    ];
  }, [currentLesson]);

  // Topic activo actual
  const [activeTopicId, setActiveTopicId] = useState<string>(() => {
    return initialTopic?.id || topics[0]?.id || "";
  });

  // Tab de contenido: notas o recursos
  const [contentTab, setContentTab] = useState<"description" | "files">("description");

  // Al cambiar de lección / topic, hacer scroll arriba suavemente
  useEffect(() => {
    const scrollEl = document.querySelector('[data-scroll-container="main"]') as HTMLElement | null;
    if (scrollEl) {
      scrollEl.scrollTo({ top: 0, behavior: "smooth" });
    } else {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }, [activeTopicId]);

  // Topic seleccionado actualmente
  const currentTopic = useMemo(() => {
    return topics.find((t) => t.id === activeTopicId) || topics[0] || null;
  }, [topics, activeTopicId]);

  const activeIndex = topics.findIndex((t) => t.id === activeTopicId);
  const prevTopic = activeIndex > 0 ? topics[activeIndex - 1] : null;
  const nextTopic = activeIndex < topics.length - 1 ? topics[activeIndex + 1] : null;

  const isCurrentCompleted = currentTopic ? completedTopicIds.includes(currentTopic.id) : false;

  // Formateador de Embed de Video
  const renderVideoPlayer = () => {
    if (!currentTopic || !currentTopic.video_url) {
      return (
        <div className={styles.videoPlaceholderContainer}>
          <div className={styles.videoPlaceholderContent}>
            <div className={styles.videoPlaceholderPlayIcon}>
              <Video size={42} />
            </div>
            <h3 className={styles.videoPlaceholderTitle}>{currentTopic?.title}</h3>
            <p className={styles.videoPlaceholderSubtitle}>
              Video explicativo disponible para reproducción • Duración estimada: {currentTopic?.duration || "15m"}
            </p>
          </div>
        </div>
      );
    }

    const url = currentTopic.video_url.trim();

    // YouTube
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch) {
      return (
        <div className={styles.videoResponsiveWrapper}>
          <iframe
            src={`https://www.youtube.com/embed/${ytMatch[1]}?autoplay=0&rel=0&modestbranding=1`}
            title={currentTopic.title}
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
            title={currentTopic.title}
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
            poster={currentTopic.cover}
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
          title={currentTopic.title}
          allowFullScreen
          className={styles.videoIframe}
        />
      </div>
    );
  };

  const completedInModule = topics.filter((t) => completedTopicIds.includes(t.id)).length;
  const modulePercentage = topics.length > 0 ? Math.round((completedInModule / topics.length) * 100) : 0;

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
          </div>
        </div>

        <div className={styles.classroomHeaderRight}>
          <div className={styles.classroomModuleBadge}>
            <CheckCircle2 size={14} className={styles.badgeCheckIcon} />
            <span>
              {completedInModule} de {topics.length} completadas ({modulePercentage}%)
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
              </span>
              <h1 className={styles.activeTopicTitle}>
                {currentTopic?.title || "Lección del módulo"}
              </h1>
              {currentTopic?.duration && (
                <div className={styles.topicDurationBadge}>
                  <Clock size={13} />
                  <span>{currentTopic.duration}</span>
                </div>
              )}
            </div>

            {/* Botón de Marcar como Completado */}
            {currentTopic && (
              <button
                type="button"
                className={[
                  styles.completeToggleBtn,
                  isCurrentCompleted ? styles.completeToggleBtnDone : "",
                ].join(" ")}
                onClick={() => onToggleCompleteTopic(currentTopic.id)}
              >
                <CheckCircle2 size={16} />
                <span>{isCurrentCompleted ? "✓ Lección Completada" : "Marcar como Completada"}</span>
              </button>
            )}
          </div>

          {/* Navegación Anterior / Siguiente Lección */}
          <div className={styles.prevNextNavRow}>
            {prevTopic ? (
              <button
                type="button"
                className={styles.prevNextBtn}
                onClick={() => setActiveTopicId(prevTopic.id)}
              >
                <ChevronLeft size={16} />
                <div className={styles.prevNextBtnInfo}>
                  <small>Anterior</small>
                  <span>{prevTopic.title}</span>
                </div>
              </button>
            ) : <div />}

            {nextTopic && (
              <button
                type="button"
                className={[styles.prevNextBtn, styles.nextBtn].join(" ")}
                onClick={() => setActiveTopicId(nextTopic.id)}
              >
                <div className={styles.prevNextBtnInfo}>
                  <small>Siguiente</small>
                  <span>{nextTopic.title}</span>
                </div>
                <ChevronRight size={16} />
              </button>
            )}
          </div>

          {/* Pestañas de Descripción y Archivos de la Lección */}
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
                <span>Recursos & Descargas ({currentTopic?.files?.length || 0})</span>
              </button>
            </div>

            <div className={styles.topicTabBody}>
              {contentTab === "description" && (
                <div className={styles.topicDescriptionBox}>
                  {currentTopic?.description ? (
                    <p>{currentTopic.description}</p>
                  ) : (
                    <p className={styles.noContentText}>
                      No hay notas o descripciones adicionales registradas para esta lección.
                    </p>
                  )}
                </div>
              )}

              {contentTab === "files" && (
                <div className={styles.topicFilesBox}>
                  {currentTopic?.files && currentTopic.files.length > 0 ? (
                    <div className={styles.filesList}>
                      {currentTopic.files.map((file, idx) => (
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

          {/* Lista de Topics */}
          <div className={styles.topicsScrollList}>
            {topics.map((top, idx) => {
              const isActive = top.id === activeTopicId;
              const isCompleted = completedTopicIds.includes(top.id);
              const numberStr = String(idx + 1).padStart(2, "0");

              return (
                <div
                  key={top.id}
                  className={[
                    styles.topicListItem,
                    isActive ? styles.topicListItemActive : "",
                    isCompleted ? styles.topicListItemCompleted : "",
                  ].join(" ")}
                  onClick={() => setActiveTopicId(top.id)}
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
