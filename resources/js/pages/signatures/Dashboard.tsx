{certificates.map(cert => (
    <CertificateCard
      key={cert.id}
      status={cert.status}
      expiresAt={cert.expires_at}
      onDownload={cert.can_download}
      onRenew={cert.can_renew}
    />
  ))}
  