import { useState, useEffect, useMemo } from "react";
import {
  User,
  Users,
  Search,
  Plus,
  ShieldCheck,
  Mail,
  BookOpen,
  Trash2,
  Edit3,
  MessageSquare,
  Award,
  Sparkles,
  Key,
  CheckCircle2,
  AlertTriangle,
  UserCheck,
  Check,
  Copy,
  RefreshCw,
  Eye,
  EyeOff,
  KeyRound,
  ArrowLeft,
  ArrowRight,
  GraduationCap,
  Layers,
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { FileDropzone } from "../components/arc/file-dropzone/file-dropzone";
import { Alert } from "../components/arc/alert/alert";
import { ModuleSkeleton } from "../components/arc/skeleton";
import { SegmentedControl } from "../components/arc/segmented-control/segmented-control";
import { api, Instructor, Course, CommunityChannel } from "../services/api";
import styles from "./InstructorsView.module.css";

const generateRandomPassword = () => {
  const chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%&*";
  let pwd = "";
  for (let i = 0; i < 12; i++) {
    pwd += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return pwd;
};

interface ToastState {
  id: number;
  tone: "info" | "success" | "warning" | "danger";
  title: string;
  description?: string;
}

export function InstructorsView() {
  const [instructors, setInstructors] = useState<Instructor[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [channels, setChannels] = useState<CommunityChannel[]>([]);
  const [currentAdmin, setCurrentAdmin] = useState<{ id: number; name: string; email: string; avatar: string; is_already_instructor: boolean } | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Filtros y búsqueda
  const [searchQuery, setSearchQuery] = useState("");
  const [filterMode, setFilterMode] = useState<"all" | "courses" | "community" | "admin">("all");

  // Alerta tipo Toast
  const [toast, setToast] = useState<ToastState | null>(null);
  const showToast = (tone: "info" | "success" | "warning" | "danger", title: string, description?: string) => {
    setToast({ id: Date.now(), tone, title, description });
  };

  useEffect(() => {
    if (!toast) return;
    const timer = setTimeout(() => setToast(null), 4000);
    return () => clearTimeout(timer);
  }, [toast]);

  // Modal Wizard de Crear / Editar Docente
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [wizardStep, setWizardStep] = useState<1 | 2 | 3>(1);
  const [editingInstructor, setEditingInstructor] = useState<Instructor | null>(null);
  const [editTab, setEditTab] = useState<"info" | "courses" | "community">("info");
  const [editChangePassword, setEditChangePassword] = useState(false);

  // Campos de formulario
  const [formName, setFormName] = useState("");
  const [formEmail, setFormEmail] = useState("");
  const [formRole, setFormRole] = useState("");
  const [formAvatar, setFormAvatar] = useState("");
  const [formBio, setFormBio] = useState("");
  const [formCreateWpUser, setFormCreateWpUser] = useState(true);
  const [formPasswordMode, setFormPasswordMode] = useState<"auto" | "manual">("auto");
  const [formPassword, setFormPassword] = useState("");
  const [formSendWelcomeEmail, setFormSendWelcomeEmail] = useState(true);
  const [showPassword, setShowPassword] = useState(false);
  const [isPasswordCopied, setIsPasswordCopied] = useState(false);
  const [formAllowedChannels, setFormAllowedChannels] = useState<number[]>([]);
  const [formAssignedCourses, setFormAssignedCourses] = useState<number[]>([]);

  // Filtrar exclusivamente los cursos activos/publicados para que no salgan borradores
  const activeCourses = useMemo(() => {
    const published = courses.filter((c) => c.status === "publish");
    return published.length > 0 ? published : courses;
  }, [courses]);
  const [isSaving, setIsSaving] = useState(false);
  const [isUploadingAvatar, setIsUploadingAvatar] = useState(false);

  // Modal de confirmación para eliminar
  const [deleteConfirm, setDeleteConfirm] = useState<{ id: string; name: string } | null>(null);

  const loadData = async () => {
    try {
      setIsLoading(true);
      const [instData, coursesData, channelsData] = await Promise.all([
        api.getInstructors(),
        api.getCourses(),
        api.getCommunityChannels(),
      ]);
      setInstructors(instData.instructors || []);
      setCurrentAdmin(instData.current_admin || null);
      setCourses(coursesData || []);
      setChannels(channelsData || []);
    } catch (e) {
      console.warn("Error cargando docentes:", e);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  // Sincronizar o Asignar al Admin actual como docente
  const handleSyncAdmin = async () => {
    try {
      const res = await api.syncAdminAsInstructor();
      if (res.success && res.instructor) {
        showToast("success", "Administrador Asignado", "Tu usuario administrador ahora está registrado como docente.");
        await loadData();
      } else {
        showToast("danger", "Error", res.message || "No se pudo asignar al administrador.");
      }
    } catch {
      showToast("danger", "Error", "Ocurrió un error al sincronizar al administrador.");
    }
  };

  // Abrir Modal para Nuevo Docente
  const openCreateModal = () => {
    setEditingInstructor(null);
    setWizardStep(1);
    setFormName("");
    setFormEmail("");
    setFormRole("Docente Titular");
    setFormAvatar("");
    setFormBio("");
    setFormCreateWpUser(true);
    setFormPasswordMode("auto");
    setFormPassword(generateRandomPassword());
    setFormSendWelcomeEmail(true);
    setShowPassword(false);
    setIsPasswordCopied(false);
    setFormAllowedChannels([]);
    setFormAssignedCourses([]);
    setIsModalOpen(true);
  };

  // Abrir Modal para Editar Docente
  const openEditModal = (inst: Instructor) => {
    setEditingInstructor(inst);
    setEditTab("info");
    setWizardStep(1);
    setFormName(inst.name || "");
    setFormEmail(inst.email || "");
    setFormRole(inst.role || "Docente");
    setFormAvatar(inst.avatar || "");
    setFormBio(inst.bio || "");
    setFormCreateWpUser(Boolean(inst.userId));
    setEditChangePassword(false);
    setFormPasswordMode("manual");
    setFormPassword("");
    setFormSendWelcomeEmail(false);
    setShowPassword(false);
    setIsPasswordCopied(false);
    setFormAllowedChannels(inst.allowedChannelIds || []);
    setFormAssignedCourses(
      inst.assigned_courses ? inst.assigned_courses.map((c) => c.id) : inst.assignedCourseIds || []
    );
    setIsModalOpen(true);
  };

  const copyPasswordToClipboard = () => {
    if (!formPassword) return;
    navigator.clipboard.writeText(formPassword);
    setIsPasswordCopied(true);
    showToast("info", "Contraseña Copiada", "La contraseña ha sido copiada al portapapeles.");
    setTimeout(() => setIsPasswordCopied(false), 2500);
  };

  const validateStep1 = () => {
    if (!formName.trim()) {
      showToast("warning", "Campo requerido", "Por favor ingresa el nombre del docente.");
      return false;
    }
    if (!formEmail.trim()) {
      showToast("warning", "Campo requerido", "Por favor ingresa el correo del docente.");
      return false;
    }
    // Si estamos editando y no se activó cambio de contraseña, no requerir clave
    if (editingInstructor && !editChangePassword) {
      return true;
    }
    if (formCreateWpUser && formPasswordMode === "manual" && !formPassword.trim()) {
      showToast("warning", "Contraseña requerida", "Define una contraseña manual o selecciona generarla automáticamente.");
      return false;
    }
    return true;
  };

  const handleNextStep = () => {
    if (wizardStep === 1) {
      if (validateStep1()) {
        setWizardStep(2);
      }
    } else if (wizardStep === 2) {
      setWizardStep(3);
    }
  };

  // Subida de Foto del Docente con File Upload
  const handleUploadAvatar = async (files: File[]) => {
    if (!files || files.length === 0) return;
    const file = files[0];
    const localUrl = URL.createObjectURL(file);
    setFormAvatar(localUrl);
    setIsUploadingAvatar(true);
    showToast("info", "Subiendo foto...", "Procesando la imagen del docente.");

    try {
      const uploadedUrl = await api.uploadMedia(file);
      if (uploadedUrl) {
        setFormAvatar(uploadedUrl);
        showToast("success", "Foto cargada", "La imagen se subió correctamente.");
      }
    } catch {
      showToast("danger", "Error", "No se pudo subir la foto del docente.");
    } finally {
      setIsUploadingAvatar(false);
    }
  };

  // Guardar Docente (Crear o Actualizar)
  const handleSaveInstructor = async () => {
    if (!formName.trim()) {
      showToast("warning", "Campo requerido", "Por favor ingresa el nombre del docente.");
      return;
    }

    setIsSaving(true);
    try {
      const shouldUpdatePassword = editingInstructor
        ? editChangePassword && Boolean(formPassword.trim())
        : formCreateWpUser && Boolean(formPassword.trim());

      const payload: Partial<Instructor> & { createWpUser?: boolean; password?: string; sendWelcomeEmail?: boolean } = {
        id: editingInstructor ? editingInstructor.id : undefined,
        name: formName.trim(),
        email: formEmail.trim(),
        role: formRole.trim() || "Docente",
        avatar: formAvatar.trim(),
        bio: formBio.trim(),
        allowedChannelIds: formAllowedChannels,
        assignedCourseIds: formAssignedCourses,
        createWpUser: formCreateWpUser,
        password: shouldUpdatePassword ? formPassword.trim() : undefined,
        sendWelcomeEmail: editingInstructor ? (editChangePassword && formSendWelcomeEmail) : formSendWelcomeEmail,
      };

      const res = await api.saveInstructor(payload);
      if (res.success) {
        showToast(
          "success",
          editingInstructor ? "Docente actualizado" : "Docente registrado",
          res.message || "Los datos del docente se guardaron correctamente."
        );
        setIsModalOpen(false);
        await loadData();
      } else {
        showToast("danger", "Error al guardar", res.message || "No se pudo guardar el docente.");
      }
    } catch {
      showToast("danger", "Error", "Ocurrió un error inesperado al guardar el docente.");
    } finally {
      setIsSaving(false);
    }
  };

  // Confirmar y Ejecutar Eliminación
  const executeDeleteInstructor = async () => {
    if (!deleteConfirm) return;
    try {
      const ok = await api.deleteInstructor(deleteConfirm.id);
      if (ok) {
        showToast("info", "Docente eliminado", `"${deleteConfirm.name}" ha sido desvinculado.`);
        setDeleteConfirm(null);
        await loadData();
      } else {
        showToast("danger", "Error", "No se pudo eliminar el docente.");
      }
    } catch {
      showToast("danger", "Error", "Ocurrió un error al eliminar el docente.");
    }
  };

  // Filtrado de docentes
  const filteredInstructors = instructors.filter((inst) => {
    const matchesSearch =
      (inst.name && inst.name.toLowerCase().includes(searchQuery.toLowerCase())) ||
      (inst.role && inst.role.toLowerCase().includes(searchQuery.toLowerCase())) ||
      (inst.email && inst.email.toLowerCase().includes(searchQuery.toLowerCase()));

    if (!matchesSearch) return false;

    if (filterMode === "courses") {
      return (inst.assigned_courses && inst.assigned_courses.length > 0) || (inst.assignedCourseIds && inst.assignedCourseIds.length > 0);
    }
    if (filterMode === "community") {
      return inst.allowedChannelIds && inst.allowedChannelIds.length > 0;
    }
    if (filterMode === "admin") {
      return !!inst.isAdmin;
    }

    return true;
  });

  if (isLoading) {
    return <ModuleSkeleton type="students" />;
  }

  return (
    <div className={styles.container}>
      {/* Toast Alert */}
      {toast && (
        <div style={{ position: "fixed", bottom: 24, right: 24, zIndex: 9999 }}>
          <Alert tone={toast.tone} title={toast.title}>
            {toast.description}
          </Alert>
        </div>
      )}

      {/* Cabecera Superior */}
      <div className={styles.topHeader}>
        <div>
          <h1 className={styles.pageTitle}>Gestión de Docentes & Mentores</h1>
          <p className={styles.pageSubtitle}>
            Administra los profesores de la academia, sus perfiles profesionales, cursos asignados y canales de comunidad que pueden moderar.
          </p>
        </div>
        <div className={styles.topActions}>
          <div className={styles.searchWrap}>
            <Input
              sizeVariant="md"
              leftIcon={<Search size={16} />}
              placeholder="Buscar docente por nombre o especialidad..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </div>
          {currentAdmin && !currentAdmin.is_already_instructor && (
            <Button variant="secondary" onClick={handleSyncAdmin} title="Registrar tu usuario administrador como docente">
              <ShieldCheck size={16} />
              <span>Asignarme como Docente</span>
            </Button>
          )}
          <Button variant="primary" onClick={openCreateModal}>
            <Plus size={16} />
            <span>Nuevo Docente</span>
          </Button>
        </div>
      </div>

      {/* Barra de Filtros */}
      <div className={styles.filterTabs}>
        <button
          type="button"
          className={[styles.filterBtn, filterMode === "all" ? styles.filterTabActive : ""].join(" ")}
          onClick={() => setFilterMode("all")}
        >
          Todos ({instructors.length})
        </button>
        <button
          type="button"
          className={[styles.filterBtn, filterMode === "courses" ? styles.filterTabActive : ""].join(" ")}
          onClick={() => setFilterMode("courses")}
        >
          Con Cursos Asignados ({instructors.filter((i) => (i.assigned_courses && i.assigned_courses.length > 0) || (i.assignedCourseIds && i.assignedCourseIds.length > 0)).length})
        </button>
        <button
          type="button"
          className={[styles.filterBtn, filterMode === "community" ? styles.filterTabActive : ""].join(" ")}
          onClick={() => setFilterMode("community")}
        >
          Moderadores de Comunidad ({instructors.filter((i) => i.allowedChannelIds && i.allowedChannelIds.length > 0).length})
        </button>
        <button
          type="button"
          className={[styles.filterBtn, filterMode === "admin" ? styles.filterTabActive : ""].join(" ")}
          onClick={() => setFilterMode("admin")}
        >
          Administradores ({instructors.filter((i) => i.isAdmin).length})
        </button>
      </div>

      {/* Grid de Tarjetas de Docentes */}
      {filteredInstructors.length > 0 ? (
        <div className={styles.instructorsGrid}>
          {filteredInstructors.map((inst) => {
            const assignedCourses = inst.assigned_courses || [];
            const allowedChannels = (inst.allowedChannelIds || [])
              .map((id) => channels.find((c) => c.id === id))
              .filter(Boolean) as CommunityChannel[];

            return (
              <div key={inst.id} className={styles.instructorCard}>
                <div className={styles.cardHeader}>
                  <div className={styles.avatarWrap}>
                    <img
                      src={
                        inst.avatar ||
                        "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80"
                      }
                      alt={inst.name}
                      className={styles.avatarImg}
                      onError={(e) => {
                        (e.target as HTMLImageElement).src =
                          "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80";
                      }}
                    />
                    {inst.isAdmin && (
                      <span className={styles.adminBadge} title="Usuario Administrador">
                        <ShieldCheck size={12} />
                      </span>
                    )}
                  </div>
                  <div className={styles.headerInfo}>
                    <h3 className={styles.instructorName}>{inst.name}</h3>
                    <span className={styles.instructorRole}>{inst.role || "Docente"}</span>
                    {inst.email && (
                      <div className={styles.instructorEmail}>
                        <Mail size={12} />
                        <span>{inst.email}</span>
                      </div>
                    )}
                  </div>
                </div>

                {inst.bio && <p className={styles.bioText}>{inst.bio}</p>}

                {/* Cursos Asignados */}
                <div className={styles.detailsSection}>
                  <span className={styles.sectionLabel}>
                    <BookOpen size={12} /> Cursos que imparte ({assignedCourses.length}):
                  </span>
                  <div className={styles.pillsList}>
                    {assignedCourses.length > 0 ? (
                      assignedCourses.map((c) => (
                        <span key={c.id} className={styles.coursePill} title={c.title}>
                          {c.title}
                        </span>
                      ))
                    ) : (
                      <span className={styles.emptyPill}>Sin cursos asignados</span>
                    )}
                  </div>
                </div>

                {/* Canales de Comunidad que Modera */}
                <div className={styles.detailsSection}>
                  <span className={styles.sectionLabel}>
                    <MessageSquare size={12} /> Canales que modera ({allowedChannels.length}):
                  </span>
                  <div className={styles.pillsList}>
                    {allowedChannels.length > 0 ? (
                      allowedChannels.map((ch) => (
                        <span key={ch.id} className={styles.channelPill}>
                          #{ch.name}
                        </span>
                      ))
                    ) : (
                      <span className={styles.emptyPill}>Sin moderación en comunidad</span>
                    )}
                  </div>
                </div>

                {/* Footer de la tarjeta con acciones */}
                <div className={styles.cardFooter}>
                  <div>
                    {inst.isAdmin ? (
                      <Badge variant="accent" size="sm">
                        Admin
                      </Badge>
                    ) : inst.userId ? (
                      <Badge variant="success" size="sm">
                        Usuario WP
                      </Badge>
                    ) : (
                      <Badge variant="neutral" size="sm">
                        Perfil Público
                      </Badge>
                    )}
                  </div>
                  <div className={styles.cardFooterRight}>
                    <Button variant="secondary" size="sm" onClick={() => openEditModal(inst)}>
                      <Edit3 size={13} /> Editar
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => setDeleteConfirm({ id: inst.id, name: inst.name || "este docente" })}
                      title="Eliminar docente"
                    >
                      <Trash2 size={13} />
                    </Button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      ) : (
        <div className={styles.emptyState}>
          <Users size={42} />
          <h4 style={{ margin: 0, color: "var(--foreground)" }}>No se encontraron docentes</h4>
          <p style={{ margin: 0, fontSize: 13 }}>
            {searchQuery
              ? "No hay resultados para tu búsqueda. Intenta con otro término."
              : "Aún no has registrado docentes en la academia."}
          </p>
          <Button variant="primary" size="sm" onClick={openCreateModal}>
            <Plus size={15} /> Crear Primer Docente
          </Button>
        </div>
      )}

      {/* MODAL WIZARD: REGISTRAR / EDITAR DOCENTE */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingInstructor ? `Gestionar Docente: ${editingInstructor.name}` : "Registrar Nuevo Docente"}
        maxWidth="720px"
        footer={
          editingInstructor ? (
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", width: "100%" }}>
              <Button variant="ghost" onClick={() => setIsModalOpen(false)}>
                Cancelar
              </Button>
              <Button
                variant="primary"
                onClick={handleSaveInstructor}
                loading={isSaving || isUploadingAvatar}
                disabled={isSaving || isUploadingAvatar}
              >
                <UserCheck size={15} />
                <span>Guardar Cambios</span>
              </Button>
            </div>
          ) : (
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", width: "100%" }}>
              <div>
                <Button variant="ghost" onClick={() => setIsModalOpen(false)}>
                  Cancelar
                </Button>
              </div>
              <div style={{ display: "flex", gap: "8px" }}>
                {wizardStep > 1 && (
                  <Button variant="secondary" onClick={() => setWizardStep((prev) => (prev - 1) as any)}>
                    <ArrowLeft size={14} />
                    <span>Atrás</span>
                  </Button>
                )}
                {wizardStep < 3 ? (
                  <Button variant="primary" onClick={handleNextStep}>
                    <span>Siguiente Paso</span>
                    <ArrowRight size={14} />
                  </Button>
                ) : (
                  <Button
                    variant="primary"
                    onClick={handleSaveInstructor}
                    loading={isSaving || isUploadingAvatar}
                    disabled={isSaving || isUploadingAvatar}
                  >
                    <UserCheck size={15} />
                    <span>Completar Registro de Docente</span>
                  </Button>
                )}
              </div>
            </div>
          )
        }
      >
        <div className={styles.modalForm}>
          {editingInstructor ? (
            /* Modo Edición: Tabs Directos para navegación sin bloqueos */
            <div style={{ marginBottom: "16px" }}>
              <SegmentedControl<"info" | "courses" | "community">
                options={[
                  { value: "info", label: "Datos & Cuenta", icon: <User size={14} /> },
                  {
                    value: "courses",
                    label: `Cursos Asignados (${activeCourses.filter((c) => formAssignedCourses.includes(c.id)).length})`,
                    icon: <GraduationCap size={14} />,
                  },
                  {
                    value: "community",
                    label: `Comunidad & Canales (${formAllowedChannels.length})`,
                    icon: <MessageSquare size={14} />,
                  },
                ]}
                value={editTab}
                onChange={setEditTab}
                size="sm"
              />
            </div>
          ) : (
            /* Modo Creación: Wizard Guiado por Pasos */
            <div className={styles.wizardStepsHeader}>
              <div
                className={`${styles.wizardStepItem} ${wizardStep === 1 ? styles.wizardStepActive : wizardStep > 1 ? styles.wizardStepDone : ""}`}
                onClick={() => setWizardStep(1)}
              >
                <div className={styles.wizardStepBadge}>{wizardStep > 1 ? <Check size={13} /> : "1"}</div>
                <div className={styles.wizardStepContent}>
                  <span className={styles.wizardStepLabel}>Paso 1</span>
                  <span className={styles.wizardStepName}>Datos & Cuenta</span>
                </div>
              </div>

              <div className={`${styles.wizardStepDivider} ${wizardStep >= 2 ? styles.wizardStepDividerActive : ""}`} />

              <div
                className={`${styles.wizardStepItem} ${wizardStep === 2 ? styles.wizardStepActive : wizardStep > 2 ? styles.wizardStepDone : ""}`}
                onClick={() => {
                  if (validateStep1()) setWizardStep(2);
                }}
              >
                <div className={styles.wizardStepBadge}>{wizardStep > 2 ? <Check size={13} /> : "2"}</div>
                <div className={styles.wizardStepContent}>
                  <span className={styles.wizardStepLabel}>Paso 2</span>
                  <span className={styles.wizardStepName}>Cursos Asignados</span>
                </div>
              </div>

              <div className={`${styles.wizardStepDivider} ${wizardStep >= 3 ? styles.wizardStepDividerActive : ""}`} />

              <div
                className={`${styles.wizardStepItem} ${wizardStep === 3 ? styles.wizardStepActive : ""}`}
                onClick={() => {
                  if (validateStep1()) setWizardStep(3);
                }}
              >
                <div className={styles.wizardStepBadge}>3</div>
                <div className={styles.wizardStepContent}>
                  <span className={styles.wizardStepLabel}>Paso 3</span>
                  <span className={styles.wizardStepName}>Comunidad & Resumen</span>
                </div>
              </div>
            </div>
          )}

          {/* =========================================================================
              PASO 1 / TAB 1: DATOS PERSONALES, ESPECIALIDAD Y CREDENCIALES DE ACCESO
              ========================================================================= */}
          {(editingInstructor ? editTab === "info" : wizardStep === 1) && (
            <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              <div className={styles.formGrid}>
                <Input
                  label="Nombre Completo *"
                  placeholder="Ej: Lic. Carlos Gómez"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                />
                <Input
                  label="Especialidad / Cargo Profesional *"
                  placeholder="Ej: Senior Web Developer & Mentor"
                  value={formRole}
                  onChange={(e) => setFormRole(e.target.value)}
                />
              </div>

              <div className={styles.formGrid}>
                <Input
                  label="Correo Electrónico *"
                  type="email"
                  placeholder="docente@academia.com"
                  value={formEmail}
                  onChange={(e) => setFormEmail(e.target.value)}
                  hint="Se utilizará como usuario de inicio de sesión y para notificaciones."
                />
                <div>
                  <label className={styles.label}>Foto de Perfil / Avatar</label>
                  {formAvatar ? (
                    <div className={styles.avatarPreviewRow}>
                      <img src={formAvatar} alt="Preview avatar" className={styles.avatarPreviewLarge} />
                      <Button variant="ghost" size="sm" onClick={() => setFormAvatar("")}>
                        <Trash2 size={13} /> Quitar
                      </Button>
                    </div>
                  ) : null}
                  <FileDropzone
                    accept="image/*"
                    multiple={false}
                    maxFiles={1}
                    showList={false}
                    label={formAvatar ? "Cambiar foto" : "Subir foto del docente"}
                    description="PNG, JPG o WEBP (Recomendado 400x400 px)"
                    onFilesChange={handleUploadAvatar}
                  />
                </div>
              </div>

              {/* Biografía */}
              <div>
                <label className={styles.label}>Biografía / Presentación Profesional</label>
                <textarea
                  placeholder="Breve reseña sobre su trayectoria, certificaciones o experiencia que verán los alumnos..."
                  value={formBio}
                  onChange={(e) => setFormBio(e.target.value)}
                  className={styles.textarea}
                  rows={2}
                />
              </div>

              {/* Sección de Cuenta y Credenciales de Docente */}
              <div className={styles.noticeBox} style={{ flexDirection: "column", gap: "10px" }}>
                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", width: "100%" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: "8px" }}>
                    <ShieldCheck size={18} style={{ color: "var(--accent)" }} />
                    <span style={{ fontWeight: 600, color: "var(--foreground)", fontSize: "13px" }}>
                      Cuenta de Acceso con Rol de Docente
                    </span>
                  </div>
                  <Switch
                    checked={formCreateWpUser}
                    onCheckedChange={(checked) => setFormCreateWpUser(checked)}
                    id="create-wp-user-switch"
                  />
                </div>
                <p style={{ margin: 0, color: "var(--text-muted)", fontSize: "12px" }}>
                  Al activarse, el docente tendrá usuario en WordPress con rol exclusivo <strong>crezca_instructor</strong> para iniciar sesión en la academia sin acceso a datos administrativos ni finanzas.
                </p>

                {formCreateWpUser && (
                  <div style={{ display: "flex", flexDirection: "column", gap: "10px", marginTop: "6px", width: "100%" }}>
                    <SegmentedControl<"auto" | "manual">
                      options={[
                        { value: "auto", label: "Generar Clave Automática", icon: <Sparkles size={13} /> },
                        { value: "manual", label: "Definir Clave Manual", icon: <KeyRound size={13} /> },
                      ]}
                      value={formPasswordMode}
                      onChange={(mode) => {
                        setFormPasswordMode(mode);
                        if (mode === "auto" && !formPassword) {
                          setFormPassword(generateRandomPassword());
                        }
                      }}
                      size="sm"
                    />

                    {formPasswordMode === "auto" ? (
                      <div style={{ display: "flex", alignItems: "center", gap: "10px", background: "var(--surface)", padding: "8px 12px", borderRadius: "8px", border: "1px solid var(--border)" }}>
                        <Key size={15} style={{ color: "var(--accent)" }} />
                        <span style={{ fontFamily: "monospace", fontSize: "13px", color: "var(--foreground)", fontWeight: 600, flex: 1 }}>
                          {formPassword || "Generando..."}
                        </span>
                        <button type="button" className={styles.copyPwdBtn} onClick={() => setFormPassword(generateRandomPassword())} title="Generar otra clave">
                          <RefreshCw size={13} />
                          <span>Regenerar</span>
                        </button>
                        <button type="button" className={styles.copyPwdBtn} onClick={copyPasswordToClipboard} title="Copiar contraseña">
                          {isPasswordCopied ? <Check size={13} style={{ color: "#10b981" }} /> : <Copy size={13} />}
                          <span>{isPasswordCopied ? "¡Copiada!" : "Copiar"}</span>
                        </button>
                      </div>
                    ) : (
                      <div style={{ position: "relative" }}>
                        <Input
                          label="Contraseña Manual *"
                          type={showPassword ? "text" : "password"}
                          placeholder="Ingresa una contraseña segura (mínimo 8 caracteres)"
                          value={formPassword}
                          onChange={(e) => setFormPassword(e.target.value)}
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          style={{ position: "absolute", right: "10px", top: "34px", background: "transparent", border: "none", color: "var(--text-muted)", cursor: "pointer" }}
                        >
                          {showPassword ? <EyeOff size={15} /> : <Eye size={15} />}
                        </button>
                      </div>
                    )}

                    <label style={{ display: "flex", alignItems: "center", gap: "8px", cursor: "pointer", fontSize: "12.5px", color: "var(--foreground)", marginTop: "4px" }}>
                      <input
                        type="checkbox"
                        checked={formSendWelcomeEmail}
                        onChange={(e) => setFormSendWelcomeEmail(e.target.checked)}
                      />
                      <span>Enviar correo de bienvenida con credenciales de acceso al docente</span>
                    </label>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* =========================================================================
              PASO 2 / TAB 2: ASIGNACIÓN DE CURSOS
              ========================================================================= */}
          {(editingInstructor ? editTab === "courses" : wizardStep === 2) && (
            <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              <div>
                <h4 style={{ margin: "0 0 4px", fontSize: "14px", color: "var(--foreground)", display: "flex", alignItems: "center", gap: "6px" }}>
                  <GraduationCap size={16} style={{ color: "var(--accent)" }} />
                  <span>Formaciones y Cursos Impartidos</span>
                </h4>
                <p style={{ margin: 0, fontSize: "12px", color: "var(--text-muted)" }}>
                  Selecciona los cursos donde este docente figurará como mentor y tendrá permisos para visualizar y gestionar sus alumnos.
                </p>
              </div>

              {activeCourses.length === 0 ? (
                <div style={{ padding: "20px", textAlign: "center", color: "var(--text-muted)", background: "var(--surface)", borderRadius: "8px" }}>
                  No hay cursos creados en la academia todavía.
                </div>
              ) : (
                <div style={{ display: "flex", flexDirection: "column", gap: "8px", maxHeight: "320px", overflowY: "auto", paddingRight: "4px" }}>
                  {activeCourses.map((course) => {
                    const isSelected = formAssignedCourses.includes(course.id);
                    return (
                      <div
                        key={course.id}
                        className={`${styles.courseSelectableCard} ${isSelected ? styles.courseSelectableCardActive : ""}`}
                        onClick={() => {
                          if (isSelected) {
                            setFormAssignedCourses(formAssignedCourses.filter((id) => id !== course.id));
                          } else {
                            setFormAssignedCourses([...formAssignedCourses, course.id]);
                          }
                        }}
                      >
                        <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                          <div style={{ width: "36px", height: "36px", borderRadius: "6px", background: "var(--surface-muted)", overflow: "hidden", display: "grid", placeItems: "center" }}>
                            {course.thumbnail ? (
                              <img src={course.thumbnail} alt={course.title} style={{ width: "100%", height: "100%", objectFit: "cover" }} />
                            ) : (
                              <GraduationCap size={18} style={{ color: "var(--text-muted)" }} />
                            )}
                          </div>
                          <div>
                            <span style={{ fontSize: "13px", fontWeight: 600, color: "var(--foreground)" }}>{course.title}</span>
                            <div style={{ fontSize: "11px", color: "var(--text-muted)" }}>
                              {course.sections ? `${course.sections.length} Módulos` : "Formación estándar"}
                            </div>
                          </div>
                        </div>

                        <Switch
                          checked={isSelected}
                          onCheckedChange={() => {}}
                          id={`course-assign-${course.id}`}
                        />
                      </div>
                    );
                  })}
                </div>
              )}

              <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "8px 12px", background: "var(--surface-subtle)", borderRadius: "8px", fontSize: "12px" }}>
                <span style={{ color: "var(--text-muted)" }}>Total formaciones seleccionadas:</span>
                <Badge variant={formAssignedCourses.length > 0 ? "accent" : "neutral"} size="sm">
                  {formAssignedCourses.length} {formAssignedCourses.length === 1 ? "Curso" : "Cursos"}
                </Badge>
              </div>
            </div>
          )}

          {/* =========================================================================
              PASO 3 / TAB 3: COMUNIDAD Y RESUMEN GENERAL
              ========================================================================= */}
          {(editingInstructor ? editTab === "community" : wizardStep === 3) && (
            <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              {/* Canales de Moderación */}
              <div>
                <h4 style={{ margin: "0 0 4px", fontSize: "14px", color: "var(--foreground)", display: "flex", alignItems: "center", gap: "6px" }}>
                  <MessageSquare size={16} style={{ color: "var(--accent)" }} />
                  <span>Canales de Comunidad para Moderar</span>
                </h4>
                <p style={{ margin: "0 0 8px", fontSize: "12px", color: "var(--text-muted)" }}>
                  Asigna los canales donde el docente tendrá insignia y permisos para interactuar con los alumnos.
                </p>

                {channels.length > 0 && (
                  <div className={styles.checkboxGrid}>
                    {channels.map((ch) => {
                      const isChecked = formAllowedChannels.includes(ch.id);
                      return (
                        <label key={ch.id} className={styles.checkboxItem}>
                          <input
                            type="checkbox"
                            checked={isChecked}
                            onChange={(e) => {
                              if (e.target.checked) {
                                setFormAllowedChannels([...formAllowedChannels, ch.id]);
                              } else {
                                setFormAllowedChannels(formAllowedChannels.filter((id) => id !== ch.id));
                              }
                            }}
                          />
                          <span>#{ch.name}</span>
                        </label>
                      );
                    })}
                  </div>
                )}
              </div>

              {/* Tarjeta de Resumen Visual */}
              <div className={styles.wizardSummaryCard}>
                <div className={styles.wizardSummaryTitle}>
                  <CheckCircle2 size={15} style={{ color: "#10b981" }} />
                  <span>Resumen del Registro del Docente</span>
                </div>

                <div className={styles.wizardSummaryGrid}>
                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Docente</span>
                    <span className={styles.wizardSummaryVal}>{formName}</span>
                  </div>

                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Especialidad</span>
                    <span className={styles.wizardSummaryVal}>{formRole}</span>
                  </div>

                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Correo de Acceso</span>
                    <span className={styles.wizardSummaryVal}>{formEmail}</span>
                  </div>

                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Cuenta Docente</span>
                    <span className={styles.wizardSummaryVal}>
                      {formCreateWpUser ? "✓ Rol crezca_instructor Activo" : "Solo Perfil Público"}
                    </span>
                  </div>

                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Cursos Asignados</span>
                    <span className={styles.wizardSummaryVal}>
                      {formAssignedCourses.length} {formAssignedCourses.length === 1 ? "Formación" : "Formaciones"}
                    </span>
                  </div>

                  <div className={styles.wizardSummaryItem}>
                    <span className={styles.wizardSummaryLabel}>Canales Moderados</span>
                    <span className={styles.wizardSummaryVal}>
                      {formAllowedChannels.length} {formAllowedChannels.length === 1 ? "Canal" : "Canales"}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </Modal>

      {/* MODAL DE CONFIRMACIÓN PARA ELIMINAR */}
      {deleteConfirm && (
        <Modal
          isOpen={true}
          onClose={() => setDeleteConfirm(null)}
          title="Desvincular Docente"
          maxWidth="460px"
          footer={
            <>
              <Button variant="ghost" onClick={() => setDeleteConfirm(null)}>
                Cancelar
              </Button>
              <Button variant="danger" onClick={executeDeleteInstructor}>
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
                ¿Estás seguro de que deseas desvincular a {deleteConfirm.name?.trim() ? <strong>"{deleteConfirm.name}"</strong> : "este docente"} del directorio de docentes?
                Esta acción no eliminará los cursos que imparte, pero dejará de figurar como mentor oficial.
              </p>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
