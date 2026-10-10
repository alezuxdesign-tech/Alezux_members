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
  Tag,
  ArrowUp,
  ArrowDown,
  AlertTriangle,
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { Alert } from "../components/arc/alert/alert";
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
import { FileDropzone, formatFileSize } from "../components/arc/file-dropzone/file-dropzone";
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

interface ToastState {
  id: number;
  tone: "info" | "success" | "warning" | "danger";
  title: string;
  description?: string;
}

interface DeleteConfirmState {
  title: string;
  message: string;
  onConfirm: () => void;
}

export function CoursesView() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [availablePlans, setAvailablePlans] = useState<FinancePlan[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Alertas Arc tipo Toast
  const [toast, setToast] = useState<ToastState | null>(null);

  const showToast = (tone: "info" | "success" | "warning" | "danger", title: string, description?: string) => {
    setToast({ id: Date.now(), tone, title, description });
  };

  useEffect(() => {
    if (!toast) return;
    const timer = setTimeout(() => {
      setToast(null);
    }, 4500);
    return () => clearTimeout(timer);
  }, [toast]);

  // Modal de confirmación para eliminar (Arc)
  const [deleteConfirm, setDeleteConfirm] = useState<DeleteConfirmState | null>(null);

  // Modo de Vista: "grid" (catálogo de tarjetas) | "builder" (página de edición del curso)
  const [viewMode, setViewMode] = useState<"grid" | "builder">("grid");
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);

  // Sub-tab dentro del editor: "curriculum" (módulos y lecciones) | "settings" (configuración general)
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
  const [isFreeCourse, setIsFreeCourse] = useState(false);
  const [priceInputValue, setPriceInputValue] = useState<string>("0.00");
  const [courseStatus, setCourseStatus] = useState<"publish" | "draft">("publish");
  const [courseLinkedPlanId, setCourseLinkedPlanId] = useState<number | null>(null);

  // Estado del Constructor del Curso Activo (Builder)
  const [sections, setSections] = useState<CourseSection[]>([]);
  const [isSavingCurriculum, setIsSavingCurriculum] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [collapsedSections, setCollapsedSections] = useState<Record<string, boolean>>({});

  // Drag and Drop state
  const [draggedLesson, setDraggedLesson] = useState<{ sectionId: string; lessonIndex: number } | null>(null);
  const [draggedSectionIndex, setDraggedSectionIndex] = useState<number | null>(null);

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
    const numPrice = Number(course.price) || 0;
    setCoursePrice(numPrice);
    setIsFreeCourse(numPrice === 0);
    setPriceInputValue(
      numPrice > 0
        ? numPrice.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : "0.00"
    );
    setCourseStatus(course.status || "publish");
    setCourseLinkedPlanId(course.linkedPlanId ?? null);
    setSections(course.sections || []);
    setBuilderTab("curriculum");
    setSavedSuccess(false);
    setViewMode("builder");
  };

  // Manejador del Switch de Curso Gratuito
  const handleToggleFreeCourse = (checked: boolean) => {
    setIsFreeCourse(checked);
    if (checked) {
      setCoursePrice(0);
      setPriceInputValue("0.00");
      showToast("info", "Curso marcado como Gratuito", "El precio del curso se ha fijado en $0.00 USD.");
    } else {
      if (Number(coursePrice) === 0) {
        setCoursePrice(10);
        setPriceInputValue("10.00");
      }
      showToast("info", "Curso marcado de Pago", "Puedes especificar el precio en USD.");
    }
  };

  // Manejadores del Input de Precio con formateo
  const handlePriceChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value;
    const clean = raw.replace(/[^0-9.]/g, "");
    const parts = clean.split(".");
    const sanitized = parts.length > 2 ? `${parts[0]}.${parts.slice(1).join("")}` : clean;
    setPriceInputValue(sanitized);
    const num = parseFloat(sanitized);
    setCoursePrice(isNaN(num) ? 0 : num);
  };

  const handlePriceBlur = () => {
    const num = parseFloat(priceInputValue.replace(/,/g, ""));
    if (isNaN(num) || num <= 0) {
      if (!isFreeCourse) {
        setCoursePrice(0);
        setPriceInputValue("0.00");
      }
    } else {
      setCoursePrice(num);
      setPriceInputValue(
        num.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      );
    }
  };

  const handlePriceFocus = () => {
    const stripped = priceInputValue.replace(/,/g, "");
    if (stripped === "0.00") {
      setPriceInputValue("");
    } else {
      setPriceInputValue(stripped);
    }
  };

  // Subida de imagen de portada (miniatura 16:9)
  const handleUploadThumbnail = async (files: File[]) => {
    if (!files || files.length === 0) return;
    const file = files[0];
    const localUrl = URL.createObjectURL(file);
    setCourseThumbnail(localUrl);
    showToast("info", "Subiendo imagen...", "Procesando la portada del curso.");

    const uploadedUrl = await api.uploadMedia(file);
    if (uploadedUrl) {
      setCourseThumbnail(uploadedUrl);
      showToast("success", "Portada actualizada", "La imagen de portada se subió correctamente.");
    }
  };

  // Subida de imagen de banner panorámico (3:1)
  const handleUploadBanner = async (files: File[]) => {
    if (!files || files.length === 0) return;
    const file = files[0];
    const localUrl = URL.createObjectURL(file);
    setCourseBanner(localUrl);
    showToast("info", "Subiendo banner...", "Procesando la cabecera panorámica.");

    const uploadedUrl = await api.uploadMedia(file);
    if (uploadedUrl) {
      setCourseBanner(uploadedUrl);
      showToast("success", "Banner actualizado", "La cabecera panorámica se subió correctamente.");
    }
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

    try {
      const finalPrice = isFreeCourse ? 0 : Number(coursePrice) || 0;
      const updatedData: Partial<Course> = {
        title: courseTitle,
        description: courseDescription,
        thumbnail: courseThumbnail,
        banner: courseBanner,
        price: finalPrice,
        status: courseStatus,
        linkedPlanId: null,
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
      showToast("success", "Curso guardado exitosamente", "Se han guardado todos los cambios de configuración y módulos.");
    } catch (err) {
      setIsSavingCurriculum(false);
      showToast("danger", "Error al guardar", "Ocurrió un problema guardando los cambios del curso.");
    }
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
    showToast("success", "Portada actualizada", "La portada del módulo se guardó correctamente.");
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
    showToast("success", "Curso creado", `El curso "${created.title}" fue creado exitosamente.`);
  };

  // --- OPERACIONES DEL CONSTRUCTOR DE MÓDULOS ---
  const handleAddSection = (afterIndex?: number) => {
    const newSection: CourseSection = {
      id: `sec-${Date.now()}`,
      title: `Nuevo Módulo ${sections.length + 1}`,
      cover: "",
      description: "",
      lessons: [],
    };

    if (typeof afterIndex === "number") {
      const updated = [...sections];
      updated.splice(afterIndex + 1, 0, newSection);
      setSections(updated);
    } else {
      setSections([...sections, newSection]);
    }
    showToast("info", "Módulo añadido", "Se ha creado un nuevo módulo en blanco.");
  };

  const handleDeleteSection = (sectionId: string) => {
    const sec = sections.find((s) => s.id === sectionId);
    setDeleteConfirm({
      title: "Eliminar Módulo",
      message: `¿Estás seguro de que deseas eliminar "${sec?.title || "este módulo"}" y todas sus lecciones? Esta acción no se puede deshacer.`,
      onConfirm: () => {
        setSections(sections.filter((s) => s.id !== sectionId));
        setDeleteConfirm(null);
        showToast("info", "Módulo eliminado", `El módulo "${sec?.title || ""}" ha sido eliminado.`);
      },
    });
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
    const sec = sections.find((s) => s.id === sectionId);
    const les = sec?.lessons?.find((l) => l.id === topicId);
    setDeleteConfirm({
      title: "Eliminar Lección",
      message: `¿Estás seguro de que deseas eliminar la lección "${les?.title || "este topic"}"?`,
      onConfirm: () => {
        setSections(
          sections.map((s) =>
            s.id === sectionId ? { ...s, lessons: s.lessons.filter((l) => l.id !== topicId) } : s
          )
        );
        setDeleteConfirm(null);
        showToast("info", "Topic eliminado", `La lección "${les?.title || ""}" ha sido eliminada.`);
      },
    });
  };

  // Guardar cambios dentro del Modal de Topic
  const handleSaveTopicFromModal = () => {
    if (!editingTopicState) return;
    const { sectionId, topicIndex, isNew, topic } = editingTopicState;

    if (!topic.title.trim()) {
      showToast("warning", "Campo requerido", "El título de la lección no puede estar vacío.");
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
    showToast("success", isNew ? "Topic añadido" : "Topic actualizado", `La lección "${topic.title}" fue guardada.`);
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

  const handleUpdateFileName = (fileIndex: number, newName: string) => {
    if (!editingTopicState) return;
    const updatedFiles = [...(editingTopicState.topic.files || [])];
    if (updatedFiles[fileIndex]) {
      updatedFiles[fileIndex] = { ...updatedFiles[fileIndex], name: newName };
      setEditingTopicState({
        ...editingTopicState,
        topic: {
          ...editingTopicState.topic,
          files: updatedFiles,
        },
      });
    }
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

  // --- REORDENACIÓN DE MÓDULOS ---
  const handleDropSection = (targetIndex: number) => {
    if (draggedSectionIndex === null || draggedSectionIndex === targetIndex) {
      setDraggedSectionIndex(null);
      return;
    }
    const updated = [...sections];
    const [moved] = updated.splice(draggedSectionIndex, 1);
    updated.splice(targetIndex, 0, moved);
    setSections(updated);
    setDraggedSectionIndex(null);
  };

  const handleMoveSectionUp = (index: number) => {
    if (index <= 0) return;
    const updated = [...sections];
    const [moved] = updated.splice(index, 1);
    updated.splice(index - 1, 0, moved);
    setSections(updated);
  };

  const handleMoveSectionDown = (index: number) => {
    if (index >= sections.length - 1) return;
    const updated = [...sections];
    const [moved] = updated.splice(index, 1);
    updated.splice(index + 1, 0, moved);
    setSections(updated);
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
          </div>

          <div className={styles.builderNavRight}>
            {savedSuccess && (
              <span className={styles.saveAlert}>
                <Check size={16} /> Cambios guardados correctamente
              </span>
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
                    <DollarSign size={13} /> {isFreeCourse || !coursePrice || Number(coursePrice) === 0 ? "Gratis" : `${coursePrice} USD`}
                  </span>
                  <span style={{ fontSize: 12, color: "rgba(255,255,255,0.7)" }}>
                    {sections.length} Módulos &bull; {totalTopics} Lecciones
                  </span>
                </div>
              </div>
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
              <Settings size={16} /> Configuración General
            </button>
          </div>
        </div>

        {/* =========================================================================
            PESTAÑA 1: CONFIGURACIÓN GENERAL DEL CURSO
           ========================================================================= */}
        {builderTab === "settings" && (
          <div className={styles.courseSettingsCard}>
            <div className={styles.settingsSectionHeader}>
              <div>
                <h3 className={styles.settingsTitle}>Configuración General</h3>
                <p className={styles.settingsDesc}>
                  Gestiona la información principal, imagen de portada, banner panorámico, precio y publicación.
                </p>
              </div>
              <Button variant="primary" loading={isSavingCurriculum} onClick={handleSaveAllCourse}>
                <Save size={16} /> Guardar Cambios
              </Button>
            </div>

            {/* 1. SECCIÓN DE IMÁGENES: PORTADA Y BANNER CON FILEDROPZONE */}
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

                  {courseThumbnail ? (
                    <div className={styles.imageCardPreviewWrap}>
                      <div className={styles.coverPreviewContainer}>
                        <img
                          src={courseThumbnail}
                          alt="Portada del Curso"
                          className={styles.coverPreviewImgLarge}
                          onError={(e) => {
                            (e.target as HTMLElement).style.display = "none";
                          }}
                        />
                      </div>
                      <button
                        type="button"
                        className={styles.imageCardRemoveBtn}
                        onClick={() => {
                          setCourseThumbnail("");
                          showToast("info", "Portada quitada", "Se ha removido la imagen de portada.");
                        }}
                        title="Quitar imagen de portada"
                      >
                        <Trash2 size={13} /> Quitar Portada
                      </button>
                    </div>
                  ) : null}

                  <FileDropzone
                    accept="image/*"
                    multiple={false}
                    maxFiles={1}
                    showList={false}
                    label={courseThumbnail ? "Arrastra otra imagen para reemplazar la portada" : "Arrastra la imagen de portada aquí"}
                    description="o haz clic para buscar en tu equipo (PNG, JPG, WEBP)"
                    note="Recomendado: 1280x720 (16:9)"
                    onFilesChange={handleUploadThumbnail}
                  />
                </div>

                {/* Banner Panorámico 3:1 */}
                <div className={styles.imageCard}>
                  <div className={styles.imageCardHeader}>
                    <label className={styles.label}>Imagen de Banner (Cabecera Panorámica)</label>
                    <span className={styles.miniLabel}>Recomendado: 3:1 (1200x400 px)</span>
                  </div>

                  {courseBanner ? (
                    <div className={styles.imageCardPreviewWrap}>
                      <div className={styles.bannerPreviewContainer}>
                        <img
                          src={courseBanner}
                          alt="Banner del Curso"
                          className={styles.bannerPreviewImgLarge}
                          onError={(e) => {
                            (e.target as HTMLElement).style.display = "none";
                          }}
                        />
                      </div>
                      <button
                        type="button"
                        className={styles.imageCardRemoveBtn}
                        onClick={() => {
                          setCourseBanner("");
                          showToast("info", "Banner quitado", "Se ha removido la cabecera panorámica.");
                        }}
                        title="Quitar imagen de banner"
                      >
                        <Trash2 size={13} /> Quitar Banner
                      </button>
                    </div>
                  ) : null}

                  <FileDropzone
                    accept="image/*"
                    multiple={false}
                    maxFiles={1}
                    showList={false}
                    label={courseBanner ? "Arrastra otra imagen para reemplazar el banner" : "Arrastra el banner panorámico aquí"}
                    description="o haz clic para buscar en tu equipo (PNG, JPG, WEBP)"
                    note="Recomendado: 1200x400 (3:1)"
                    onFilesChange={handleUploadBanner}
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

            {/* 3. COMERCIALIZACIÓN, PRECIO Y ESTADO */}
            <div className={styles.settingsGroup}>
              <h4 className={styles.settingsGroupTitle}>
                <DollarSign size={18} /> Comercialización, Precio y Publicación
              </h4>

              <div className={styles.pricingAndStatusGrid}>
                {/* Switch de Curso Gratuito */}
                <div className={styles.statusToggleBox}>
                  <label className={styles.label}>Modalidad de Acceso</label>
                  <div className={styles.switchRow}>
                    <Switch
                      checked={isFreeCourse}
                      onCheckedChange={handleToggleFreeCourse}
                      id="course-free-switch"
                    />
                    <label htmlFor="course-free-switch" className={styles.switchLabel}>
                      <span className={isFreeCourse ? styles.statusTextActive : styles.statusTextPay}>
                        {isFreeCourse ? "Curso Gratuito" : "Curso de Pago"}
                      </span>
                      <span className={styles.switchSubtext}>
                        {isFreeCourse
                          ? "Cualquier alumno registrado puede ingresar sin coste."
                          : "Requiere un pago para acceder al curso."}
                      </span>
                    </label>
                  </div>
                </div>

                {/* Input de Precio con formateo de moneda */}
                <div className={styles.formGroup}>
                  <label className={styles.label}>
                    Precio del Curso {isFreeCourse ? "(Gratuito)" : "($ USD)"}
                  </label>
                  <div className={styles.inputWithIcon}>
                    <span className={styles.currencyPrefix} aria-hidden="true">$</span>
                    <input
                      type="text"
                      inputMode="decimal"
                      placeholder="0.00"
                      disabled={isFreeCourse}
                      value={priceInputValue}
                      onChange={handlePriceChange}
                      onFocus={handlePriceFocus}
                      onBlur={handlePriceBlur}
                      className={[styles.currencyInput, isFreeCourse ? styles.currencyInputDisabled : ""].join(" ")}
                    />
                    <span className={styles.currencySuffix}>USD</span>
                  </div>
                  <span className={styles.inputHelper}>
                    {isFreeCourse
                      ? "El precio está fijado en $0.00 USD por ser curso gratuito."
                      : "Formato en USD. Especifica el monto para venta directa."}
                  </span>
                </div>

                {/* Switch de Publicación en la Plataforma */}
                <div className={styles.statusToggleBox}>
                  <label className={styles.label}>Estado en la Plataforma</label>
                  <div className={styles.switchRow}>
                    <Switch
                      checked={courseStatus === "publish"}
                      onCheckedChange={(checked) => {
                        const nextStatus = checked ? "publish" : "draft";
                        setCourseStatus(nextStatus);
                        showToast(
                          "info",
                          checked ? "Curso habilitado" : "Curso en borrador",
                          checked
                            ? "El curso ahora está activo y visible para los alumnos."
                            : "El curso se encuentra en modo borrador y oculto."
                        );
                      }}
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
                <div
                  key={section.id}
                  className={[
                    styles.moduleCard,
                    draggedSectionIndex === sIdx ? styles.moduleCardDragging : "",
                  ].join(" ")}
                  onDragOver={(e) => {
                    if (draggedSectionIndex !== null) e.preventDefault();
                  }}
                  onDrop={() => {
                    if (draggedSectionIndex !== null) handleDropSection(sIdx);
                  }}
                >
                  {/* Encabezado del Módulo con Portada de Módulo */}
                  <div
                    className={[
                      styles.moduleHeader,
                      !isCollapsed ? styles.moduleHeaderOpen : "",
                    ].join(" ")}
                  >
                    <div className={styles.moduleHeaderLeft}>
                      {/* Drag Handle para reordenar módulo */}
                      <div
                        className={styles.moduleDragHandle}
                        draggable
                        onDragStart={(e) => {
                          e.stopPropagation();
                          setDraggedSectionIndex(sIdx);
                        }}
                        onDragEnd={() => setDraggedSectionIndex(null)}
                        title="Arrastrar para cambiar el orden de este módulo"
                      >
                        <GripVertical size={16} />
                      </div>

                      {/* Botones de subida y bajada rápida de orden */}
                      <div className={styles.reorderButtons}>
                        <button
                          type="button"
                          className={styles.reorderBtn}
                          onClick={() => handleMoveSectionUp(sIdx)}
                          disabled={sIdx === 0}
                          title="Mover módulo hacia arriba"
                        >
                          <ArrowUp size={11} />
                        </button>
                        <button
                          type="button"
                          className={styles.reorderBtn}
                          onClick={() => handleMoveSectionDown(sIdx)}
                          disabled={sIdx === sections.length - 1}
                          title="Mover módulo hacia abajo"
                        >
                          <ArrowDown size={11} />
                        </button>
                      </div>

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
                                <Video size={16} />
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

            {/* Botón único de Nuevo Módulo al final de todos los módulos */}
            <div className={styles.addModuleBelowWrapper}>
              <Button
                variant="secondary"
                size="sm"
                onClick={() => handleAddSection()}
                className={styles.addModuleBelowBtn}
                title="Añadir un nuevo módulo al final del curso"
              >
                <Plus size={15} /> Nuevo Módulo
              </Button>
            </div>

          {sections.length === 0 && (
            <div className={styles.emptyGrid}>
              <Layers size={36} />
              <h3>Este curso aún no tiene módulos configurados</h3>
              <p>Comienza creando el primer módulo para organizar tus clases y contenidos.</p>
              <Button variant="primary" onClick={() => handleAddSection()}>
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
            description="Arrastra o selecciona la imagen de portada para este módulo directamente desde tu equipo."
            maxWidth="620px"
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
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", width: "100%", marginTop: "6px" }}>
                  <span className={styles.pageSubtitle}>Previsualización (16:9)</span>
                  <Button
                    variant="danger"
                    size="sm"
                    onClick={() => setEditingModuleCover({ ...editingModuleCover, cover: "" })}
                  >
                    <Trash2 size={13} /> Quitar Portada
                  </Button>
                </div>
              </div>
            ) : null}

            <div style={{ marginTop: editingModuleCover.cover ? "16px" : "0" }}>
              <FileDropzone
                accept="image/*"
                multiple={false}
                maxFiles={1}
                showList={false}
                label={editingModuleCover.cover ? "Arrastra otra imagen para reemplazarla" : "Arrastra la imagen de portada aquí"}
                description="o haz clic para buscar en tu equipo"
                note="Recomendado: 1280x720 (16:9) - PNG, JPG, JPEG, WebP"
                onFilesChange={(files: File[]) => {
                  if (files && files.length > 0) {
                    const file = files[0];
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                      if (ev.target?.result) {
                        setEditingModuleCover((prev) =>
                          prev ? { ...prev, cover: ev.target!.result as string } : null
                        );
                      }
                    };
                    reader.readAsDataURL(file);
                  }
                }}
              />
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
            description="Configura el título, video, duración, notas y recursos descargables."
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
                <div>
                  <span className={styles.filesTitle}>
                    Archivos Complementarios ({editingTopicState.topic.files?.length || 0})
                  </span>
                  <p className={styles.pageSubtitle} style={{ margin: "2px 0 0" }}>
                    Recursos y materiales descargables para los alumnos
                  </p>
                </div>
              </div>

              {/* FileDropzone para subir recursos complementarios (sin lista interna duplicada) */}
              <FileDropzone
                multiple={true}
                maxFiles={10}
                showList={false}
                label="Arrastra archivos complementarios aquí"
                description="o haz clic para buscarlos en tu equipo"
                note="PDF, ZIP, DOCX, XLSX, plantillas, etc. (Máx. 50MB)"
                onFilesChange={(files: File[]) => {
                  if (files && files.length > 0) {
                    files.forEach((file) => {
                      if (file.size < 8 * 1024 * 1024) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                          const dataUrl = (e.target?.result as string) || "";
                          const newAttachment: CourseFileAttachment = {
                            id: `f-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`,
                            name: file.name,
                            size: formatFileSize(file.size),
                            url: dataUrl,
                          };
                          setEditingTopicState((prev) => {
                            if (!prev) return null;
                            return {
                              ...prev,
                              topic: {
                                ...prev.topic,
                                files: [...(prev.topic.files || []), newAttachment],
                              },
                            };
                          });
                        };
                        reader.readAsDataURL(file);
                      } else {
                        const newAttachment: CourseFileAttachment = {
                          id: `f-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`,
                          name: file.name,
                          size: formatFileSize(file.size),
                          url: URL.createObjectURL(file),
                        };
                        setEditingTopicState((prev) => {
                          if (!prev) return null;
                          return {
                            ...prev,
                            topic: {
                              ...prev.topic,
                              files: [...(prev.topic.files || []), newAttachment],
                            },
                          };
                        });
                      }
                    });
                  }
                }}
              />

              {/* Lista única de archivos actuales con nombre editable */}
              {editingTopicState.topic.files && editingTopicState.topic.files.length > 0 && (
                <div className={styles.filesList}>
                  {editingTopicState.topic.files.map((file, fIdx) => (
                    <div key={file.id || fIdx} className={styles.fileItem}>
                      <div className={styles.fileItemLeft}>
                        <div className={styles.fileIconWrap}>
                          <Paperclip size={16} />
                        </div>
                        <div className={styles.fileNameInputWrap}>
                          <span className={styles.fileNameLabel}>Nombre para los alumnos:</span>
                          <input
                            type="text"
                            className={styles.fileNameInput}
                            value={file.name}
                            onChange={(e) => handleUpdateFileName(fIdx, e.target.value)}
                            placeholder="Nombre visible del archivo..."
                            title="Haz clic para editar el nombre de este archivo"
                          />
                        </div>
                      </div>

                      <div className={styles.fileItemRight}>
                        {file.size && (
                          <span className={styles.fileSizeBadge}>
                            {file.size}
                          </span>
                        )}
                        {file.url && (
                          <a
                            href={file.url}
                            target="_blank"
                            download={file.name}
                            rel="noopener noreferrer"
                            className={styles.filePreviewBtn}
                            title="Descargar o previsualizar recurso"
                          >
                            <ExternalLink size={13} /> Ver
                          </a>
                        )}
                        <button
                          type="button"
                          className={styles.deleteBtn}
                          onClick={() => handleRemoveFileFromTopic(fIdx)}
                          title="Eliminar este archivo"
                        >
                          <Trash2 size={15} />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}

              {/* Opción para añadir recurso por enlace externo (Drive, Dropbox, etc.) */}
              <div className={styles.addFileSection}>
                <div className={styles.addFileSectionHeader}>
                  O vincula un recurso externo (Google Drive, Notion, Dropbox, OneDrive):
                </div>
                <div className={styles.addFileBox}>
                  <Input
                    placeholder="Nombre: Ej: Guía PDF en Drive"
                    value={newFileName}
                    onChange={(e) => setNewFileName(e.target.value)}
                  />
                  <Input
                    placeholder="URL: https://drive.google.com/..."
                    value={newFileUrl}
                    onChange={(e) => setNewFileUrl(e.target.value)}
                  />
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={handleAddFileToTopic}
                    disabled={!newFileName.trim() || !newFileUrl.trim()}
                  >
                    <Plus size={14} /> Añadir Enlace
                  </Button>
                </div>
              </div>
            </div>
          </Modal>
        )}

        {/* Contenedor flotante de Alertas Arc */}
        {toast && (
          <div className={styles.toastContainer}>
            <Alert
              tone={toast.tone}
              title={toast.title}
              onDismiss={() => setToast(null)}
            >
              {toast.description}
            </Alert>
          </div>
        )}

        {/* Modal de confirmación para eliminar (Arc) */}
        {deleteConfirm && (
          <Modal
            isOpen={true}
            onClose={() => setDeleteConfirm(null)}
            title={deleteConfirm.title}
            maxWidth="460px"
            footer={
              <>
                <Button variant="ghost" onClick={() => setDeleteConfirm(null)}>
                  Cancelar
                </Button>
                <Button variant="danger" onClick={deleteConfirm.onConfirm}>
                  <Trash2 size={15} /> Confirmar Eliminación
                </Button>
              </>
            }
          >
            <div style={{ display: "flex", gap: "14px", alignItems: "flex-start", padding: "12px 0" }}>
              <div
                style={{
                  width: "42px",
                  height: "42px",
                  borderRadius: "var(--radius-pill)",
                  background: "rgba(239, 68, 68, 0.12)",
                  color: "var(--danger)",
                  display: "grid",
                  placeItems: "center",
                  flexShrink: 0,
                }}
              >
                <AlertTriangle size={22} />
              </div>
              <div>
                <p style={{ margin: 0, fontSize: "var(--text-sm)", color: "var(--foreground)", lineHeight: 1.5 }}>
                  {deleteConfirm.message}
                </p>
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

      {/* Contenedor flotante de Alertas Arc */}
      {toast && (
        <div className={styles.toastContainer}>
          <Alert
            tone={toast.tone}
            title={toast.title}
            onDismiss={() => setToast(null)}
          >
            {toast.description}
          </Alert>
        </div>
      )}

      {/* Modal de confirmación para eliminar (Arc) */}
      {deleteConfirm && (
        <Modal
          isOpen={true}
          onClose={() => setDeleteConfirm(null)}
          title={deleteConfirm.title}
          maxWidth="460px"
          footer={
            <>
              <Button variant="ghost" onClick={() => setDeleteConfirm(null)}>
                Cancelar
              </Button>
              <Button variant="danger" onClick={deleteConfirm.onConfirm}>
                <Trash2 size={15} /> Confirmar Eliminación
              </Button>
            </>
          }
        >
          <div style={{ display: "flex", gap: "14px", alignItems: "flex-start", padding: "12px 0" }}>
            <div
              style={{
                width: "42px",
                height: "42px",
                borderRadius: "var(--radius-pill)",
                background: "rgba(239, 68, 68, 0.12)",
                color: "var(--danger)",
                display: "grid",
                placeItems: "center",
                flexShrink: 0,
              }}
            >
              <AlertTriangle size={22} />
            </div>
            <div>
              <p style={{ margin: 0, fontSize: "var(--text-sm)", color: "var(--foreground)", lineHeight: 1.5 }}>
                {deleteConfirm.message}
              </p>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
