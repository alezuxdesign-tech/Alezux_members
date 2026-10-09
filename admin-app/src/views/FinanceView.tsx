import { useState, useEffect } from "react";
import { Plus, Link2, Copy, Check, CreditCard, DollarSign, Users, TrendingUp, ExternalLink } from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { Input } from "../components/arc/input/input";
import { api, FinancePlan, Course } from "../services/api";
import styles from "./FinanceView.module.css";

export function FinanceView() {
  const [plans, setPlans] = useState<FinancePlan[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [copiedToken, setCopiedToken] = useState<string | null>(null);

  // Modal para Crear Plan
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [planName, setPlanName] = useState("");
  const [selectedCourseId, setSelectedCourseId] = useState<number>(0);
  const [totalQuotas, setTotalQuotas] = useState<number>(4);
  const [quotaAmount, setQuotaAmount] = useState<number>(97);
  const [isCreating, setIsCreating] = useState(false);

  useEffect(() => {
    api.getPlans().then(setPlans);
    api.getCourses().then(setCourses);
  }, []);

  const handleCopyLink = (plan: FinancePlan) => {
    navigator.clipboard.writeText(plan.checkoutUrl);
    setCopiedToken(plan.token);
    setTimeout(() => setCopiedToken(null), 2000);
  };

  const handleCreatePlan = async () => {
    if (!planName.trim()) return;
    setIsCreating(true);

    const targetCourse = courses.find((c) => c.id === selectedCourseId);
    const created = await api.createPlan({
      name: planName,
      courseId: selectedCourseId,
      courseTitle: targetCourse ? targetCourse.title : "Todos los Cursos",
      totalQuotas,
      quotaAmount,
    });

    setPlans([created, ...plans]);
    setIsCreating(false);
    setIsModalOpen(false);

    // Reset form
    setPlanName("");
    setTotalQuotas(4);
    setQuotaAmount(97);
  };

  const totalRevenueProjected = plans.reduce((acc, p) => acc + p.totalAmount * p.subscribersCount, 0);
  const totalSubscribers = plans.reduce((acc, p) => acc + p.subscribersCount, 0);

  return (
    <div className={styles.container}>
      {/* Header */}
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Módulo de Finanzas & Planes de Pago</h1>
          <p className={styles.subtitle}>
            Crea planes en cuotas o pago único y genera enlaces de pago directos con Stripe para tus clientes.
          </p>
        </div>
        <Button variant="primary" onClick={() => setIsModalOpen(true)}>
          <Plus size={16} /> Crear Nuevo Plan
        </Button>
      </div>

      {/* Métricas Financieras Arc UI */}
      <div className={styles.metricsRow}>
        <MetricCard
          label="Volumen Proyectado"
          value={totalRevenueProjected > 0 ? totalRevenueProjected : 14850}
          prefix="$"
          suffix=" USD"
          change="+18%"
          context="Planes vigentes"
        />
        <MetricCard
          label="Alumnos en Financiación"
          value={totalSubscribers > 0 ? totalSubscribers : 142}
          suffix=" activos"
          change="+12%"
          context="Suscripciones y cuotas"
        />
        <MetricCard
          label="Planes Disponibles"
          value={plans.length}
          suffix=" configurados"
          context="Stripe Connect"
        />
      </div>

      {/* Grid de Planes */}
      <div className={styles.plansGrid}>
        {plans.map((plan) => {
          const isCopied = copiedToken === plan.token;
          return (
            <div key={plan.id} className={styles.planCard}>
              <div className={styles.planTop}>
                <div>
                  <Badge variant={plan.totalQuotas > 1 ? "accent" : "success"} size="sm">
                    {plan.totalQuotas > 1 ? `${plan.totalQuotas} Cuotas Recurrentes` : "Pago Único"}
                  </Badge>
                  <h3 className={styles.planName}>{plan.name}</h3>
                </div>
                <div className={styles.planPriceBox}>
                  <span className={styles.currency}>$</span>
                  <span className={styles.priceAmount}>{plan.quotaAmount}</span>
                  <span className={styles.pricePeriod}>
                    {plan.totalQuotas > 1 ? "/cuota" : " total"}
                  </span>
                </div>
              </div>

              <div className={styles.courseTag}>
                <CreditCard size={14} />
                <span>{plan.courseTitle}</span>
              </div>

              <div className={styles.planDetails}>
                <div className={styles.detailRow}>
                  <span>Total a pagar:</span>
                  <strong className={styles.tabularNums}>${plan.totalAmount} USD</strong>
                </div>
                <div className={styles.detailRow}>
                  <span>Alumnos suscritos:</span>
                  <span className={styles.tabularNums}>{plan.subscribersCount} alumnos</span>
                </div>
              </div>

              {/* Caja de Link de Pago Generado */}
              <div className={styles.linkGeneratorBox}>
                <div className={styles.linkDisplay}>
                  <Link2 size={14} className={styles.linkIcon} />
                  <span className={styles.linkUrlText}>{plan.checkoutUrl}</span>
                </div>

                <div className={styles.linkActions}>
                  <Button
                    variant={isCopied ? "primary" : "secondary"}
                    size="sm"
                    className={styles.copyBtn}
                    onClick={() => handleCopyLink(plan)}
                  >
                    {isCopied ? (
                      <>
                        <Check size={14} /> Copiado
                      </>
                    ) : (
                      <>
                        <Copy size={14} /> Copiar Link
                      </>
                    )}
                  </Button>

                  <a
                    href={plan.checkoutUrl}
                    target="_blank"
                    rel="noreferrer"
                    className={styles.testLinkBtn}
                    title="Abrir checkout"
                  >
                    <ExternalLink size={14} />
                  </a>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Modal para Crear Plan */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title="Crear Nuevo Plan de Pago"
        description="Configura el número de cuotas y el precio que cobrarás a través de Stripe."
        footer={
          <>
            <Button variant="ghost" onClick={() => setIsModalOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" loading={isCreating} onClick={handleCreatePlan}>
              Crear Plan y Generar Link
            </Button>
          </>
        }
      >
        <div className={styles.formGroup}>
          <Input
            label="Nombre del Plan *"
            placeholder="Ej: Plan Especial 3 Cuotas"
            value={planName}
            onChange={(e) => setPlanName(e.target.value)}
          />
        </div>

        <div className={styles.formGroup}>
          <label className={styles.label}>Curso Asociado</label>
          <div className={styles.selectWrap}>
            <select
              value={selectedCourseId}
              onChange={(e) => setSelectedCourseId(Number(e.target.value))}
              className={styles.select}
            >
              <option value={0}>Todos los Cursos (Membresía Completa)</option>
              {courses.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.title}
                </option>
              ))}
            </select>
          </div>
        </div>

        <div className={styles.formRow}>
          <Input
            label="Número de Cuotas"
            type="number"
            min={1}
            max={24}
            value={totalQuotas}
            hint="1 = Pago único, 2-12 = Cuotas"
            onChange={(e) => setTotalQuotas(Number(e.target.value))}
          />

          <Input
            label="Monto por Cuota (USD)"
            type="number"
            min={1}
            value={quotaAmount}
            hint={`Total: $${totalQuotas * quotaAmount} USD`}
            onChange={(e) => setQuotaAmount(Number(e.target.value))}
          />
        </div>
      </Modal>
    </div>
  );
}
