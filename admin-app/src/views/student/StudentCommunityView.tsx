import React from "react";
import { 
  MessageCircle, 
  Users, 
  Video, 
  ExternalLink, 
  Calendar, 
  ShieldCheck, 
  Sparkles, 
  CheckCircle2, 
  HelpCircle,
  Clock,
  ArrowRight
} from "lucide-react";
import { Course } from "../../services/api";
import styles from "./StudentPortal.module.css";

interface StudentCommunityViewProps {
  courses: Course[];
  academyName: string;
}

export const StudentCommunityView: React.FC<StudentCommunityViewProps> = ({
  courses,
  academyName,
}) => {
  // Obtener URLs de los cursos activos si están configuradas
  const courseWithWhatsApp = courses.find((c) => c.whatsapp_url);
  const courseWithSlack = courses.find((c) => c.slack_url);
  const courseWithZoom = courses.find((c) => c.zoom_url);

  const whatsappUrl = courseWithWhatsApp?.whatsapp_url || "https://chat.whatsapp.com";
  const slackUrl = courseWithSlack?.slack_url || "https://slack.com";
  const zoomUrl = courseWithZoom?.zoom_url || "https://zoom.us";

  return (
    <div className={styles.communityHubContainer}>
      {/* 1. CABECERA DE LA COMUNIDAD */}
      <div className={styles.communityHeroBanner}>
        <div className={styles.communityHeroContent}>
          <div className={styles.communityTag}>
            <Users size={14} />
            <span>Comunidad Oficial</span>
          </div>
          <h1 className={styles.communityTitle}>
            Comunidad & Canales de {academyName || "la Academia"}
          </h1>
          <p className={styles.communitySubtitle}>
            Conéctate con otros estudiantes, debate con los instructores, resuelve tus dudas y participa en las mentorías en directo.
          </p>
        </div>
      </div>

      {/* 2. TARJETAS PRINCIPALES DE LOS 3 CANALES */}
      <div className={styles.communityCardsGrid}>
        {/* Tarjeta WhatsApp */}
        <div className={styles.communityChannelCard} style={{ borderColor: "rgba(37, 211, 102, 0.25)" }}>
          <div className={styles.channelHeader}>
            <div className={styles.channelIconBox} style={{ background: "rgba(37, 211, 102, 0.15)", color: "#25D366" }}>
              <MessageCircle size={28} />
            </div>
            <span className={styles.channelPill} style={{ background: "rgba(37, 211, 102, 0.12)", color: "#25D366" }}>
              Canal Activo
            </span>
          </div>

          <h3 className={styles.channelTitle}>Grupo VIP de WhatsApp</h3>
          <p className={styles.channelDesc}>
            Conversaciones diarias, avisos de nuevas clases, noticias de la industria y networking directo entre los miembros de tu generación.
          </p>

          <div className={styles.channelMetaList}>
            <div className={styles.channelMetaItem}>
              <CheckCircle2 size={14} className={styles.channelCheckIcon} />
              <span>Respuestas rápidas de la comunidad</span>
            </div>
            <div className={styles.channelMetaItem}>
              <CheckCircle2 size={14} className={styles.channelCheckIcon} />
              <span>Notificaciones de lanzamientos y eventos</span>
            </div>
          </div>

          <a
            href={whatsappUrl}
            target="_blank"
            rel="noopener noreferrer"
            className={styles.channelActionBtn}
            style={{ background: "#25D366", color: "#090a0f" }}
          >
            <span>Unirme al Grupo de WhatsApp</span>
            <ExternalLink size={15} />
          </a>
        </div>

        {/* Tarjeta Slack */}
        <div className={styles.communityChannelCard} style={{ borderColor: "rgba(192, 132, 252, 0.25)" }}>
          <div className={styles.channelHeader}>
            <div className={styles.channelIconBox} style={{ background: "rgba(74, 21, 75, 0.3)", color: "#c084fc" }}>
              <Users size={28} />
            </div>
            <span className={styles.channelPill} style={{ background: "rgba(192, 132, 252, 0.12)", color: "#c084fc" }}>
              Discusión Técnica
            </span>
          </div>

          <h3 className={styles.channelTitle}>Workspace en Slack</h3>
          <p className={styles.channelDesc}>
            Canales clasificados por temática, hilos organizados para soporte técnico de lecciones, revisión de ejercicios y recursos descargables.
          </p>

          <div className={styles.channelMetaList}>
            <div className={styles.channelMetaItem}>
              <CheckCircle2 size={14} className={styles.channelCheckIcon} />
              <span>Canales ordenados por cada módulo</span>
            </div>
            <div className={styles.channelMetaItem}>
              <CheckCircle2 size={14} className={styles.channelCheckIcon} />
              <span>Soporte por parte del equipo docente</span>
            </div>
          </div>

          <a
            href={slackUrl}
            target="_blank"
            rel="noopener noreferrer"
            className={styles.channelActionBtn}
            style={{ background: "#4A154B", color: "#ffffff" }}
          >
            <span>Entrar al Slack Oficial</span>
            <ExternalLink size={15} />
          </a>
        </div>

        {/* Tarjeta Zoom */}
        <div className={styles.communityChannelCard} style={{ borderColor: "rgba(45, 140, 255, 0.25)" }}>
          <div className={styles.channelHeader}>
            <div className={styles.channelIconBox} style={{ background: "rgba(45, 140, 255, 0.15)", color: "#2D8CFF" }}>
              <Video size={28} />
            </div>
            <span className={styles.channelPill} style={{ background: "rgba(45, 140, 255, 0.12)", color: "#2D8CFF" }}>
              En Vivo
            </span>
          </div>

          <h3 className={styles.channelTitle}>Sesiones en Vivo por Zoom</h3>
          <p className={styles.channelDesc}>
            Mentorías grupales semanales, resolución de dudas en directo y masterclasses exclusivas con expertos invitados.
          </p>

          <div className={styles.channelMetaList}>
            <div className={styles.channelMetaItem}>
              <CheckCircle2 size={14} className={styles.channelCheckIcon} />
              <span>Interacción en directo con instructores</span>
            </div>
            <div className={styles.channelMetaItem}>
              <CheckCircle2 size={14} className={styles.channelCheckIcon} />
              <span>Grabaciones resubidas a la plataforma</span>
            </div>
          </div>

          <a
            href={zoomUrl}
            target="_blank"
            rel="noopener noreferrer"
            className={styles.channelActionBtn}
            style={{ background: "#2D8CFF", color: "#ffffff" }}
          >
            <span>Acceder a la Sala de Zoom</span>
            <ExternalLink size={15} />
          </a>
        </div>
      </div>

      {/* 3. CALENDARIO DE SESIONES & NORMAS DE CONVIVENCIA */}
      <div className={styles.communityBottomGrid}>
        {/* Próximas Sesiones */}
        <div className={styles.communityInfoCard}>
          <div className={styles.cardHeaderRow}>
            <Calendar size={18} className={styles.infoCardIcon} />
            <h4 className={styles.infoCardTitle}>Horarios de Mentorías en Vivo</h4>
          </div>
          <div className={styles.scheduleList}>
            <div className={styles.scheduleItem}>
              <div className={styles.scheduleTime}>
                <Clock size={13} />
                <span>Jueves • 19:00 hrs (GMT-5)</span>
              </div>
              <span className={styles.scheduleName}>Sesión de Preguntas & Respuestas en Vivo</span>
            </div>
            <div className={styles.scheduleItem}>
              <div className={styles.scheduleTime}>
                <Clock size={13} />
                <span>Sábados • 11:00 hrs (GMT-5)</span>
              </div>
              <span className={styles.scheduleName}>Workshop Práctico & Revisión de Proyectos</span>
            </div>
          </div>
        </div>

        {/* Normas de la Comunidad */}
        <div className={styles.communityInfoCard}>
          <div className={styles.cardHeaderRow}>
            <ShieldCheck size={18} className={styles.infoCardIcon} />
            <h4 className={styles.infoCardTitle}>Normas de Convivencia</h4>
          </div>
          <ul className={styles.rulesList}>
            <li>Trato cordial, empático y respetuoso con todos los compañeros y mentores.</li>
            <li>No se permite spam, ofertas comerciales no autorizadas ni enlaces de afiliado.</li>
            <li>Comparte dudas con claridad para que el resto de la comunidad también aprenda.</li>
            <li>El contenido compartido dentro de los canales privados es estrictamente confidencial.</li>
          </ul>
        </div>
      </div>
    </div>
  );
};
