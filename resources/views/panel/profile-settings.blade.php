
@extends('panel.layouts.master')

@push('css-styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/profile-settings.css') }}"
    >
@endpush

@section('wrapper')
    @php
        $user = auth()->user();

        $initials = collect(
            preg_split('/\s+/', trim($user->name ?? 'User'))
        )
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'U';
    @endphp

    <div class="zs-settings">

        {{-- =====================================
             PAGE HEADER
        ===================================== --}}

        <div class="zs-page-header">
            <div>
                <div class="zs-breadcrumb">
                    <a href="{{ url('/panel/dashboard') }}">
                        Dashboard
                    </a>

                    <i class="fas fa-chevron-right"></i>

                    <span>Profile Settings</span>
                </div>

                <h1>Profile Settings</h1>

                <p>
                    Manage your personal information,
                    security and preferences.
                </p>
            </div>
        </div>

        {{-- =====================================
             PROFILE OVERVIEW
        ===================================== --}}

        <div class="zs-profile-banner">

            <div class="zs-profile-avatar">
                {{ $initials }}
            </div>

            <div class="zs-profile-identity">

                <h2>{{ $user->name }}</h2>

                <p>{{ $user->email }}</p>

                <span class="zs-account-status">

                    <i class="fas {{ $user->email_verified_at
                        ? 'fa-check-circle'
                        : 'fa-exclamation-circle' }}">
                    </i>

                    {{ $user->email_verified_at
                        ? 'Verified Account'
                        : 'Email Not Verified' }}

                </span>

            </div>
        </div>

        {{-- =====================================
             SETTINGS LAYOUT
        ===================================== --}}

        <div class="zs-settings-layout">

            {{-- =====================================
                 SETTINGS NAVIGATION
            ===================================== --}}

            <aside class="zs-settings-sidebar">

                <div class="zs-sidebar-title">
                    ACCOUNT SETTINGS
                </div>

                <button
                    type="button"
                    class="zs-nav-item active"
                    data-zs-tab="personal"
                >
                    <i class="fas fa-user"></i>
                    Personal Information
                </button>

                <button
                    type="button"
                    class="zs-nav-item"
                    data-zs-tab="security"
                >
                    <i class="fas fa-shield-alt"></i>
                    Security
                </button>

                <button
                    type="button"
                    class="zs-nav-item"
                    data-zs-tab="preferences"
                >
                    <i class="fas fa-sliders-h"></i>
                    Preferences
                </button>

            </aside>

            {{-- =====================================
                 SETTINGS CONTENT
            ===================================== --}}

            <div class="zs-settings-content">

                {{-- =====================================
                     PERSONAL INFORMATION
                ===================================== --}}

                <section
                    class="zs-tab-panel active"
                    id="zs-personal"
                >

                    <div class="zs-card">

                        <div class="zs-card-header">

                            <div>
                                <h3>Personal Information</h3>

                                <p>
                                    Update your profile details
                                    and contact information.
                                </p>
                            </div>

                            <i class="fas fa-user-edit zs-card-icon"></i>

                        </div>

                        {{-- PROFILE UPDATE FORM --}}

                        <form
                            method="POST"
                            action="{{ route('panel.profile-settings.update') }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="zs-form-grid">

                                {{-- Full Name --}}

                                <div class="zs-form-group zs-full">

                                    <label for="zs-name">
                                        Full Name
                                    </label>

                                    <input
                                        id="zs-name"
                                        type="text"
                                        name="name"
                                        class="zs-input @error('name', 'profile') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}"
                                        autocomplete="name"
                                        maxlength="255"
                                        required
                                    >

                                    @error('name', 'profile')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                    @enderror

                                </div>

                                {{-- Email Address --}}

                                <div class="zs-form-group zs-full">

                                    <label for="zs-email">
                                        Email Address
                                    </label>

                                    <input
                                        id="zs-email"
                                        type="email"
                                        name="email"
                                        class="zs-input @error('email', 'profile') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}"
                                        autocomplete="email"
                                        maxlength="255"
                                        required
                                    >

                                    <small>
                                        Your email is used for account
                                        notifications and security updates.
                                    </small>

                                    @error('email', 'profile')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                    @enderror

                                </div>

                            </div>

                            {{-- FORM ACTIONS --}}

                            <div class="zs-form-footer">

                                <span>
                                    Your changes will be saved
                                    to your ZeitOps account.
                                </span>

                                <button
                                    type="submit"
                                    class="zs-primary-btn"
                                >
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>

                            </div>

                        </form>

                    </div>

                    {{-- =====================================
                         ACCOUNT INFORMATION
                    ===================================== --}}

                    <div class="zs-card">

                        <div class="zs-card-header">

                            <div>
                                <h3>Account Information</h3>

                                <p>
                                    Details about your ZeitOps account.
                                </p>
                            </div>

                            <i class="fas fa-id-card zs-card-icon"></i>

                        </div>

                        <div class="zs-info-row">

                            <span>Account Type</span>

                            <strong>Personal Account</strong>

                        </div>

                        <div class="zs-info-row">

                            <span>Email Verification</span>

                            <strong>
                                {{ $user->email_verified_at
                                    ? 'Verified'
                                    : 'Not Verified' }}
                            </strong>

                        </div>

                        <div class="zs-info-row">

                            <span>Member Since</span>

                            <strong>
                                {{ $user->created_at?->format('F d, Y') ?? 'N/A' }}
                            </strong>

                        </div>

                    </div>

                </section>

                {{-- =====================================
                     SECURITY
                ===================================== --}}

                <section
                    class="zs-tab-panel"
                    id="zs-security"
                >

                    {{-- =====================================
                         CHANGE PASSWORD
                    ===================================== --}}

                    <div class="zs-card">

                        <div class="zs-card-header">

                            <div>
                                <h3>Change Password</h3>

                                <p>
                                    Choose a strong password to
                                    protect your account.
                                </p>
                            </div>

                            <i class="fas fa-lock zs-card-icon"></i>

                        </div>

                        <form
                            method="POST"
                            action="{{ route('panel.profile-settings.password') }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="zs-form-grid">

                                {{-- Current Password --}}

                                <div class="zs-form-group zs-full">

                                    <label for="zs-current-password">
                                        Current Password
                                    </label>

                                    <input
                                        id="zs-current-password"
                                        type="password"
                                        name="current_password"
                                        class="zs-input @error('current_password', 'password') is-invalid @enderror"
                                        autocomplete="current-password"
                                        placeholder="Enter current password"
                                        required
                                    >

                                    @error('current_password', 'password')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                    @enderror

                                </div>

                                {{-- New Password --}}

                                <div class="zs-form-group">

                                    <label for="zs-new-password">
                                        New Password
                                    </label>

                                    <input
                                        id="zs-new-password"
                                        type="password"
                                        name="password"
                                        class="zs-input @error('password', 'password') is-invalid @enderror"
                                        autocomplete="new-password"
                                        placeholder="Enter new password"
                                        minlength="8"
                                        required
                                    >

                                    @error('password', 'password')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                    @enderror

                                </div>

                                {{-- Confirm Password --}}

                                <div class="zs-form-group">

                                    <label for="zs-confirm-password">
                                        Confirm New Password
                                    </label>

                                    <input
                                        id="zs-confirm-password"
                                        type="password"
                                        name="password_confirmation"
                                        class="zs-input"
                                        autocomplete="new-password"
                                        placeholder="Confirm new password"
                                        minlength="8"
                                        required
                                    >

                                </div>

                            </div>

                            {{-- FORM ACTIONS --}}

                            <div class="zs-form-footer">

                                <span>
                                    Use at least 8 characters,
                                    including letters and numbers.
                                </span>

                                <button
                                    type="submit"
                                    class="zs-primary-btn"
                                >
                                    <i class="fas fa-lock"></i>
                                    Update Password
                                </button>

                            </div>

                        </form>

                    </div>

                    {{-- =====================================
                         TWO-FACTOR AUTHENTICATION
                    ===================================== --}}

                    <div class="zs-card">

                        <div class="zs-card-header">

                            <div>
                                <h3>Two-Factor Authentication</h3>

                                <p>
                                    Add another layer of security
                                    to your ZeitOps account.
                                </p>
                            </div>

                            <i class="fas fa-shield-alt zs-card-icon"></i>

                        </div>

                        <div class="zs-feature-row">

                            <div class="zs-feature-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>

                            <div class="zs-feature-description">

                                <strong>Authenticator App</strong>

                                <span>
                                    Protect sign-ins with a
                                    one-time verification code.
                                </span>

                            </div>

                            <span class="zs-coming-soon">
                                Coming Soon
                            </span>

                        </div>

                    </div>

                </section>

                {{-- =====================================
                     PREFERENCES
                ===================================== --}}

                <section
                    class="zs-tab-panel"
                    id="zs-preferences"
                >

                    {{-- =====================================
                         APPEARANCE & PREFERENCES
                    ===================================== --}}

                    <div class="zs-card">

                        <div class="zs-card-header">

                            <div>
                                <h3>Appearance & Preferences</h3>

                                <p>
                                    Customize how ZeitOps
                                    looks and feels.
                                </p>
                            </div>

                            <i class="fas fa-palette zs-card-icon"></i>

                        </div>

                        {{-- Theme --}}

                        <div class="zs-form-group">

                            <label for="zs-theme">
                                Appearance
                            </label>

                            <select
                                id="zs-theme"
                                class="zs-input"
                                disabled
                            >
                                <option>Light Theme</option>
                                <option>Dark Theme</option>
                                <option>System Default</option>
                            </select>

                            <small>
                                Theme switching will be available
                                in a future update.
                            </small>

                        </div>

                        {{-- Language --}}

                        <div class="zs-form-group zs-preference-field">

                            <label for="zs-language">
                                Language
                            </label>

                            <select
                                id="zs-language"
                                class="zs-input"
                                disabled
                            >
                                <option>English</option>
                                <option>Dutch</option>
                                <option>Persian</option>
                            </select>

                            <small>
                                Additional interface languages
                                are planned.
                            </small>

                        </div>

                    </div>

                    {{-- =====================================
                         NOTIFICATIONS
                    ===================================== --}}

                    <div class="zs-card">

                        <div class="zs-card-header">

                            <div>
                                <h3>Notifications</h3>

                                <p>
                                    Configure how you receive
                                    important alerts.
                                </p>
                            </div>

                            <i class="fas fa-bell zs-card-icon"></i>

                        </div>

                        <div class="zs-feature-row">

                            <div class="zs-feature-description">

                                <strong>Email Notifications</strong>

                                <span>
                                    Server alerts, deployment status
                                    and account updates.
                                </span>

                            </div>

                            <span class="zs-coming-soon">
                                Coming Soon
                            </span>

                        </div>

                    </div>

                </section>

            </div>

        </div>

    </div>
