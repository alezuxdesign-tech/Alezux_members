"use client";

import { useEffect, useId, useLayoutEffect, useRef, useState } from "react";
import type { DragEvent, KeyboardEvent } from "react";
import { ArrowUp, CircleAlert, File as FileIcon, FileArchive, FileImage, FilePlay, FileText, RotateCw, X } from "lucide-react";
import { AnimatePresence, animate, motion, useIsPresent, useMotionTemplate, useMotionValue, useMotionValueEvent, useReducedMotion, useSpring, useTransform } from "motion/react";
import type { HTMLMotionProps, TargetAndTransition, Transition } from "motion/react";
import { motionTokens } from "../lib/motion-tokens";
import styles from "./file-dropzone.module.css";

export type FileDropzoneStatus = "uploading" | "uploaded" | "failed";
export type FileDropzoneItem = { id: string; name: string; size: number; status?: FileDropzoneStatus; progress?: number; error?: string; retryable?: boolean; file?: File; preview?: string };
export type FileDropzoneUpload = (item: FileDropzoneItem, options: { onProgress: (percent: number) => void; signal: AbortSignal }) => Promise<void>;

export type FileDropzoneProps = {
  accept?: string;
  multiple?: boolean;
  maxFiles?: number;
  onFilesChange?: (files: File[]) => void;
  label?: string;
  description?: string;
  defaultItems?: FileDropzoneItem[];
  onUpload?: FileDropzoneUpload;
  maxSize?: number;
  listPlacement?: "below" | "inside";
  note?: string;
  dropLabel?: string;
  compactAt?: number;
  className?: string;
  showList?: boolean;
};

const MB = 1024 * 1024;
const PERIOD = 7;
const enter: Transition = { duration: motionTokens.duration.standard, ease: [...motionTokens.ease.enter] };
const exitFast: Transition = { duration: motionTokens.duration.fast, ease: [...motionTokens.ease.standard] };
const instant: Transition = { duration: 0 };
const fade: Transition = { duration: motionTokens.duration.instant };
const collapse: Transition = { ...motionTokens.spring.smooth, visualDuration: 0.3 };
const textIn: TargetAndTransition = { opacity: 0, y: "0.3em", filter: `blur(${motionTokens.blur.soft}px)` };
const textOut: TargetAndTransition = { opacity: 0, y: "-0.3em", filter: `blur(${motionTokens.blur.subtle}px)`, transition: exitFast };
const shown: TargetAndTransition = { opacity: 1, y: "0em", filter: "blur(0px)" };
const clamp = (value: number) => Math.min(Math.max(value, 0), 100);
const SHAKE = { x: [0, -7, 6, -4, 3, -1.5, 0] };
const shakeTransition: Transition = { duration: 0.42, ease: "easeOut" };
const land: Transition = { type: "spring", visualDuration: 0.42, bounce: 0.14 };

export function formatFileSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < MB) return `${Math.round(bytes / 1024)} KB`;
  const mb = bytes / MB;
  return `${mb >= 10 ? Math.round(mb) : Number(mb.toFixed(1))} MB`;
}

function TypeIcon({ name }: { name: string }) {
  const props = { size: 20, strokeWidth: 1.75, "aria-hidden": true } as const;
  const extension = name.toLowerCase().split(".").pop() ?? "";
  if (["png", "jpg", "jpeg", "gif", "webp", "svg", "avif", "heic"].includes(extension)) return <FileImage {...props} />;
  if (["mov", "mp4", "webm", "m4v", "avi"].includes(extension)) return <FilePlay {...props} />;
  if (["zip", "gz", "tar", "rar", "7z"].includes(extension)) return <FileArchive {...props} />;
  if (["pdf", "md", "txt", "doc", "docx", "rtf"].includes(extension)) return <FileText {...props} />;
  return <FileIcon {...props} />;
}

function Swap(props: HTMLMotionProps<"span">) {
  const present = useIsPresent();
  return <motion.span {...props} aria-hidden={present ? props["aria-hidden"] : true} />;
}

function TextSwap({ text, className, reduce }: { text: string; className?: string; reduce: boolean }) {
  return (
    <AnimatePresence mode="popLayout" initial={false}>
      <Swap
        key={text}
        className={className}
        initial={reduce ? { opacity: 0 } : textIn}
        animate={shown}
        exit={reduce ? { opacity: 0, transition: fade } : textOut}
        transition={reduce ? fade : enter}
      >
        {text}
      </Swap>
    </AnimatePresence>
  );
}

