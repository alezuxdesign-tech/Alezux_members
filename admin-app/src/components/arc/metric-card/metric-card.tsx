"use client";

import { ReactNode } from "react";
import { motion } from "motion/react";
import { motionTokens } from "../lib/motion-tokens";
import { AnimatedCounter } from "../animated-counter/animated-counter";
import styles from "./metric-card.module.css";

export interface MetricCardProps {
  label: string;
  value: number;
  prefix?: string;
  suffix?: string;
  decimals?: number;
  locale?: string;
  context?: string;
  change?: string;
  icon?: ReactNode;
}

export function MetricCard({
  label,
  value,
  prefix,
  suffix,
  decimals = 0,
  locale = "en-US",
  context,
  change,
  icon,
}: MetricCardProps) {
  const isUp = change && /^[+]/.test(change);
  const isDown = change && /^[-−]/.test(change);

  return (
    <motion.article 
      className={styles.card}
      whileHover={{ y: -3, transition: motionTokens.spring.snappy }}
    >
      <div className={styles.top}>
        <div className={styles.labelGroup}>
          {icon && <span className={styles.icon}>{icon}</span>}
          <span className={styles.labelText}>{label}</span>
        </div>
        {change && (
          <small 
            className={styles.badge}
            data-trend={isUp ? "up" : isDown ? "down" : undefined}
          >
            {change}
          </small>
        )}
      </div>

      <div className={styles.numberWrapper}>
        <AnimatedCounter
          value={value}
          prefix={prefix}
          suffix={suffix}
          decimals={decimals}
          locale={locale}
          animateOnView
        />
      </div>

      {context && <p className={styles.context}>{context}</p>}
    </motion.article>
  );
}

export default MetricCard;
