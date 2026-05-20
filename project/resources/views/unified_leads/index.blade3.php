@include('layouts.masterhead')
<link rel="stylesheet" href="{{ asset('assets/plugins/switchery/switchery.min.css') }}">

<style>
    .unified-leads-container {
        max-width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
        --ul-surface: #ffffff;
        --ul-bg: #eef2f6;
        --ul-border: #e2e8f0;
        --ul-text: #0f172a;
        --ul-muted: #64748b;
        --ul-indigo: #4f46e5;
        --ul-violet: #7c3aed;
        --ul-green: #059669;
        --ul-red: #dc2626;
        --ul-teal: #0d9488;
        --ul-amber: #d97706;
        --ul-radius: 16px;
        --ul-shadow: 0 1px 2px rgba(15, 23, 42, 0.05), 0 12px 40px rgba(15, 23, 42, 0.07);
        background: var(--ul-bg);
        min-height: 100vh;
        padding: 0;
        color: var(--ul-text);
        font-feature-settings: "ss01" on;
    }

    /* Sidebar 280px; keep below fixed .header (theme uses ~50px top padding — do not shrink or title overlaps menu) */
    .unified-leads-container .main-container {
        width: 100%;
        max-width: none;
        box-sizing: border-box;
        padding-top: 52px;
        padding-right: 8px;
        padding-bottom: 0;
        padding-left: 286px;
    }

    body.sidebar-shrink .unified-leads-container .main-container {
        padding-left: 12px;
    }

    .unified-leads-container .pd-ltr-20 {
        padding: 0 !important;
        width: 100%;
        max-width: 100%;
    }

    @media (max-width: 1300px) {
        .unified-leads-container .main-container {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 48px;
        }
    }

    .ul-page-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        padding-top: 0;
    }

    .ul-page-title {
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        margin: 0;
        color: var(--ul-text);
    }

    .ul-page-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 10px 16px;
        border-radius: 999px;
        background: var(--ul-surface);
        border: 1px solid var(--ul-border);
        box-shadow: var(--ul-shadow);
        color: var(--ul-indigo);
    }

    .ul-kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-bottom: 10px;
    }

    .ul-kpi {
        position: relative;
        overflow: hidden;
        border-radius: var(--ul-radius);
        padding: 16px 18px;
        color: #fff;
        box-shadow: var(--ul-shadow);
        min-height: 102px;
    }

    .ul-kpi::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 100% 0%, rgba(255, 255, 255, 0.22), transparent 55%);
        pointer-events: none;
    }

    .ul-kpi-label {
        position: relative;
        z-index: 1;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.92;
        margin-bottom: 10px;
    }

    .ul-kpi-value {
        position: relative;
        z-index: 1;
        font-size: 2.15rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .ul-kpi-meta {
        position: relative;
        z-index: 1;
        margin-top: 10px;
        font-size: 0.72rem;
        opacity: 0.9;
        line-height: 1.45;
    }

    .ul-kpi--indigo {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }

    .ul-kpi--green {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .ul-kpi--red {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    }

    .ul-kpi--teal {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    }

    .ul-track-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 10px;
        margin-bottom: 8px;
    }

    .ul-track-card {
        background: var(--ul-surface);
        border-radius: var(--ul-radius);
        border: 1px solid var(--ul-border);
        box-shadow: var(--ul-shadow);
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .ul-track-card--sub {
        border-top: 3px solid var(--ul-indigo);
    }

    .ul-track-card--fb {
        border-top: 3px solid var(--ul-green);
    }

    .ul-track-card--exp {
        border-top: 3px solid var(--ul-red);
    }

    .ul-track-head {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ul-track-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .ul-track-icon--sub {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }

    .ul-track-icon--fb {
        background: linear-gradient(135deg, #059669, #047857);
    }

    .ul-track-icon--exp {
        background: linear-gradient(135deg, #dc2626, #991b1b);
    }

    .ul-track-title {
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        color: var(--ul-text);
    }

    .ul-track-sub {
        font-size: 0.8rem;
        color: var(--ul-muted);
        margin: 2px 0 0 0;
    }

    .ul-track-metric {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1;
    }

    .ul-track-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .ul-track-pill {
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 0.75rem;
    }

    .ul-track-pill--need {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .ul-track-pill--ok {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }

    .ul-track-pill strong {
        display: block;
        font-size: 1.35rem;
        font-weight: 800;
        margin-top: 4px;
    }

    .ul-track-foot {
        font-size: 0.78rem;
        color: var(--ul-muted);
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
        line-height: 1.45;
    }

    .ul-journey-cell {
        min-width: 0;
    }

    .ul-journey-dates {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--ul-text);
        margin-bottom: 8px;
    }

    .ul-journey-arrow {
        color: var(--ul-muted);
        font-weight: 400;
    }

    .ul-journey-bar-wrap {
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .ul-journey-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--ul-indigo), var(--ul-teal));
        transition: width 0.35s ease;
    }

    .ul-journey-bar-fill.ul-journey-bar-fill--done {
        background: linear-gradient(90deg, #94a3b8, #64748b);
    }

    .ul-journey-cap {
        font-size: 0.7rem;
        color: var(--ul-muted);
        line-height: 1.35;
    }

    .user-link {
        text-decoration: none !important;
        color: inherit !important;
        display: block;
        transition: opacity 0.2s ease;
    }

    .user-link:hover {
        opacity: 0.75;
    }

    .user-link:hover .user-name {
        color: var(--ul-indigo);
    }

    .ul-comm-stack {
        display: flex;
        flex-direction: row;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ul-comm-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 8px;
        padding: 5px 7px;
        font-size: 0.65rem;
        font-weight: 700;
        width: auto;
        max-width: 100%;
        box-sizing: border-box;
        line-height: 1.25;
    }

    .ul-comm-chip--yes {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .ul-comm-chip--no {
        background: #f8fafc;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
    }

    .ul-comm-chip i {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .ul-comm-hint {
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        margin-top: 4px;
    }

    .ul-lifecycle-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #fff;
        max-width: 100%;
        white-space: normal;
        text-align: center;
        line-height: 1.2;
    }

    .filter-section .form-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        margin-bottom: 8px;
        display: block;
    }

    .ul-filters-form .form-control,
    .ul-filters-form select.form-control {
        font-size: 0.875rem;
        border-radius: 10px;
        min-height: 40px;
        padding: 8px 12px;
        border-color: #cbd5e1;
    }

    .ul-filters-form .form-control:focus {
        border-color: var(--ul-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    .ul-filter-group {
        margin-bottom: 1.35rem;
        padding-bottom: 1.35rem;
        border-bottom: 1px solid #e8eef4;
    }

    .ul-filter-group:last-of-type {
        border-bottom: none;
        margin-bottom: 0.75rem;
        padding-bottom: 0;
    }

    .ul-filter-group-title {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.11em;
        color: var(--ul-indigo);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ul-filter-group-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, #c7d2fe, transparent);
        max-width: 140px;
    }

    .ul-filter-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding-top: 14px;
        margin-top: 4px;
        border-top: 1px solid #eef2f7;
    }

    .ul-filter-actions .btn {
        font-size: 0.875rem;
        padding: 9px 18px;
        border-radius: 10px;
    }

    .filter-section.filter-section--compact {
        padding: 0;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .ul-filter-panel-body {
        display: none;
        padding: 18px 18px 20px;
        border-top: 1px solid #eef2f7;
    }

    .filter-section.is-filters-open .ul-filter-panel-body {
        display: block;
    }

    .ul-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px 16px;
        padding: 12px 16px;
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
    }

    .ul-filter-bar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .ul-filter-bar-title {
        font-size: 0.9375rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #0f172a;
        margin: 0;
    }

    .ul-filter-bar-note {
        font-size: 0.75rem;
        color: var(--ul-muted);
        font-weight: 600;
        line-height: 1.4;
    }

    .ul-filter-pill {
        font-size: 0.62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 5px 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: var(--ul-indigo);
        border: 1px solid #c7d2fe;
    }

    .ul-filter-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 0.8125rem;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, color 0.2s;
    }

    .ul-filter-toggle:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .filter-section.is-filters-open .ul-filter-toggle {
        background: #eef2ff;
        border-color: #a5b4fc;
        color: var(--ul-indigo);
    }

    .ul-filter-toggle-icon {
        display: inline-flex;
        transition: transform 0.25s ease;
        font-size: 14px;
    }

    .filter-section.is-filters-open .ul-filter-toggle-icon {
        transform: rotate(180deg);
    }

    .unified-leads-container .main-container,
    .unified-leads-container .pd-ltr-20 {
        overflow-x: hidden;
        box-sizing: border-box;
    }

    .ul-expand-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 8px;
        border: 1px solid var(--ul-border);
        background: var(--ul-surface);
        color: var(--ul-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.2s, border-color 0.2s, background 0.2s;
    }

    .ul-expand-btn .icon-copy {
        font-size: 14px;
    }

    .ul-expand-btn:hover {
        color: var(--ul-indigo);
        border-color: #c7d2fe;
        background: #eef2ff;
    }

    .ul-expand-btn[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .ul-expand-btn i {
        transition: transform 0.25s ease;
    }

    .ul-lead-expand-row td {
        padding: 0 !important;
        border-top: none !important;
        background: #f8fafc;
    }

    .ul-expand-panel {
        padding: 20px 22px 22px;
        border-top: 1px solid var(--ul-border);
    }

    .ul-expand-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }

    .ul-expand-card {
        background: var(--ul-surface);
        border: 1px solid var(--ul-border);
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .ul-expand-card h4 {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--ul-muted);
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ul-expand-card h4 i {
        color: var(--ul-indigo);
    }

    .ul-expand-kv {
        display: grid;
        gap: 10px;
    }

    .ul-expand-kv div {
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #334155;
    }

    .ul-expand-kv strong {
        display: block;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--ul-muted);
        margin-bottom: 3px;
        font-weight: 700;
    }

    .ul-expand-blurb {
        font-size: 0.8rem;
        color: #475569;
        line-height: 1.5;
        margin-top: 8px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .ul-expand-foot {
        margin-top: 14px;
        font-size: 0.75rem;
        color: var(--ul-muted);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card,
    .filter-section,
    .leads-table-container {
        background: #fff;
        border: 1px solid #e7edf5;
        border-radius: 18px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        max-width: 100%;
        overflow-x: hidden;
    }

    /* No horizontal scrollbar — table stays within the panel width */
    .ul-table-scroller {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        padding: 0 0 10px;
    }

    .stat-card {
        padding: 18px 20px;
    }

    .stat-number {
        font-size: 30px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 6px;
    }

    .stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 600;
        color: #64748b;
    }

    .filter-section:not(.filter-section--compact) {
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .filter-header h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }

    .filter-subtext {
        font-size: 13px;
        color: #64748b;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 8px 12px;
        background: #eef2ff;
        color: #3730a3;
        font-size: 12px;
        font-weight: 700;
    }

    .followup-track-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .followup-track-card {
        background: #fff;
        border: 1px solid #e7edf5;
        border-radius: 18px;
        padding: 18px 20px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
    }

    .followup-track-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .followup-track-count {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 8px;
    }

    .followup-track-note {
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }

    .form-control {
        border: 1px solid #d8e1ec;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    .btn-filter {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        color: white;
        font-weight: 600;
    }

    .btn-reset {
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 600;
    }

    .table-topbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
        align-items: center;
        padding: 12px 14px 0;
    }

    .table-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
    }

    .legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 8px 14px 0;
    }

    .legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .table-heading {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .table-subheading {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 2px;
        line-height: 1.35;
    }

    .leads-table {
        width: 100%;
        max-width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .leads-table thead th {
        background: #f8fafc;
        padding: 7px 5px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        white-space: normal;
        line-height: 1.25;
        hyphens: auto;
        word-break: break-word;
        position: sticky;
        top: 0;
        z-index: 2;
        vertical-align: bottom;
    }

    .leads-table tbody td {
        padding: 6px 5px;
        vertical-align: top;
        font-size: 11px;
        color: #334155;
        border-top: 1px solid #eef2f7;
        transition: background-color 0.2s ease;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .leads-table tbody tr:hover td {
        background: #fafcff;
    }

    .leads-table tbody tr:hover {
        box-shadow: inset 0 0 0 9999px rgba(79, 70, 229, 0.015);
    }

    .source-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: white;
    }

    .user-info-card {
        display: flex;
        gap: 8px;
        min-width: 0;
    }

    .user-info-card > div:last-child {
        min-width: 0;
    }

    .user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .user-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 13px;
        margin-bottom: 3px;
        line-height: 1.3;
    }

    .user-email,
    .meta-line,
    .feedback-meta,
    .expire-meta,
    .followup-disabled {
        font-size: 12px;
        color: #64748b;
    }

    .meta-line,
    .expire-meta {
        margin-top: 4px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        max-width: 100%;
        white-space: normal;
        text-align: center;
        line-height: 1.2;
        justify-content: center;
    }

    .leads-table .status-badge {
        display: flex;
    }

    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }

    .status-badge.expired {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-badge.pending {
        background: #fff7db;
        color: #a16207;
    }

    .status-badge.inactive {
        background: #e2e8f0;
        color: #475569;
    }

    .comm-stack {
        display: grid;
        gap: 8px;
    }

    .comm-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
    }

    .comm-indicator {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }

    .comm-indicator.sent {
        background: #dcfce7;
        color: #166534;
    }

    .comm-indicator.not-sent {
        background: #fee2e2;
        color: #b91c1c;
    }

    .followup-box {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 0;
    }

    .followup-tracks {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-start;
    }

    .followup-track-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .followup-track-label {
        font-size: 10px;
        font-weight: 800;
        color: #64748b;
        width: 30px;
        flex-shrink: 0;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .followup-track-row .followup-box {
        min-width: 0;
    }

    .followup-by-stack {
        font-size: 12px;
        line-height: 1.45;
    }

    .followup-by-stack strong {
        color: #475569;
        margin-right: 4px;
    }

    .info-icon {
        cursor: pointer;
        color: #667eea;
        font-size: 16px;
        margin-left: 8px;
        vertical-align: middle;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .info-icon:hover {
        color: #764ba2;
        transform: scale(1.1);
    }

    /* Ensure followup cell content stays inline */
    td .followup-switch,
    td .info-icon,
    td .switchery {
        display: inline-block;
        vertical-align: middle;
    }

    table td:has(.followup-switch) {
        white-space: normal;
    }

    .feedback-preview {
        display: grid;
        gap: 6px;
    }

    .followup-focus {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .followup-focus-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
    }

    .followup-focus-pill.active-subscription {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
    }

    .followup-focus-pill.feedback-completed {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .followup-focus-pill.expire-renewal {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .feedback-stars i {
        font-size: 12px;
        margin-right: 2px;
    }

    .empty-state {
        padding: 48px 20px;
        text-align: center;
        color: #64748b;
    }

    .detail-btn {
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        padding: 7px 12px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .detail-hero {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
        border: 1px solid #dbe4ff;
        margin-bottom: 16px;
    }

    .detail-hero-name {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .detail-hero-meta {
        font-size: 13px;
        color: #64748b;
        line-height: 1.6;
    }

    .detail-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-start;
        justify-content: flex-end;
    }

    .detail-pill {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid transparent;
    }

    .detail-pill.primary {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
    }

    .detail-pill.success {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .detail-pill.danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .detail-pill.warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #b45309;
    }

    .modal-content {
        border-radius: 16px;
        border: none;
    }

    .modal-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        border: none;
    }

    @media (max-width: 767px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .table-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }

    /* Pagination Styles */
    .pagination-info {
        font-weight: 500;
    }

    .pagination-links .pagination {
        margin: 0;
        display: flex;
        gap: 4px;
        list-style: none;
        padding: 0;
    }

    .pagination-links .page-item {
        margin: 0;
    }

    .pagination-links .page-link {
        color: var(--ul-indigo);
        background-color: white;
        border: 1px solid var(--ul-border);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.813rem;
        font-weight: 500;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }

    .pagination-links .page-link:hover {
        background-color: var(--ul-indigo);
        color: white;
        border-color: var(--ul-indigo);
        text-decoration: none;
    }

    .pagination-links .page-item.active .page-link {
        background-color: var(--ul-indigo);
        border-color: var(--ul-indigo);
        color: white;
        z-index: 1;
    }

    .pagination-links .page-item.disabled .page-link {
        color: var(--ul-muted);
        background-color: #f8fafc;
        border-color: var(--ul-border);
        cursor: not-allowed;
        pointer-events: none;
    }

    .table-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid var(--ul-border);
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-actions {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .table-heading {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--ul-text);
        margin-bottom: 4px;
    }

    .table-subheading {
        font-size: 0.875rem;
        color: var(--ul-muted);
    }

    @media (max-width: 768px) {
        .table-topbar {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .table-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .pagination-links {
            order: -1;
            width: 100%;
            display: flex;
            justify-content: center;
        }
    }
</style>

<div class="unified-leads-container">
    <div class="main-container">
        <div class="pd-ltr-20">
            <header class="ul-page-head">
                <div>
                    <h1 class="ul-page-title">Unified leads</h1>
                </div>
                <div class="ul-page-badge">{{ $total }} in view</div>
            </header>

            @if(isset($debug_info))
            <div style="background: #f8f9fa; padding: 10px; margin-bottom: 20px; border-radius: 8px; font-size: 12px;">
                <strong>Debug Info:</strong><br>
                Purchase History: {{ $debug_info['purchase_history_count'] ?? 'N/A' }} records |
                Orders: {{ $debug_info['orders_count'] ?? 'N/A' }} records |
                Active Orders: {{ $debug_info['active_orders_count'] ?? 'N/A' }} records<br>
                Has Followup Columns: {{ $debug_info['has_followup_columns'] ? 'YES' : 'NO' }} |
                Using Fallback: {{ $debug_info['using_fallback'] ? 'YES' : 'NO' }}<br>
                Result Total: {{ $debug_info['result_total'] ?? 'N/A' }} |
                Result Leads: {{ $debug_info['result_leads_count'] ?? 'N/A' }}
            </div>
            @endif

            <div class="ul-kpi-row">
                <div class="ul-kpi ul-kpi--indigo">
                    <div class="ul-kpi-label">Total leads</div>
                    <div class="ul-kpi-value">{{ $stats['total'] }}</div>
                    <div class="ul-kpi-meta">Filtered list</div>
                </div>
                <div class="ul-kpi ul-kpi--green">
                    <div class="ul-kpi-label">Active subscriptions</div>
                    <div class="ul-kpi-value">{{ $stats['active_subscriptions'] }}</div>
                    <div class="ul-kpi-meta">Follow-up pending: {{ $stats['active_needs_followup'] }} · Expiring in 7d:
                        {{ $stats['active_expiring_week'] }}</div>
                </div>
                <div class="ul-kpi ul-kpi--red">
                    <div class="ul-kpi-label">Expired</div>
                    <div class="ul-kpi-value">{{ $stats['expired_subscriptions'] }}</div>
                    <div class="ul-kpi-meta">Today {{ $stats['expired_today'] }} · 7d {{ $stats['expired_week'] }} · 30d
                        {{ $stats['expired_month'] }}</div>
                </div>
                <div class="ul-kpi ul-kpi--teal">
                    <div class="ul-kpi-label">Feedback</div>
                    <div class="ul-kpi-value">{{ $stats['completed_feedback'] }}</div>
                    <div class="ul-kpi-meta">Pending {{ $stats['pending_feedback'] }} · Needs follow-up
                        {{ $stats['feedback_needs_followup'] }}</div>
                </div>
            </div>

            <div class="ul-track-row">
                <article class="ul-track-card ul-track-card--sub">
                    <div class="ul-track-head">
                        <div class="ul-track-icon ul-track-icon--sub"><i class="icon-copy dw dw-checked"></i></div>
                        <div>
                            <h2 class="ul-track-title">Subscription follow-up</h2>
                            <p class="ul-track-sub">Active subscribers · stored on order</p>
                        </div>
                    </div>
                    <div class="ul-track-metric" style="color:#4338ca;">{{ $stats['subscription_followups'] }}</div>
                    <div class="ul-track-split">
                        <div class="ul-track-pill ul-track-pill--need">Needs call<strong>{{ $stats['active_needs_followup'] }}</strong></div>
                        <div class="ul-track-pill ul-track-pill--ok">Logged<strong>{{ $stats['active_with_followup'] }}</strong></div>
                    </div>
                    <div class="ul-track-foot">Expiring soon: <strong>{{ $stats['active_expiring_week'] }}</strong> in 7
                        days · <strong>{{ $stats['active_expiring_month'] }}</strong> in 30 days</div>
                </article>
                <article class="ul-track-card ul-track-card--fb">
                    <div class="ul-track-head">
                        <div class="ul-track-icon ul-track-icon--fb"><i class="icon-copy dw dw-chat-3"></i></div>
                        <div>
                            <h2 class="ul-track-title">Feedback follow-up</h2>
                            <p class="ul-track-sub">After WhatsApp feedback sent</p>
                        </div>
                    </div>
                    <div class="ul-track-metric" style="color:#047857;">{{ $stats['feedback_followups'] }}</div>
                    <div class="ul-track-split">
                        <div class="ul-track-pill ul-track-pill--need">Needs call<strong>{{ $stats['feedback_needs_followup'] }}</strong></div>
                        <div class="ul-track-pill ul-track-pill--ok">Logged<strong>{{ $stats['feedback_with_followup'] }}</strong></div>
                    </div>
                    <div class="ul-track-foot">Feedback messages sent: <strong>{{ $stats['feedback_sent'] }}</strong>
                    </div>
                </article>
                <article class="ul-track-card ul-track-card--exp">
                    <div class="ul-track-head">
                        <div class="ul-track-icon ul-track-icon--exp"><i class="icon-copy dw dw-calendar1"></i></div>
                        <div>
                            <h2 class="ul-track-title">Expiry / renewal</h2>
                            <p class="ul-track-sub">Expired users · transaction log</p>
                        </div>
                    </div>
                    <div class="ul-track-metric" style="color:#b91c1c;">{{ $stats['expiry_followups'] }}</div>
                    <div class="ul-track-split">
                        <div class="ul-track-pill ul-track-pill--need">Needs call<strong>{{ $stats['expired_needs_followup'] }}</strong></div>
                        <div class="ul-track-pill ul-track-pill--ok">Logged<strong>{{ $stats['expired_with_followup'] }}</strong></div>
                    </div>
                    <div class="ul-track-foot">Expiry cohorts: today <strong>{{ $stats['expired_today'] }}</strong> · 7d
                        <strong>{{ $stats['expired_week'] }}</strong> · 30d <strong>{{ $stats['expired_month'] }}</strong>
                    </div>
                </article>
            </div>

            @php
                $ulFilterKeys = [
                    'search',
                    'subscription_filter',
                    'comm_filter',
                    'subscription_followup_filter',
                    'feedback_followup_filter',
                    'expiry_followup_filter',
                    'subscription_followup_label',
                    'feedback_followup_label',
                    'expiry_followup_label',
                ];
                $ulHasActiveFilters = false;
                foreach ($ulFilterKeys as $ulK) {
                    if (request()->filled($ulK)) {
                        $ulHasActiveFilters = true;
                        break;
                    }
                }
            @endphp
            <div class="filter-section filter-section--compact {{ $ulHasActiveFilters ? 'is-filters-open' : '' }}"
                id="ulFiltersWrap">
                <div class="ul-filter-bar">
                    <div class="ul-filter-bar-left">
                        <span class="ul-filter-bar-title">Filters</span>
                        @if ($ulHasActiveFilters)
                            <span class="ul-filter-pill">Filtered</span>
                        @endif
                        <span class="ul-filter-bar-note d-none d-md-inline">Search, outreach, follow-up &amp; labels</span>
                    </div>
                    <button type="button" class="ul-filter-toggle" id="ulFilterToggle"
                        aria-expanded="{{ $ulHasActiveFilters ? 'true' : 'false' }}"
                        aria-controls="ulFilterPanelBody">
                        <i class="icon-copy dw dw-down ul-filter-toggle-icon" aria-hidden="true"></i>
                        <span class="ul-filter-toggle-text">{{ $ulHasActiveFilters ? 'Hide filters' : 'Show filters' }}</span>
                    </button>
                </div>
                <div class="ul-filter-panel-body" id="ulFilterPanelBody">
                    <form method="GET" action="{{ route('unified_leads.index') }}" class="ul-filters-form">
                        <div class="ul-filter-group">
                            <div class="ul-filter-group-title">Search &amp; lifecycle</div>
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-8 mb-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Name, email, phone, plan…" value="{{ request('search') }}">
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <label class="form-label">Subscription</label>
                                    <select name="subscription_filter" class="form-control">
                                        <option value="">All statuses</option>
                                        <option value="Active" {{ request('subscription_filter') == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Expired" {{ request('subscription_filter') == 'Expired' ? 'selected' : '' }}>Expired</option>
                                    </select>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-12 col-sm-6 mb-3">
                                    <label class="form-label">Outreach (email / WhatsApp)</label>
                                    <select name="comm_filter" class="form-control">
                                        <option value="">All touchpoints</option>
                                        <option value="both" {{ request('comm_filter') == 'both' ? 'selected' : '' }}>Email &amp; WhatsApp</option>
                                        <option value="email_only" {{ request('comm_filter') == 'email_only' ? 'selected' : '' }}>Email only</option>
                                        <option value="whatsapp_only" {{ request('comm_filter') == 'whatsapp_only' ? 'selected' : '' }}>WhatsApp only</option>
                                        <option value="none" {{ request('comm_filter') == 'none' ? 'selected' : '' }}>No templates sent</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ul-filter-group">
                            <div class="ul-filter-group-title">Follow-up call status</div>
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Subscription Followup (Active Order)</label>
                                    <select name="subscription_followup_filter" class="form-control">
                                        <option value="">Any</option>
                                        <option value="pending" {{ request('subscription_followup_filter') == 'pending' ? 'selected' : '' }}>Pending call</option>
                                        <option value="completed" {{ request('subscription_followup_filter') == 'completed' ? 'selected' : '' }}>Logged</option>
                                    </select>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Feedback Followup (WP Feedback)</label>
                                    <select name="feedback_followup_filter" class="form-control">
                                        <option value="">Any</option>
                                        <option value="pending" {{ request('feedback_followup_filter') == 'pending' ? 'selected' : '' }}>Pending call</option>
                                        <option value="completed" {{ request('feedback_followup_filter') == 'completed' ? 'selected' : '' }}>Logged</option>
                                    </select>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Expire User Followup</label>
                                    <select name="expiry_followup_filter" class="form-control">
                                        <option value="">Any</option>
                                        <option value="pending" {{ request('expiry_followup_filter') == 'pending' ? 'selected' : '' }}>Pending call</option>
                                        <option value="completed" {{ request('expiry_followup_filter') == 'completed' ? 'selected' : '' }}>Logged</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ul-filter-group">
                            <div class="ul-filter-group-title">Follow-up outcome labels</div>
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Subscription Followup Label</label>
                                    <select name="subscription_followup_label" class="form-control">
                                        <option value="">All outcomes</option>
                                        <option value="__empty__" {{ request('subscription_followup_label') === '__empty__' ? 'selected' : '' }}>No label set</option>
                                        @foreach ($followupLabels as $sk => $sl)
                                            <option value="{{ $sk }}" {{ request('subscription_followup_label') === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Feedback Followup Label (WP Feedback)</label>
                                    <select name="feedback_followup_label" class="form-control">
                                        <option value="">All outcomes</option>
                                        <option value="__empty__" {{ request('feedback_followup_label') === '__empty__' ? 'selected' : '' }}>No label set</option>
                                        @foreach ($feedbackFollowupLabels as $fk => $fl)
                                            <option value="{{ $fk }}" {{ request('feedback_followup_label') === $fk ? 'selected' : '' }}>{{ $fl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Expire User Followup Label</label>
                                    <select name="expiry_followup_label" class="form-control">
                                        <option value="">All outcomes</option>
                                        <option value="__empty__" {{ request('expiry_followup_label') === '__empty__' ? 'selected' : '' }}>No label set</option>
                                        @foreach ($followupLabels as $ek => $el)
                                            <option value="{{ $ek }}" {{ request('expiry_followup_label') === $ek ? 'selected' : '' }}>{{ $el }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ul-filter-group">
                            <div class="ul-filter-group-title">Specific Attributes &amp; Feedback</div>
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">WP Feedback Status</label>
                                    <select name="wp_feedback_status" class="form-control">
                                        <option value="">All statuses</option>
                                        <option value="completed" {{ request('wp_feedback_status') === 'completed' ? 'selected' : '' }}>Submitted (Completed)</option>
                                        <option value="pending" {{ request('wp_feedback_status') === 'pending' ? 'selected' : '' }}>Pending Response</option>
                                        <option value="expired" {{ request('wp_feedback_status') === 'expired' ? 'selected' : '' }}>Expired (No response)</option>
                                        <option value="no_request" {{ request('wp_feedback_status') === 'no_request' ? 'selected' : '' }}>Not Requested</option>
                                    </select>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                                    <label class="form-label">Feedback Rating</label>
                                    <select name="wp_feedback_rating" class="form-control">
                                        <option value="">All ratings</option>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" {{ (int)request('wp_feedback_rating') === $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method_filter" class="form-control">
                                        <option value="">All methods</option>
                                        @php
                                            $pMethods = ['Razorpay', 'PhonePe', 'upi', 'Stripe', 'Manual', 'card'];
                                        @endphp
                                        @foreach ($pMethods as $pm)
                                            <option value="{{ $pm }}" {{ request('payment_method_filter') === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 mb-3">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status_filter" class="form-control">
                                        <option value="">All statuses</option>
                                        <option value="Success" {{ request('payment_status_filter') === 'Success' ? 'selected' : '' }}>Success</option>
                                        <option value="Failed" {{ request('payment_status_filter') === 'Failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="Pending" {{ request('payment_status_filter') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 mb-3">
                                    <label class="form-label">Country (e.g. 91)</label>
                                    <input type="text" name="country_filter" class="form-control"
                                        placeholder="Country code" value="{{ request('country_filter') }}">
                                </div>
                            </div>
                        </div>

                        <div class="ul-filter-actions">
                            <button type="submit" class="btn btn-filter">
                                <i class="icon-copy dw dw-search"></i> Apply filters
                            </button>
                            <a href="{{ route('unified_leads.index') }}" class="btn btn-light border btn-reset mb-0">
                                <i class="icon-copy dw dw-refresh"></i> Reset all
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="leads-table-container">
                <div class="table-topbar">
                    <div>
                        <div class="table-heading">Lead list</div>
                        <div class="table-subheading">
                            @if($leads->total() > 0)
                                Showing {{ $leads->firstItem() }} to {{ $leads->lastItem() }} of {{ number_format($leads->total()) }} results
                            @else
                                No results found
                            @endif
                        </div>
                    </div>
                    <div class="table-actions" style="display: flex; align-items: center; gap: 15px;">
                        <form method="GET" action="{{ route('unified_leads.index') }}" style="margin: 0;">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select name="per_page" class="form-control" onchange="this.form.submit()" style="width: auto; display: inline-block; font-size: 0.875rem; padding: 6px 12px;">
                                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50 per page</option>
                                <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100 per page</option>
                                <option value="200" {{ request('per_page', 50) == 200 ? 'selected' : '' }}>200 per page</option>
                                <option value="500" {{ request('per_page', 50) == 500 ? 'selected' : '' }}>500 per page</option>
                            </select>
                        </form>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="exportLeadsBtn">
                            <i class="icon-copy dw dw-download"></i> Export CSV
                        </button>
                    </div>
                </div>

                <div class="legend-row">
                    <span class="legend-pill"><span class="legend-dot" style="background:#16a34a;"></span> Active
                        Subscription</span>
                    <span class="legend-pill"><span class="legend-dot" style="background:#dc2626;"></span> Expired
                        Subscription</span>
                    <span class="legend-pill"><span class="legend-dot" style="background:#047857;"></span> Feedback
                        Completed</span>
                    <span class="legend-pill"><span class="legend-dot" style="background:#b45309;"></span> Pending
                        Attention</span>
                </div>

                <div class="ul-table-scroller">
                    <table class="leads-table">
                        <colgroup>
                            <col style="width: 2%;" />
                            <col style="width: 2%;" />
                            <col style="width: 8%;" />
                            <col style="width: 15%;" />
                            <col style="width: 8%;" />
                            <col style="width: 10%;" />
                            <col style="width: 6%;" />
                            <col style="width: 6%;" />
                            <col style="width: 9%;" />
                            <col style="width: 8%;" />
                            <col style="width: 8%;" />
                            <col style="width: 8%;" />
                            <col style="width: 5%;" />
                            <col style="width: 5%;" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th title="Expand row details"></th>
                                <th>#</th>
                                <th>Stage</th>
                                <th>Lead</th>
                                <th>Plan</th>
                                <th>Timeline</th>
                                <th>Account Creation Email / WhatsApp</th>
                                <th>Subscription Followup</th>
                                <th>WP feedback</th>
                                <th>Feedback Followup</th>
                                <th>Expire User Email / WhatsApp</th>
                                <th>Expire User Followup</th>
                                <th>Expiry</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                @php
                                    $subscriptionClass = match ($lead['subscription_status']) {
                                        'Active' => 'active',
                                        'Expired' => 'expired',
                                        'Pending' => 'pending',
                                        'Failed' => 'inactive',
                                        default => 'inactive',
                                    };

                                    $feedbackClass = match ($lead['feedback_status']) {
                                        'Completed' => 'active',
                                        'Expired' => 'expired',
                                        'Pending' => 'pending',
                                        default => 'inactive',
                                    };
                                @endphp
                                <tr class="ul-lead-main-row">
                                    <td>
                                        <button type="button" class="ul-expand-btn" aria-expanded="false"
                                            aria-label="Show outreach and feedback details for {{ $lead['name'] }}">
                                            <i class="icon-copy dw dw-down"></i>
                                        </button>
                                    </td>
                                    <td><strong>{{ ($leads->currentPage() - 1) * $leads->perPage() + $loop->iteration }}</strong>
                                    </td>
                                    <td>
                                        <span class="ul-lifecycle-pill"
                                            style="background: {{ $lead['lifecycle_color'] }};">
                                            {{ $lead['lifecycle_label'] }}
                                        </span>
                                        @if(isset($lead['days_active']))
                                            <div style="font-size: 11px; color: #16a34a; margin-top: 4px; font-weight: 600;">
                                                <i class="icon-copy dw dw-checked" style="font-size: 10px;"></i>
                                                {{ $lead['days_active'] }} days active
                                            </div>
                                        @endif
                                        @if(isset($lead['days_to_expiry']) && $lead['days_to_expiry'] > 0)
                                            <div style="font-size: 11px; color: #ea580c; margin-top: 4px; font-weight: 600;">
                                                <i class="icon-copy dw dw-alarm-clock" style="font-size: 10px;"></i>
                                                {{ $lead['days_to_expiry'] }} days left
                                            </div>
                                        @endif
                                        @if(isset($lead['days_expired']))
                                            <div style="font-size: 11px; color: #dc2626; margin-top: 4px; font-weight: 600;">
                                                <i class="icon-copy dw dw-calendar1" style="font-size: 10px;"></i>
                                                {{ $lead['days_expired'] }} days ago
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="user-info-card">
                                            <div class="user-avatar">
                                                {{ strtoupper(substr(trim($lead['name']) ?: 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ url('user_detail/' . $lead['email']) }}" class="user-link">
                                                    <div class="user-name">{{ $lead['name'] }}</div>
                                                    <div class="user-email">{{ $lead['email'] }}</div>
                                                </a>
                                                <div class="meta-line">
                                                    {{ $lead['usage_type'] && $lead['usage_type'] !== '-' ? ucfirst($lead['usage_type']) : 'Unknown' }}
                                                    usage
                                                    @if(!empty($lead['amount']) && $lead['amount'] !== '-')
                                                        | {{ $lead['amount'] }}
                                                    @endif
                                                </div>
                                                <div class="meta-line">Source: {{ $lead['from_where'] ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="status-badge {{ $subscriptionClass }}">{{ $lead['subscription_status'] ?: '-' }}</span>
                                        <div class="meta-line">
                                            {{ $lead['plan_title'] ?? ucfirst(str_replace('_', ' ', $lead['plan_type'])) }}
                                        </div>
                                        <div class="meta-line">Order status: {{ $lead['order_status'] ?? '-' }}</div>
                                        @if(!empty($lead['created_at']))
                                            <div class="meta-line" style="color: {{ $lead['subscription_status'] === 'Active' ? '#16a34a' : '#6b7280' }}; font-weight: 600;">
                                                <i class="icon-copy dw dw-calendar1" style="font-size: 10px;"></i>
                                                Started: {{ \Carbon\Carbon::parse($lead['created_at'])->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="ul-journey-cell">
                                        <div class="ul-journey-dates">
                                            <span title="Start">{{ $lead['journey_start_label'] ?? '—' }}</span>
                                            <span class="ul-journey-arrow" aria-hidden="true">→</span>
                                            <span title="Expiry / end">{{ $lead['journey_end_label'] ?? '—' }}</span>
                                        </div>
                                        @if($lead['journey_bar_pct'] !== null)
                                            <div class="ul-journey-bar-wrap" role="progressbar"
                                                aria-valuenow="{{ min(100, (int) $lead['journey_bar_pct']) }}"
                                                aria-valuemin="0" aria-valuemax="100"
                                                aria-label="Subscription cycle progress">
                                                <div class="ul-journey-bar-fill {{ $lead['subscription_status'] === 'Expired' ? 'ul-journey-bar-fill--done' : '' }}"
                                                    style="width: {{ min(100, max(0, (int) $lead['journey_bar_pct'])) }}%;"></div>
                                            </div>
                                        @endif
                                        <div class="ul-journey-cap">{{ $lead['journey_caption'] ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="ul-comm-stack">
                                            @if($lead['account_creation_email_count'] > 0)
                                                <span class="ul-comm-chip ul-comm-chip--yes"><i
                                                        class="fa-solid fa-envelope"></i> {{ (int) $lead['account_creation_email_count'] }}</span>
                                            @else
                                                <span class="ul-comm-chip ul-comm-chip--no"><i
                                                        class="fa-solid fa-envelope"></i> 0</span>
                                            @endif
                                            
                                            @if($lead['account_creation_wp_count'] > 0)
                                                <span class="ul-comm-chip ul-comm-chip--yes"><i
                                                        class="fa-brands fa-whatsapp"></i> {{ (int) $lead['account_creation_wp_count'] }}</span>
                                            @else
                                                <span class="ul-comm-chip ul-comm-chip--no"><i
                                                        class="fa-brands fa-whatsapp"></i> 0</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($lead['requires_subscription_followup']))
                                            <div class="followup-box">
                                                <input type="checkbox" class="followup-switch switch-btn"
                                                    data-id="{{ $lead['subscription_followup_id'] }}" data-track="subscription"
                                                    data-plan="{{ $lead['plan_title'] ?? $lead['plan_type'] }}"
                                                    data-size="small" {{ !empty($lead['subscription_followup_call']) ? 'checked' : '' }} />
                                                @if(!empty($lead['subscription_followup_call']) && (($lead['subscription_followup_note'] ?? '') !== '' || ($lead['subscription_followup_label'] ?? '') !== ''))
                                                    @php
                                                        $labelKey = $lead['subscription_followup_label'] ?? '';
                                                        $labelDisplay = isset($followupLabels[$labelKey]) ? $followupLabels[$labelKey] : $labelKey;
                                                    @endphp
                                                    <i class="fa-solid fa-circle-info info-icon"
                                                        data-id="{{ $lead['subscription_followup_id'] }}" data-track="subscription"
                                                        data-name="{{ $lead['name'] }}"
                                                        data-plan="{{ $lead['plan_title'] ?? $lead['plan_type'] }}"
                                                        data-note="{{ $lead['subscription_followup_note'] }}"
                                                        data-label="{{ $labelKey }}" data-label-display="{{ $labelDisplay }}"
                                                        title="Subscription follow-up details"></i>
                                                @endif
                                            </div>
                                            <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                                                <strong>By:</strong> {{ $lead['subscription_followup_by'] ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($lead['feedback_sent']))
                                            @php
                                                $fbBadge = match ($lead['feedback_status'] ?? '') {
                                                    'Completed' => 'active',
                                                    'Pending' => 'pending',
                                                    'Expired' => 'expired',
                                                    default => 'inactive',
                                                };
                                            @endphp
                                            <span
                                                class="status-badge {{ $fbBadge }}">{{ $lead['feedback_status'] ?? '—' }}</span>
                                            @if(!empty($lead['feedback_request_date_short']))
                                                <div class="meta-line">WPsent {{ $lead['feedback_request_date_short'] }}</div>
                                            @endif
                                            @if($lead['feedback_rating'] !== null && $lead['feedback_rating'] !== '')
                                                <div class="meta-line">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <span style="color: {{ $i <= (int)$lead['feedback_rating'] ? '#f59e0b' : '#cbd5e1' }}; font-size: 15px; line-height: 1;">★</span>
                                                    @endfor
                                                </div>
                                            @endif
                                        @else
                                            <div class="ul-comm-stack">
                                                <span class="ul-comm-chip ul-comm-chip--no"><i
                                                        class="fa-brands fa-whatsapp"></i> No request</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($lead['requires_feedback_followup']))
                                            <div class="followup-box">
                                                <input type="checkbox" class="followup-switch switch-btn"
                                                    data-id="{{ $lead['feedback_followup_id'] ?? 'feedback_new_' . $lead['user_id'] }}"
                                                    data-track="feedback"
                                                    data-plan="{{ $lead['plan_title'] ?? $lead['plan_type'] }}"
                                                    data-size="small" {{ !empty($lead['feedback_followup_call']) ? 'checked' : '' }} />
                                                @if(!empty($lead['feedback_followup_call']) && (($lead['feedback_followup_note'] ?? '') !== '' || ($lead['feedback_followup_label'] ?? '') !== ''))
                                                    @php
                                                        $labelKey = $lead['feedback_followup_label'] ?? '';
                                                        $labelDisplay = $feedbackFollowupLabels[$labelKey] ?? $followupLabels[$labelKey] ?? $labelKey;
                                                    @endphp
                                                    <i class="fa-solid fa-circle-info info-icon"
                                                        data-id="{{ $lead['feedback_followup_id'] ?? 'feedback_new_' . $lead['user_id'] }}"
                                                        data-track="feedback" data-name="{{ $lead['name'] }}"
                                                        data-plan="{{ $lead['plan_title'] ?? $lead['plan_type'] }}"
                                                        data-note="{{ $lead['feedback_followup_note'] }}"
                                                        data-label="{{ $labelKey }}" data-label-display="{{ $labelDisplay }}"
                                                        title="Feedback follow-up details"></i>
                                                @endif
                                            </div>
                                            <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                                                <strong>By:</strong> {{ $lead['feedback_followup_by'] ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="ul-comm-stack">
                                            @if($lead['email_sent'])
                                                <span class="ul-comm-chip ul-comm-chip--yes"><i
                                                        class="fa-solid fa-envelope"></i> {{ (int) ($lead['email_count'] ?? 0) }}</span>
                                            @else
                                                <span class="ul-comm-chip ul-comm-chip--no"><i
                                                        class="fa-solid fa-envelope"></i> 0</span>
                                            @endif
                                            
                                            @if($lead['whatsapp_sent'])
                                                <span class="ul-comm-chip ul-comm-chip--yes"><i
                                                        class="fa-brands fa-whatsapp"></i> {{ (int) ($lead['whatsapp_count'] ?? 0) }}</span>
                                            @else
                                                <span class="ul-comm-chip ul-comm-chip--no"><i
                                                        class="fa-brands fa-whatsapp"></i> 0</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($lead['requires_expiry_followup']))
                                            <div class="followup-box">
                                                <input type="checkbox" class="followup-switch switch-btn"
                                                    data-id="{{ $lead['expiry_followup_id'] }}" data-track="expiry"
                                                    data-plan="{{ $lead['plan_title'] ?? $lead['plan_type'] }}"
                                                    data-size="small" {{ !empty($lead['expiry_followup_call']) ? 'checked' : '' }} />
                                                @if(!empty($lead['expiry_followup_call']))
                                                    @php
                                                        $labelKey = $lead['expiry_followup_label'] ?? '';
                                                        $labelDisplay = isset($followupLabels[$labelKey]) ? $followupLabels[$labelKey] : $labelKey;
                                                    @endphp
                                                    <i class="fa-solid fa-circle-info info-icon"
                                                        data-id="{{ $lead['expiry_followup_id'] }}" data-track="expiry"
                                                        data-name="{{ $lead['name'] }}"
                                                        data-plan="{{ $lead['plan_title'] ?? $lead['plan_type'] }}"
                                                        data-note="{{ $lead['expiry_followup_note'] ?? '' }}"
                                                        data-label="{{ $labelKey }}" data-label-display="{{ $labelDisplay }}"
                                                        title="Expiry / renewal follow-up details"></i>
                                                @endif
                                            </div>
                                            <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                                                <strong>By:</strong> {{ $lead['expiry_followup_by'] ?? 'N/A' }}
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead['expire_date'])
                                            <div style="font-weight: 700;">{{ $lead['expire_date'] }}</div>
                                            @if(!empty($lead['expire_meta']))
                                                <div class="expire-meta">{{ $lead['expire_meta'] }}</div>
                                            @endif
                                        @else
                                            <span class="feedback-meta">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $vSubL = $lead['subscription_followup_label'] ?? '';
                                            $vSubLD = $vSubL !== '' ? ($followupLabels[$vSubL] ?? $vSubL) : '';
                                            $vFbL = $lead['feedback_followup_label'] ?? '';
                                            $vFbLD = $vFbL !== '' ? ($feedbackFollowupLabels[$vFbL] ?? $followupLabels[$vFbL] ?? $vFbL) : '';
                                            $vExpL = $lead['expiry_followup_label'] ?? '';
                                            $vExpLD = $vExpL !== '' ? ($followupLabels[$vExpL] ?? $vExpL) : '';
                                        @endphp
                                        <button type="button" class="btn btn-outline-dark detail-btn lead-detail-btn"
                                            data-name="{{ $lead['name'] }}" data-email="{{ $lead['email'] }}"
                                            data-contact="{{ $lead['contact_no'] }}"
                                            data-plan="{{ $lead['plan_title'] ?? '-' }}"
                                            data-subscription="{{ $lead['subscription_status'] ?? '-' }}"
                                            data-order-status="{{ $lead['order_status'] ?? '-' }}"
                                            data-usage="{{ $lead['usage_type'] ?? '-' }}"
                                            data-source="{{ $lead['source_label'] ?? '-' }}"
                                            data-from-where="{{ $lead['from_where'] ?? '-' }}"
                                            data-amount="{{ $lead['amount'] ?? '-' }}"
                                            data-email-send="{{ $lead['email_sent'] ? 'Yes (' . ($lead['email_count'] ?? 1) . ')' : 'No' }}"
                                            data-whatsapp-send="{{ $lead['whatsapp_sent'] ? 'Yes (' . ($lead['whatsapp_count'] ?? 1) . ')' : 'No' }}"
                                            data-feedback-wa="{{ !empty($lead['feedback_sent']) ? 'Yes' : 'No' }}"
                                            data-followup-call="{{ $lead['followup_call'] ? 'Yes' : 'No' }}"
                                            data-followup-call-sub="{{ !empty($lead['requires_subscription_followup']) ? (($lead['subscription_followup_call'] ?? 0) ? 'Yes' : 'No') : '—' }}"
                                            data-followup-call-fb="{{ !empty($lead['requires_feedback_followup']) ? (($lead['feedback_followup_call'] ?? 0) ? 'Yes' : 'No') : '—' }}"
                                            data-followup-call-exp="{{ !empty($lead['requires_expiry_followup']) ? (($lead['expiry_followup_call'] ?? 0) ? 'Yes' : 'No') : '—' }}"
                                            data-followup-by="{{ $lead['followup_by'] ?? 'N/A' }}"
                                            data-followup-by-sub="{{ !empty($lead['requires_subscription_followup']) ? ($lead['subscription_followup_by'] ?? 'N/A') : '—' }}"
                                            data-followup-by-fb="{{ !empty($lead['requires_feedback_followup']) ? ($lead['feedback_followup_by'] ?? 'N/A') : '—' }}"
                                            data-followup-by-exp="{{ !empty($lead['requires_expiry_followup']) ? ($lead['expiry_followup_by'] ?? 'N/A') : '—' }}"
                                            data-followup-label="{{ $lead['followup_label'] ?? '-' }}"
                                            data-followup-note="{{ $lead['followup_note'] ?? '-' }}"
                                            data-followup-label-sub="{{ !empty($lead['requires_subscription_followup']) ? ($lead['subscription_followup_label'] ?? '') : '' }}"
                                            data-followup-label-sub-display="{{ $vSubLD }}"
                                            data-followup-note-sub="{{ !empty($lead['requires_subscription_followup']) ? ($lead['subscription_followup_note'] ?? '') : '' }}"
                                            data-followup-label-fb="{{ !empty($lead['requires_feedback_followup']) ? ($lead['feedback_followup_label'] ?? '') : '' }}"
                                            data-followup-label-fb-display="{{ $vFbLD }}"
                                            data-followup-note-fb="{{ !empty($lead['requires_feedback_followup']) ? ($lead['feedback_followup_note'] ?? '') : '' }}"
                                            data-followup-label-exp="{{ !empty($lead['requires_expiry_followup']) ? ($lead['expiry_followup_label'] ?? '') : '' }}"
                                            data-followup-label-exp-display="{{ $vExpLD }}"
                                            data-followup-note-exp="{{ !empty($lead['requires_expiry_followup']) ? ($lead['expiry_followup_note'] ?? '') : '' }}"
                                            data-followup-focus="{{ trim((!empty($lead['requires_subscription_followup']) ? 'Subscription Active, ' : '') . (!empty($lead['requires_feedback_followup']) ? 'Feedback Completed, ' : '') . (!empty($lead['requires_expiry_followup']) ? 'Recent Expire, ' : ''), ', ') ?: '-' }}"
                                            data-feedback-status="{{ $lead['feedback_status'] ?? '-' }}"
                                            data-feedback-rating="{{ $lead['feedback_rating'] ?? '-' }}"
                                            data-feedback-sent-on="{{ !empty($lead['feedback_request_date']) ? \Carbon\Carbon::parse($lead['feedback_request_date'])->format('d/m/Y H:i') : '-' }}"
                                            data-feedback-comment="{{ $lead['feedback_details']['comment'] ?? '-' }}"
                                            data-feedback-suggestions="{{ $lead['feedback_details']['suggestions'] ?? '-' }}"
                                            data-expiry-date="{{ $lead['expire_date'] ?? '-' }}"
                                            data-expiry-meta="{{ $lead['expire_meta'] ?? '-' }}"
                                            data-journey-start="{{ $lead['journey_start_label'] ?? '' }}"
                                            data-journey-end="{{ $lead['journey_end_label'] ?? '' }}"
                                            data-journey-caption="{{ $lead['journey_caption'] ?? '' }}"
                                            data-comm-ref="{{ $lead['comm_track_ref'] ?? '' }}"
                                            data-comm-blurb="{{ $lead['comm_track_blurb'] ?? '' }}"
                                            data-feedback-wp-ref="{{ $lead['feedback_wp_string_id'] ?? '' }}"
                                            data-feedback-wp-expires="{{ $lead['feedback_wp_expires_label'] ?? '' }}"
                                            data-feedback-wp-completed="{{ $lead['feedback_wp_completed_label'] ?? '' }}"
                                            data-feedback-wp-submitted="{{ $lead['feedback_wp_submitted_label'] ?? '' }}"
                                            data-feedback-wp-status-raw="{{ $lead['feedback_wp_status_raw'] ?? '' }}"
                                            data-feedback-preview="{{ $lead['feedback_comment_preview'] ?? '' }}">
                                            View
                                        </button>
                                    </td>
                                </tr>
                                <tr class="ul-lead-expand-row" style="display: none;">
                                    <td colspan="15" class="ul-lead-expand-cell">
                                        <div class="ul-expand-panel">
                                            <div class="ul-expand-grid">
                                                <div class="ul-expand-card">
                                                    <h4><i class="fa-solid fa-paper-plane"></i> Email &amp; WhatsApp
                                                        tracking</h4>
                                                    <div class="ul-expand-kv">
                                                        <div>
                                                            <strong>Where counts are stored</strong>
                                                            {{ $lead['comm_track_ref'] ?? '—' }}
                                                            @if(($lead['comm_track_source'] ?? '') === 'active_order')
                                                                <span class="badge badge-light border mt-1">Active
                                                                    subscription</span>
                                                            @else
                                                                <span class="badge badge-light border mt-1">Expired
                                                                    user</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>Email templates</strong>
                                                            {{ $lead['email_sent'] ? (int) ($lead['email_count'] ?? 0) . ' send(s) logged' : 'None logged' }}
                                                        </div>
                                                        <div>
                                                            <strong>WhatsApp templates</strong>
                                                            {{ $lead['whatsapp_sent'] ? (int) ($lead['whatsapp_count'] ?? 0) . ' send(s) logged' : 'None logged' }}
                                                        </div>
                                                        <div>
                                                            <strong>Touchpoint pattern</strong>
                                                            @switch($lead['comm_pattern'] ?? 'none')
                                                                @case('both')
                                                                    Email &amp; WhatsApp
                                                                @break

                                                                @case('email_only')
                                                                    Email only
                                                                @break

                                                                @case('whatsapp_only')
                                                                    WhatsApp only
                                                                @break

                                                                @default
                                                                    No automated sends
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                    <p class="ul-expand-blurb mb-0">{{ $lead['comm_track_blurb'] ?? '' }}
                                                    </p>
                                                </div>
                                                <div class="ul-expand-card">
                                                    <h4><i class="fa-brands fa-whatsapp"></i> WhatsApp feedback
                                                        campaign</h4>
                                                    <div class="ul-expand-kv">
                                                        <div>
                                                            <strong>Request status</strong>
                                                            @if(!empty($lead['feedback_sent']))
                                                                {{ $lead['feedback_status'] ?? '—' }}
                                                                @if(!empty($lead['feedback_wp_status_raw']))
                                                                    <span class="text-muted">({{ $lead['feedback_wp_status_raw'] }})</span>
                                                                @endif
                                                            @else
                                                                No WhatsApp feedback request for this user.
                                                            @endif
                                                        </div>
                                                        @if(!empty($lead['feedback_sent']))
                                                            <div>
                                                                <strong>Message sent</strong>
                                                                {{ $lead['feedback_request_date_short'] ?? '—' }}
                                                            </div>
                                                            <div>
                                                                <strong>Request ref</strong>
                                                                {{ $lead['feedback_wp_string_id'] ?? '—' }}
                                                            </div>
                                                            <div>
                                                                <strong>Link expires</strong>
                                                                {{ $lead['feedback_wp_expires_label'] ?? '—' }}
                                                            </div>
                                                            <div>
                                                                <strong>Response submitted</strong>
                                                                {{ $lead['feedback_wp_submitted_label'] ?? ($lead['feedback_wp_completed_label'] ?? '—') }}
                                                            </div>
                                                            @if(!empty($lead['feedback_comment_preview']))
                                                                <div>
                                                                    <strong>Comment preview</strong>
                                                                    {{ $lead['feedback_comment_preview'] }}
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="ul-expand-card">
                                                    <h4><i class="fa-solid fa-phone"></i> Follow-up snapshot</h4>
                                                    <div class="ul-expand-kv">
                                                        <div>
                                                            <strong>Subscription F/U</strong>
                                                            @if(!empty($lead['requires_subscription_followup']))
                                                                {{ !empty($lead['subscription_followup_call']) ? 'Logged' : 'Pending' }}
                                                                @if(!empty($lead['subscription_followup_by']))
                                                                    · {{ $lead['subscription_followup_by'] }}
                                                                @endif
                                                            @else
                                                                N/A (not on active order row)
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>Feedback F/U</strong>
                                                            @if(!empty($lead['requires_feedback_followup']))
                                                                {{ !empty($lead['feedback_followup_call']) ? 'Logged' : 'Pending' }}
                                                                @if(!empty($lead['feedback_followup_by']))
                                                                    · {{ $lead['feedback_followup_by'] }}
                                                                @endif
                                                                @if(!empty($lead['feedback_followup_label']))
                                                                    <div class="mt-1" style="font-size:0.78rem;color:#475569;">
                                                                        Label: {{ $feedbackFollowupLabels[$lead['feedback_followup_label']] ?? $lead['feedback_followup_label'] }}
                                                                    </div>
                                                                @endif
                                                            @else
                                                                —
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <strong>Expiry / renewal F/U</strong>
                                                            @if(!empty($lead['requires_expiry_followup']))
                                                                {{ !empty($lead['expiry_followup_call']) ? 'Logged' : 'Pending' }}
                                                                @if(!empty($lead['expiry_followup_by']))
                                                                    · {{ $lead['expiry_followup_by'] }}
                                                                @endif
                                                            @else
                                                                —
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <p class="ul-expand-foot mb-0">Use <strong>View</strong> for the full
                                                        modal including notes and full feedback text.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="empty-state">No leads match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-footer" style="padding: 18px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; align-items: center;">
                    <div class="pagination-links">
                        {{ $leads->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="leadDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
            <!-- Header with Gradient -->
            <div class="modal-header"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 25px 30px; border-radius: 20px 20px 0 0;">
                <div style="display: flex; align-items: center; width: 100%;">
                    <div
                        style="background: rgba(255,255,255,0.2); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        <i class="fa-solid fa-user" style="font-size: 22px; color: #fff;"></i>
                    </div>
                    <div>
                        <h5 style="color: #ffffff; font-weight: 700; font-size: 20px; margin: 0;">Lead Details</h5>
                        <p style="margin: 0;"><a href="#" id="detailUserLink" style="color: rgba(255,255,255,0.9); font-size: 14px; text-decoration: none; opacity: 0.9;"></a></p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal"
                    style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 28px; font-weight: 300;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="padding: 30px; background: #f8f9fa;">
                <!-- Contact Info Section -->
                <div
                    style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h6
                        style="color: #667eea; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center;">
                        <i class="fa-solid fa-address-card" style="margin-right: 8px;"></i> Contact Information
                    </h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Email</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailEmail"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Contact</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailContact"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Source</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailSource"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                From Where</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFromWhere"></div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Info Section -->
                <div
                    style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h6
                        style="color: #667eea; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center;">
                        <i class="fa-solid fa-crown" style="margin-right: 8px;"></i> Subscription Details
                    </h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Plan</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailPlan"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Amount</div>
                            <div style="font-size: 14px; color: #16a34a; font-weight: 700;" id="detailAmount"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Status</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailSubscription">
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Order Status</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailOrderStatus">
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Usage</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailUsage"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Expiry Date</div>
                            <div style="font-size: 14px; color: #dc2626; font-weight: 600;" id="detailExpiryDate"></div>
                        </div>
                    </div>
                    <div style="margin-top: 16px; padding: 14px 16px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div
                            style="font-size: 11px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                            Subscription timeline</div>
                        <div style="font-size: 13px; color: #1f2937; font-weight: 600; line-height: 1.5;" id="detailJourneyLine">
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 6px; line-height: 1.45;" id="detailJourneyCaption"></div>
                    </div>
                </div>

                <!-- Communication Section -->
                <div
                    style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h6
                        style="color: #667eea; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center;">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Communication Status
                    </h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                <i class="fa-solid fa-envelope" style="margin-right: 4px;"></i> Email Send
                            </div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailEmailSend"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                <i class="fa-brands fa-whatsapp" style="margin-right: 4px;"></i> WhatsApp Send
                            </div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailWhatsAppSend">
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                <i class="fa-brands fa-whatsapp" style="margin-right: 4px;"></i> Feedback WhatsApp
                            </div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFeedbackWa"></div>
                        </div>
                    </div>
                    {{-- <div style="margin-top: 16px; padding: 14px 16px; border-radius: 12px; background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div
                            style="font-size: 11px; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                            Where template counts are stored</div>
                        <div style="font-size: 14px; color: #14532d; font-weight: 600;" id="detailCommRef"></div>
                        <div style="font-size: 13px; color: #365314; margin-top: 8px; line-height: 1.5;" id="detailCommBlurb"></div>
                    </div> --}}
                    <div style="margin-top: 14px; padding: 14px 16px; border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0;">
                        <div
                            style="font-size: 11px; color: #047857; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;">
                            WhatsApp feedback request (automation)</div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; font-size: 13px; color: #1f2937;">
                            <div><span style="display:block;font-size:10px;color:#6b7280;font-weight:700;text-transform:uppercase;">Ref</span><span id="detailFeedbackWpRef">—</span></div>
                            <div><span style="display:block;font-size:10px;color:#6b7280;font-weight:700;text-transform:uppercase;">Workflow status</span><span id="detailFeedbackWpStatusRaw">—</span></div>
                            <div><span style="display:block;font-size:10px;color:#6b7280;font-weight:700;text-transform:uppercase;">Link expires</span><span id="detailFeedbackWpExpires">—</span></div>
                            <div><span style="display:block;font-size:10px;color:#6b7280;font-weight:700;text-transform:uppercase;">Response submitted</span><span id="detailFeedbackWpSubmitted">—</span></div>
                        </div>
                        <div style="margin-top: 12px; font-size: 12px; color: #4b5563; line-height: 1.5; white-space: pre-wrap;" id="detailFeedbackPreview"></div>
                    </div>
                </div>

                <!-- Feedback Section -->
                <div
                    style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h6
                        style="color: #667eea; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center;">
                        <i class="fa-solid fa-star" style="margin-right: 8px;"></i> Feedback Details
                    </h6>
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Status</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFeedbackStatus">
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Rating</div>
                            <div style="font-size: 14px; color: #f59e0b; font-weight: 600;" id="detailFeedbackRating">
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Sent On</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFeedbackSentOn">
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <div
                            style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                            Comment</div>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 4px solid #10b981; font-size: 13px; color: #374151; line-height: 1.6; white-space: pre-wrap; min-height: 50px;"
                            id="detailFeedbackComment"></div>
                    </div>
                    <div>
                        <div
                            style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                            Suggestions</div>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 4px solid #3b82f6; font-size: 13px; color: #374151; line-height: 1.6; white-space: pre-wrap; min-height: 50px;"
                            id="detailFeedbackSuggestions"></div>
                    </div>
                </div>
                    <!-- Follow-up Section -->
                <div
                    style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h6
                        style="color: #667eea; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center;">
                        <i class="fa-solid fa-phone" style="margin-right: 8px;"></i> Follow-up Information
                    </h6>
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Follow-up Call</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFollowupCall">
                            </div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Follow By</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFollowupBy"></div>
                        </div>
                        <div>
                            <div
                                style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">
                                Follow-up Label</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;" id="detailFollowupLabel">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div
                            style="font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">
                            Follow-up Note</div>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; border-left: 4px solid #667eea; font-size: 13px; color: #374151; line-height: 1.6; white-space: pre-wrap;"
                            id="detailFollowupNote"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer"
                style="border-top: 1px solid #e5e7eb; padding: 20px 30px; background: #f8f9fa; border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                    style="border-radius: 8px; padding: 10px 24px; font-weight: 600;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Followup Info Modal (matching Recent Expire styling) -->
<div class="modal fade" id="followupInfoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 450px;">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 20px 25px; border-radius: 16px 16px 0 0;">
                <div style="display: flex; align-items: center; width: 100%;">
                    <div
                        style="background: rgba(255,255,255,0.2); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fa-solid fa-circle-info" style="font-size: 20px; color: #fff;"></i>
                    </div>
                    <h5 class="modal-title" style="color: #ffffff; font-weight: 700; font-size: 18px; margin: 0;">
                        Follow Up Details
                    </h5>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal"
                    style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 24px; font-weight: 300; margin: 0; padding: 0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <i class="fa-solid fa-tag" style="color: #667eea; margin-right: 10px; font-size: 16px;"></i>
                        <strong
                            style="color: #495057; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Label</strong>
                    </div>
                    <div id="infoLabel"
                        style="background: #f8f9fa; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #667eea; font-size: 14px; color: #212529; font-weight: 500;">
                        -
                    </div>
                </div>
                <div>
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <i class="fa-solid fa-comment-dots"
                            style="color: #764ba2; margin-right: 10px; font-size: 16px;"></i>
                        <strong
                            style="color: #495057; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Note</strong>
                    </div>
                    <div id="infoNote"
                        style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #764ba2; font-size: 14px; color: #495057; line-height: 1.6; min-height: 60px; white-space: pre-wrap;">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer"
                style="border-top: 1px solid #e9ecef; padding: 15px 25px; background: #f8f9fa; border-radius: 0 0 16px 16px; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-primary" id="editFollowupBtn"
                    style="border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="fa-solid fa-edit"></i> Edit
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    style="border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="followupEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="icon-copy dw dw-phone-call"></i> Edit Follow-up: <span id="editUserName"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="followupForm">
                    <input type="hidden" id="leadId" name="id">
                    <input type="hidden" id="followupTrack" name="followup_track" value="">
                    <input type="hidden" name="followup_call" value="1">

                    <div class="form-group">
                        <label class="form-label" id="followupLabelFieldLabel">Follow-up label</label>
                        <select name="followup_label" id="followupLabel" class="form-control">
                            <option value="">Select label</option>
                        </select>
                        <small class="text-muted d-block mt-1" id="followupLabelHint" style="display: none;"></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Follow-up Note</label>
                        <textarea name="followup_note" id="followupNote" class="form-control" rows="4"
                            placeholder="Add notes about the call..."></textarea>
                        <small class="text-muted d-block mt-2" id="followupHelperText">Capture what the user thinks
                            about the plan, usability and renewal intent.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveFollowup()">
                    <i class="icon-copy dw dw-checked"></i> Save Follow-up
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="feedbackViewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="icon-copy dw dw-chat-3"></i> Feedback Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label style="font-weight: 700;">User</label>
                    <p id="feedbackUserName" style="margin-bottom: 0;"></p>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Submitted At</label>
                    <p id="feedbackSubmittedAt" style="margin-bottom: 0;"></p>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Rating</label>
                    <div id="feedbackRatingStars"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Comment</label>
                    <div id="feedbackComment" class="form-control" style="height: auto; min-height: 80px;"></div>
                </div>
                <div class="form-group">
                    <label style="font-weight: 700;">Suggestions</label>
                    <div id="feedbackSuggestions" class="form-control" style="height: auto; min-height: 80px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
<script src="{{ asset('assets/plugins/switchery/switchery.min.js') }}"></script>

<script>
    const UNIFIED_LABELS_SUB = @json($followupLabels);
    const UNIFIED_LABELS_FB = @json($feedbackFollowupLabels);

    function unifiedFillFollowupLabelSelect(track, selectedValue) {
        const map = (track === 'feedback') ? UNIFIED_LABELS_FB : UNIFIED_LABELS_SUB;
        const $sel = $('#followupLabel');
        $sel.empty();
        const placeholder = track === 'feedback'
            ? 'Select WhatsApp feedback outcome'
            : 'Select follow-up label';
        $sel.append($('<option></option>').attr('value', '').text(placeholder));
        $.each(map, function (key, label) {
            $sel.append($('<option></option>').attr('value', key).text(label));
        });
        if (selectedValue) {
            $sel.val(selectedValue);
        }
        if (track === 'feedback') {
            $('#followupLabelFieldLabel').text('WhatsApp feedback follow-up label');
            $('#followupLabelHint').text('Only for Fb F/U (WhatsApp feedback campaign).').show();
        } else {
            $('#followupLabelFieldLabel').text('Follow-up label');
            $('#followupLabelHint').hide();
        }
    }

    // Ensure modal close works regardless of Bootstrap version
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function () {
        $(this).closest('.modal').modal('hide');
    });

    $(document).ready(function () {
        // Initialize Switchery for all followup switches
        document.querySelectorAll('.followup-switch').forEach(function (elem) {
            if (!elem.hasAttribute('data-switchery')) {
                new Switchery(elem, {
                    color: '#28a745',
                    size: 'small'
                });
            }
        });
    });

    $(document).on('click', '.ul-expand-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $btn = $(this);
        const $main = $btn.closest('tr.ul-lead-main-row');
        const $detail = $main.next('tr.ul-lead-expand-row');
        const isOpen = $btn.attr('aria-expanded') === 'true';
        $('.ul-lead-expand-row').not($detail).hide();
        $('.ul-expand-btn').not($btn).attr('aria-expanded', 'false');
        if (isOpen) {
            $detail.hide();
            $btn.attr('aria-expanded', 'false');
        } else {
            $detail.show();
            $btn.attr('aria-expanded', 'true');
        }
    });

    // Checkbox change - Use event delegation (matching WP Feedback)
    $(document).off("change", ".followup-switch").on("change", ".followup-switch", function () {
        let leadId = $(this).data("id");
        let isChecked = $(this).is(":checked");
        let $checkbox = $(this);
        let track = $(this).data('track') || 'subscription';

        if (isChecked) {
            // Store checkbox reference for modal cancel
            $("#followupEditModal").data('checkbox', $checkbox);

            const $row = $checkbox.closest('tr');
            const userName = $row.find('.user-name').text();
            const planTitle = $checkbox.data('plan') || 'selected plan';
            const followupHelpers = {
                subscription: `Capture subscription experience, plan satisfaction and renewal intent (${planTitle}).`,
                feedback: `Follow up on WhatsApp feedback: clarify satisfaction, issues, and next steps (${planTitle}).`,
                expiry: `Capture why the plan expired and interest in renewal or another package (${planTitle}).`
            };

            $('#leadId').val(leadId);
            $('#followupTrack').val(track);
            $('#editUserName').text(userName);
            unifiedFillFollowupLabelSelect(track, '');
            $('#followupNote').val('');
            $('#followupHelperText').text(followupHelpers[track] || followupHelpers.subscription);
            $('#followupEditModal').modal('show');
        } else {
            if (confirm("Are you sure you want to uncheck this follow-up?")) {
                $.ajax({
                    url: '{{ route("unified_leads.followup_update") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: leadId,
                        track: track,
                        followup_call: 0
                    },
                    success: function (response) {
                        if (response.success) {
                            // Update UI immediately without page reload
                            const $row = $checkbox.closest('tr');

                            // Find the correct followup column based on track
                            let columnIndex;
                            if (track === 'subscription') {
                                columnIndex = 9;
                            } else if (track === 'feedback') {
                                columnIndex = 10;
                            } else if (track === 'expiry') {
                                columnIndex = 11;
                            }

                            if (columnIndex) {
                                const $followupCell = $row.find('td').eq(columnIndex);
                                $followupCell.find('.info-icon').remove();
                                // Be more specific - only update the direct child div
                                $followupCell.find('> div[style*="font-size: 11px"]').html('<strong>By:</strong> N/A');
                            }
                        } else {
                            alert("Error: " + response.message);
                            $checkbox.prop("checked", true);

                            // Reinitialize Switchery
                            if (typeof Switchery !== 'undefined') {
                                try {
                                    const switcheryInstance = $checkbox[0].switchery;
                                    if (switcheryInstance) {
                                        switcheryInstance.destroy();
                                        new Switchery($checkbox[0], { color: '#28a745', size: 'small' });
                                    }
                                } catch (e) {
                                    console.warn('Switchery update failed:', e);
                                }
                            }
                        }
                    },
                    error: function (err) {
                        console.error('❌ Error unchecking followup:', err);
                        alert("Error: " + (err.responseJSON?.message || err.responseText || 'Unknown error'));
                        $checkbox.prop("checked", true);

                        // Reinitialize Switchery
                        if (typeof Switchery !== 'undefined') {
                            try {
                                const switcheryInstance = $checkbox[0].switchery;
                                if (switcheryInstance) {
                                    switcheryInstance.destroy();
                                    new Switchery($checkbox[0], { color: '#28a745', size: 'small' });
                                }
                            } catch (e) {
                                console.warn('Switchery update failed:', e);
                            }
                        }
                    }
                });
            } else {
                $checkbox.prop("checked", true);

                // Reinitialize Switchery
                if (typeof Switchery !== 'undefined') {
                    try {
                        const switcheryInstance = $checkbox[0].switchery;
                        if (switcheryInstance) {
                            switcheryInstance.destroy();
                            new Switchery($checkbox[0], { color: '#28a745', size: 'small' });
                        }
                    } catch (e) {
                        console.warn('Switchery update failed:', e);
                    }
                }
            }
        }
    });

    // Handle modal close without saving - uncheck the checkbox (matching WP Feedback)
    $('#followupEditModal').on('hidden.bs.modal', function (e) {
        const $checkbox = $(this).data('checkbox');
        const wasSubmitted = $(this).data('submitted');

        // If modal was closed without submitting, uncheck the checkbox
        if ($checkbox && !wasSubmitted) {
            $checkbox.prop('checked', false);

            // Reinitialize Switchery if it exists
            if (typeof Switchery !== 'undefined') {
                try {
                    const switcheryInstance = $checkbox[0].switchery;
                    if (switcheryInstance) {
                        switcheryInstance.destroy();
                        new Switchery($checkbox[0], { color: '#28a745', size: 'small' });
                    }
                } catch (e) {
                    console.warn('Switchery update failed:', e);
                }
            }
        }

        // Reset submitted flag
        $(this).data('submitted', false);
        $(this).data('checkbox', null);
    });

    // Info Icon Click - Show modal with followup details (matching WP Feedback)
    $(document).off("click", ".info-icon").on("click", ".info-icon", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let leadId = $(this).attr("data-id");
        let note = $(this).attr("data-note");
        let label = $(this).attr("data-label");
        let labelDisplay = $(this).attr("data-label-display");
        let track = $(this).attr("data-track") || 'subscription';
        let userName = $(this).attr("data-name");
        let planTitle = $(this).attr("data-plan") || 'selected plan';

        // Store data for edit button
        $("#followupInfoModal").data("lead-id", leadId);
        $("#followupInfoModal").data("note", note);
        $("#followupInfoModal").data("label", label);
        $("#followupInfoModal").data("followup-track", track);
        $("#followupInfoModal").data("user-name", userName);
        $("#followupInfoModal").data("plan-title", planTitle);

        // Populate modal (using correct IDs: infoLabel and infoNote)
        $("#infoLabel").text(labelDisplay || '-');
        $("#infoNote").text(note || '-');

        // Show modal
        $("#followupInfoModal").modal("show");
    });

    // Edit button in info modal - Open edit form (matching WP Feedback)
    $(document).on("click", "#editFollowupBtn", function () {
        // Get stored data
        let leadId = $("#followupInfoModal").data("lead-id");
        let note = $("#followupInfoModal").data("note");
        let label = $("#followupInfoModal").data("label");
        let track = $("#followupInfoModal").data("followup-track") || 'subscription';
        let userName = $("#followupInfoModal").data("user-name");
        let planTitle = $("#followupInfoModal").data("plan-title") || 'selected plan';

        // Close info modal
        $("#followupInfoModal").modal("hide");

        // Wait for info modal to close, then open edit modal
        setTimeout(function () {
            const followupHelpers = {
                subscription: `Capture subscription experience, plan satisfaction and renewal intent (${planTitle}).`,
                feedback: `Follow up on WhatsApp feedback: clarify satisfaction, issues, and next steps (${planTitle}).`,
                expiry: `Capture why the plan expired and interest in renewal or another package (${planTitle}).`
            };

            // Populate edit form
            $("#leadId").val(leadId);
            $("#followupTrack").val(track);
            $("#editUserName").text(userName);
            unifiedFillFollowupLabelSelect(track, label || '');
            $("#followupNote").val(note && note !== '-' ? note : '');
            $("#followupHelperText").text(followupHelpers[track] || followupHelpers.subscription);

            // Open edit modal
            $("#followupEditModal").modal("show");
        }, 300);
    });

    function saveFollowup() {
        const formData = $('#followupForm').serialize();
        const leadId = $("#leadId").val();
        const track = $("#followupTrack").val();
        const followupNote = $("#followupNote").val();
        const followupLabel = $("#followupLabel").val();
        const followupLabelDisplay = $("#followupLabel option:selected").text().trim();

        $.ajax({
            url: '{{ route("unified_leads.followup_update") }}',
            method: 'POST',
            data: formData + '&_token={{ csrf_token() }}',
            success: function (response) {
                if (response.success) {
                    // Mark as submitted so modal close handler doesn't uncheck
                    $("#followupEditModal").data('submitted', true);

                    $("#followupEditModal").modal("hide");

                    // Update UI immediately without page reload (matching WP Feedback)
                    const $checkbox = $('input.followup-switch[data-id="' + leadId + '"][data-track="' + track + '"]');

                    if ($checkbox.length > 0) {
                        const $row = $checkbox.closest('tr');

                        // Find the correct followup column based on track
                        let columnIndex;
                        if (track === 'subscription') {
                            columnIndex = 7; // Subscription Followup column (after Account Creation Email/WhatsApp)
                        } else if (track === 'feedback') {
                            columnIndex = 9; // Feedback Followup column
                        } else if (track === 'expiry') {
                            columnIndex = 11; // Expire User Followup column
                        }

                        if (columnIndex) {
                            const $followupCell = $row.find('td').eq(columnIndex);
                            let $infoIcon = $followupCell.find('.info-icon');

                            if ($infoIcon.length > 0) {
                                // Info icon exists - update it
                                $infoIcon.attr('data-note', followupNote);
                                $infoIcon.attr('data-label', followupLabel);
                                $infoIcon.attr('data-label-display', followupLabelDisplay);
                            } else {
                                // Info icon doesn't exist - create it
                                const infoIconHtml = `
                                    <i class="fa-solid fa-circle-info info-icon"
                                       data-id="${leadId}"
                                       data-track="${track}"
                                       data-name="${$("#editUserName").text()}"
                                       data-plan="${$checkbox.data('plan') || 'selected plan'}"
                                       data-note="${followupNote}"
                                       data-label="${followupLabel}"
                                       data-label-display="${followupLabelDisplay}"
                                       title="Follow-up details"></i>
                                `;
                                $followupCell.find('.followup-box').append(infoIconHtml);
                            }

                            // Update Follow By name - be more specific to avoid updating wrong column
                            $followupCell.find('> div[style*="font-size: 11px"]').html('<strong>By:</strong> ' + (response.emp_name || 'N/A'));

                            // Highlight the row to show it was updated
                            $row.css('background-color', '#d4edda');
                            setTimeout(function () {
                                $row.css('background-color', '');
                            }, 2000);
                        }
                    }
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (err) {
                console.error('❌ Error saving followup:', err);
                alert("Error: " + (err.responseJSON?.message || err.responseText || 'Unknown error'));
            }
        });
    }

    $(document).on('click', '.view-feedback-btn', function () {
        const rating = parseInt($(this).data('rating')) || 0;
        const comment = $(this).data('comment') || '-';
        const suggestions = $(this).data('suggestions') || '-';
        const submittedAt = $(this).data('submitted-at') || '-';
        const userName = $(this).data('name') || '-';

        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<span style="font-size: 20px; color: ${i <= rating ? '#f59e0b' : '#d1d5db'};">★</span>`;
        }

        $('#feedbackUserName').text(userName);
        $('#feedbackSubmittedAt').text(submittedAt);
        $('#feedbackRatingStars').html(starsHtml + ` <span style="font-weight: 600;">(${rating}/5)</span>`);
        $('#feedbackComment').text(comment);
        $('#feedbackSuggestions').text(suggestions);
        $('#feedbackViewModal').modal('show');
    });

    $(document).on('click', '.lead-detail-btn', function () {
        const data = $(this).data();

        $('#detailName').text(data.name || '-');
        $('#detailUserLink').text(data.name || '-').attr('href', '{{ url("user_detail") }}/' + data.email);
        $('#detailHeroEmail').text(data.email || '-');
        $('#detailHeroContact').text(data.contact || '-');
        $('#detailHeroSource').text(data.source || '-');
        $('#detailHeroSubscription')
            .text(data.subscription || '-')
            .removeClass('success danger warning')
            .addClass((data.subscription || '').toLowerCase() === 'expired' ? 'danger' : 'success');
        $('#detailHeroOrderStatus')
            .text(data.orderStatus || '-')
            .removeClass('success danger warning')
            .addClass((data.orderStatus || '').toLowerCase() === 'expired' ? 'danger' : 'warning');
        $('#detailEmail').text(data.email || '-');
        $('#detailContact').text(data.contact || '-');
        $('#detailSource').text(data.source || '-');
        $('#detailFromWhere').text(data.fromWhere || '-');
        $('#detailPlan').text(data.plan || '-');
        $('#detailAmount').text(data.amount || '-');
        $('#detailSubscription').text(data.subscription || '-');
        $('#detailOrderStatus').text(data.orderStatus || '-');
        $('#detailUsage').text(data.usage || '-');
        $('#detailFocus').text(data.followupFocus || '-');
        $('#detailEmailSend').text(data.emailSend || '-');
        $('#detailWhatsAppSend').text(data.whatsappSend || '-');
        $('#detailFeedbackWa').text(data.feedbackWa || '-');
        $('#detailCommRef').text(data.commRef || '—');
        $('#detailCommBlurb').text(data.commBlurb || '—');
        $('#detailFeedbackWpRef').text(data.feedbackWpRef || '—');
        $('#detailFeedbackWpStatusRaw').text(data.feedbackWpStatusRaw || '—');
        $('#detailFeedbackWpExpires').text(data.feedbackWpExpires || '—');
        $('#detailFeedbackWpSubmitted').text(data.feedbackWpSubmitted || '—');
        $('#detailFeedbackPreview').text(data.feedbackPreview ? data.feedbackPreview : '');
        const callParts = [];
        if (data.followupCallSub && data.followupCallSub !== '—') {
            callParts.push('Sub: ' + data.followupCallSub);
        }
        if (data.followupCallFb && data.followupCallFb !== '—') {
            callParts.push('FB: ' + data.followupCallFb);
        }
        if (data.followupCallExp && data.followupCallExp !== '—') {
            callParts.push('Exp: ' + data.followupCallExp);
        }
        $('#detailFollowupCall').text(callParts.length ? callParts.join(' · ') : (data.followupCall || '-'));

        const byParts = [];
        if (data.followupBySub && data.followupBySub !== '—') {
            byParts.push('Sub: ' + data.followupBySub);
        }
        if (data.followupByFb && data.followupByFb !== '—') {
            byParts.push('FB: ' + data.followupByFb);
        }
        if (data.followupByExp && data.followupByExp !== '—') {
            byParts.push('Exp: ' + data.followupByExp);
        }
        $('#detailFollowupBy').text(byParts.length ? byParts.join(' · ') : (data.followupBy || '-'));

        const labelParts = [];
        if (data.followupLabelSub) {
            labelParts.push('Sub: ' + (data.followupLabelSubDisplay || data.followupLabelSub));
        }
        if (data.followupLabelFb) {
            labelParts.push('FB: ' + (data.followupLabelFbDisplay || data.followupLabelFb));
        }
        if (data.followupLabelExp) {
            labelParts.push('Exp: ' + (data.followupLabelExpDisplay || data.followupLabelExp));
        }
        $('#detailFollowupLabel').text(labelParts.length ? labelParts.join(' | ') : (data.followupLabel || '-'));

        const noteParts = [];
        if (data.followupNoteSub) {
            noteParts.push('Sub: ' + data.followupNoteSub);
        }
        if (data.followupNoteFb) {
            noteParts.push('FB: ' + data.followupNoteFb);
        }
        if (data.followupNoteExp) {
            noteParts.push('Exp: ' + data.followupNoteExp);
        }
        $('#detailFollowupNote').text(noteParts.length ? noteParts.join('\n\n') : (data.followupNote || '-'));
        $('#detailFeedbackStatus').text(data.feedbackStatus || '-');
        const ratingVal = parseInt(data.feedbackRating);
        if (!isNaN(ratingVal) && ratingVal > 0) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<span style="color: ${i <= ratingVal ? '#f59e0b' : '#cbd5e1'}; font-size: 18px;">★</span>`;
            }
            $('#detailFeedbackRating').html(stars + ' (' + ratingVal + '/5)');
        } else {
            $('#detailFeedbackRating').text(data.feedbackRating || '-');
        }
        $('#detailFeedbackSentOn').text(data.feedbackSentOn || '-');
        $('#detailFeedbackComment').text(data.feedbackComment || '-');
        $('#detailFeedbackSuggestions').text(data.feedbackSuggestions || '-');
        $('#detailExpiryDate').text(data.expiryDate || '-');
        $('#detailExpiryMeta').text(data.expiryMeta || '-');
        const jStart = (data.journeyStart || '').trim();
        const jEnd = (data.journeyEnd || '').trim();
        $('#detailJourneyLine').text(
            (jStart || jEnd) ? (jStart || '—') + ' → ' + (jEnd || '—') : '—'
        );
        $('#detailJourneyCaption').text((data.journeyCaption || '').trim() || '—');
        $('#leadDetailModal').modal('show');
    });

    (function () {
        var wrap = document.getElementById('ulFiltersWrap');
        var btn = document.getElementById('ulFilterToggle');
        if (!wrap || !btn) {
            return;
        }
        var textEl = btn.querySelector('.ul-filter-toggle-text');
        btn.addEventListener('click', function () {
            var open = wrap.classList.toggle('is-filters-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (textEl) {
                textEl.textContent = open ? 'Hide filters' : 'Show filters';
            }
        });
    })();

    $('#exportLeadsBtn').on('click', function () {
        const rows = [];
        const headers = [];

        $('.leads-table thead th').each(function () {
            headers.push($(this).text().trim());
        });
        rows.push(headers);

        $('.leads-table tbody tr').each(function () {
            const row = [];
            $(this).find('td').each(function () {
                row.push($(this).text().replace(/\s+/g, ' ').trim());
            });
            if (row.length) {
                rows.push(row);
            }
        });

        const csvContent = rows
            .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'unified-leads.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });
</script>