<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="/panel/dashboard.js"></script>
<script>

    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const overlay = document.getElementById('sidebar-overlay');

        if (!sidebar) {
            console.error('ZeitOps: #sidebar not found');
            return;
        }

        const mobileQuery = window.matchMedia('(max-width: 991px)');
        const menuItems = Array.from(
            sidebar.querySelectorAll(
                '.sidebar-menu > ul > li.has-submenu'
            )
        );

        // =========================================
        // SIDEBAR TOGGLE
        // =========================================

        function setSidebarOpen(open) {
            if (mobileQuery.matches) {
                sidebar.classList.toggle('mobile-open', open);

                if (overlay) {
                    overlay.classList.toggle('active', open);
                }

                document.body.classList.toggle(
                    'sidebar-is-open',
                    open
                );

                document.body.classList.remove('sidebar-hidden');
            } else {
                sidebar.classList.remove('mobile-open');

                if (overlay) {
                    overlay.classList.remove('active');
                }

                document.body.classList.remove('sidebar-is-open');
                document.body.classList.toggle('sidebar-hidden', !open);
            }

            if (toggle) {
                toggle.setAttribute(
                    'aria-expanded',
                    String(open)
                );
            }
        }

        if (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();

                if (mobileQuery.matches) {
                    setSidebarOpen(
                        !sidebar.classList.contains('mobile-open')
                    );
                } else {
                    setSidebarOpen(
                        document.body.classList.contains('sidebar-hidden')
                    );
                }
            });
        } else {
            console.warn('ZeitOps: #sidebar-toggle not found');
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                if (mobileQuery.matches) {
                    setSidebarOpen(false);
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && mobileQuery.matches) {
                setSidebarOpen(false);
            }
        });

        mobileQuery.addEventListener('change', function () {
            sidebar.classList.remove('mobile-open');
            document.body.classList.remove(
                'sidebar-is-open',
                'sidebar-hidden'
            );

            if (overlay) {
                overlay.classList.remove('active');
            }

            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        // =========================================
        // ACCORDION DROPDOWNS
        // =========================================

        function closeDropdown(item) {
            const trigger = item.querySelector(':scope > a');
            const submenu = item.querySelector(':scope > ul.submenu');

            item.classList.remove('submenu-open');

            if (submenu) {
                submenu.hidden = true;
            }

            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        }

        function openDropdown(item) {
            const trigger = item.querySelector(':scope > a');
            const submenu = item.querySelector(':scope > ul.submenu');

            if (!trigger || !submenu) return;

            // Accordion: close all others first
            menuItems.forEach(function (otherItem) {
                if (otherItem !== item) {
                    closeDropdown(otherItem);
                }
            });

            submenu.hidden = false;
            item.classList.add('submenu-open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        menuItems.forEach(function (item, index) {
            const trigger = item.querySelector(':scope > a');
            const submenu = item.querySelector(':scope > ul.submenu');

            if (!trigger || !submenu) return;

            if (!submenu.id) {
                submenu.id = 'zeitops-submenu-' + index;
            }

            trigger.setAttribute('aria-controls', submenu.id);

            // Preserve the active dropdown rendered by Laravel.
            const isActive =
                item.classList.contains('submenu-open') ||
                submenu.querySelector('a.active, a[aria-current="page"]') !== null;

            item.classList.toggle('submenu-open', isActive);
            submenu.hidden = !isActive;

            trigger.setAttribute(
                'aria-expanded',
                String(isActive)
            );

            // Keep normal accordion behavior.
            trigger.addEventListener('click', function (event) {
                event.preventDefault();

                const isOpen = item.classList.contains('submenu-open');

                if (isOpen) {
                    closeDropdown(item);
                } else {
                    openDropdown(item);
                }
            });
        });

        // =========================================
        // CLOSE MOBILE MENU AFTER NAVIGATION
        // =========================================

        sidebar.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', function () {
                const href = link.getAttribute('href');

                if (
                    mobileQuery.matches &&
                    href &&
                    href !== '#' &&
                    !link.closest('.has-submenu > a')
                ) {
                    setSidebarOpen(false);
                }
            });
        });

        console.log(
            'ZeitOps sidebar initialized:',
            menuItems.length,
            'dropdown menus'
        );
    });

</script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Membership Notification --}}
@if(session('membership_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'warning',
                title: 'Membership Required',
                text: @json(session('membership_error')),
                confirmButtonText: 'Got it',
                confirmButtonColor: '#3468F6',
                background: '#ffffff',
                color: '#26354b',
                customClass: {
                    popup: 'zeitops-membership-alert'
                }
            });
        });
    </script>
@endif

