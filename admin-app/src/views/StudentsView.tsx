import { useState, useEffect } from "react";
import {
  Search,
  ShieldAlert,
  GraduationCap,
  Check,
  Plus,
  Filter,
  User,
  Key,
  Eye,
  EyeOff,
  Sparkles,
  AlertCircle,
  Mail,
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Switch } from "../components/arc/switch/switch";
import { Input } from "../components/arc/input/input";
import { SegmentedControl, SegmentOption } from "../components/arc/segmented-control/segmented-control";
import { api, Student, Course } from "../services/api";
import styles from "./StudentsView.module.css";

const MANAGE_STATUS_OPTIONS: SegmentOption<"active" | "inactive" | "blocked">[] = [
  { value: "active", label: "Activo" },
  { value: "inactive", label: "Inactivo" },
  { value: "blocked", label: "Bloqueado" },
];

const PWD_MODE_OPTIONS: SegmentOption<"keep" | "manual" | "auto">[] = [
  { value: "keep", label: "Mantener actual" },
  { value: "manual", label: "Cambiar manualmente" },
  { value: "auto", label: "Generar y enviar por correo" },
];

const CREATE_PWD_MODE_OPTIONS: SegmentOption<"auto" | "manual">[] = [
  { value: "auto", label: "Generar y enviar bienvenida" },
  { value: "manual", label: "Definir manualmente" },
];

