// Cliente API para comunicar con los endpoints REST de WordPress de Alezux Members

export interface DashboardStats {
  totalStudents: number;
  totalStudentsChange: string;
  activeCourses: number;
  activeCoursesChange: string;
  monthlyRevenue: number;
  monthlyRevenueChange: string;
  topClass: {
    id: number;
    title: string;
    courseTitle: string;
    views: number;
    completions: number;
    completionRate: string;
  };
  studentFlow: {
    label: string;
    students: number;
    activity: number;
  }[];
}

export interface Student {
  id: number;
  name: string;
  email: string;
  avatar: string;
  joinedDate: string;
  status: "active" | "inactive" | "blocked";
  planName: string;
  enabledCourseIds: number[];
}

export interface CreateStudentPayload {
  name: string;
  email: string;
  status?: "active" | "inactive" | "blocked";
  passwordMode: "auto" | "manual";
  password?: string;
  planName?: string;
  enabledCourseIds?: number[];
  sendWelcomeEmail?: boolean;
}

export interface UpdateStudentPayload {
  name?: string;
  email?: string;
  status?: "active" | "inactive" | "blocked";
  passwordMode?: "keep" | "manual" | "auto";
  password?: string;
  sendEmail?: boolean;
  enabledCourseIds?: number[];
}

export interface CourseFileAttachment {
  id?: string;
  name: string;
  url: string;
  size?: string;
}

export interface CourseTopic {
  id: string;
  title: string;
  cover?: string;
  description?: string;
  video_url?: string;
  duration?: string;
  files?: CourseFileAttachment[];
}

export interface CourseLesson extends CourseTopic {
  topics?: CourseTopic[];
}

export interface CourseSection {
  id: string;
  title: string;
  cover?: string;
  description?: string;
  lessons: CourseLesson[];
  topics?: CourseTopic[];
}

export interface Course {
  id: number;
  title: string;
  slug: string;
  description: string;
  thumbnail: string;
  banner?: string;
  price?: number;
  linkedPlanId?: number | null;
  status: "publish" | "draft";
  studentCount: number;
  sections: CourseSection[];
}

export interface FinancePlan {
  id: number;
  name: string;
  courseId: number;
  courseTitle: string;
  totalQuotas: number;
  quotaAmount: number;
  totalAmount: number;
  frequency?: string;
  whatsapp_number?: string;
  access_rules?: any;
  token: string;
  checkoutUrl: string;
  subscribersCount: number;
}

export interface SaleTransaction {
  id: number;
  student: string;
  studentEmail: string;
  method: string;
  amount: number;
  currency: string;
  course: string;
  quotasDesc: string;
  status: "succeeded" | "pending" | "failed" | "refunded" | string;
  date: string;
  ref: string;
}

export interface SubscriptionItem {
  id: number;
  student: string;
  studentEmail: string;
  studentAvatar: string;
  plan: string;
  totalQuotas: number;
  quotasPaid: number;
  percent: number;
  amount: number;
  status: "active" | "completed" | "past_due" | "canceled" | "pending" | string;
  nextPayment: string;
  nextPaymentRaw?: string;
  stripeId?: string;
}

export interface FinanceSettings {
  stripe_public_key: string;
  stripe_secret_key: string;
  webhook_url?: string;
}

export interface MarketingAutomation {
  id: string;
  name: string;
  category: "all" | "registro" | "finanzas" | "cursos" | "logros";
  triggerEvent: string;
  subject: string;
  body: string;
  enabled: boolean;
  sentCount: number;
  variables?: string[];
  description?: string;
}

export interface MarketingSettings {
  from_name: string;
  from_email: string;
  logo_url: string;
  smtp_enabled: boolean;
  smtp_host: string;
  smtp_port: number;
  smtp_secure: "tls" | "ssl" | "none";
  smtp_auth: boolean;
  smtp_username: string;
  smtp_password: string;
  smtp_skip_ssl: boolean;
}

export interface PlatformSettings {
  academy_name: string;
  academy_logo: string;
  theme: "dark" | "light";
  accent: string;
}

export interface EmailLogItem {
  id: number;
  type?: string;
  recipient: string;
  date: string;
  status: string;
  rawStatus: string;
  openedAt?: string | null;
}

// Datos Mock de respaldo (usados en Vite local dev o si la API de WP aún no tiene datos)
const MOCK_STATS: DashboardStats = {
  totalStudents: 1420,
  totalStudentsChange: "+12.4%",
  activeCourses: 18,
  activeCoursesChange: "+2 este mes",
  monthlyRevenue: 18450,
  monthlyRevenueChange: "+18.2%",
  topClass: {
    id: 101,
    title: "Estrategias de Escalamiento con Meta Ads 2026",
    courseTitle: "Master en Marketing Digital & Performance",
    views: 4892,
    completions: 3410,
    completionRate: "69.7%",
  },
  studentFlow: [
    { label: "Lun", students: 780, activity: 1240 },
    { label: "Mar", students: 890, activity: 1420 },
    { label: "Mié", students: 1040, activity: 1890 },
    { label: "Jue", students: 1120, activity: 2010 },
    { label: "Vie", students: 990, activity: 1750 },
    { label: "Sáb", students: 1250, activity: 2400 },
    { label: "Dom", students: 1420, activity: 2850 },
  ],
};

