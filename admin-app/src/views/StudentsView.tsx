import { useState, useEffect, useMemo } from "react";
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
  CheckCircle2,
  Clock,
  Ban,
  DollarSign,
  CreditCard,
  Receipt,
  ShoppingCart
} from "lucide-react";
import { FinancePlan } from "../services/api";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Switch } from "../components/arc/switch/switch";
import { Input } from "../components/arc/input/input";
import { Select } from "../components/arc/select";
import { SegmentedControl, SegmentOption } from "../components/arc/segmented-control/segmented-control";
import { api, Student, Course } from "../services/api";
import { ModuleSkeleton } from "../components/arc/skeleton";
import styles from "./StudentsView.module.css";

const STATUS_ITEMS = [
  { value: "active" as const, label: "Activo", icon: <CheckCircle2 size={15} /> },
  { value: "inactive" as const, label: "Inactivo", icon: <Clock size={15} /> },
  { value: "blocked" as const, label: "Bloqueado", icon: <Ban size={15} /> },
];

const SALES_CHANNEL_OPTIONS = [
  { value: "transfer", label: "Transferencia Bancaria" },
  { value: "zelle", label: "Zelle" },
  { value: "pago_movil", label: "Pago Móvil" },
  { value: "cash", label: "Efectivo / En persona" },
  { value: "whatsapp", label: "WhatsApp / Venta Directa" },
  { value: "hotmart", label: "Hotmart" },
  { value: "stripe", label: "Stripe / Tarjeta" },
  { value: "other", label: "Otro Canal" },
];

const CREATE_STATUS_ITEMS = [
  { value: "active" as const, label: "Activo", icon: <CheckCircle2 size={15} /> },
  { value: "inactive" as const, label: "Inactivo", icon: <Clock size={15} /> },
];

const PWD_MODE_OPTIONS: SegmentOption<"manual" | "auto">[] = [
  { value: "manual", label: "Cambiar Manualmente", icon: <Key size={14} /> },
  { value: "auto", label: "Cambiar Automáticamente", icon: <Sparkles size={14} /> },
];

const CREATE_PWD_MODE_OPTIONS: SegmentOption<"auto" | "manual">[] = [
  { value: "auto", label: "Generar y enviar bienvenida" },
  { value: "manual", label: "Definir manualmente" },
];

interface StudentsViewProps {
  isInstructor?: boolean;
}

