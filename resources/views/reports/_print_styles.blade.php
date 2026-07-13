<style>
    body { color: #0f172a; font-family: Arial, sans-serif; margin: 0; }
    .sheet { margin: 24px auto; max-width: 900px; padding: 28px; }
    .print-header { align-items: center; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; gap: 16px; margin-bottom: 18px; padding-bottom: 14px; }
    .print-brand { align-items: center; display: flex; gap: 12px; min-width: 0; }
    .print-logo { border: 1px solid #cbd5e1; border-radius: 8px; height: 54px; object-fit: contain; padding: 4px; width: 54px; }
    .print-app-name { font-size: 17px; font-weight: 800; line-height: 1.2; }
    .print-subtitle, .print-meta { color: #64748b; font-size: 12px; font-weight: 700; }
    .topline { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
    h1 { font-size: 24px; margin: 0; }
    h2 { font-size: 16px; margin: 22px 0 8px; }
    p { margin: 4px 0; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #cbd5e1; font-size: 12px; padding: 8px; text-align: left; }
    th { background: #f1f5f9; }
    .grid { display: grid; gap: 8px 24px; grid-template-columns: repeat(2, 1fr); margin-top: 16px; }
    .ticket-details { align-items: start; display: grid; gap: 24px; grid-template-columns: 1fr 128px; margin-top: 16px; }
    .ticket-photo { border: 1px solid #0f172a; height: 150px; object-fit: cover; padding: 3px; width: 116px; }
    .ticket-photo-placeholder { align-items: center; border: 1px dashed #94a3b8; color: #64748b; display: flex; font-size: 11px; font-weight: 700; height: 150px; justify-content: center; text-align: center; width: 116px; }
    .muted { color: #64748b; font-size: 12px; }
    .print-actions { margin: 16px auto; max-width: 900px; text-align: right; }
    .print-actions button { background: #0e7490; border: 0; border-radius: 8px; color: white; cursor: pointer; font-weight: 700; padding: 10px 14px; }
    @media print {
        .print-actions { display: none; }
        .sheet { margin: 0; max-width: none; padding: 0; }
    }
</style>