const MOCK_COURSES: Course[] = [
  {
    id: 1,
    title: "Master en Marketing Digital & Performance",
    slug: "marketing-digital-performance",
    description: "Domina la adquisición de tráfico, funnels y optimización de conversión.",
    thumbnail: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
    banner: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80",
    price: 197,
    linkedPlanId: 1,
    status: "publish",
    studentCount: 520,
    sections: [
      {
        id: "sec-1",
        title: "Módulo 1: Fundamentos y Mentalidad del Media Buyer",
        cover: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
        lessons: [
          { 
            id: "les-1", 
            title: "Bienvenida y Hoja de Ruta", 
            duration: "12m",
            cover: "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80",
            description: "Introducción completa a la formación, mentalidad del comprador de medios y configuración del entorno.",
            video_url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            files: [
              { id: "f-1", name: "Roadmap_Media_Buyer_2026.pdf", url: "https://example.com/files/roadmap.pdf", size: "2.4 MB" }
            ]
          },
          { 
            id: "les-2", 
            title: "Configuración de Business Manager y Pixeles", 
            duration: "25m",
            cover: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&auto=format&fit=crop&q=80",
            description: "Paso a paso para crear cuentas publicitarias, dominios verificados y API de conversiones.",
            video_url: "https://vimeo.com/76979871",
            files: [
              { id: "f-2", name: "Checklist_Seguridad_BM.xlsx", url: "https://example.com/files/checklist.xlsx", size: "850 KB" }
            ]
          },
          { 
            id: "les-3", 
            title: "Estructuras de Campañas CBO vs ABO", 
            duration: "32m",
            cover: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
            description: "Cuándo utilizar optimización a nivel de campaña o de conjunto de anuncios para presupuestos escalables.",
            video_url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            files: []
          },
        ],
      },
      {
        id: "sec-2",
        title: "Módulo 2: Creativos de Alta Conversión",
        cover: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&auto=format&fit=crop&q=80",
        lessons: [
          { 
            id: "les-4", 
            title: "Psicología de Hooks y Primeros 3 Segundos", 
            duration: "18m",
            cover: "https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=600&auto=format&fit=crop&q=80",
            description: "Fórmulas de ganchos que retienen la atención en TikTok e Instagram Reels.",
            video_url: "https://vimeo.com/76979871",
            files: [
              { id: "f-3", name: "Plantilla_50_Hooks_Virales.pdf", url: "https://example.com/files/hooks.pdf", size: "4.1 MB" }
            ]
          },
          { 
            id: "les-5", 
            title: "Estrategias de Escalamiento con Meta Ads 2026", 
            duration: "45m",
            cover: "https://images.unsplash.com/photo-1533750516457-a7f992034fec?w=600&auto=format&fit=crop&q=80",
            description: "Cómo duplicar y escalar presupuestos diarios sin arruinar el ROAS ni disparar el CPA.",
            video_url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            files: []
          },
        ],
      },
    ],
  },
  {
    id: 2,
    title: "Desarrollo Web Full Stack con WordPress & React",
    slug: "desarrollo-web-fullstack",
    description: "Crea plataformas escalables, plugins personalizados y arquitecturas modernas.",
    thumbnail: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80",
    banner: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&auto=format&fit=crop&q=80",
    price: 297,
    linkedPlanId: 2,
    status: "publish",
    studentCount: 390,
    sections: [
      {
        id: "sec-3",
        title: "Módulo 1: Arquitectura de Plugins en WordPress",
        cover: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80",
        lessons: [
          { 
            id: "les-6", 
            title: "Estructura Modular 'Lego-style'", 
            duration: "28m",
            cover: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80",
            description: "Separación por capas: Core, Modules, Views y Assets para mantenimiento ágil.",
            video_url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            files: [
              { id: "f-4", name: "Boilerplate_Plugin_Starter.zip", url: "https://example.com/files/boilerplate.zip", size: "1.2 MB" }
            ]
          },
          { 
            id: "les-7", 
            title: "Integración con Elementor Widgets", 
            duration: "35m",
            cover: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&auto=format&fit=crop&q=80",
            description: "Cómo registrar widgets personalizados con controles de estilo y renderizado dinámico.",
            video_url: "https://vimeo.com/76979871",
            files: []
          },
        ],
      },
    ],
  },
  {
    id: 3,
    title: "Ventas y Cierre de Clientes High-Ticket",
    slug: "ventas-high-ticket",
    description: "Aprende el método consultivo para cerrar contratos de alto valor.",
    thumbnail: "https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&auto=format&fit=crop&q=80",
    banner: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&auto=format&fit=crop&q=80",
    price: 147,
    linkedPlanId: 3,
    status: "publish",
    studentCount: 510,
    sections: [
      {
        id: "sec-4",
        title: "Módulo 1: Prospección y Cualificación",
        cover: "https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&auto=format&fit=crop&q=80",
        lessons: [
          { 
            id: "les-8", 
            title: "Framework de Descubrimiento de Dolor", 
            duration: "40m",
            cover: "https://images.unsplash.com/photo-1557804506-669a67965ba0?w=600&auto=format&fit=crop&q=80",
            description: "Guión de preguntas de alta cualificación para filtrar prospectos no calificados antes de la llamada.",
            video_url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
            files: [
              { id: "f-5", name: "Script_Llamada_Cualificacion.pdf", url: "https://example.com/files/script.pdf", size: "1.8 MB" }
            ]
          },
        ],
      },
    ],
  },
];