function MotionText({ text }: { text: string }) {
  const reduced = useReducedMotion();
  const words = text.split(" ");
  return (
    <>
      <span className={styles.srOnly}>{text}</span>
      <span className={styles.words} aria-hidden="true">
        <AnimatePresence initial={false} mode="popLayout">
          {words.map((word, index) => (
            <motion.span
              key={`${index}:${word}`}
              className={styles.word}
              initial={reduced ? { opacity: 0 } : { opacity: 0, y: "0.35em", filter: `blur(${motionTokens.blur.soft}px)` }}
              animate={{ opacity: 1, y: 0, filter: "blur(0px)" }}
              exit={reduced ? { opacity: 0, transition: { duration: 0 } } : { opacity: 0, y: "-0.35em", filter: `blur(${motionTokens.blur.subtle}px)`, transition: exitFast }}
              transition={reduced ? { duration: motionTokens.duration.instant } : { duration: motionTokens.duration.standard, ease: [...motionTokens.ease.enter] }}
            >
              {index < words.length - 1 ? `${word} ` : word}
            </motion.span>
          ))}
        </AnimatePresence>
      </span>
    </>
  );
}

function ErrorRow({ text }: { text: string }) {
  const reduced = useReducedMotion();
  const copyRef = useRef<HTMLDivElement>(null);
  const [height, setHeight] = useState<number | "auto">("auto");
  useEffect(() => {
    const node = copyRef.current;
    if (!node || typeof ResizeObserver === "undefined") return;
    const observer = new ResizeObserver(([entry]) => {
      if (entry) setHeight(entry.borderBoxSize?.[0]?.blockSize ?? node.offsetHeight);
    });
    observer.observe(node);
    return () => observer.disconnect();
  }, []);
  return (
    <motion.div
      className={styles.messageSlot}
      initial={{ height: 0, opacity: 0 }}
      animate={{ height, opacity: 1 }}
      exit={{ height: 0, opacity: 0, transition: reduced ? { duration: 0 } : { height: motionTokens.spring.smooth, opacity: { duration: motionTokens.duration.instant } } }}
      transition={reduced ? { duration: 0 } : { height: motionTokens.spring.smooth, opacity: { duration: motionTokens.duration.fast } }}
    >
      <motion.div
        ref={copyRef}
        className={styles.error}
        role="alert"
        initial={reduced ? false : { y: "0.35em", filter: `blur(${motionTokens.blur.soft}px)` }}
        animate={{ y: 0, filter: "blur(0px)" }}
        transition={{ duration: reduced ? 0 : motionTokens.duration.standard, ease: [...motionTokens.ease.enter] }}
      >
        <CircleAlert className={styles.errorIcon} size={14} strokeWidth={2.25} aria-hidden="true" />
        <span className={styles.errorCopy}>
          <MotionText text={text} />
        </span>
      </motion.div>
    </motion.div>
  );
}

function DrawnCheck({ reduce }: { reduce: boolean }) {
  return (
    <svg className={styles.stateIcon} width={14} height={14} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.25} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <motion.path
        d="M12 2.5a9.5 9.5 0 1 1 0 19a9.5 9.5 0 1 1 0-19"
        initial={reduce ? false : { pathLength: 0 }}
        animate={{ pathLength: 1 }}
        transition={{ duration: motionTokens.duration.considered * 0.7, ease: [...motionTokens.ease.enter] }}
      />
      <motion.path
        d="m8.2 12.4 2.6 2.6 5-5.2"
        initial={reduce ? false : { pathLength: 0 }}
        animate={{ pathLength: 1 }}
        transition={{ ...enter, delay: 0.16 }}
      />
    </svg>
  );
}

type RowProps = {
  item: FileDropzoneItem;
  reduce: boolean;
  delay: number;
  fresh: boolean;
  canRetry: boolean;
  onRemove: () => void;
  onRetry: () => void;
  removeRef: (node: HTMLButtonElement | null) => void;
};

