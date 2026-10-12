import React, { useState } from "react";
import { 
  ArrowLeft, 
  Check, 
  MoreHorizontal, 
  Video, 
  Edit3, 
  Camera, 
  Globe, 
  ExternalLink, 
  Mail, 
  ShieldCheck, 
  BookOpen, 
  CheckCircle2, 
  Calendar, 
  Save, 
  Lock,
  LogOut,
  Sparkles
} from "lucide-react";
import { StudentProfile, Course, api } from "../../services/api";
import { Modal } from "../../components/arc/modal/modal";
import { Button } from "../../components/arc/button/button";
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
  // Modal de edición
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [showFullBio, setShowFullBio] = useState(false);

  // Estados editables del perfil
  const [name, setName] = useState(profile.name);
  const [email, setEmail] = useState(profile.email);
  const [headline, setHeadline] = useState(profile.headline || "Diseñador de Producto & Estratega Digital");
  const [experience, setExperience] = useState(profile.experience || "Especializado en diseño UX/UI, brand strategy y desarrollo de arquitecturas escalables.");
  const [bio, setBio] = useState(
    profile.bio || 
    "Apasionado por crear experiencias digitales de alto impacto, plataformas escalables y estrategias de crecimiento para startups y creadores. Formándome continuamente en los programas de Crezca para llevar proyectos al siguiente nivel.\n\nHe trabajado en múltiples proyectos de diseño, desarrollo frontend y adquisición de usuarios, combinando buenas prácticas de experiencia de usuario con una mentalidad ágil y orientada a métricas."
  );
  const [location, setLocation] = useState(profile.location || "Bogotá, Colombia");
  const [website, setWebsite] = useState(profile.website || "alezux.com");
  const [avatar, setAvatar] = useState(profile.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80");
  const [coverBanner, setCoverBanner] = useState(profile.coverBanner || "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1600&auto=format&fit=crop&q=80");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const [isSaving, setIsSaving] = useState(false);
  const [message, setMessage] = useState<{ text: string; type: "success" | "error" } | null>(null);

  const enrolledCount = profile.enrolledCourseIds.length;
  const completedCount = profile.completedTopicIds.length;

  // Manejo de guardado en el Modal
  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (password && password !== confirmPassword) {
      setMessage({ text: "Las contraseñas no coinciden.", type: "error" });
      return;
    }

    setIsSaving(true);
    setMessage(null);
    try {
      const payload: Partial<StudentProfile> & { password?: string } = {
        name,
        email,
        headline,
        experience,
        bio,
        location,
        website,
        avatar,
        coverBanner,
      };
      if (password) {
        payload.password = password;
      }
      const updated = await api.updateStudentProfile(payload);
      onProfileUpdate(updated);
      setMessage({ text: "Perfil actualizado correctamente.", type: "success" });
      setPassword("");
      setConfirmPassword("");
      setTimeout(() => {
        setIsEditModalOpen(false);
        setMessage(null);
      }, 1000);
    } catch (err: any) {
      setMessage({ text: err.message || "Error al actualizar el perfil.", type: "error" });
    } finally {
      setIsSaving(false);
    }
  };

  // Dividir biografía en párrafos
  const bioParagraphs = (profile.bio || bio).split("\n").filter((p) => p.trim() !== "");
  const displayedBio = showFullBio ? bioParagraphs : bioParagraphs.slice(0, 2);

  const websiteUrl = (profile.website || website).startsWith("http")
    ? (profile.website || website)
    : `https://${profile.website || website}`;

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

      {/* 2. TARJETA PRINCIPAL DEL PERFIL (SHOWCASE TIPO REFERENCIA) */}
      <div className={styles.profileShowcaseCard}>
        {/* Banner Panorámico Superior */}
        <div className={styles.profileCoverWrap}>
          <img
            src={profile.coverBanner || coverBanner}
            alt="Portada del perfil"
            className={styles.profileCoverImg}
          />
          <button
            type="button"
            className={styles.editCoverBtn}
            onClick={() => {
              const url = prompt("Ingresa la URL de tu imagen de portada / banner:", profile.coverBanner || coverBanner);
              if (url) {
                setCoverBanner(url);
                api.updateStudentProfile({ coverBanner: url }).then(onProfileUpdate);
              }
            }}
            title="Cambiar imagen de portada"
          >
            <Camera size={13} />
            <span>Cambiar portada</span>
          </button>
        </div>

        {/* Sección de Avatar, Verificación y Acciones */}
        <div className={styles.profileHeaderBody}>
          <div className={styles.profileAvatarAndActions}>
            {/* Avatar circular con badge verificado */}
            <div className={styles.profileAvatarWrapper}>
              <img
                src={profile.avatar || avatar}
                alt={profile.name}
                className={styles.profileAvatarImg}
              />
              {/* Badge azul con checkmark verificado (igual a la referencia) */}
              <div className={styles.verifiedBadge} title="Perfil verificado de estudiante">
                <Check size={16} strokeWidth={3.5} />
              </div>
              <button
                type="button"
                className={styles.avatarChangeOverlayBtn}
                onClick={() => {
                  const url = prompt("Ingresa la URL de tu nueva foto de perfil:", profile.avatar || avatar);
                  if (url) {
                    setAvatar(url);
                    api.updateStudentProfile({ avatar: url }).then(onProfileUpdate);
                  }
                }}
                title="Cambiar foto de perfil"
              >
                <Camera size={18} />
              </button>
            </div>

            {/* Grupo de botones de acción a la derecha */}
            <div className={styles.profileActionBtnsGroup}>
              <button
                type="button"
                className={styles.iconCircleBtn}
                title="Copiar enlace del perfil"
                onClick={() => {
                  navigator.clipboard?.writeText(window.location.href);
                  alert("¡Enlace del perfil copiado al portapapeles!");
                }}
              >
                <MoreHorizontal size={18} />
              </button>

              <button
                type="button"
                className={styles.outlineActionBtn}
                onClick={onBack}
                title="Ir a tus cursos y lecciones"
              >
                <Video size={15} />
                <span>Aula Virtual</span>
              </button>

              <button
                type="button"
                className={styles.solidActionBtn}
                onClick={() => setIsEditModalOpen(true)}
              >
                <Edit3 size={15} />
                <span>Editar perfil</span>
              </button>
            </div>
          </div>

          {/* Nombre e Identidad del Estudiante */}
          <div className={styles.profileIdentity}>
            <h1 className={styles.profileMainName}>{profile.name}</h1>
            <p className={styles.profileHeadline}>
              {profile.headline || headline}
            </p>
          </div>
        </div>

        {/* Sección "Experience" */}
        <div className={styles.profileExperienceSection}>
          <div className={styles.experienceHeaderRow}>
            <h3 className={styles.experienceHeading}>Experience</h3>
            <button
              type="button"
              className={styles.experienceMoreBtn}
              onClick={() => setIsEditModalOpen(true)}
              title="Editar especialidad"
            >
              <MoreHorizontal size={16} />
            </button>
          </div>
          <p className={styles.experienceText}>
            {profile.experience || experience}
          </p>
        </div>

        {/* Línea Divisoria */}
        <div className={styles.profileDividerLine} />

        {/* Grilla Principal de Contenido: About me (Izq) y Detalles (Der) */}
        <div className={styles.profileContentGrid}>
          {/* Columna Izquierda: About me */}
          <div className={styles.profileAboutCol}>
            <h3 className={styles.aboutHeading}>About me</h3>
            {displayedBio.map((paragraph, idx) => (
              <p key={idx} className={styles.aboutParagraph}>
                {paragraph}
              </p>
            ))}

            {bioParagraphs.length > 2 && (
              <button
                type="button"
                className={styles.readMoreBtn}
                onClick={() => setShowFullBio(!showFullBio)}
              >
                {showFullBio ? "Leer menos" : "Read more"}
              </button>
            )}

            {/* Badges de Progreso y Aprendizaje en Crezca */}
            <div className={styles.profileBadgesRow}>
              <div className={styles.profilePillBadge} title="Cursos con acceso activo">
                <BookOpen size={14} />
                <span><strong>{enrolledCount}</strong> Cursos Habilitados</span>
              </div>
              <div className={styles.profilePillBadge} title="Clases completadas">
                <CheckCircle2 size={14} />
                <span><strong>{completedCount}</strong> Lecciones Completadas</span>
              </div>
              <div className={styles.profilePillBadge} title="Plan de suscripción">
                <ShieldCheck size={14} />
                <span>{profile.planName}</span>
              </div>
              <div className={styles.profilePillBadge} title="Fecha de registro">
                <Calendar size={14} />
                <span>Miembro desde {profile.joinedDate}</span>
              </div>
            </div>
          </div>

          {/* Columna Derecha: Metadata 2x2 (Location, Website, Membresía, Email) */}
          <div className={styles.profileMetaCol}>
            {/* Ubicación */}
            <div className={styles.profileMetaItem}>
              <span className={styles.profileMetaLabel}>Location</span>
              <div className={styles.profileMetaValue}>
                <Globe size={15} className={styles.metaIconInline} />
                <span>{profile.location || location}</span>
              </div>
            </div>

            {/* Website / Portafolio */}
            <div className={styles.profileMetaItem}>
              <span className={styles.profileMetaLabel}>Website</span>
              <a
                href={websiteUrl}
                target="_blank"
                rel="noreferrer"
                className={styles.profileMetaLink}
                title={websiteUrl}
              >
                <span>{profile.website || website}</span>
                <ExternalLink size={13} />
              </a>
            </div>

            {/* Membresía / Plan */}
            <div className={styles.profileMetaItem}>
              <span className={styles.profileMetaLabel}>Membresía</span>
              <div className={styles.profileMetaValue}>
                <ShieldCheck size={15} className={styles.metaIconInline} />
                <span>{profile.planName}</span>
              </div>
            </div>

            {/* Email */}
            <div className={styles.profileMetaItem}>
              <span className={styles.profileMetaLabel}>Email</span>
              <a
                href={`mailto:${profile.email}`}
                className={styles.profileMetaLink}
                title={profile.email}
              >
                <span>{profile.email}</span>
                <ExternalLink size={13} />
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* 3. MODAL DE EDICIÓN DEL PERFIL */}
      <Modal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        title="Editar Perfil"
        description="Actualiza tu información pública, bio y datos de contacto de tu perfil."
        maxWidth="680px"
        footer={
          <div style={{ display: "flex", justifyContent: "flex-end", gap: "10px", width: "100%" }}>
            <Button variant="secondary" onClick={() => setIsEditModalOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" onClick={handleSave} disabled={isSaving}>
              <Save size={15} />
              <span>{isSaving ? "Guardando..." : "Guardar Cambios"}</span>
            </Button>
          </div>
        }
      >
        <form onSubmit={handleSave} className={styles.modalFormBody}>
          {message && (
            <div className={message.type === "success" ? styles.alertSuccess : styles.alertError}>
              {message.text}
            </div>
          )}

          <div className={styles.modalFormRow}>
            <div className={styles.modalFormGroup}>
              <label>Nombre Completo</label>
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
                className={styles.modalInput}
                placeholder="Ej. Alejandro Zuñiga"
              />
            </div>

            <div className={styles.modalFormGroup}>
              <label>Correo Electrónico</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className={styles.modalInput}
                placeholder="ejemplo@correo.com"
              />
            </div>
          </div>

          <div className={styles.modalFormGroup}>
            <label>Titular / Headline</label>
            <input
              type="text"
              value={headline}
              onChange={(e) => setHeadline(e.target.value)}
              className={styles.modalInput}
              placeholder="Ej. Diseñador de Producto & Estratega Digital"
            />
          </div>

          <div className={styles.modalFormGroup}>
            <label>Especialidad / Experience</label>
            <input
              type="text"
              value={experience}
              onChange={(e) => setExperience(e.target.value)}
              className={styles.modalInput}
              placeholder="Ej. Especializado en UX/UI, funnels de conversión y Webflow."
            />
          </div>

          <div className={styles.modalFormGroup}>
            <label>Sobre mí (Biografía completa)</label>
            <textarea
              value={bio}
              onChange={(e) => setBio(e.target.value)}
              className={styles.modalTextarea}
              rows={4}
              placeholder="Escribe tu trayectoria, objetivos de aprendizaje y experiencia..."
            />
          </div>

          <div className={styles.modalFormRow}>
            <div className={styles.modalFormGroup}>
              <label>Ubicación (Location)</label>
              <input
                type="text"
                value={location}
                onChange={(e) => setLocation(e.target.value)}
                className={styles.modalInput}
                placeholder="Ej. Bogotá, Colombia"
              />
            </div>

            <div className={styles.modalFormGroup}>
              <label>Sitio Web / Portafolio</label>
              <input
                type="text"
                value={website}
                onChange={(e) => setWebsite(e.target.value)}
                className={styles.modalInput}
                placeholder="Ej. miportafolio.com"
              />
            </div>
          </div>

          <div className={styles.modalSectionDivider} />
          <h4 className={styles.modalSectionHeading}>Imágenes de Perfil</h4>

          <div className={styles.modalFormRow}>
            <div className={styles.modalFormGroup}>
              <label>URL Foto de Perfil (Avatar)</label>
              <input
                type="url"
                value={avatar}
                onChange={(e) => setAvatar(e.target.value)}
                className={styles.modalInput}
                placeholder="https://images.unsplash.com/..."
              />
            </div>

            <div className={styles.modalFormGroup}>
              <label>URL Banner de Portada</label>
              <input
                type="url"
                value={coverBanner}
                onChange={(e) => setCoverBanner(e.target.value)}
                className={styles.modalInput}
                placeholder="https://images.unsplash.com/..."
              />
            </div>
          </div>

          <div className={styles.modalSectionDivider} />
          <h4 className={styles.modalSectionHeading}>Seguridad y Contraseña (Opcional)</h4>

          <div className={styles.modalFormRow}>
            <div className={styles.modalFormGroup}>
              <label>Nueva Contraseña</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className={styles.modalInput}
                placeholder="Mínimo 6 caracteres"
                minLength={6}
              />
            </div>

            <div className={styles.modalFormGroup}>
              <label>Confirmar Contraseña</label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                className={styles.modalInput}
                placeholder="Repetir contraseña"
                minLength={6}
              />
            </div>
          </div>
        </form>
      </Modal>
    </div>
  );
};