const MOCK_STUDENTS: Student[] = [
  {
    id: 101,
    name: "Carlos Mendoza",
    email: "carlos.mendoza@empresa.com",
    avatar: "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80",
    joinedDate: "05/10/2026",
    status: "active",
    planName: "Plan Anual VIP (4 Cuotas)",
    enabledCourseIds: [1, 2],
  },
  {
    id: 102,
    name: "Valeria Gómez",
    email: "valeria.g@marketingagency.io",
    avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80",
    joinedDate: "02/10/2026",
    status: "active",
    planName: "Plan Pro Mensual",
    enabledCourseIds: [1, 3],
  },
  {
    id: 103,
    name: "Sebastián Rivas",
    email: "srivas@digitalgrowth.com",
    avatar: "https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150&auto=format&fit=crop&q=80",
    joinedDate: "28/09/2026",
    status: "active",
    planName: "Pago Único Completo",
    enabledCourseIds: [1, 2, 3],
  },
  {
    id: 104,
    name: "Mariana Silva",
    email: "mariana.silva@outlook.com",
    avatar: "https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150&auto=format&fit=crop&q=80",
    joinedDate: "15/09/2026",
    status: "inactive",
    planName: "Plan Trimestral",
    enabledCourseIds: [1],
  },
];

const MOCK_PLANS: FinancePlan[] = [
  {
    id: 1,
    name: "Master Full Access - 4 Cuotas",
    courseId: 1,
    courseTitle: "Master en Marketing Digital & Performance",
    totalQuotas: 4,
    quotaAmount: 97,
    totalAmount: 388,
    token: "token_plan_mkt_4q",
    checkoutUrl: `${window.location.origin}/?alezux_action=checkout&token=token_plan_mkt_4q`,
    subscribersCount: 142,
  },
  {
    id: 2,
    name: "Desarrollo Full Stack - Pago Único",
    courseId: 2,
    courseTitle: "Desarrollo Web Full Stack con WordPress & React",
    totalQuotas: 1,
    quotaAmount: 297,
    totalAmount: 297,
    token: "token_plan_dev_single",
    checkoutUrl: `${window.location.origin}/?alezux_action=checkout&token=token_plan_dev_single`,
    subscribersCount: 88,
  },
  {
    id: 3,
    name: "Membresía All-Access Anual",
    courseId: 0,
    courseTitle: "Todos los Cursos",
    totalQuotas: 12,
    quotaAmount: 49,
    totalAmount: 588,
    token: "token_all_access_annual",
    checkoutUrl: `${window.location.origin}/?alezux_action=checkout&token=token_all_access_annual`,
    subscribersCount: 230,
  },
];

const MOCK_SALES: SaleTransaction[] = [
  {
    id: 501,
    student: "Carlos Mendoza",
    studentEmail: "carlos.mendoza@empresa.com",
    method: "Stripe",
    amount: 97,
    currency: "USD",
    course: "Master en Marketing Digital & Performance",
    quotasDesc: "Recurrente (2/4)",
    status: "succeeded",
    date: "08/10/2026 16:45",
    ref: "ch_3N1abc992kd",
  },
  {
    id: 502,
    student: "Valeria Gómez",
    studentEmail: "valeria.g@marketingagency.io",
    method: "Stripe",
    amount: 297,
    currency: "USD",
    course: "Desarrollo Web Full Stack con WordPress",
    quotasDesc: "Pago Único",
    status: "succeeded",
    date: "07/10/2026 11:20",
    ref: "ch_3M4xyz881aa",
  },
  {
    id: 503,
    student: "Sebastián Rivas",
    studentEmail: "srivas@digitalgrowth.com",
    method: "Stripe",
    amount: 49,
    currency: "USD",
    course: "Membresía All-Access Anual",
    quotasDesc: "Recurrente (1/12)",
    status: "succeeded",
    date: "06/10/2026 18:05",
    ref: "ch_3L9qwe772bb",
  },
  {
    id: 504,
    student: "Mariana Silva",
    studentEmail: "mariana.silva@outlook.com",
    method: "Stripe",
    amount: 97,
    currency: "USD",
    course: "Master en Marketing Digital & Performance",
    quotasDesc: "Recurrente (1/4)",
    status: "failed",
    date: "05/10/2026 09:30",
    ref: "ch_3K2err661cc",
  },
  {
    id: 505,
    student: "Andrés Delgado",
    studentEmail: "adelgado@innovacion.pe",
    method: "Manual",
    amount: 97,
    currency: "USD",
    course: "Master en Marketing Digital & Performance",
    quotasDesc: "Recurrente (3/4)",
    status: "succeeded",
    date: "04/10/2026 14:15",
    ref: "MANUAL-9821ABCD",
  },
];

