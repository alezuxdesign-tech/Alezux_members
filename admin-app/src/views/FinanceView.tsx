import { useState, useEffect, useMemo } from "react";
import {
  Plus,
  Copy,
  Check,
  CreditCard,
  DollarSign,
  Users,
  TrendingUp,
  Pencil,
  Trash2,
  Search,
  RotateCcw,
  Receipt,
  Calendar,
  ShieldCheck,
  Eye,
  EyeOff,
  MessageSquare,
  AlertCircle,
  Settings,
  Layers,
  Sparkles,
} from "lucide-react";
import { Button } from "../components/arc/button/button";
import { Badge } from "../components/arc/badge/badge";
import { Modal } from "../components/arc/modal/modal";
import { MetricCard } from "../components/arc/metric-card/metric-card";
import { Input } from "../components/arc/input/input";
import { SegmentedControl, SegmentOption } from "../components/arc/segmented-control/segmented-control";
import { Select, SelectOption } from "../components/arc/select/select";
import {
  api,
  FinancePlan,
  Course,
  SaleTransaction,
  SubscriptionItem,
  FinanceSettings,
} from "../services/api";
import styles from "./FinanceView.module.css";

type FinanceTab = "planes" | "ventas" | "suscripciones" | "configuracion";
type PlanModalTab = "general" | "reglas";

const TABS: SegmentOption<FinanceTab>[] = [
  { value: "planes", label: "Planes de Pago" },
  { value: "ventas", label: "Historial de Ventas" },
  { value: "suscripciones", label: "Suscripciones & Cuotas" },
  { value: "configuracion", label: "Pasarela & Stripe" },
];

const PLAN_MODAL_TABS: SegmentOption<PlanModalTab>[] = [
  { value: "general", label: "Detalles del Plan" },
  { value: "reglas", label: "Reglas de Liberación" },
];

