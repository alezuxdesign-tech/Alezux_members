import React, { useState } from "react";
import { 
  User, 
  Mail, 
  Lock, 
  Calendar, 
  Award, 
  BookOpen, 
  CheckCircle2, 
  LogOut, 
  ArrowLeft, 
  Save, 
  Sparkles,
  Camera
} from "lucide-react";
import { StudentProfile, Course, api } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentProfileViewProps {
  profile: StudentProfile;
  courses: Course[];
  onProfileUpdate: (updated: StudentProfile) => void;
  onBack: () => void;
  onLogout?: () => void;
}

export const StudentProfileView: React.FC<StudentProfileViewProps> = ({
  profile,
  courses,
  onProfileUpdate,
  onBack,
  onLogout,
}) => {
  const [name, setName] = useState(profile.name);
  const [email, setEmail] = useState(profile.email);
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [avatar, setAvatar] = useState(profile.avatar);
  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState<{ text: string; type: "success" | "error" } | null>(null);

  const enrolledCount = profile.enrolledCourseIds.length;
  const completedCount = profile.completedTopicIds.length;

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (password && password !== confirmPassword) {
      setMessage({ text: "Las contraseñas no coinciden.", type: "error" });
      return;
    }

    setIsSaving(true);
    setMessage(null);
    try {
      const payload: { name?: string; email?: string; password?: string; avatar?: string } = {
        name,
        email,
        avatar,
      };
      if (password) {
        payload.password = password;
      }
      const updated = await api.updateStudentProfile(payload);
      onProfileUpdate(updated);
      setMessage({ text: "Perfil actualizado correctamente.", type: "success" });
      setPassword("");
      setConfirmPassword("");
    } catch (err: any) {
      setMessage({ text: err.message || "Error al actualizar el perfil.", type: "error" });
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className={styles.profileContainer}>
      <div className={styles.profileHeaderNav}>
        <button type="button" className={styles.backBtn} onClick={onBack}>
          <ArrowLeft size={16} />
          <span>Volver al aula</span>
        </button>
      </div>

      <div className={styles.profileGrid}>
        {/* Tarjeta Lateral de Información del Estudiante */}
        <div className={styles.profileCardSide}>
          <div className={styles.avatarWrapper}>
            <img 
              src={avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80"} 
              alt={name} 
              className={styles.avatarImg} 
            />
            <button 
              type="button" 
              className={styles.avatarChangeBtn}
              onClick={() => {
                const newUrl = prompt("Ingresa la URL de tu nueva foto de perfil:", avatar);
                if (newUrl) setAvatar(newUrl);
              }}
              title="Cambiar foto de perfil"
            >
              <Camera size={14} />
            </button>
          </div>

          <h2 className={styles.profileName}>{profile.name}</h2>
          <span className={styles.profileUsername}>@{profile.username}</span>
          <span className={styles.profilePlanBadge}>{profile.planName}</span>

          <div className={styles.profileStatsList}>
            <div className={styles.profileStatItem}>
              <div className={styles.profileStatIcon}>
                <BookOpen size={16} />
              </div>
              <div className={styles.profileStatText}>
                <strong>{enrolledCount}</strong>
                <span>Cursos Habilitados</span>
              </div>
            </div>

            <div className={styles.profileStatItem}>
              <div className={styles.profileStatIcon}>
                <CheckCircle2 size={16} />
              </div>
              <div className={styles.profileStatText}>
                <strong>{completedCount}</strong>
                <span>Lecciones Completadas</span>
              </div>
            </div>

            <div className={styles.profileStatItem}>
              <div className={styles.profileStatIcon}>
                <Calendar size={16} />
              </div>
              <div className={styles.profileStatText}>
                <strong>{profile.joinedDate}</strong>
                <span>Miembro desde</span>
              </div>
            </div>
          </div>

          {onLogout && (
            <button type="button" className={styles.logoutBtn} onClick={onLogout}>
              <LogOut size={16} />
              <span>Cerrar Sesión</span>
            </button>
          )}
        </div>

        {/* Formulario de Configuración de Cuenta */}
        <div className={styles.profileCardMain}>
          <div className={styles.profileSectionTitleRow}>
            <Sparkles size={18} className={styles.sparkleIcon} />
            <h3 className={styles.profileSectionTitle}>Configuración de Cuenta</h3>
          </div>
          <p className={styles.profileSectionDesc}>
            Actualiza tus datos personales y credenciales de acceso para tu aula virtual.
          </p>

          {message && (
            <div className={message.type === "success" ? styles.alertSuccess : styles.alertError}>
              {message.text}
            </div>
          )}

          <form onSubmit={handleSave} className={styles.profileForm}>
            <div className={styles.formRow}>
              <label className={styles.formLabel}>
                <span>Nombre Completo</span>
                <div className={styles.inputWrapper}>
                  <User size={16} className={styles.inputIcon} />
                  <input
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    required
                    className={styles.textInput}
                    placeholder="Tu nombre completo"
                  />
                </div>
              </label>

              <label className={styles.formLabel}>
                <span>Correo Electrónico</span>
                <div className={styles.inputWrapper}>
                  <Mail size={16} className={styles.inputIcon} />
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    className={styles.textInput}
                    placeholder="tu@correo.com"
                  />
                </div>
              </label>
            </div>

            <div className={styles.formDivider} />

            <h4 className={styles.formSubtitle}>Cambiar Contraseña</h4>
            <p className={styles.formSubtext}>
              Deja estos campos en blanco si deseas conservar tu contraseña actual.
            </p>

            <div className={styles.formRow}>
              <label className={styles.formLabel}>
                <span>Nueva Contraseña</span>
                <div className={styles.inputWrapper}>
                  <Lock size={16} className={styles.inputIcon} />
                  <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className={styles.textInput}
                    placeholder="Nueva contraseña segura"
                    minLength={6}
                  />
                </div>
              </label>

              <label className={styles.formLabel}>
                <span>Confirmar Contraseña</span>
                <div className={styles.inputWrapper}>
                  <Lock size={16} className={styles.inputIcon} />
                  <input
                    type="password"
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    className={styles.textInput}
                    placeholder="Repite la contraseña"
                    minLength={6}
                  />
                </div>
              </label>
            </div>

            <div className={styles.formSubmitRow}>
              <button 
                type="submit" 
                className={styles.saveBtn}
                disabled={isSaving}
              >
                <Save size={16} />
                <span>{isSaving ? "Guardando cambios..." : "Guardar Perfil"}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};
