import React, { useState, useEffect, useRef } from "react";
import ReactDOM from "react-dom";
import { 
  Bell, 
  Calendar, 
  BookOpen, 
  MessageSquare, 
  Check,
  CheckCheck, 
  Trash2, 
  Info, 
  X,
  Award,
  Loader2
} from "lucide-react";
import { api, NotificationItem } from "../services/api";
import styles from "./NotificationsPopover.module.css";

export type { NotificationItem };

interface NotificationsPopoverProps {
  isOpen: boolean;
  onClose: () => void;
  onNavigate?: (tab: string, targetId?: string) => void;
  appMode: "admin" | "student" | "instructor";
  onUpdateCount?: (count: number) => void;
}

export const NotificationsPopover: React.FC<NotificationsPopoverProps> = ({
  isOpen,
  onClose,
  onNavigate,
  appMode,
  onUpdateCount,
}) => {
  const [filter, setFilter] = useState<"all" | "unread">("all");
  const [loading, setLoading] = useState(false);
  const [notifications, setNotifications] = useState<NotificationItem[]>(() => {
    try {
      const stored = localStorage.getItem("crezca_notifications");
      if (stored) {
        const parsed = JSON.parse(stored);
        if (Array.isArray(parsed)) return parsed;
      }
    } catch (e) {
      // fallback
    }
    return [];
  });

  const popoverRef = useRef<HTMLDivElement | null>(null);

  // Cargar notificaciones reales del servidor al montar o al abrir
  useEffect(() => {
    let isMounted = true;
    const fetchFresh = async () => {
      if (notifications.length === 0) setLoading(true);
      try {
        const res = await api.getNotifications();
        if (isMounted && res && Array.isArray(res.notifications)) {
          setNotifications(res.notifications);
          localStorage.setItem("crezca_notifications", JSON.stringify(res.notifications));
          if (onUpdateCount) {
            onUpdateCount(res.unread_count);
          }
        }
      } catch (err) {
        console.error("Error al obtener notificaciones:", err);
      } finally {
        if (isMounted) setLoading(false);
      }
    };

    if (isOpen) {
      fetchFresh();
    }
    return () => {
      isMounted = false;
    };
  }, [isOpen]);

  // Actualizar count y localStorage cada vez que cambia notifications
  const syncState = (next: NotificationItem[]) => {
    setNotifications(next);
    localStorage.setItem("crezca_notifications", JSON.stringify(next));
    const unread = next.filter((n) => !n.read).length;
    if (onUpdateCount) {
      onUpdateCount(unread);
    }
  };

  // Cerrar al presionar Escape
  useEffect(() => {
    if (!isOpen) return;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };

    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const unreadCount = notifications.filter((n) => !n.read).length;
  const filteredList = filter === "unread" 
    ? notifications.filter((n) => !n.read) 
    : notifications;

  const handleMarkAllAsRead = async (e: React.MouseEvent) => {
    e.stopPropagation();
    const next = notifications.map((n) => ({ ...n, read: true }));
    syncState(next);
    await api.markNotificationRead("all");
  };

  const handleToggleRead = async (e: React.MouseEvent, id: string) => {
    e.stopPropagation();
    const item = notifications.find((n) => n.id === id);
    const willBeRead = item ? !item.read : true;
    const next = notifications.map((n) =>
      n.id === id ? { ...n, read: willBeRead } : n
    );
    syncState(next);

    if (willBeRead) {
      await api.markNotificationRead(id);
    }
  };

  const handleDeleteItem = async (e: React.MouseEvent, id: string) => {
    e.stopPropagation();
    const next = notifications.filter((n) => n.id !== id);
    syncState(next);
    await api.deleteNotification(id);
  };

  const handleClearAll = async (e: React.MouseEvent) => {
    e.stopPropagation();
    syncState([]);
    await api.clearNotifications(false);
  };

  const handleClearRead = async (e: React.MouseEvent) => {
    e.stopPropagation();
    const next = notifications.filter((n) => !n.read);
    syncState(next);
    await api.clearNotifications(true);
  };

  const handleClickItem = async (item: NotificationItem) => {
    if (!item.read) {
      const next = notifications.map((n) =>
        n.id === item.id ? { ...n, read: true } : n
      );
      syncState(next);
      api.markNotificationRead(item.id).catch(() => {});
    }
    if (item.targetTab && onNavigate) {
      onNavigate(item.targetTab, item.targetId);
    }
    onClose();
  };

  const getIcon = (type: NotificationItem["type"]) => {
    switch (type) {
      case "event":
        return <Calendar size={17} />;
      case "course":
        return <BookOpen size={17} />;
      case "community":
        return <MessageSquare size={17} />;
      case "system":
        return <Award size={17} />;
      default:
        return <Info size={17} />;
    }
  };

  const popoverContent = (
    <>
      <div 
        className={styles.popoverOverlay} 
        onClick={(e) => {
          e.stopPropagation();
          onClose();
        }} 
      />
      <div 
        ref={popoverRef} 
        className={styles.popoverContainer} 
        onClick={(e) => e.stopPropagation()}
      >
        <div className={styles.header}>
          <div className={styles.headerTitleRow}>
            <h4 className={styles.headerTitle}>Notificaciones</h4>
            {unreadCount > 0 && (
              <span className={styles.unreadChip}>{unreadCount} nuevas</span>
            )}
          </div>
          <div className={styles.headerActions}>
            {unreadCount > 0 && (
              <button
                type="button"
                className={styles.markAllBtn}
                onClick={handleMarkAllAsRead}
                title="Marcar todas como leídas"
              >
                <CheckCheck size={14} />
                <span>Leer todas</span>
              </button>
            )}
            {notifications.length > 0 && (
              <button
                type="button"
                className={styles.clearAllBtn}
                onClick={handleClearAll}
                title="Eliminar todas las notificaciones"
              >
                <Trash2 size={13} />
                <span>Limpiar</span>
              </button>
            )}
            <button
              type="button"
              className={styles.closePopoverBtn}
              onClick={onClose}
              title="Cerrar notificaciones"
              aria-label="Cerrar"
            >
              <X size={16} />
            </button>
          </div>
        </div>

        <div className={styles.tabsRow}>
          <button
            type="button"
            className={`${styles.tabBtn} ${filter === "all" ? styles.tabActive : ""}`}
            onClick={() => setFilter("all")}
          >
            Todas ({notifications.length})
          </button>
          <button
            type="button"
            className={`${styles.tabBtn} ${filter === "unread" ? styles.tabActive : ""}`}
            onClick={() => setFilter("unread")}
          >
            No leídas ({unreadCount})
          </button>
        </div>

        <div className={styles.list}>
          {loading && notifications.length === 0 ? (
            <div className={styles.emptyState}>
              <Loader2 size={24} className="spin" style={{ opacity: 0.5, animation: "spin 1s linear infinite" }} />
              <p>Cargando notificaciones...</p>
            </div>
          ) : filteredList.length === 0 ? (
            <div className={styles.emptyState}>
              <Bell size={24} style={{ opacity: 0.3 }} />
              <p>No tienes notificaciones {filter === "unread" ? "sin leer" : ""}</p>
            </div>
          ) : (
            filteredList.map((item) => (
              <div
                key={item.id}
                className={`${styles.item} ${!item.read ? styles.unread : ""}`}
                onClick={() => handleClickItem(item)}
              >
                <div className={styles.iconWrap}>
                  {getIcon(item.type)}
                </div>
                <div className={styles.itemContent}>
                  <div className={styles.itemTitle}>{item.title}</div>
                  <div className={styles.itemDesc}>{item.message}</div>
                  <div className={styles.itemTime}>{item.time}</div>
                </div>

                <div className={styles.itemRightSide}>
                  {!item.read && <span className={styles.unreadDot} />}
                  <div className={styles.itemActions}>
                    <button
                      type="button"
                      className={`${styles.itemActionBtn} ${item.read ? styles.actionRead : styles.actionUnread}`}
                      onClick={(e) => handleToggleRead(e, item.id)}
                      title={item.read ? "Marcar como no leída" : "Marcar como leída"}
                      aria-label={item.read ? "Marcar como no leída" : "Marcar como leída"}
                    >
                      {item.read ? <CheckCheck size={13} /> : <Check size={13} />}
                    </button>
                    <button
                      type="button"
                      className={`${styles.itemActionBtn} ${styles.actionDelete}`}
                      onClick={(e) => handleDeleteItem(e, item.id)}
                      title="Eliminar notificación"
                      aria-label="Eliminar"
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>

        <div className={styles.footer}>
          <span className={styles.footerText}>
            {appMode === "admin" ? "Notificaciones de la Academia" : (appMode === "instructor" ? "Panel del Docente" : "Campus Virtual del Estudiante")}
          </span>
          {notifications.some((n) => n.read) && (
            <button
              type="button"
              className={styles.clearReadFooterBtn}
              onClick={handleClearRead}
              title="Eliminar notificaciones leídas"
            >
              <Trash2 size={11} />
              <span>Borrar leídas</span>
            </button>
          )}
        </div>
      </div>
    </>
  );

  // En móvil montamos directo en body para evitar problemas de stacking context / backdrop-filter
  const isMobileView = typeof window !== "undefined" && window.innerWidth <= 768;
  if (isMobileView && typeof document !== "undefined") {
    return ReactDOM.createPortal(popoverContent, document.body);
  }

  return popoverContent;
};
