import { useState, useEffect } from "react";
import { 
  Plus, 
  GripVertical, 
  FolderPlus, 
  FileText, 
  Trash2, 
  Check, 
  Clock, 
  ChevronRight, 
  ChevronDown,
  ArrowLeft,
  Search,
  Video,
  Paperclip,
  Edit3,
  Layers,
  Users,
  Image as ImageIcon,
  ExternalLink,
  Save,
  BookOpen,
  DollarSign,
  CreditCard,
  Sliders,
  Settings,
  Tag
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { 
  api, 
  Course, 
  CourseSection, 
  CourseLesson, 
  CourseTopic, 
  CourseFileAttachment,
  FinancePlan
} from "../services/api";
import { ModuleSkeleton } from "../components/arc/skeleton";
import styles from "./CoursesView.module.css";

interface EditingTopicState {
  sectionId: string;
  topicIndex: number;
  isNew: boolean;
  topic: CourseTopic;
}

interface EditingModuleCoverState {
  sectionId: string;
  sectionTitle: string;
  cover: string;
}

export function CoursesView() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [availablePlans, setAvailablePlans] = useState<FinancePlan[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modo de Vista: "grid" (catálogo de tarjetas) | "builder" (página de edición del curso)
  const [viewMode, setViewMode] = useState<"grid" | "builder">("grid");
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);

  // Sub-tab dentro del editor: "curriculum" (módulos y lecciones) | "settings" (configuración general y portadas)
  const [builderTab, setBuilderTab] = useState<"curriculum" | "settings">("curriculum");

  // Filtros de Catálogo
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState<"all" | "publish" | "draft">("all");

  // Modal para Nuevo Curso
  const [isNewCourseModalOpen, setIsNewCourseModalOpen] = useState(false);
  const [newTitle, setNewTitle] = useState("");
  const [newDescription, setNewDescription] = useState("");
  const [newThumbnail, setNewThumbnail] = useState("");
  const [isCreatingCourse, setIsCreatingCourse] = useState(false);

  // Estados de Configuración General del Curso en edición
  const [courseTitle, setCourseTitle] = useState("");
  const [courseDescription, setCourseDescription] = useState("");
  const [courseThumbnail, setCourseThumbnail] = useState("");
  const [courseBanner, setCourseBanner] = useState("");
  const [coursePrice, setCoursePrice] = useState<number | string>(0);
  const [courseStatus, setCourseStatus] = useState<"publish" | "draft">("publish");
  const [courseLinkedPlanId, setCourseLinkedPlanId] = useState<number | null>(null);

  // Estado del Constructor del Curso Activo (Builder)
  const [sections, setSections] = useState<CourseSection[]>([]);
  const [isSavingCurriculum, setIsSavingCurriculum] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [collapsedSections, setCollapsedSections] = useState<Record<string, boolean>>({});

  // Drag and Drop state
  const [draggedLesson, setDraggedLesson] = useState<{ sectionId: string; lessonIndex: number } | null>(null);

  // Modal Detallado de Topic / Lección
  const [editingTopicState, setEditingTopicState] = useState<EditingTopicState | null>(null);
  const [newFileName, setNewFileName] = useState("");
  const [newFileUrl, setNewFileUrl] = useState("");

  // Modal para Editar Portada de Módulo
  const [editingModuleCover, setEditingModuleCover] = useState<EditingModuleCoverState | null>(null);

  useEffect(() => {
    Promise.all([api.getCourses(), api.getPlans()])
      .then(([coursesData, plansData]) => {
        setCourses(coursesData);
        setAvailablePlans(plansData);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, []);

  // --- NAVEGACIÓN ENTRE VISTAS ---
  const handleOpenCourseBuilder = (course: Course) => {
    setSelectedCourse(course);
    setCourseTitle(course.title || "");
    setCourseDescription(course.description || "");
    setCourseThumbnail(course.thumbnail || "");
    setCourseBanner(course.banner || "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80");
    setCoursePrice(course.price ?? 0);
    setCourseStatus(course.status || "publish");
    setCourseLinkedPlanId(course.linkedPlanId ?? null);
    setSections(course.sections || []);
    setBuilderTab("curriculum");
    setSavedSuccess(false);
    setViewMode("builder");
  };

  const handleBackToGrid = () => {
    // Si tenemos cambios locales, refrescamos el curso en la lista principal
    if (selectedCourse) {
      setCourses((prev) =>
        prev.map((c) =>
          c.id === selectedCourse.id
            ? {
                ...c,
                title: courseTitle,
                description: courseDescription,
                thumbnail: courseThumbnail,
                banner: courseBanner,
                price: Number(coursePrice) || 0,
                status: courseStatus,
                linkedPlanId: courseLinkedPlanId,
                sections,
              }
            : c
        )
      );
    }
    setViewMode("grid");
  };

  // Guardar todos los datos del curso (metadatos + currículum)
  const handleSaveAllCourse = async () => {
    if (!selectedCourse) return;
    setIsSavingCurriculum(true);

    const updatedData: Partial<Course> = {
      title: courseTitle,
      description: courseDescription,
      thumbnail: courseThumbnail,
      banner: courseBanner,
      price: Number(coursePrice) || 0,
      status: courseStatus,
      linkedPlanId: courseLinkedPlanId,
      sections,
    };

    await api.saveCourse(selectedCourse.id, updatedData, sections);

    setSelectedCourse((prev) => (prev ? { ...prev, ...updatedData } : null));
    setCourses((prev) =>
      prev.map((c) => (c.id === selectedCourse.id ? { ...c, ...updatedData } : c))
    );

    setIsSavingCurriculum(false);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3500);
  };

  // Actualizar portada de módulo
  const handleUpdateSectionCover = (sectionId: string, cover: string) => {
    setSections((prev) =>
      prev.map((s) => (s.id === sectionId ? { ...s, cover } : s))
    );
  };

  const handleSaveModuleCoverModal = () => {
    if (!editingModuleCover) return;
    handleUpdateSectionCover(editingModuleCover.sectionId, editingModuleCover.cover);
    setEditingModuleCover(null);
  };

  // --- CREACIÓN DE NUEVO CURSO ---
  const handleCreateCourse = async () => {
    if (!newTitle.trim()) return;
    setIsCreatingCourse(true);
    const created = await api.createCourse(newTitle, newDescription, newThumbnail);
    setIsCreatingCourse(false);

    setCourses([created, ...courses]);
    setIsNewCourseModalOpen(false);
    setNewTitle("");
    setNewDescription("");
    setNewThumbnail("");

    // Abrir de inmediato el builder del curso recién creado
    handleOpenCourseBuilder(created);
  };

  // --- OPERACIONES DEL CONSTRUCTOR DE MÓDULOS ---
  const handleAddSection = () => {
    const newSection: CourseSection = {
      id: `sec-${Date.now()}`,
      title: `Nuevo Módulo ${sections.length + 1}`,
      cover: courseThumbnail || "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
      description: "",
      lessons: [
        {
          id: `top-${Date.now()}`,
          title: "Lección 1: Introducción y Objetivos",
          duration: "10m",
          cover: "",
          description: "Descripción de la lección.",
          video_url: "",
          files: [],
        },
      ],
    };
    setSections([...sections, newSection]);
  };

  const handleDeleteSection = (sectionId: string) => {
    if (window.confirm("¿Seguro que deseas eliminar este módulo y todas sus lecciones?")) {
      setSections(sections.filter((s) => s.id !== sectionId));
    }
  };

  const handleUpdateSectionTitle = (sectionId: string, title: string) => {
    setSections(sections.map((s) => (s.id === sectionId ? { ...s, title } : s)));
  };

  const toggleSectionCollapse = (sectionId: string) => {
    setCollapsedSections((prev) => ({ ...prev, [sectionId]: !prev[sectionId] }));
  };

  // --- GESTIÓN DE TOPICS / LECCIONES ---
  const handleOpenAddTopic = (sectionId: string) => {
    const section = sections.find((s) => s.id === sectionId);
    const count = section ? section.lessons.length + 1 : 1;

    setEditingTopicState({
      sectionId,
      topicIndex: count - 1,
      isNew: true,
      topic: {
        id: `top-${Date.now()}`,
        title: `Lección ${count}: Nueva Clase`,
        cover: "",
        duration: "15m",
        description: "",
        video_url: "",
        files: [],
      },
    });
    setNewFileName("");
    setNewFileUrl("");
  };

  const handleOpenEditTopic = (sectionId: string, topic: CourseLesson, index: number) => {
    setEditingTopicState({
      sectionId,
      topicIndex: index,
      isNew: false,
      topic: {
        id: topic.id,
        title: topic.title || "",
        cover: topic.cover || "",
        duration: topic.duration || "15m",
        description: topic.description || "",
        video_url: topic.video_url || "",
        files: topic.files ? [...topic.files] : [],
      },
    });
    setNewFileName("");
    setNewFileUrl("");
  };

  const handleDeleteTopic = (sectionId: string, topicId: string) => {
    setSections(
      sections.map((s) =>
        s.id === sectionId ? { ...s, lessons: s.lessons.filter((l) => l.id !== topicId) } : s
      )
    );
  };

  // Guardar cambios dentro del Modal de Topic
  const handleSaveTopicFromModal = () => {
    if (!editingTopicState) return;
    const { sectionId, topicIndex, isNew, topic } = editingTopicState;

    if (!topic.title.trim()) {
      alert("El título de la lección no puede estar vacío.");
      return;
    }

    setSections((prev) =>
      prev.map((s) => {
        if (s.id !== sectionId) return s;
        const newLessons = [...s.lessons];
        if (isNew) {
          newLessons.push(topic);
        } else {
          newLessons[topicIndex] = topic;
        }
        return { ...s, lessons: newLessons };
      })
    );

    setEditingTopicState(null);
  };

  // Añadir archivo complementario a la lección en edición
  const handleAddFileToTopic = () => {
    if (!editingTopicState || !newFileName.trim() || !newFileUrl.trim()) return;

    const newFile: CourseFileAttachment = {
      id: `f-${Date.now()}`,
      name: newFileName.trim(),
      url: newFileUrl.trim(),
    };

    setEditingTopicState({
      ...editingTopicState,
      topic: {
        ...editingTopicState.topic,
        files: [...(editingTopicState.topic.files || []), newFile],
      },
    });

    setNewFileName("");
    setNewFileUrl("");
  };

  const handleRemoveFileFromTopic = (fileIndex: number) => {
    if (!editingTopicState) return;
    const updatedFiles = (editingTopicState.topic.files || []).filter((_, i) => i !== fileIndex);
    setEditingTopicState({
      ...editingTopicState,
      topic: {
        ...editingTopicState.topic,
        files: updatedFiles,
      },
    });
  };

  // --- DRAG AND DROP HANDLERS ---
  const handleDragStart = (sectionId: string, lessonIndex: number) => {
    setDraggedLesson({ sectionId, lessonIndex });
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
  };

  const handleDrop = (targetSectionId: string, targetLessonIndex: number) => {
    if (!draggedLesson) return;
    const { sectionId: sourceSectionId, lessonIndex: sourceLessonIndex } = draggedLesson;

    const newSections = [...sections];
    const sourceSection = newSections.find((s) => s.id === sourceSectionId);
    const targetSection = newSections.find((s) => s.id === targetSectionId);

    if (!sourceSection || !targetSection) return;

    const [movedLesson] = sourceSection.lessons.splice(sourceLessonIndex, 1);
    targetSection.lessons.splice(targetLessonIndex, 0, movedLesson);

    setSections(newSections);
    setDraggedLesson(null);
  };

  // --- GUARDADO DE CURRÍCULUM EN SERVIDOR ---
  const handleSaveCurriculum = async () => {
    if (!selectedCourse) return;
    setIsSavingCurriculum(true);
    await api.saveCourseCurriculum(selectedCourse.id, sections);
    setIsSavingCurriculum(false);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  // Filtrado de cursos en la vista Grid
  const filteredCourses = courses.filter((c) => {
    const matchesSearch =
      c.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      c.description.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesStatus = statusFilter === "all" || c.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  if (isLoading) {
    return <ModuleSkeleton type="courses" />;
  }

  // =========================================================================
  // RENDER: VISTA 2 (OTRA PÁGINA) - BUILDER / GESTIÓN DE MÓDULOS Y TOPICS
  // =========================================================================
  if (viewMode === "builder" && selectedCourse) {
    const totalTopics = sections.reduce((acc, s) => acc + (s.lessons?.length || 0), 0);

    return (
      <div className={styles.builderView}>
        {/* Barra Superior con botón para volver al catálogo */}
        <div className={styles.builderNav}>
          <div className={styles.builderNavLeft}>
            <button type="button" className={styles.backBtn} onClick={handleBackToGrid}>
              <ArrowLeft size={16} /> Volver a Cursos
            </button>

            <div className={styles.builderBreadcrumb}>
              <h2 className={styles.builderCourseName}>{courseTitle || selectedCourse.title}</h2>
              <div className={styles.builderMetaBadges}>
                <Badge variant={courseStatus === "publish" ? "success" : "neutral"} size="sm">
                  {courseStatus === "publish" ? "Habilitado / Publicado" : "Deshabilitado / Borrador"}
                </Badge>
                <span>&bull;</span>
                <span>{sections.length} Módulos</span>
                <span>&bull;</span>
                <span>{totalTopics} Topics / Lecciones</span>
                {coursePrice !== undefined && coursePrice !== "" && Number(coursePrice) > 0 && (
                  <>
                    <span>&bull;</span>
                    <span style={{ color: "#38bdf8", fontWeight: 700 }}>${coursePrice} USD</span>
                  </>
                )}
                {courseLinkedPlanId && (
                  <>
                    <span>&bull;</span>
                    <span style={{ color: "#c084fc", fontWeight: 600 }}>
                      Plan: {availablePlans.find((p) => p.id === courseLinkedPlanId)?.name || `#${courseLinkedPlanId}`}
                    </span>
                  </>
                )}
              </div>
            </div>
          </div>

          <div className={styles.builderNavRight}>
            {savedSuccess && (
              <span className={styles.saveAlert}>
                <Check size={16} /> Cambios guardados correctamente
              </span>
            )}
            {builderTab === "curriculum" && (
              <Button variant="secondary" onClick={handleAddSection}>
                <FolderPlus size={16} /> + Módulo
              </Button>
            )}
            <Button variant="primary" loading={isSavingCurriculum} onClick={handleSaveAllCourse}>
              <Save size={16} /> Guardar Curso
            </Button>
          </div>
        </div>

        {/* Hero Showcase con Banner Panorámico y Portada 16:9 */}
        <div
          className={styles.builderHeroBanner}
          style={{
            backgroundImage: `url(${courseBanner || "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80"})`,
          }}
        >
          <div className={styles.builderHeroOverlay}>
            <div className={styles.builderHeroLeft}>
              {/* Portada del curso con hover y preview */}
              <div
                className={styles.builderHeroThumbWrapper}
                onClick={() => setBuilderTab("settings")}
                title="Clic para cambiar portada y banner en Configuración"
              >
                <img
                  src={courseThumbnail || selectedCourse.thumbnail}
                  alt={courseTitle}
                  className={styles.builderHeroThumbImg}
                  onError={(e) => {
                    (e.target as HTMLElement).style.display = "none";
                  }}
                />
                <div className={styles.builderHeroThumbOverlay}>
                  <Edit3 size={13} />
                  <span>Cambiar</span>
                </div>
              </div>

              {/* Información y Badges del Curso */}
              <div className={styles.builderHeroInfo}>
                <h3 className={styles.builderHeroTitle}>{courseTitle || selectedCourse.title}</h3>
                <p className={styles.builderHeroDesc}>
                  {courseDescription || selectedCourse.description || "Sin descripción asignada para este curso."}
                </p>
                <div className={styles.builderHeroBadges}>
                  <Badge variant={courseStatus === "publish" ? "success" : "neutral"} size="sm">
                    {courseStatus === "publish" ? "Habilitado" : "Borrador"}
                  </Badge>
                  <span className={styles.heroPriceTag}>
                    <DollarSign size={13} /> {coursePrice && Number(coursePrice) > 0 ? `${coursePrice} USD` : "Gratis / Incluido"}
                  </span>
                  {courseLinkedPlanId ? (
                    <span className={styles.heroPlanTag}>
                      <CreditCard size={13} /> Plan: {availablePlans.find((p) => p.id === courseLinkedPlanId)?.name || `#${courseLinkedPlanId}`}
                    </span>
                  ) : (
                    <span className={styles.heroPlanTag} style={{ opacity: 0.8 }}>
                      <Tag size={12} /> Sin Plan Vinculado
                    </span>
                  )}
                  <span style={{ fontSize: 12, color: "rgba(255,255,255,0.7)" }}>
                    {sections.length} Módulos &bull; {totalTopics} Lecciones
                  </span>
                </div>
              </div>
            </div>

            <div className={styles.builderHeroActions}>
              <Button
                variant={builderTab === "settings" ? "secondary" : "primary"}
                onClick={() => setBuilderTab(builderTab === "settings" ? "curriculum" : "settings")}
              >
                <Settings size={15} />
                {builderTab === "settings" ? "Ver Módulos" : "Configurar Portada, Banner y Precio"}
              </Button>
            </div>
          </div>
        </div>

        {/* Sub-Tabs de Navegación entre Módulos y Ajustes Generales */}
        <div className={styles.builderSubTabsBar}>
          <div className={styles.builderTabs}>
            <button
              type="button"
              className={[
                styles.builderTabBtn,
                builderTab === "curriculum" ? styles.builderTabBtnActive : "",
              ].join(" ")}
              onClick={() => setBuilderTab("curriculum")}
            >
              <Layers size={16} /> Estructura de Módulos ({sections.length})
            </button>
            <button
              type="button"
              className={[
                styles.builderTabBtn,
                builderTab === "settings" ? styles.builderTabBtnActive : "",
              ].join(" ")}
              onClick={() => setBuilderTab("settings")}
            >
              <Settings size={16} /> Configuración General y Portadas
            </button>
          </div>

          <div style={{ display: "flex", gap: "8px" }}>
            {builderTab === "curriculum" && (
              <Button variant="secondary" size="sm" onClick={handleAddSection}>
                <Plus size={14} /> Nuevo Módulo
              </Button>
            )}
          </div>
        </div>

        {/* =========================================================================
            PESTAÑA 1: CONFIGURACIÓN GENERAL Y PORTADAS DEL CURSO
           ========================================================================= */}
        {builderTab === "settings" && (
          <div className={styles.courseSettingsCard}>
            <div className={styles.settingsSectionHeader}>
              <div>
                <h3 className={styles.settingsTitle}>Configuración General y Portadas del Curso</h3>
                <p className={styles.settingsDesc}>
                  Cambia la imagen de portada, la imagen de banner, el nombre, la descripción, el precio, el estado de habilitación y la vinculación a planes.
                </p>
              </div>
              <Button variant="primary" loading={isSavingCurriculum} onClick={handleSaveAllCourse}>
                <Save size={16} /> Guardar Cambios
              </Button>
            </div>

            {/* 1. SECCIÓN DE IMÁGENES: PORTADA Y BANNER */}
            <div className={styles.settingsGroup}>
              <h4 className={styles.settingsGroupTitle}>
                <ImageIcon size={18} /> Imágenes Principales del Curso
              </h4>

              <div className={styles.imagesGrid}>
                {/* Portada 16:9 */}
                <div className={styles.imageCard}>
                  <div className={styles.imageCardHeader}>
                    <label className={styles.label}>Imagen de Portada (Miniatura / Card)</label>
                    <span className={styles.miniLabel}>Recomendado: 16:9 (600x338 px)</span>
                  </div>
                  <div className={styles.coverPreviewContainer}>
                    {courseThumbnail ? (
                      <img
                        src={courseThumbnail}
                        alt="Portada del Curso"
                        className={styles.coverPreviewImgLarge}
                        onError={(e) => {
                          (e.target as HTMLElement).style.display = "none";
                        }}
                      />
                    ) : (
                      <div className={styles.placeholderLarge}>
                        <ImageIcon size={32} />
                        <span>Sin imagen de portada</span>
                      </div>
                    )}
                  </div>
                  <Input
                    placeholder="https://... (URL de la imagen de portada)"
                    value={courseThumbnail}
                    onChange={(e) => setCourseThumbnail(e.target.value)}
                  />
                </div>

                {/* Banner Panorámico 3:1 */}
                <div className={styles.imageCard}>
                  <div className={styles.imageCardHeader}>
                    <label className={styles.label}>Imagen de Banner (Cabecera Panorámica)</label>
                    <span className={styles.miniLabel}>Recomendado: 3:1 (1200x400 px)</span>
                  </div>
                  <div className={styles.bannerPreviewContainer}>
                    {courseBanner ? (
                      <img
                        src={courseBanner}
                        alt="Banner del Curso"
                        className={styles.bannerPreviewImgLarge}
                        onError={(e) => {
                          (e.target as HTMLElement).style.display = "none";
                        }}
                      />
                    ) : (
                      <div className={styles.placeholderLarge}>
                        <ImageIcon size={32} />
                        <span>Sin imagen de banner</span>
                      </div>
                    )}
                  </div>
                  <Input
                    placeholder="https://... (URL del banner panorámico)"
                    value={courseBanner}
                    onChange={(e) => setCourseBanner(e.target.value)}
                  />
                </div>
              </div>
            </div>

            {/* 2. SECCIÓN DE INFORMACIÓN BÁSICA: NOMBRE Y DESCRIPCIÓN */}
            <div className={styles.settingsGroup}>
              <h4 className={styles.settingsGroupTitle}>
                <FileText size={18} /> Información del Curso
              </h4>

              <div className={styles.formGroup}>
                <Input
                  label="Nombre del Curso *"
                  placeholder="Ej: Master en Marketing Digital & Performance"
                  value={courseTitle}
                  onChange={(e) => setCourseTitle(e.target.value)}
                />
              </div>

              <div className={styles.formGroup}>
                <label className={styles.label}>Descripción Completa del Curso</label>
                <textarea
                  placeholder="Describe detalladamente los temas, conocimientos adquiridos y beneficios de este curso..."
                  value={courseDescription}
                  onChange={(e) => setCourseDescription(e.target.value)}
                  className={styles.textarea}
                  rows={4}
                />
              </div>
            </div>

            {/* 3. COMERCIALIZACIÓN, PRECIO, PLAN Y ESTADO */}
            <div className={styles.settingsGroup}>
              <h4 className={styles.settingsGroupTitle}>
                <DollarSign size={18} /> Comercialización, Precio y Publicación
              </h4>

              <div className={styles.pricingAndStatusGrid}>
                {/* Precio */}
                <div className={styles.formGroup}>
                  <label className={styles.label}>Precio del Curso ($ USD)</label>
                  <div className={styles.inputWithIcon}>
                    <span className={styles.currencyPrefix}>$</span>
                    <input
                      type="number"
                      min="0"
                      step="1"
                      placeholder="0.00"
                      value={coursePrice}
                      onChange={(e) => setCoursePrice(e.target.value === "" ? "" : Number(e.target.value))}
                      className={styles.currencyInput}
                    />
                  </div>
                  <span className={styles.inputHelper}>Precio de referencia para venta directa o catálogo.</span>
                </div>

                {/* Switch de Estado */}
                <div className={styles.statusToggleBox}>
                  <label className={styles.label}>Estado en la Plataforma</label>
                  <div className={styles.switchRow}>
                    <Switch
                      checked={courseStatus === "publish"}
                      onCheckedChange={(checked) => setCourseStatus(checked ? "publish" : "draft")}
                      id="course-status-switch"
                    />
                    <label htmlFor="course-status-switch" className={styles.switchLabel}>
                      <span className={courseStatus === "publish" ? styles.statusTextActive : styles.statusTextDraft}>
                        {courseStatus === "publish" ? "Habilitado (Público)" : "Deshabilitado (Borrador)"}
                      </span>
                      <span className={styles.switchSubtext}>
                        {courseStatus === "publish"
                          ? "El curso está activo y disponible para alumnos inscritos."
                          : "El curso está en modo borrador y oculto en la plataforma."}
                      </span>
                    </label>
                  </div>
                </div>

                {/* Vinculación a Plan de Finanzas */}
                <div className={styles.formGroup}>
                  <label className={styles.label}>Vincular a Plan de Finanzas</label>
                  <div className={styles.selectWrapper}>
                    <select
                      value={courseLinkedPlanId ?? ""}
                      onChange={(e) => {
                        const val = e.target.value;
                        setCourseLinkedPlanId(val === "" ? null : Number(val));
                      }}
                      className={styles.styledSelect}
                    >
                      <option value="">Sin vincular a ningún plan (Venta directa o gratuita)</option>
                      {availablePlans.map((plan) => (
                        <option key={plan.id} value={plan.id}>
                          {plan.name} — {plan.totalQuotas} {plan.totalQuotas === 1 ? "pago de" : "cuotas de"} ${plan.quotaAmount} (Total: ${plan.totalAmount})
                        </option>
                      ))}
                    </select>
                  </div>
                  <span className={styles.inputHelper}>
                    {courseLinkedPlanId ? (
                      <span className={styles.linkedPlanNotice}>
                        <Check size={13} /> Vinculado al Plan #{courseLinkedPlanId}.
                      </span>
                    ) : (
                      "Permite sincronizar este curso con planes de cuotas creados en Finanzas."
                    )}
                  </span>
                </div>
              </div>
            </div>

            {/* Footer con botón de guardado */}
            <div className={styles.settingsFooter}>
              <Button variant="secondary" onClick={() => setBuilderTab("curriculum")}>
                Volver a Módulos y Lecciones
              </Button>
              <Button variant="primary" loading={isSavingCurriculum} onClick={handleSaveAllCourse}>
                <Save size={16} /> Guardar Todos los Cambios
              </Button>
            </div>
          </div>
        )}

        {/* =========================================================================
            PESTAÑA 2: ESTRUCTURA DE MÓDULOS Y TOPICS
           ========================================================================= */}
        {builderTab === "curriculum" && (
          <div className={styles.modulesContainer}>
            {sections.map((section, sIdx) => {
              const isCollapsed = !!collapsedSections[section.id];
              const topicsCount = section.lessons ? section.lessons.length : 0;

              return (
                <div key={section.id} className={styles.moduleCard}>
                  {/* Encabezado del Módulo con Portada de Módulo */}
                  <div
                    className={[
                      styles.moduleHeader,
                      !isCollapsed ? styles.moduleHeaderOpen : "",
                    ].join(" ")}
                  >
                    <div className={styles.moduleHeaderLeft}>
                      <button
                        type="button"
                        className={styles.collapseBtn}
                        onClick={() => toggleSectionCollapse(section.id)}
                        title={isCollapsed ? "Expandir módulo" : "Colapsar módulo"}
                      >
                        {isCollapsed ? <ChevronRight size={18} /> : <ChevronDown size={18} />}
                      </button>

                      {/* Portada del Módulo */}
                      <div
                        className={styles.moduleHeaderCover}
                        onClick={() =>
                          setEditingModuleCover({
                            sectionId: section.id,
                            sectionTitle: section.title,
                            cover: section.cover || "",
                          })
                        }
                        title="Clic para cambiar portada de este módulo"
                      >
                        {section.cover ? (
                          <img
                            src={section.cover}
                            alt={section.title}
                            className={styles.moduleHeaderCoverImg}
                            onError={(e) => {
                              (e.target as HTMLElement).style.display = "none";
                            }}
                          />
                        ) : (
                          <div className={styles.moduleHeaderCoverPlaceholder}>
                            <ImageIcon size={14} />
                            <span>+ Portada</span>
                          </div>
                        )}
                        <div className={styles.moduleCoverOverlay}>
                          <Edit3 size={12} />
                        </div>
                      </div>

                      <span className={styles.moduleIndexPill}>Módulo {sIdx + 1}</span>

                      <input
                        type="text"
                        value={section.title}
                        onChange={(e) => handleUpdateSectionTitle(section.id, e.target.value)}
                        className={styles.moduleTitleInput}
                        placeholder="Nombre del Módulo..."
                      />
                    </div>

                    <div className={styles.moduleHeaderRight}>
                      <span className={styles.moduleCountBadge}>
                        {topicsCount} {topicsCount === 1 ? "Topic" : "Topics"}
                      </span>
                      <Button
                        variant="secondary"
                        size="sm"
                        onClick={() =>
                          setEditingModuleCover({
                            sectionId: section.id,
                            sectionTitle: section.title,
                            cover: section.cover || "",
                          })
                        }
                        title="Cambiar la imagen de portada de este módulo"
                      >
                        <ImageIcon size={14} /> Portada
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleOpenAddTopic(section.id)}
                        title="Añadir Topic a este módulo"
                      >
                        <Plus size={15} /> Topic
                      </Button>
                      <button
                        type="button"
                        className={styles.deleteBtn}
                        onClick={() => handleDeleteSection(section.id)}
                        title="Eliminar módulo"
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </div>

                  {/* Contenido Expandido del Módulo */}
                  {!isCollapsed && (
                    <>
                      {/* Barra de Portada Expandida del Módulo */}
                      <div className={styles.moduleCoverBar}>
                        <div
                          className={styles.moduleCoverBarPreview}
                          onClick={() =>
                            setEditingModuleCover({
                              sectionId: section.id,
                              sectionTitle: section.title,
                              cover: section.cover || "",
                            })
                          }
                          title="Cambiar imagen de portada"
                        >
                          {section.cover ? (
                            <img src={section.cover} alt={section.title} className={styles.moduleCoverBarImg} />
                          ) : (
                            <div className={styles.moduleCoverBarPlaceholder}>
                              <ImageIcon size={18} />
                              <span>Sin portada</span>
                            </div>
                          )}
                          <span className={styles.moduleCoverChangeHint}>Cambiar</span>
                        </div>
                        <div className={styles.moduleCoverBarInputs}>
                          <label className={styles.miniLabel}>URL de Portada del Módulo:</label>
                          <input
                            type="text"
                            value={section.cover || ""}
                            onChange={(e) => handleUpdateSectionCover(section.id, e.target.value)}
                            placeholder="https://... (URL de la imagen de portada para este módulo)"
                            className={styles.moduleCoverInlineInput}
                          />
                        </div>
                      </div>
                    <div className={styles.topicsContainer}>
                      {section.lessons && section.lessons.length > 0 ? (
                        section.lessons.map((lesson, lIdx) => (
                          <div
                            key={lesson.id}
                            className={styles.topicRow}
                            draggable
                            onDragStart={() => handleDragStart(section.id, lIdx)}
                            onDragOver={handleDragOver}
                            onDrop={() => handleDrop(section.id, lIdx)}
                          >
                            <div className={styles.dragHandle} title="Arrastrar para reordenar">
                              <GripVertical size={16} />
                            </div>

                            {/* Portada / Miniatura del Topic */}
                            {lesson.cover ? (
                              <img
                                src={lesson.cover}
                                alt={lesson.title}
                                className={styles.topicThumb}
                              />
                            ) : (
                              <div className={styles.topicThumbPlaceholder}>
                                <ImageIcon size={18} />
                              </div>
                            )}

                            {/* Información y Badges del Topic */}
                            <div className={styles.topicInfo}>
                              <h4 className={styles.topicTitle}>{lesson.title}</h4>
                              <div className={styles.topicBadges}>
                                {lesson.video_url && (
                                  <span
                                    className={[styles.topicBadge, styles.topicBadgeVideo].join(" ")}
                                    title={`Video: ${lesson.video_url}`}
                                  >
                                    <Video size={12} /> Video
                                  </span>
                                )}

                                {lesson.files && lesson.files.length > 0 && (
                                  <span
                                    className={[styles.topicBadge, styles.topicBadgeFiles].join(" ")}
                                  >
                                    <Paperclip size={12} /> {lesson.files.length} Archivos
                                  </span>
                                )}

                                {lesson.description && (
                                  <span
                                    className={[styles.topicBadge, styles.topicBadgeDesc].join(" ")}
                                  >
                                    <FileText size={12} /> Notas
                                  </span>
                                )}

                                {lesson.duration && (
                                  <span className={styles.topicBadge}>
                                    <Clock size={11} /> {lesson.duration}
                                  </span>
                                )}
                              </div>
                            </div>

                            {/* Acciones del Topic */}
                            <div className={styles.topicActions}>
                              <Button
                                variant="secondary"
                                size="sm"
                                onClick={() => handleOpenEditTopic(section.id, lesson, lIdx)}
                              >
                                <Edit3 size={14} /> Editar
                              </Button>
                              <button
                                type="button"
                                className={styles.deleteBtn}
                                onClick={() => handleDeleteTopic(section.id, lesson.id)}
                                title="Eliminar lección"
                              >
                                <Trash2 size={15} />
                              </button>
                            </div>
                          </div>
                        ))
                      ) : (
                        <div
                          className={styles.emptyTopicsNotice}
                          onDragOver={handleDragOver}
                          onDrop={() => handleDrop(section.id, 0)}
                        >
                          <p>No hay lecciones o topics creados en este módulo todavía.</p>
                          <Button
                            variant="secondary"
                            size="sm"
                            onClick={() => handleOpenAddTopic(section.id)}
                          >
                            <Plus size={14} /> Agregar Primer Topic
                          </Button>
                        </div>
                      )}
                    </div>

                    {section.lessons && section.lessons.length > 0 && (
                      <div className={styles.moduleBottomBar}>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleOpenAddTopic(section.id)}
                        >
                          <Plus size={14} /> Agregar otro Topic a este módulo
                        </Button>
                      </div>
                    )}
                  </>
                )}
              </div>
            );
          })}

          {sections.length === 0 && (
            <div className={styles.emptyGrid}>
              <Layers size={36} />
              <h3>Este curso aún no tiene módulos configurados</h3>
              <p>Comienza creando el primer módulo para organizar tus clases y contenidos.</p>
              <Button variant="primary" onClick={handleAddSection}>
                <Plus size={16} /> Crear Primer Módulo
              </Button>
            </div>
          )}
        </div>
        )}

        {/* MODAL PARA CAMBIAR PORTADA DEL MÓDULO */}
        {editingModuleCover && (
          <Modal
            isOpen={true}
            onClose={() => setEditingModuleCover(null)}
            title={`Portada del Módulo: ${editingModuleCover.sectionTitle}`}
            description="Configura la imagen de portada para este módulo. Esta imagen acompañará el temario y las lecciones del módulo."
            maxWidth="640px"
            footer={
              <>
                <Button variant="ghost" onClick={() => setEditingModuleCover(null)}>
                  Cancelar
                </Button>
                <Button variant="primary" onClick={handleSaveModuleCoverModal}>
                  Guardar Portada
                </Button>
              </>
            }
          >
            <div className={styles.formGroup}>
              <Input
                label="URL de Imagen de Portada del Módulo *"
                placeholder="https://... (Enlace directo a la imagen)"
                value={editingModuleCover.cover}
                onChange={(e) =>
                  setEditingModuleCover({ ...editingModuleCover, cover: e.target.value })
                }
              />
            </div>

            {editingModuleCover.cover ? (
              <div className={styles.coverModalPreviewWrapper}>
                <img
                  src={editingModuleCover.cover}
                  alt="Previsualización Portada de Módulo"
                  className={styles.coverModalPreviewImg}
                  onError={(e) => {
                    (e.target as HTMLElement).style.display = "none";
                  }}
                />
                <span className={styles.pageSubtitle}>Previsualización en proporción 16:9</span>
              </div>
            ) : (
              <div className={styles.coverModalPlaceholderWrapper}>
                <ImageIcon size={36} />
                <p>Pega un enlace de imagen para ver la previsualización del módulo.</p>
              </div>
            )}

            {/* Muestras rápidas de portadas */}
            <div className={styles.imageSuggestions}>
              <span className={styles.miniLabel}>Sugerencias de imágenes HD para formación:</span>
              <div className={styles.suggestionChips}>
                {[
                  { label: "Marketing / Negocios", url: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80" },
                  { label: "Tecnología / Código", url: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80" },
                  { label: "Estrategia / Datos", url: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&auto=format&fit=crop&q=80" },
                  { label: "Creatividad / Video", url: "https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=600&auto=format&fit=crop&q=80" },
                ].map((sug, sIdx) => (
                  <button
                    key={sIdx}
                    type="button"
                    className={styles.suggestionChip}
                    onClick={() => setEditingModuleCover({ ...editingModuleCover, cover: sug.url })}
                  >
                    {sug.label}
                  </button>
                ))}
              </div>
            </div>
          </Modal>
        )}

        {/* MODAL DETALLADO PARA EDITAR TOPIC / LECCIÓN */}
        {editingTopicState && (
          <Modal
            isOpen={true}
            onClose={() => setEditingTopicState(null)}
            title={
              editingTopicState.isNew
                ? "Nuevo Topic / Lección"
                : `Editar: ${editingTopicState.topic.title}`
            }
            description="Configura el título, portada, video, notas y archivos complementarios descargables."
            maxWidth="740px"
            footer={
              <>
                <Button variant="ghost" onClick={() => setEditingTopicState(null)}>
                  Cancelar
                </Button>
                <Button variant="primary" onClick={handleSaveTopicFromModal}>
                  Guardar Lección
                </Button>
              </>
            }
          >
            <div className={styles.formGroup}>
              <Input
                label="Título del Topic / Lección *"
                placeholder="Ej: Lección 1: Cómo configurar la campaña..."
                value={editingTopicState.topic.title}
                onChange={(e) =>
                  setEditingTopicState({
                    ...editingTopicState,
                    topic: { ...editingTopicState.topic, title: e.target.value },
                  })
                }
              />
            </div>

            {/* Portada de la lección */}
            <div className={styles.formGroup}>
              <Input
                label="URL de Portada / Miniatura del Topic"
                placeholder="https://... (URL de la imagen de portada)"
                value={editingTopicState.topic.cover || ""}
                onChange={(e) =>
                  setEditingTopicState({
                    ...editingTopicState,
                    topic: { ...editingTopicState.topic, cover: e.target.value },
                  })
                }
              />
              {editingTopicState.topic.cover && (
                <div className={styles.coverPreviewRow}>
                  <img
                    src={editingTopicState.topic.cover}
                    alt="Preview Portada"
                    className={styles.coverPreviewImg}
                    onError={(e) => {
                      (e.target as HTMLElement).style.display = "none";
                    }}
                  />
                  <span className={styles.pageSubtitle}>Previsualización de Portada</span>
                </div>
              )}
            </div>

            {/* Link del Video y Duración */}
            <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr", gap: "16px" }}>
              <div className={styles.formGroup}>
                <Input
                  label="Enlace del Video (YouTube, Vimeo, BunnyCDN, MP4)"
                  placeholder="https://www.youtube.com/watch?v=... o https://vimeo.com/..."
                  value={editingTopicState.topic.video_url || ""}
                  onChange={(e) =>
                    setEditingTopicState({
                      ...editingTopicState,
                      topic: { ...editingTopicState.topic, video_url: e.target.value },
                    })
                  }
                />
              </div>

              <div className={styles.formGroup}>
                <Input
                  label="Duración Estimada"
                  placeholder="Ej: 15m"
                  value={editingTopicState.topic.duration || "15m"}
                  onChange={(e) =>
                    setEditingTopicState({
                      ...editingTopicState,
                      topic: { ...editingTopicState.topic, duration: e.target.value },
                    })
                  }
                />
              </div>
            </div>

            {/* Descripción / Contenido */}
            <div className={styles.formGroup}>
              <label className={styles.label}>Descripción o Notas de la Lección</label>
              <textarea
                placeholder="Escribe el resumen de la lección, notas clave, conceptos a recordar..."
                value={editingTopicState.topic.description || ""}
                onChange={(e) =>
                  setEditingTopicState({
                    ...editingTopicState,
                    topic: { ...editingTopicState.topic, description: e.target.value },
                  })
                }
                className={styles.textarea}
                rows={4}
              />
            </div>

            {/* Archivos Complementarios Descargables */}
            <div className={styles.filesSection}>
              <div className={styles.filesHeader}>
                <span className={styles.filesTitle}>
                  Archivos Complementarios ({editingTopicState.topic.files?.length || 0})
                </span>
                <span className={styles.pageSubtitle}>Recursos descargables para el alumno</span>
              </div>

              {/* Lista de archivos actuales */}
              {editingTopicState.topic.files && editingTopicState.topic.files.length > 0 && (
                <div className={styles.filesList}>
                  {editingTopicState.topic.files.map((file, fIdx) => (
                    <div key={file.id || fIdx} className={styles.fileItem}>
                      <div className={styles.fileItemLeft}>
                        <Paperclip size={15} style={{ color: "var(--accent)", flexShrink: 0 }} />
                        <span>{file.name}</span>
                        {file.url && (
                          <a
                            href={file.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={styles.fileItemLink}
                            title="Probar enlace"
                          >
                            <ExternalLink size={12} />
                          </a>
                        )}
                      </div>
                      <button
                        type="button"
                        className={styles.deleteBtn}
                        onClick={() => handleRemoveFileFromTopic(fIdx)}
                        title="Eliminar este archivo"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  ))}
                </div>
              )}

              {/* Caja para añadir nuevo archivo */}
              <div className={styles.addFileBox}>
                <Input
                  placeholder="Nombre: Ej: Plantilla_PDF.pdf"
                  value={newFileName}
                  onChange={(e) => setNewFileName(e.target.value)}
                />
                <Input
                  placeholder="URL de descarga: https://..."
                  value={newFileUrl}
                  onChange={(e) => setNewFileUrl(e.target.value)}
                />
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={handleAddFileToTopic}
                  disabled={!newFileName.trim() || !newFileUrl.trim()}
                >
                  <Plus size={14} /> Añadir Archivo
                </Button>
              </div>
            </div>
          </Modal>
        )}
      </div>
    );
  }

  // =========================================================================
  // RENDER: VISTA 1 - CATÁLOGO DE CURSOS EN GRID
  // =========================================================================
  return (
    <div className={styles.container}>
      {/* Barra Superior */}
      <div className={styles.topHeader}>
        <div>
          <h1 className={styles.pageTitle}>Gestión y Creación de Cursos</h1>
          <p className={styles.pageSubtitle}>
            Administra tus formaciones en cuadrícula, crea módulos y gestiona cada lección con sus videos y recursos.
          </p>
        </div>
        <Button variant="primary" onClick={() => setIsNewCourseModalOpen(true)}>
          <Plus size={16} /> Crear Nuevo Curso
        </Button>
      </div>

      {/* Barra de Controles y Búsqueda */}
      <div className={styles.controlsBar}>
        <div className={styles.searchBox}>
          <Search size={16} className={styles.searchIcon} />
          <input
            type="text"
            placeholder="Buscar curso por nombre o descripción..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className={styles.searchInput}
          />
        </div>

        <div className={styles.filterTabs}>
          <button
            type="button"
            className={[styles.filterBtn, statusFilter === "all" ? styles.filterTabActive : ""].join(" ")}
            onClick={() => setStatusFilter("all")}
          >
            Todos ({courses.length})
          </button>
          <button
            type="button"
            className={[styles.filterBtn, statusFilter === "publish" ? styles.filterTabActive : ""].join(" ")}
            onClick={() => setStatusFilter("publish")}
          >
            Publicados ({courses.filter((c) => c.status === "publish").length})
          </button>
          <button
            type="button"
            className={[styles.filterBtn, statusFilter === "draft" ? styles.filterTabActive : ""].join(" ")}
            onClick={() => setStatusFilter("draft")}
          >
            Borradores ({courses.filter((c) => c.status === "draft").length})
          </button>
        </div>
      </div>

      {/* Grid de Tarjetas de Cursos */}
      <div className={styles.coursesGrid}>
        {filteredCourses.map((course) => {
          const totalModules = course.sections ? course.sections.length : 0;
          const totalTopics = course.sections
            ? course.sections.reduce((acc, s) => acc + (s.lessons?.length || 0), 0)
            : 0;

          return (
            <div key={course.id} className={styles.courseCard}>
              {/* Portada 16:9 con Badge de Estado y Precio */}
              <div className={styles.courseCoverWrapper}>
                <img src={course.thumbnail} alt={course.title} className={styles.courseCoverImg} />
                <span className={styles.cardPriceTag}>
                  {course.price && Number(course.price) > 0 ? `$${course.price} USD` : "Gratis"}
                </span>
                <div className={styles.courseCoverOverlay}>
                  <Badge variant={course.status === "publish" ? "success" : "neutral"} size="sm">
                    {course.status === "publish" ? "Publicado" : "Borrador"}
                  </Badge>
                </div>
              </div>

              {/* Contenido de la Tarjeta */}
              <div className={styles.courseCardBody}>
                <h3 className={styles.courseCardTitle}>{course.title}</h3>
                <p className={styles.courseCardDescription}>{course.description}</p>

                {/* Plan Vinculado si aplica */}
                {course.linkedPlanId ? (
                  <div className={styles.cardPlanTag}>
                    <CreditCard size={12} />
                    <span>
                      {availablePlans.find((p) => p.id === course.linkedPlanId)?.name || `Plan #${course.linkedPlanId}`}
                    </span>
                  </div>
                ) : null}

                {/* Métricas del Curso */}
                <div className={styles.courseCardStats}>
                  <div className={styles.statItem} title="Módulos en el plan de estudio">
                    <Layers size={14} style={{ color: "var(--accent)" }} />
                    <span>{totalModules} Módulos</span>
                  </div>
                  <div className={styles.statItem} title="Lecciones y topics">
                    <FileText size={14} style={{ color: "#3b82f6" }} />
                    <span>{totalTopics} Topics</span>
                  </div>
                  <div className={styles.statItem} title="Alumnos inscritos">
                    <Users size={14} style={{ color: "var(--success)" }} />
                    <span>{course.studentCount} Alumnos</span>
                  </div>
                </div>
              </div>

              {/* Botón de Acción Principal */}
              <div className={styles.courseCardFooter}>
                <Button
                  variant="primary"
                  className={styles.btnEditCourse}
                  onClick={() => handleOpenCourseBuilder(course)}
                >
                  <Edit3 size={15} /> Editar Curso
                </Button>
              </div>
            </div>
          );
        })}

        {filteredCourses.length === 0 && (
          <div className={styles.emptyGrid}>
            <BookOpen size={40} />
            <h3>No se encontraron cursos</h3>
            <p>Intenta con otro término de búsqueda o crea una nueva formación.</p>
            <Button variant="secondary" onClick={() => setIsNewCourseModalOpen(true)}>
              <Plus size={16} /> Crear Curso
            </Button>
          </div>
        )}
      </div>

      {/* Modal para Crear Nuevo Curso */}
      <Modal
        isOpen={isNewCourseModalOpen}
        onClose={() => setIsNewCourseModalOpen(false)}
        title="Crear Nueva Formación / Curso"
        description="Ingresa los datos iniciales del curso para agregarlo al catálogo y configurar sus módulos."
        footer={
          <>
            <Button variant="ghost" onClick={() => setIsNewCourseModalOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" loading={isCreatingCourse} onClick={handleCreateCourse}>
              Crear Curso
            </Button>
          </>
        }
      >
        <div className={styles.formGroup}>
          <Input
            label="Título del Curso *"
            placeholder="Ej: Master en Automatizaciones de IA..."
            value={newTitle}
            onChange={(e) => setNewTitle(e.target.value)}
          />
        </div>

        <div className={styles.formGroup}>
          <label className={styles.label}>Descripción Breve</label>
          <textarea
            placeholder="Describe los objetivos y qué aprenderá el alumno en este curso..."
            value={newDescription}
            onChange={(e) => setNewDescription(e.target.value)}
            className={styles.textarea}
            rows={3}
          />
        </div>

        <div className={styles.formGroup}>
          <Input
            label="URL de Miniatura / Portada"
            placeholder="https://... (URL de la imagen)"
            value={newThumbnail}
            onChange={(e) => setNewThumbnail(e.target.value)}
          />
        </div>
      </Modal>
    </div>
  );
}