const MOCK_SUBSCRIPTIONS: SubscriptionItem[] = [
  {
    id: 201,
    student: "Carlos Mendoza",
    studentEmail: "carlos.mendoza@empresa.com",
    studentAvatar: "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80",
    plan: "Master Full Access - 4 Cuotas",
    totalQuotas: 4,
    quotasPaid: 2,
    percent: 50,
    amount: 97,
    status: "active",
    nextPayment: "08/11/2026",
    nextPaymentRaw: "2026-11-08",
    stripeId: "sub_1N1xyz993",
  },
  {
    id: 202,
    student: "Sebastián Rivas",
    studentEmail: "srivas@digitalgrowth.com",
    studentAvatar: "https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150&auto=format&fit=crop&q=80",
    plan: "Membresía All-Access Anual",
    totalQuotas: 12,
    quotasPaid: 1,
    percent: 8,
    amount: 49,
    status: "active",
    nextPayment: "06/11/2026",
    nextPaymentRaw: "2026-11-06",
    stripeId: "sub_1M4abc882",
  },
  {
    id: 203,
    student: "Mariana Silva",
    studentEmail: "mariana.silva@outlook.com",
    studentAvatar: "https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150&auto=format&fit=crop&q=80",
    plan: "Master Full Access - 4 Cuotas",
    totalQuotas: 4,
    quotasPaid: 1,
    percent: 25,
    amount: 97,
    status: "past_due",
    nextPayment: "05/10/2026 (Atrasado)",
    nextPaymentRaw: "2026-10-05",
    stripeId: "sub_1K2err661",
  },
  {
    id: 204,
    student: "Andrés Delgado",
    studentEmail: "adelgado@innovacion.pe",
    studentAvatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80",
    plan: "Master Full Access - 4 Cuotas",
    totalQuotas: 4,
    quotasPaid: 4,
    percent: 100,
    amount: 97,
    status: "completed",
    nextPayment: "Pagado Totalmente",
    stripeId: "sub_1J9qwe550",
  },
];

