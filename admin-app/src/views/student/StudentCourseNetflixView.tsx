import React, { useRef, useState } from "react";
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

  // Refs para hacer scroll horizontal con las flechas < y > en cada sección
  const scrollContainers = useRef<Record<string, HTMLDivElement | null>>({});

  const scrollSection = (secId: string, direction: "left" | "right") => {
    const el = scrollContainers.current[secId];
    if (el) {
      const scrollAmount = 380;
      el.scrollBy({
        left: direction === "left" ? -scrollAmount : scrollAmount,
        behavior: "smooth",
      });
    }
  };

  const handleCardClick = (sec: CourseSection, les: CourseLesson) => {
    if (!hasAccess) {
      setLockedItemName(les.title);
      setPurchaseModalOpen(true);
      return;
    }
    // Si tiene acceso, abrir el reproductor en este módulo
    const firstTopic = les.topics && les.topics.length > 0 ? les.topics[0] : (les as unknown as CourseTopic);
    onSelectModule(sec, les, firstTopic);
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
        {sections.map((section, secIdx) => {
          const lessons = section.lessons || [];
          const secKey = section.id || `sec-${secIdx}`;

          return (
            <section key={secKey} className={styles.netflixSection}>
              {/* Encabezado de la Sección con Flechas de Navegación < y > */}
              <div className={styles.netflixSectionHeader}>
                <h3 className={styles.netflixSectionTitle}>
                  {section.title || `Sección ${secIdx + 1}`}
                </h3>
                <div className={styles.carouselNavControls}>
                  <button
                    type="button"
                    className={styles.carouselArrowBtn}
                    onClick={() => scrollSection(secKey, "left")}
                    aria-label="Desplazar hacia la izquierda"
                  >
                    <ChevronLeft size={20} />
                  </button>
                  <button
                    type="button"
                    className={styles.carouselArrowBtn}
                    onClick={() => scrollSection(secKey, "right")}
                    aria-label="Desplazar hacia la derecha"
                  >
                    <ChevronRight size={20} />
                  </button>
                </div>
              </div>

              {/* Fila Horizontal Desplazable de Tarjetas Tipo Portada (Poster 2:3) */}
              <div 
                ref={(el) => (scrollContainers.current[secKey] = el)}
                className={styles.netflixCarouselRow}
              >
                {lessons.map((lesson, lesIdx) => {
                  const moduleNumber = lesIdx + 1;
                  const cover = lesson.cover || section.cover || course.thumbnail;
                  const trackTag = section.title.toUpperCase();

                  return (
                    <div
                      key={lesson.id || `les-${lesIdx}`}
                      className={[
                        styles.netflixPosterCard,
                        !hasAccess ? styles.netflixCardLocked : "",
                      ].join(" ")}
                      onClick={() => handleCardClick(section, lesson)}
                    >
                      {/* Fondo de Portada con Imagen o Gradiente Vibrante */}
                      <div 
                        className={styles.posterBgImage}
                        style={{
                          backgroundImage: cover ? `url(${cover})` : undefined,
                        }}
                      />

                      {/* Degradado vertical de lectura */}
                      <div className={styles.posterGradientOverlay} />

                      {/* Tag Superior: Modulo 1, Modulo 2... */}
                      <div className={styles.posterTopBadge}>
                        <span>Módulo {moduleNumber}</span>
                      </div>

                      {/* Overlay de Bloqueo si no tiene acceso */}
                      {!hasAccess && (
                        <div className={styles.posterLockOverlay}>
                          <Lock size={22} className={styles.posterLockIcon} />
                        </div>
                      )}

                      {/* Contenido Inferior: Subtítulo de categoría y Título del módulo */}
                      <div className={styles.posterBottomContent}>
                        <span className={styles.posterTrackTag}>
                          {trackTag || course.title.toUpperCase()}
                        </span>
                        <h4 className={styles.posterTitle}>
                          {lesson.title}
                        </h4>
                      </div>

                      {/* Hover Play Button Glow */}
                      {hasAccess && (
                        <div className={styles.posterHoverPlay}>
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
