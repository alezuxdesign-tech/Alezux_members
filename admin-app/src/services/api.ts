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

export interface CourseTopic {
  id: string;
  title: string;
}

export interface CourseLesson {
  id: string;
  title: string;
  duration?: string;
  topics?: CourseTopic[];
}

export interface CourseSection {
  id: string;
  title: string;
  lessons: CourseLesson[];
}

export interface Course {
  id: number;
  title: string;
  slug: string;
  description: string;
  thumbnail: string;
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
  token: string;
  checkoutUrl: string;
  subscribersCount: number;
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
    status: "publish",
    studentCount: 520,
    sections: [
      {
        id: "sec-1",
        title: "Módulo 1: Fundamentos y Mentalidad del Media Buyer",
        lessons: [
          { id: "les-1", title: "Bienvenida y Hoja de Ruta", duration: "12m" },
          { id: "les-2", title: "Configuración de Business Manager y Pixeles", duration: "25m" },
          { id: "les-3", title: "Estructuras de Campañas CBO vs ABO", duration: "32m" },
        ],
      },
      {
        id: "sec-2",
        title: "Módulo 2: Creativos de Alta Conversión",
        lessons: [
          { id: "les-4", title: "Psicología de Hooks y Primeros 3 Segundos", duration: "18m" },
          { id: "les-5", title: "Estrategias de Escalamiento con Meta Ads 2026", duration: "45m" },
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
    status: "publish",
    studentCount: 390,
    sections: [
      {
        id: "sec-3",
        title: "Módulo 1: Arquitectura de Plugins en WordPress",
        lessons: [
          { id: "les-6", title: "Estructura Modular 'Lego-style'", duration: "28m" },
          { id: "les-7", title: "Integración con Elementor Widgets", duration: "35m" },
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
    status: "publish",
    studentCount: 510,
    sections: [
      {
        id: "sec-4",
        title: "Módulo 1: Prospección y Cualificación",
        lessons: [
          { id: "les-8", title: "Framework de Descubrimiento de Dolor", duration: "40m" },
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

  async saveCourseCurriculum(courseId: number, sections: CourseSection[]): Promise<boolean> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}courses/${courseId}/curriculum`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": this.nonce,
          },
          body: JSON.stringify({ sections }),
        });
        return res.ok;
      }
    } catch (e) {
      console.warn("API Error saving curriculum:", e);
    }
    return true; // Local success
  }

  async createCourse(title: string, description: string, thumbnail: string): Promise<Course> {
    const newCourse: Course = {
      id: Date.now(),
      title,
      slug: title.toLowerCase().replace(/[^a-z0-9]+/g, "-"),
      description,
      thumbnail: thumbnail || "https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop&q=80",
      status: "publish",
      studentCount: 0,
      sections: [
        {
          id: `sec-${Date.now()}`,
          title: "Módulo 1: Introducción",
          lessons: [
            { id: `les-${Date.now()}`, title: "Primera Lección de Bienvenida", duration: "10m" },
          ],
        },
      ],
    };
    return newCourse;
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

  // --- FINANCE ---
  async getPlans(): Promise<FinancePlan[]> {
    try {
      if (this.wpData) {
        const res = await fetch(`${this.rootUrl}finance/plans`, {
          headers: { "X-WP-Nonce": this.nonce },
        });
        if (res.ok) {
          return await res.json();
        }
      }
    } catch (e) {
      console.warn("API Error, using mock plans fallback:", e);
    }
    return MOCK_PLANS;
  }

  async createPlan(data: { name: string; courseId: number; courseTitle: string; totalQuotas: number; quotaAmount: number }): Promise<FinancePlan> {
    const token = `token_${Math.random().toString(36).substring(2, 10)}`;
    const newPlan: FinancePlan = {
      id: Date.now(),
      name: data.name,
      courseId: data.courseId,
      courseTitle: data.courseTitle,
      totalQuotas: data.totalQuotas,
      quotaAmount: data.quotaAmount,
      totalAmount: data.totalQuotas * data.quotaAmount,
      token,
      checkoutUrl: `${window.location.origin}/?alezux_action=checkout&token=${token}`,
      subscribersCount: 0,
    };
    return newPlan;
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
}

export const api = new ApiService();
