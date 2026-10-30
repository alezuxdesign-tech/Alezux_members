import React, { useState, useRef } from "react";
import { 
  ArrowLeft, 
  Check, 
  Camera, 
  Mail, 
  ShieldCheck, 
  BookOpen, 
  CheckCircle2, 
  Calendar, 
  Lock,
  LogOut,
  UploadCloud,
  Eye,
  EyeOff,
  Play,
  AlertCircle,
  Loader2,
  Sparkles,
  KeyRound
} from "lucide-react";
import { StudentProfile, Course, api } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentProfileViewProps {
  profile: StudentProfile;
  courses: Course[];
  onProfileUpdate: (updated: StudentProfile) => void;
  onBack: () => void;
  onLogout?: () => void;
  onSelectCourse?: (course: Course) => void;
}

export const StudentProfileView: React.FC<StudentProfileViewProps> = ({
  profile,
  courses,
  onProfileUpdate,
  onBack,
  onLogout,
  onSelectCourse,
}) => {
  // Input oculto para selección nativa de archivo desde la PC
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Estados del Dropzone y Avatar
  const [isDragging, setIsDragging] = useState(false);
  const [isUploadingAvatar, setIsUploadingAvatar] = useState(false);
  const [avatarMessage, setAvatarMessage] = useState<{ text: string; type: "success" | "error" } | null>(null);

  // Estados de Seguridad y Cambio de Contraseña
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isUpdatingPassword, setIsUpdatingPassword] = useState(false);
  const [passwordMessage, setPasswordMessage] = useState<{ text: string; type: "success" | "error" } | null>(null);

  // Filtrar exclusivamente los cursos que el estudiante tiene habilitados
  const enabledCourses = courses.filter((c) => {
    const hasDirectAccess = Boolean(c.hasAccess);
    const isEnrolled = profile.enrolledCourseIds && profile.enrolledCourseIds.map(Number).includes(Number(c.id));
    return hasDirectAccess || isEnrolled;
  });

  const enrolledCount = enabledCourses.length > 0 ? enabledCourses.length : profile.enrolledCourseIds.length;
  const completedCount = profile.completedTopicIds ? profile.completedTopicIds.length : 0;

  // Manejo de subida de archivo de avatar desde PC (Click o Dropzone)
  const processAvatarFile = async (file: File) => {
    if (!file.type.startsWith("image/")) {
      setAvatarMessage({ text: "Por favor selecciona un archivo de imagen válido (JPG, PNG o WEBP).", type: "error" });
      return;
    }

    if (file.size > 8 * 1024 * 1024) {
      setAvatarMessage({ text: "La imagen excede el límite permitido de 8MB.", type: "error" });
      return;
    }

    setIsUploadingAvatar(true);
    setAvatarMessage(null);

    try {
      const newAvatarUrl = await api.uploadStudentAvatar(file);
      const updatedProfile = await api.updateStudentProfile({ avatar: newAvatarUrl });
      onProfileUpdate(updatedProfile);
      setAvatarMessage({ text: "¡Foto de perfil actualizada exitosamente!", type: "success" });
      setTimeout(() => setAvatarMessage(null), 3500);
    } catch (err: any) {
      setAvatarMessage({ text: err.message || "Error al subir la imagen. Intenta de nuevo.", type: "error" });
    } finally {
      setIsUploadingAvatar(false);
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      processAvatarFile(file);
    }
    // Resetear valor para permitir volver a seleccionar el mismo archivo si es necesario
    if (fileInputRef.current) {
      fileInputRef.current.value = "";
    }
  };

  // Drag and drop handlers
  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    const file = e.dataTransfer.files?.[0];
    if (file) {
      processAvatarFile(file);
    }
  };

  // Manejo de actualización de contraseña
  const handlePasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newPassword.trim()) {
      setPasswordMessage({ text: "Por favor ingresa tu nueva contraseña.", type: "error" });
      return;
    }

    if (newPassword.length < 6) {
      setPasswordMessage({ text: "La contraseña debe tener al menos 6 caracteres.", type: "error" });
      return;
    }

    if (newPassword !== confirmPassword) {
      setPasswordMessage({ text: "Las contraseñas no coinciden. Verifícalas e inténtalo de nuevo.", type: "error" });
      return;
    }

    setIsUpdatingPassword(true);
    setPasswordMessage(null);

    try {
      const updated = await api.updateStudentProfile({ password: newPassword });
      onProfileUpdate(updated);
      setPasswordMessage({ text: "¡Tu contraseña ha sido actualizada correctamente!", type: "success" });
      setNewPassword("");
      setConfirmPassword("");
      setTimeout(() => setPasswordMessage(null), 4000);
    } catch (err: any) {
      setPasswordMessage({ text: err.message || "Error al actualizar la contraseña.", type: "error" });
    } finally {
      setIsUpdatingPassword(false);
    }
  };

  return (
    <div className={styles.profileContainer}>
      {/* 1. NAVEGACIÓN SUPERIOR */}
      <div className={styles.profileHeaderNav}>
        <button type="button" className={styles.backBtn} onClick={onBack}>
          <ArrowLeft size={16} />
          <span>Volver al aula</span>
        </button>

        {onLogout && (
          <button type="button" className={styles.backBtn} onClick={onLogout} title="Cerrar sesión">
            <LogOut size={15} />
            <span>Cerrar sesión</span>
          </button>
        )}
      </div>

      {/* Input de archivo invisible para subir foto desde la PC */}
      <input
        type="file"
        ref={fileInputRef}
        accept="image/png, image/jpeg, image/jpg, image/webp"
        style={{ display: "none" }}
        onChange={handleFileChange}
      />

      {/* 2. TARJETA PRINCIPAL DE IDENTIDAD DEL ESTUDIANTE */}
      <div className={styles.profileShowcaseCard}>
        {/* Banner Panorámico Superior */}
        <div className={styles.profileCoverWrap}>
          <img
            src={profile.coverBanner || "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1600&auto=format&fit=crop&q=80"}
            alt="Portada del perfil"
            className={styles.profileCoverImg}
          />
        </div>

        {/* Sección de Avatar con Dropzone y Datos de Identidad */}
        <div className={styles.profileHeaderBody}>
          <div className={styles.profileAvatarAndActions}>
            {/* Avatar interactivo tipo DROPZONE: Click abre explorador de archivos de PC, o arrastrar & soltar */}
            <div
              className={`${styles.profileAvatarWrapper} ${isDragging ? styles.avatarDragging : ""}`}
              onClick={() => !isUploadingAvatar && fileInputRef.current?.click()}
              onDragOver={handleDragOver}
              onDragLeave={handleDragLeave}
              onDrop={handleDrop}
              title="Haz clic o arrastra una imagen para subirla desde tu computador"
              style={{ cursor: isUploadingAvatar ? "wait" : "pointer" }}
            >
              <img
                src={profile.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80"}
                alt={profile.name}
                className={styles.profileAvatarImg}
              />

              {/* Overlay hover / dragover indicando subir desde PC */}
              <div className={styles.avatarChangeOverlayBtn}>
                {isUploadingAvatar ? (
                  <Loader2 size={24} className={styles.spinIcon} />
                ) : (
                  <div className={styles.avatarDropzonePrompt}>
                    <Camera size={22} />
                    <span>{isDragging ? "Soltar imagen" : "Cambiar foto"}</span>
                  </div>
                )}
              </div>

              {/* Badge azul con checkmark verificado */}
              <div className={styles.verifiedBadge} title="Perfil verificado de estudiante">
                <Check size={16} strokeWidth={3.5} />
              </div>
            </div>

            {/* Botón explícito para cambiar foto desde PC */}
            <div className={styles.profileActionBtnsGroup}>
              <button
                type="button"
                className={styles.outlineActionBtn}
                onClick={() => fileInputRef.current?.click()}
                disabled={isUploadingAvatar}
              >
                {isUploadingAvatar ? <Loader2 size={15} className={styles.spinIcon} /> : <UploadCloud size={15} />}
                <span>{isUploadingAvatar ? "Subiendo foto..." : "Subir foto desde PC"}</span>
              </button>
            </div>
          </div>

          {/* Feedback de subida de avatar */}
          {avatarMessage && (
            <div className={`${styles.avatarAlertNotice} ${avatarMessage.type === "success" ? styles.alertSuccess : styles.alertError}`}>
              {avatarMessage.type === "success" ? <CheckCircle2 size={16} /> : <AlertCircle size={16} />}
              <span>{avatarMessage.text}</span>
            </div>
          )}

          {/* Nombre y Correo del Estudiante */}
          <div className={styles.profileIdentity}>
            <h1 className={styles.profileMainName}>{profile.name}</h1>
            <div className={styles.profileEmailRow}>
              <Mail size={14} className={styles.metaIconInline} />
              <span>{profile.email}</span>
            </div>
          </div>

          {/* Badges de Estado y Membresía */}
          <div className={styles.profileBadgesRow}>
            <div className={styles.profilePillBadge} title="Plan activo en la academia">
              <ShieldCheck size={14} style={{ color: "var(--accent)" }} />
              <span>{profile.planName || "Membresía Activa"}</span>
            </div>
            <div className={styles.profilePillBadge} title="Cursos con acceso activo">
              <BookOpen size={14} />
              <span><strong>{enrolledCount}</strong> Cursos Habilitados</span>
            </div>
            {completedCount > 0 && (
              <div className={styles.profilePillBadge} title="Lecciones completadas">
                <CheckCircle2 size={14} />
                <span><strong>{completedCount}</strong> Lecciones Completadas</span>
              </div>
            )}
            <div className={styles.profilePillBadge} title="Fecha de inicio">
              <Calendar size={14} />
              <span>Miembro desde {profile.joinedDate || "este año"}</span>
            </div>
          </div>
        </div>
      </div>

      {/* 3. SECCIÓN: MIS CURSOS HABILITADOS */}
      <div className={styles.profileSectionCard}>
        <div className={styles.sectionHeaderRow}>
          <div className={styles.sectionTitleWrap}>
            <BookOpen size={20} style={{ color: "var(--accent)" }} />
            <h2 className={styles.sectionHeading}>Mis Cursos Habilitados</h2>
          </div>
          <span className={styles.sectionBadgeCount}>
            {enabledCourses.length} {enabledCourses.length === 1 ? "curso disponible" : "cursos disponibles"}
          </span>
        </div>

        {enabledCourses.length > 0 ? (
          <div className={styles.profileCoursesGrid}>
            {enabledCourses.map((course) => {
              const totalLessons = course.sections?.reduce((acc, s) => acc + (s.lessons?.length || 0), 0) || 0;
              const progressPct = Math.round(course.progress || 0);

              return (
                <div key={course.id} className={styles.profileCourseCard}>
                  <div className={styles.courseThumbWrap}>
                    <img
                      src={course.thumbnail || "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&auto=format&fit=crop&q=80"}
                      alt={course.title}
                      className={styles.courseThumbImg}
                    />
                    <div className={styles.courseLevelBadge}>
                      {(course as any).level || "Habilitado"}
                    </div>
                  </div>

                  <div className={styles.courseCardBody}>
                    <h3 className={styles.courseCardTitle}>{course.title}</h3>
                    
                    <div className={styles.courseMetaLine}>
                      <span>{totalLessons} {totalLessons === 1 ? "lección" : "lecciones"}</span>
                      {progressPct > 0 && <span>• {progressPct}% completado</span>}
                    </div>

                    {/* Barra de progreso */}
                    <div className={styles.profileProgressBarTrack}>
                      <div
                        className={styles.profileProgressBarFill}
                        style={{ width: `${progressPct}%` }}
                      />
                    </div>

                    <button
                      type="button"
                      className={styles.enterCourseBtn}
                      onClick={() => (onSelectCourse ? onSelectCourse(course) : onBack())}
                    >
                      <Play size={14} fill="currentColor" />
                      <span>{progressPct > 0 ? "Continuar Aprendiendo" : "Entrar al curso"}</span>
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className={styles.emptyCoursesBox}>
            <Sparkles size={36} style={{ color: "var(--accent)", opacity: 0.8 }} />
            <h4 className={styles.emptyTitle}>Aún no tienes cursos activos</h4>
            <p className={styles.emptyText}>
              Tus cursos matriculados aparecerán automáticamente en esta sección para que accedas a ellos directamente.
            </p>
            <button type="button" className={styles.solidActionBtn} onClick={onBack}>
              Explorar Catálogo de Cursos
            </button>
          </div>
        )}
      </div>

      {/* 4. SECCIÓN: SEGURIDAD Y CAMBIO DE CONTRASEÑA */}
      <div className={styles.profileSectionCard}>
        <div className={styles.sectionHeaderRow}>
          <div className={styles.sectionTitleWrap}>
            <KeyRound size={20} style={{ color: "var(--accent)" }} />
            <h2 className={styles.sectionHeading}>Seguridad y Contraseña</h2>
          </div>
        </div>

        <p className={styles.securitySubtext}>
          Puedes actualizar tu contraseña de acceso en cualquier momento. Te recomendamos utilizar al menos 8 caracteres combinando letras y números.
        </p>

        <form onSubmit={handlePasswordSubmit} className={styles.passwordForm}>
          <div className={styles.passwordFieldsRow}>
            {/* Campo Nueva Contraseña */}
            <div className={styles.passwordFieldGroup}>
              <label className={styles.passwordFieldLabel}>Nueva Contraseña</label>
              <div className={styles.passwordInputContainer}>
                <Lock size={16} className={styles.inputPrefixIcon} />
                <input
                  type={showNewPassword ? "text" : "password"}
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  placeholder="Escribe tu nueva contraseña"
                  className={styles.passwordInput}
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  className={styles.passwordEyeBtn}
                  onClick={() => setShowNewPassword(!showNewPassword)}
                  title={showNewPassword ? "Ocultar" : "Mostrar"}
                >
                  {showNewPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>

            {/* Campo Confirmar Contraseña */}
            <div className={styles.passwordFieldGroup}>
              <label className={styles.passwordFieldLabel}>Confirmar Nueva Contraseña</label>
              <div className={styles.passwordInputContainer}>
                <Lock size={16} className={styles.inputPrefixIcon} />
                <input
                  type={showConfirmPassword ? "text" : "password"}
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  placeholder="Confirma tu nueva contraseña"
                  className={styles.passwordInput}
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  className={styles.passwordEyeBtn}
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  title={showConfirmPassword ? "Ocultar" : "Mostrar"}
                >
                  {showConfirmPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </div>
          </div>

          {/* Mensaje de éxito / error en cambio de contraseña */}
          {passwordMessage && (
            <div className={`${styles.passwordNoticeAlert} ${passwordMessage.type === "success" ? styles.alertSuccess : styles.alertError}`}>
              {passwordMessage.type === "success" ? <CheckCircle2 size={16} /> : <AlertCircle size={16} />}
              <span>{passwordMessage.text}</span>
            </div>
          )}

          <div className={styles.passwordActionsRow}>
            <button
              type="submit"
              className={styles.solidActionBtn}
              disabled={isUpdatingPassword || (!newPassword && !confirmPassword)}
            >
              {isUpdatingPassword ? <Loader2 size={15} className={styles.spinIcon} /> : <Lock size={15} />}
              <span>{isUpdatingPassword ? "Guardando..." : "Guardar Nueva Contraseña"}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
