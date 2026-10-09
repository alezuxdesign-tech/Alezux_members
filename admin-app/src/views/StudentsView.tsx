import { useState, useEffect } from "react";
import { Search, ShieldAlert, GraduationCap, Check, Plus, Filter } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Switch } from "../components/arc/switch/switch";
import { Input } from "../components/arc/input/input";
import { api, Student, Course } from "../services/api";
import styles from "./StudentsView.module.css";

export function StudentsView() {
  const [students, setStudents] = useState<Student[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [search, setSearch] = useState("");
  const [filterStatus, setFilterStatus] = useState<string>("all");

  // Modal de Gestión de Accesos
  const [selectedStudent, setSelectedStudent] = useState<Student | null>(null);
  const [studentCourses, setStudentCourses] = useState<number[]>([]);
  const [isSaving, setIsSaving] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);

  useEffect(() => {
    api.getStudents().then(setStudents);
    api.getCourses().then(setCourses);
  }, []);

  const openAccessModal = (student: Student) => {
    setSelectedStudent(student);
    setStudentCourses([...student.enabledCourseIds]);
    setSavedSuccess(false);
  };

  const handleToggleCourse = (courseId: number) => {
    if (studentCourses.includes(courseId)) {
      setStudentCourses(studentCourses.filter((id) => id !== courseId));
    } else {
      setStudentCourses([...studentCourses, courseId]);
    }
  };

  const saveCourseAccess = async () => {
    if (!selectedStudent) return;
    setIsSaving(true);

    for (const course of courses) {
      const isEnabled = studentCourses.includes(course.id);
      await api.toggleStudentCourseAccess(selectedStudent.id, course.id, isEnabled);
    }

    setStudents((prev) =>
      prev.map((s) => (s.id === selectedStudent.id ? { ...s, enabledCourseIds: studentCourses } : s))
    );

    setIsSaving(false);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 2500);
  };

  const filteredStudents = students.filter((student) => {
    const matchesSearch =
      student.name.toLowerCase().includes(search.toLowerCase()) ||
      student.email.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = filterStatus === "all" || student.status === filterStatus;
    return matchesSearch && matchesStatus;
  });

  return (
    <div className={styles.container}>
      {/* Barra Superior con Controles */}
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Gestión de Estudiantes</h1>
          <p className={styles.subtitle}>
            Administra los accesos de tus alumnos a los cursos y supervisa su estado de cuenta.
          </p>
        </div>

        <div className={styles.actions}>
          <div className={styles.searchWrap}>
            <Input
              sizeVariant="sm"
              leftIcon={<Search size={14} />}
              placeholder="Buscar por nombre o correo..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>

          <div className={styles.filterDropdown}>
            <Filter size={14} />
            <select
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
              className={styles.select}
            >
              <option value="all">Todos los estados</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
              <option value="blocked">Bloqueados</option>
            </select>
          </div>

          <Button variant="primary" size="sm">
            <Plus size={15} />
            Nuevo Alumno
          </Button>
        </div>
      </div>

      {/* Tabla de Estudiantes */}
      <div className={styles.tableWrapper}>
        <table className={styles.table}>
          <thead>
            <tr>
              <th>Estudiante</th>
              <th>Fecha de Registro</th>
              <th>Plan / Membresía</th>
              <th>Cursos Habilitados</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {filteredStudents.map((student) => {
              const enabledCount = student.enabledCourseIds.length;
              return (
                <tr key={student.id}>
                  <td>
                    <div className={styles.studentCell}>
                      <img src={student.avatar} alt={student.name} className={styles.avatar} />
                      <div>
                        <div className={styles.studentName}>{student.name}</div>
                        <div className={styles.studentEmail}>{student.email}</div>
                      </div>
                    </div>
                  </td>
                  <td className={styles.tabularDate}>{student.joinedDate}</td>
                  <td>
                    <span className={styles.planName}>{student.planName}</span>
                  </td>
                  <td>
                    <Badge variant={enabledCount > 0 ? "accent" : "neutral"} size="sm">
                      <GraduationCap size={13} />
                      {enabledCount} {enabledCount === 1 ? "curso" : "cursos"}
                    </Badge>
                  </td>
                  <td>
                    <Badge
                      size="sm"
                      variant={
                        student.status === "active"
                          ? "success"
                          : student.status === "blocked"
                          ? "danger"
                          : "warning"
                      }
                    >
                      {student.status === "active"
                        ? "Activo"
                        : student.status === "blocked"
                        ? "Bloqueado"
                        : "Pendiente"}
                    </Badge>
                  </td>
                  <td style={{ textAlign: "right" }}>
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => openAccessModal(student)}
                    >
                      Gestionar Cursos
                    </Button>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>

        {filteredStudents.length === 0 && (
          <div className={styles.emptyState}>
            <ShieldAlert size={36} className={styles.emptyIcon} />
            <p>No se encontraron estudiantes que coincidan con la búsqueda.</p>
          </div>
        )}
      </div>

      {/* Modal para Habilitar / Inhabilitar Cursos de Estudiante */}
      <Modal
        isOpen={!!selectedStudent}
        onClose={() => setSelectedStudent(null)}
        title={
          selectedStudent ? (
            <div className={styles.modalTitleWrap}>
              <span>Gestionar Cursos de {selectedStudent.name}</span>
            </div>
          ) : (
            ""
          )
        }
        description="Activa o desactiva qué formaciones están habilitadas para este estudiante de manera inmediata."
        footer={
          <>
            {savedSuccess && (
              <span className={styles.successMessage}>
                <Check size={16} /> Accesos actualizados
              </span>
            )}
            <Button variant="ghost" onClick={() => setSelectedStudent(null)}>
              Cerrar
            </Button>
            <Button variant="primary" loading={isSaving} onClick={saveCourseAccess}>
              Guardar Cambios
            </Button>
          </>
        }
      >
        <div className={styles.courseAccessList}>
          {courses.map((course) => {
            const isEnabled = studentCourses.includes(course.id);
            return (
              <div
                key={course.id}
                className={[styles.courseAccessRow, isEnabled ? styles.courseRowActive : ""].join(" ")}
                onClick={() => handleToggleCourse(course.id)}
              >
                <div className={styles.courseThumbWrap}>
                  <img src={course.thumbnail} alt={course.title} className={styles.courseThumb} />
                </div>
                <div className={styles.courseDetails}>
                  <h4 className={styles.courseRowTitle}>{course.title}</h4>
                  <span className={styles.courseModulesCount}>
                    {course.sections.length} Módulos estructurados
                  </span>
                </div>
                <div onClick={(e) => e.stopPropagation()}>
                  <Switch
                    checked={isEnabled}
                    onCheckedChange={() => handleToggleCourse(course.id)}
                    aria-label={`Acceso a ${course.title}`}
                  />
                </div>
              </div>
            );
          })}
        </div>
      </Modal>
    </div>
  );
}