function Thumb({ item }: { item: FileDropzoneItem }) {
  const [broken, setBroken] = useState(false);
  if (!item.preview || broken) {
    return (
      <span className={styles.fileIcon}>
        <TypeIcon name={item.name} />
      </span>
    );
  }
  return (
    <span className={styles.thumb}>
      <img src={item.preview} alt="" width={40} height={40} decoding="async" onError={() => setBroken(true)} />
    </span>
  );
}

function FileRow({ item, reduce, delay, fresh, canRetry, onRemove, onRetry, removeRef }: RowProps) {
  const { status } = item;
  const rowRef = useRef<HTMLDivElement>(null);
  const shakenFor = useRef<FileDropzoneStatus | "new" | undefined>(fresh ? "new" : status);
  useEffect(() => {
    const previous = shakenFor.current;
    if (status === previous) return;
    shakenFor.current = status;
    const node = rowRef.current;
    if (status !== "failed" || reduce || !node) return;
    animate(node, SHAKE, { ...shakeTransition, delay: previous === "new" ? delay + 0.3 : 0 });
  }, [status, reduce, delay]);

  const progress = useMotionValue(status === "uploaded" ? 100 : item.progress ?? 0);
  const x = useTransform(progress, (value) => `${clamp(value) - 100}%`);
  const percent = useTransform(progress, (value) => `${Math.round(clamp(value))}%`);
  const [filled, setFilled] = useState(status !== "uploading");
  const [seen, setSeen] = useState(status);
  if (seen !== status) {
    setSeen(status);
    if (status === "uploading") setFilled(false);
  }
  const phase = status === "uploaded" && !filled && !reduce ? "uploading" : status;
  const target = status === "uploaded" ? 100 : item.progress ?? 0;
  useEffect(() => {
    if (status !== "uploading" && status !== "uploaded") return;
    if (reduce || (status === "uploading" && target === 0)) {
      progress.jump(target);
      return;
    }
    const controls = animate(progress, target, {
      ...motionTokens.spring.smooth,
      onComplete: status === "uploaded" ? () => setFilled(true) : undefined,
    });
    return () => controls.stop();
  }, [status, target, progress, reduce]);

  useMotionValueEvent(progress, "change", (value) => {
    if (status === "uploaded" && value >= 99.5) setFilled(true);
  });
  const failed = status === "failed";
  const retry = failed && canRetry && item.retryable !== false;
  return (
    <motion.li
      className={styles.item}
      initial={reduce ? { opacity: 0 } : { height: 0 }}
      animate={{ height: "auto", opacity: 1 }}
      exit={
        reduce
          ? { opacity: 0, transition: fade }
          : { height: 0, opacity: 0, transition: { height: collapse, opacity: { ...exitFast, delay: 0.04 } } }
      }
      transition={reduce ? fade : { height: { ...motionTokens.spring.smooth, delay }, opacity: fade }}
    >
      <motion.div
        ref={rowRef}
        className={`${styles.row} ${failed ? styles.isFailed : ""}`}
        initial={reduce ? false : { opacity: 0, y: -22, scale: 0.94, filter: `blur(${motionTokens.blur.soft}px)` }}
        animate={{ opacity: 1, y: 0, scale: 1, filter: "blur(0px)" }}
        exit={reduce ? undefined : { scale: 0.97, filter: `blur(${motionTokens.blur.subtle}px)`, transition: exitFast }}
        transition={reduce ? instant : { y: { ...land, delay }, scale: { ...land, delay }, opacity: { ...enter, delay }, filter: { ...enter, delay } }}
      >
        <Thumb item={item} />
        <span className={styles.copy}>
          <span className={styles.name} title={item.name}>
            {item.name}
          </span>
          <span className={styles.meta}>
            <span>{formatFileSize(item.size)}</span>
            {phase && (
              <>
                <span className={styles.dot} aria-hidden="true">
                  ·
                </span>
                <span className={styles.phase}>
                  <AnimatePresence mode="popLayout" initial={false}>
                    <Swap
                      key={phase}
                      className={`${styles.state} ${phase === "uploaded" ? styles.uploaded : phase === "failed" ? styles.failed : ""}`}
                      initial={reduce ? { opacity: 0 } : textIn}
                      animate={shown}
                      exit={reduce ? { opacity: 0, transition: fade } : textOut}
                      transition={reduce ? fade : enter}
                    >
                      {phase === "uploading" ? (
                        <span>
                          Uploading <motion.span className={styles.percent}>{percent}</motion.span>
                        </span>
                      ) : phase === "uploaded" ? (
                        <>
                          <DrawnCheck reduce={reduce} />
                          <span>Uploaded</span>
                        </>
                      ) : (
                        <>
                          <CircleAlert className={styles.stateIcon} size={14} strokeWidth={2.25} aria-hidden="true" />
                          <span className={styles.reason} title={item.error}>
                            <span className={styles.srOnly}>Failed: </span>
                            {item.error || "Upload failed"}
                          </span>
                        </>
                      )}
                    </Swap>
                  </AnimatePresence>
                </span>
              </>
            )}
          </span>
          <AnimatePresence initial={false}>
            {phase === "uploading" && (
              <motion.span
                key="bar"
                className={styles.barFrame}
                initial={reduce ? { opacity: 0 } : { height: 0, opacity: 0 }}
                animate={{ height: "auto", opacity: 1 }}
                exit={
                  reduce
                    ? { opacity: 0, transition: fade }
                    : { height: 0, opacity: 0, transition: { height: { ...collapse, delay: 0.22 }, opacity: { ...exitFast, delay: 0.16 } } }
                }
                transition={reduce ? fade : { height: motionTokens.spring.smooth, opacity: enter }}
              >
                <span className={styles.bar} role="progressbar" aria-label={`Uploading ${item.name}`} aria-valuemin={0} aria-valuemax={100} aria-valuenow={Math.round(clamp(target))}>
                  <motion.span className={styles.barFill} style={{ x }} />
                </span>
              </motion.span>
            )}
          </AnimatePresence>
        </span>
        <span className={styles.actions}>
          <AnimatePresence initial={false}>
            {retry && (
              <motion.span
                key="retry"
                className={styles.retrySlot}
                initial={reduce ? { opacity: 0 } : { width: 0, opacity: 0 }}
                animate={{ width: "auto", opacity: 1 }}
                exit={reduce ? { opacity: 0, transition: fade } : { width: 0, opacity: 0, transition: { width: collapse, opacity: { duration: motionTokens.duration.instant } } }}
                transition={reduce ? fade : { width: motionTokens.spring.smooth, opacity: enter }}
              >
                <motion.span className={styles.retryInner} initial={reduce ? false : { scale: 0.8, filter: `blur(${motionTokens.blur.subtle}px)` }} animate={{ scale: 1, filter: "blur(0px)" }} transition={reduce ? instant : motionTokens.spring.snappy}>
                  <button type="button" className={styles.retry} onClick={onRetry} aria-label={`Retry ${item.name}`} title="Retry">
                    <RotateCw size={14} strokeWidth={2} aria-hidden="true" />
                    <span className={styles.retryLabel}>Retry</span>
                  </button>
                </motion.span>
              </motion.span>
            )}
          </AnimatePresence>
          <button ref={removeRef} type="button" className={styles.remove} onClick={onRemove} aria-label={`Remove ${item.name}`} title="Remove">
            <X size={16} strokeWidth={1.75} aria-hidden="true" />
          </button>
        </span>
      </motion.div>
    </motion.li>
  );
}