export function StudentsView() {
  const [students, setStudents] = useState<Student[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [search, setSearch] = useState("");
  const [filterStatus, setFilterStatus] = useState<string>("all");

  // Modal de Gestión (Editar información, clave y cursos)
  const [selectedStudent, setSelectedStudent] = useState<Student | null>(null);
  const [manageTab, setManageTab] = useState<"info" | "courses">("info");
  const [editName, setEditName] = useState("");
  const [editEmail, setEditEmail] = useState("");
  const [editStatus, setEditStatus] = useState<"active" | "inactive" | "blocked">("active");
  const [editPasswordMode, setEditPasswordMode] = useState<"keep" | "manual" | "auto">("keep");
  const [editManualPassword, setEditManualPassword] = useState("");
  const [editSendEmail, setEditSendEmail] = useState(true);
  const [showEditPassword, setShowEditPassword] = useState(false);
  const [editCourses, setEditCourses] = useState<number[]>([]);
  const [isSaving, setIsSaving] = useState(false);
  const [manageFeedback, setManageFeedback] = useState<{ type: "success" | "error"; text: string } | null>(null);

  // Modal de Crear Nuevo Alumno
  const [isCreateOpen, setIsCreateOpen] = useState(false);
  const [createTab, setCreateTab] = useState<"info" | "courses">("info");
  const [newName, setNewName] = useState("");
  const [newEmail, setNewEmail] = useState("");
  const [newStatus, setNewStatus] = useState<"active" | "inactive" | "blocked">("active");
  const [newPasswordMode, setNewPasswordMode] = useState<"auto" | "manual">("auto");
  const [newPassword, setNewPassword] = useState("");
  const [newSendWelcome, setNewSendWelcome] = useState(true);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [newCourses, setNewCourses] = useState<number[]>([]);
  const [isCreating, setIsCreating] = useState(false);
  const [createFeedback, setCreateFeedback] = useState<{ type: "success" | "error"; text: string } | null>(null);

  useEffect(() => {
    api.getStudents().then(setStudents);
    api.getCourses().then(setCourses);
  }, []);

  // Abrir Modal de Gestión
  const openManageModal = (student: Student) => {
    setSelectedStudent(student);
    setManageTab("info");
    setEditName(student.name);
    setEditEmail(student.email);
    setEditStatus(student.status);
    setEditPasswordMode("keep");
    setEditManualPassword("");
    setEditSendEmail(true);
    setShowEditPassword(false);
    setEditCourses([...student.enabledCourseIds]);
    setManageFeedback(null);
  };

  const handleToggleEditCourse = (courseId: number) => {
    if (editCourses.includes(courseId)) {
      setEditCourses(editCourses.filter((id) => id !== courseId));
    } else {
      setEditCourses([...editCourses, courseId]);
    }
  };

  const handleSaveStudent = async () => {
    if (!selectedStudent) return;
    if (!editName.trim() || !editEmail.trim()) {
      setManageFeedback({ type: "error", text: "El nombre y el correo no pueden estar vacíos." });
      return;
    }

    if (editPasswordMode === "manual" && !editManualPassword.trim()) {
      setManageFeedback({
        type: "error",
        text: "Por favor ingresa una nueva contraseña o selecciona 'Mantener actual'.",
      });
      return;
    }

    setIsSaving(true);
    setManageFeedback(null);

    const res = await api.updateStudent(selectedStudent.id, {
      name: editName.trim(),
      email: editEmail.trim(),
      status: editStatus,
      passwordMode: editPasswordMode,
      password: editManualPassword,
      sendEmail: editSendEmail,
      enabledCourseIds: editCourses,
    });

    setIsSaving(false);

    if (res.success) {
      if (res.student) {
        setStudents((prev) => prev.map((s) => (s.id === selectedStudent.id ? res.student! : s)));
        setSelectedStudent(res.student);
      } else {
        setStudents((prev) =>
          prev.map((s) =>
            s.id === selectedStudent.id
              ? { ...s, name: editName, email: editEmail, status: editStatus, enabledCourseIds: editCourses }
              : s
          )
        );
      }
      setManageFeedback({ type: "success", text: res.message || "Estudiante actualizado correctamente." });
      setTimeout(() => {
        setSelectedStudent(null);
      }, 1400);
    } else {
      setManageFeedback({ type: "error", text: res.message || "Error al actualizar estudiante." });
    }
  };

  // Abrir Modal de Nuevo Alumno
  const openCreateModal = () => {
    setNewName("");
    setNewEmail("");
    setNewStatus("active");
    setNewPasswordMode("auto");
    setNewPassword("");
    setNewSendWelcome(true);
    setShowNewPassword(false);
    setNewCourses(courses.length > 0 ? [courses[0].id] : []);
    setCreateFeedback(null);
    setCreateTab("info");
    setIsCreateOpen(true);
  };

  const handleToggleNewCourse = (courseId: number) => {
    if (newCourses.includes(courseId)) {
      setNewCourses(newCourses.filter((id) => id !== courseId));
    } else {
      setNewCourses([...newCourses, courseId]);
    }
  };

  const handleCreateStudent = async () => {
    if (!newName.trim() || !newEmail.trim()) {
      setCreateFeedback({ type: "error", text: "Por favor completa el nombre y el correo electrónico." });
      return;
    }

    if (newPasswordMode === "manual" && !newPassword.trim()) {
      setCreateFeedback({
        type: "error",
        text: "Por favor escribe una contraseña para el alumno o selecciona 'Generar automáticamente'.",
      });
      return;
    }

    setIsCreating(true);
    setCreateFeedback(null);

    const res = await api.createStudent({
      name: newName.trim(),
      email: newEmail.trim(),
      status: newStatus,
      passwordMode: newPasswordMode,
      password: newPassword,
      sendWelcomeEmail: newSendWelcome,
      enabledCourseIds: newCourses,
    });

    setIsCreating(false);

    if (res.success) {
      if (res.student) {
        setStudents((prev) => [res.student!, ...prev]);
      }
      setCreateFeedback({ type: "success", text: res.message || "Alumno registrado exitosamente." });
      setTimeout(() => {
        setIsCreateOpen(false);
      }, 1400);
    } else {
      setCreateFeedback({ type: "error", text: res.message || "Error al registrar el alumno." });
    }
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
            Administra los accesos de tus alumnos a los cursos, modifica su información y gestiona sus contraseñas.
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

          <Button variant="primary" size="sm" onClick={openCreateModal}>
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
                        : "Inactivo"}
                    </Badge>
                  </td>
                  <td style={{ textAlign: "right" }}>
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => openManageModal(student)}
                    >
                      Gestionar
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

      {/* MODAL GESTIONAR ESTUDIANTE */}
      <Modal
        isOpen={!!selectedStudent}
        onClose={() => setSelectedStudent(null)}
        title={
          selectedStudent ? (
            <div className={styles.modalTitleWrap}>
              <span>Gestionar: {selectedStudent.name}</span>
            </div>
          ) : (
            ""
          )
        }
        description="Modifica la información personal, estado, contraseña y formaciones habilitadas para este estudiante."
        footer={
          <>
            {manageFeedback && (
              <div
                className={[
                  styles.feedbackBox,
                  manageFeedback.type === "success" ? styles.feedbackSuccess : styles.feedbackError,
                ].join(" ")}
              >
                {manageFeedback.type === "success" ? <Check size={14} /> : <AlertCircle size={14} />}
                <span>{manageFeedback.text}</span>
              </div>
            )}
            <Button variant="ghost" onClick={() => setSelectedStudent(null)}>
              Cerrar
            </Button>
            <Button variant="primary" loading={isSaving} onClick={handleSaveStudent}>
              Guardar Cambios
            </Button>
          </>
        }
      >
        <div className={styles.modalBody}>
          <div className={styles.modalTabs}>
            <SegmentedControl<"info" | "courses">
              options={[
                { value: "info", label: "Datos & Contraseña", icon: <User size={14} /> },
                {
                  value: "courses",
                  label: `Cursos Habilitados (${editCourses.length})`,
                  icon: <GraduationCap size={14} />,
                },
              ]}
              value={manageTab}
              onChange={setManageTab}
              size="sm"
            />
          </div>

          {manageTab === "info" && (
            <>
              {/* Información Personal */}
              <div className={styles.formGrid}>
                <div className={styles.formGroup}>
                  <label className={styles.label}>Nombre Completo</label>
                  <Input
                    value={editName}
                    onChange={(e) => setEditName(e.target.value)}
                    placeholder="Ej. Carlos Mendoza"
                  />
                </div>

                <div className={styles.formGroup}>
                  <label className={styles.label}>Correo Electrónico</label>
                  <Input
                    type="email"
                    value={editEmail}
                    onChange={(e) => setEditEmail(e.target.value)}
                    placeholder="alumno@ejemplo.com"
                  />
                </div>

                <div className={styles.formGridFull}>
                  <div className={styles.formGroup}>
                    <label className={styles.label}>Estado de la Cuenta</label>
                    <SegmentedControl<"active" | "inactive" | "blocked">
                      options={MANAGE_STATUS_OPTIONS}
                      value={editStatus}
                      onChange={setEditStatus}
                      size="sm"
                    />
                  </div>
                </div>
              </div>

              {/* Gestión de Contraseña */}
              <div className={styles.sectionCard}>
                <div className={styles.sectionHeader}>
                  <h4 className={styles.sectionTitle}>Seguridad & Contraseña</h4>
                  <p className={styles.sectionSubtitle}>
                    Cambia la contraseña de forma manual o genérala automáticamente y envíala por correo.
                  </p>
                </div>

                <SegmentedControl<"keep" | "manual" | "auto">
                  options={PWD_MODE_OPTIONS}
                  value={editPasswordMode}
                  onChange={setEditPasswordMode}
                  size="sm"
                />

                {editPasswordMode === "keep" && (
                  <p className={styles.switchDesc}>
                    La contraseña actual del alumno no sufrirá ninguna modificación al guardar.
                  </p>
                )}

                {editPasswordMode === "manual" && (
                  <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                    <div className={styles.formGroup}>
                      <label className={styles.label}>Nueva Contraseña</label>
                      <div className={styles.passwordInputWrapper}>
                        <Input
                          type={showEditPassword ? "text" : "password"}
                          value={editManualPassword}
                          onChange={(e) => setEditManualPassword(e.target.value)}
                          placeholder="Escribe la nueva contraseña segura..."
                        />
                        <button
                          type="button"
                          className={styles.eyeIconButton}
                          onClick={() => setShowEditPassword(!showEditPassword)}
                          aria-label={showEditPassword ? "Ocultar" : "Mostrar"}
                        >
                          {showEditPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                        </button>
                      </div>
                    </div>

                    <div className={styles.switchRow}>
                      <div className={styles.switchLabel}>
                        <span className={styles.switchTitle}>Enviar nueva clave al correo del alumno</span>
                        <span className={styles.switchDesc}>
                          Utiliza la plantilla de correo de Marketing (admin_reset_password) para notificar al estudiante.
                        </span>
                      </div>
                      <Switch checked={editSendEmail} onCheckedChange={setEditSendEmail} />
                    </div>
                  </div>
                )}

                {editPasswordMode === "auto" && (
                  <div className={styles.noticeBanner}>
                    <Sparkles size={18} className={styles.noticeBannerIcon} />
                    <div>
                      <strong>Generación & Envío Automático:</strong>
                      <br />
                      Al presionar <em>Guardar Cambios</em>, el sistema generará una contraseña segura aleatoria de 12 caracteres y se la enviará automáticamente al correo <strong>{editEmail || "del alumno"}</strong> utilizando la plantilla de correo <em>Reset por Admin (admin_reset_password)</em> del módulo de Marketing.
                    </div>
                  </div>
                )}
              </div>
            </>
          )}

          {manageTab === "courses" && (
            <div className={styles.courseAccessList}>
              <p className={styles.switchDesc}>
                Activa o desactiva qué formaciones están habilitadas para este estudiante de manera inmediata:
              </p>
              {courses.map((course) => {
                const isEnabled = editCourses.includes(course.id);
                return (
                  <div
                    key={course.id}
                    className={[styles.courseAccessRow, isEnabled ? styles.courseRowActive : ""].join(" ")}
                    onClick={() => handleToggleEditCourse(course.id)}
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
                        onCheckedChange={() => handleToggleEditCourse(course.id)}
                        aria-label={`Acceso a ${course.title}`}
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </Modal>

      {/* MODAL CREAR NUEVO ALUMNO */}
      <Modal
        isOpen={isCreateOpen}
        onClose={() => setIsCreateOpen(false)}
        title="Crear Nuevo Alumno"
        description="Registra un nuevo estudiante en la plataforma, define sus credenciales y asígnale cursos."
        footer={
          <>
            {createFeedback && (
              <div
                className={[
                  styles.feedbackBox,
                  createFeedback.type === "success" ? styles.feedbackSuccess : styles.feedbackError,
                ].join(" ")}
              >
                {createFeedback.type === "success" ? <Check size={14} /> : <AlertCircle size={14} />}
                <span>{createFeedback.text}</span>
              </div>
            )}
            <Button variant="ghost" onClick={() => setIsCreateOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" loading={isCreating} onClick={handleCreateStudent}>
              Crear Alumno
            </Button>
          </>
        }
      >
        <div className={styles.modalBody}>
          <div className={styles.modalTabs}>
            <SegmentedControl<"info" | "courses">
              options={[
                { value: "info", label: "Datos & Credenciales", icon: <User size={14} /> },
                {
                  value: "courses",
                  label: `Cursos Iniciales (${newCourses.length})`,
                  icon: <GraduationCap size={14} />,
                },
              ]}
              value={createTab}
              onChange={setCreateTab}
              size="sm"
            />
          </div>

          {createTab === "info" && (
            <>
              <div className={styles.formGrid}>
                <div className={styles.formGroup}>
                  <label className={styles.label}>Nombre Completo *</label>
                  <Input
                    value={newName}
                    onChange={(e) => setNewName(e.target.value)}
                    placeholder="Ej. Valeria Gómez"
                  />
                </div>

                <div className={styles.formGroup}>
                  <label className={styles.label}>Correo Electrónico *</label>
                  <Input
                    type="email"
                    value={newEmail}
                    onChange={(e) => setNewEmail(e.target.value)}
                    placeholder="valeria@ejemplo.com"
                  />
                </div>

                <div className={styles.formGridFull}>
                  <div className={styles.formGroup}>
                    <label className={styles.label}>Estado Inicial</label>
                    <SegmentedControl<"active" | "inactive" | "blocked">
                      options={MANAGE_STATUS_OPTIONS}
                      value={newStatus}
                      onChange={setNewStatus}
                      size="sm"
                    />
                  </div>
                </div>
              </div>

              <div className={styles.sectionCard}>
                <div className={styles.sectionHeader}>
                  <h4 className={styles.sectionTitle}>Credenciales de Acceso</h4>
                  <p className={styles.sectionSubtitle}>
                    Configura cómo se generará la contraseña para la cuenta del nuevo estudiante.
                  </p>
                </div>

                <SegmentedControl<"auto" | "manual">
                  options={CREATE_PWD_MODE_OPTIONS}
                  value={newPasswordMode}
                  onChange={setNewPasswordMode}
                  size="sm"
                />

                {newPasswordMode === "auto" && (
                  <div className={styles.noticeBanner}>
                    <Sparkles size={18} className={styles.noticeBannerIcon} />
                    <div>
                      <strong>Envío de Bienvenida Automático:</strong>
                      <br />
                      Se generará una contraseña segura aleatoria y se enviará automáticamente al alumno el correo de bienvenida <em>(student_welcome)</em> del módulo de Marketing con su usuario, contraseña generada y cursos asignados.
                    </div>
                  </div>
                )}

                {newPasswordMode === "manual" && (
                  <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                    <div className={styles.formGroup}>
                      <label className={styles.label}>Contraseña Inicial *</label>
                      <div className={styles.passwordInputWrapper}>
                        <Input
                          type={showNewPassword ? "text" : "password"}
                          value={newPassword}
                          onChange={(e) => setNewPassword(e.target.value)}
                          placeholder="Ingresa una contraseña..."
                        />
                        <button
                          type="button"
                          className={styles.eyeIconButton}
                          onClick={() => setShowNewPassword(!showNewPassword)}
                          aria-label={showNewPassword ? "Ocultar" : "Mostrar"}
                        >
                          {showNewPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                        </button>
                      </div>
                    </div>

                    <div className={styles.switchRow}>
                      <div className={styles.switchLabel}>
                        <span className={styles.switchTitle}>Enviar correo de bienvenida</span>
                        <span className={styles.switchDesc}>
                          Envía el correo <em>student_welcome</em> con estas credenciales y enlaces al alumno.
                        </span>
                      </div>
                      <Switch checked={newSendWelcome} onCheckedChange={setNewSendWelcome} />
                    </div>
                  </div>
                )}
              </div>
            </>
          )}

          {createTab === "courses" && (
            <div className={styles.courseAccessList}>
              <p className={styles.switchDesc}>
                Habilita los cursos a los que este alumno tendrá acceso desde el momento de su registro:
              </p>
              {courses.map((course) => {
                const isEnabled = newCourses.includes(course.id);
                return (
                  <div
                    key={course.id}
                    className={[styles.courseAccessRow, isEnabled ? styles.courseRowActive : ""].join(" ")}
                    onClick={() => handleToggleNewCourse(course.id)}
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
                        onCheckedChange={() => handleToggleNewCourse(course.id)}
                        aria-label={`Acceso a ${course.title}`}
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </Modal>
    </div>
  );
}