const formatCurrency = (amount: number, decimals: number = 2): string => {
  return new Intl.NumberFormat("en-US", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(amount || 0);
};

export function FinanceView() {
  const [activeTab, setActiveTab] = useState<FinanceTab>("planes");

  // Planes
  const [plans, setPlans] = useState<FinancePlan[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [copiedToken, setCopiedToken] = useState<string | null>(null);

  // Ventas
  const [sales, setSales] = useState<SaleTransaction[]>([]);
  const [salesTotal, setSalesTotal] = useState(0);
  const [salesSearch, setSalesSearch] = useState("");
  const [salesStatus, setSalesStatus] = useState("");
  const [loadingSales, setLoadingSales] = useState(false);

  // Suscripciones
  const [subscriptions, setSubscriptions] = useState<SubscriptionItem[]>([]);
  const [subsTotal, setSubsTotal] = useState(0);
  const [subsSearch, setSubsSearch] = useState("");
  const [loadingSubs, setLoadingSubs] = useState(false);

  // Configuración Pasarela
  const [settings, setSettings] = useState<FinanceSettings>({
    stripe_public_key: "",
    stripe_secret_key: "",
    webhook_url: "",
  });
  const [showSecretKey, setShowSecretKey] = useState(false);
  const [copiedWebhook, setCopiedWebhook] = useState(false);
  const [isSavingSettings, setIsSavingSettings] = useState(false);
  const [settingsNotice, setSettingsNotice] = useState<string | null>(null);

  // Modal Crear Plan
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [createModalTab, setCreateModalTab] = useState<PlanModalTab>("general");
  const [createName, setCreateName] = useState("");
  const [createCourseId, setCreateCourseId] = useState<number>(0);
  const [createTotalQuotas, setCreateTotalQuotas] = useState<number>(4);
  const [createQuotaAmount, setCreateQuotaAmount] = useState<number>(97);
  const [createFrequency, setCreateFrequency] = useState<string>("month");
  const [createWhatsapp, setCreateWhatsapp] = useState<string>("");
  const [createAccessRules, setCreateAccessRules] = useState<Record<string, number>>({});
  const [createModules, setCreateModules] = useState<{ id: number; title: string }[]>([]);
  const [loadingCreateModules, setLoadingCreateModules] = useState(false);
  const [isCreating, setIsCreating] = useState(false);

  // Modal Configurar / Editar Plan
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [editModalTab, setEditModalTab] = useState<PlanModalTab>("general");
  const [editingPlan, setEditingPlan] = useState<FinancePlan | null>(null);
  const [editName, setEditName] = useState("");
  const [editCourseId, setEditCourseId] = useState<number>(0);
  const [editTotalQuotas, setEditTotalQuotas] = useState<number>(1);
  const [editQuotaAmount, setEditQuotaAmount] = useState<number>(97);
  const [editFrequency, setEditFrequency] = useState<string>("month");
  const [editWhatsapp, setEditWhatsapp] = useState<string>("");
  const [editAccessRules, setEditAccessRules] = useState<Record<string, number>>({});
  const [editModules, setEditModules] = useState<{ id: number; title: string }[]>([]);
  const [loadingEditModules, setLoadingEditModules] = useState(false);
  const [isSavingEdit, setIsSavingEdit] = useState(false);

  // Modal Pago Manual
  const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
  const [selectedSubForPayment, setSelectedSubForPayment] = useState<SubscriptionItem | null>(null);
  const [paymentAmount, setPaymentAmount] = useState<number>(0);
  const [paymentNote, setPaymentNote] = useState<string>("");
  const [isRegisteringPayment, setIsRegisteringPayment] = useState(false);
  const [paymentNotice, setPaymentNotice] = useState<string | null>(null);

  const createQuotaOptions: SelectOption<number>[] = useMemo(
    () => [
      { value: 1, label: "Cuota 1 (Inmediato al comprar)" },
      ...Array.from({ length: Math.max(0, createTotalQuotas - 1) }, (_, i) => ({
        value: i + 2,
        label: `Cuota ${i + 2}`,
      })),
    ],
    [createTotalQuotas]
  );

  const editQuotaOptions: SelectOption<number>[] = useMemo(
    () => [
      { value: 1, label: "Cuota 1 (Inmediato al comprar)" },
      ...Array.from({ length: Math.max(0, editTotalQuotas - 1) }, (_, i) => ({
        value: i + 2,
        label: `Cuota ${i + 2}`,
      })),
    ],
    [editTotalQuotas]
  );

  useEffect(() => {
    loadAllData();
  }, []);

  const loadAllData = () => {
    api.getPlans().then(setPlans);
    api.getCourses().then((cList) => {
      setCourses(cList);
      if (cList.length > 0 && createCourseId === 0) {
        // Inicializar con el primer curso para facilitar la configuración de reglas
        setCreateCourseId(cList[0].id);
        fetchModulesForCreate(cList[0].id);
      }
    });
    loadSales();
    loadSubscriptions();
    api.getFinanceSettings().then(setSettings);
  };

  const loadSales = () => {
    setLoadingSales(true);
    api.getSales({ search: salesSearch, status: salesStatus }).then((res) => {
      setSales(res.rows);
      setSalesTotal(res.total);
      setLoadingSales(false);
    });
  };

  const loadSubscriptions = () => {
    setLoadingSubs(true);
    api.getSubscriptions({ search: subsSearch }).then((res) => {
      setSubscriptions(res.rows);
      setSubsTotal(res.total);
      setLoadingSubs(false);
    });
  };

  // Cargar módulos para Crear Plan
  const fetchModulesForCreate = async (courseId: number) => {
    if (courseId <= 0) {
      setCreateModules([]);
      setCreateAccessRules({});
      return;
    }
    setLoadingCreateModules(true);
    const mods = await api.getCourseModules(courseId);
    setCreateModules(mods);

    // Inicializar reglas por defecto (todas en cuota 1 si no existen)
    const initialRules: Record<string, number> = {};
    mods.forEach((m, idx) => {
      initialRules[m.id.toString()] = 1;
    });
    setCreateAccessRules(initialRules);
    setLoadingCreateModules(false);
  };

  // Cargar módulos para Editar Plan
  const fetchModulesForEdit = async (courseId: number, existingRules?: any) => {
    if (courseId <= 0) {
      setEditModules([]);
      setEditAccessRules({});
      return;
    }
    setLoadingEditModules(true);
    const mods = await api.getCourseModules(courseId);
    setEditModules(mods);

    // Parsear reglas existentes
    let parsedRules: Record<string, number> = {};
    if (existingRules) {
      if (typeof existingRules === "object") {
        parsedRules = { ...existingRules };
      } else if (typeof existingRules === "string") {
        try {
          parsedRules = JSON.parse(existingRules);
        } catch {
          parsedRules = {};
        }
      }
    }

    // Completar módulos sin regla asignada
    mods.forEach((m) => {
      const key = m.id.toString();
      if (!parsedRules[key]) {
        parsedRules[key] = 1;
      }
    });

    setEditAccessRules(parsedRules);
    setLoadingEditModules(false);
  };

  // Handlers para Planes
  const handleCopyLink = (plan: FinancePlan) => {
    navigator.clipboard.writeText(plan.checkoutUrl);
    setCopiedToken(plan.token);
    setTimeout(() => setCopiedToken(null), 2000);
  };

  const handleOpenCreateModal = () => {
    setIsCreateModalOpen(true);
    setCreateModalTab("general");
    const firstCourse = courses.length > 0 ? courses[0].id : 0;
    setCreateCourseId(firstCourse);
    fetchModulesForCreate(firstCourse);
  };

  const handleCreatePlan = async () => {
    if (!createName.trim()) return;
    setIsCreating(true);

    const targetCourse = courses.find((c) => c.id === createCourseId);
    const created = await api.createPlan({
      name: createName,
      courseId: createCourseId,
      courseTitle: targetCourse ? targetCourse.title : "Todos los Cursos",
      totalQuotas: createTotalQuotas,
      quotaAmount: createQuotaAmount,
      frequency: createFrequency,
      whatsapp_number: createWhatsapp,
      access_rules: createAccessRules,
    });

    setPlans([created, ...plans]);
    setIsCreating(false);
    setIsCreateModalOpen(false);

    // Reset
    setCreateName("");
    setCreateTotalQuotas(4);
    setCreateQuotaAmount(97);
    setCreateWhatsapp("");
    setCreateAccessRules({});
    setCreateModalTab("general");
  };

  const handleOpenEditPlan = (plan: FinancePlan) => {
    setEditingPlan(plan);
    setEditName(plan.name);
    setEditCourseId(plan.courseId);
    setEditTotalQuotas(plan.totalQuotas);
    setEditQuotaAmount(plan.quotaAmount);
    setEditFrequency(plan.frequency || "month");
    setEditWhatsapp(plan.whatsapp_number || "");
    setEditModalTab("general");
    setIsEditModalOpen(true);
    fetchModulesForEdit(plan.courseId, plan.access_rules);
  };

  const handleSaveEditPlan = async () => {
    if (!editingPlan || !editName.trim()) return;
    setIsSavingEdit(true);

    const targetCourse = courses.find((c) => c.id === editCourseId);
    await api.updatePlan(editingPlan.id, {
      name: editName,
      courseId: editCourseId,
      totalQuotas: editTotalQuotas,
      quotaAmount: editQuotaAmount,
      frequency: editFrequency,
      whatsapp_number: editWhatsapp,
      access_rules: editAccessRules,
    });

    setPlans((prev) =>
      prev.map((p) =>
        p.id === editingPlan.id
          ? {
              ...p,
              name: editName,
              courseId: editCourseId,
              courseTitle: targetCourse ? targetCourse.title : "Todos los Cursos",
              totalQuotas: editTotalQuotas,
              quotaAmount: editQuotaAmount,
              totalAmount: editTotalQuotas * editQuotaAmount,
              frequency: editFrequency,
              whatsapp_number: editWhatsapp,
              access_rules: editAccessRules,
            }
          : p
      )
    );

    setIsSavingEdit(false);
    setIsEditModalOpen(false);
  };

  const handleDeletePlan = async (id: number) => {
    if (!window.confirm("¿Seguro que deseas eliminar este plan de pago?")) return;
    await api.deletePlan(id);
    setPlans((prev) => prev.filter((p) => p.id !== id));
  };

  // Funciones de conveniencia para reglas de acceso
  const distributeRulesEqually = (
    modules: { id: number; title: string }[],
    totalQ: number,
    setRulesFn: (fn: (prev: Record<string, number>) => Record<string, number>) => void
  ) => {
    if (modules.length === 0) return;
    const newRules: Record<string, number> = {};
    modules.forEach((mod, idx) => {
      // Distribuir proporcionalmente entre las cuotas
      const assignedQuota = Math.min(totalQ, Math.floor((idx / modules.length) * totalQ) + 1);
      newRules[mod.id.toString()] = assignedQuota;
    });
    setRulesFn(() => newRules);
  };

  const unlockAllInQuotaOne = (
    modules: { id: number; title: string }[],
    setRulesFn: (fn: (prev: Record<string, number>) => Record<string, number>) => void
  ) => {
    const newRules: Record<string, number> = {};
    modules.forEach((mod) => {
      newRules[mod.id.toString()] = 1;
    });
    setRulesFn(() => newRules);
  };

  // Handlers para Suscripciones & Pagos Manuales
  const handleOpenManualPayment = (sub: SubscriptionItem) => {
    setSelectedSubForPayment(sub);
    setPaymentAmount(sub.amount);
    setPaymentNote(`Abono cuota ${sub.quotasPaid + 1} de ${sub.totalQuotas}`);
    setPaymentNotice(null);
    setIsPaymentModalOpen(true);
  };

  const handleConfirmManualPayment = async () => {
    if (!selectedSubForPayment) return;
    setIsRegisteringPayment(true);

    const res = await api.registerManualPayment(
      selectedSubForPayment.id,
      paymentAmount,
      paymentNote
    );

    if (res.success) {
      setPaymentNotice(res.message || "Pago registrado correctamente.");
      setTimeout(() => {
        setIsPaymentModalOpen(false);
        loadSubscriptions();
        loadSales();
      }, 1200);
    }
    setIsRegisteringPayment(false);
  };

  // Handlers para Configuración
  const handleSaveSettings = async () => {
    setIsSavingSettings(true);
    await api.saveFinanceSettings(settings);
    setIsSavingSettings(false);
    setSettingsNotice("Configuración de Stripe guardada con éxito.");
    setTimeout(() => setSettingsNotice(null), 3500);
  };

  const handleCopyWebhook = () => {
    if (settings.webhook_url) {
      navigator.clipboard.writeText(settings.webhook_url);
      setCopiedWebhook(true);
      setTimeout(() => setCopiedWebhook(false), 2000);
    }
  };

  // KPIs
  const totalRevenueProjected = plans.reduce(
    (acc, p) => acc + p.totalAmount * p.subscribersCount,
    0
  );
  const totalSubscribers = plans.reduce((acc, p) => acc + p.subscribersCount, 0);

  // Filtrado reactivo de ventas
  const filteredSales = useMemo(() => {
    return sales.filter((item) => {
      const matchSearch =
        !salesSearch ||
        item.student.toLowerCase().includes(salesSearch.toLowerCase()) ||
        item.studentEmail.toLowerCase().includes(salesSearch.toLowerCase()) ||
        item.ref.toLowerCase().includes(salesSearch.toLowerCase());
      const matchStatus = !salesStatus || item.status === salesStatus;
      return matchSearch && matchStatus;
    });
  }, [sales, salesSearch, salesStatus]);

  // Filtrado reactivo de suscripciones
  const filteredSubs = useMemo(() => {
    return subscriptions.filter((sub) => {
      return (
        !subsSearch ||
        sub.student.toLowerCase().includes(subsSearch.toLowerCase()) ||
        sub.studentEmail.toLowerCase().includes(subsSearch.toLowerCase()) ||
        sub.plan.toLowerCase().includes(subsSearch.toLowerCase())
      );
    });
  }, [subscriptions, subsSearch]);

  return (
    <div className={styles.container}>
      {/* Header */}
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>Módulo de Finanzas & Planes de Pago</h1>
          <p className={styles.subtitle}>
            Gestión centralizada de ventas, suscripciones recurrentes, control de cuotas y reglas de liberación de contenido.
          </p>
        </div>
        <div className={styles.headerActions}>
          <Button variant="secondary" onClick={() => setActiveTab("configuracion")}>
            <Settings size={16} /> Pasarela Stripe
          </Button>
          <Button variant="primary" onClick={handleOpenCreateModal}>
            <Plus size={16} /> Crear Nuevo Plan
          </Button>
        </div>
      </div>

      {/* Métricas Financieras Arc UI */}
      <div className={styles.metricsRow}>
        <MetricCard
          label="Volumen Proyectado"
          value={totalRevenueProjected > 0 ? totalRevenueProjected : 14850}
          prefix="$"
          suffix="USD"
          decimals={2}
          change="+18%"
          context="Planes vigentes"
        />
        <MetricCard
          label="Alumnos en Financiación"
          value={totalSubscribers > 0 ? totalSubscribers : 142}
          suffix="activos"
          change="+12%"
          context="Suscripciones y cuotas"
        />
        <MetricCard
          label="Transacciones de Venta"
          value={salesTotal > 0 ? salesTotal : 58}
          suffix="registradas"
          context="Historial de pagos"
        />
        <MetricCard
          label="Planes Disponibles"
          value={plans.length}
          suffix="configurados"
          context="Stripe Checkout"
        />
      </div>

      {/* Barra de Pestañas Arc UI */}
      <div className={styles.tabsRow}>
        <SegmentedControl<FinanceTab>
          options={TABS}
          value={activeTab}
          onChange={setActiveTab}
        />
      </div>

      {/* ============================================================== */}
      {/* PESTAÑA 1: PLANES DE PAGO */}
      {/* ============================================================== */}
      {activeTab === "planes" && (
        <div className={styles.plansGrid}>
          {plans.map((plan) => {
            const isCopied = copiedToken === plan.token;
            return (
              <div key={plan.id} className={styles.planCard}>
                <div className={styles.planTop}>
                  <div>
                    <Badge variant={plan.totalQuotas > 1 ? "accent" : "success"} size="sm">
                      {plan.totalQuotas > 1
                        ? `${plan.totalQuotas} Cuotas Recurrentes`
                        : "Pago Único"}
                    </Badge>
                    <h3 className={styles.planName}>{plan.name}</h3>
                  </div>
                  <div className={styles.planPriceBox}>
                    <span className={styles.currency}>$</span>
                    <span className={styles.priceAmount}>{formatCurrency(plan.quotaAmount)}</span>
                    <span className={styles.pricePeriod}>
                      {plan.totalQuotas > 1 ? "/cuota" : " total"}
                    </span>
                  </div>
                </div>

                <div className={styles.courseTag}>
                  <CreditCard size={14} />
                  <span>{plan.courseTitle}</span>
                </div>

                {plan.whatsapp_number && (
                  <div className={styles.whatsappTag}>
                    <MessageSquare size={13} />
                    <span>Soporte: {plan.whatsapp_number}</span>
                  </div>
                )}

                <div className={styles.planDetails}>
                  <div className={styles.detailRow}>
                    <span>Total a pagar:</span>
                    <strong className={styles.tabularNums}>${formatCurrency(plan.totalAmount)} USD</strong>
                  </div>
                  <div className={styles.detailRow}>
                    <span>Frecuencia:</span>
                    <span>{plan.frequency === "contado" ? "Inmediato" : "Mensual"}</span>
                  </div>
                  <div className={styles.detailRow}>
                    <span>Alumnos suscritos:</span>
                    <span className={styles.tabularNums}>{plan.subscribersCount} alumnos</span>
                  </div>
                </div>

                {/* Caja de Link de Pago con Copy Button integrado */}
                <div className={styles.linkGeneratorBox}>
                  <div className={styles.shareLinkBox}>
                    <span className={styles.shareLinkUrl} title={plan.checkoutUrl}>
                      {plan.checkoutUrl}
                    </span>
                    <button
                      type="button"
                      className={[
                        styles.shareCopyBtn,
                        isCopied ? styles.shareCopied : "",
                      ]
                        .filter(Boolean)
                        .join(" ")}
                      onClick={() => handleCopyLink(plan)}
                      title="Copiar enlace de pago"
                    >
                      {isCopied ? (
                        <>
                          <Check size={13} className={styles.shareCheckIcon} />
                          <span>Copiado</span>
                        </>
                      ) : (
                        <>
                          <Copy size={13} />
                          <span>Copy link</span>
                        </>
                      )}
                    </button>
                  </div>

                  <div className={styles.cardFooterActions}>
                    <Button
                      variant="secondary"
                      size="sm"
                      onClick={() => handleOpenEditPlan(plan)}
                      title="Editar plan y configurar reglas de liberación"
                    >
                      <Pencil size={13} /> Configurar
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDeletePlan(plan.id)}
                      title="Eliminar este plan"
                    >
                      <Trash2 size={13} /> Eliminar
                    </Button>
                  </div>
                </div>
              </div>
            );
          })}

          {plans.length === 0 && (
            <div className={styles.emptyState}>
              <CreditCard size={40} className={styles.emptyIcon} />
              <h3 className={styles.emptyTitle}>No hay planes creados todavía</h3>
              <p className={styles.emptyDesc}>
                Crea tu primer plan en cuotas o pago único para empezar a generar enlaces directos con Stripe.
              </p>
              <Button
                variant="primary"
                style={{ marginTop: "1rem" }}
                onClick={handleOpenCreateModal}
              >
                <Plus size={16} /> Crear Primer Plan
              </Button>
            </div>
          )}
        </div>
      )}

      {/* ============================================================== */}
      {/* PESTAÑA 2: HISTORIAL DE VENTAS */}
      {/* ============================================================== */}
      {activeTab === "ventas" && (
        <div>
          {/* Barra de Filtros */}
          <div className={styles.filterBar}>
            <div className={styles.filterGroup}>
              <div className={styles.searchBox}>
                <Input
                  placeholder="Buscar por alumno, correo o referencia..."
                  value={salesSearch}
                  onChange={(e) => setSalesSearch(e.target.value)}
                />
              </div>
              <div className={styles.selectWrap} style={{ width: "180px" }}>
                <select
                  value={salesStatus}
                  onChange={(e) => setSalesStatus(e.target.value)}
                  className={styles.select}
                >
                  <option value="">Todos los Estados</option>
                  <option value="succeeded">Completado</option>
                  <option value="pending">Pendiente</option>
                  <option value="failed">Fallido</option>
                  <option value="refunded">Reembolsado</option>
                </select>
              </div>
            </div>

            <Button variant="secondary" size="sm" onClick={loadSales} disabled={loadingSales}>
              <RotateCcw size={14} /> Actualizar
            </Button>
          </div>

          {/* Tabla de Ventas */}
          <div className={styles.tableWrapper}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>Alumno</th>
                  <th>Curso / Plan</th>
                  <th>Monto</th>
                  <th>Modalidad</th>
                  <th>Método</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                  <th>Referencia</th>
                </tr>
              </thead>
              <tbody>
                {filteredSales.map((sale) => {
                  let badgeVariant: "success" | "warning" | "danger" | "neutral" = "neutral";
                  if (sale.status === "succeeded") badgeVariant = "success";
                  else if (sale.status === "pending") badgeVariant = "warning";
                  else if (sale.status === "failed") badgeVariant = "danger";

                  return (
                    <tr key={sale.id}>
                      <td>
                        <div className={styles.studentInfo}>
                          <span className={styles.studentName}>{sale.student}</span>
                          <span className={styles.studentEmail}>{sale.studentEmail}</span>
                        </div>
                      </td>
                      <td>{sale.course}</td>
                      <td>
                        <span className={styles.amountText}>
                          ${formatCurrency(sale.amount)} {sale.currency}
                        </span>
                      </td>
                      <td>
                        <Badge variant="neutral" size="sm">
                          {sale.quotasDesc}
                        </Badge>
                      </td>
                      <td>{sale.method}</td>
                      <td>
                        <Badge variant={badgeVariant} size="sm">
                          {sale.status === "succeeded"
                            ? "Completado"
                            : sale.status === "pending"
                            ? "Pendiente"
                            : sale.status === "failed"
                            ? "Fallido"
                            : sale.status}
                        </Badge>
                      </td>
                      <td style={{ whiteSpace: "nowrap" }}>{sale.date}</td>
                      <td>
                        <span className={styles.refCode}>{sale.ref}</span>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>

            {filteredSales.length === 0 && (
              <div className={styles.emptyState}>
                <Receipt size={36} className={styles.emptyIcon} />
                <h4 className={styles.emptyTitle}>No se encontraron transacciones</h4>
                <p className={styles.emptyDesc}>
                  Las compras de los estudiantes a través de Stripe o registros manuales aparecerán aquí automáticamente.
                </p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* ============================================================== */}
      {/* PESTAÑA 3: SUSCRIPCIONES & CUOTAS */}
      {/* ============================================================== */}
      {activeTab === "suscripciones" && (
        <div>
          {/* Barra de Filtros */}
          <div className={styles.filterBar}>
            <div className={styles.searchBox}>
              <Input
                placeholder="Buscar por estudiante o plan..."
                value={subsSearch}
                onChange={(e) => setSubsSearch(e.target.value)}
              />
            </div>
            <Button variant="secondary" size="sm" onClick={loadSubscriptions} disabled={loadingSubs}>
              <RotateCcw size={14} /> Actualizar
            </Button>
          </div>

          {/* Tabla de Suscripciones */}
          <div className={styles.tableWrapper}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>Estudiante</th>
                  <th>Plan Contratado</th>
                  <th>Progreso de Cuotas</th>
                  <th>Monto Cuota</th>
                  <th>Próximo Cobro</th>
                  <th>Estado</th>
                  <th style={{ textAlign: "right" }}>Acción</th>
                </tr>
              </thead>
              <tbody>
                {filteredSubs.map((sub) => {
                  let badgeVariant: "accent" | "success" | "danger" | "neutral" = "neutral";
                  if (sub.status === "active") badgeVariant = "accent";
                  else if (sub.status === "completed") badgeVariant = "success";
                  else if (sub.status === "past_due") badgeVariant = "danger";

                  return (
                    <tr key={sub.id}>
                      <td>
                        <div className={styles.studentCell}>
                          <img
                            src={
                              sub.studentAvatar ||
                              "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80"
                            }
                            alt={sub.student}
                            className={styles.avatar}
                          />
                          <div className={styles.studentInfo}>
                            <span className={styles.studentName}>{sub.student}</span>
                            <span className={styles.studentEmail}>{sub.studentEmail}</span>
                          </div>
                        </div>
                      </td>
                      <td>{sub.plan}</td>
                      <td>
                        <div className={styles.progressWrapper}>
                          <div className={styles.progressBarBg}>
                            <div
                              className={styles.progressBarFill}
                              style={{ width: `${sub.percent}%` }}
                            />
                          </div>
                          <span className={styles.progressLabel}>
                            {sub.quotasPaid} de {sub.totalQuotas} cuotas ({sub.percent}%)
                          </span>
                        </div>
                      </td>
                      <td>
                        <span className={styles.amountText}>${formatCurrency(sub.amount)} USD</span>
                      </td>
                      <td>
                        <span
                          style={{
                            color: sub.nextPayment.includes("Atrasado")
                              ? "var(--danger)"
                              : sub.nextPayment.includes("Pagado")
                              ? "var(--success)"
                              : "inherit",
                            fontWeight: 500,
                          }}
                        >
                          {sub.nextPayment}
                        </span>
                      </td>
                      <td>
                        <Badge variant={badgeVariant} size="sm">
                          {sub.status === "active"
                            ? "Activa"
                            : sub.status === "completed"
                            ? "Completada"
                            : sub.status === "past_due"
                            ? "Atrasada"
                            : sub.status}
                        </Badge>
                      </td>
                      <td style={{ textAlign: "right" }}>
                        <Button
                          variant="secondary"
                          size="sm"
                          onClick={() => handleOpenManualPayment(sub)}
                          disabled={sub.status === "completed"}
                          title="Registrar pago manual de la siguiente cuota"
                        >
                          <DollarSign size={13} />
                          {sub.status === "completed" ? "Finalizado" : "Registrar Pago"}
                        </Button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>

            {filteredSubs.length === 0 && (
              <div className={styles.emptyState}>
                <Users size={36} className={styles.emptyIcon} />
                <h4 className={styles.emptyTitle}>No hay suscripciones activas</h4>
                <p className={styles.emptyDesc}>
                  Cuando un estudiante adquiera un plan financiado, su ficha de seguimiento de cuotas aparecerá aquí.
                </p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* ============================================================== */}
      {/* PESTAÑA 4: CONFIGURACIÓN PASARELA STRIPE */}
      {/* ============================================================== */}
      {activeTab === "configuracion" && (
        <div className={styles.settingsCard}>
          <div className={styles.settingsHeader}>
            <h3 className={styles.settingsTitle}>Credenciales de Pasarela Stripe</h3>
            <p className={styles.settingsDesc}>
              Conecta tu cuenta de Stripe para procesar cobros automáticos con tarjeta, suscripciones en cuotas y pagos únicos.
            </p>
          </div>

          {settingsNotice && (
            <div className={styles.noticeSuccess}>
              <Check size={16} />
              <span>{settingsNotice}</span>
            </div>
          )}

          <div className={styles.formGroup}>
            <Input
              label="Stripe Publishable Key (Clave Pública)"
              placeholder="pk_test_... o pk_live_..."
              value={settings.stripe_public_key}
              onChange={(e) => setSettings({ ...settings, stripe_public_key: e.target.value })}
            />
          </div>

          <div className={styles.formGroup}>
            <div className={styles.passwordField}>
              <Input
                label="Stripe Secret Key (Clave Secreta)"
                type={showSecretKey ? "text" : "password"}
                placeholder="sk_test_... o sk_live_..."
                value={settings.stripe_secret_key}
                onChange={(e) => setSettings({ ...settings, stripe_secret_key: e.target.value })}
              />
              <button
                type="button"
                className={styles.eyeToggleBtn}
                onClick={() => setShowSecretKey(!showSecretKey)}
                aria-label="Mostrar u ocultar clave secreta"
              >
                {showSecretKey ? <EyeOff size={15} /> : <Eye size={15} />}
              </button>
            </div>
          </div>

          <div className={styles.formGroup}>
            <label className={styles.label}>URL del Webhook de Stripe (Para tu Dashboard de Stripe)</label>
            <div className={styles.webhookBox}>
              <span className={styles.webhookText}>
                {settings.webhook_url || `${window.location.origin}/?alezux_webhook=stripe`}
              </span>
              <Button variant="secondary" size="sm" onClick={handleCopyWebhook}>
                {copiedWebhook ? <Check size={14} /> : <Copy size={14} />}
                {copiedWebhook ? "Copiado" : "Copiar"}
              </Button>
            </div>
            <p className={styles.settingsDesc}>
              Añade esta URL en tu Stripe Dashboard en <em>Desarrolladores → Webhooks</em> para que las suscripciones y accesos se activen de forma instantánea al pagar.
            </p>
          </div>

          <div>
            <Button variant="primary" loading={isSavingSettings} onClick={handleSaveSettings}>
              Guardar Configuración de Stripe
            </Button>
          </div>
        </div>
      )}

      {/* ============================================================== */}
      {/* MODAL PARA CREAR NUEVO PLAN CON REGLAS DE LIBERACIÓN */}
      {/* ============================================================== */}
      <Modal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
        title="Crear Nuevo Plan de Pago"
        description="Configura el número de cuotas, el precio y las reglas de liberación de contenido para el alumno."
        maxWidth="680px"
        footer={
          <>
            <Button variant="ghost" onClick={() => setIsCreateModalOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" loading={isCreating} onClick={handleCreatePlan}>
              Crear Plan y Generar Link
            </Button>
          </>
        }
      >
        <div className={styles.modalTabs}>
          <SegmentedControl<PlanModalTab>
            options={PLAN_MODAL_TABS}
            value={createModalTab}
            onChange={setCreateModalTab}
          />
        </div>

        {createModalTab === "general" ? (
          <div className={styles.modalTabContent}>
            <div className={styles.formGroup}>
              <Input
                label="Nombre del Plan *"
                placeholder="Ej: Master Marketing 4 Cuotas"
                value={createName}
                onChange={(e) => setCreateName(e.target.value)}
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label}>Curso Asociado</label>
              <div className={styles.selectWrap}>
                <select
                  value={createCourseId}
                  onChange={(e) => {
                    const cId = Number(e.target.value);
                    setCreateCourseId(cId);
                    fetchModulesForCreate(cId);
                  }}
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
                value={createTotalQuotas}
                hint="1 = Pago único, 2-12 = Cuotas"
                onChange={(e) => setCreateTotalQuotas(Number(e.target.value))}
              />

              <Input
                label="Monto por Cuota (USD)"
                type="number"
                min={1}
                value={createQuotaAmount}
                hint={`Total a cobrar: $${formatCurrency(createTotalQuotas * createQuotaAmount)} USD`}
                onChange={(e) => setCreateQuotaAmount(Number(e.target.value))}
              />
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup} style={{ marginBottom: 0 }}>
                <label className={styles.label}>Frecuencia de Cobro</label>
                <div className={styles.selectWrap}>
                  <select
                    value={createFrequency}
                    onChange={(e) => setCreateFrequency(e.target.value)}
                    className={styles.select}
                  >
                    <option value="month">Mensual (Cada 30 días)</option>
                    <option value="week">Semanal</option>
                    <option value="year">Anual</option>
                  </select>
                </div>
              </div>

              <Input
                label="WhatsApp de Soporte (Opcional)"
                placeholder="+51 987 654 321"
                value={createWhatsapp}
                onChange={(e) => setCreateWhatsapp(e.target.value)}
              />
            </div>
          </div>
        ) : (
          <div className={styles.modalTabContent}>
            {/* Sección: Reglas de Liberación de Contenido */}
            <div className={styles.rulesSection}>
              <div className={styles.rulesSectionHeader}>
                <div>
                  <h4 className={styles.rulesTitle}>Reglas de Liberación de Contenido</h4>
                  <p className={styles.rulesDesc}>
                    Define en qué cuota pagada se desbloquea cada módulo para el estudiante:
                  </p>
                </div>

                {createCourseId > 0 && createModules.length > 0 && createTotalQuotas > 1 && (
                  <div className={styles.rulesQuickActions}>
                    <Button
                      variant="ghost"
                      size="sm"
                      type="button"
                      onClick={() => unlockAllInQuotaOne(createModules, setCreateAccessRules)}
                      title="Liberar todas las lecciones en la primera cuota"
                    >
                      Todo en Cuota 1
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      type="button"
                      onClick={() =>
                        distributeRulesEqually(createModules, createTotalQuotas, setCreateAccessRules)
                      }
                      title="Distribuir secuencialmente entre las cuotas"
                    >
                      <Sparkles size={12} /> Distribuir por Cuotas
                    </Button>
                  </div>
                )}
              </div>

              {createCourseId === 0 ? (
                <div className={styles.allAccessNotice}>
                  <Check size={16} className={styles.allAccessIcon} />
                  <div>
                    <strong>Membresía All-Access:</strong> Este plan da acceso a todos los cursos. Para configurar reglas de liberación gradual de lecciones por cuota, selecciona un curso específico en la pestaña &quot;Detalles del Plan&quot;.
                  </div>
                </div>
              ) : loadingCreateModules ? (
                <div className={styles.rulesLoading}>
                  <RotateCcw size={15} className={styles.spin} />
                  <span>Cargando módulos y lecciones del curso...</span>
                </div>
              ) : createModules.length === 0 ? (
                <div className={styles.rulesEmpty}>
                  Este curso aún no tiene lecciones creadas en WordPress.
                </div>
              ) : (
                <div className={styles.rulesTableContainer}>
                  <table className={styles.rulesTable}>
                    <thead>
                      <tr>
                        <th>Módulo / Lección</th>
                        <th style={{ width: "260px" }}>Se desbloquea al pagar:</th>
                      </tr>
                    </thead>
                    <tbody>
                      {createModules.map((mod) => (
                        <tr key={mod.id}>
                          <td>
                            <span className={styles.ruleModuleTitle}>{mod.title}</span>
                          </td>
                          <td>
                            <Select<number>
                              size="sm"
                              options={createQuotaOptions}
                              value={createAccessRules[mod.id.toString()] || 1}
                              onChange={(val) => {
                                setCreateAccessRules((prev) => ({
                                  ...prev,
                                  [mod.id.toString()]: val,
                                }));
                              }}
                            />
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        )}
      </Modal>

      {/* ============================================================== */}
      {/* MODAL PARA CONFIGURAR / EDITAR PLAN EXISTENTE */}
      {/* ============================================================== */}
      <Modal
        isOpen={isEditModalOpen}
        onClose={() => setIsEditModalOpen(false)}
        title="Configurar y Editar Plan"
        description="Actualiza el nombre, curso vinculado, cuotas y reglas de liberación de contenido."
        maxWidth="680px"
        footer={
          <>
            <Button variant="ghost" onClick={() => setIsEditModalOpen(false)}>
              Cancelar
            </Button>
            <Button variant="primary" loading={isSavingEdit} onClick={handleSaveEditPlan}>
              Guardar Cambios
            </Button>
          </>
        }
      >
        <div className={styles.modalTabs}>
          <SegmentedControl<PlanModalTab>
            options={PLAN_MODAL_TABS}
            value={editModalTab}
            onChange={setEditModalTab}
          />
        </div>

        {editModalTab === "general" ? (
          <div className={styles.modalTabContent}>
            <div className={styles.formGroup}>
              <Input
                label="Nombre del Plan *"
                value={editName}
                onChange={(e) => setEditName(e.target.value)}
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.label}>Curso Asociado</label>
              <div className={styles.selectWrap}>
                <select
                  value={editCourseId}
                  onChange={(e) => {
                    const cId = Number(e.target.value);
                    setEditCourseId(cId);
                    fetchModulesForEdit(cId, editAccessRules);
                  }}
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
                value={editTotalQuotas}
                onChange={(e) => setEditTotalQuotas(Number(e.target.value))}
              />

              <Input
                label="Monto por Cuota (USD)"
                type="number"
                min={1}
                value={editQuotaAmount}
                hint={`Total a cobrar: $${formatCurrency(editTotalQuotas * editQuotaAmount)} USD`}
                onChange={(e) => setEditQuotaAmount(Number(e.target.value))}
              />
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup} style={{ marginBottom: 0 }}>
                <label className={styles.label}>Frecuencia de Cobro</label>
                <div className={styles.selectWrap}>
                  <select
                    value={editFrequency}
                    onChange={(e) => setEditFrequency(e.target.value)}
                    className={styles.select}
                  >
                    <option value="month">Mensual (Cada 30 días)</option>
                    <option value="week">Semanal</option>
                    <option value="year">Anual</option>
                  </select>
                </div>
              </div>

              <Input
                label="WhatsApp de Soporte (Opcional)"
                placeholder="+51 987 654 321"
                value={editWhatsapp}
                onChange={(e) => setEditWhatsapp(e.target.value)}
              />
            </div>
          </div>
        ) : (
          <div className={styles.modalTabContent}>
            {/* Sección: Reglas de Liberación de Contenido en Edición */}
            <div className={styles.rulesSection}>
              <div className={styles.rulesSectionHeader}>
                <div>
                  <h4 className={styles.rulesTitle}>Reglas de Liberación de Contenido</h4>
                  <p className={styles.rulesDesc}>
                    Define en qué cuota pagada se desbloquea cada módulo para el estudiante:
                  </p>
                </div>

                {editCourseId > 0 && editModules.length > 0 && editTotalQuotas > 1 && (
                  <div className={styles.rulesQuickActions}>
                    <Button
                      variant="ghost"
                      size="sm"
                      type="button"
                      onClick={() => unlockAllInQuotaOne(editModules, setEditAccessRules)}
                      title="Liberar todas las lecciones en la primera cuota"
                    >
                      Todo en Cuota 1
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      type="button"
                      onClick={() =>
                        distributeRulesEqually(editModules, editTotalQuotas, setEditAccessRules)
                      }
                      title="Distribuir secuencialmente entre las cuotas"
                    >
                      <Sparkles size={12} /> Distribuir por Cuotas
                    </Button>
                  </div>
                )}
              </div>

              {editCourseId === 0 ? (
                <div className={styles.allAccessNotice}>
                  <Check size={16} className={styles.allAccessIcon} />
                  <div>
                    <strong>Membresía All-Access:</strong> Este plan da acceso a todos los cursos. Para configurar reglas de liberación gradual de lecciones por cuota, selecciona un curso específico en la pestaña &quot;Detalles del Plan&quot;.
                  </div>
                </div>
              ) : loadingEditModules ? (
                <div className={styles.rulesLoading}>
                  <RotateCcw size={15} className={styles.spin} />
                  <span>Cargando módulos y lecciones del curso...</span>
                </div>
              ) : editModules.length === 0 ? (
                <div className={styles.rulesEmpty}>
                  Este curso aún no tiene lecciones creadas en WordPress.
                </div>
              ) : (
                <div className={styles.rulesTableContainer}>
                  <table className={styles.rulesTable}>
                    <thead>
                      <tr>
                        <th>Módulo / Lección</th>
                        <th style={{ width: "260px" }}>Se desbloquea al pagar:</th>
                      </tr>
                    </thead>
                    <tbody>
                      {editModules.map((mod) => (
                        <tr key={mod.id}>
                          <td>
                            <span className={styles.ruleModuleTitle}>{mod.title}</span>
                          </td>
                          <td>
                            <Select<number>
                              size="sm"
                              options={editQuotaOptions}
                              value={editAccessRules[mod.id.toString()] || 1}
                              onChange={(val) => {
                                setEditAccessRules((prev) => ({
                                  ...prev,
                                  [mod.id.toString()]: val,
                                }));
                              }}
                            />
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        )}
      </Modal>

      {/* ============================================================== */}
      {/* MODAL PARA REGISTRAR PAGO MANUAL DE CUOTA */}
      {/* ============================================================== */}
      <Modal
        isOpen={isPaymentModalOpen}
        onClose={() => setIsPaymentModalOpen(false)}
        title="Registrar Pago Manual de Cuota"
        description={
          selectedSubForPayment
            ? `Estudiante: ${selectedSubForPayment.student} · Plan: ${selectedSubForPayment.plan}`
            : "Registro manual de abono de cuota"
        }
        footer={
          <>
            <Button variant="ghost" onClick={() => setIsPaymentModalOpen(false)}>
              Cancelar
            </Button>
            <Button
              variant="primary"
              loading={isRegisteringPayment}
              onClick={handleConfirmManualPayment}
            >
              Confirmar Abono de Cuota
            </Button>
          </>
        }
      >
        {paymentNotice && (
          <div className={styles.noticeSuccess} style={{ marginBottom: "var(--space-4)" }}>
            <Check size={16} />
            <span>{paymentNotice}</span>
          </div>
        )}

        <div className={styles.formRow}>
          <Input
            label="Monto Recibido (USD)"
            type="number"
            value={paymentAmount}
            onChange={(e) => setPaymentAmount(Number(e.target.value))}
          />

          <div className={styles.formGroup} style={{ marginBottom: 0 }}>
            <label className={styles.label}>Estado Actual de la Suscripción</label>
            <div style={{ marginTop: "6px" }}>
              <Badge variant="neutral" size="sm">
                Cuota {selectedSubForPayment ? selectedSubForPayment.quotasPaid : 0} de{" "}
                {selectedSubForPayment ? selectedSubForPayment.totalQuotas : 0} pagada(s)
              </Badge>
            </div>
          </div>
        </div>

        <div className={styles.formGroup} style={{ marginTop: "var(--space-3)" }}>
          <Input
            label="Nota interna / Comprobante de pago"
            placeholder="Ej: Transferencia Zelle #129381 o Efectivo"
            value={paymentNote}
            onChange={(e) => setPaymentNote(e.target.value)}
          />
        </div>
      </Modal>
    </div>
  );
}
