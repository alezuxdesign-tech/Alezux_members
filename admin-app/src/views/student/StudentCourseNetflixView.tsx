import React, { useRef, useState, useMemo } from "react";
import { 
  ArrowLeft, 
  MessageCircle, 
  Users, 
  Video, 
  ChevronLeft, 
  ChevronRight, 
  Lock, 
  Play, 
  CheckCircle2, 
  Sparkles,
  ExternalLink,
  ShieldAlert,
  ArrowRight
} from "lucide-react";
import { Course, CourseSection, CourseLesson, CourseTopic } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface CarouselRow {
  id: string;
  title: string;
  modules: {
    section: CourseSection;
    moduleNumber: number;
  }[];
}

interface StudentCourseNetflixViewProps {
  course: Course;
  academyName: string;
  academyLogo: string;
  onBack: () => void;
  onSelectModule: (section: CourseSection, lesson: CourseLesson, topic?: CourseTopic) => void;
}

export const StudentCourseNetflixView: React.FC<StudentCourseNetflixViewProps> = ({
  course,
  academyName,
  academyLogo,
  onBack,
  onSelectModule,
}) => {
  const [purchaseModalOpen, setPurchaseModalOpen] = useState(false);
  const [lockedItemName, setLockedItemName] = useState("");

  const sections = course.sections || [];
  const progress = course.progress ?? 0;
  const hasAccess = !!course.hasAccess;

  // Agrupar los módulos del temario en filas de carrusel delimitadas por separadores
  const carouselRows: CarouselRow[] = useMemo(() => {
    const rows: CarouselRow[] = [];
    let currentRow: CarouselRow = {
      id: "row-initial",
      title: "Módulos del Curso",
      modules: [],
    };
    let moduleCounter = 0;

    sections.forEach((sec, idx) => {
      if (sec.is_separator) {
        if (currentRow.modules.length > 0) {
          rows.push(currentRow);
          currentRow = {
            id: sec.id || `row-${idx}`,
            title: sec.title.trim() || `Fila ${rows.length + 1}`,
            modules: [],
          };
        } else {
          // Si el separador está al inicio antes de cualquier módulo, da título a la primera fila
          currentRow.id = sec.id || `row-${idx}`;
          if (sec.title.trim()) {
            currentRow.title = sec.title.trim();
          }
        }
      } else {
        moduleCounter++;
        currentRow.modules.push({
          section: sec,
          moduleNumber: moduleCounter,
        });
      }
    });

    if (currentRow.modules.length > 0) {
      rows.push(currentRow);
    }

    return rows;
  }, [sections]);

  // Refs para hacer scroll horizontal con las flechas < y > en cada fila de carrusel
  const scrollContainers = useRef<Record<string, HTMLDivElement | null>>({});

  const scrollSection = (rowId: string, direction: "left" | "right") => {
    const el = scrollContainers.current[rowId];
    if (el) {
      const scrollAmount = 380;
      el.scrollBy({
        left: direction === "left" ? -scrollAmount : scrollAmount,
        behavior: "smooth",
      });
    }
  };

  const handleCardClick = (sec: CourseSection, moduleNumber: number) => {
    if (!hasAccess) {
      setLockedItemName(sec.title || `Módulo ${moduleNumber}`);
      setPurchaseModalOpen(true);
      return;
    }
    const lessons = sec.lessons || [];
    if (lessons.length === 0) {
      alert(`El módulo "${sec.title || `Módulo ${moduleNumber}`}" aún no tiene lecciones creadas.`);
      return;
    }
    // Si tiene acceso, abrir el reproductor en la primera clase de este módulo
    const firstLesson = lessons[0];
    const firstTopic = firstLesson.topics && firstLesson.topics.length > 0 
      ? firstLesson.topics[0] 
      : (firstLesson as unknown as CourseTopic);
    onSelectModule(sec, firstLesson, firstTopic);
  };

  return (
    <div className={styles.netflixContainer}>
      {/* 1. HERO BANNER PRINCIPAL DEL CURSO (ESTILO NETFLIX) */}
      <div 
        className={styles.netflixHeroBanner}
        style={{
          backgroundImage: `url(${course.banner || course.thumbnail || "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80"})`
        }}
      >
        {/* Degradado cinematográfico oscuro */}
        <div className={styles.heroOverlayGradient} />

        {/* Cabecera superior con Logo de la Academia */}
        <div className={styles.heroTopBar}>
          <div className={styles.heroBrandRow}>
            {academyLogo ? (
              <img src={academyLogo} alt={academyName} className={styles.heroLogoImg} />
            ) : (
              <div className={styles.heroLogoIcon}>🎓</div>
            )}
            <span className={styles.heroBrandName}>{academyName || "Academia"}</span>
          </div>
        </div>

        {/* Botón de Regresar al aula estilo imagen del usuario */}
        <div className={styles.heroBackButtonRow}>
          <button 
            type="button" 
            className={styles.backToHallBtn}
            onClick={onBack}
          >
            <ArrowLeft size={16} />
            <span>Regresar al aula</span>
          </button>
        </div>

        {/* 2. FILA DE WIDGETS FLOTANTES: PROGRESO Y COMUNIDAD (WHATSAPP, SLACK, ZOOM) */}
        <div className={styles.heroWidgetsRow}>
          {/* Widget 1: Progreso del Curso */}
          <div className={styles.heroWidgetCard}>
            <div className={styles.progressHeaderRow}>
              <span className={styles.widgetTitle}>Curso Completo</span>
              <span className={styles.widgetPercentage}>{progress}%</span>
            </div>
            <div className={styles.heroProgressTrack}>
              <div 
                className={styles.heroProgressFill} 
                style={{ width: `${progress}%` }} 
              />
            </div>
          </div>

          {/* Widget 2: Grupo de WhatsApp */}
          <a
            href={course.whatsapp_url || "#"}
            target={course.whatsapp_url ? "_blank" : undefined}
            rel="noopener noreferrer"
            className={styles.heroCommunityCard}
            onClick={(e) => {
              if (!course.whatsapp_url) {
                e.preventDefault();
                alert("El enlace del Grupo de WhatsApp aún no ha sido configurado.");
              }
            }}
          >
            <div className={styles.communityIconWrapperWhatsapp}>
              <MessageCircle size={22} className={styles.whatsappIcon} />
            </div>
            <div className={styles.communityInfo}>
              <h4 className={styles.communityTitle}>Grupo de WhatsApp</h4>
              <p className={styles.communitySub}>Aquí solo se publicará noticias</p>
              <span className={styles.communityActionLink}>
                <span>Acceder Ahora</span>
                <ArrowRight size={13} />
              </span>
            </div>
          </a>

          {/* Widget 3: Grupo de Slack */}
          <a
            href={course.slack_url || "#"}
            target={course.slack_url ? "_blank" : undefined}
            rel="noopener noreferrer"
            className={styles.heroCommunityCard}
            onClick={(e) => {
              if (!course.slack_url) {
                e.preventDefault();
                alert("El enlace del Grupo de Slack aún no ha sido configurado.");
              }
            }}
          >
            <div className={styles.communityIconWrapperSlack}>
              <Users size={22} className={styles.slackIcon} />
            </div>
            <div className={styles.communityInfo}>
              <h4 className={styles.communityTitle}>Grupo de Slack</h4>
              <p className={styles.communitySub}>Este es nuestro canal principal</p>
              <span className={styles.communityActionLink}>
                <span>Acceder Ahora</span>
                <ArrowRight size={13} />
              </span>
            </div>
          </a>

          {/* Widget 4: Link de Zoom */}
          <a
            href={course.zoom_url || "#"}
            target={course.zoom_url ? "_blank" : undefined}
            rel="noopener noreferrer"
            className={styles.heroCommunityCard}
            onClick={(e) => {
              if (!course.zoom_url) {
                e.preventDefault();
                alert("El enlace de Zoom aún no ha sido configurado.");
              }
            }}
          >
            <div className={styles.communityIconWrapperZoom}>
              <Video size={22} className={styles.zoomIcon} />
            </div>
            <div className={styles.communityInfo}>
              <h4 className={styles.communityTitle}>Link de Zoom</h4>
              <p className={styles.communitySub}>Reuniones y mentorías en vivo</p>
              <span className={styles.communityActionLink}>
                <span>Acceder Ahora</span>
                <ArrowRight size={13} />
              </span>
            </div>
          </a>
        </div>
      </div>

      {/* BANNER DE AVISO SI EL CURSO NO ESTÁ HABILITADO */}
      {!hasAccess && (
        <div className={styles.lockedNoticeBanner}>
          <div className={styles.lockedNoticeText}>
            <ShieldAlert size={22} className={styles.lockedBannerIcon} />
            <div>
              <strong>Vista previa del temario y módulos</strong>
              <p>Estás explorando los módulos del curso. Para desbloquear las reproducciones de video y materiales, adquiere tu acceso.</p>
            </div>
          </div>
          {course.checkoutUrl && (
            <a 
              href={course.checkoutUrl} 
              target="_blank" 
              rel="noopener noreferrer"
              className={styles.unlockCourseBtn}
            >
              <Sparkles size={16} />
              <span>Comprar Curso {course.price ? `($${course.price} USD)` : ""}</span>
            </a>
          )}
        </div>
      )}

      {/* 3. SECCIONES Y CARRUSELES DE MÓDULOS (PORTADAS TIPO NETFLIX) */}
      <div className={styles.sectionsList}>
        {carouselRows.map((row, rowIdx) => {
          const rowKey = row.id || `row-${rowIdx}`;

          return (
            <section key={rowKey} className={styles.netflixSection}>
              {/* Encabezado de la Fila con Flechas de Navegación < y > */}
              <div className={styles.netflixSectionHeader}>
                <h3 className={styles.netflixSectionTitle}>
                  {row.title || `Fila ${rowIdx + 1}`}
                </h3>
                <div className={styles.carouselNavControls}>
                  <button
                    type="button"
                    className={styles.carouselArrowBtn}
                    onClick={() => scrollSection(rowKey, "left")}
                    aria-label="Desplazar hacia la izquierda"
                  >
                    <ChevronLeft size={20} />
                  </button>
                  <button
                    type="button"
                    className={styles.carouselArrowBtn}
                    onClick={() => scrollSection(rowKey, "right")}
                    aria-label="Desplazar hacia la derecha"
                  >
                    <ChevronRight size={20} />
                  </button>
                </div>
              </div>

              {/* Fila Horizontal Desplazable de Tarjetas Tipo Portada de Módulo (Poster 2:3) */}
              <div 
                ref={(el) => (scrollContainers.current[rowKey] = el)}
                className={styles.netflixCarouselRow}
              >
                {row.modules.map(({ section: moduleSection, moduleNumber }) => {
                  const lessons = moduleSection.lessons || [];
                  const lessonsCount = lessons.length;
                  const cover = moduleSection.cover || course.thumbnail;
                  const trackTag = `${lessonsCount} ${lessonsCount === 1 ? "Clase" : "Clases"}`;

                  return (
                    <div
                      key={moduleSection.id}
                      className={[
                        styles.netflixPosterCard,
                        !hasAccess ? styles.netflixCardLocked : "",
                      ].join(" ")}
                      onClick={() => handleCardClick(moduleSection, moduleNumber)}
                    >
                      {/* Fondo de Portada con Imagen o Gradiente */}
                      <div 
                        className={styles.posterBgImage}
                        style={{
                          backgroundImage: cover ? `url(${cover})` : undefined,
                        }}
                      />

                      {/* Degradado vertical de lectura */}
                      <div className={styles.posterGradientOverlay} />

                      {/* Tag Superior: Módulo 1, Módulo 2... */}
                      <div className={styles.posterTopBadge}>
                        <span>Módulo {moduleNumber}</span>
                      </div>

                      {/* Overlay de Bloqueo si no tiene acceso */}
                      {!hasAccess && (
                        <div className={styles.posterLockOverlay}>
                          <Lock size={22} className={styles.posterLockIcon} />
                        </div>
                      )}

                      {/* Contenido Inferior: Cantidad de clases y Título del módulo */}
                      <div className={styles.posterBottomContent}>
                        <span className={styles.posterTrackTag}>
                          {trackTag}
                        </span>
                        <h4 className={styles.posterTitle}>
                          {moduleSection.title || `Módulo ${moduleNumber}`}
                        </h4>
                      </div>

                      {/* Hover Play Button Glow */}
                      {hasAccess && (
                        <div className={styles.posterHoverPlay} title="Comenzar a ver este módulo">
                          <Play size={20} fill="currentColor" />
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </section>
          );
        })}
      </div>

      {/* MODAL DE COMPRA / ACCESO RESTRINGIDO */}
      {purchaseModalOpen && (
        <div className={styles.modalBackdrop} onClick={() => setPurchaseModalOpen(false)}>
          <div className={styles.modalCard} onClick={(e) => e.stopPropagation()}>
            <div className={styles.modalIconRing}>
              <Lock size={28} />
            </div>
            <h3 className={styles.modalTitle}>Acceso Requerido</h3>
            <p className={styles.modalDesc}>
              El contenido de <strong>"{lockedItemName}"</strong> está reservado para los miembros inscritos en este programa.
            </p>
            <div className={styles.modalPriceBox}>
              <span>Precio del Programa</span>
              <strong>${course.price || 197} USD</strong>
            </div>
            <div className={styles.modalActions}>
              <button 
                type="button" 
                className={styles.modalCloseBtn}
                onClick={() => setPurchaseModalOpen(false)}
              >
                Cerrar
              </button>
              {course.checkoutUrl && (
                <a
                  href={course.checkoutUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={styles.modalBuyBtn}
                >
                  <span>Adquirir Acceso Ahora</span>
                  <ExternalLink size={15} />
                </a>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
