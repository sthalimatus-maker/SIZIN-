document.addEventListener('DOMContentLoaded', () => {
    const setupDialog = ({ backdrop, form, cancelButton, confirmButton, focusTarget, beforeConfirm }) => {
        let previousFocus = null;

        const close = () => {
            backdrop.hidden = true;
            document.body.classList.remove('has-modal');
            previousFocus?.focus();
        };

        form.removeAttribute('onsubmit');
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            previousFocus = document.activeElement;
            backdrop.hidden = false;
            document.body.classList.add('has-modal');
            (focusTarget || confirmButton).focus();
        });

        cancelButton.addEventListener('click', close);
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) close();
        });

        confirmButton.addEventListener('click', () => {
            if (!form.isConnected) return close();
            if (beforeConfirm && !beforeConfirm()) return;

            form.dataset.confirmed = 'true';
            close();
            form.requestSubmit();
        });

        backdrop.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
            if (event.key !== 'Tab') return;

            const controls = [...backdrop.querySelectorAll('button:not([disabled])')];
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    };

    const deleteBackdrop = document.querySelector('#delete-confirm');
    if (deleteBackdrop) {
        const cancelButton = deleteBackdrop.querySelector('[data-delete-cancel]');
        const confirmButton = deleteBackdrop.querySelector('[data-delete-confirm]');
        let pendingForm = null;
        let previousFocus = null;

        const closeDeleteDialog = () => {
            deleteBackdrop.hidden = true;
            document.body.classList.remove('has-modal');
            pendingForm = null;
            previousFocus?.focus();
        };

        document.querySelectorAll('form').forEach((form) => {
            if (!form.querySelector('button.button--danger') || form.method.toLowerCase() !== 'post') return;

            form.removeAttribute('onsubmit');
            form.addEventListener('submit', (event) => {
                if (form.dataset.confirmed === 'true') {
                    delete form.dataset.confirmed;
                    return;
                }

                event.preventDefault();
                pendingForm = form;
                previousFocus = document.activeElement;
                deleteBackdrop.hidden = false;
                document.body.classList.add('has-modal');
                confirmButton.focus();
            });
        });

        cancelButton.addEventListener('click', closeDeleteDialog);
        deleteBackdrop.addEventListener('click', (event) => {
            if (event.target === deleteBackdrop) closeDeleteDialog();
        });
        confirmButton.addEventListener('click', () => {
            if (!pendingForm?.isConnected) return closeDeleteDialog();

            const form = pendingForm;
            pendingForm = null;
            form.dataset.confirmed = 'true';
            closeDeleteDialog();
            form.requestSubmit();
        });
        deleteBackdrop.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeDeleteDialog();
            if (event.key === 'Tab') {
                const controls = [...deleteBackdrop.querySelectorAll('button:not([disabled])')];
                const first = controls[0];
                const last = controls[controls.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    const detailBackdrop = document.querySelector('#request-detail-modal');
    if (detailBackdrop) {
        const closeButton = detailBackdrop.querySelector('[data-detail-close]');
        let previousFocus = null;

        const closeDetail = () => {
            detailBackdrop.hidden = true;
            document.body.classList.remove('has-modal');
            previousFocus?.focus();
        };

        document.querySelectorAll('[data-request-detail]').forEach((button) => {
            button.addEventListener('click', () => {
                for (const key of ['type', 'reason', 'destination', 'period', 'status']) {
                    detailBackdrop.querySelector(`[data-detail="${key}"]`).textContent = button.dataset[key] || '—';
                }

                previousFocus = button;
                detailBackdrop.hidden = false;
                document.body.classList.add('has-modal');
                closeButton.focus();
            });
        });

        closeButton.addEventListener('click', closeDetail);
        detailBackdrop.addEventListener('click', (event) => {
            if (event.target === detailBackdrop) closeDetail();
        });
        detailBackdrop.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeDetail();
            if (event.key === 'Tab') {
                event.preventDefault();
                closeButton.focus();
            }
        });
    }

    const movementBackdrop = document.querySelector('#movement-modal');
    if (movementBackdrop) {
        const timeField = movementBackdrop.querySelector('#movement-time');
        const noteField = movementBackdrop.querySelector('#movement-note');
        const confirmButton = movementBackdrop.querySelector('[data-movement-confirm]');
        let pendingForm = null;
        let previousFocus = null;

        const closeMovement = () => {
            movementBackdrop.hidden = true;
            document.body.classList.remove('has-modal');
            pendingForm = null;
            previousFocus?.focus();
        };

        document.querySelectorAll('[data-movement-open]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = button.closest('[data-movement-form]');
                if (!form) return;

                pendingForm = form;
                previousFocus = button;
                const isReturn = form.dataset.kind === 'return';
                const timeName = isReturn ? 'actual_return_at' : 'actual_exit_at';
                timeField.value = form.querySelector(`[name="${timeName}"]`).value;
                noteField.value = '';
                movementBackdrop.querySelector('#movement-title').textContent = isReturn ? 'Catat siswa kembali?' : 'Catat siswa keluar?';
                movementBackdrop.querySelector('#movement-description').textContent = isReturn
                    ? `${form.dataset.student} tercatat keluar pada ${form.dataset.exitAt}. Periksa waktu kembali sebelum menyimpan.`
                    : `Catat waktu keluar untuk ${form.dataset.student}.`;
                confirmButton.textContent = isReturn ? 'Catat kembali' : 'Catat keluar';
                confirmButton.classList.toggle('button--success', isReturn);
                movementBackdrop.hidden = false;
                document.body.classList.add('has-modal');
                timeField.focus();
            });
        });

        movementBackdrop.querySelector('[data-movement-cancel]').addEventListener('click', closeMovement);
        movementBackdrop.addEventListener('click', (event) => {
            if (event.target === movementBackdrop) closeMovement();
        });
        confirmButton.addEventListener('click', () => {
            if (!pendingForm) return closeMovement();
            if (!timeField.value) {
                timeField.reportValidity();
                return;
            }

            const isReturn = pendingForm.dataset.kind === 'return';
            pendingForm.querySelector(`[name="${isReturn ? 'actual_return_at' : 'actual_exit_at'}"]`).value = timeField.value;
            pendingForm.querySelector(`[name="${isReturn ? 'return_note' : 'exit_note'}"]`).value = noteField.value.trim();
            const form = pendingForm;
            closeMovement();
            form.requestSubmit();
        });
        movementBackdrop.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeMovement();
            if (event.key === 'Tab') {
                const controls = [...movementBackdrop.querySelectorAll('input, textarea, button:not([disabled])')];
                const first = controls[0];
                const last = controls[controls.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    document.querySelector('[data-history-filter] select')?.addEventListener('change', (event) => {
        event.currentTarget.form.requestSubmit();
    });

    const logoutBackdrop = document.querySelector('#logout-confirm');
    const logoutForm = document.querySelector('[data-logout-form]');
    if (logoutBackdrop && logoutForm) {
        setupDialog({
            backdrop: logoutBackdrop,
            form: logoutForm,
            cancelButton: logoutBackdrop.querySelector('[data-logout-cancel]'),
            confirmButton: logoutBackdrop.querySelector('[data-logout-confirm]'),
        });
    }

    const approvalForm = document.querySelector('[data-approval-form]');
    const approvalBackdrop = document.querySelector('#approval-confirm');
    if (approvalForm && approvalBackdrop) {
        approvalForm.addEventListener('submit', () => {
            for (const key of ['student', 'class', 'type', 'destination', 'date', 'period']) {
                approvalBackdrop.querySelector(`[data-approval="${key}"]`).textContent = approvalForm.dataset[key] || '—';
            }
        });

        setupDialog({
            backdrop: approvalBackdrop,
            form: approvalForm,
            cancelButton: approvalBackdrop.querySelector('[data-approval-cancel]'),
            confirmButton: approvalBackdrop.querySelector('[data-approval-confirm]'),
        });
    }

    const rejectionForm = document.querySelector('[data-rejection-form]');
    const rejectionBackdrop = document.querySelector('#rejection-confirm');
    if (rejectionForm && rejectionBackdrop) {
        const reasonField = rejectionBackdrop.querySelector('#rejection-reason');
        const hiddenReason = rejectionForm.querySelector('[name="rejection_reason"]');
        reasonField.addEventListener('input', () => reasonField.setCustomValidity(''));

        setupDialog({
            backdrop: rejectionBackdrop,
            form: rejectionForm,
            focusTarget: reasonField,
            cancelButton: rejectionBackdrop.querySelector('[data-rejection-cancel]'),
            confirmButton: rejectionBackdrop.querySelector('[data-rejection-confirm]'),
            beforeConfirm: () => {
                const reason = reasonField.value.trim();
                if (!reason) {
                    reasonField.setCustomValidity('Alasan penolakan wajib diisi.');
                    reasonField.reportValidity();
                    return false;
                }

                hiddenReason.value = reason;
                return true;
            },
        });
    }

    const submitBackdrop = document.querySelector('#submit-confirm');
    const leaveForm = document.querySelector('#leave-request-form');
    if (!submitBackdrop || !leaveForm) return;

    const setSummary = (name, value) => {
        submitBackdrop.querySelector(`[data-summary="${name}"]`).textContent = value || '—';
    };
    const dateLabel = (value) => {
        if (!value) return '';
        const [year, month, day] = value.split('-').map(Number);
        return new Date(year, month - 1, day).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric',
        });
    };

    leaveForm.addEventListener('submit', () => {
        const data = new FormData(leaveForm);
        const start = `${dateLabel(data.get('leave_date'))} ${data.get('leave_time') || ''}`.trim();
        const end = `${dateLabel(data.get('return_date'))} ${data.get('return_time') || ''}`.trim();
        const evidence = leaveForm.querySelector('[name="evidence"]')?.files?.[0]?.name;

        setSummary('type', data.get('type'));
        setSummary('destination', data.get('destination'));
        setSummary('period', `${start} – ${end}`);
        setSummary('evidence', evidence || 'Tidak ada');
    });

    setupDialog({
        backdrop: submitBackdrop,
        form: leaveForm,
        cancelButton: submitBackdrop.querySelector('[data-submit-cancel]'),
        confirmButton: submitBackdrop.querySelector('[data-submit-confirm]'),
    });
});
