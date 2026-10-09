import { useState, useEffect, useMemo } from "react";
import { Zap, Send, Check, Eye, Mail, Search, Copy, CheckCheck, FileCode } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Switch } from "../components/arc/switch/switch";
import { Input } from "../components/arc/input/input";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { SegmentedControl, SegmentOption } from "../components/arc/segmented-control/segmented-control";
import { api, MarketingAutomation } from "../services/api";
import styles from "./MarketingView.module.css";

type CategoryFilter = "all" | "registro" | "finanzas" | "cursos" | "logros";

const CATEGORY_LABELS: Record<string, { label: string; variant: "neutral" | "accent" | "success" | "warning" | "danger" }> = {
  registro: { label: "Registro & Seguridad", variant: "neutral" },
  finanzas: { label: "Finanzas & Cobros", variant: "success" },
  cursos: { label: "Cursos & Lecciones", variant: "accent" },
  logros: { label: "Logros & Retención", variant: "warning" },
};

function renderSampleText(text: string): string {
  const currentYear = new Date().getFullYear().toString();
  const replacements: Record<string, string> = {
    "{{site_name}}": "Academia Crezca",
    "{{user.name}}": "Carlos Mendoza",
    "{{user.first_name}}": "Carlos",
    "{{user.username}}": "carlos_mendoza",
    "{{user.email}}": "carlos.mendoza@empresa.com",
    "{{course_title}}": "Master en Marketing Digital & Performance",
    "{{course_name}}": "Master en Marketing Digital & Performance",
    "{{plan_name}}": "Plan Pro Anual VIP (4 Cuotas)",
    "{{price}}": "$97 USD",
    "{{amount}}": "$97 USD",
    "{{date}}": "09/10/2026",
    "{{renewal_date}}": "15/11/2026",
    "{{end_date}}": "31/12/2026",
    "{{achievement_name}}": "Insignia de Constancia Pro",
    "{{days_inactive}}": "7",
    "{{year}}": currentYear,
    "{user_name}": "Carlos Mendoza",
    "{user_email}": "carlos.mendoza@empresa.com",
    "{course_title}": "Master en Marketing Digital & Performance",
    "{amount}": "$97 USD",
  };

  let rendered = text || "";
  for (const [key, value] of Object.entries(replacements)) {
    rendered = rendered.split(key).join(value);
  }
  return rendered;
}

