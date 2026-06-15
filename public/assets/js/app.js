(function () {
    'use strict';

    function configureToastr() {
        if (!window.toastr) {
            return;
        }

        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            newestOnTop: true,
            positionClass: 'toast-top-right',
            preventDuplicates: true,
            timeOut: 3000,
            extendedTimeOut: 1200
        };
    }

    function showFlashMessages() {
        if (!document.body) {
            return;
        }

        var messages = [
            { type: 'success', text: document.body.dataset.flashSuccess },
            { type: 'error', text: document.body.dataset.flashError },
            { type: 'warning', text: document.body.dataset.flashWarning },
            { type: 'info', text: document.body.dataset.flashInfo }
        ].filter(function (message) {
            return Boolean(message.text);
        });

        if (messages.length === 0) {
            return;
        }

        if (window.Swal) {
            var message = messages[0];
            var titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Information'
            };

            window.Swal.fire({
                title: titles[message.type] || 'Message',
                text: message.text,
                icon: message.type,
                confirmButtonColor: '#f5a400'
            });

            return;
        }

        messages.forEach(function (message) {
            if (window.toastr && typeof window.toastr[message.type] === 'function') {
                window.toastr[message.type](message.text);
            }
        });
    }

    function initSidebar() {
        var sidebar = document.querySelector('.app-sidebar');
        var backdrop = document.querySelector('.sidebar-backdrop');
        var toggle = document.querySelector('.menu-toggle');
        var closeButton = document.querySelector('.sidebar-close');
        var desktopQuery = window.matchMedia('(min-width: 992px)');

        if (!sidebar || !backdrop || !toggle) {
            return;
        }

        function updateToggleState(isOpen) {
            toggle.setAttribute('aria-expanded', String(isOpen));
            document.body.classList.toggle('sidebar-open', isOpen);
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            updateToggleState(false);
        }

        function openSidebar() {
            if (desktopQuery.matches) {
                return;
            }

            sidebar.classList.add('show');
            backdrop.classList.add('show');
            updateToggleState(true);
        }

        toggle.addEventListener('click', function () {
            if (sidebar.classList.contains('show')) {
                closeSidebar();
                return;
            }

            openSidebar();
        });

        if (closeButton) {
            closeButton.addEventListener('click', closeSidebar);
        }

        backdrop.addEventListener('click', closeSidebar);

        document.querySelectorAll('.sidebar-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (!desktopQuery.matches) {
                    closeSidebar();
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        desktopQuery.addEventListener('change', function (event) {
            if (event.matches) {
                closeSidebar();
            }
        });
    }

    function initProfileDropdown() {
        var dropdown = document.querySelector('.user-dropdown');
        var toggle = document.querySelector('[data-profile-dropdown-toggle]');
        var menu = document.querySelector('.profile-menu');

        if (!dropdown || !toggle || !menu) {
            return;
        }

        function closeDropdown() {
            menu.classList.remove('show');
            toggle.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function openDropdown() {
            menu.classList.add('show');
            toggle.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }

        function toggleDropdown(event) {
            event.preventDefault();
            event.stopPropagation();

            if (menu.classList.contains('show')) {
                closeDropdown();
                return;
            }

            openDropdown();
        }

        closeDropdown();

        toggle.addEventListener('click', toggleDropdown);

        document.addEventListener('click', function (event) {
            var clickedToggle = event.target.closest('[data-profile-dropdown-toggle]');

            if (clickedToggle) {
                toggleDropdown(event);
                return;
            }

            if (!dropdown.contains(event.target)) {
                closeDropdown();
            }
        });

        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDropdown();
            }
        });
    }

    function submitConfirmedForm(form) {
        form.dataset.confirmed = 'true';
        HTMLFormElement.prototype.submit.call(form);
    }

    function initActionConfirmations() {
        var confirmationTypes = [
            {
                selector: 'form.delete-form',
                title: 'Are you sure?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                confirmButtonText: 'Yes, delete it'
            },
            {
                selector: 'form.logout-form',
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                confirmButtonText: 'Yes, logout'
            },
            {
                selector: 'form.status-change-form',
                title: 'Change status?',
                text: 'Please confirm you want to update this status.',
                icon: 'question',
                confirmButtonText: 'Yes, update'
            },
            {
                selector: 'form.payment-confirm-form',
                title: 'Confirm payment?',
                text: 'Please confirm this payment action.',
                icon: 'question',
                confirmButtonText: 'Yes, confirm'
            }
        ];

        confirmationTypes.forEach(function (config) {
            document.querySelectorAll(config.selector).forEach(function (form) {
                if (form.dataset.confirmHandler === 'true') {
                    return;
                }

                form.dataset.confirmHandler = 'true';

            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();

                if (!window.Swal) {
                    submitConfirmedForm(form);
                    return;
                }

                window.Swal.fire({
                    title: config.title,
                    text: config.text,
                    icon: config.icon,
                    showCancelButton: true,
                    confirmButtonText: config.confirmButtonText,
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#f5a400',
                    cancelButtonColor: '#07111f',
                    reverseButtons: true,
                    focusCancel: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        submitConfirmedForm(form);
                    }
                });
            });
        });
        });
    }

    function initCustomerSearch() {
        document.querySelectorAll('.customer-search-form').forEach(function (form) {
            var input = form.querySelector('input[name="search"]');
            var searchTimer;

            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                window.clearTimeout(searchTimer);

                searchTimer = window.setTimeout(function () {
                    HTMLFormElement.prototype.submit.call(form);
                }, 450);
            });
        });
    }

    function initCustomerDetailsModals() {
        document.querySelectorAll('.customer-details-modal').forEach(function (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        if (window.bootstrap && window.bootstrap.Modal) {
            return;
        }

        function closeModal(modal) {
            if (!modal) {
                return;
            }

            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
            document.body.classList.remove('modal-open');
        }

        function openModal(modal) {
            if (!modal) {
                return;
            }

            document.querySelectorAll('.customer-details-modal.show').forEach(closeModal);
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            document.body.classList.add('modal-open');

            var closeButton = modal.querySelector('[data-bs-dismiss="modal"]');
            if (closeButton) {
                closeButton.focus();
            }
        }

        document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                var target = document.querySelector(trigger.dataset.bsTarget);

                if (!target || !target.classList.contains('customer-details-modal')) {
                    return;
                }

                event.preventDefault();
                openModal(target);
            });
        });

        document.querySelectorAll('.customer-details-modal').forEach(function (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal || event.target.closest('[data-bs-dismiss="modal"]')) {
                    closeModal(modal);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.customer-details-modal.show').forEach(closeModal);
        });
    }

    function initRepairAmountCalculators() {
        document.querySelectorAll('[data-repair-calculator]').forEach(function (form) {
            var quantityInput = form.querySelector('[data-repair-quantity]');
            var unitPriceInput = form.querySelector('[data-repair-unit-price]');
            var totalInput = form.querySelector('[data-repair-total]');

            if (!quantityInput || !unitPriceInput || !totalInput) {
                return;
            }

            function parseAmount(input) {
                var value = Number.parseFloat(input.value);
                return Number.isFinite(value) ? value : 0;
            }

            function updateTotal() {
                var quantity = Math.max(parseAmount(quantityInput), 0);
                var unitPrice = Math.max(parseAmount(unitPriceInput), 0);
                totalInput.value = (quantity * unitPrice).toFixed(2);
            }

            quantityInput.addEventListener('input', updateTotal);
            unitPriceInput.addEventListener('input', updateTotal);
            updateTotal();
        });
    }

    function initPasswordToggles() {
        var eyeIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
        var eyeOffIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/><path d="M9.88 5.09A10.4 10.4 0 0 1 12 4.87c6.5 0 10 7.13 10 7.13a18.2 18.2 0 0 1-2.51 3.55"/><path d="M6.61 6.61A17.8 17.8 0 0 0 2 12s3.5 7.13 10 7.13a10.6 10.6 0 0 0 4.23-.88"/></svg>';

        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            var shell = button.closest('.password-toggle-shell');
            var input = shell ? shell.querySelector('input[type="password"], input[type="text"]') : null;

            if (!input || button.dataset.passwordToggleReady === 'true') {
                return;
            }

            button.dataset.passwordToggleReady = 'true';
            button.innerHTML = eyeOffIcon;

            button.addEventListener('click', function () {
                var shouldShow = input.type === 'password';
                input.type = shouldShow ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(shouldShow));
                button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
                button.innerHTML = shouldShow ? eyeIcon : eyeOffIcon;
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        configureToastr();
        initSidebar();
        initProfileDropdown();
        initActionConfirmations();
        initCustomerSearch();
        initCustomerDetailsModals();
        initRepairAmountCalculators();
        initPasswordToggles();
        showFlashMessages();
    });
})();
