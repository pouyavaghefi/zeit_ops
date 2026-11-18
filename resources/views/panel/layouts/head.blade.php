<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Area @yield('page-title')</title>
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/panel/dashboard.css">
<link rel="stylesheet" href="/panel/own.css">
<link rel="icon" type="image/png" href="/img/icon.png">
@stack('css-styles')
<style>

    /* =========================================
       ZEITOPS PROFILE DROPDOWN
    ========================================= */

    .zeitops-profile {
        position: relative;
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
        z-index: 1050;
        font-family: inherit;
    }

    /* =========================================
       AVATAR TOGGLE
    ========================================= */

    .zp-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 4px 7px 4px 4px;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 40px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .zp-toggle:hover,
    .zp-toggle[aria-expanded="true"] {
        background: #f4f7ff;
        border-color: #b9caff;
    }

    .zp-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #008cff, #3552e8);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 7px rgba(0, 102, 255, 0.2);
    }

    .zp-chevron {
        font-size: 10px;
        color: #64748b;
        transition: transform 0.2s ease;
    }

    .zp-toggle[aria-expanded="true"] .zp-chevron {
        transform: rotate(180deg);
    }

    /* =========================================
       DROPDOWN CONTAINER
    ========================================= */

    .zeitops-profile .zp-dropdown {
        position: absolute;
        width: 365px;
        max-width: calc(100vw - 24px);
        padding: 0;
        margin-top: 10px !important;
        background: #ffffff;
        border: 1px solid #e5eaf3;
        border-radius: 16px;
        box-shadow: 0 16px 48px rgba(27, 48, 91, 0.16);
        overflow: hidden;
        color: #1e293b;
        z-index: 1060;
    }

    /* =========================================
       ACCOUNT HEADER
    ========================================= */

    .zp-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 20px 18px 17px;
    }

    .zp-header-avatar {
        width: 46px;
        height: 46px;
        flex-shrink: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 13px;
        background: linear-gradient(135deg, #327cf5, #5154e9);
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
    }

    .zp-header-info {
        flex: 1;
        min-width: 0;
    }

    .zp-header-name {
        color: #17243b;
        font-weight: 700;
        font-size: 14px;
        line-height: 1.45;
    }

    .zp-header-email {
        margin-top: 2px;
        color: #8491a7;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .zp-verified {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 8px;
        padding: 4px 8px;
        border-radius: 6px;
        color: #15803d;
        background: #ecfdf3;
        font-size: 10px;
        font-weight: 600;
    }

    .zp-unverified {
        color: #b45309;
        background: #fffbeb;
    }

    /* =========================================
       DIVIDER
    ========================================= */

    .zp-divider {
        height: 1px;
        width: 100%;
        background: #edf0f6;
    }

    /* =========================================
       WORKSPACES
    ========================================= */

    .zp-workspaces {
        padding: 14px 11px 13px;
    }

    .zp-section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 9px 13px;
        color: #6d7b93;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
    }

    .zp-workspace-count {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-width: 21px;
        height: 21px;
        padding: 0 5px;
        background: #edf2ff;
        color: #5275dd;
        border-radius: 7px;
        font-size: 11px;
        letter-spacing: 0;
    }

    .zp-workspace-group-label {
        margin: 0 9px 8px;
        font-size: 10px;
        color: #a0aabd;
        font-weight: 700;
        letter-spacing: 0.6px;
    }

    .zp-team-label {
        margin-top: 17px;
    }

    /* =========================================
       WORKSPACE ITEM
    ========================================= */

    .zp-workspace {
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        padding: 11px;
        border: 1px solid transparent;
        border-radius: 10px;
        background: transparent;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
        color: #1e293b;
    }

    .zp-workspace + .zp-workspace {
        margin-top: 4px;
    }

    .zp-workspace:hover {
        background: #f5f7fe;
    }

    .zp-workspace.active {
        background: #eef3ff;
        border-color: #cbd9ff;
    }

    .zp-workspace-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 39px;
        height: 39px;
        flex-shrink: 0;
        border-radius: 10px;
        font-size: 15px;
    }

    .zp-personal {
        background: #e5edff;
        color: #4467e9;
    }

    .zp-team {
        background: #f0eaff;
        color: #7854da;
    }

    .zp-workspace-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .zp-workspace-details strong {
        color: #25334c;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .zp-workspace-details small {
        color: #8491a8;
        font-size: 10px;
    }

    .zp-dot {
        padding: 0 3px;
    }

    .zp-workspace-check {
        color: #4567ed;
        font-size: 16px;
        display: none;
    }

    .zp-workspace.active .zp-workspace-check {
        display: flex;
    }

    /* =========================================
       ACCOUNT MENU
    ========================================= */

    .zp-menu {
        padding: 8px 11px;
    }

    .zp-menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 11px 12px;
        border-radius: 9px;
        text-decoration: none;
        color: #475569;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.18s ease;
    }

    .zp-menu-link i {
        width: 17px;
        text-align: center;
        font-size: 13px;
        color: #8593ad;
    }

    .zp-menu-link:hover {
        background: #f3f6ff;
        color: #315bea;
    }

    .zp-menu-link:hover i {
        color: #315bea;
    }

    /* =========================================
       CREATE WORKSPACE
    ========================================= */

    .zp-create-wrapper {
        padding: 4px 12px 14px;
    }

    .zp-create-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        padding: 11px 14px;
        border-radius: 9px;
        border: 1px solid #cbd9ff;
        background: #edf3ff;
        color: #345ce9;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .zp-create-btn:hover {
        background: #dfe9ff;
        border-color: #9db5ff;
    }

    /* =========================================
       FOOTER
    ========================================= */

    .zp-footer {
        padding: 9px 11px;
    }

    .zp-footer form {
        margin: 0;
    }

    .zp-logout {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 11px 12px;
        background: transparent;
        border: 0;
        border-radius: 9px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #e05262;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .zp-logout i {
        width: 17px;
        text-align: center;
    }

    .zp-logout:hover {
        background: #fff1f2;
        color: #dc3545;
    }

    /* =========================================
       RESPONSIVE
    ========================================= */

    @media (max-width: 576px) {
        .zeitops-profile .zp-dropdown {
            width: min(350px, calc(100vw - 24px));
        }

        .zp-toggle {
            gap: 5px;
        }

        .zp-avatar {
            width: 36px;
            height: 36px;
        }
    }


    /* =========================================
       TEAM WORKSPACE QUOTA
    ========================================= */

    .zeitops-profile .zp-team-quota {
        margin: 14px 12px 12px;
        padding: 14px;
        background: #f8faff;
        border: 1px solid #dce4f3;
        border-radius: 12px;
        box-sizing: border-box;
    }

    .zeitops-profile .zp-team-quota-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 13px;
    }

    .zeitops-profile .zp-team-quota-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        background: #e9eaff;
        border-radius: 10px;
        color: #777cf0;
        font-size: 15px;
    }

    .zeitops-profile .zp-team-quota-title {
        flex: 1;
        min-width: 0;
    }

    .zeitops-profile .zp-team-quota-title strong {
        display: block;
        color: #26344d;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .zeitops-profile .zp-team-quota-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        flex-shrink: 0;
        background: #e9eef9;
        border: 1px solid #d7e0f1;
        border-radius: 7px;
        color: #596984;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Progress Bar */

    .zeitops-profile .zp-team-quota-progress {
        width: 100%;
        height: 7px;
        background: #e1e7f1;
        border-radius: 20px;
        overflow: hidden;
    }

    .zeitops-profile .zp-team-quota-progress-fill {
        height: 100%;
        background: linear-gradient(
            90deg,
            #6578f6,
            #9395ff
        );
        border-radius: inherit;
        transition: width 0.3s ease;
    }

    /* Quota Footer */

    .zeitops-profile .zp-team-quota-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 13px;
    }

    .zeitops-profile .zp-team-quota-description {
        flex: 1;
        min-width: 0;
        color: #78869c;
        font-size: 11px;
        font-weight: 400;
        line-height: 1.6;
    }

    .zeitops-profile .zp-team-quota-remaining {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 6px 9px;
        background: #e9edff;
        color: #6269cc;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* =========================================
       TEAM WORKSPACE ACTIONS
    ========================================= */

    .zeitops-profile .zp-team-actions {
        display: flex;
        flex-direction: column;
        gap: 9px;
        padding: 0 12px 14px;
    }

    /* Manage Workspaces */

    .zeitops-profile .zp-manage-workspaces {
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        padding: 11px 12px;
        background: transparent;
        border: none;
        border-radius: 9px;
        color: #475569;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .zeitops-profile .zp-manage-workspaces i {
        width: 18px;
        color: #8492a9;
        font-size: 14px;
        text-align: center;
    }

    .zeitops-profile .zp-manage-workspaces:hover {
        background: #f1f5ff;
        color: #4263ed;
    }

    .zeitops-profile .zp-manage-workspaces:hover i {
        color: #4263ed;
    }

    /* Create New Workspace */

    .zeitops-profile .zp-create-workspace {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 12px 14px;
        background: #7c86f5;
        border: 1px solid #7c86f5;
        border-radius: 9px;
        color: #ffffff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
        cursor: pointer;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .zeitops-profile .zp-create-workspace i {
        font-size: 13px;
        color: inherit;
    }

    .zeitops-profile .zp-create-workspace:hover {
        background: #6673ea;
        border-color: #6673ea;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(102, 115, 234, 0.18);
    }

    .zeitops-profile .zp-create-workspace:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        box-shadow: none;
    }

    /* =========================================
       DROPDOWN CONSISTENCY
    ========================================= */

    .zeitops-profile .zp-dropdown {
        width: 365px;
        max-width: calc(100vw - 24px);
        box-sizing: border-box;
        overflow: hidden;
    }

    .zeitops-profile .zp-dropdown button {
        font-family: inherit;
    }

    .zeitops-profile .zp-dropdown,
    .zeitops-profile .zp-dropdown * {
        box-sizing: border-box;
    }

    @media (max-width: 576px) {
        .zeitops-profile .zp-team-quota-footer {
            gap: 8px;
        }

        .zeitops-profile .zp-team-quota-description {
            font-size: 10px;
        }

        .zeitops-profile .zp-team-quota-remaining {
            font-size: 10px;
        }
    }


</style>

