"use client";

import { ReactNode } from "react";
import { motion } from "motion/react";
import { motionTokens } from "../lib/motion-tokens";
import styles from "./segmented-control.module.css";

export interface SegmentOption<T extends string> {
  value: T;
  label: ReactNode;
  icon?: ReactNode;
}

export interface SegmentedControlProps<T extends string> {
  options: SegmentOption<T>[];
  value: T;
  onChange: (value: T) => void;
  className?: string;
  size?: "sm" | "md";
}

export function SegmentedControl<T extends string>({
  options,
  value,
  onChange,
  className,
  size = "md",
}: SegmentedControlProps<T>) {
  return (
    <div className={[styles.container, styles[size], className].filter(Boolean).join(" ")} role="tablist">
      {options.map((option) => {
        const isSelected = option.value === value;
        return (
          <button
            key={option.value}
            type="button"
            role="tab"
            aria-selected={isSelected}
            className={[styles.button, isSelected ? styles.selected : ""].filter(Boolean).join(" ")}
            onClick={() => onChange(option.value)}
          >
            {isSelected && (
              <motion.span
                layoutId="segmented-control-active"
                className={styles.activePill}
                transition={motionTokens.spring.snappy}
              />
            )}
            <span className={styles.content}>
              {option.icon && <span className={styles.icon}>{option.icon}</span>}
              <span className={styles.label}>{option.label}</span>
            </span>
          </button>
        );
      })}
    </div>
  );
}

export default SegmentedControl;