export function FileDropzone({
  accept,
  multiple = true,
  maxFiles = 5,
  onFilesChange,
  label = "Add files",
  description = "Drop files here or choose from your device",
  defaultItems,
  onUpload,
  maxSize,
  listPlacement = "below",
  note,
  dropLabel,
  compactAt,
  className,
  showList = true,
}: FileDropzoneProps) {
  const [items, setItems] = useState<FileDropzoneItem[]>(() => defaultItems ?? []);
  const [dragging, setDragging] = useState(false);
  const [error, setError] = useState("");
  const [announcement, setAnnouncement] = useState("");
  const [batchStart, setBatchStart] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);
  const dropRef = useRef<HTMLButtonElement>(null);
  const zoneRef = useRef<HTMLDivElement>(null);
  const edgeRef = useRef<SVGRectElement>(null);
  const dragDepth = useRef(0);
  const nextId = useRef(0);
  const controllers = useRef(new Map<string, AbortController>());
  const removeRefs = useRef(new Map<string, HTMLButtonElement>());
  const descriptionId = useId();
  const noteId = useId();
  const reduce = !!useReducedMotion();
  const inside = listPlacement === "inside";
  const compact = compactAt !== undefined && items.length >= compactAt;
  const [freshIds, setFreshIds] = useState<ReadonlySet<string>>(() => new Set());
  const ownedPreviews = useRef(new Map<string, string>());
  const [hovered, setHovered] = useState(false);
  const [focusWithin, setFocusWithin] = useState(false);

  useEffect(() => {
    const active = controllers.current;
    const previews = ownedPreviews.current;
    return () => {
      active.forEach((controller) => controller.abort());
      previews.forEach((url) => URL.revokeObjectURL(url));
    };
  }, []);

  const pointerX = useMotionValue(50),
    pointerY = useMotionValue(50);
  const glowSpring = { stiffness: 260, damping: 32, mass: 0.8 };
  const glowX = useSpring(pointerX, glowSpring),
    glowY = useSpring(pointerY, glowSpring);
  const edgeLight = useMotionTemplate`radial-gradient(180px circle at ${glowX}% ${glowY}%, var(--accent), transparent 70%)`;
  const washLight = useMotionTemplate`radial-gradient(260px circle at ${glowX}% ${glowY}%, color-mix(in oklab, var(--accent) 6%, transparent), transparent 70%)`;
  const track = (event: DragEvent) => {
    const zone = zoneRef.current;
    if (!zone) return;
    const box = zone.getBoundingClientRect();
    const x = ((event.clientX - box.left) / Math.max(1, box.width)) * 100,
      y = ((event.clientY - box.top) / Math.max(1, box.height)) * 100;
    if (reduce || !dragging) {
      pointerX.jump(x);
      pointerY.jump(y);
      glowX.jump(x);
      glowY.jump(y);
    } else {
      pointerX.set(x);
      pointerY.set(y);
    }
  };

  const shakeZone = () => {
    if (!reduce && zoneRef.current) animate(zoneRef.current, SHAKE, shakeTransition);
  };

  useLayoutEffect(() => {
    const zone = zoneRef.current,
      edge = edgeRef.current;
    if (!zone || !edge) return;
    const fit = () => {
      const width = zone.offsetWidth - 1,
        height = zone.offsetHeight - 1;
      const radius = Math.max(0, Math.min(parseFloat(getComputedStyle(zone).borderTopLeftRadius) - 0.5, width / 2, height / 2));
      const perimeter = 2 * (width + height) - 8 * radius + 2 * Math.PI * radius;
      edge.setAttribute("rx", String(radius));
      edge.setAttribute("pathLength", String(Math.max(8, Math.round(perimeter / PERIOD)) * PERIOD));
    };
    fit();
    let frame = 0;
    const follow = () => {
      fit();
      frame = requestAnimationFrame(follow);
    };
    const onRun = (event: TransitionEvent) => {
      if (event.target === zone && event.propertyName.includes("radius")) {
        cancelAnimationFrame(frame);
        follow();
      }
    };
    const onEnd = (event: TransitionEvent) => {
      if (event.target === zone && event.propertyName.includes("radius")) {
        cancelAnimationFrame(frame);
        fit();
      }
    };
    zone.addEventListener("transitionrun", onRun);
    zone.addEventListener("transitionend", onEnd);
    zone.addEventListener("transitioncancel", onEnd);
    const observer = typeof ResizeObserver === "undefined" ? undefined : new ResizeObserver(fit);
    observer?.observe(zone);
    return () => {
      observer?.disconnect();
      cancelAnimationFrame(frame);
      zone.removeEventListener("transitionrun", onRun);
      zone.removeEventListener("transitionend", onEnd);
      zone.removeEventListener("transitioncancel", onEnd);
    };
  }, []);

  const filesOf = (list: FileDropzoneItem[]) => list.flatMap((item) => (item.file ? [item.file] : []));
  const patch = (id: string, next: (item: FileDropzoneItem) => FileDropzoneItem) =>
    setItems((current) => current.map((item) => (item.id === id ? next(item) : item)));

  function startUpload(item: FileDropzoneItem) {
    if (!onUpload) return;
    controllers.current.get(item.id)?.abort();
    const controller = new AbortController();
    controllers.current.set(item.id, controller);
    patch(item.id, (current) => ({ ...current, status: "uploading", progress: 0, error: undefined }));
    onUpload(
      { ...item, status: "uploading", progress: 0, error: undefined },
      {
        signal: controller.signal,
        onProgress: (percent) => {
          if (!controller.signal.aborted) {
            patch(item.id, (current) =>
              current.status === "uploading" ? { ...current, progress: Math.max(current.progress ?? 0, clamp(percent)) } : current
            );
          }
        },
      }
    )
      .then(
        () => {
          if (controller.signal.aborted) return;
          patch(item.id, (current) => ({ ...current, status: "uploaded", progress: 100 }));
          setAnnouncement(`${item.name} uploaded`);
        },
        (reason: unknown) => {
          if (controller.signal.aborted) return;
          const message = reason instanceof Error && reason.message ? reason.message : "Upload failed";
          patch(item.id, (current) => ({ ...current, status: "failed", error: message, retryable: true }));
          setAnnouncement(`${item.name} failed. ${message}`);
        }
      )
      .finally(() => {
        if (controllers.current.get(item.id) === controller) controllers.current.delete(item.id);
      });
  }

  function addFiles(incoming: FileList | File[]) {
    const list = Array.from(incoming);
    const accepted = accept?.split(",").map((value) => value.trim().toLowerCase()).filter(Boolean) ?? [];
    const matching = list.filter(
      (file) =>
        accepted.length === 0 ||
        accepted.some((type) =>
          type.startsWith(".")
            ? file.name.toLowerCase().endsWith(type)
            : type.endsWith("/*")
            ? file.type.startsWith(type.slice(0, -1))
            : file.type === type
        )
    );
    const kept = multiple ? items : [];
    const same = (a: File, b: File) => a.name === b.name && a.size === b.size && a.lastModified === b.lastModified;
    const fresh = matching.filter(
      (file, index) => matching.findIndex((other) => same(other, file)) === index && !kept.some((item) => item.file && same(item.file, file))
    );
    const room = multiple ? Math.max(0, maxFiles - kept.length) : 1;
    const added = fresh.slice(0, room).map((file): FileDropzoneItem => {
      const tooLarge = maxSize !== undefined && file.size > maxSize;
      const id = `${file.name}-${file.size}-${file.lastModified}-${nextId.current++}`;
      const preview = file.type.startsWith("image/") && typeof URL.createObjectURL === "function" ? URL.createObjectURL(file) : undefined;
      if (preview) ownedPreviews.current.set(id, preview);
      return {
        id,
        name: file.name,
        size: file.size,
        file,
        preview,
        status: tooLarge ? "failed" : onUpload ? "uploading" : undefined,
        progress: 0,
        error: tooLarge ? `File is larger than ${formatFileSize(maxSize)}` : undefined,
        retryable: !tooLarge,
      };
    });
    const rejected = matching.length !== list.length;
    const overflow = fresh.length > added.length;
    setError(
      rejected
        ? list.length - matching.length === 1 && list.length === 1
          ? `${list[0]!.name} is not an accepted file type.`
          : "Some files were not added because their type is not accepted."
        : overflow
        ? `You can add up to ${maxFiles} ${maxFiles === 1 ? "file" : "files"}.`
        : ""
    );
    if (rejected || overflow) shakeZone();
    if (!added.length) return;
    if (!multiple) {
      controllers.current.forEach((controller) => controller.abort());
      controllers.current.clear();
    }
    const next = [...kept, ...added];
    setFreshIds((current) => new Set([...current, ...added.map((item) => item.id)]));
    setBatchStart(kept.length);
    if (showList) {
      setItems((current) => (multiple ? [...current, ...added] : added));
      onFilesChange?.(filesOf(next));
    } else {
      setItems([]);
      onFilesChange?.(filesOf(added));
    }
    const failed = added.filter((item) => item.status === "failed");
    setAnnouncement(
      `${added.length} ${added.length === 1 ? "file" : "files"} added.${
        failed.length ? ` ${failed.map((item) => `${item.name}: ${item.error}`).join(". ")}.` : ""
      }`
    );
    added.filter((item) => item.status === "uploading").forEach(startUpload);
  }

  function removeItem(target: FileDropzoneItem) {
    controllers.current.get(target.id)?.abort();
    controllers.current.delete(target.id);
    const index = items.findIndex((item) => item.id === target.id);
    const neighbor = items[index + 1] ?? items[index - 1];
    const next = items.filter((item) => item.id !== target.id);
    const preview = ownedPreviews.current.get(target.id);
    if (preview) {
      ownedPreviews.current.delete(target.id);
      window.setTimeout(() => URL.revokeObjectURL(preview), 600);
    }
    setItems((current) => current.filter((item) => item.id !== target.id));
    setError("");
    setAnnouncement(`${target.name} removed`);
    if (target.file) onFilesChange?.(filesOf(next));
    requestAnimationFrame(() => (neighbor ? removeRefs.current.get(neighbor.id) : dropRef.current)?.focus());
  }

  function retryItem(item: FileDropzoneItem) {
    setAnnouncement(`Retrying ${item.name}`);
    startUpload(item);
    requestAnimationFrame(() => removeRefs.current.get(item.id)?.focus());
  }

  function moveFocus(event: KeyboardEvent<HTMLUListElement>) {
    if (event.key === "Delete" || event.key === "Backspace") {
      const row = (event.target as HTMLElement).closest<HTMLElement>(`.${styles.row}`);
      const remove = row?.querySelector<HTMLButtonElement>(`.${styles.remove}`);
      if (remove) {
        event.preventDefault();
        remove.click();
      }
      return;
    }
    if (event.key !== "ArrowDown" && event.key !== "ArrowUp") return;
    const button = (event.target as HTMLElement).closest("button");
    const rows = Array.from(event.currentTarget.querySelectorAll<HTMLElement>(`.${styles.row}`));
    const row = button?.closest<HTMLElement>(`.${styles.row}`);
    if (!button || !row) return;
    const nextRow = rows[rows.indexOf(row) + (event.key === "ArrowDown" ? 1 : -1)];
    if (!nextRow) return;
    event.preventDefault();
    (
      nextRow.querySelector<HTMLButtonElement>(`.${button.classList.contains(styles.retry!) ? styles.retry : styles.remove}`) ??
      nextRow.querySelector<HTMLButtonElement>(`.${styles.remove}`)
    )?.focus();
  }

  const pasteArmed = hovered || focusWithin;
  const addRef = useRef(addFiles);
  useLayoutEffect(() => {
    addRef.current = addFiles;
  });
  useEffect(() => {
    if (!pasteArmed) return;
    const onPaste = (event: ClipboardEvent) => {
      const files = event.clipboardData?.files;
      if (!files?.length) return;
      const target = event.target as HTMLElement | null;
      if (target?.closest("input:not([type=file]), textarea, [contenteditable='true']")) return;
      event.preventDefault();
      addRef.current(files);
    };
    document.addEventListener("paste", onPaste);
    return () => document.removeEventListener("paste", onPaste);
  }, [pasteArmed]);

  const carriesFiles = (event: DragEvent) => Array.from(event.dataTransfer?.types ?? []).includes("Files");
  const dropCopy = dropLabel ?? (onUpload ? "Drop to upload" : multiple ? "Drop to add files" : "Drop to add the file");
  const noteCopy = note ?? (accept ? `Accepted: ${accept}` : `Up to ${maxFiles} ${maxFiles === 1 ? "file" : "files"}`);
  const slot = {
    initial: reduce ? { opacity: 0 } : { height: 0, opacity: 0 },
    animate: { height: "auto", opacity: 1 },
    exit: reduce ? { opacity: 0, transition: fade } : { height: 0, opacity: 0, transition: { height: collapse, opacity: exitFast } },
    transition: reduce ? fade : { height: motionTokens.spring.smooth, opacity: enter },
  };
  const rowsInside = inside && items.length > 0;

  const list = (
    <motion.ul
      className={styles.list}
      aria-label="Files"
      aria-hidden={items.length ? undefined : true}
      onKeyDown={moveFocus}
      initial={false}
      animate={{ paddingTop: items.length && !inside ? 4 : 0, paddingBottom: items.length && inside ? 12 : 0 }}
      transition={reduce ? instant : motionTokens.spring.smooth}
    >
      <AnimatePresence initial={false}>
        {items.map((item, index) => (
          <FileRow
            key={item.id}
            item={item}
            reduce={reduce}
            delay={reduce ? 0 : Math.min(Math.max(0, index - batchStart), 7) * motionTokens.stagger.item * 1.6}
            fresh={freshIds.has(item.id)}
            canRetry={!!onUpload}
            onRemove={() => removeItem(item)}
            onRetry={() => retryItem(item)}
            removeRef={(node) => {
              if (node) removeRefs.current.set(item.id, node);
              else removeRefs.current.delete(item.id);
            }}
          />
        ))}
      </AnimatePresence>
    </motion.ul>
  );

  return (
    <div
      className={[styles.wrapper, className].filter(Boolean).join(" ")}
      onPointerEnter={() => setHovered(true)}
      onPointerLeave={() => setHovered(false)}
      onFocus={() => setFocusWithin(true)}
      onBlur={(event) => {
        if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setFocusWithin(false);
      }}
    >
      <div
        ref={zoneRef}
        className={`${styles.dropzone} ${dragging ? styles.dragging : ""} ${inside ? styles.inside : ""} ${compact ? styles.compact : ""}`}
        onDragEnter={(event) => {
          if (!carriesFiles(event)) return;
          event.preventDefault();
          if (!dragDepth.current) track(event);
          dragDepth.current += 1;
          setDragging(true);
        }}
        onDragOver={(event) => {
          if (!carriesFiles(event)) return;
          event.preventDefault();
          event.dataTransfer.dropEffect = "copy";
          track(event);
        }}
        onDragLeave={(event) => {
          if (!carriesFiles(event)) return;
          dragDepth.current = Math.max(0, dragDepth.current - 1);
          if (!dragDepth.current) setDragging(false);
        }}
        onDrop={(event) => {
          event.preventDefault();
          dragDepth.current = 0;
          setDragging(false);
          if (event.dataTransfer.files.length) addFiles(event.dataTransfer.files);
        }}
      >
        <motion.span className={styles.wash} style={{ backgroundImage: washLight }} aria-hidden="true" />
        <svg className={styles.edge} aria-hidden="true">
          <rect ref={edgeRef} width="100%" height="100%" rx={33.5} pathLength={1358} />
        </svg>
        <motion.span className={styles.glow} style={{ backgroundImage: edgeLight }} aria-hidden="true" />
        <motion.button
          ref={dropRef}
          type="button"
          className={styles.trigger}
          onClick={() => inputRef.current?.click()}
          aria-describedby={compact ? descriptionId : `${descriptionId} ${noteId}`}
          initial={false}
          animate={{
            paddingTop: compact ? 16 : 22,
            paddingBottom: compact ? (rowsInside ? 8 : 16) : rowsInside ? 22 : 26,
          }}
          transition={reduce ? instant : motionTokens.spring.smooth}
        >
          <AnimatePresence initial={false}>
            {(!compact || dragging) && (
              <motion.span key="icon" className={styles.iconSlot} {...slot}>
                <span className={styles.sheets} aria-hidden="true">
                  <span className={styles.sheet} data-sheet="back" />
                  <span className={styles.sheet} data-sheet="side" />
                  <span className={styles.sheet} data-sheet="front">
                    <ArrowUp size={14} strokeWidth={2} />
                  </span>
                </span>
              </motion.span>
            )}
          </AnimatePresence>
          <strong className={styles.label}>
            <TextSwap text={dragging ? dropCopy : label} className={styles.labelText} reduce={reduce} />
          </strong>
          <span id={descriptionId} className={styles.hint}>
            {description}
          </span>
          <AnimatePresence initial={false}>
            {!compact && (
              <motion.span key="note" className={styles.noteSlot} {...slot}>
                <small id={noteId} className={styles.note}>
                  {noteCopy}
                </small>
              </motion.span>
            )}
          </AnimatePresence>
        </motion.button>
        {inside && showList && list}
      </div>
      <input
        ref={inputRef}
        className={styles.input}
        type="file"
        accept={accept}
        multiple={multiple}
        tabIndex={-1}
        aria-hidden="true"
        onChange={(event) => {
          if (event.target.files) addFiles(event.target.files);
          event.target.value = "";
        }}
      />
      <AnimatePresence initial={false}>{error ? <ErrorRow key="error" text={error} /> : null}</AnimatePresence>
      {!inside && showList && list}
      <span className={styles.srOnly} role="status" aria-live="polite">
        {announcement}
      </span>
    </div>
  );
}

export default FileDropzone;