function renderSampleEmail(content: string): string {
  const logoSvg =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='160' height='40' viewBox='0 0 160 40'><rect width='160' height='40' rx='8' fill='%236366f1'/><text x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' fill='%23ffffff' font-family='sans-serif' font-weight='bold' font-size='16'>CREZCA</text></svg>";

  const trophySvg =
    "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='90' height='90' viewBox='0 0 90 90'><circle cx='45' cy='45' r='40' fill='%23fef3c7' stroke='%23f59e0b' stroke-width='3'/><text x='50%25' y='58%25' dominant-baseline='middle' text-anchor='middle' font-size='38'>🏆</text></svg>";

  const currentYear = new Date().getFullYear().toString();

  const replacements: Record<string, string> = {
    "{{logo_url}}": logoSvg,
    "{{site_name}}": "Academia Crezca",
    "{{home_url}}": "#",
    "{{login_url}}": "#",
    "{{year}}": currentYear,
    "{{user.name}}": "Carlos Mendoza",
    "{{user.first_name}}": "Carlos",
    "{{user.username}}": "carlos_mendoza",
    "{{user.email}}": "carlos.mendoza@empresa.com",
    "{{password}}": "••••••••••••",
    "{{new_password}}": "••••••••••••",
    "{{reset_link}}": "#",
    "{{course_title}}": "Master en Marketing Digital & Performance",
    "{{course_name}}": "Master en Marketing Digital & Performance",
    "{{courses_list}}":
      "<ul style='margin: 0; padding-left: 20px; line-height: 1.8;'><li><a href='#' style='color: #4f46e5; font-weight: 600;'>Módulo Avanzado de Funnels y Automatización</a></li><li><a href='#' style='color: #4f46e5; font-weight: 600;'>Copywriting y Cierre High-Ticket</a></li></ul>",
    "{{lessons_list}}":
      "<ul style='margin: 0; padding-left: 20px; line-height: 1.8;'><li><a href='#' style='color: #4f46e5;'>Clase 1: Configuración de Campañas de Alto Impacto</a></li><li><a href='#' style='color: #4f46e5;'>Clase 2: Métricas y Optimización de ROAS</a></li></ul>",
    "{{plan.name}}": "Plan Pro Anual VIP (4 Cuotas)",
    "{{plan_name}}": "Plan Pro Anual VIP (4 Cuotas)",
    "{{payment.amount}}": "97",
    "{{payment.currency}}": "USD",
    "{{payment.ref}}": "TX-89421-CRZ",
    "{{price}}": "$97 USD",
    "{{amount}}": "$97 USD",
    "{{date}}": "09/10/2026",
    "{{renewal_date}}": "15/11/2026",
    "{{attempt_date}}": "09/10/2026",
    "{{retry_url}}": "#",
    "{{end_date}}": "31/12/2026",
    "{{achievement.image}}": trophySvg,
    "{{achievement.title}}": "Insignia de Constancia Pro",
    "{{achievement.message}}": "¡Has completado con éxito 10 clases seguidas esta semana!",
    "{{achievement_name}}": "Insignia de Constancia Pro",
    "{{achievement_desc}}": "¡Has completado con éxito 10 clases seguidas esta semana!",
    "{{days_inactive}}": "7",
    "{user_name}": "Carlos Mendoza",
    "{user_email}": "carlos.mendoza@empresa.com",
    "{course_title}": "Master en Marketing Digital & Performance",
    "{amount}": "$97 USD",
  };

  let rendered = content || "";
  for (const [key, value] of Object.entries(replacements)) {
    rendered = rendered.split(key).join(value);
  }

  // Si no incluye etiquetas <html> o <body> completas, envolver en un contenedor estilizado
  if (!rendered.includes("<html") && !rendered.includes("<body")) {
    rendered = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
          body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
          .container { max-width: 580px; margin: 0 auto; background: #ffffff; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07); border: 1px solid #e2e8f0; }
          .header { text-align: center; margin-bottom: 24px; }
          .header img { max-width: 150px; }
          .btn { display: inline-block; padding: 12px 24px; background: #4f46e5; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 16px 0; }
          .footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center; }
          .content { line-height: 1.6; font-size: 15px; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <img src="${logoSvg}" alt="Crezca">
          </div>
          <div class="content">
            ${rendered.replace(/\n/g, "<br />")}
          </div>
          <div class="footer">
            <p>&copy; ${currentYear} Academia Crezca. Todos los derechos reservados.</p>
          </div>
        </div>
      </body>
      </html>
    `;
  }

  return rendered;
}

export function MarketingView() {
  const [automations, setAutomations] = useState<MarketingAutomation[]>([]);
  const [activeFilter, setActiveFilter] = useState<CategoryFilter>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedAuto, setSelectedAuto] = useState<MarketingAutomation | null>(null);

  // Switch de modo de vista: Vista Previa vs Editor HTML
  const [isPreviewMode, setIsPreviewMode] = useState(false);

  // Form states for modal
  const [editSubject, setEditSubject] = useState("");
  const [editBody, setEditBody] = useState("");
  const [editEnabled, setEditEnabled] = useState(true);
  const [testEmail, setTestEmail] = useState("");
  const [copiedVar, setCopiedVar] = useState<string | null>(null);

  // Actions loading/feedback
  const [isSaving, setIsSaving] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);
  const [isSendingTest, setIsSendingTest] = useState(false);
  const [testSuccess, setTestSuccess] = useState(false);

  useEffect(() => {
    api.getAutomations().then((data) => {
      setAutomations(data);
    });
  }, []);

  const handleToggle = async (id: string, currentEnabled: boolean) => {
    const nextState = !currentEnabled;
    setAutomations((prev) =>
      prev.map((a) => (a.id === id ? { ...a, enabled: nextState } : a))
    );
    await api.toggleAutomation(id, nextState);
  };

  const handleOpenModal = (auto: MarketingAutomation, startWithPreview: boolean = false) => {
    setSelectedAuto(auto);
    setEditSubject(auto.subject);
    setEditBody(auto.body);
    setEditEnabled(auto.enabled);
    setIsPreviewMode(startWithPreview);
    setTestEmail("");
    setCopiedVar(null);
    setSaveSuccess(false);
    setTestSuccess(false);
  };

  const handleSaveModal = async () => {
    if (!selectedAuto) return;
    setIsSaving(true);
    const ok = await api.saveAutomation(selectedAuto.id, {
      subject: editSubject,
      body: editBody,
      enabled: editEnabled,
    });
    setIsSaving(false);
    if (ok) {
      setAutomations((prev) =>
        prev.map((a) =>
          a.id === selectedAuto.id
            ? { ...a, subject: editSubject, body: editBody, enabled: editEnabled }
            : a
        )
      );
      setSaveSuccess(true);
      setTimeout(() => {
        setSaveSuccess(false);
        setSelectedAuto(null);
      }, 1100);
    }
  };

  const handleSendTest = async () => {
    if (!selectedAuto) return;
    setIsSendingTest(true);
    const res = await api.sendTestEmail(selectedAuto.id, testEmail.trim() || undefined);
    setIsSendingTest(false);
    if (res.success) {
      setTestSuccess(true);
      setTimeout(() => setTestSuccess(false), 3000);
    }
  };

  const handleCopyVar = (variable: string) => {
    navigator.clipboard?.writeText(variable);
    setCopiedVar(variable);
    setTimeout(() => setCopiedVar(null), 1800);
  };

  // KPIs
  const totalAutomations = automations.length;
  const activeCount = automations.filter((a) => a.enabled).length;
  const totalSentCount = useMemo(() => {
    return automations.reduce((acc, a) => acc + (a.sentCount || 0), 0);
  }, [automations]);

  // Filtered list
  const filteredAutomations = useMemo(() => {
    return automations.filter((auto) => {
      const matchesCategory =
        activeFilter === "all" || auto.category === activeFilter;
      const query = searchQuery.toLowerCase().trim();
      const matchesSearch =
        !query ||
        auto.name.toLowerCase().includes(query) ||
        auto.subject.toLowerCase().includes(query) ||
        auto.triggerEvent.toLowerCase().includes(query) ||
        (auto.description && auto.description.toLowerCase().includes(query));

      return matchesCategory && matchesSearch;
    });
  }, [automations, activeFilter, searchQuery]);

  const categoryOptions: SegmentOption<CategoryFilter>[] = [
    { value: "all", label: `Todos (${automations.length})` },
    { value: "registro", label: `Registro (${automations.filter((a) => a.category === "registro").length})` },
    { value: "finanzas", label: `Finanzas (${automations.filter((a) => a.category === "finanzas").length})` },
    { value: "cursos", label: `Cursos (${automations.filter((a) => a.category === "cursos").length})` },
    { value: "logros", label: `Logros (${automations.filter((a) => a.category === "logros").length})` },
  ];

  return (
    <div className={styles.container}>
      {/* Header */}
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Marketing & Automatizaciones de Email</h1>
          <p className={styles.subtitle}>
            Suite completa de correos transaccionales y de retención para tu academia digital.
          </p>
        </div>
      </div>

      {/* Métricas Generales */}
      <div className={styles.metricsGrid}>
        <MetricCard
          label="Total Plantillas"
          value={totalAutomations}
          icon={<Mail size={16} />}
          context="12 eventos automatizados del sistema"
        />
        <MetricCard
          label="Automatizaciones Activas"
          value={activeCount}
          icon={<Zap size={16} />}
          context={`${totalAutomations > 0 ? Math.round((activeCount / totalAutomations) * 100) : 100}% activas en producción`}
        />
        <MetricCard
          label="Correos Enviados"
          value={totalSentCount}
          icon={<Send size={16} />}
          context="Historial acumulado de envíos"
        />
      </div>

      {/* Barra de Filtros & Búsqueda */}
      <div className={styles.filterBar}>
        <SegmentedControl<CategoryFilter>
          options={categoryOptions}
          value={activeFilter}
          onChange={setActiveFilter}
          size="sm"
        />

        <div className={styles.searchWrapper}>
          <Input
            placeholder="Buscar por nombre, asunto o evento..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            leftIcon={<Search size={14} />}
          />
        </div>
      </div>

      {/* Grid de Automatizaciones */}
      <div className={styles.automationsGrid}>
        {filteredAutomations.map((auto) => {
          const catInfo = CATEGORY_LABELS[auto.category] || {
            label: "General",
            variant: "neutral" as const,
          };

          return (
            <div key={auto.id} className={styles.card}>
              <div className={styles.cardHeader}>
                <div className={styles.badgeGroup}>
                  <Badge variant={catInfo.variant} size="sm">
                    {catInfo.label}
                  </Badge>
                  <div className={styles.triggerBadge}>
                    <Zap size={12} className={styles.zapIcon} />
                    <span>{auto.triggerEvent}</span>
                  </div>
                </div>

                <Switch
                  checked={auto.enabled}
                  onCheckedChange={() => handleToggle(auto.id, auto.enabled)}
                  aria-label={`Activar automatización ${auto.name}`}
                />
              </div>

              <div>
                <h3 className={styles.cardTitle}>{auto.name}</h3>
                {auto.description && (
                  <p className={styles.cardDescription}>{auto.description}</p>
                )}
              </div>

              <div className={styles.subjectBox}>
                <span className={styles.subjectLabel}>Asunto:</span>
                <span className={styles.subjectText}>&ldquo;{auto.subject}&rdquo;</span>
              </div>

              <div className={styles.cardFooter}>
                <div className={styles.statsRow}>
                  <span className={styles.statLabel}>Enviados:</span>
                  <Badge variant="neutral" size="sm">
                    <span className={styles.tabularNums}>
                      {auto.sentCount.toLocaleString()}
                    </span>
                  </Badge>
                </div>

                <div className={styles.cardActions}>
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={() => handleOpenModal(auto, true)}
                    title="Previsualizar cómo verá el correo el alumno"
                  >
                    <Eye size={14} /> Vista Previa
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => handleOpenModal(auto, false)}
                    title="Editar asunto y código HTML"
                  >
                    <FileCode size={14} /> Editar
                  </Button>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {filteredAutomations.length === 0 && (
        <div className={styles.emptyState}>
          <Mail size={36} className={styles.emptyIcon} />
          <h3 className={styles.emptyTitle}>No se encontraron automatizaciones</h3>
          <p className={styles.emptySubtitle}>
            Intenta cambiar el filtro de categoría o borrar el texto de búsqueda.
          </p>
        </div>
      )}

      {/* Modal de Edición, Previsualización y Envío de Prueba */}
      <Modal
        isOpen={!!selectedAuto}
        onClose={() => setSelectedAuto(null)}
        title={selectedAuto ? selectedAuto.name : ""}
        description={selectedAuto ? selectedAuto.description : ""}
        maxWidth="740px"
        footer={
          <>
            {saveSuccess && (
              <span className={styles.saveSuccessNotice}>
                <CheckCheck size={16} /> ¡Plantilla guardada!
              </span>
            )}
            {testSuccess && (
              <span className={styles.testSuccessNotice}>
                <Check size={16} /> Correo de prueba enviado
              </span>
            )}
            <Button
              variant="ghost"
              onClick={() => setSelectedAuto(null)}
              disabled={isSaving || isSendingTest}
            >
              Cerrar
            </Button>
            <Button
              variant="secondary"
              loading={isSendingTest}
              onClick={handleSendTest}
            >
              <Send size={14} /> Enviar Prueba
            </Button>
            <Button
              variant="primary"
              loading={isSaving}
              onClick={handleSaveModal}
            >
              Guardar Plantilla
            </Button>
          </>
        }
      >
        {selectedAuto && (
          <div className={styles.modalBody}>
            {/* 1. Estado Activo / Inactivo */}
            <div className={styles.statusToggleRow}>
              <div>
                <span className={styles.statusLabel}>
                  {editEnabled ? "Automatización Activa" : "Automatización Pausada"}
                </span>
                <p className={styles.statusHint}>
                  {editEnabled
                    ? "Los correos se enviarán automáticamente en cuanto ocurra el evento."
                    : "No se enviarán correos mientras esté desactivada."}
                </p>
              </div>
              <Switch
                checked={editEnabled}
                onCheckedChange={setEditEnabled}
                aria-label="Alternar estado de envío"
              />
            </div>

            {/* 2. Switch de Vista Previa vs Editor de Código */}
            <div className={styles.previewToggleRow}>
              <div>
                <span className={styles.previewToggleTitle}>
                  {isPreviewMode ? "Previsualización del Correo (Visual)" : "Editor de Plantilla (Código HTML)"}
                </span>
                <p className={styles.previewToggleDesc}>
                  {isPreviewMode
                    ? "Viendo cómo verá el alumno el correo con estilos y variables de prueba."
                    : "Activa el switch para ver el correo renderizado con diseño y estilos."}
                </p>
              </div>
              <div className={styles.switchWrapper}>
                <span className={styles.switchStateLabel}>
                  {isPreviewMode ? "Vista Previa" : "Código HTML"}
                </span>
                <Switch
                  checked={isPreviewMode}
                  onCheckedChange={setIsPreviewMode}
                  aria-label="Alternar previsualización del correo"
                />
              </div>
            </div>

            {/* MODO VISTA PREVIA */}
            {isPreviewMode ? (
              <div className={styles.previewContainer}>
                {/* Cabecera simulada del cliente de correo */}
                <div className={styles.emailClientBar}>
                  <div className={styles.emailClientField}>
                    <span className={styles.emailClientLabel}>De:</span>
                    <span className={styles.emailClientValue}>Academia Crezca &lt;notificaciones@crezca.com&gt;</span>
                  </div>
                  <div className={styles.emailClientField}>
                    <span className={styles.emailClientLabel}>Para:</span>
                    <span className={styles.emailClientValue}>Carlos Mendoza &lt;carlos.mendoza@empresa.com&gt;</span>
                  </div>
                  <div className={styles.emailClientField}>
                    <span className={styles.emailClientLabel}>Asunto:</span>
                    <span className={styles.emailClientSubject}>
                      {renderSampleText(editSubject)}
                    </span>
                  </div>
                </div>

                {/* Iframe que aísla los estilos CSS del email */}
                <div className={styles.iframeBox}>
                  <iframe
                    title="Previsualización de Correo"
                    srcDoc={renderSampleEmail(editBody)}
                    className={styles.emailIframe}
                    sandbox="allow-same-origin"
                  />
                </div>
              </div>
            ) : (
              /* MODO EDITOR DE CÓDIGO HTML */
              <>
                {/* Asunto */}
                <div className={styles.formGroup}>
                  <Input
                    label="Línea de Asunto"
                    value={editSubject}
                    onChange={(e) => setEditSubject(e.target.value)}
                  />
                </div>

                {/* Contenido / Cuerpo */}
                <div className={styles.formGroup}>
                  <label className={styles.label}>Cuerpo del Mensaje (HTML o Texto)</label>
                  <textarea
                    rows={9}
                    value={editBody}
                    onChange={(e) => setEditBody(e.target.value)}
                    className={styles.textarea}
                    placeholder="Escribe el contenido del correo..."
                  />
                </div>

                {/* Variables Dinámicas */}
                {selectedAuto.variables && selectedAuto.variables.length > 0 && (
                  <div className={styles.variablesSection}>
                    <div className={styles.variablesHeader}>
                      <span>Variables dinámicas disponibles (clic para copiar):</span>
                      {copiedVar && (
                        <span className={styles.copiedHint}>
                          <Copy size={12} /> ¡Copiada al portapapeles!
                        </span>
                      )}
                    </div>
                    <div className={styles.variablesPills}>
                      {selectedAuto.variables.map((v) => (
                        <button
                          key={v}
                          type="button"
                          className={styles.varPill}
                          onClick={() => handleCopyVar(v)}
                          title="Clic para copiar variable"
                        >
                          {v}
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </>
            )}

            {/* Enviar Prueba a email personalizado */}
            <div className={styles.testSection}>
              <div className={styles.testInputWrapper}>
                <Input
                  label="Destinatario de Prueba (Opcional)"
                  placeholder="admin@tuacademia.com"
                  value={testEmail}
                  onChange={(e) => setTestEmail(e.target.value)}
                  type="email"
                />
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}
