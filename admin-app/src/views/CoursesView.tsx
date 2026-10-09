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
  ChevronDown
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { api, Course, CourseSection, CourseLesson } from "../services/api";
import { ModuleSkeleton } from "../components/arc/skeleton";
import styles from "./CoursesView.module.css";

export function CoursesView() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Modal para Nuevo Curso
  const [isNewCourseModalOpen, setIsNewCourseModalOpen] = useState(false);
  const [newTitle, setNewTitle] = useState("");
  const [newDescription, setNewDescription] = useState("");
  const [newThumbnail, setNewThumbnail] = useState("");

  // Estado del Constructor del Curso Activo
  const [sections, setSections] = useState<CourseSection[]>([]);
  const [isSavingCurriculum, setIsSavingCurriculum] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);
  const [collapsedSections, setCollapsedSections] = useState<Record<string, boolean>>({});

  // Drag and Drop state
  const [draggedLesson, setDraggedLesson] = useState<{ sectionId: string; lessonIndex: number } | null>(null);

  useEffect(() => {
    api.getCourses()
      .then((data) => {
        setCourses(data);
        if (data.length > 0 && !selectedCourse) {
          setSelectedCourse(data[0]);
          setSections(data[0].sections);
        }
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, []);

  const handleSelectCourse = (course: Course) => {
    setSelectedCourse(course);
    setSections(course.sections);
    setSavedSuccess(false);
  };

  const handleCreateCourse = async () => {
    if (!newTitle.trim()) return;
    const created = await api.createCourse(newTitle, newDescription, newThumbnail);
    setCourses([created, ...courses]);
    setSelectedCourse(created);
    setSections(created.sections);
    setIsNewCourseModalOpen(false);
    setNewTitle("");
    setNewDescription("");
    setNewThumbnail("");
  };

  // --- Operaciones del Constructor Drag & Drop ---
  const handleAddSection = () => {
    const newSection: CourseSection = {
      id: `sec-${Date.now()}`,
      title: `Nuevo Módulo ${sections.length + 1}`,
      lessons: [
        { id: `les-${Date.now()}`, title: "Lección 1: Introducción", duration: "10m" },
      ],
    };
    setSections([...sections, newSection]);
  };

  const handleDeleteSection = (sectionId: string) => {
    setSections(sections.filter((s) => s.id !== sectionId));
  };

  const handleUpdateSectionTitle = (sectionId: string, title: string) => {
    setSections(sections.map((s) => (s.id === sectionId ? { ...s, title } : s)));
  };

  const handleAddLesson = (sectionId: string) => {
    const section = sections.find((s) => s.id === sectionId);
    const lessonCount = section ? section.lessons.length + 1 : 1;
    const newLesson: CourseLesson = {
      id: `les-${Date.now()}`,
      title: `Lección ${lessonCount}: Nueva Clase`,
      duration: "15m",
    };
    setSections(
      sections.map((s) => (s.id === sectionId ? { ...s, lessons: [...s.lessons, newLesson] } : s))
    );
  };

  const handleDeleteLesson = (sectionId: string, lessonId: string) => {
    setSections(
      sections.map((s) =>
        s.id === sectionId ? { ...s, lessons: s.lessons.filter((l) => l.id !== lessonId) } : s
      )
    );
  };

  const handleUpdateLessonTitle = (sectionId: string, lessonId: string, title: string) => {
    setSections(
      sections.map((s) =>
        s.id === sectionId
          ? {
              ...s,
              lessons: s.lessons.map((l) => (l.id === lessonId ? { ...l, title } : l)),
            }
          : s
      )
    );
  };

  const toggleSectionCollapse = (sectionId: string) => {
    setCollapsedSections((prev) => ({ ...prev, [sectionId]: !prev[sectionId] }));
  };

  // --- Drag and Drop Handlers ---
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

  const handleSaveCurriculum = async () => {
    if (!selectedCourse) return;
    setIsSavingCurriculum(true);
    await api.saveCourseCurriculum(selectedCourse.id, sections);
    setIsSavingCurriculum(false);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 2500);
  };

  if (isLoading) {
    return <ModuleSkeleton type="courses" />;
  }

  return (
    <div className={styles.container}>
      {/* Barra Superior */}
      <div className={styles.topHeader}>
        <div>
          <h1 className={styles.pageTitle}>Gestión y Creación de Cursos</h1>
          <p className={styles.pageSubtitle}>
            Diseña formaciones completas y estructura el plan de estudios con el constructor Drag & Drop.
          </p>
        </div>
        <Button variant="primary" onClick={() => setIsNewCourseModalOpen(true)}>
          <Plus size={16} /> Crear Nuevo Curso
        </Button>
      </div>

      <div className={styles.layout}>
        {/* Selector Lateral de Cursos */}
        <div className={styles.courseSidebar}>
          <h3 className={styles.sidebarTitle}>Cursos Activos ({courses.length})</h3>
          <div className={styles.courseList}>
            {courses.map((course) => {
              const isSelected = selectedCourse?.id === course.id;
              return (
                <div
                  key={course.id}
                  className={[styles.courseCard, isSelected ? styles.courseCardActive : ""].join(" ")}
                  onClick={() => handleSelectCourse(course)}
                >
                  <img src={course.thumbnail} alt={course.title} className={styles.courseThumb} />
                  <div className={styles.courseCardInfo}>
                    <h4 className={styles.courseCardTitle}>{course.title}</h4>
                    <div className={styles.courseCardMeta}>
                      <span>{course.studentCount} Alumnos</span>
                      <Badge variant="success" size="sm">Publicado</Badge>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Panel del Constructor de Currículum (Drag and Drop) */}
        {selectedCourse && (
          <div className={styles.builderArea}>
            <div className={styles.builderHeader}>
              <div>
                <span className={styles.builderTagline}>Constructor de Currículum</span>
                <h2 className={styles.builderCourseTitle}>{selectedCourse.title}</h2>
              </div>
              <div className={styles.builderActions}>
                {savedSuccess && (
                  <span className={styles.saveAlert}>
                    <Check size={16} /> Estructura guardada
                  </span>
                )}
                <Button variant="secondary" onClick={handleAddSection}>
                  <FolderPlus size={16} /> + Módulo
                </Button>
                <Button
                  variant="primary"
                  loading={isSavingCurriculum}
                  onClick={handleSaveCurriculum}
                >
                  Guardar Estructura
                </Button>
              </div>
            </div>

            {/* Lista de Módulos / Secciones */}
            <div className={styles.sectionsContainer}>
              {sections.map((section, sIdx) => {
                const isCollapsed = !!collapsedSections[section.id];
                return (
                  <div key={section.id} className={styles.sectionBlock}>
                    {/* Encabezado del Módulo */}
                    <div className={styles.sectionHeader}>
                      <button
                        type="button"
                        className={styles.collapseToggle}
                        onClick={() => toggleSectionCollapse(section.id)}
                      >
                        {isCollapsed ? <ChevronRight size={18} /> : <ChevronDown size={18} />}
                      </button>

                      <div className={styles.sectionTitleBox}>
                        <span className={styles.sectionIndexBadge}>Módulo {sIdx + 1}</span>
                        <input
                          type="text"
                          value={section.title}
                          onChange={(e) => handleUpdateSectionTitle(section.id, e.target.value)}
                          className={styles.sectionTitleInput}
                        />
                      </div>

                      <div className={styles.sectionControls}>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleAddLesson(section.id)}
                          title="Agregar lección a este módulo"
                        >
                          <Plus size={15} /> Lección
                        </Button>
                        <button
                          type="button"
                          className={styles.deleteSectionBtn}
                          onClick={() => handleDeleteSection(section.id)}
                          title="Eliminar módulo"
                        >
                          <Trash2 size={15} />
                        </button>
                      </div>
                    </div>

                    {/* Lista de Lecciones Arrastrables (Drag and Drop) */}
                    {!isCollapsed && (
                      <div className={styles.lessonsDropArea}>
                        {section.lessons.map((lesson, lIdx) => (
                          <div
                            key={lesson.id}
                            className={styles.lessonItem}
                            draggable
                            onDragStart={() => handleDragStart(section.id, lIdx)}
                            onDragOver={handleDragOver}
                            onDrop={() => handleDrop(section.id, lIdx)}
                          >
                            <div className={styles.dragHandle} title="Arrastrar para reordenar">
                              <GripVertical size={16} />
                            </div>

                            <FileText size={16} className={styles.lessonIcon} />

                            <input
                              type="text"
                              value={lesson.title}
                              onChange={(e) =>
                                handleUpdateLessonTitle(section.id, lesson.id, e.target.value)
                              }
                              className={styles.lessonTitleInput}
                            />

                            <span className={styles.durationTag}>
                              <Clock size={12} /> {lesson.duration || "15m"}
                            </span>

                            <button
                              type="button"
                              className={styles.deleteLessonBtn}
                              onClick={() => handleDeleteLesson(section.id, lesson.id)}
                              title="Eliminar lección"
                            >
                              <Trash2 size={14} />
                            </button>
                          </div>
                        ))}

                        {section.lessons.length === 0 && (
                          <div
                            className={styles.emptyLessonsNotice}
                            onDragOver={handleDragOver}
                            onDrop={() => handleDrop(section.id, 0)}
                          >
                            <span>Arrastra lecciones aquí o haz clic en "+ Lección"</span>
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        )}
      </div>

      {/* Modal para Crear Nuevo Curso */}
      <Modal
        isOpen={isNewCourseModalOpen}
        onClose={() => setIsNewCourseModalOpen(false)}
        title="Crear Nueva Formación / Curso"
        description="Ingresa los datos iniciales del curso para habilitarlo en la plataforma."
        footer={
          <>
            <Button variant="ghost" onClick={() => setIsNewCourseModalOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" onClick={handleCreateCourse}>
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
            placeholder="Describe los objetivos y qué aprenderá el alumno..."
            value={newDescription}
            onChange={(e) => setNewDescription(e.target.value)}
            className={styles.textarea}
            rows={3}
          />
        </div>

        <div className={styles.formGroup}>
          <Input
            label="URL de Miniatura / Portada"
            placeholder="https://..."
            value={newThumbnail}
            onChange={(e) => setNewThumbnail(e.target.value)}
          />
        </div>
      </Modal>
    </div>
  );
}
