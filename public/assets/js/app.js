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

        if (window.toastr) {
            messages.forEach(function (message) {
                if (typeof window.toastr[message.type] === 'function') {
                    window.toastr[message.type](message.text);
                }
            });
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
                confirmButtonColor: '#272757'
            });
            return;
        }

        messages.forEach(function (message) {
            window.alert(message.text);
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
            },
            {
                selector: 'form.important-action-form',
                title: 'Confirm action?',
                text: 'Please confirm you want to continue.',
                icon: 'question',
                confirmButtonText: 'Yes, continue'
            },
            {
                selector: 'form.profile-update-form',
                title: 'Update profile?',
                text: 'Please confirm you want to save these profile changes.',
                icon: 'question',
                confirmButtonText: 'Yes, update'
            },
            {
                selector: 'form.password-update-form',
                title: 'Update password?',
                text: 'Please confirm you want to change your login password.',
                icon: 'warning',
                confirmButtonText: 'Yes, update password'
            }
        ];

        confirmationTypes.forEach(function (config) {
            document.querySelectorAll(config.selector).forEach(function (form) {
                if (form.dataset.confirmHandler === 'true') {
                    return;
                }

                form.dataset.confirmHandler = 'true';

            form.addEventListener('submit', function (event) {
                if (event.defaultPrevented) {
                    return;
                }

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
                    confirmButtonColor: '#272757',
                    cancelButtonColor: '#111827',
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

    function initSalesCalculators() {
        document.querySelectorAll('[data-sales-form]').forEach(function (form) {
            var itemsWrap = form.querySelector('[data-sales-items]');
            var addButton = form.querySelector('[data-sales-add-row]');
            var subtotalInput = form.querySelector('[data-sales-subtotal]');
            var discountInput = form.querySelector('[data-sales-discount]');
            var vatInput = form.querySelector('[data-sales-vat]');
            var grandTotalInput = form.querySelector('[data-sales-grand-total]');

            if (!itemsWrap || !addButton || !subtotalInput || !discountInput || !vatInput || !grandTotalInput) {
                return;
            }

            function parseAmount(value) {
                var amount = Number.parseFloat(value);
                return Number.isFinite(amount) ? amount : 0;
            }

            function rows() {
                return Array.prototype.slice.call(itemsWrap.querySelectorAll('[data-sales-row]'));
            }

            function selectedOption(row) {
                var select = row.querySelector('[data-sales-battery]');
                return select ? select.options[select.selectedIndex] : null;
            }

            function availableStock(row) {
                var option = selectedOption(row);
                var stock = option ? parseAmount(option.dataset.stock) : 0;
                var select = row.querySelector('[data-sales-battery]');
                var originalBattery = row.dataset.originalBattery || '';
                var currentQuantity = parseAmount(row.dataset.currentQuantity || 0);

                if (select && select.value && select.value === originalBattery) {
                    stock += currentQuantity;
                }

                return stock;
            }

            function updateRemoveButtons() {
                var currentRows = rows();
                currentRows.forEach(function (row) {
                    var removeButton = row.querySelector('[data-sales-remove-row]');
                    if (removeButton) {
                        removeButton.disabled = currentRows.length <= 1;
                    }
                });
            }

            function reindexRows() {
                rows().forEach(function (row, index) {
                    row.querySelectorAll('select, input').forEach(function (input) {
                        if (input.name) {
                            input.name = input.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                        }

                        if (input.id) {
                            input.id = input.id.replace(/items_\d+_/, 'items_' + index + '_');
                        }
                    });

                    row.querySelectorAll('label[for]').forEach(function (label) {
                        label.setAttribute('for', label.getAttribute('for').replace(/items_\d+_/, 'items_' + index + '_'));
                    });
                });
            }

            function updateRow(row, shouldWarn) {
                var option = selectedOption(row);
                var quantityInput = row.querySelector('[data-sales-quantity]');
                var unitPriceInput = row.querySelector('[data-sales-unit-price]');
                var lineTotalInput = row.querySelector('[data-sales-line-total]');

                if (!quantityInput || !unitPriceInput || !lineTotalInput) {
                    return;
                }

                var quantity = Math.max(parseAmount(quantityInput.value), 0);
                var price = option ? parseAmount(option.dataset.price) : 0;
                var stock = availableStock(row);

                if (option && option.value && quantity > stock) {
                    quantityInput.value = stock > 0 ? stock : 1;
                    quantity = parseAmount(quantityInput.value);

                    if (shouldWarn && window.toastr) {
                        window.toastr.error('Insufficient stock.');
                    }
                }

                unitPriceInput.value = price.toFixed(2);
                lineTotalInput.value = (quantity * price).toFixed(2);
            }

            function updateSummary() {
                var subtotal = rows().reduce(function (total, row) {
                    var lineTotalInput = row.querySelector('[data-sales-line-total]');
                    return total + parseAmount(lineTotalInput ? lineTotalInput.value : 0);
                }, 0);
                var discount = Math.max(parseAmount(discountInput.value), 0);
                var vat = Math.max(parseAmount(vatInput.value), 0);
                var grandTotal = Math.max(subtotal - discount + vat, 0);

                subtotalInput.value = subtotal.toFixed(2);
                grandTotalInput.value = grandTotal.toFixed(2);
            }

            function updateAll(shouldWarn) {
                rows().forEach(function (row) {
                    updateRow(row, shouldWarn);
                });
                updateSummary();
                updateRemoveButtons();
            }

            function bindRow(row) {
                var batterySelect = row.querySelector('[data-sales-battery]');
                var quantityInput = row.querySelector('[data-sales-quantity]');
                var removeButton = row.querySelector('[data-sales-remove-row]');

                if (batterySelect) {
                    batterySelect.addEventListener('change', function () {
                        updateAll(false);
                    });
                }

                if (quantityInput) {
                    quantityInput.addEventListener('input', function () {
                        updateAll(true);
                    });
                }

                if (removeButton) {
                    removeButton.addEventListener('click', function () {
                        if (rows().length <= 1) {
                            return;
                        }

                        row.remove();
                        reindexRows();
                        updateAll(false);
                    });
                }
            }

            addButton.addEventListener('click', function () {
                var template = rows()[0];
                var clone = template.cloneNode(true);

                clone.dataset.originalBattery = '';
                clone.dataset.currentQuantity = '0';
                clone.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
                    feedback.remove();
                });
                clone.querySelectorAll('.is-invalid').forEach(function (field) {
                    field.classList.remove('is-invalid');
                });
                clone.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                clone.querySelectorAll('input').forEach(function (input) {
                    if (input.matches('[data-sales-quantity]')) {
                        input.value = '1';
                    } else {
                        input.value = '0.00';
                    }
                });

                itemsWrap.appendChild(clone);
                bindRow(clone);
                reindexRows();
                updateAll(false);
            });

            discountInput.addEventListener('input', function () {
                updateSummary();
            });
            vatInput.addEventListener('input', function () {
                updateSummary();
            });

            rows().forEach(bindRow);
            updateAll(false);
        });
    }

    function initPaymentAllocations() {
        document.querySelectorAll('[data-payment-form]').forEach(function (form) {
            var amountInput = form.querySelector('[data-payment-amount]');
            var modeInputs = Array.prototype.slice.call(form.querySelectorAll('[data-payment-mode]'));
            var allocationInputs = Array.prototype.slice.call(form.querySelectorAll('[data-payment-allocation]'));
            var rows = Array.prototype.slice.call(form.querySelectorAll('[data-payment-invoice-row]'));
            var allocatedTotalOutputs = Array.prototype.slice.call(form.querySelectorAll('[data-payment-allocated-total]'));
            var remainingAfterTotalOutputs = Array.prototype.slice.call(form.querySelectorAll('[data-payment-remaining-after-total]'));
            var summaryRow = form.querySelector('[data-payment-summary-row]');
            var summaryReceived = form.querySelector('[data-payment-summary-received]');
            var summaryRemaining = form.querySelector('[data-payment-summary-remaining]');

            if (!amountInput || (allocationInputs.length === 0 && !summaryRow)) {
                return;
            }

            function parseAmount(value) {
                var amount = Number.parseFloat(value);
                return Number.isFinite(amount) ? amount : 0;
            }

            function rounded(amount) {
                return Math.round((amount + Number.EPSILON) * 100) / 100;
            }

            function currentMode() {
                var checked = modeInputs.find(function (input) {
                    return input.checked;
                });

                return checked ? checked.value : 'auto';
            }

            function invoiceRemaining(row) {
                return rounded(Math.max(parseAmount(row.dataset.remaining), 0));
            }

            function setAllocation(input, amount) {
                input.value = amount > 0 ? rounded(amount).toFixed(2) : '0.00';
            }

            function formatCurrency(amount) {
                return 'AED ' + rounded(amount).toLocaleString(undefined, {
                    minimumFractionDigits: rounded(amount) % 1 === 0 ? 0 : 2,
                    maximumFractionDigits: 2
                });
            }

            function updatePaymentSummary() {
                if (!summaryRow) {
                    return;
                }

                var totalPending = rounded(Math.max(parseAmount(summaryRow.dataset.totalPending), 0));
                var received = rounded(Math.max(parseAmount(amountInput.value), 0));
                var applied = Math.min(received, totalPending);
                var remaining = rounded(Math.max(totalPending - applied, 0));

                if (summaryReceived) {
                    summaryReceived.textContent = formatCurrency(applied);
                }

                if (summaryRemaining) {
                    summaryRemaining.textContent = formatCurrency(remaining);
                }
            }

            function updateAllocatedTotal() {
                var allocatedTotal = allocationInputs.reduce(function (total, input) {
                    return rounded(total + Math.max(parseAmount(input.value), 0));
                }, 0);
                var remainingAfterTotal = 0;

                rows.forEach(function (row) {
                    var input = row.querySelector('[data-payment-allocation]');
                    var rowAfter = row.querySelector('[data-payment-row-after]');
                    var remaining = invoiceRemaining(row);
                    var allocation = rounded(Math.max(parseAmount(input ? input.value : 0), 0));
                    var after = rounded(Math.max(remaining - allocation, 0));

                    remainingAfterTotal = rounded(remainingAfterTotal + after);

                    if (rowAfter) {
                        rowAfter.textContent = formatCurrency(after);
                    }
                });

                allocatedTotalOutputs.forEach(function (output) {
                    output.textContent = formatCurrency(allocatedTotal);
                });

                remainingAfterTotalOutputs.forEach(function (output) {
                    output.textContent = formatCurrency(remainingAfterTotal);
                });
            }

            function autoAllocate() {
                var remainingPayment = rounded(Math.max(parseAmount(amountInput.value), 0));

                rows.forEach(function (row) {
                    var input = row.querySelector('[data-payment-allocation]');
                    var allocated = Math.min(invoiceRemaining(row), remainingPayment);

                    if (!input) {
                        return;
                    }

                    setAllocation(input, allocated);
                    remainingPayment = rounded(remainingPayment - allocated);
                });

                updateAllocatedTotal();
            }

            function updateAllocationMode() {
                var isAuto = currentMode() === 'auto';

                allocationInputs.forEach(function (input) {
                    input.readOnly = isAuto;
                    input.classList.toggle('is-readonly', isAuto);
                });

                if (isAuto) {
                    autoAllocate();
                    return;
                }

                updateAllocatedTotal();
            }

            function validateAllocation() {
                var paymentAmount = rounded(Math.max(parseAmount(amountInput.value), 0));
                var allocatedTotal = 0;
                var invalidInput = null;

                if (summaryRow && allocationInputs.length === 0) {
                    var totalPending = rounded(Math.max(parseAmount(summaryRow.dataset.totalPending), 0));

                    if (paymentAmount <= 0) {
                        if (window.toastr) {
                            window.toastr.error('Received amount must be greater than zero.');
                        }
                        amountInput.focus();
                        return false;
                    }

                    if (paymentAmount > totalPending) {
                        if (window.toastr) {
                            window.toastr.error('Received amount cannot exceed total pending amount.');
                        }
                        amountInput.focus();
                        return false;
                    }

                    return true;
                }

                if (currentMode() === 'auto') {
                    autoAllocate();
                }

                rows.forEach(function (row) {
                    var input = row.querySelector('[data-payment-allocation]');
                    var allocation = rounded(Math.max(parseAmount(input ? input.value : 0), 0));
                    var remaining = invoiceRemaining(row);

                    if (allocation > remaining) {
                        invalidInput = input;
                    }

                    allocatedTotal = rounded(allocatedTotal + allocation);
                });

                if (paymentAmount <= 0) {
                    if (window.toastr) {
                        window.toastr.error('Payment amount must be greater than zero.');
                    }
                    amountInput.focus();
                    return false;
                }

                if (invalidInput) {
                    if (window.toastr) {
                        window.toastr.error('Allocation cannot exceed invoice remaining amount.');
                    }
                    invalidInput.focus();
                    return false;
                }

                if (Math.abs(allocatedTotal - paymentAmount) > 0.009) {
                    if (window.toastr) {
                        window.toastr.error('Total allocation must equal payment amount.');
                    }
                    amountInput.focus();
                    return false;
                }

                return true;
            }

            amountInput.addEventListener('input', function () {
                updatePaymentSummary();

                if (currentMode() === 'auto') {
                    autoAllocate();
                }
            });

            modeInputs.forEach(function (input) {
                input.addEventListener('change', updateAllocationMode);
            });

            allocationInputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    var max = parseAmount(input.getAttribute('max'));
                    var value = parseAmount(input.value);

                    if (value > max) {
                        input.value = max.toFixed(2);
                    }

                    updateAllocatedTotal();
                });
            });

            form.addEventListener('submit', function (event) {
                if (!validateAllocation()) {
                    event.preventDefault();
                }
            });

            updatePaymentSummary();
            updateAllocationMode();
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
        initPaymentAllocations();
        initActionConfirmations();
        initCustomerSearch();
        initCustomerDetailsModals();
        initRepairAmountCalculators();
        initSalesCalculators();
        initPasswordToggles();
        showFlashMessages();
    });
})();
