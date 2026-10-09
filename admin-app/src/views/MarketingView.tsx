import { useState, useEffect } from "react";
import { Mail, Zap, Send, Check, Clock, Eye, Edit3 } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
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
              <div
                className={[styles.switch, auto.enabled ? styles.switchActive : ""].join(" ")}
                onClick={() => handleToggle(auto.id, auto.enabled)}
                title={auto.enabled ? "Desactivar automatización" : "Activar automatización"}
              >
                <div className={styles.switchThumb} />
              </div>
            </div>

            <h3 className={styles.cardTitle}>{auto.name}</h3>
            <p className={styles.subjectText}>
              <strong>Asunto:</strong> "{auto.subject}"
            </p>

            <div className={styles.statsRow}>
              <span className={styles.statLabel}>Enviados en total:</span>
              <Badge variant="neutral">{auto.sentCount.toLocaleString()} correos</Badge>
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
        description="Plantilla de correo automático con variables dinámicas {user_name}, {user_email}..."
        footer={
          <>
            {testSent && (
              <span className={styles.testSuccessNotice}>
                <Check size={16} /> ¡Correo de prueba enviado al admin!
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
              <label className={styles.label}>Línea de Asunto</label>
              <input
                type="text"
                defaultValue={selectedAuto.subject}
                className={styles.input}
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
