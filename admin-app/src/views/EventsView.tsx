import React, { useState, useEffect } from "react";
import {
  Calendar,
  Clock,
  Plus,
  Search,
  Video,
  Trash2,
  Edit3,
  ExternalLink,
  Users,
  CheckCircle2,
  AlertTriangle
} from "lucide-react";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Input } from "../components/arc/input/input";
import { Switch } from "../components/arc/switch/switch";
import { Alert } from "../components/arc/alert/alert";
import { api, AcademyEvent, Instructor } from "../services/api";
import styles from "./EventsView.module.css";

interface ToastState {
  id: number;
  tone: "info" | "success" | "warning" | "danger";
  title: string;
  description?: string;
}

export function EventsView() {
  const [events, setEvents] = useState<AcademyEvent[]>([]);
  const [instructors, setInstructors] = useState<Instructor[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Filtros
  const [searchQuery, setSearchQuery] = useState("");
  const [filterMode, setFilterMode] = useState<"all" | "upcoming" | "live" | "past">("all");

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

  // Modal Crear / Editar
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingEvent, setEditingEvent] = useState<AcademyEvent | null>(null);

  // Campos de formulario
  const [formTitle, setFormTitle] = useState("");
  const [formDescription, setFormDescription] = useState("");
  const [formDate, setFormDate] = useState("");
  const [formTime, setFormTime] = useState("19:00");
  const [formTimezone, setFormTimezone] = useState("GMT-5");
  const [formType, setFormType] = useState<"live" | "workshop" | "qna" | "webinar">("live");
  const [formBadge, setFormBadge] = useState("En Vivo");
  const [formMeetingUrl, setFormMeetingUrl] = useState("");
  const [formInstructorId, setFormInstructorId] = useState("");
  const [formIsActive, setFormIsActive] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  // Modal Confirmación de Eliminación
  const [deleteConfirm, setDeleteConfirm] = useState<{ id: string; title: string } | null>(null);

  // Carga de datos
  const loadData = async () => {
    setIsLoading(true);
    try {
      const [eventsData, instData] = await Promise.all([
        api.getAcademyEvents(),
        api.getInstructors(),
      ]);
      setEvents(eventsData || []);
      setInstructors(instData.instructors || []);
    } catch (e) {
      console.warn("Error loading events", e);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const openCreateModal = () => {
    setEditingEvent(null);
    setFormTitle("");
    setFormDescription("");
    const futureDate = new Date(Date.now() + 2 * 86400000).toISOString().split("T")[0];
    setFormDate(futureDate);
    setFormTime("19:00");
    setFormTimezone("GMT-5");
    setFormType("live");
    setFormBadge("En Vivo");
    setFormMeetingUrl("https://zoom.us/j/");
    setFormInstructorId(instructors[0]?.id || "");
    setFormIsActive(true);
    setIsModalOpen(true);
  };

  const openEditModal = (evt: AcademyEvent) => {
    setEditingEvent(evt);
    setFormTitle(evt.title || "");
    setFormDescription(evt.description || "");
    setFormDate(evt.date || "");
    setFormTime(evt.time || "19:00");
    setFormTimezone(evt.timezone || "GMT-5");
    setFormType(evt.type || "live");
    setFormBadge(evt.badge || "En Vivo");
    setFormMeetingUrl(evt.meeting_url || "");
    const matchingInst = instructors.find((i) => i.name === evt.instructor);
    setFormInstructorId(matchingInst ? matchingInst.id : "");
    setFormIsActive(evt.is_active !== false);
    setIsModalOpen(true);
  };

  const handleSaveEvent = async () => {
    if (!formTitle.trim()) {
      showToast("warning", "Título requerido", "Ingresa el nombre del evento o clase en vivo.");
      return;
    }
    if (!formDate) {
      showToast("warning", "Fecha requerida", "Selecciona la fecha programada para la sesión.");
      return;
    }

    setIsSaving(true);
    const selectedInst = instructors.find((i) => i.id === formInstructorId);

    const eventItem: AcademyEvent = {
      id: editingEvent ? editingEvent.id : `evt-${Date.now()}`,
      title: formTitle.trim(),
      description: formDescription.trim(),
      date: formDate,
      time: formTime,
      timezone: formTimezone,
      type: formType,
      badge: formBadge || (formType === "live" ? "En Vivo" : formType === "workshop" ? "Taller Práctico" : "Masterclass"),
      meeting_url: formMeetingUrl.trim(),
      instructor: selectedInst ? selectedInst.name : "Equipo Docente",
      instructor_avatar: selectedInst?.avatar || "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80",
      is_active: formIsActive,
    };

    let updatedList: AcademyEvent[];
    if (editingEvent) {
      updatedList = events.map((e) => (e.id === editingEvent.id ? eventItem : e));
    } else {
      updatedList = [eventItem, ...events];
    }

    try {
      await api.saveAcademyEvents(updatedList);
      setEvents(updatedList);
      setIsModalOpen(false);
      showToast(
        "success",
        editingEvent ? "Evento actualizado" : "Evento programado",
        `"${eventItem.title}" guardado en la base de datos de la academia.`
      );
    } catch {
      showToast("danger", "Error", "No se pudo guardar el evento.");
    } finally {
      setIsSaving(false);
    }
  };

  const executeDeleteEvent = async () => {
    if (!deleteConfirm) return;
    const updatedList = events.filter((e) => e.id !== deleteConfirm.id);
    try {
      await api.saveAcademyEvents(updatedList);
      setEvents(updatedList);
      setDeleteConfirm(null);
      showToast("info", "Evento eliminado", `Se removió "${deleteConfirm.title}" del calendario.`);
    } catch {
      showToast("danger", "Error", "No se pudo eliminar el evento.");
    }
  };

  // Filtrado
  const filteredEvents = events.filter((evt) => {
    const matchesSearch =
      evt.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (evt.description && evt.description.toLowerCase().includes(searchQuery.toLowerCase())) ||
      (evt.instructor && evt.instructor.toLowerCase().includes(searchQuery.toLowerCase()));

    if (!matchesSearch) return false;

    const todayStr = new Date().toISOString().split("T")[0];
    if (filterMode === "upcoming") return evt.date >= todayStr;
    if (filterMode === "live") return evt.date === todayStr;
    if (filterMode === "past") return evt.date < todayStr;
    return true;
  });

  const nextUpcomingEvent = events
    .filter((e) => e.date >= new Date().toISOString().split("T")[0] && e.is_active !== false)
    .sort((a, b) => a.date.localeCompare(b.date))[0];

  const formatDisplayDate = (dateStr: string, timeStr: string) => {
    try {
      const [year, month, day] = dateStr.split("-").map(Number);
      const d = new Date(year, month - 1, day);
      const dayName = d.toLocaleDateString("es-ES", { weekday: "short" });
      const monthName = d.toLocaleDateString("es-ES", { month: "short" });
      const capDay = dayName.charAt(0).toUpperCase() + dayName.slice(1);
      return `${capDay} ${day} ${monthName} · ${timeStr} hrs`;
    } catch {
      return `${dateStr} · ${timeStr}`;
    }
  };

  return (
    <div className={styles.container}>
      {/* Toast Alert */}
      {toast && (
        <div style={{ position: "fixed", top: 20, right: 24, zIndex: 9999 }}>
          <Alert tone={toast.tone} title={toast.title}>{toast.description}</Alert>
        </div>
      )}

      {/* Page Header */}
      <div className={styles.pageHeader}>
        <div>
          <h1 className={styles.pageTitle}>Eventos & Clases en Vivo</h1>
          <p className={styles.pageDescription}>
            Programa masterclasses, mentorías grupales y transmisiones de Zoom para tus estudiantes.
          </p>
        </div>
        <Button variant="primary" size="md" onClick={openCreateModal}>
          <Plus size={16} /> Agendar Nuevo Evento
        </Button>
      </div>

      {/* KPI Cards (Exactamente como en OverviewView del Administrador) */}
      <div className={styles.metricsGrid}>
        <MetricCard
          label="Sesiones Programadas"
          value={events.length}
          context="Total de eventos agendados en la academia"
          icon={<Calendar size={16} />}
        />
        <MetricCard
          label="Próxima Sesión"
          value={nextUpcomingEvent ? 1 : 0}
          suffix={nextUpcomingEvent ? `(${nextUpcomingEvent.time})` : ""}
          context={nextUpcomingEvent ? `${nextUpcomingEvent.date} · ${nextUpcomingEvent.title.slice(0, 26)}...` : "Sin sesiones próximas agendadas"}
          icon={<Clock size={16} />}
        />
        <MetricCard
          label="Mentorías y Talleres"
          value={events.filter((e) => e.type === "workshop" || e.type === "qna").length}
          context="Sesiones prácticas e interactivas"
          icon={<Users size={16} />}
        />
        <MetricCard
          label="Transmisiones en Vivo"
          value={events.filter((e) => e.type === "live").length}
          context="Clases en directo y webinars"
          icon={<Video size={16} />}
        />
      </div>

      {/* Filtros y Buscador */}
      <div className={styles.filterBar}>
        <div className={styles.searchInputWrap}>
          <Search size={16} className={styles.searchIcon} />
          <input
            type="text"
            className={styles.searchInput}
            placeholder="Buscar evento por título, tema o docente..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        <div className={styles.tabsGroup}>
          <button
            type="button"
            className={`${styles.tabBtn} ${filterMode === "all" ? styles.tabBtnActive : ""}`}
            onClick={() => setFilterMode("all")}
          >
            Todos ({events.length})
          </button>
          <button
            type="button"
            className={`${styles.tabBtn} ${filterMode === "upcoming" ? styles.tabBtnActive : ""}`}
            onClick={() => setFilterMode("upcoming")}
          >
            Próximos
          </button>
          <button
            type="button"
            className={`${styles.tabBtn} ${filterMode === "live" ? styles.tabBtnActive : ""}`}
            onClick={() => setFilterMode("live")}
          >
            Hoy
          </button>
          <button
            type="button"
            className={`${styles.tabBtn} ${filterMode === "past" ? styles.tabBtnActive : ""}`}
            onClick={() => setFilterMode("past")}
          >
            Pasados
          </button>
        </div>
      </div>

      {/* Grid de Tarjetas de Eventos */}
      {filteredEvents.length > 0 ? (
        <div className={styles.eventsGrid}>
          {filteredEvents.map((evt) => (
            <div key={evt.id} className={styles.eventCard}>
              <div className={styles.eventCardHeader}>
                <div className={styles.eventDateRow}>
                  <Calendar size={14} />
                  <span>{formatDisplayDate(evt.date, evt.time)}</span>
                </div>
                <Badge variant={evt.is_active !== false ? "accent" : "neutral"} size="sm">
                  {evt.badge || "En Vivo"}
                </Badge>
              </div>

              <h3 className={styles.eventCardTitle}>{evt.title}</h3>
              <p className={styles.eventCardDesc}>
                {evt.description || "Sesión formativa en tiempo real para alumnos de la academia."}
              </p>

              {evt.instructor && (
                <div className={styles.eventInstructorRow}>
                  {evt.instructor_avatar ? (
                    <img src={evt.instructor_avatar} alt={evt.instructor} className={styles.eventInstructorAvatar} />
                  ) : (
                    <div style={{ width: 28, height: 28, borderRadius: "50%", background: "var(--border)", display: "grid", placeItems: "center" }}>
                      <Users size={14} />
                    </div>
                  )}
                  <span className={styles.eventInstructorName}>
                    Docente: <strong>{evt.instructor}</strong>
                  </span>
                </div>
              )}

              <div className={styles.eventCardFooter}>
                {evt.meeting_url ? (
                  <a
                    href={evt.meeting_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className={styles.eventMeetingLink}
                    title="Probar sala de Zoom / Enlace de reunión"
                  >
                    <Video size={14} />
                    <span>Abrir Sala Zoom</span>
                    <ExternalLink size={12} />
                  </a>
                ) : (
                  <span style={{ fontSize: 12, color: "var(--text-muted)" }}>Sin enlace configurado</span>
                )}

                <div className={styles.cardActions}>
                  <Button variant="secondary" size="sm" onClick={() => openEditModal(evt)}>
                    <Edit3 size={13} />
                  </Button>
                  <Button variant="ghost" size="sm" onClick={() => setDeleteConfirm({ id: evt.id, title: evt.title })}>
                    <Trash2 size={13} />
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className={styles.emptyState}>
          <Calendar size={40} style={{ opacity: 0.5 }} />
          <h4 style={{ margin: 0, color: "var(--foreground)" }}>No se encontraron eventos programados</h4>
          <p style={{ margin: 0, fontSize: 13, maxWidth: 400 }}>
            {searchQuery
              ? "No hay resultados que coincidan con la búsqueda."
              : "Agenda mentorías en vivo o clases para que aparezcan en el panel de los estudiantes."}
          </p>
          <Button variant="secondary" size="sm" onClick={openCreateModal} style={{ marginTop: 8 }}>
            <Plus size={14} /> Programar Primera Sesión
          </Button>
        </div>
      )}

      {/* Modal Crear / Editar Evento */}
      {isModalOpen && (
        <Modal
          isOpen={true}
          onClose={() => setIsModalOpen(false)}
          title={editingEvent ? "Editar Sesión en Vivo" : "Agendar Nueva Sesión en Vivo"}
          maxWidth="560px"
          footer={
            <>
              <Button variant="ghost" onClick={() => setIsModalOpen(false)}>
                Cancelar
              </Button>
              <Button variant="primary" onClick={handleSaveEvent} loading={isSaving} disabled={isSaving}>
                <CheckCircle2 size={15} /> {editingEvent ? "Guardar Cambios" : "Programar Evento"}
              </Button>
            </>
          }
        >
          <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
            <Input
              label="Título del Evento / Sesión *"
              placeholder="Ej: Mentoría Grupal: Preguntas & Respuestas"
              value={formTitle}
              onChange={(e) => setFormTitle(e.target.value)}
            />

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                <label style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Fecha *</label>
                <input
                  type="date"
                  value={formDate}
                  onChange={(e) => setFormDate(e.target.value)}
                  style={{
                    height: 40,
                    background: "var(--background)",
                    color: "var(--foreground)",
                    border: "1px solid var(--border)",
                    borderRadius: "var(--radius-control)",
                    padding: "0 12px",
                    outline: "none",
                    fontSize: 13,
                  }}
                />
              </div>

              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                <label style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Hora y Zona *</label>
                <div style={{ display: "flex", gap: 6 }}>
                  <input
                    type="time"
                    value={formTime}
                    onChange={(e) => setFormTime(e.target.value)}
                    style={{
                      height: 40,
                      flex: 1,
                      background: "var(--background)",
                      color: "var(--foreground)",
                      border: "1px solid var(--border)",
                      borderRadius: "var(--radius-control)",
                      padding: "0 10px",
                      outline: "none",
                      fontSize: 13,
                    }}
                  />
                  <input
                    type="text"
                    value={formTimezone}
                    onChange={(e) => setFormTimezone(e.target.value)}
                    placeholder="GMT-5"
                    style={{
                      width: 74,
                      height: 40,
                      background: "var(--background)",
                      color: "var(--foreground)",
                      border: "1px solid var(--border)",
                      borderRadius: "var(--radius-control)",
                      padding: "0 8px",
                      outline: "none",
                      fontSize: 12,
                      textAlign: "center",
                    }}
                  />
                </div>
              </div>
            </div>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                <label style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Docente / Anfitrión</label>
                <select
                  value={formInstructorId}
                  onChange={(e) => setFormInstructorId(e.target.value)}
                  style={{
                    height: 40,
                    background: "var(--background)",
                    color: "var(--foreground)",
                    border: "1px solid var(--border)",
                    borderRadius: "var(--radius-control)",
                    padding: "0 12px",
                    outline: "none",
                    fontSize: 13,
                  }}
                >
                  <option value="">-- Seleccionar Docente --</option>
                  {instructors.map((inst) => (
                    <option key={inst.id} value={inst.id}>
                      {inst.name} {inst.isAdmin ? "(Admin)" : ""}
                    </option>
                  ))}
                </select>
              </div>

              <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                <label style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Tipo de Sesión</label>
                <select
                  value={formType}
                  onChange={(e) => {
                    const t = e.target.value as any;
                    setFormType(t);
                    setFormBadge(t === "live" ? "En Vivo" : t === "workshop" ? "Taller Práctico" : t === "qna" ? "Preguntas & Respuestas" : "Webinar");
                  }}
                  style={{
                    height: 40,
                    background: "var(--background)",
                    color: "var(--foreground)",
                    border: "1px solid var(--border)",
                    borderRadius: "var(--radius-control)",
                    padding: "0 12px",
                    outline: "none",
                    fontSize: 13,
                  }}
                >
                  <option value="live">En Vivo (Masterclass / Clase)</option>
                  <option value="workshop">Taller Práctico / Lab</option>
                  <option value="qna">Preguntas & Respuestas (Q&A)</option>
                  <option value="webinar">Webinar Abierto</option>
                </select>
              </div>
            </div>

            <Input
              label="Enlace de Reunión de Zoom / Meet / Transmisión *"
              placeholder="https://zoom.us/j/123456789 o enlace de Google Meet"
              value={formMeetingUrl}
              onChange={(e) => setFormMeetingUrl(e.target.value)}
              hint="Los alumnos podrán unirse directamente con este enlace desde su Dashboard."
            />

            <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
              <label style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)" }}>Descripción / Temario de la sesión</label>
              <textarea
                value={formDescription}
                onChange={(e) => setFormDescription(e.target.value)}
                placeholder="Explica a los alumnos qué temas se abordarán en esta sesión en tiempo real..."
                rows={3}
                style={{
                  background: "var(--background)",
                  color: "var(--foreground)",
                  border: "1px solid var(--border)",
                  borderRadius: "var(--radius-control)",
                  padding: "10px 12px",
                  outline: "none",
                  fontSize: 13,
                  fontFamily: "inherit",
                  resize: "vertical",
                }}
              />
            </div>

            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", padding: "10px 14px", background: "var(--surface-muted)", borderRadius: "var(--radius-control)", border: "1px solid var(--border-subtle)" }}>
              <div>
                <span style={{ fontSize: 13, fontWeight: 600, color: "var(--foreground)", display: "block" }}>
                  Visible en Campus de Estudiantes
                </span>
                <span style={{ fontSize: 11, color: "var(--text-muted)" }}>
                  Si está activo, aparecerá en el dashboard de los alumnos.
                </span>
              </div>
              <Switch
                checked={formIsActive}
                onCheckedChange={setFormIsActive}
                id="event-active-switch"
              />
            </div>
          </div>
        </Modal>
      )}

      {/* Modal Confirmación de Eliminación */}
      {deleteConfirm && (
        <Modal
          isOpen={true}
          onClose={() => setDeleteConfirm(null)}
          title="Eliminar Evento"
          maxWidth="440px"
          footer={
            <>
              <Button variant="ghost" onClick={() => setDeleteConfirm(null)}>
                Cancelar
              </Button>
              <Button variant="danger" onClick={executeDeleteEvent}>
                <Trash2 size={15} /> Confirmar Eliminación
              </Button>
            </>
          }
        >
          <div style={{ display: "flex", gap: 14, alignItems: "flex-start", padding: "8px 0" }}>
            <div style={{ width: 40, height: 40, borderRadius: "50%", background: "rgba(239, 68, 68, 0.12)", color: "var(--danger)", display: "grid", placeItems: "center", flexShrink: 0 }}>
              <AlertTriangle size={20} />
            </div>
            <div>
              <p style={{ margin: 0, fontSize: 14, color: "var(--foreground)", lineHeight: 1.5 }}>
                ¿Estás seguro de que deseas eliminar la sesión <strong>"{deleteConfirm.title}"</strong> del calendario de la academia?
              </p>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
