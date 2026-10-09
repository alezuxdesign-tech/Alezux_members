import { useState, useEffect } from "react";
import { Zap, Send, Check, Eye } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { Switch } from "../components/arc/switch/switch";
import { Input } from "../components/arc/input/input";
import { api, MarketingAutomation } from "../services/api";
import styles from "./MarketingView.module.css";

export function MarketingView() {
  const [automations, setAutomations] = useState<MarketingAutomation[]>([]);
  const [selectedAuto, setSelectedAuto] = useState<MarketingAutomation | null>(null);
  const [testSent, setTestSent] = useState(false);
  const [isSendingTest, setIsSendingTest] = useState(false);

  useEffect(() => {
    api.getAutomations().then(setAutomations);
  }, []);

  const handleToggle = (id: string, currentEnabled: boolean) => {
    setAutomations(
      automations.map((a) => (a.id === id ? { ...a, enabled: !currentEnabled } : a))
    );
  };

  const handleSendTest = () => {
    setIsSendingTest(true);
    setTimeout(() => {
      setIsSendingTest(false);
      setTestSent(true);
      setTimeout(() => setTestSent(false), 2500);
    }, 800);
  };

  return (
    <div className={styles.container}>
      {/* Header */}
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Marketing & Automatizaciones de Email</h1>
          <p className={styles.subtitle}>
            Configura correos basados en eventos para retener alumnos, avisar cuotas pendientes y maximizar graduaciones.
          </p>
        </div>
      </div>

      {/* Grid de Automatizaciones */}
      <div className={styles.automationsGrid}>
        {automations.map((auto) => (
          <div key={auto.id} className={styles.card}>
            <div className={styles.cardTop}>
              <div className={styles.triggerBadge}>
                <Zap size={14} className={styles.zapIcon} />
                <span>{auto.triggerEvent}</span>
              </div>
              <Switch
                checked={auto.enabled}
                onCheckedChange={() => handleToggle(auto.id, auto.enabled)}
                aria-label={`Activar automatización ${auto.name}`}
              />
            </div>

            <h3 className={styles.cardTitle}>{auto.name}</h3>
            <p className={styles.subjectText}>
              <span className={styles.subjectLabel}>Asunto:</span> &ldquo;{auto.subject}&rdquo;
            </p>

            <div className={styles.statsRow}>
              <span className={styles.statLabel}>Enviados en total:</span>
              <Badge variant="neutral" size="sm">
                <span className={styles.tabularNums}>{auto.sentCount.toLocaleString()}</span> correos
              </Badge>
            </div>

            <div className={styles.cardActions}>
              <Button variant="secondary" size="sm" onClick={() => setSelectedAuto(auto)}>
                <Eye size={14} /> Ver Plantilla
              </Button>
            </div>
          </div>
        ))}
      </div>

      {/* Modal de Previsualización y Envío de Prueba */}
      <Modal
        isOpen={!!selectedAuto}
        onClose={() => setSelectedAuto(null)}
        title={selectedAuto ? selectedAuto.name : ""}
        description="Plantilla de correo automático con variables dinámicas de personalización."
        footer={
          <>
            {testSent && (
              <span className={styles.testSuccessNotice}>
                <Check size={16} /> Correo de prueba enviado
              </span>
            )}
            <Button variant="ghost" onClick={() => setSelectedAuto(null)}>
              Cerrar
            </Button>
            <Button variant="secondary" loading={isSendingTest} onClick={handleSendTest}>
              <Send size={14} /> Enviar Prueba
            </Button>
            <Button variant="primary" onClick={() => setSelectedAuto(null)}>
              Guardar Plantilla
            </Button>
          </>
        }
      >
        {selectedAuto && (
          <div className={styles.templateEditor}>
            <div className={styles.formGroup}>
              <Input
                label="Línea de Asunto"
                defaultValue={selectedAuto.subject}
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label}>Cuerpo del Mensaje</label>
              <textarea
                rows={6}
                defaultValue={selectedAuto.body}
                className={styles.textarea}
              />
            </div>

            <div className={styles.tagsHint}>
              <span>Variables admitidas:</span>
              <code>{"{user_name}"}</code>
              <code>{"{user_email}"}</code>
              <code>{"{course_title}"}</code>
              <code>{"{amount}"}</code>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}
