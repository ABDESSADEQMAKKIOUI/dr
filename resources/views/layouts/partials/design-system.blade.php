<style>
/* ─── Modern Design System ─────────────────────────────────────────── */
:root{--accent:#4F46E5}

/* Page hero */
.pg-hero{display:flex;align-items:center;gap:1rem;margin-bottom:.5rem}
.pg-hero-icon{width:3rem;height:3rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;background:var(--accent);flex-shrink:0}
.pg-hero-icon svg{width:1.5rem;height:1.5rem;color:#fff}
.pg-hero-title{font-size:1.5rem;font-weight:800;color:#0f172a;margin:0}
.pg-hero-sub{font-size:.875rem;color:#64748b;margin:0}

/* Modern card */
.mc{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;overflow:hidden;margin-bottom:1.5rem}
.mc-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9}
.mc-icon{width:2rem;height:2rem;border-radius:.5rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.mc-icon svg{width:1rem;height:1rem}
.mc-head-title{font-size:.9375rem;font-weight:600;color:#1e293b;margin:0}
.mc-head-sub{font-size:.75rem;color:#94a3b8;margin:0}
.mc-body{padding:1.25rem 1.5rem}
.mc-body-lg{padding:1.5rem}

/* Modern inputs */
.fi{width:100%;border:1px solid #e2e8f0;border-radius:.75rem;padding:.625rem 1rem;font-size:.875rem;color:#1e293b;background:#fff;transition:border-color .15s,box-shadow .15s;outline:none}
.fi:focus{border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 15%,transparent)}
.fi-label{display:block;font-size:.875rem;font-weight:500;color:#374151;margin-bottom:.25rem}
.fi-hint{font-size:.75rem;color:#94a3b8;margin-top:.25rem}
.fi-group{margin-bottom:1.25rem}

/* Toggle switch */
.ts{position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0}
.ts input{opacity:0;width:0;height:0;position:absolute}
.ts-slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:26px;transition:.25s}
.ts-slider:before{position:absolute;content:"";height:20px;width:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.ts input:checked+.ts-slider{background:var(--accent)}
.ts input:checked+.ts-slider:before{transform:translateX(22px)}

/* Toggle row */
.tr{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #f8fafc}
.tr:last-child{border-bottom:none}
.tr-title{font-size:.875rem;font-weight:500;color:#1e293b}
.tr-sub{font-size:.8125rem;color:#94a3b8;margin-top:.125rem}

/* Flash alert */
.flash-ok{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.75rem;font-size:.875rem;font-weight:500;background:#d1fae5;color:#065f46;margin-bottom:1.25rem}
.flash-err{display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-radius:.75rem;font-size:.875rem;font-weight:500;background:#fee2e2;color:#991b1b;margin-bottom:1.25rem}
.flash-ok svg,.flash-err svg{width:1.25rem;height:1.25rem;flex-shrink:0}

/* Accent button */
.btn-ac{padding:.625rem 1.5rem;border-radius:.75rem;background:var(--accent);color:#fff;font-size:.875rem;font-weight:600;border:none;cursor:pointer;transition:opacity .15s;box-shadow:0 1px 2px rgba(0,0,0,.1)}
.btn-ac:hover{opacity:.88}
.btn-out{padding:.625rem 1.5rem;border-radius:.75rem;background:transparent;color:var(--accent);font-size:.875rem;font-weight:600;border:2px solid var(--accent);cursor:pointer;transition:opacity .15s}
.btn-out:hover{opacity:.75}

/* KPI stat card */
.kpi{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between}
.kpi-icon{width:3rem;height:3rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.kpi-icon svg{width:1.5rem;height:1.5rem}
.kpi-label{font-size:.8125rem;color:#64748b;font-weight:500;margin-bottom:.25rem}
.kpi-value{font-size:1.5rem;font-weight:800;color:#0f172a}
.kpi-value.sm{font-size:1.125rem}

/* Report filter card */
.rf{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;padding:1.25rem 1.5rem;margin-bottom:1.5rem}
.rf-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}
.rf-title{font-size:1rem;font-weight:700;color:#0f172a}

/* Modern table */
.mt{width:100%;border-collapse:collapse}
.mt thead tr{background:linear-gradient(135deg,var(--accent) 0%,color-mix(in srgb,var(--accent) 80%,#000) 100%)}
.mt thead th{padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#fff}
.mt tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s}
.mt tbody tr:hover{background:#f8fafc}
.mt tbody td{padding:.75rem 1rem;font-size:.875rem;color:#374151}
.mt tbody tr:last-child{border-bottom:none}
.mt-wrap{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #f1f5f9;overflow:hidden}

/* Badge */
.badge-ok{display:inline-flex;align-items:center;padding:.2rem .625rem;border-radius:20px;font-size:.75rem;font-weight:700;background:#d1fae5;color:#065f46}
.badge-warn{display:inline-flex;align-items:center;padding:.2rem .625rem;border-radius:20px;font-size:.75rem;font-weight:700;background:#fef3c7;color:#92400e}
.badge-danger{display:inline-flex;align-items:center;padding:.2rem .625rem;border-radius:20px;font-size:.75rem;font-weight:700;background:#fee2e2;color:#991b1b}
.badge-info{display:inline-flex;align-items:center;padding:.2rem .625rem;border-radius:20px;font-size:.75rem;font-weight:700;background:#dbeafe;color:#1d4ed8}
.badge-gray{display:inline-flex;align-items:center;padding:.2rem .625rem;border-radius:20px;font-size:.75rem;font-weight:700;background:#f1f5f9;color:#64748b}

@media(max-width:640px){.kpi-value{font-size:1.25rem}}
</style>
