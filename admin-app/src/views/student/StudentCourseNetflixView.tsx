import React, { useRef, useState, useMemo } from "react";
import { 
  ArrowLeft, 
  ChevronLeft, 
  ChevronRight, 
  Lock, 
  Play, 
  CheckCircle2, 
  Sparkles,
  ExternalLink,
  ShieldAlert,
  X,
  ShoppingBag,
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
  const [inAppCheckoutUrl, setInAppCheckoutUrl] = useState<string | null>(null);

  const sections = course.sections || [];
  const progress = course.progress ?? 0;
  const hasAccess = !!course.hasAccess;
  const effectiveCheckoutUrl = course.checkoutUrl || (course.id ? `/?alezux_action=checkout&course_id=${course.id}` : null);

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

        {/* Botón de Regresar al aula */}
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
          {effectiveCheckoutUrl && (
            <button 
              type="button" 
              onClick={() => setInAppCheckoutUrl(effectiveCheckoutUrl)}
              className={styles.unlockCourseBtn}
            >
              <Sparkles size={16} />
              <span>Adquirir Formación {course.price ? `($${course.price} USD)` : ""}</span>
            </button>
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
                  const isCourseLabelsDisabled = course.show_card_labels === false || (course.show_card_labels as any) === '0' || (course.show_card_labels as any) === 'false';
                  const showLabels = !isCourseLabelsDisabled && !moduleSection.hide_card_labels;

                  return (
                    <div
                      key={moduleSection.id}
                      className={[
                        styles.netflixPosterCard,
                        !hasAccess ? styles.netflixCardLocked : "",
                      ].join(" ")}
                      onClick={() => handleCardClick(moduleSection, moduleNumber)}
                      title={moduleSection.title || `Módulo ${moduleNumber}`}
                    >
                      {/* Fondo de Portada con Imagen o Fallback */}
                      {cover ? (
                        <img 
                          src={cover} 
                          alt={moduleSection.title || `Módulo ${moduleNumber}`} 
                          className={styles.posterBgImage}
                          loading="lazy"
                        />
                      ) : (
                        <div className={styles.posterBgFallback}>
                          {academyLogo ? (
                            <img src={academyLogo} alt={academyName} className={styles.posterFallbackLogo} />
                          ) : (
                            <div className={styles.posterFallbackIcon}>🎓</div>
                          )}
                        </div>
                      )}

                      {/* Degradado vertical de lectura (limpio si las etiquetas están ocultas) */}
                      <div className={showLabels ? styles.posterGradientOverlay : styles.posterGradientOverlayClean} />

                      {/* Tag Superior: Módulo 1, Módulo 2... (solo si las etiquetas están activas) */}
                      {showLabels && (
                        <div className={styles.posterTopBadge}>
                          <span>Módulo {moduleNumber}</span>
                        </div>
                      )}

                      {/* Overlay de Bloqueo si no tiene acceso */}
                      {!hasAccess && (
                        <div className={styles.posterLockOverlay}>
                          <Lock size={22} className={styles.posterLockIcon} />
                        </div>
                      )}

                      {/* Contenido Inferior: Cantidad de clases y Título del módulo (solo si las etiquetas están activas) */}
                      {showLabels && (
                        <div className={styles.posterBottomContent}>
                          <span className={styles.posterTrackTag}>
                            {trackTag}
                          </span>
                          <h4 className={styles.posterTitle}>
                            {moduleSection.title || `Módulo ${moduleNumber}`}
                          </h4>
                        </div>
                      )}

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
            {/* Botón de cerrar tipo 'X' en la esquina superior derecha */}
            <button
              type="button"
              className={styles.modalCloseIconBtn}
              onClick={() => setPurchaseModalOpen(false)}
              aria-label="Cerrar ventana de información"
            >
              <X size={16} />
            </button>

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
                className={styles.modalBuyBtn}
                onClick={() => {
                  if (effectiveCheckoutUrl) {
                    setPurchaseModalOpen(false);
                    setInAppCheckoutUrl(effectiveCheckoutUrl);
                  }
                }}
              >
                <ShoppingBag size={16} />
                <span>Adquirir Formación Ahora</span>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* CHECKOUT INTEGRADO SIN SALIR DE LA PLATAFORMA */}
      {inAppCheckoutUrl && (
        <div className={styles.inAppCheckoutOverlay} onClick={() => setInAppCheckoutUrl(null)}>
          <div className={styles.inAppCheckoutContainer} onClick={(e) => e.stopPropagation()}>
            <div className={styles.inAppCheckoutHeader}>
              <div className={styles.inAppCheckoutHeaderTitle}>
                <ShoppingBag size={18} style={{ color: "var(--accent)" }} />
                <span>Inscripción y Pago Seguro — {course.title}</span>
              </div>
              <button
                type="button"
                className={styles.inAppCheckoutCloseBtn}
                onClick={() => setInAppCheckoutUrl(null)}
                aria-label="Cerrar pasarela de checkout"
              >
                <X size={18} />
              </button>
            </div>
            <iframe
              src={inAppCheckoutUrl}
              title={`Checkout ${course.title}`}
              className={styles.inAppCheckoutIframe}
            />
          </div>
        </div>
      )}
    </div>
  );
};
