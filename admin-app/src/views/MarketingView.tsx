import { useState, useEffect, useMemo } from "react";
import { Zap, Send, Check, Eye, Mail, Search, Copy, CheckCheck } from "lucide-react";
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

export function MarketingView() {
  const [automations, setAutomations] = useState<MarketingAutomation[]>([]);
  const [activeFilter, setActiveFilter] = useState<CategoryFilter>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedAuto, setSelectedAuto] = useState<MarketingAutomation | null>(null);

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

  const handleOpenModal = (auto: MarketingAutomation) => {
    setSelectedAuto(auto);
    setEditSubject(auto.subject);
    setEditBody(auto.body);
    setEditEnabled(auto.enabled);
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

                <Button
                  variant="secondary"
                  size="sm"
                  onClick={() => handleOpenModal(auto)}
                >
                  <Eye size={14} /> Editar Plantilla
                </Button>
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
        maxWidth="680px"
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
            {/* Estado Activo / Inactivo */}
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