const MOCK_AUTOMATIONS: MarketingAutomation[] = [
  {
    id: "student_welcome",
    name: "Registro - Bienvenida",
    category: "registro",
    triggerEvent: "Al registrarse o adquirir membresía",
    subject: "Bienvenido a {{site_name}} - Tus Credenciales",
    body: "¡Hola {{user.first_name}}! Tu cuenta ha sido creada exitosamente. Estamos emocionados de tenerte aquí.\n\nUsuario: {{user.username}}\nContraseña: {{password}}\nCurso: {{course_title}}\n\nIngresa a la plataforma aquí: {{login_url}}",
    enabled: true,
    sentCount: 1420,
    variables: ["{{user.name}}", "{{user.username}}", "{{user.email}}", "{{password}}", "{{course_title}}", "{{login_url}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía automáticamente cuando un estudiante se registra exitosamente en la plataforma.",
  },
  {
    id: "user_recover_password",
    name: "Seguridad - Recuperar Contraseña",
    category: "registro",
    triggerEvent: "Al solicitar recuperar contraseña",
    subject: "Recuperación de Contraseña - {{site_name}}",
    body: "Hola {{user.name}},\n\nHemos recibido una solicitud para restablecer tu contraseña. Si no fuiste tú, puedes ignorar este correo.\n\nPara restablecerla, haz clic en el siguiente enlace:\n{{reset_link}}",
    enabled: true,
    sentCount: 215,
    variables: ["{{user.name}}", "{{reset_link}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía cuando un usuario solicita restablecer su contraseña desde el formulario de acceso.",
  },
  {
    id: "admin_reset_password",
    name: "Seguridad - Reset por Admin",
    category: "registro",
    triggerEvent: "Al actualizar clave desde panel admin",
    subject: "Tu contraseña ha sido restablecida",
    body: "Hola {{user.name}},\n\nUn administrador ha actualizado tus credenciales de acceso a la plataforma.\n\nNueva contraseña: {{new_password}}\n\nTe recomendamos cambiarla después de iniciar sesión en {{login_url}}",
    enabled: true,
    sentCount: 48,
    variables: ["{{user.name}}", "{{new_password}}", "{{password}}", "{{login_url}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía cuando un administrador restablece manualmente la contraseña de un usuario.",
  },
  {
    id: "payment_success",
    name: "Finanzas - Pago Exitoso",
    category: "finanzas",
    triggerEvent: "Al procesar cobro exitosamente",
    subject: "Confirmación de Pago - {{plan_name}}",
    body: "¡Pago Recibido!\n\nHola {{user.name}}, hemos procesado tu pago correctamente para la membresía {{plan_name}} por un importe de {{price}} (Referencia: {{amount}}).\n\n¡Gracias por tu confianza!",
    enabled: true,
    sentCount: 940,
    variables: ["{{user.name}}", "{{plan_name}}", "{{price}}", "{{date}}", "{{amount}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía al usuario confirmando que su pago se ha procesado correctamente.",
  },
  {
    id: "payment_failed",
    name: "Finanzas - Pago Fallido",
    category: "finanzas",
    triggerEvent: "Al fallar cobro recurrente o cuota",
    subject: "Acción Requerida: Pago Fallido de {{plan_name}}",
    body: "Hola {{user.name}},\n\nIntentamos procesar la renovación de tu membresía {{plan_name}} pero la transacción ha fallado.\n\nPor favor actualiza tu método de pago para evitar la interrupción de tus accesos formativos en {{retry_url}}.",
    enabled: true,
    sentCount: 34,
    variables: ["{{user.name}}", "{{plan_name}}", "{{attempt_date}}", "{{retry_url}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía cuando un intento de pago o renovación de cuota falla.",
  },
  {
    id: "payment_reminder",
    name: "Finanzas - Recordatorio Renovación",
    category: "finanzas",
    triggerEvent: "3 días antes de renovación de cuota",
    subject: "Recordatorio: Próxima cuota de {{plan_name}} - {{site_name}}",
    body: "Hola {{user.name}},\n\nTe recordamos que tu próxima cuota o renovación para tu membresía {{plan_name}} se procesará el {{renewal_date}} por un valor de {{price}}.\n\nAsegúrate de tener fondos disponibles para continuar sin pausas.",
    enabled: true,
    sentCount: 680,
    variables: ["{{user.name}}", "{{plan_name}}", "{{renewal_date}}", "{{price}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía días antes de que una suscripción se renueve automáticamente.",
  },
  {
    id: "subscription_cancelled",
    name: "Finanzas - Suscripción Cancelada",
    category: "finanzas",
    triggerEvent: "Al cancelar membresía o plan",
    subject: "Confirmación de Suscripción Cancelada - {{plan_name}}",
    body: "Hola {{user.name}},\n\nTe informamos que tu suscripción a {{plan_name}} ha sido cancelada. Mantendrás acceso a los contenidos hasta el {{end_date}}.\n\nEsperamos verte pronto de regreso.",
    enabled: true,
    sentCount: 86,
    variables: ["{{user.name}}", "{{plan_name}}", "{{end_date}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía cuando una suscripción es cancelada por el usuario o administrador.",
  },
  {
    id: "achievement_assigned",
    name: "Logros - Nuevo Logro Desbloqueado",
    category: "logros",
    triggerEvent: "Al desbloquear insignia o logro",
    subject: "¡Ganaste un nuevo Logro! - {{achievement_name}}",
    body: "¡Felicidades, {{user.name}}!\n\nHas desbloqueado un nuevo logro en la academia: {{achievement_name}}.\n\n{{achievement_desc}}\n\n¡Sigue adelante con tu progreso!",
    enabled: true,
    sentCount: 520,
    variables: ["{{user.name}}", "{{achievement_name}}", "{{achievement_desc}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía cuando un estudiante desbloquea un nuevo logro o insignia.",
  },
  {
    id: "inactivity_alert",
    name: "Retención - Alerta de Inactividad",
    category: "logros",
    triggerEvent: "Tras 7+ días continuos de inactividad",
    subject: "¡Te extrañamos en {{site_name}}!",
    body: "¡Hola {{user.name}}!\n\nHemos notado que llevas {{days_inactive}} días sin ingresar a la plataforma. No pierdas el ritmo, tus lecciones están esperándote para continuar.\n\nIngresa aquí: {{login_url}}",
    enabled: true,
    sentCount: 184,
    variables: ["{{user.name}}", "{{days_inactive}}", "{{login_url}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía automáticamente si el estudiante no ingresa por varios días seguidos.",
  },
  {
    id: "course_available",
    name: "Cursos - Nuevo Curso Disponible",
    category: "cursos",
    triggerEvent: "Al publicar un nuevo curso formativo",
    subject: "¡Nuevo Curso Lanzado: {{course_name}}!",
    body: "Hola {{user.name}},\n\nEstamos muy emocionados de presentarte el nuevo curso disponible en la plataforma:\n{{course_name}}\n\nEsperamos que este contenido te ayude a seguir creciendo profesionalmente.",
    enabled: true,
    sentCount: 3100,
    variables: ["{{user.name}}", "{{course_name}}", "{{courses_list}}", "{{site_name}}", "{{logo_url}}"],
    description: "Notificación enviada a todos los usuarios cuando se publica un nuevo curso.",
  },
  {
    id: "lesson_available",
    name: "Cursos - Nuevas Lecciones Disponibles",
    category: "cursos",
    triggerEvent: "Al publicar nuevas lecciones",
    subject: "Novedades en tu curso: {{course_name}}",
    body: "Hola {{user.name}},\n\nSe ha añadido nuevo contenido al curso {{course_name}} en el que estás inscrito.\n\n¡No pierdas el ritmo y continúa con tu formación!",
    enabled: true,
    sentCount: 1980,
    variables: ["{{user.name}}", "{{course_name}}", "{{lessons_list}}", "{{site_name}}", "{{logo_url}}"],
    description: "Notificación a los alumnos inscritos cuando se añaden nuevas lecciones a un curso.",
  },
  {
    id: "course_completed",
    name: "Graduación - Curso Completado",
    category: "cursos",
    triggerEvent: "Al alcanzar el 100% del curso",
    subject: "¡Felicitaciones por graduarte de {{course_title}}!",
    body: "¡Increíble trabajo {{user.name}}!\n\nHas completado el 100% del curso {{course_title}}. Tu constancia ha rendido frutos y tu certificado oficial ya está disponible en tu panel.",
    enabled: true,
    sentCount: 312,
    variables: ["{{user.name}}", "{{course_title}}", "{{login_url}}", "{{site_name}}", "{{logo_url}}"],
    description: "Se envía cuando un alumno completa satisfactoriamente el 100% de los módulos de un curso.",
  },
];

class ApiService {
  private get wpData() {
    return (window as any).crezca_admin_data || (window as any).alezux_admin_data || null;
  }

  private get rootUrl() {
    return this.wpData?.root_url || "/wp-json/crezca/v1/";
  }

  private get nonce() {
    return this.wpData?.nonce || "";
  }

  // --- STATS ---
  async getDashboardStats(): Promise<DashboardStats> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}stats`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          return { ...MOCK_STATS, ...data };
        }
      }
    } catch (e) {
      console.warn("API Error, using mock stats fallback:", e);
    }
    return MOCK_STATS;
  }

  // --- COURSES ---
  async getCourses(): Promise<Course[]> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}courses`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error, using mock courses fallback:", e);
    }
    return MOCK_COURSES;
  }

  async saveCourse(courseId: number, courseData: Partial<Course>, sections?: CourseSection[]): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}courses/${courseId}/curriculum`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({
            sections: sections || courseData.sections,
            ...courseData,
          }),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error saving course:", e);
    }

    // Local mock update
    const found = MOCK_COURSES.find((c) => c.id === courseId);
    if (found) {
      if (sections) found.sections = sections;
      Object.assign(found, courseData);
    }
    return true;
  }

  async saveCourseCurriculum(courseId: number, sections: CourseSection[], courseData?: Partial<Course>): Promise<boolean> {
    return this.saveCourse(courseId, { ...(courseData || {}), sections }, sections);
  }

  async createCourse(title: string, description: string, thumbnail: string): Promise<Course> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}courses`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({ title, description, thumbnail }),
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error creating course:", e);
    }

    const newCourse: Course = {
      id: Date.now(),
      title,
      slug: title.toLowerCase().replace(/[^a-z0-9]+/g, "-"),
      description,
      thumbnail: thumbnail || "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
      banner: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&auto=format&fit=crop&q=80",
      price: 97,
      linkedPlanId: null,
      status: "publish",
      studentCount: 0,
      sections: [
        {
          id: `sec-${Date.now()}`,
          title: "Módulo 1: Introducción",
          cover: thumbnail || "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
          lessons: [
            { 
              id: `les-${Date.now()}`, 
              title: "Primera Lección de Bienvenida", 
              duration: "10m",
              cover: "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80",
              description: "Bienvenida al curso y objetivos principales de aprendizaje.",
              video_url: "",
              files: []
            },
          ],
        },
      ],
    };
    return newCourse;
  }

  async getCourseModules(courseId: number): Promise<{ id: number; title: string }[]> {
    try {
      if (this.wpData && courseId > 0) {
        const res = await fetch(`${this.rootUrl}courses/${courseId}/modules`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          if (Array.isArray(data) && data.length > 0) {
            return data;
          }
        }
      }
    } catch (e) {
      console.warn("API Error fetching course modules:", e);
    }

    // Fallback: extraer de los cursos o devolver lecciones estándar
    const found = MOCK_COURSES.find((c) => c.id === courseId);
    if (found && found.sections && found.sections.length > 0) {
      const list: { id: number; title: string }[] = [];
      let counter = 100;
      found.sections.forEach((sec) => {
        sec.lessons.forEach((l) => {
          list.push({
            id: Number(l.id.replace(/\D/g, "")) || ++counter,
            title: `${sec.title} - ${l.title}`,
          });
        });
      });
      if (list.length > 0) return list;
    }

    return [
      { id: 101, title: "Módulo 1: Fundamentos y Bienvenida" },
      { id: 102, title: "Módulo 2: Estrategias y Técnicas Principales" },
      { id: 103, title: "Módulo 3: Casos Prácticos y Configuración" },
      { id: 104, title: "Módulo 4: Proyecto Final y Certificación" },
    ];
  }

  // --- STUDENTS ---
  async getStudents(): Promise<Student[]> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}students`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error, using mock students fallback:", e);
    }
    return MOCK_STUDENTS;
  }

  async toggleStudentCourseAccess(studentId: number, courseId: number, enable: boolean): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}students/${studentId}/course-access`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({ courseId, enable }),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error toggling access:", e);
    }
    return true;
  }

  async createStudent(payload: CreateStudentPayload): Promise<{ success: boolean; student?: Student; message?: string }> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}students`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (res.ok && data.success) {
          return data;
        } else {
          return { success: false, message: data.message || "Error al crear estudiante" };
        }
      }
    } catch (e: any) {
      console.warn("API Error creating student:", e);
      return { success: false, message: e?.message || "Error de conexión" };
    }

    // Mock fallback
    const newStudent: Student = {
      id: Date.now(),
      name: payload.name || "Nuevo Alumno",
      email: payload.email,
      avatar: `https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80`,
      joinedDate: new Date().toLocaleDateString("es-ES"),
      status: payload.status || "active",
      planName: payload.planName || "Acceso Manual",
      enabledCourseIds: payload.enabledCourseIds || [],
    };
    MOCK_STUDENTS.unshift(newStudent);
    return {
      success: true,
      student: newStudent,
      message: "Alumno creado exitosamente (Modo Demo)." + (payload.passwordMode === "auto" ? " Se simula el envío del correo de bienvenida con credenciales." : ""),
    };
  }

  async updateStudent(id: number, payload: UpdateStudentPayload): Promise<{ success: boolean; student?: Student; message?: string }> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}students/${id}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (res.ok && data.success) {
          return data;
        } else {
          return { success: false, message: data.message || "Error al actualizar estudiante" };
        }
      }
    } catch (e: any) {
      console.warn("API Error updating student:", e);
      return { success: false, message: e?.message || "Error de conexión" };
    }

    // Mock fallback
    const idx = MOCK_STUDENTS.findIndex((s) => s.id === id);
    if (idx !== -1) {
      MOCK_STUDENTS[idx] = {
        ...MOCK_STUDENTS[idx],
        name: payload.name !== undefined ? payload.name : MOCK_STUDENTS[idx].name,
        email: payload.email !== undefined ? payload.email : MOCK_STUDENTS[idx].email,
        status: payload.status !== undefined ? payload.status : MOCK_STUDENTS[idx].status,
        enabledCourseIds: payload.enabledCourseIds !== undefined ? payload.enabledCourseIds : MOCK_STUDENTS[idx].enabledCourseIds,
      };
      return {
        success: true,
        student: MOCK_STUDENTS[idx],
        message: "Estudiante actualizado correctamente." + (payload.passwordMode === "auto" ? " Se simuló el envío de la nueva contraseña por correo." : ""),
      };
    }
    return { success: true, message: "Estudiante actualizado" };
  }

  // --- FINANCE ---
  async getPlans(): Promise<FinancePlan[]> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/plans`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          if (Array.isArray(data) && data.length > 0) {
            return data;
          }
        }
      }
    } catch (e) {
      console.warn("API Error, using mock plans fallback:", e);
    }
    return MOCK_PLANS;
  }

  async createPlan(data: Partial<FinancePlan>): Promise<FinancePlan> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/create-plan`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(data),
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error creating plan:", e);
    }

    const token = `token_${Math.random().toString(36).substring(2, 10)}`;
    const newPlan: FinancePlan = {
      id: Date.now(),
      name: data.name || "Nuevo Plan",
      courseId: data.courseId || 0,
      courseTitle: data.courseTitle || "Todos los Cursos",
      totalQuotas: data.totalQuotas || 1,
      quotaAmount: data.quotaAmount || 97,
      totalAmount: (data.totalQuotas || 1) * (data.quotaAmount || 97),
      frequency: data.frequency || "month",
      whatsapp_number: data.whatsapp_number || "",
      token,
      checkoutUrl: `${window.location.origin}/?alezux_action=checkout&token=${token}`,
      subscribersCount: 0,
    };
    return newPlan;
  }

  async updatePlan(id: number, data: Partial<FinancePlan>): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/plans/${id}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(data),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error updating plan:", e);
    }
    return true;
  }

  async deletePlan(id: number): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/plans/${id}`, {
          method: "DELETE",
          headers: { "X-WP-Nonce": this.nonce },
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error deleting plan:", e);
    }
    return true;
  }

  async getSales(params?: { search?: string; status?: string; page?: number; limit?: number }): Promise<{ rows: SaleTransaction[]; total: number; pages: number }> {
    try {
      if (this.wpData) {
        const searchParams = new URLSearchParams();
        if (params?.search) searchParams.append("search", params.search);
        if (params?.status) searchParams.append("status", params.status);
        if (params?.page) searchParams.append("page", params.page.toString());
        if (params?.limit) searchParams.append("limit", params.limit.toString());

        const res = await fetch(`${this.rootUrl}finance/sales?${searchParams.toString()}`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          if (Array.isArray(data.rows) && data.rows.length > 0) {
            return data;
          }
        }
      }
    } catch (e) {
      console.warn("API Error fetching sales:", e);
    }
    return {
      rows: MOCK_SALES,
      total: MOCK_SALES.length,
      pages: 1,
    };
  }

  async getSubscriptions(params?: { search?: string; page?: number; limit?: number }): Promise<{ rows: SubscriptionItem[]; total: number; pages: number }> {
    try {
      if (this.wpData) {
        const searchParams = new URLSearchParams();
        if (params?.search) searchParams.append("search", params.search);
        if (params?.page) searchParams.append("page", params.page.toString());
        if (params?.limit) searchParams.append("limit", params.limit.toString());

        const res = await fetch(`${this.rootUrl}finance/subscriptions?${searchParams.toString()}`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          if (Array.isArray(data.rows) && data.rows.length > 0) {
            return data;
          }
        }
      }
    } catch (e) {
      console.warn("API Error fetching subscriptions:", e);
    }
    return {
      rows: MOCK_SUBSCRIPTIONS,
      total: MOCK_SUBSCRIPTIONS.length,
      pages: 1,
    };
  }

  async registerManualPayment(subscriptionId: number, amount: number, note?: string): Promise<{ success: boolean; message?: string }> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/subscriptions/${subscriptionId}/payment`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({ amount, note }),
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error registering manual payment:", e);
    }
    return { success: true, message: "Pago manual registrado correctamente (Modo simulación)." };
  }

  async getFinanceSettings(): Promise<FinanceSettings> {
    const defaultSettings: FinanceSettings = {
      stripe_public_key: "",
      stripe_secret_key: "",
      webhook_url: `${window.location.origin}/?alezux_webhook=stripe`,
    };
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/settings`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error fetching finance settings:", e);
    }
    return defaultSettings;
  }

  async saveFinanceSettings(settings: FinanceSettings): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/settings`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(settings),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error saving finance settings:", e);
    }
    return true;
  }

  // --- MARKETING ---
  async getAutomations(): Promise<MarketingAutomation[]> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/automations`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          if (Array.isArray(data) && data.length > 0) {
            return data;
          }
        }
      }
    } catch (e) {
      console.warn("API Error fetching automations, using mock fallback:", e);
    }
    return MOCK_AUTOMATIONS;
  }

  async toggleAutomation(id: string, enabled: boolean): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/automations/${id}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({ enabled }),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error toggling automation:", e);
    }
    return true;
  }

  async saveAutomation(id: string, data: { subject: string; body: string; enabled: boolean }): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/automations/${id}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(data),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error saving automation:", e);
    }
    return true;
  }

  async sendTestEmail(id: string, email?: string): Promise<{ success: boolean; message?: string }> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/send-test`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({ id, email }),
        });
        if (res.ok) {
          const json = await res.json();
          return { success: json.success ?? true };
        }
      }
    } catch (e) {
      console.warn("API Error sending test email:", e);
    }
    return { success: true };
  }

  // --- MARKETING GENERAL SETTINGS ---
  async getMarketingSettings(): Promise<MarketingSettings> {
    const defaultSettings: MarketingSettings = {
      from_name: "Academia Crezca",
      from_email: "notificaciones@crezca.com",
      logo_url: "",
      smtp_enabled: false,
      smtp_host: "",
      smtp_port: 587,
      smtp_secure: "tls",
      smtp_auth: true,
      smtp_username: "",
      smtp_password: "",
      smtp_skip_ssl: false,
    };
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/settings`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          return { ...defaultSettings, ...data };
        }
      }
    } catch (e) {
      console.warn("API Error getting marketing settings:", e);
    }
    return defaultSettings;
  }

  async saveMarketingSettings(settings: MarketingSettings): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/settings`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(settings),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error saving marketing settings:", e);
    }
    return true;
  }

  // --- PLATFORM GENERAL SETTINGS (Nombre, Logo, Tema, Acento) ---
  async getPlatformSettings(): Promise<PlatformSettings> {
    const defaultSettings: PlatformSettings = {
      academy_name: this.wpData?.academy_name || "Crezca",
      academy_logo: this.wpData?.academy_logo || "",
      theme: (this.wpData?.theme === "light" ? "light" : "dark"),
      accent: this.wpData?.accent || "violet",
    };
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}platform/settings`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          const data = await res.json();
          return { ...defaultSettings, ...data };
        }
      }
    } catch (e) {
      console.warn("API Error fetching platform settings:", e);
    }
    return defaultSettings;
  }

  async savePlatformSettings(settings: Partial<PlatformSettings>): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}platform/settings`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify(settings),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error saving platform settings:", e);
    }
    return true;
  }

  async uploadLogo(file: File): Promise<string | null> {
    try {
      if (this.wpData) {
        const formData = new FormData();
        formData.append("file", file);

        const res = await fetch(`${this.rootUrl}marketing/upload-logo`, {
          method: "POST",
          headers: {
            "X-WP-Nonce": this.nonce,
          },
          body: formData,
        });

        if (res.ok) {
          const json = await res.json();
          if (json.url) return json.url;
        }
      }
    } catch (e) {
      console.warn("API Error uploading logo:", e);
    }
    return URL.createObjectURL(file);
  }

  async uploadMedia(file: File): Promise<string | null> {
    return this.uploadLogo(file);
  }

  async getAutomationLogs(typeId: string): Promise<EmailLogItem[]> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/automations/${typeId}/logs`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error fetching logs:", e);
    }
    // Mock logs de prueba si no hay conexión WP o en desarrollo
    return [
      {
        id: 1001,
        recipient: "carlos.mendoza@empresa.com",
        date: "Hoy, 14:32",
        status: "Leído",
        rawStatus: "opened",
        openedAt: "Hoy, 14:35",
      },
      {
        id: 1002,
        recipient: "valeria.g@marketingagency.io",
        date: "Ayer, 09:15",
        status: "Enviado",
        rawStatus: "sent",
      },
      {
        id: 1003,
        recipient: "srivas@digitalgrowth.com",
        date: "07/10/2026, 18:20",
        status: "Leído",
        rawStatus: "opened",
        openedAt: "07/10/2026, 19:02",
      },
      {
        id: 1004,
        recipient: "mariana.silva@outlook.com",
        date: "05/10/2026, 11:00",
        status: "Fallido",
        rawStatus: "failed",
      },
    ];
  }

  async resendMarketingLog(logId: number): Promise<{ success: boolean; message?: string }> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}marketing/logs/${logId}/resend`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error resending log:", e);
    }
    return { success: true, message: "Correo reenviado correctamente." };
  }
}

export const api = new ApiService();