export function StudentsView({ isInstructor = false }: StudentsViewProps = {}) {
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
  const [editPasswordMode, setEditPasswordMode] = useState<"manual" | "auto">("manual");
  const [editManualPassword, setEditManualPassword] = useState("");
  const [editSendEmail, setEditSendEmail] = useState(true);
  const [showEditPassword, setShowEditPassword] = useState(false);
  const [editCourses, setEditCourses] = useState<number[]>([]);
  const [isSaving, setIsSaving] = useState(false);
  const [isResettingAuto, setIsResettingAuto] = useState(false);
  const [autoResetSuccess, setAutoResetSuccess] = useState(false);
  const [manageFeedback, setManageFeedback] = useState<{ type: "success" | "error"; text: string } | null>(null);

  // Modal de Crear Nuevo Alumno
  const [isCreateOpen, setIsCreateOpen] = useState(false);
  const [createStep, setCreateStep] = useState<1 | 2 | 3>(1);
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
  const [isLoading, setIsLoading] = useState(true);

  // Estados de Venta & Finanzas para el registro manual
  const [plans, setPlans] = useState<FinancePlan[]>([]);
  const [selectedPlanId, setSelectedPlanId] = useState<number>(0);
  const [salesChannel, setSalesChannel] = useState<string>("transfer");
  const [saleAmount, setSaleAmount] = useState<string>("");
  const [saleReference, setSaleReference] = useState<string>("");
  const [recordSale, setRecordSale] = useState<boolean>(true);

  // Filtrar exclusivamente los cursos activos/publicados para que no salgan borradores
  const activeCourses = useMemo(() => {
    const published = courses.filter((c) => c.status === "publish");
    return published.length > 0 ? published : courses;
  }, [courses]);

  useEffect(() => {
    Promise.all([api.getStudents(), api.getCourses({ activeOnly: true }), api.getPlans()])
      .then(([studentsData, coursesData, plansData]) => {
        setStudents(studentsData);
        setCourses(coursesData);
        setPlans(plansData);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, []);

  const handlePlanChange = (planIdStr: string) => {
    const id = Number(planIdStr);
    setSelectedPlanId(id);
    const p = plans.find((item) => item.id === id);
    if (p) {
      if (p.totalAmount || p.quotaAmount) {
        setSaleAmount(String(p.totalAmount || p.quotaAmount));
      }
      if (p.courseId && activeCourses.some((c) => c.id === Number(p.courseId))) {
        setNewCourses((prev) => Array.from(new Set([...prev, Number(p.courseId)])));
      }
    }
  };

  // Abrir Modal de Gestión
  const openManageModal = (student: Student) => {
    setSelectedStudent(student);
    setManageTab("info");
    setEditName(student.name);
    setEditEmail(student.email);
    setEditStatus(student.status);
    setEditPasswordMode("manual");
    setEditManualPassword("");
    setEditSendEmail(true);
    setShowEditPassword(false);
    setIsResettingAuto(false);
    setAutoResetSuccess(false);
    const validCourseIds = (student.enabledCourseIds || []).filter((id) =>
      activeCourses.some((c) => c.id === id)
    );
    setEditCourses(validCourseIds);
    setManageFeedback(null);
  };

  const handleToggleEditCourse = (courseId: number) => {
    if (editCourses.includes(courseId)) {
      setEditCourses(editCourses.filter((id) => id !== courseId));
    } else {
      setEditCourses([...editCourses, courseId]);
    }
  };

  // Botón interior para generar y enviar contraseña automáticamente
  const handleTriggerAutoPasswordReset = async () => {
    if (!selectedStudent) return;
    setIsResettingAuto(true);
    setAutoResetSuccess(false);
    setManageFeedback(null);

    const res = await api.updateStudent(selectedStudent.id, {
      name: editName.trim(),
      email: editEmail.trim(),
      status: editStatus,
      passwordMode: "auto",
      enabledCourseIds: editCourses,
    });

    setIsResettingAuto(false);

    if (res.success) {
      setAutoResetSuccess(true);
      if (res.student) {
        setStudents((prev) => prev.map((s) => (s.id === selectedStudent.id ? res.student! : s)));
        setSelectedStudent(res.student);
      }
      setManageFeedback({
        type: "success",
        text: `Contraseña generada y enviada a ${editEmail || selectedStudent.email} con éxito.`,
      });
      setTimeout(() => setAutoResetSuccess(false), 5000);
    } else {
      setManageFeedback({
        type: "error",
        text: res.message || "Error al generar y enviar contraseña.",
      });
    }
  };

  // Guardar cambios generales del estudiante
  const handleSaveStudent = async () => {
    if (!selectedStudent) return;
    if (!editName.trim() || !editEmail.trim()) {
      setManageFeedback({ type: "error", text: "El nombre y el correo no pueden estar vacíos." });
      return;
    }

    let pwdMode: "keep" | "manual" | "auto" = "keep";
    if (editPasswordMode === "manual") {
      if (editManualPassword.trim().length > 0) {
        pwdMode = "manual";
      } else {
        pwdMode = "keep";
      }
    } else if (editPasswordMode === "auto") {
      pwdMode = autoResetSuccess ? "keep" : "auto";
    }

    setIsSaving(true);
    setManageFeedback(null);

    const res = await api.updateStudent(selectedStudent.id, {
      name: editName.trim(),
      email: editEmail.trim(),
      status: editStatus,
      passwordMode: pwdMode,
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
    setNewCourses(activeCourses.length > 0 ? [activeCourses[0].id] : []);
    setSelectedPlanId(0);
    setSalesChannel("transfer");
    setSaleAmount("");
    setSaleReference("");
    setRecordSale(true);
    setCreateFeedback(null);
    setCreateStep(1);
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

    const selectedPlan = plans.find((p) => p.id === selectedPlanId);
    const res = await api.createStudent({
      name: newName.trim(),
      email: newEmail.trim(),
      status: newStatus,
      passwordMode: newPasswordMode,
      password: newPassword,
      sendWelcomeEmail: newSendWelcome,
      enabledCourseIds: newCourses,
      planId: selectedPlanId > 0 ? selectedPlanId : undefined,
      planName: selectedPlan ? selectedPlan.name : "Acceso Manual",
      salesChannel: salesChannel,
      paymentMethod: salesChannel,
      saleAmount: saleAmount ? parseFloat(saleAmount) : 0,
      saleReference: saleReference.trim(),
      recordSale: recordSale,
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

  if (isLoading) {
    return <ModuleSkeleton type="students" />;
  }

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

          <div className={styles.filterWrap}>
            <Select<string>
              size="sm"
              value={filterStatus}
              onChange={(val) => setFilterStatus(val)}
              options={[
                { value: "all", label: "Todos los estados", icon: <Filter size={14} /> },
                { value: "active", label: "Activos", icon: <CheckCircle2 size={14} /> },
                { value: "inactive", label: "Inactivos", icon: <Clock size={14} /> },
                { value: "blocked", label: "Bloqueados", icon: <Ban size={14} /> },
              ]}
            />
          </div>

          {!isInstructor && (
            <Button variant="primary" size="sm" onClick={openCreateModal}>
              <Plus size={15} />
              Nuevo Alumno
            </Button>
          )}
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
              const enabledCount = (student.enabledCourseIds || []).filter((id) =>
                activeCourses.some((c) => c.id === id)
              ).length;
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
                  label: `Cursos Habilitados (${activeCourses.filter((c) => editCourses.includes(c.id)).length})`,
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

                {/* EXPANDING BUTTON GROUP PARA ESTADO DE LA CUENTA */}
                <div className={styles.formGridFull}>
                  <div className={styles.formGroup}>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "2px" }}>
                      <label className={styles.label}>Estado de la Cuenta</label>
                      <span style={{ fontSize: "11px", color: "var(--text-muted)" }}>
                        Actualmente:{" "}
                        <strong
                          style={{
                            color:
                              editStatus === "active"
                                ? "#4ade80"
                                : editStatus === "inactive"
                                ? "#fbbf24"
                                : "#f87171",
                          }}
                        >
                          {editStatus === "active"
                            ? "Activo"
                            : editStatus === "inactive"
                            ? "Inactivo"
                            : "Bloqueado"}
                        </strong>
                      </span>
                    </div>

                    <div className={styles.expandingButtonGroup} role="radiogroup" aria-label="Estado de la cuenta">
                      {STATUS_ITEMS.map((item) => {
                        const isSelected = editStatus === item.value;
                        return (
                          <button
                            key={item.value}
                            type="button"
                            role="radio"
                            aria-checked={isSelected}
                            className={[
                              styles.expandingBtn,
                              isSelected ? styles.expandingBtnActive : "",
                              isSelected ? styles[`expandingBtnActive_${item.value}`] : "",
                            ]
                              .filter(Boolean)
                              .join(" ")}
                            onClick={() => setEditStatus(item.value)}
                          >
                            {item.icon}
                            <span>{item.label}</span>
                            {isSelected && <span className={styles.activeDot} />}
                          </button>
                        );
                      })}
                    </div>
                  </div>
                </div>
              </div>

              {/* Gestión de Contraseña */}
              <div className={styles.sectionCard}>
                <div className={styles.sectionHeader}>
                  <h4 className={styles.sectionTitle}>Seguridad & Contraseña</h4>
                  <p className={styles.sectionSubtitle}>
                    Modifica la contraseña manualmente o genérala automáticamente y envíala al correo del alumno.
                  </p>
                </div>

                <SegmentedControl<"manual" | "auto">
                  options={PWD_MODE_OPTIONS}
                  value={editPasswordMode}
                  onChange={setEditPasswordMode}
                  size="sm"
                />

                {editPasswordMode === "manual" && (
                  <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                    <div className={styles.formGroup}>
                      <label className={styles.label}>Nueva Contraseña</label>
                      <div className={styles.passwordInputWrapper}>
                        <Input
                          type={showEditPassword ? "text" : "password"}
                          value={editManualPassword}
                          onChange={(e) => setEditManualPassword(e.target.value)}
                          placeholder="Escribe la nueva contraseña (deja vacío para no cambiarla)..."
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
                      <span className={styles.switchDesc}>
                        Deja este campo en blanco si no deseas modificar la contraseña actual del estudiante.
                      </span>
                    </div>

                    {editManualPassword.trim().length > 0 && (
                      <div className={styles.switchRow}>
                        <div className={styles.switchLabel}>
                          <span className={styles.switchTitle}>Enviar nueva clave al correo del alumno</span>
                          <span className={styles.switchDesc}>
                            Utiliza la plantilla de correo de Marketing (admin_reset_password) para notificar al estudiante.
                          </span>
                        </div>
                        <Switch checked={editSendEmail} onCheckedChange={setEditSendEmail} />
                      </div>
                    )}
                  </div>
                )}

                {editPasswordMode === "auto" && (
                  <div className={styles.autoPasswordContainer}>
                    <div className={styles.noticeBanner}>
                      <Sparkles size={18} className={styles.noticeBannerIcon} />
                      <div>
                        <strong>Generación & Envío Automático:</strong>
                        <br />
                        El sistema generará una contraseña segura aleatoria de 12 caracteres y se la enviará de inmediato al correo <strong>{editEmail || selectedStudent.email}</strong> utilizando la plantilla de correo <em>Reset por Admin (admin_reset_password)</em> del módulo de Marketing.
                      </div>
                    </div>

                    <div className={styles.autoActionRow}>
                      <Button
                        type="button"
                        variant="primary"
                        size="sm"
                        loading={isResettingAuto}
                        onClick={handleTriggerAutoPasswordReset}
                      >
                        <Mail size={15} />
                        Generar y Enviar Nueva Contraseña
                      </Button>

                      {autoResetSuccess && (
                        <span className={styles.successMessage}>
                          <Check size={14} /> Contraseña enviada con éxito a {editEmail || selectedStudent.email}
                        </span>
                      )}
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
              {activeCourses.map((course) => {
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
                        {course.sections?.length || 0} Módulos estructurados
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
      {/* =========================================================
          MODAL DE CREAR NUEVO ALUMNO (WIZARD POR PASOS)
          ========================================================= */}
      <Modal
        isOpen={isCreateOpen}
        onClose={() => setIsCreateOpen(false)}
        title="Registrar Nuevo Estudiante"
        description="Completa el proceso guiado en 3 pasos para registrar al alumno, asentar su venta y habilitar sus accesos."
        footer={
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", width: "100%", gap: "12px", flexWrap: "wrap" }}>
            <div style={{ flex: 1, minWidth: "220px" }}>
              {createFeedback && (
                <div
                  className={[
                    styles.feedbackBox,
                    createFeedback.type === "success" ? styles.feedbackSuccess : styles.feedbackError,
                  ].join(" ")}
                  style={{ margin: 0 }}
                >
                  {createFeedback.type === "success" ? <Check size={14} /> : <AlertCircle size={14} />}
                  <span>{createFeedback.text}</span>
                </div>
              )}
            </div>

            <div style={{ display: "flex", alignItems: "center", gap: "8px", flexShrink: 0 }}>
              {createStep === 1 && (
                <>
                  <Button variant="ghost" onClick={() => setIsCreateOpen(false)}>
                    Cancelar
                  </Button>
                  <Button
                    variant="primary"
                    onClick={() => {
                      if (!newName.trim() || !newEmail.trim()) {
                        setCreateFeedback({
                          type: "error",
                          text: "Por favor ingresa el nombre y correo del estudiante para continuar.",
                        });
                        return;
                      }
                      if (newPasswordMode === "manual" && !newPassword.trim()) {
                        setCreateFeedback({
                          type: "error",
                          text: "Por favor define una contraseña para el estudiante o activa generación automática.",
                        });
                        return;
                      }
                      setCreateFeedback(null);
                      setCreateStep(2);
                    }}
                  >
                    Siguiente: Registrar Venta →
                  </Button>
                </>
              )}

              {createStep === 2 && (
                <>
                  <Button
                    variant="secondary"
                    onClick={() => {
                      setCreateFeedback(null);
                      setCreateStep(1);
                    }}
                  >
                    ← Volver a Datos
                  </Button>
                  <Button
                    variant="primary"
                    onClick={() => {
                      setCreateFeedback(null);
                      setCreateStep(3);
                    }}
                  >
                    Siguiente: Habilitar Cursos →
                  </Button>
                </>
              )}

              {createStep === 3 && (
                <>
                  <Button
                    variant="secondary"
                    onClick={() => {
                      setCreateFeedback(null);
                      setCreateStep(2);
                    }}
                  >
                    ← Volver a Venta
                  </Button>
                  <Button variant="primary" loading={isCreating} onClick={handleCreateStudent}>
                    <CheckCircle2 size={16} />
                    Crear y Matricular Estudiante
                  </Button>
                </>
              )}
            </div>
          </div>
        }
      >
        <div className={styles.modalBody}>
          {/* ENCABEZADO DEL WIZARD / INDICADOR DE PASOS */}
          <div className={styles.wizardStepsHeader}>
            <div
              className={[
                styles.wizardStepItem,
                createStep === 1 ? styles.wizardStepActive : "",
                createStep > 1 ? styles.wizardStepDone : "",
              ].join(" ")}
              onClick={() => {
                setCreateFeedback(null);
                setCreateStep(1);
              }}
            >
              <div className={styles.wizardStepBadge}>
                {createStep > 1 ? <Check size={13} /> : "1"}
              </div>
              <div className={styles.wizardStepContent}>
                <span className={styles.wizardStepLabel}>Paso 1</span>
                <span className={styles.wizardStepName}>Datos & Clave</span>
              </div>
            </div>

            <div
              className={[
                styles.wizardStepDivider,
                createStep >= 2 ? styles.wizardStepDividerActive : "",
              ].join(" ")}
            />

            <div
              className={[
                styles.wizardStepItem,
                createStep === 2 ? styles.wizardStepActive : "",
                createStep > 2 ? styles.wizardStepDone : "",
              ].join(" ")}
              onClick={() => {
                if (!newName.trim() || !newEmail.trim()) {
                  setCreateFeedback({
                    type: "error",
                    text: "Completa el nombre y correo en el Paso 1 primero.",
                  });
                  return;
                }
                setCreateFeedback(null);
                setCreateStep(2);
              }}
            >
              <div className={styles.wizardStepBadge}>
                {createStep > 2 ? <Check size={13} /> : "2"}
              </div>
              <div className={styles.wizardStepContent}>
                <span className={styles.wizardStepLabel}>Paso 2</span>
                <span className={styles.wizardStepName}>Venta & Finanzas</span>
              </div>
            </div>

            <div
              className={[
                styles.wizardStepDivider,
                createStep >= 3 ? styles.wizardStepDividerActive : "",
              ].join(" ")}
            />

            <div
              className={[
                styles.wizardStepItem,
                createStep === 3 ? styles.wizardStepActive : "",
              ].join(" ")}
              onClick={() => {
                if (!newName.trim() || !newEmail.trim()) {
                  setCreateFeedback({
                    type: "error",
                    text: "Completa el nombre y correo en el Paso 1 primero.",
                  });
                  return;
                }
                setCreateFeedback(null);
                setCreateStep(3);
              }}
            >
              <div className={styles.wizardStepBadge}>
                {createStep === 3 ? <GraduationCap size={13} /> : "3"}
              </div>
              <div className={styles.wizardStepContent}>
                <span className={styles.wizardStepLabel}>Paso 3</span>
                <span className={styles.wizardStepName}>
                  Cursos ({activeCourses.filter((c) => newCourses.includes(c.id)).length})
                </span>
              </div>
            </div>
          </div>

          {/* PASO 1: DATOS & CREDENCIALES */}
          {createStep === 1 && (
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
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "2px" }}>
                      <label className={styles.label}>Estado Inicial</label>
                      <span style={{ fontSize: "11px", color: "var(--text-muted)" }}>
                        Actualmente:{" "}
                        <strong
                          style={{
                            color: newStatus === "active" ? "#4ade80" : "#fbbf24",
                          }}
                        >
                          {newStatus === "active" ? "Activo" : "Inactivo"}
                        </strong>
                      </span>
                    </div>

                    <div className={styles.expandingButtonGroup} role="radiogroup" aria-label="Estado inicial">
                      {CREATE_STATUS_ITEMS.map((item) => {
                        const isSelected = newStatus === item.value;
                        return (
                          <button
                            key={item.value}
                            type="button"
                            role="radio"
                            aria-checked={isSelected}
                            className={[
                              styles.expandingBtn,
                              isSelected ? styles.expandingBtnActive : "",
                              isSelected ? styles[`expandingBtnActive_${item.value}`] : "",
                            ]
                              .filter(Boolean)
                              .join(" ")}
                            onClick={() => setNewStatus(item.value)}
                          >
                            {item.icon}
                            <span>{item.label}</span>
                            {isSelected && <span className={styles.activeDot} />}
                          </button>
                        );
                      })}
                    </div>
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

          {/* PASO 2: VENTA & FINANZAS */}
          {createStep === 2 && (
            <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
              <div className={styles.sectionCard}>
                <div className={styles.sectionHeader}>
                  <h4 className={styles.sectionTitle}>Registro de Venta en Finanzas</h4>
                  <p className={styles.sectionSubtitle}>
                    Como esta es una venta manual, registra por dónde compró el alumno para que se contabilice en tus ingresos y reportes.
                  </p>
                </div>

                <div className={styles.switchRow} style={{ marginTop: "var(--space-2)", marginBottom: "var(--space-2)" }}>
                  <div className={styles.switchLabel}>
                    <span className={styles.switchTitle}>Contabilizar como venta en Finanzas</span>
                    <span className={styles.switchDesc}>
                      Registra de inmediato esta transacción como cobro exitoso (paid) en el historial de finanzas.
                    </span>
                  </div>
                  <Switch checked={recordSale} onCheckedChange={setRecordSale} />
                </div>

                {recordSale ? (
                  <div className={styles.formGrid} style={{ marginTop: "var(--space-3)" }}>
                    <div className={styles.formGroup}>
                      <label className={styles.label}>¿Por dónde compró? (Canal de Venta) *</label>
                      <select
                        value={salesChannel}
                        onChange={(e) => setSalesChannel(e.target.value)}
                        style={{
                          width: "100%",
                          height: "38px",
                          borderRadius: "8px",
                          padding: "0 10px",
                          background: "var(--surface)",
                          border: "1px solid var(--border)",
                          color: "var(--foreground)",
                          fontSize: "13px",
                          outline: "none"
                        }}
                      >
                        {SALES_CHANNEL_OPTIONS.map((ch) => (
                          <option key={ch.value} value={ch.value}>
                            {ch.label}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div className={styles.formGroup}>
                      <label className={styles.label}>Plan o Membresía Vinculada</label>
                      <select
                        value={selectedPlanId}
                        onChange={(e) => handlePlanChange(e.target.value)}
                        style={{
                          width: "100%",
                          height: "38px",
                          borderRadius: "8px",
                          padding: "0 10px",
                          background: "var(--surface)",
                          border: "1px solid var(--border)",
                          color: "var(--foreground)",
                          fontSize: "13px",
                          outline: "none"
                        }}
                      >
                        <option value={0}>Venta Directa / Sin Plan Específico</option>
                        {plans.map((pl) => (
                          <option key={pl.id} value={pl.id}>
                            {`${pl.name} ($${pl.totalAmount || pl.quotaAmount || 0})`}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div className={styles.formGroup}>
                      <label className={styles.label}>Monto de la Venta ($ USD) *</label>
                      <Input
                        type="number"
                        step="0.01"
                        value={saleAmount}
                        onChange={(e) => setSaleAmount(e.target.value)}
                        placeholder="Ej. 197.00"
                      />
                    </div>

                    <div className={styles.formGroup}>
                      <label className={styles.label}>Referencia / Comprobante (Opcional)</label>
                      <Input
                        value={saleReference}
                        onChange={(e) => setSaleReference(e.target.value)}
                        placeholder="Ej. Ref #984218, Comprobante Zelle..."
                      />
                    </div>
                  </div>
                ) : (
                  <div className={styles.noticeBanner} style={{ marginTop: "var(--space-3)" }}>
                    <AlertCircle size={18} className={styles.noticeBannerIcon} />
                    <div>
                      <strong>Acceso de Cortesía / Sin Venta:</strong>
                      <br />
                      El estudiante se registrará y matriculará sin generar ninguna transacción en el módulo de Finanzas.
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* PASO 3: CURSOS HABILITADOS & CONFIRMACIÓN */}
          {createStep === 3 && (
            <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
              <div className={styles.courseAccessList}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "8px" }}>
                  <p className={styles.switchDesc} style={{ margin: 0 }}>
                    Habilita las formaciones a las que este alumno tendrá acceso inmediato:
                  </p>
                  <Badge variant={activeCourses.filter((c) => newCourses.includes(c.id)).length > 0 ? "accent" : "neutral"} size="sm">
                    <GraduationCap size={13} />
                    {activeCourses.filter((c) => newCourses.includes(c.id)).length} de {activeCourses.length} seleccionados
                  </Badge>
                </div>

                {activeCourses.length === 0 ? (
                  <div className={styles.emptyState} style={{ padding: "20px" }}>
                    <GraduationCap size={30} className={styles.emptyIcon} />
                    <p>No hay cursos publicados actualmente en la academia.</p>
                  </div>
                ) : (
                  activeCourses.map((course) => {
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
                            {course.sections?.length || 0} Módulos estructurados
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
                  })
                )}
              </div>

              {/* TARJETA DE RESUMEN FINAL ANTES DE CONFIRMAR */}
              <div className={styles.wizardSummaryCard}>
                <h4 className={styles.wizardSummaryTitle}>
                  <CheckCircle2 size={15} style={{ color: "var(--accent)" }} />
                  Resumen de la Nueva Matrícula
                </h4>
                <div className={styles.wizardSummaryGrid}>
                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Estudiante</span>
                    <span className={styles.wizardSummaryVal}>
                      {newName || "Sin nombre"} ({newEmail || "Sin correo"})
                    </span>
                  </div>
                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Estado de la Cuenta</span>
                    <span className={styles.wizardSummaryVal}>
                      {newStatus === "active" ? "Activo" : "Inactivo"}
                    </span>
                  </div>
                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Registro en Finanzas</span>
                    <span className={styles.wizardSummaryVal}>
                      {recordSale
                        ? `${SALES_CHANNEL_OPTIONS.find((o) => o.value === salesChannel)?.label || salesChannel} · $${saleAmount || "0"} USD`
                        : "Acceso de cortesía (Sin cobro)"}
                    </span>
                  </div>
                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Cursos Asignados</span>
                    <span className={styles.wizardSummaryVal} style={{ color: "var(--accent)" }}>
                      {activeCourses.filter((c) => newCourses.includes(c.id)).length === 0
                        ? "Ningún curso seleccionado"
                        : `${activeCourses.filter((c) => newCourses.includes(c.id)).length} curso(s) activo(s)`}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </Modal>
    </div>
  );
}
