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
  HelpCircle,
  UserPlus,
  UserCheck,
  ShieldCheck,
  ArrowRightLeft,
  Sparkles,
  RefreshCw,
  Rows,
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { MoneyInput } from "../components/arc/money-input/money-input";
import { Switch } from "../components/arc/switch/switch";
import { Alert } from "../components/arc/alert/alert";
import { 
  api, 
  Course, 
  CourseSection, 
  CourseLesson, 
  CourseTopic, 
  CourseSubtopic,
  CourseQuestion,
  CourseInstructor,
  Instructor,
  CourseFileAttachment,
  FinancePlan,
  LMSSettingsResponse,
  LMSMigrationResponse
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

interface EditingSubtopicState {
  sectionId: string;
  topicId: string;
  subtopicIndex: number;
  isNew: boolean;
  subtopic: CourseSubtopic;
}

interface EditingModuleCoverState {
  sectionId: string;
  sectionTitle: string;
  cover: string;
  hide_card_labels?: boolean;
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

interface CoursesViewProps {
  onNavigateToInstructors?: () => void;
}

export function CoursesView({ onNavigateToInstructors }: CoursesViewProps = {}) {
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
  const [showCardLabels, setShowCardLabels] = useState<boolean>(true);

  // Docentes / Mentores del Curso
  const [instructors, setInstructors] = useState<CourseInstructor[]>([]);
  const [globalInstructors, setGlobalInstructors] = useState<Instructor[]>([]);
  const [selectedInstructorIdToAssign, setSelectedInstructorIdToAssign] = useState<string>("");
  const [isAssigningAdmin, setIsAssigningAdmin] = useState(false);
  const [isSavingInstructorDirect, setIsSavingInstructorDirect] = useState(false);
  const [newInstructorName, setNewInstructorName] = useState("");
  const [newInstructorRole, setNewInstructorRole] = useState("");
  const [newInstructorAvatar, setNewInstructorAvatar] = useState("");
  const [newInstructorBio, setNewInstructorBio] = useState("");
  const [isAddingInstructor, setIsAddingInstructor] = useState(false);

  // Estado del Constructor del Curso Activo (Builder)
  const [sections, setSections] = useState<CourseSection[]>([]);
  const [isSavingCurriculum, setIsSavingCurriculum] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [collapsedSections, setCollapsedSections] = useState<Record<string, boolean>>({});

  // Subtopics expandidos y modal de edición de subtopic
  const [expandedSubtopics, setExpandedSubtopics] = useState<Record<string, boolean>>({});
  const [editingSubtopicState, setEditingSubtopicState] = useState<EditingSubtopicState | null>(null);

  // Drag and Drop state
  const [activeDragHandleIndex, setActiveDragHandleIndex] = useState<number | null>(null);
  const [draggedSectionIndex, setDraggedSectionIndex] = useState<number | null>(null);
  const [dragOverSectionIndex, setDragOverSectionIndex] = useState<number | null>(null);

  const [activeTopicDrag, setActiveTopicDrag] = useState<{ sectionId: string; lessonIndex: number } | null>(null);
  const [draggedLesson, setDraggedLesson] = useState<{ sectionId: string; lessonIndex: number } | null>(null);
  const [dragOverTopic, setDragOverTopic] = useState<{ sectionId: string; lessonIndex: number } | null>(null);

  const [isUploadingInstructorAvatar, setIsUploadingInstructorAvatar] = useState(false);

  // Modal Detallado de Topic / Lección
  const [editingTopicState, setEditingTopicState] = useState<EditingTopicState | null>(null);
  const [newFileName, setNewFileName] = useState("");
  const [newFileUrl, setNewFileUrl] = useState("");

  // Modal para Editar Portada de Módulo
  const [editingModuleCover, setEditingModuleCover] = useState<EditingModuleCoverState | null>(null);

  // Estados para Selector de Motor LMS y Migración desde LearnDash
  const [lmsSettings, setLmsSettings] = useState<LMSSettingsResponse | null>(null);
  const [isLmsModalOpen, setIsLmsModalOpen] = useState(false);
  const [isMigrating, setIsMigrating] = useState(false);
  const [migrationResult, setMigrationResult] = useState<LMSMigrationResponse | null>(null);

  const loadLmsSettings = async () => {
    try {
      const data = await api.getLMSSettings();
      setLmsSettings(data);
    } catch (e) {
      console.warn("Could not load LMS settings", e);
    }
  };

  const loadCourses = async () => {
    try {
      setIsLoading(true);
      const coursesData = await api.getCourses();
      setCourses(coursesData);
    } catch (e) {
      console.warn("Could not refresh courses", e);
    } finally {
      setIsLoading(false);
    }
  };

  const handleLmsModeChange = async (mode: "auto" | "native" | "learndash") => {
    try {
      const res = await api.saveLMSSettings({ mode });
      if (res.success) {
        setLmsSettings((prev) => prev ? { 
          ...prev, 
          mode, 
          active_mode: mode === "learndash" ? "learndash" : "native" 
        } : null);
        showToast(
          "success", 
          "Modo LMS actualizado", 
          `El motor preferido ahora es: ${mode === "native" ? "Crezca LMS Nativo" : mode === "learndash" ? "LearnDash LMS" : "Detección Automática"}.`
        );
        loadCourses();
      }
    } catch {
      showToast("danger", "Error", "No se pudo guardar la preferencia del motor LMS.");
    }
  };

  const [isMigrationModalOpen, setIsMigrationModalOpen] = useState(false);

  const handleOpenMigrateModal = () => {
    setIsMigrationModalOpen(true);
  };

  const confirmAndExecuteMigration = async () => {
    setIsMigrating(true);
    try {
      const res = await api.migrateFromLearnDash(true);
      setMigrationResult(res);
      setIsMigrationModalOpen(false);
      if (res.success) {
        showToast("success", "Migración completada con éxito", res.message);
        await loadLmsSettings();
        await loadCourses();
      } else {
        showToast("warning", "Aviso de migración", res.message);
      }
    } catch {
      setIsMigrationModalOpen(false);
      showToast("danger", "Error", "Ocurrió un error al migrar los cursos.");
    } finally {
      setIsMigrating(false);
    }
  };

  const loadGlobalInstructors = async () => {
    try {
      const data = await api.getInstructors();
      setGlobalInstructors(data.instructors || []);
    } catch (e) {
      console.warn("Could not load global instructors", e);
    }
  };

  useEffect(() => {
    Promise.all([api.getCourses(), api.getPlans(), api.getLMSSettings(), api.getInstructors()])
      .then(([coursesData, plansData, lmsData, instructorsData]) => {
        setCourses(coursesData);
        setAvailablePlans(plansData);
        setLmsSettings(lmsData);
        setGlobalInstructors(instructorsData.instructors || []);
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
    setShowCardLabels(course.show_card_labels !== false);
    setInstructors(course.instructors || []);
    setSections(course.sections || []);
    setBuilderTab("curriculum");
    setSavedSuccess(false);
    setViewMode("builder");
  };

  // Manejador del Switch de visibilidad de textos en tarjetas de módulos
  const handleToggleCardLabels = async (checked: boolean) => {
    setShowCardLabels(checked);
    if (selectedCourse) {
      const updated = { ...selectedCourse, show_card_labels: checked };
      setSelectedCourse(updated);
      setCourses((prev) => prev.map((c) => (c.id === selectedCourse.id ? updated : c)));
      try {
        await api.saveCourse(selectedCourse.id, { show_card_labels: checked }, sections);
        showToast(
          "info",
          checked ? "Textos en tarjetas activados" : "Textos en tarjetas desactivados",
          checked
            ? "Se mostrarán las etiquetas automáticas ('Módulo X', clases y título)."
            : "Las portadas se mostrarán limpias sin textos automáticos superpuestos."
        );
      } catch (e) {
        console.warn("Could not save card labels setting", e);
      }
    }
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

  // Subida de imagen de foto de perfil del docente (avatar)
  const handleUploadInstructorAvatar = async (files: File[]) => {
    if (!files || files.length === 0) return;
    const file = files[0];
    const localUrl = URL.createObjectURL(file);
    setNewInstructorAvatar(localUrl);
    setIsUploadingInstructorAvatar(true);
    showToast("info", "Subiendo foto...", "Procesando la imagen del docente.");

    try {
      const uploadedUrl = await api.uploadMedia(file);
      if (uploadedUrl) {
        setNewInstructorAvatar(uploadedUrl);
        showToast("success", "Foto subida", "La imagen del docente se cargó correctamente.");
      }
    } catch {
      showToast("danger", "Error", "No se pudo subir la foto del docente.");
    } finally {
      setIsUploadingInstructorAvatar(false);
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
                show_card_labels: showCardLabels,
                instructors,
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
        show_card_labels: showCardLabels,
        instructors,
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
    setSections((prev) =>
      prev.map((s) =>
        s.id === editingModuleCover.sectionId
          ? {
              ...s,
              cover: editingModuleCover.cover,
              hide_card_labels: !!editingModuleCover.hide_card_labels,
            }
          : s
      )
    );
    setEditingModuleCover(null);
    showToast("success", "Portada actualizada", "La configuración de la portada del módulo se guardó correctamente.");
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
    const newSectionId = `sec-${Date.now()}`;
    const newSection: CourseSection = {
      id: newSectionId,
      title: "",
      cover: "",
      description: "",
      lessons: [],
      is_separator: false,
    };

    if (typeof afterIndex === "number") {
      const updated = [...sections];
      updated.splice(afterIndex + 1, 0, newSection);
      setSections(updated);
    } else {
      setSections([...sections, newSection]);
    }
    setCollapsedSections((prev) => ({ ...prev, [newSectionId]: false }));
    showToast("info", "Módulo añadido", "Se ha creado un nuevo módulo en blanco.");
  };

  const handleAddSeparator = (afterIndex?: number) => {
    const newSeparatorId = `sep-${Date.now()}`;
    const newSeparator: CourseSection = {
      id: newSeparatorId,
      title: "",
      cover: "",
      description: "",
      lessons: [],
      is_separator: true,
    };

    if (typeof afterIndex === "number") {
      const updated = [...sections];
      updated.splice(afterIndex + 1, 0, newSeparator);
      setSections(updated);
    } else {
      setSections([...sections, newSeparator]);
    }
    showToast("info", "Separador añadido", "Se ha insertado un nuevo separador para crear otra fila de carrusel en Campus.");
  };

  const handleDeleteSection = (sectionId: string) => {
    const sec = sections.find((s) => s.id === sectionId);
    const isSep = !!sec?.is_separator;
    setDeleteConfirm({
      title: isSep ? "Eliminar Separador" : "Eliminar Módulo",
      message: isSep
        ? `¿Estás seguro de que deseas eliminar este separador de carrusel? Los módulos que estaban debajo se integrarán en la fila anterior.`
        : `¿Estás seguro de que deseas eliminar "${sec?.title || "este módulo"}" y todas sus lecciones? Esta acción no se puede deshacer.`,
      onConfirm: () => {
        setSections(sections.filter((s) => s.id !== sectionId));
        setDeleteConfirm(null);
        showToast("info", isSep ? "Separador eliminado" : "Módulo eliminado", isSep ? "El separador de carrusel fue eliminado." : `El módulo "${sec?.title || ""}" ha sido eliminado.`);
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
        title: "",
        cover: "",
        duration: "15m",
        description: "",
        video_url: "",
        files: [],
        questions: [],
        subtopics: [],
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
        questions: topic.questions ? [...topic.questions] : [],
        subtopics: topic.subtopics ? [...topic.subtopics] : [],
      },
    });
    setNewFileName("");
    setNewFileUrl("");
  };

  // --- GESTIÓN DE SUBTOPICS (SUB-CLASES CORTAS) ---
  const handleOpenAddSubtopic = (sectionId: string, topicId: string) => {
    const sec = sections.find((s) => s.id === sectionId);
    const les = sec?.lessons.find((l) => l.id === topicId);
    const count = les?.subtopics ? les.subtopics.length + 1 : 1;

    setEditingSubtopicState({
      sectionId,
      topicId,
      subtopicIndex: count - 1,
      isNew: true,
      subtopic: {
        id: `sub-${Date.now()}`,
        title: "",
        duration: "10m",
        video_url: "",
        description: "",
        files: [],
        questions: [],
      },
    });
    setExpandedSubtopics((prev) => ({ ...prev, [topicId]: true }));
  };

  const handleOpenEditSubtopic = (
    sectionId: string,
    topicId: string,
    sub: CourseSubtopic,
    index: number
  ) => {
    setEditingSubtopicState({
      sectionId,
      topicId,
      subtopicIndex: index,
      isNew: false,
      subtopic: {
        ...sub,
        files: sub.files ? [...sub.files] : [],
        questions: sub.questions ? [...sub.questions] : [],
      },
    });
  };

  const handleDeleteSubtopic = (sectionId: string, topicId: string, subtopicId: string) => {
    setDeleteConfirm({
      title: "Eliminar Sub-clase",
      message: "¿Estás seguro de que deseas eliminar esta sub-clase?",
      onConfirm: () => {
        setSections((prev) =>
          prev.map((s) => {
            if (s.id !== sectionId) return s;
            return {
              ...s,
              lessons: s.lessons.map((l) => {
                if (l.id !== topicId) return l;
                return {
                  ...l,
                  subtopics: (l.subtopics || []).filter((sub) => sub.id !== subtopicId),
                };
              }),
            };
          })
        );
        setDeleteConfirm(null);
        showToast("info", "Sub-clase eliminada", "Se ha removido la sub-clase.");
      },
    });
  };

  const handleSaveSubtopicFromModal = () => {
    if (!editingSubtopicState) return;
    const { sectionId, topicId, subtopicIndex, isNew, subtopic } = editingSubtopicState;

    if (!subtopic.title.trim()) {
      showToast("warning", "Campo requerido", "El título de la sub-clase no puede estar vacío.");
      return;
    }

    setSections((prev) =>
      prev.map((s) => {
        if (s.id !== sectionId) return s;
        return {
          ...s,
          lessons: s.lessons.map((l) => {
            if (l.id !== topicId) return l;
            const currentSubs = [...(l.subtopics || [])];
            if (isNew) {
              currentSubs.push(subtopic);
            } else {
              currentSubs[subtopicIndex] = subtopic;
            }
            return { ...l, subtopics: currentSubs };
          }),
        };
      })
    );

    setEditingSubtopicState(null);
    showToast("success", isNew ? "Sub-clase añadida" : "Sub-clase actualizada", `"${subtopic.title}" guardada.`);
  };

  // --- GESTIÓN DE DOCENTES / MENTORES (GUARDADO INMEDIATO) ---
  const handleAssignExistingInstructor = async () => {
    if (!selectedInstructorIdToAssign) {
      showToast("warning", "Selecciona un docente", "Elige un docente de la lista para asignarlo.");
      return;
    }

    const found = globalInstructors.find((i) => String(i.id) === String(selectedInstructorIdToAssign));
    if (!found) return;

    const alreadyAssigned = instructors.some(
      (i) => String(i.id) === String(found.id) || i.name.trim().toLowerCase() === found.name.trim().toLowerCase()
    );
    if (alreadyAssigned) {
      showToast("warning", "Docente ya asignado", `"${found.name}" ya forma parte del equipo docente de este curso.`);
      return;
    }

    const newInst: CourseInstructor = {
      id: found.id,
      name: found.name,
      role: found.role || "Docente Principal",
      avatar: found.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80",
      bio: found.bio || "",
    };

    const updatedList = [...instructors, newInst];
    setInstructors(updatedList);
    setSelectedInstructorIdToAssign("");

    if (selectedCourse) {
      setIsSavingInstructorDirect(true);
      try {
        await api.saveCourseInstructors(selectedCourse.id, updatedList);
        setSelectedCourse((prev) => (prev ? { ...prev, instructors: updatedList } : null));
        setCourses((prev) =>
          prev.map((c) => (c.id === selectedCourse.id ? { ...c, instructors: updatedList } : c))
        );
        showToast("success", "Docente asignado y guardado", `"${newInst.name}" se guardó a la primera en la base de datos.`);
      } catch {
        showToast("danger", "Error al guardar", "No se pudo sincronizar el docente en el curso.");
      } finally {
        setIsSavingInstructorDirect(false);
      }
    }
  };

  const handleAssignAdminAsInstructor = async () => {
    setIsAssigningAdmin(true);
    try {
      const res = await api.syncAdminAsInstructor();
      if (!res.success || !res.instructor) {
        showToast("danger", "Error", res.message || "No se pudo sincronizar al administrador.");
        return;
      }

      const adminData = res.instructor;
      const alreadyAssigned = instructors.some(
        (i) => String(i.id) === String(adminData.id) || i.name.trim().toLowerCase() === adminData.name.trim().toLowerCase()
      );

      if (alreadyAssigned) {
        showToast("info", "Ya asignado", "El administrador ya está asignado como docente en este curso.");
        return;
      }

      const adminInst: CourseInstructor = {
        id: adminData.id,
        name: adminData.name,
        role: adminData.role || "Administrador & Docente",
        avatar: adminData.avatar || "",
        bio: adminData.bio || "Administrador de la plataforma y docente titular.",
      };

      const updatedList = [...instructors, adminInst];
      setInstructors(updatedList);

      if (selectedCourse) {
        await api.saveCourseInstructors(selectedCourse.id, updatedList);
        setSelectedCourse((prev) => (prev ? { ...prev, instructors: updatedList } : null));
        setCourses((prev) =>
          prev.map((c) => (c.id === selectedCourse.id ? { ...c, instructors: updatedList } : c))
        );
      }

      await loadGlobalInstructors();
      showToast("success", "Administrador asignado", "El administrador es ahora docente de este curso y se guardó a la primera.");
    } catch {
      showToast("danger", "Error", "Ocurrió un problema al sincronizar al administrador.");
    } finally {
      setIsAssigningAdmin(false);
    }
  };

  const handleAddInstructor = async () => {
    if (!newInstructorName.trim()) {
      showToast("warning", "Nombre requerido", "Ingresa el nombre del docente.");
      return;
    }

    setIsSavingInstructorDirect(true);
    const newInst: CourseInstructor = {
      id: `inst-${Date.now()}`,
      name: newInstructorName.trim(),
      role: newInstructorRole.trim() || "Docente Principal",
      avatar: newInstructorAvatar.trim() || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80",
      bio: newInstructorBio.trim(),
    };

    const updatedList = [...instructors, newInst];
    setInstructors(updatedList);

    if (selectedCourse) {
      try {
        await api.saveCourseInstructors(selectedCourse.id, updatedList);
        setSelectedCourse((prev) => (prev ? { ...prev, instructors: updatedList } : null));
        setCourses((prev) =>
          prev.map((c) => (c.id === selectedCourse.id ? { ...c, instructors: updatedList } : c))
        );
        showToast("success", "Docente guardado inmediatamente", `"${newInst.name}" quedó registrado y guardado a la primera.`);
      } catch {
        showToast("danger", "Error", "No se pudo guardar el docente en el curso.");
      }
    }

    setNewInstructorName("");
    setNewInstructorRole("");
    setNewInstructorAvatar("");
    setNewInstructorBio("");
    setIsAddingInstructor(false);
    setIsSavingInstructorDirect(false);

    await loadGlobalInstructors();
  };

  const handleDeleteInstructor = async (index: number) => {
    const target = instructors[index];
    const updatedList = instructors.filter((_, idx) => idx !== index);
    setInstructors(updatedList);

    if (selectedCourse) {
      try {
        await api.saveCourseInstructors(selectedCourse.id, updatedList);
        setSelectedCourse((prev) => (prev ? { ...prev, instructors: updatedList } : null));
        setCourses((prev) =>
          prev.map((c) => (c.id === selectedCourse.id ? { ...c, instructors: updatedList } : c))
        );
        showToast("info", "Docente removido", `Se quitó a "${target?.name || "el docente"}" del curso y se guardaron los cambios.`);
      } catch {
        showToast("danger", "Error", "No se pudo actualizar la lista de docentes.");
      }
    }
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

  // --- DRAG AND DROP HANDLERS (TOPICS) ---
  const handleDragStart = (sectionId: string, lessonIndex: number) => {
    setDraggedLesson({ sectionId, lessonIndex });
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    if (e.dataTransfer) {
      e.dataTransfer.dropEffect = "move";
    }
  };

  const handleDrop = (targetSectionId: string, targetLessonIndex: number) => {
    if (!draggedLesson) return;
    const { sectionId: sourceSectionId, lessonIndex: sourceLessonIndex } = draggedLesson;

    if (sourceSectionId === targetSectionId && sourceLessonIndex === targetLessonIndex) {
      setDraggedLesson(null);
      setDragOverTopic(null);
      setActiveTopicDrag(null);
      return;
    }

    const newSections = [...sections];
    const sourceSection = newSections.find((s) => s.id === sourceSectionId);
    const targetSection = newSections.find((s) => s.id === targetSectionId);

    if (!sourceSection || !targetSection) {
      setDraggedLesson(null);
      setDragOverTopic(null);
      setActiveTopicDrag(null);
      return;
    }

    const [movedLesson] = sourceSection.lessons.splice(sourceLessonIndex, 1);
    targetSection.lessons.splice(targetLessonIndex, 0, movedLesson);

    setSections(newSections);
    setDraggedLesson(null);
    setDragOverTopic(null);
    setActiveTopicDrag(null);
    showToast("info", "Lección reordenada", `La lección "${movedLesson.title || "sin título"}" fue movida.`);
  };

  // --- REORDENACIÓN DE MÓDULOS ---
  const handleDropSection = (targetIndex: number) => {
    if (draggedSectionIndex === null || draggedSectionIndex === targetIndex) {
      setDraggedSectionIndex(null);
      setDragOverSectionIndex(null);
      setActiveDragHandleIndex(null);
      return;
    }
    const updated = [...sections];
    const [moved] = updated.splice(draggedSectionIndex, 1);
    updated.splice(targetIndex, 0, moved);
    setSections(updated);
    setDraggedSectionIndex(null);
    setDragOverSectionIndex(null);
    setActiveDragHandleIndex(null);
    showToast("info", "Orden de módulos actualizado", `El módulo se movió a la posición ${targetIndex + 1}.`);
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

            {/* 1. INFORMACIÓN DEL CURSO: NOMBRE Y DESCRIPCIÓN */}
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

            {/* 2. COMERCIALIZACIÓN, PRECIO Y ESTADO */}
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

                {/* Money Input para Precio del Curso */}
                <div className={styles.formGroup}>
                  <MoneyInput
                    label={`Precio del Curso ${isFreeCourse ? "(Gratuito)" : "($ USD)"}`}
                    value={Number(coursePrice) || 0}
                    onChange={(val) => {
                      setCoursePrice(val);
                      setPriceInputValue(val.toFixed(2));
                    }}
                    disabled={isFreeCourse}
                    currency="USD"
                    symbol="$"
                    placeholder="0.00"
                    hint={
                      isFreeCourse
                        ? "El precio está fijado en $0.00 USD por ser curso gratuito."
                        : "Formato en USD. Especifica el monto para venta directa."
                    }
                  />
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

            {/* 3. DOCENTES & MENTORES DEL CURSO */}
            <div className={styles.settingsGroup}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", width: "100%", flexWrap: "wrap", gap: 12 }}>
                <div>
                  <h4 className={styles.settingsGroupTitle}>
                    <Users size={18} /> Docentes & Mentores del Curso ({instructors.length})
                  </h4>
                  <p className={styles.switchSubtext} style={{ marginTop: 4 }}>
                    Asigna a los profesores del curso. Los cambios de docentes se guardan inmediatamente en la base de datos a la primera.
                  </p>
                </div>
                <div style={{ display: "flex", alignItems: "center", gap: 8, flexWrap: "wrap" }}>
                  <Button
                    variant="secondary"
                    size="sm"
                    loading={isAssigningAdmin}
                    disabled={isAssigningAdmin}
                    onClick={handleAssignAdminAsInstructor}
                    title="Sincroniza y asigna al usuario Administrador como docente del curso"
                  >
                    <ShieldCheck size={14} /> Asignar Admin como Docente
                  </Button>
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => setIsAddingInstructor(true)}
                  >
                    <UserPlus size={14} /> + Registrar Nuevo Docente
                  </Button>
                  {onNavigateToInstructors && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={onNavigateToInstructors}
                      title="Ir al módulo completo de Docentes para gestionar permisos y accesos"
                    >
                      <ExternalLink size={13} /> Directorio de Docentes
                    </Button>
                  )}
                </div>
              </div>

              {/* Selector de Docente Registrado del Directorio */}
              <div className={styles.instructorSelectorBar}>
                <span className={styles.miniLabel} style={{ display: "flex", alignItems: "center", gap: 6 }}>
                  <UserCheck size={14} /> Seleccionar Docente Registrado:
                </span>
                <select
                  value={selectedInstructorIdToAssign}
                  onChange={(e) => setSelectedInstructorIdToAssign(e.target.value)}
                  className={styles.instructorSelectInput}
                >
                  <option value="">-- Elige un docente del directorio global --</option>
                  {globalInstructors.map((gi) => (
                    <option key={gi.id} value={gi.id}>
                      {gi.name} {gi.role ? `(${gi.role})` : ""} {gi.isAdmin ? "★ Admin" : ""}
                    </option>
                  ))}
                </select>
                <Button
                  variant="primary"
                  size="sm"
                  disabled={!selectedInstructorIdToAssign || isSavingInstructorDirect}
                  loading={isSavingInstructorDirect}
                  onClick={handleAssignExistingInstructor}
                >
                  <UserPlus size={14} /> Asignar al Curso
                </Button>
              </div>

              {isAddingInstructor && (
                <div style={{ background: "var(--surface)", border: "1px solid var(--border)", borderRadius: "var(--radius-control)", padding: 16, display: "flex", flexDirection: "column", gap: 12 }}>
                  <span className={styles.miniLabel}>Nuevo Docente / Mentor (Se guardará directamente en el curso):</span>
                  <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                    <Input
                      label="Nombre del Docente *"
                      placeholder="Ej: Lic. Carlos Gómez"
                      value={newInstructorName}
                      onChange={(e) => setNewInstructorName(e.target.value)}
                    />
                    <Input
                      label="Especialidad / Rol"
                      placeholder="Ej: Especialista en Performance & Growth"
                      value={newInstructorRole}
                      onChange={(e) => setNewInstructorRole(e.target.value)}
                    />
                  </div>

                  {/* Subida de foto de perfil con File Upload */}
                  <div className={styles.formGroup}>
                    <label className={styles.label}>Foto de Perfil / Avatar del Docente</label>
                    {newInstructorAvatar ? (
                      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 8 }}>
                        <img
                          src={newInstructorAvatar}
                          alt="Avatar preview"
                          style={{
                            width: 54,
                            height: 54,
                            borderRadius: "50%",
                            objectFit: "cover",
                            border: "2px solid var(--border)",
                          }}
                        />
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setNewInstructorAvatar("")}
                        >
                          <Trash2 size={13} /> Quitar Foto
                        </Button>
                      </div>
                    ) : null}
                    <FileDropzone
                      accept="image/*"
                      multiple={false}
                      maxFiles={1}
                      showList={false}
                      label={newInstructorAvatar ? "Arrastra otra imagen para reemplazar la foto" : "Arrastra la foto del docente aquí"}
                      description="o haz clic para buscar en tu equipo (PNG, JPG, WEBP)"
                      note="Recomendado: Cuadrada (400x400 px)"
                      onFilesChange={handleUploadInstructorAvatar}
                    />
                  </div>

                  <div className={styles.formGroup}>
                    <label className={styles.label}>Breve Biografía / Presentación</label>
                    <textarea
                      placeholder="Resumen de experiencia profesional o logros del docente..."
                      value={newInstructorBio}
                      onChange={(e) => setNewInstructorBio(e.target.value)}
                      className={styles.textarea}
                      rows={2}
                    />
                  </div>
                  <div style={{ display: "flex", justifyContent: "flex-end", gap: 8 }}>
                    <Button variant="ghost" size="sm" onClick={() => { setIsAddingInstructor(false); setNewInstructorAvatar(""); }}>
                      Cancelar
                    </Button>
                    <Button
                      variant="primary"
                      size="sm"
                      onClick={handleAddInstructor}
                      disabled={isUploadingInstructorAvatar || isSavingInstructorDirect}
                      loading={isUploadingInstructorAvatar || isSavingInstructorDirect}
                    >
                      <UserCheck size={14} /> Guardar Docente Inmediatamente
                    </Button>
                  </div>
                </div>
              )}

              {instructors.length > 0 ? (
                <div className={styles.instructorsList}>
                  {instructors.map((inst, idx) => (
                    <div key={inst.id || idx} className={styles.instructorCard}>
                      <img
                        src={inst.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80"}
                        alt={inst.name}
                        className={styles.instructorAvatar}
                      />
                      <div className={styles.instructorInfo}>
                        <span className={styles.instructorName}>{inst.name}</span>
                        <span className={styles.instructorRole}>{inst.role || "Docente"}</span>
                        {inst.bio && (
                          <span style={{ fontSize: 11, color: "var(--text-muted)", marginTop: 2, display: "-webkit-box", WebkitLineClamp: 2, WebkitBoxOrient: "vertical", overflow: "hidden" }}>
                            {inst.bio}
                          </span>
                        )}
                      </div>
                      <button
                        type="button"
                        className={styles.deleteBtn}
                        onClick={() => handleDeleteInstructor(idx)}
                        title="Quitar docente del curso (se guardará inmediatamente)"
                      >
                        <Trash2 size={14} />
                      </button>
                    </div>
                  ))}
                </div>
              ) : (
                <p className={styles.switchSubtext}>
                  No hay docentes asignados a este curso todavía. Selecciona uno del directorio o haz clic en "Asignar Admin como Docente".
                </p>
              )}
            </div>

            {/* 4. IMÁGENES PRINCIPALES DEL CURSO: PORTADA Y BANNER */}
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

            {/* Opción de Superposición de textos en tarjetas de módulos */}
            <div className={styles.settingsGroup}>
              <div className={styles.settingsGroupHeader}>
                <h4 className={styles.settingsGroupTitle}>
                  <Layers size={16} /> Portadas y Textos en Campus (Netflix)
                </h4>
                <p className={styles.pageSubtitle}>
                  Configura si deseas que el campus superponga los textos automáticos sobre las tarjetas.
                </p>
              </div>
              <div style={{ background: "var(--surface)", border: "1px solid var(--border)", borderRadius: "var(--radius-panel)", padding: "16px 20px", display: "flex", justifyContent: "space-between", alignItems: "center", gap: "20px" }}>
                <div>
                  <strong style={{ fontSize: "14px", color: "var(--foreground)", display: "block" }}>
                    Superponer etiquetas y textos automáticos en las portadas
                  </strong>
                  <span style={{ fontSize: "12px", color: "var(--text-muted)", display: "block", marginTop: "4px" }}>
                    Muestra el tag superior ("Módulo 1"), el contador de clases y el nombre del módulo sobre la imagen. Desactívalo si utilizas imágenes con diseño gráfico personalizado que ya traen su propio texto integrado.
                  </span>
                </div>
                <Switch
                  checked={showCardLabels}
                  onCheckedChange={handleToggleCardLabels}
                />
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
            {/* Barra superior de herramientas del temario */}
            <div className={styles.curriculumToolbar}>
              <div className={styles.curriculumStats}>
                <span className={styles.curriculumCountPill}>
                  {sections.filter((s) => !s.is_separator).length} {sections.filter((s) => !s.is_separator).length === 1 ? "Módulo" : "Módulos"}
                </span>
                <span className={styles.curriculumRowsPill}>
                  {sections.filter((s) => s.is_separator).length + 1} {sections.filter((s) => s.is_separator).length + 1 === 1 ? "Fila de Carrusel" : "Filas de Carrusel"} en Campus
                </span>
              </div>
              <div className={styles.curriculumActions}>
                <div className={styles.cardLabelsSwitchWrap} title="Activa o desactiva la superposición de etiquetas ('Módulo X', clases y título) en Campus">
                  <span>Textos en tarjetas</span>
                  <Switch
                    checked={showCardLabels}
                    onCheckedChange={handleToggleCardLabels}
                    size="sm"
                  />
                </div>
                <Button variant="secondary" size="sm" onClick={() => handleAddSection()}>
                  <Plus size={15} /> Nuevo Módulo
                </Button>
                <Button variant="secondary" size="sm" onClick={() => handleAddSeparator()}>
                  <Rows size={15} /> Añadir Separador
                </Button>
              </div>
            </div>

            {(() => {
              let actualModuleIndex = 0;
              return sections.map((section, sIdx) => {
                const isSeparator = !!section.is_separator;
                if (!isSeparator) {
                  actualModuleIndex++;
                }

                if (isSeparator) {
                  return (
                    <div
                      key={section.id}
                      className={[
                        styles.separatorCard,
                        draggedSectionIndex === sIdx ? styles.moduleCardDragging : "",
                        dragOverSectionIndex === sIdx && draggedSectionIndex !== sIdx ? styles.moduleCardDropTarget : "",
                      ].filter(Boolean).join(" ")}
                      draggable={activeDragHandleIndex === sIdx}
                      onDragStart={(e) => {
                        e.dataTransfer.setData("text/plain", `${sIdx}`);
                        e.dataTransfer.effectAllowed = "move";
                        setDraggedSectionIndex(sIdx);
                      }}
                      onDragOver={(e) => {
                        if (draggedSectionIndex !== null) {
                          e.preventDefault();
                          if (e.dataTransfer) {
                            e.dataTransfer.dropEffect = "move";
                          }
                          if (dragOverSectionIndex !== sIdx) {
                            setDragOverSectionIndex(sIdx);
                          }
                        }
                      }}
                      onDragLeave={() => {
                        if (dragOverSectionIndex === sIdx) {
                          setDragOverSectionIndex(null);
                        }
                      }}
                      onDrop={(e) => {
                        e.preventDefault();
                        if (draggedSectionIndex !== null) {
                          handleDropSection(sIdx);
                        }
                        setDragOverSectionIndex(null);
                        setActiveDragHandleIndex(null);
                      }}
                      onDragEnd={() => {
                        setDraggedSectionIndex(null);
                        setDragOverSectionIndex(null);
                        setActiveDragHandleIndex(null);
                      }}
                    >
                      <div className={styles.separatorCardLeft}>
                        {/* Drag Handle para reordenar separador */}
                        <div
                          className={styles.moduleDragHandle}
                          onMouseDown={() => setActiveDragHandleIndex(sIdx)}
                          onMouseUp={() => setActiveDragHandleIndex(null)}
                          title="Mantén presionado y arrastra para reubicar este separador"
                        >
                          <GripVertical size={16} />
                        </div>

                        {/* Botones de subida y bajada de orden */}
                        <div className={styles.reorderButtons}>
                          <button
                            type="button"
                            className={styles.reorderBtn}
                            onClick={() => handleMoveSectionUp(sIdx)}
                            disabled={sIdx === 0}
                            title="Mover separador hacia arriba"
                          >
                            <ArrowUp size={11} />
                          </button>
                          <button
                            type="button"
                            className={styles.reorderBtn}
                            onClick={() => handleMoveSectionDown(sIdx)}
                            disabled={sIdx === sections.length - 1}
                            title="Mover separador hacia abajo"
                          >
                            <ArrowDown size={11} />
                          </button>
                        </div>

                        {/* Badge de Separador */}
                        <div className={styles.separatorTag}>
                          <Rows size={13} />
                          <span>Separador · Nueva Fila de Carrusel</span>
                        </div>

                        {/* Título de la fila opcional */}
                        <input
                          type="text"
                          value={section.title}
                          onChange={(e) => handleUpdateSectionTitle(section.id, e.target.value)}
                          className={styles.separatorTitleInput}
                          placeholder="Nombre de la fila en Campus (opcional, ej: Fase 2: Escalamiento...)"
                        />
                      </div>

                      <div className={styles.separatorCardRight}>
                        <button
                          type="button"
                          className={styles.deleteBtn}
                          onClick={() => handleDeleteSection(section.id)}
                          title="Eliminar separador"
                        >
                          <Trash2 size={16} />
                        </button>
                      </div>
                    </div>
                  );
                }

                const currentModuleNum = actualModuleIndex;
                const isCollapsed = !!collapsedSections[section.id];
                const topicsCount = section.lessons ? section.lessons.length : 0;

                return (
                  <div
                    key={section.id}
                  className={[
                    styles.moduleCard,
                    draggedSectionIndex === sIdx ? styles.moduleCardDragging : "",
                    dragOverSectionIndex === sIdx && draggedSectionIndex !== sIdx ? styles.moduleCardDropTarget : "",
                  ].filter(Boolean).join(" ")}
                  draggable={activeDragHandleIndex === sIdx}
                  onDragStart={(e) => {
                    e.dataTransfer.setData("text/plain", `${sIdx}`);
                    e.dataTransfer.effectAllowed = "move";
                    setDraggedSectionIndex(sIdx);
                  }}
                  onDragOver={(e) => {
                    if (draggedSectionIndex !== null) {
                      e.preventDefault();
                      if (e.dataTransfer) {
                        e.dataTransfer.dropEffect = "move";
                      }
                      if (dragOverSectionIndex !== sIdx) {
                        setDragOverSectionIndex(sIdx);
                      }
                    }
                  }}
                  onDragLeave={() => {
                    if (dragOverSectionIndex === sIdx) {
                      setDragOverSectionIndex(null);
                    }
                  }}
                  onDrop={(e) => {
                    e.preventDefault();
                    if (draggedSectionIndex !== null) {
                      handleDropSection(sIdx);
                    }
                    setDragOverSectionIndex(null);
                    setActiveDragHandleIndex(null);
                  }}
                  onDragEnd={() => {
                    setDraggedSectionIndex(null);
                    setDragOverSectionIndex(null);
                    setActiveDragHandleIndex(null);
                  }}
                >
                  {/* Encabezado del Módulo con Portada de Módulo */}
                  <div
                    className={[
                      styles.moduleHeader,
                      !isCollapsed ? styles.moduleHeaderOpen : "",
                    ].join(" ")}
                    onClick={(e) => {
                      const target = e.target as HTMLElement;
                      if (
                        target.closest("input") ||
                        target.closest("button") ||
                        target.closest(`.${styles.moduleHeaderCover}`) ||
                        target.closest(`.${styles.moduleDragHandle}`) ||
                        target.closest(`.${styles.reorderButtons}`)
                      ) {
                        return;
                      }
                      toggleSectionCollapse(section.id);
                    }}
                  >
                    <div className={styles.moduleHeaderLeft}>
                      {/* Drag Handle para reordenar módulo */}
                      <div
                        className={styles.moduleDragHandle}
                        onMouseDown={() => setActiveDragHandleIndex(sIdx)}
                        onMouseUp={() => setActiveDragHandleIndex(null)}
                        title="Mantén presionado y arrastra para cambiar el orden de este módulo"
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
                        onClick={(e) => {
                          e.stopPropagation();
                          toggleSectionCollapse(section.id);
                        }}
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
                            hide_card_labels: !!section.hide_card_labels,
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

                      <span className={styles.moduleIndexPill}>Módulo {currentModuleNum}</span>

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
                          <div key={lesson.id} className={styles.topicItemGroup}>
                            <div
                              className={[
                                styles.topicRow,
                                draggedLesson?.sectionId === section.id && draggedLesson?.lessonIndex === lIdx
                                  ? styles.topicRowDragging
                                  : "",
                                dragOverTopic?.sectionId === section.id && dragOverTopic?.lessonIndex === lIdx
                                  ? styles.topicRowDropTarget
                                  : "",
                              ].filter(Boolean).join(" ")}
                              draggable={activeTopicDrag?.sectionId === section.id && activeTopicDrag?.lessonIndex === lIdx}
                              onDragStart={(e) => {
                                e.dataTransfer.setData("text/plain", `${section.id}:${lIdx}`);
                                e.dataTransfer.effectAllowed = "move";
                                handleDragStart(section.id, lIdx);
                              }}
                              onDragOver={(e) => {
                                if (draggedLesson) {
                                  e.preventDefault();
                                  if (e.dataTransfer) {
                                    e.dataTransfer.dropEffect = "move";
                                  }
                                  setDragOverTopic({ sectionId: section.id, lessonIndex: lIdx });
                                }
                              }}
                              onDragLeave={() => {
                                setDragOverTopic(null);
                              }}
                              onDrop={(e) => {
                                e.preventDefault();
                                handleDrop(section.id, lIdx);
                                setDragOverTopic(null);
                                setActiveTopicDrag(null);
                              }}
                              onDragEnd={() => {
                                setDraggedLesson(null);
                                setDragOverTopic(null);
                                setActiveTopicDrag(null);
                              }}
                            >
                              <div
                                className={styles.dragHandle}
                                onMouseDown={() => setActiveTopicDrag({ sectionId: section.id, lessonIndex: lIdx })}
                                onMouseUp={() => setActiveTopicDrag(null)}
                                title="Mantén presionado y arrastra para reordenar"
                              >
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

                                {lesson.questions && lesson.questions.length > 0 && (
                                  <span
                                    className={[styles.topicBadge, styles.topicBadgeDesc].join(" ")}
                                  >
                                    <HelpCircle size={12} /> {lesson.questions.length} Preguntas
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
                              <button
                                type="button"
                                className={styles.subtopicsToggleBtn}
                                onClick={() =>
                                  setExpandedSubtopics((prev) => ({
                                    ...prev,
                                    [lesson.id]: !prev[lesson.id],
                                  }))
                                }
                                title="Dividir esta clase en varias sub-clases cortas"
                              >
                                {lesson.subtopics && lesson.subtopics.length > 0 ? (
                                  <>
                                    <Layers size={12} />
                                    <span>{lesson.subtopics.length} Sub-clases</span>
                                    {expandedSubtopics[lesson.id] ? <ChevronDown size={12} /> : <ChevronRight size={12} />}
                                  </>
                                ) : (
                                  <>
                                    <Plus size={12} />
                                    <span>Sub-clases</span>
                                  </>
                                )}
                              </button>

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

                          {/* Subtopics anidados si está expandido */}
                          {expandedSubtopics[lesson.id] && (
                            <div className={styles.subtopicsWrapper}>
                              {lesson.subtopics && lesson.subtopics.length > 0 ? (
                                lesson.subtopics.map((sub, subIdx) => (
                                  <div key={sub.id || subIdx} className={styles.subtopicRow}>
                                    <div className={styles.subtopicInfo}>
                                      <span className={styles.subtopicIndex}>Parte {subIdx + 1}</span>
                                      <span className={styles.subtopicTitle}>{sub.title}</span>
                                      {sub.video_url && (
                                        <span className={[styles.topicBadge, styles.topicBadgeVideo].join(" ")}>
                                          <Video size={11} /> Video
                                        </span>
                                      )}
                                      {sub.duration && (
                                        <span className={styles.topicBadge}>
                                          <Clock size={10} /> {sub.duration}
                                        </span>
                                      )}
                                      {sub.files && sub.files.length > 0 && (
                                        <span className={[styles.topicBadge, styles.topicBadgeFiles].join(" ")}>
                                          <Paperclip size={10} /> {sub.files.length}
                                        </span>
                                      )}
                                      {sub.questions && sub.questions.length > 0 && (
                                        <span className={[styles.topicBadge, styles.topicBadgeDesc].join(" ")}>
                                          <HelpCircle size={10} /> {sub.questions.length} Preguntas
                                        </span>
                                      )}
                                    </div>
                                    <div className={styles.subtopicActions}>
                                      <Button
                                        variant="secondary"
                                        size="sm"
                                        onClick={() => handleOpenEditSubtopic(section.id, lesson.id, sub, subIdx)}
                                      >
                                        <Edit3 size={13} /> Editar
                                      </Button>
                                      <button
                                        type="button"
                                        className={styles.deleteBtn}
                                        onClick={() => handleDeleteSubtopic(section.id, lesson.id, sub.id)}
                                        title="Eliminar sub-clase"
                                      >
                                        <Trash2 size={14} />
                                      </button>
                                    </div>
                                  </div>
                                ))
                              ) : null}
                              <div style={{ marginTop: 4 }}>
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  onClick={() => handleOpenAddSubtopic(section.id, lesson.id)}
                                >
                                  <Plus size={13} /> Añadir Sub-clase a {lesson.title}
                                </Button>
                              </div>
                            </div>
                          )}
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
          });
        })()}

            {/* Botones de Nuevo Módulo y Separador al final de todos los módulos */}
            <div className={styles.addModuleBelowWrapper}>
              <Button
                variant="secondary"
                size="sm"
                onClick={() => handleAddSection()}
                className={styles.addModuleBelowBtn}
                title="Añadir un nuevo módulo al temario"
              >
                <Plus size={15} /> Nuevo Módulo
              </Button>
              <Button
                variant="secondary"
                size="sm"
                onClick={() => handleAddSeparator()}
                className={styles.addSeparatorBelowBtn}
                title="Añadir un separador para crear otra fila de carrusel en Campus"
              >
                <Rows size={15} /> Separador de Carrusel
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

            <div style={{ marginTop: "16px", padding: "12px 14px", background: "var(--surface-muted)", border: "1px solid var(--border)", borderRadius: "var(--radius-control)", display: "flex", justifyContent: "space-between", alignItems: "center", gap: "14px" }}>
              <div>
                <strong style={{ fontSize: "13px", color: "var(--foreground)", display: "block" }}>
                  Ocultar textos automáticos en esta portada
                </strong>
                <span style={{ fontSize: "11px", color: "var(--text-muted)", display: "block", marginTop: "2px" }}>
                  No muestra la etiqueta "Módulo X", contador de clases ni título sobre esta tarjeta (útil si tu imagen ya tiene el texto incorporado).
                </span>
              </div>
              <Switch
                checked={!!editingModuleCover.hide_card_labels}
                onCheckedChange={(checked) => setEditingModuleCover({ ...editingModuleCover, hide_card_labels: checked })}
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

            {/* PREGUNTAS Y QUIZZES DE AUTOEVALUACIÓN DEL TOPIC */}
            <div className={styles.filesSection}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 12 }}>
                <div>
                  <span className={styles.filesTitle}>
                    Preguntas / Quizzes de la Clase ({editingTopicState.topic.questions?.length || 0})
                  </span>
                  <p className={styles.pageSubtitle} style={{ margin: "2px 0 0" }}>
                    Preguntas interactivas para comprobar el aprendizaje de esta lección
                  </p>
                </div>
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={() => {
                    const newQ: CourseQuestion = {
                      id: `q-${Date.now()}`,
                      question: "",
                      options: ["Opción A", "Opción B"],
                      correctIndex: 0,
                    };
                    setEditingTopicState((prev) => {
                      if (!prev) return null;
                      return {
                        ...prev,
                        topic: {
                          ...prev.topic,
                          questions: [...(prev.topic.questions || []), newQ],
                        },
                      };
                    });
                  }}
                >
                  <Plus size={14} /> Añadir Pregunta
                </Button>
              </div>

              {editingTopicState.topic.questions && editingTopicState.topic.questions.map((q, qIdx) => (
                <div key={q.id || qIdx} className={styles.questionCard}>
                  <div className={styles.questionCardHeader}>
                    <span className={styles.questionIndexBadge}>Pregunta {qIdx + 1}</span>
                    <button
                      type="button"
                      className={styles.deleteBtn}
                      onClick={() => {
                        setEditingTopicState((prev) => {
                          if (!prev) return null;
                          return {
                            ...prev,
                            topic: {
                              ...prev.topic,
                              questions: (prev.topic.questions || []).filter((_, idx) => idx !== qIdx),
                            },
                          };
                        });
                      }}
                      title="Eliminar pregunta"
                    >
                      <Trash2 size={14} />
                    </button>
                  </div>
                  <Input
                    label="Enunciado de la Pregunta"
                    placeholder="Ej: ¿Cuál es el concepto clave explicado en este video?"
                    value={q.question}
                    onChange={(e) => {
                      const val = e.target.value;
                      setEditingTopicState((prev) => {
                        if (!prev) return null;
                        const nextQs = [...(prev.topic.questions || [])];
                        nextQs[qIdx] = { ...nextQs[qIdx], question: val };
                        return { ...prev, topic: { ...prev.topic, questions: nextQs } };
                      });
                    }}
                  />
                  <div className={styles.optionsGrid}>
                    <label className={styles.miniLabel}>Opciones de Respuesta (marca la correcta):</label>
                    {q.options.map((opt, oIdx) => (
                      <div key={oIdx} className={styles.optionItemRow}>
                        <input
                          type="radio"
                          name={`top-q-opt-${q.id || qIdx}`}
                          checked={q.correctIndex === oIdx}
                          onChange={() => {
                            setEditingTopicState((prev) => {
                              if (!prev) return null;
                              const nextQs = [...(prev.topic.questions || [])];
                              nextQs[qIdx] = { ...nextQs[qIdx], correctIndex: oIdx };
                              return { ...prev, topic: { ...prev.topic, questions: nextQs } };
                            });
                          }}
                          className={styles.optionRadio}
                          title="Marcar como respuesta correcta"
                        />
                        <input
                          type="text"
                          value={opt}
                          onChange={(e) => {
                            const val = e.target.value;
                            setEditingTopicState((prev) => {
                              if (!prev) return null;
                              const nextQs = [...(prev.topic.questions || [])];
                              const nextOpts = [...nextQs[qIdx].options];
                              nextOpts[oIdx] = val;
                              nextQs[qIdx] = { ...nextQs[qIdx], options: nextOpts };
                              return { ...prev, topic: { ...prev.topic, questions: nextQs } };
                            });
                          }}
                          className={styles.fileNameInput}
                          placeholder={`Opción ${oIdx + 1}...`}
                        />
                      </div>
                    ))}
                    {q.options.length < 4 && (
                      <button
                        type="button"
                        className={styles.subtopicsToggleBtn}
                        style={{ width: "fit-content", marginTop: 4 }}
                        onClick={() => {
                          setEditingTopicState((prev) => {
                            if (!prev) return null;
                            const nextQs = [...(prev.topic.questions || [])];
                            nextQs[qIdx] = {
                              ...nextQs[qIdx],
                              options: [...nextQs[qIdx].options, `Opción ${nextQs[qIdx].options.length + 1}`],
                            };
                            return { ...prev, topic: { ...prev.topic, questions: nextQs } };
                          });
                        }}
                      >
                        + Añadir opción de respuesta
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </Modal>
        )}

        {/* MODAL PARA EDITAR SUBTOPIC (SUB-CLASE CORTA) */}
        {editingSubtopicState && (
          <Modal
            isOpen={true}
            onClose={() => setEditingSubtopicState(null)}
            title={
              editingSubtopicState.isNew
                ? "Nueva Sub-clase"
                : `Editar Sub-clase: ${editingSubtopicState.subtopic.title}`
            }
            description="Configura la clase corta: video, duración, notas, descargables y preguntas."
            maxWidth="740px"
            footer={
              <>
                <Button variant="ghost" onClick={() => setEditingSubtopicState(null)}>
                  Cancelar
                </Button>
                <Button variant="primary" onClick={handleSaveSubtopicFromModal}>
                  Guardar Sub-clase
                </Button>
              </>
            }
          >
            <div className={styles.formGroup}>
              <Input
                label="Título de la Sub-clase *"
                placeholder="Ej: Parte 1: Introducción a la herramienta..."
                value={editingSubtopicState.subtopic.title}
                onChange={(e) =>
                  setEditingSubtopicState({
                    ...editingSubtopicState,
                    subtopic: { ...editingSubtopicState.subtopic, title: e.target.value },
                  })
                }
              />
            </div>

            <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr", gap: "16px" }}>
              <div className={styles.formGroup}>
                <Input
                  label="Enlace del Video (YouTube, Vimeo, BunnyCDN, MP4)"
                  placeholder="https://www.youtube.com/watch?v=... o https://vimeo.com/..."
                  value={editingSubtopicState.subtopic.video_url || ""}
                  onChange={(e) =>
                    setEditingSubtopicState({
                      ...editingSubtopicState,
                      subtopic: { ...editingSubtopicState.subtopic, video_url: e.target.value },
                    })
                  }
                />
              </div>

              <div className={styles.formGroup}>
                <Input
                  label="Duración Estimada"
                  placeholder="Ej: 10m"
                  value={editingSubtopicState.subtopic.duration || "10m"}
                  onChange={(e) =>
                    setEditingSubtopicState({
                      ...editingSubtopicState,
                      subtopic: { ...editingSubtopicState.subtopic, duration: e.target.value },
                    })
                  }
                />
              </div>
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label}>Descripción o Notas de la Sub-clase</label>
              <textarea
                placeholder="Resumen o conceptos clave de esta parte corta..."
                value={editingSubtopicState.subtopic.description || ""}
                onChange={(e) =>
                  setEditingSubtopicState({
                    ...editingSubtopicState,
                    subtopic: { ...editingSubtopicState.subtopic, description: e.target.value },
                  })
                }
                className={styles.textarea}
                rows={3}
              />
            </div>

            {/* Archivos Descargables del Subtopic */}
            <div className={styles.filesSection}>
              <div className={styles.filesHeader}>
                <div>
                  <span className={styles.filesTitle}>
                    Archivos Complementarios ({editingSubtopicState.subtopic.files?.length || 0})
                  </span>
                  <p className={styles.pageSubtitle} style={{ margin: "2px 0 0" }}>
                    Recursos y materiales descargables para esta sub-clase
                  </p>
                </div>
              </div>

              <FileDropzone
                multiple={true}
                maxFiles={5}
                showList={false}
                label="Arrastra archivos para esta sub-clase"
                description="PDF, ZIP, DOCX, XLSX (Máx. 50MB)"
                onFilesChange={(files: File[]) => {
                  if (files && files.length > 0) {
                    files.forEach((file) => {
                      const newAttachment: CourseFileAttachment = {
                        id: `f-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`,
                        name: file.name,
                        size: formatFileSize(file.size),
                        url: URL.createObjectURL(file),
                      };
                      setEditingSubtopicState((prev) => {
                        if (!prev) return null;
                        return {
                          ...prev,
                          subtopic: {
                            ...prev.subtopic,
                            files: [...(prev.subtopic.files || []), newAttachment],
                          },
                        };
                      });
                    });
                  }
                }}
              />

              {editingSubtopicState.subtopic.files && editingSubtopicState.subtopic.files.length > 0 && (
                <div className={styles.filesList}>
                  {editingSubtopicState.subtopic.files.map((file, fIdx) => (
                    <div key={file.id || fIdx} className={styles.fileItem}>
                      <div className={styles.fileItemLeft}>
                        <div className={styles.fileIconWrap}><Paperclip size={16} /></div>
                        <input
                          type="text"
                          className={styles.fileNameInput}
                          value={file.name}
                          onChange={(e) => {
                            const val = e.target.value;
                            setEditingSubtopicState((prev) => {
                              if (!prev) return null;
                              const nextFiles = [...(prev.subtopic.files || [])];
                              nextFiles[fIdx] = { ...nextFiles[fIdx], name: val };
                              return { ...prev, subtopic: { ...prev.subtopic, files: nextFiles } };
                            });
                          }}
                        />
                      </div>
                      <div className={styles.fileItemRight}>
                        {file.size && <span className={styles.fileSizeBadge}>{file.size}</span>}
                        <button
                          type="button"
                          className={styles.deleteBtn}
                          onClick={() => {
                            setEditingSubtopicState((prev) => {
                              if (!prev) return null;
                              return {
                                ...prev,
                                subtopic: {
                                  ...prev.subtopic,
                                  files: (prev.subtopic.files || []).filter((_, idx) => idx !== fIdx),
                                },
                              };
                            });
                          }}
                        >
                          <Trash2 size={14} />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Preguntas de Autoevaluación del Subtopic */}
            <div className={styles.filesSection}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 10 }}>
                <div>
                  <span className={styles.filesTitle}>
                    Preguntas / Quiz ({editingSubtopicState.subtopic.questions?.length || 0})
                  </span>
                  <p className={styles.pageSubtitle} style={{ margin: "2px 0 0" }}>
                    Preguntas de autoevaluación para esta sub-clase
                  </p>
                </div>
                <Button
                  variant="secondary"
                  size="sm"
                  onClick={() => {
                    const newQ: CourseQuestion = {
                      id: `q-${Date.now()}`,
                      question: "",
                      options: ["Opción A", "Opción B"],
                      correctIndex: 0,
                    };
                    setEditingSubtopicState((prev) => {
                      if (!prev) return null;
                      return {
                        ...prev,
                        subtopic: {
                          ...prev.subtopic,
                          questions: [...(prev.subtopic.questions || []), newQ],
                        },
                      };
                    });
                  }}
                >
                  <Plus size={14} /> Añadir Pregunta
                </Button>
              </div>

              {editingSubtopicState.subtopic.questions && editingSubtopicState.subtopic.questions.map((q, qIdx) => (
                <div key={q.id || qIdx} className={styles.questionCard}>
                  <div className={styles.questionCardHeader}>
                    <span className={styles.questionIndexBadge}>Pregunta {qIdx + 1}</span>
                    <button
                      type="button"
                      className={styles.deleteBtn}
                      onClick={() => {
                        setEditingSubtopicState((prev) => {
                          if (!prev) return null;
                          return {
                            ...prev,
                            subtopic: {
                              ...prev.subtopic,
                              questions: (prev.subtopic.questions || []).filter((_, idx) => idx !== qIdx),
                            },
                          };
                        });
                      }}
                    >
                      <Trash2 size={14} />
                    </button>
                  </div>
                  <Input
                    label="Enunciado"
                    placeholder="Escribe la pregunta..."
                    value={q.question}
                    onChange={(e) => {
                      const val = e.target.value;
                      setEditingSubtopicState((prev) => {
                        if (!prev) return null;
                        const nextQs = [...(prev.subtopic.questions || [])];
                        nextQs[qIdx] = { ...nextQs[qIdx], question: val };
                        return { ...prev, subtopic: { ...prev.subtopic, questions: nextQs } };
                      });
                    }}
                  />
                  <div className={styles.optionsGrid}>
                    <label className={styles.miniLabel}>Opciones (marca la correcta):</label>
                    {q.options.map((opt, oIdx) => (
                      <div key={oIdx} className={styles.optionItemRow}>
                        <input
                          type="radio"
                          name={`sub-q-opt-${q.id || qIdx}`}
                          checked={q.correctIndex === oIdx}
                          onChange={() => {
                            setEditingSubtopicState((prev) => {
                              if (!prev) return null;
                              const nextQs = [...(prev.subtopic.questions || [])];
                              nextQs[qIdx] = { ...nextQs[qIdx], correctIndex: oIdx };
                              return { ...prev, subtopic: { ...prev.subtopic, questions: nextQs } };
                            });
                          }}
                          className={styles.optionRadio}
                        />
                        <input
                          type="text"
                          value={opt}
                          onChange={(e) => {
                            const val = e.target.value;
                            setEditingSubtopicState((prev) => {
                              if (!prev) return null;
                              const nextQs = [...(prev.subtopic.questions || [])];
                              const nextOpts = [...nextQs[qIdx].options];
                              nextOpts[oIdx] = val;
                              nextQs[qIdx] = { ...nextQs[qIdx], options: nextOpts };
                              return { ...prev, subtopic: { ...prev.subtopic, questions: nextQs } };
                            });
                          }}
                          className={styles.fileNameInput}
                        />
                      </div>
                    ))}
                  </div>
                </div>
              ))}
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
        <div className={styles.topHeaderActions}>
          <div className={styles.searchWrap}>
            <Input
              sizeVariant="md"
              leftIcon={<Search size={16} />}
              placeholder="Buscar curso por nombre o descripción..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </div>
          <Button variant="primary" onClick={() => setIsNewCourseModalOpen(true)}>
            <Plus size={16} /> Crear Nuevo Curso
          </Button>
        </div>
      </div>

      {/* Banner Informativo si LearnDash está presente y no se ha seleccionado modo nativo */}
      {(lmsSettings?.learndash_courses_count ?? 0) > 0 && lmsSettings?.mode !== "native" && (
        <div className={styles.lmsCatalogBanner}>
          <div className={styles.lmsCatalogBannerLeft}>
            <Sparkles size={22} className={styles.lmsCatalogBannerIcon} />
            <div>
              <h4 className={styles.lmsCatalogBannerTitle}>
                LearnDash Detectado: {lmsSettings?.learndash_courses_count} curso(s) encontrados
              </h4>
              <p className={styles.lmsCatalogBannerDesc}>
                Puedes operar con LearnDash o copiarlos a Crezca LMS nativo con 1 clic para tener mayor velocidad, soporte de sub-clases y total independencia.
              </p>
            </div>
          </div>
          <div className={styles.lmsCatalogBannerActions}>
            <Button
              variant="primary"
              size="sm"
              onClick={handleOpenMigrateModal}
              disabled={isMigrating}
            >
              {isMigrating ? <RefreshCw size={13} /> : <ArrowRightLeft size={13} />}
              <span>{isMigrating ? "Migrando..." : "Migrar a Crezca LMS"}</span>
            </Button>
            <Button
              variant="secondary"
              size="sm"
              onClick={() => setIsLmsModalOpen(true)}
            >
              <Settings size={13} />
              <span>Configurar Motor</span>
            </Button>
          </div>
        </div>
      )}

      {/* Pestañas de Filtro de Estado (Tags sueltos sin card) */}
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

      {/* Modal de Configuración de Motor LMS y Migración */}
      {isLmsModalOpen && (
        <Modal
          isOpen={true}
          onClose={() => setIsLmsModalOpen(false)}
          title="Motor de Cursos (LMS Engine) & Migración"
          maxWidth="640px"
          footer={
            <Button variant="secondary" onClick={() => setIsLmsModalOpen(false)}>
              Cerrar
            </Button>
          }
        >
          <div className={styles.lmsModalBody}>
            {/* Estadísticas de cursos */}
            <div className={styles.lmsModalHeaderStats}>
              <div className={styles.lmsModalStatCard}>
                <span className={styles.lmsModalStatLabel}>LearnDash</span>
                <span className={styles.lmsModalStatValue} style={{ fontSize: 13, color: lmsSettings?.is_learndash_active ? "#0db879" : "var(--text-muted)" }}>
                  {lmsSettings?.is_learndash_active ? "● Activo en WP" : "○ No detectado"}
                </span>
              </div>
              <div className={styles.lmsModalStatCard}>
                <span className={styles.lmsModalStatLabel}>Cursos LearnDash</span>
                <span className={styles.lmsModalStatValue}>
                  {lmsSettings?.learndash_courses_count ?? 0}
                </span>
              </div>
              <div className={styles.lmsModalStatCard}>
                <span className={styles.lmsModalStatLabel}>Cursos Crezca LMS</span>
                <span className={styles.lmsModalStatValue} style={{ color: "var(--accent)" }}>
                  {lmsSettings?.native_courses_count ?? 0}
                </span>
              </div>
            </div>

            {/* Selector de Modo */}
            <div>
              <label className={styles.label} style={{ marginBottom: 8, display: "block" }}>
                Selecciona el motor que gestionará tus formaciones:
              </label>
              <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
                <div
                  className={[styles.lmsModeItemMini, lmsSettings?.mode === "native" ? styles.lmsModeItemMiniActive : ""].join(" ")}
                  onClick={() => handleLmsModeChange("native")}
                >
                  <div style={{ flex: 1 }}>
                    <div className={styles.lmsModeItemMiniTitle}>
                      <span>Crezca LMS Nativo</span>
                      <span className={[styles.topicBadge, styles.topicBadgeDesc].join(" ")} style={{ color: "#0db879" }}>Recomendado</span>
                    </div>
                    <p className={styles.lmsModeItemMiniDesc}>
                      Almacena los cursos de forma 100% independiente en tu base de datos (CPT crezca_course). Rápido, seguro y sin plugins adicionales.
                    </p>
                  </div>
                  {lmsSettings?.mode === "native" && <Check size={16} color="var(--accent)" />}
                </div>

                <div
                  className={[styles.lmsModeItemMini, lmsSettings?.mode === "auto" ? styles.lmsModeItemMiniActive : ""].join(" ")}
                  onClick={() => handleLmsModeChange("auto")}
                >
                  <div style={{ flex: 1 }}>
                    <div className={styles.lmsModeItemMiniTitle}>
                      <span>Detección Automática (Híbrido)</span>
                    </div>
                    <p className={styles.lmsModeItemMiniDesc}>
                      Si LearnDash está instalado y activo, lee sus cursos; de lo contrario activa automáticamente Crezca LMS.
                    </p>
                  </div>
                  {lmsSettings?.mode === "auto" && <Check size={16} color="var(--accent)" />}
                </div>

                <div
                  className={[styles.lmsModeItemMini, lmsSettings?.mode === "learndash" ? styles.lmsModeItemMiniActive : ""].join(" ")}
                  onClick={() => handleLmsModeChange("learndash")}
                >
                  <div style={{ flex: 1 }}>
                    <div className={styles.lmsModeItemMiniTitle}>
                      <span>Forzar LearnDash LMS</span>
                    </div>
                    <p className={styles.lmsModeItemMiniDesc}>
                      Opera exclusivamente sobre los cursos legados de LearnDash (sfwd-courses).
                    </p>
                  </div>
                  {lmsSettings?.mode === "learndash" && <Check size={16} color="var(--accent)" />}
                </div>
              </div>
            </div>

            {/* Acción de Migración */}
            <div style={{ background: "var(--surface-muted)", border: "1px solid var(--border-subtle)", borderRadius: "var(--radius-control)", padding: 14 }}>
              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", gap: 12, flexWrap: "wrap" }}>
                <div>
                  <h5 style={{ margin: "0 0 2px", fontSize: 13, fontWeight: 700, color: "var(--foreground)" }}>
                    Migrar Cursos desde LearnDash a Crezca LMS
                  </h5>
                  <p style={{ margin: 0, fontSize: 11.5, color: "var(--text-muted)" }}>
                    Copia cursos, módulos, lecciones, temas y alumnos matriculados al motor nativo sin alterar los originales.
                  </p>
                </div>
                <Button
                  variant="primary"
                  size="sm"
                  onClick={handleOpenMigrateModal}
                  disabled={isMigrating || (lmsSettings?.learndash_courses_count ?? 0) === 0}
                >
                  {isMigrating ? <RefreshCw size={13} /> : <ArrowRightLeft size={13} />}
                  <span>{isMigrating ? "Migrando..." : "Iniciar Migración"}</span>
                </Button>
              </div>

              {migrationResult && (
                <div style={{ marginTop: 12 }}>
                  <Alert
                    tone={migrationResult.success ? "success" : "warning"}
                    title="Resultado"
                    onDismiss={() => setMigrationResult(null)}
                  >
                    {migrationResult.message}
                  </Alert>
                </div>
              )}
            </div>
          </div>
        </Modal>
      )}

      {/* Modal Arc de Confirmación de Migración de Cursos */}
      {isMigrationModalOpen && (
        <Modal
          isOpen={true}
          onClose={() => {
            if (!isMigrating) setIsMigrationModalOpen(false);
          }}
          title="Migrar Cursos a Crezca LMS"
          maxWidth="520px"
          footer={
            <>
              <Button
                variant="ghost"
                onClick={() => setIsMigrationModalOpen(false)}
                disabled={isMigrating}
              >
                Cancelar
              </Button>
              <Button
                variant="primary"
                onClick={confirmAndExecuteMigration}
                disabled={isMigrating}
              >
                {isMigrating ? (
                  <>
                    <RefreshCw size={14} className="spin" /> Migrando cursos...
                  </>
                ) : (
                  <>
                    <ArrowRightLeft size={14} /> Confirmar e Iniciar
                  </>
                )}
              </Button>
            </>
          }
        >
          <div className={styles.migrationConfirmBody}>
            <div className={styles.migrationConfirmIconWrap}>
              <ArrowRightLeft size={22} />
            </div>
            <div className={styles.migrationConfirmContent}>
              <h4 className={styles.migrationConfirmTitle}>
                ¿Deseas migrar todos tus cursos de LearnDash a Crezca LMS?
              </h4>
              <p className={styles.migrationConfirmDesc}>
                Se crearán copias nativas en Crezca con sus módulos, lecciones, temas y alumnos matriculados. Los cursos originales de LearnDash <strong>no se verán afectados ni se borrarán</strong>.
              </p>
              <div className={styles.migrationConfirmList}>
                <div className={styles.migrationConfirmItem}>
                  <Check size={14} className={styles.migrationConfirmItemIcon} />
                  <span>Conserva videos, descripciones y archivos descargables</span>
                </div>
                <div className={styles.migrationConfirmItem}>
                  <Check size={14} className={styles.migrationConfirmItemIcon} />
                  <span>Transfiere el progreso de lecciones de los estudiantes</span>
                </div>
                <div className={styles.migrationConfirmItem}>
                  <Check size={14} className={styles.migrationConfirmItemIcon} />
                  <span>Permite operar de forma 100% independiente sin LearnDash</span>
                </div>
              </div>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