@endsection

{{-- =====================================
     PAGE-SPECIFIC JAVASCRIPT
===================================== --}}

@push('js-scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            "use strict";

            const settings = document.querySelector(".zs-settings");

            if (!settings) return;

            const tabs = settings.querySelectorAll("[data-zs-tab]");
            const panels = settings.querySelectorAll(".zs-tab-panel");

            // =====================================
            // TAB SWITCHING
            // =====================================

            function activateTab(tabName) {

                const validTabs = ["personal", "security", "preferences"];

                if (!validTabs.includes(tabName)) {
                    tabName = "personal";
                }

                tabs.forEach(tab => {

                    const active = tab.dataset.zsTab === tabName;

                    tab.classList.toggle("active", active);

                    tab.setAttribute(
                        "aria-pressed",
                        String(active)
                    );

                });

                panels.forEach(panel => {

                    const active = panel.id === `zs-${tabName}`;

                    panel.classList.toggle("active", active);

                });

            }

            tabs.forEach(tab => {

                tab.addEventListener("click", function () {

                    activateTab(this.dataset.zsTab);

                });

            });

            // =====================================
            // RESTORE ACTIVE TAB
            // =====================================

            const activeTab = @json(
        session('active_tab')
        ?? ($errors->password->any() ? 'security' : 'personal')
    );

            activateTab(activeTab);

            // =====================================
            // SUCCESS NOTIFICATION
            // =====================================

            @if(session('success'))

            if (typeof Swal !== "undefined") {

                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: @json(session('success')),
                    confirmButtonText: "OK",
                    confirmButtonColor: "#3468F6",
                    timer: 2500,
                    timerProgressBar: true
                });

            }

            @endif

            // =====================================
            // VALIDATION ERROR NOTIFICATION
            // =====================================

            @if($errors->any())

            if (typeof Swal !== "undefined") {

                Swal.fire({
                    icon: "error",
                    title: "Validation Error",
                    text: "Please review the highlighted fields.",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#3468F6"
                });

            }

            @endif

        });
    </script>
@endpush
