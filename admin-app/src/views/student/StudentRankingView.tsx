import React, { useState, useEffect } from "react";
import { 
  Trophy, 
  Crown, 
  Flame, 
  Zap, 
  Sparkles, 
  Medal, 
  ArrowUpRight,
  TrendingUp,
  Award
} from "lucide-react";
import { api, LeaderboardResponse, LeaderboardItem } from "../../services/api";
import styles from "./StudentRankingView.module.css";

interface StudentRankingViewProps {
  currentUserId?: number;
}

export const StudentRankingView: React.FC<StudentRankingViewProps> = ({ currentUserId }) => {
  const [period, setPeriod] = useState<"all" | "month" | "week">("all");
  const [data, setData] = useState<LeaderboardResponse | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchRanking = async (selectedPeriod: "all" | "month" | "week") => {
    setLoading(true);
    try {
      const res = await api.getRanking(selectedPeriod);
      setData(res);
    } catch (e) {
      console.error("Error cargando ranking:", e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchRanking(period);
  }, [period]);

  const top1 = data?.leaderboard.find((i) => i.rank === 1);
  const top2 = data?.leaderboard.find((i) => i.rank === 2);
  const top3 = data?.leaderboard.find((i) => i.rank === 3);
  const restItems = data?.leaderboard.filter((i) => i.rank > 3) || [];
  const currentUser = data?.currentUser;

  return (
    <div className={styles.container}>
      {/* Encabezado */}
      <div className={styles.header}>
        <div className={styles.headerInfo}>
          <h1>
            <Trophy size={28} color="#facc15" />
            <span>Ranking & Salón de la Fama</span>
          </h1>
          <p>Compite sanamente, completa lecciones, gana XP y escala a la cima de la academia.</p>
        </div>

        {/* Selector de periodo */}
        <div className={styles.periodSelector}>
          <button
            type="button"
            className={`${styles.periodBtn} ${period === "week" ? styles.periodBtnActive : ""}`}
            onClick={() => setPeriod("week")}
          >
            Esta Semana
          </button>
          <button
            type="button"
            className={`${styles.periodBtn} ${period === "month" ? styles.periodBtnActive : ""}`}
            onClick={() => setPeriod("month")}
          >
            Este Mes
          </button>
          <button
            type="button"
            className={`${styles.periodBtn} ${period === "all" ? styles.periodBtnActive : ""}`}
            onClick={() => setPeriod("all")}
          >
            Histórico
          </button>
        </div>
      </div>

      {/* Podio Top 3 */}
      <div className={styles.podiumContainer}>
        {/* 2do Lugar (Plata) */}
        {top2 ? (
          <div className={styles.podiumStep}>
            <div className={styles.avatarWrapper}>
              <img
                src={top2.avatar || `https://api.dicebear.com/7.x/initials/svg?seed=${top2.name}`}
                alt={top2.name}
                className={`${styles.podiumAvatar} ${styles.secondAvatar}`}
              />
              <div className={`${styles.rankNumberBadge} ${styles.rank2Badge}`}>2</div>
            </div>
            <div className={styles.podiumName}>{top2.name}</div>
            <div className={styles.podiumLevel}>
              <span>{top2.badge}</span>
              <span>{top2.level_name}</span>
            </div>
            <div className={styles.podiumXp}>{top2.xp.toLocaleString()} XP</div>
            <div className={`${styles.podiumPillar} ${styles.pillarSecond}`}>2</div>
          </div>
        ) : (
          <div className={styles.podiumStep} />
        )}

        {/* 1er Lugar (Oro) */}
        {top1 ? (
          <div className={styles.podiumStep}>
            <div className={styles.avatarWrapper}>
              <Crown size={32} className={styles.crownIcon} />
              <img
                src={top1.avatar || `https://api.dicebear.com/7.x/initials/svg?seed=${top1.name}`}
                alt={top1.name}
                className={`${styles.podiumAvatar} ${styles.firstAvatar}`}
              />
              <div className={`${styles.rankNumberBadge} ${styles.rank1Badge}`}>1</div>
            </div>
            <div className={styles.podiumName}>{top1.name}</div>
            <div className={styles.podiumLevel} style={{ borderColor: "#facc15", color: "#facc15" }}>
              <span>{top1.badge}</span>
              <span>{top1.level_name}</span>
            </div>
            <div className={styles.podiumXp} style={{ color: "#facc15", fontSize: "16px" }}>
              {top1.xp.toLocaleString()} XP
            </div>
            <div className={`${styles.podiumPillar} ${styles.pillarFirst}`}>1</div>
          </div>
        ) : (
          <div className={styles.podiumStep} />
        )}

        {/* 3er Lugar (Bronce) */}
        {top3 ? (
          <div className={styles.podiumStep}>
            <div className={styles.avatarWrapper}>
              <img
                src={top3.avatar || `https://api.dicebear.com/7.x/initials/svg?seed=${top3.name}`}
                alt={top3.name}
                className={`${styles.podiumAvatar} ${styles.thirdAvatar}`}
              />
              <div className={`${styles.rankNumberBadge} ${styles.rank3Badge}`}>3</div>
            </div>
            <div className={styles.podiumName}>{top3.name}</div>
            <div className={styles.podiumLevel}>
              <span>{top3.badge}</span>
              <span>{top3.level_name}</span>
            </div>
            <div className={styles.podiumXp}>{top3.xp.toLocaleString()} XP</div>
            <div className={`${styles.podiumPillar} ${styles.pillarThird}`}>3</div>
          </div>
        ) : (
          <div className={styles.podiumStep} />
        )}
      </div>

      {/* Tarjeta de Tu Posición Actual */}
      {currentUser && (
        <div className={styles.currentUserCard}>
          <div className={styles.userCardLeft}>
            <div className={styles.userRankBadge}>
              #{currentUser.rank}
            </div>
            <div className={styles.userCardInfo}>
              <h3>Tu Posición Actual</h3>
              <div className={styles.userCardMeta}>
                <span className={styles.levelTag} style={{ background: currentUser.badge_color }}>
                  {currentUser.badge} Nivel {currentUser.level}: {currentUser.level_name}
                </span>
                <span>•</span>
                <strong style={{ color: "#f8fafc" }}>{currentUser.xp.toLocaleString()} XP acumulados</strong>
              </div>
            </div>
          </div>

          <div className={styles.userProgressWrapper}>
            <div className={styles.progressLabel}>
              <span>Progreso de Nivel</span>
              <span>{currentUser.progress_pct}%</span>
            </div>
            <div className={styles.progressBarBg}>
              <div
                className={styles.progressBarFill}
                style={{ width: `${currentUser.progress_pct}%` }}
              />
            </div>
            {currentUser.points_to_higher > 0 && (
              <span style={{ fontSize: "11px", color: "#a78bfa" }}>
                ⚡ A solo <strong>{currentUser.points_to_higher} XP</strong> de subir un puesto en la tabla.
              </span>
            )}
          </div>
        </div>
      )}

      {/* Tabla Puestos 4 al 20+ */}
      <div className={styles.tableContainer}>
        <div className={styles.tableHeader}>
          <span>Puesto</span>
          <span>Estudiante</span>
          <span>Rango / Nivel</span>
          <span style={{ textAlign: "right" }}>Puntos XP</span>
        </div>

        {restItems.map((item) => (
          <div
            key={item.user_id}
            className={`${styles.tableRow} ${item.is_current ? styles.tableRowCurrent : ""}`}
          >
            <div className={styles.rowRank}>#{item.rank}</div>
            <div className={styles.rowStudent}>
              <img
                src={item.avatar || `https://api.dicebear.com/7.x/initials/svg?seed=${item.name}`}
                alt={item.name}
                className={styles.studentAvatar}
              />
              <div className={styles.studentName}>
                <span>{item.name}</span>
                {item.is_current && <span className={styles.youBadge}>Tú</span>}
              </div>
            </div>
            <div className={styles.rowLevel}>
              <span>{item.badge}</span>
              <span>{item.level_name}</span>
            </div>
            <div className={styles.rowXp}>{item.xp.toLocaleString()} XP</div>
          </div>
        ))}

        {restItems.length === 0 && !loading && (
          <div style={{ padding: "32px", textAlign: "center", color: "#64748b", fontSize: "14px" }}>
            El top 3 lidera la tabla en este periodo. ¡Completa lecciones para entrar en la lista!
          </div>
        )}
      </div>
    </div>
  );
};
