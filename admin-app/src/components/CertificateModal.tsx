import React, { useState } from "react";
import { 
  Award, 
  Printer, 
  Share2, 
  Copy, 
  Check, 
  ExternalLink, 
  X, 
  ShieldCheck 
} from "lucide-react";
import { Certificate } from "../services/api";
import styles from "./CertificateModal.module.css";

interface CertificateModalProps {
  certificate: Certificate;
  isOpen: boolean;
  onClose: () => void;
}

export function CertificateModal({ certificate, isOpen, onClose }: CertificateModalProps) {
  const [copied, setCopied] = useState(false);

  if (!isOpen || !certificate) return null;

  const handleCopyLink = () => {
    navigator.clipboard.writeText(certificate.verification_url);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const handlePrint = () => {
    window.print();
  };

  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${encodeURIComponent(
    certificate.verification_url
  )}`;

  return (
    <div className={styles.overlay} onClick={onClose}>
      <div className={styles.modal} onClick={(e) => e.stopPropagation()}>
        {/* Encabezado */}
        <div className={styles.header}>
          <div className={styles.headerTitle}>
            <Award size={20} color="#facc15" />
            <span>Certificado Oficial de Graduación</span>
          </div>
          <button type="button" className={styles.closeButton} onClick={onClose} aria-label="Cerrar">
            <X size={18} />
          </button>
        </div>

        {/* Hoja del Diploma */}
        <div className={styles.content}>
          <div className={styles.certificateSheet} id="crezca-certificate-render">
            <div className={styles.innerBorder} />

            <div className={styles.certHeader}>
              <div className={styles.academyBadge}>
                <ShieldCheck size={20} color="#d4af37" />
                <span>{certificate.academy_name}</span>
              </div>
              <div className={styles.certIdBadge}>
                ID: {certificate.cert_code}
              </div>
            </div>

            <h1 className={styles.certMainTitle}>Certificado de Finalización</h1>
            <p className={styles.certSubtitle}>Se otorga con mérito y distinción a</p>

            <div className={styles.studentName}>{certificate.student_name}</div>

            <p className={styles.certText}>
              Por haber completado con éxito todos los módulos, lecciones prácticas y requisitos académicos del curso:
            </p>

            <div className={styles.courseTitle}>"{certificate.course_title}"</div>

            <p className={styles.certText}>
              Emitido y validado oficialmente el día {certificate.formatted_date}.
            </p>

            <div className={styles.certFooter}>
              {/* Firma */}
              <div className={styles.signatureBox}>
                {certificate.instructor_signature ? (
                  <img
                    src={certificate.instructor_signature}
                    alt="Firma"
                    className={styles.signatureImage}
                  />
                ) : (
                  <div className={styles.signaturePlaceholder}>
                    {certificate.instructor_name}
                  </div>
                )}
                <div className={styles.signatureLine} />
                <span className={styles.signerName}>{certificate.instructor_name}</span>
                <span className={styles.signerRole}>Instructor Principal & Dirección</span>
              </div>

              {/* Sello Dorado */}
              <div className={styles.sealBox}>
                <div className={styles.goldSeal}>
                  <Award size={32} />
                </div>
                <span className={styles.sealText}>Acreditado</span>
              </div>

              {/* QR de Validación */}
              <div className={styles.qrBox}>
                <div className={styles.qrCodeContainer}>
                  <img
                    src={qrUrl}
                    alt="QR de Validación"
                    width={72}
                    height={72}
                    loading="lazy"
                  />
                </div>
                <span className={styles.verificationCaption}>Escanear para Validar</span>
              </div>
            </div>
          </div>
        </div>

        {/* Acciones */}
        <div className={styles.actionFooter}>
          <div className={styles.actionGroup}>
            <button type="button" className={styles.btnPrimary} onClick={handlePrint}>
              <Printer size={16} />
              <span>Imprimir / Guardar en PDF</span>
            </button>
            <a
              href={certificate.linkedin_url}
              target="_blank"
              rel="noopener noreferrer"
              className={styles.btnLinkedIn}
            >
              <Share2 size={16} />
              <span>Añadir a mi perfil de LinkedIn</span>
            </a>
          </div>

          <div className={styles.actionGroup}>
            <button type="button" className={styles.btnSecondary} onClick={handleCopyLink}>
              {copied ? <Check size={14} color="#34d399" /> : <Copy size={14} />}
              <span>{copied ? "¡Enlace Copiado!" : "Copiar Enlace de Verificación"}</span>
            </button>
            <a
              href={certificate.verification_url}
              target="_blank"
              rel="noopener noreferrer"
              className={styles.btnSecondary}
            >
              <ExternalLink size={14} />
              <span>Verificar</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
