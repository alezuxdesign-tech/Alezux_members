import { useState, useEffect } from "react";
import {
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
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { FileDropzone } from "../components/arc/file-dropzone/file-dropzone";
import { Alert } from "../components/arc/alert/alert";
import { ModuleSkeleton } from "../components/arc/skeleton";
import { api, Instructor, Course, CommunityChannel } from "../services/api";
import styles from "./InstructorsView.module.css";

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

  // Modal de Crear / Editar Docente
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingInstructor, setEditingInstructor] = useState<Instructor | null>(null);

  // Campos de formulario
  const [formName, setFormName] = useState("");
  const [formEmail, setFormEmail] = useState("");
  const [formRole, setFormRole] = useState("");
  const [formAvatar, setFormAvatar] = useState("");
  const [formBio, setFormBio] = useState("");
  const [formCreateWpUser, setFormCreateWpUser] = useState(false);
  const [formPassword, setFormPassword] = useState("");
  const [formAllowedChannels, setFormAllowedChannels] = useState<number[]>([]);
  const [formAssignedCourses, setFormAssignedCourses] = useState<number[]>([]);
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
    setFormName("");
    setFormEmail("");
    setFormRole("Docente Principal");
    setFormAvatar("");
    setFormBio("");
    setFormCreateWpUser(false);
    setFormPassword("");
    setFormAllowedChannels([]);
    setFormAssignedCourses([]);
    setIsModalOpen(true);
  };

  // Abrir Modal para Editar Docente
  const openEditModal = (inst: Instructor) => {
    setEditingInstructor(inst);
    setFormName(inst.name || "");
    setFormEmail(inst.email || "");
    setFormRole(inst.role || "Docente");
    setFormAvatar(inst.avatar || "");
    setFormBio(inst.bio || "");
    setFormCreateWpUser(false);
    setFormPassword("");
    setFormAllowedChannels(inst.allowedChannelIds || []);
    setFormAssignedCourses(
      inst.assigned_courses ? inst.assigned_courses.map((c) => c.id) : inst.assignedCourseIds || []
    );
    setIsModalOpen(true);
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
      const payload: Partial<Instructor> & { createWpUser?: boolean; password?: string } = {
        id: editingInstructor ? editingInstructor.id : undefined,
        name: formName.trim(),
        email: formEmail.trim(),
        role: formRole.trim() || "Docente",
        avatar: formAvatar.trim(),
        bio: formBio.trim(),
        allowedChannelIds: formAllowedChannels,
        assignedCourseIds: formAssignedCourses,
        createWpUser: formCreateWpUser,
        password: formPassword.trim() || undefined,
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
                      onClick={() => setDeleteConfirm({ id: inst.id, name: inst.name })}
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

      {/* MODAL CREAR / EDITAR DOCENTE */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingInstructor ? `Editar Docente: ${editingInstructor.name}` : "Registrar Nuevo Docente"}
        maxWidth="680px"
        footer={
          <>
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
              <span>{editingInstructor ? "Guardar Cambios" : "Registrar Docente"}</span>
            </Button>
          </>
        }
      >
        <div className={styles.modalForm}>
          {/* Si es nuevo, opción de crear usuario de WordPress */}
          {!editingInstructor && (
            <div className={styles.noticeBox}>
              <Sparkles size={18} style={{ color: "var(--accent)", flexShrink: 0, marginTop: 2 }} />
              <div>
                <span style={{ fontWeight: 600, color: "var(--foreground)" }}>Cuenta de Acceso en WordPress</span>
                <p style={{ margin: "4px 0 0", color: "var(--text-muted)" }}>
                  Puedes registrar al docente como perfil público o crearle automáticamente un usuario para que pueda iniciar sesión en la academia.
                </p>
                <div style={{ marginTop: 8 }}>
                  <label style={{ display: "flex", alignItems: "center", gap: 8, cursor: "pointer", fontSize: 13 }}>
                    <Switch
                      checked={formCreateWpUser}
                      onCheckedChange={(checked) => setFormCreateWpUser(checked)}
                      id="create-wp-user-switch"
                    />
                    <span>Crear usuario y contraseña de WordPress para este docente</span>
                  </label>
                </div>
              </div>
            </div>
          )}

          <div className={styles.formGrid}>
            <Input
              label="Nombre del Docente *"
              placeholder="Ej: Lic. Carlos Gómez"
              value={formName}
              onChange={(e) => setFormName(e.target.value)}
            />
            <Input
              label="Especialidad / Rol Profesional *"
              placeholder="Ej: Senior Growth & Performance"
              value={formRole}
              onChange={(e) => setFormRole(e.target.value)}
            />
          </div>

          <div className={styles.formGrid}>
            <Input
              label="Correo Electrónico"
              type="email"
              placeholder="docente@academia.com"
              value={formEmail}
              onChange={(e) => setFormEmail(e.target.value)}
              hint={formCreateWpUser ? "Se usará para su cuenta de WordPress." : "Para contacto y notificaciones."}
            />
            {formCreateWpUser && (
              <Input
                label="Contraseña de Acceso"
                type="password"
                placeholder="Dejar en blanco para auto-generar"
                value={formPassword}
                onChange={(e) => setFormPassword(e.target.value)}
                hint="Mínimo 8 caracteres."
              />
            )}
          </div>

          {/* Subida de Foto de Perfil con File Upload */}
          <div>
            <label className={styles.label}>Foto de Perfil / Avatar</label>
            {formAvatar ? (
              <div className={styles.avatarPreviewRow}>
                <img src={formAvatar} alt="Preview avatar" className={styles.avatarPreviewLarge} />
                <Button variant="ghost" size="sm" onClick={() => setFormAvatar("")}>
                  <Trash2 size={13} /> Quitar Foto
                </Button>
              </div>
            ) : null}
            <FileDropzone
              accept="image/*"
              multiple={false}
              maxFiles={1}
              showList={false}
              label={formAvatar ? "Arrastra otra imagen para reemplazar la foto" : "Arrastra la foto del docente aquí"}
              description="o haz clic para buscar en tu equipo (PNG, JPG, WEBP)"
              note="Recomendado: Cuadrada 400x400 px"
              onFilesChange={handleUploadAvatar}
            />
          </div>

          {/* Biografía */}
          <div>
            <label className={styles.label}>Biografía / Presentación Profesional</label>
            <textarea
              placeholder="Breve reseña sobre su trayectoria, experiencia, certificaciones o logros que verán los alumnos..."
              value={formBio}
              onChange={(e) => setFormBio(e.target.value)}
              className={styles.textarea}
              rows={3}
            />
          </div>

          {/* Cursos Asignados */}
          {courses.length > 0 && (
            <div>
              <label className={styles.label}>
                <BookOpen size={14} /> Cursos Impartidos por este Docente
              </label>
              <div className={styles.checkboxGrid}>
                {courses.map((course) => {
                  const isChecked = formAssignedCourses.includes(course.id);
                  return (
                    <label key={course.id} className={styles.checkboxItem}>
                      <input
                        type="checkbox"
                        checked={isChecked}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setFormAssignedCourses([...formAssignedCourses, course.id]);
                          } else {
                            setFormAssignedCourses(formAssignedCourses.filter((id) => id !== course.id));
                          }
                        }}
                      />
                      <span>{course.title}</span>
                    </label>
                  );
                })}
              </div>
            </div>
          )}

          {/* Permisos de Moderación de Comunidad */}
          {channels.length > 0 && (
            <div>
              <label className={styles.label}>
                <MessageSquare size={14} /> Canales de Comunidad que puede Moderar
              </label>
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
                ¿Estás seguro de que deseas desvincular a <strong>{deleteConfirm.name}</strong> del directorio de docentes?
                Esta acción no eliminará los cursos que imparte, pero dejará de figurar como mentor oficial.
              </p>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
