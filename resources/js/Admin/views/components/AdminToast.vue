
<script setup>
import { adminToast, hideAdminToast } from '../../composables/useAdminToast';

const styles = {
    success: {
        title: 'Success',
        icon: 'bi-check-circle-fill',
        className: 'toast-success',
    },
    error: {
        title: 'Error',
        icon: 'bi-x-circle-fill',
        className: 'toast-error',
    },
    warning: {
        title: 'Warning',
        icon: 'bi-exclamation-triangle-fill',
        className: 'toast-warning',
    },
    info: {
        title: 'Information',
        icon: 'bi-info-circle-fill',
        className: 'toast-info',
    },
};
</script>

<template>
    <Transition name="toast-slide">
        <div
            v-if="adminToast.visible"
            class="admin-toast toast show border-0 shadow"
            :class="styles[adminToast.type].className"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            <div class="d-flex align-items-start p-3 gap-3">
                <i
                    :class="['bi', styles[adminToast.type].icon, 'toast-icon']"
                    aria-hidden="true"
                ></i>

                <div class="flex-grow-1">
                    <div class="fw-bold mb-1">
                        {{ styles[adminToast.type].title }}
                    </div>
                    <div class="toast-message">
                        {{ adminToast.message }}
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close notification"
                    @click="hideAdminToast"
                ></button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.admin-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 2000;
    width: min(390px, calc(100vw - 32px));
    background: var(--app-white);
    border-radius: 12px;
    border-left: 4px solid var(--app-primary);
}

.toast-success {
    border-left-color: var(--app-success);
}

.toast-error {
    border-left-color: var(--app-danger);
}

.toast-warning {
    border-left-color: var(--app-warning);
}

.toast-info {
    border-left-color: var(--app-info);
}

.toast-icon {
    font-size: 22px;
}

.toast-success .toast-icon {
    color: var(--app-success);
}

.toast-error .toast-icon {
    color: var(--app-danger);
}

.toast-warning .toast-icon {
    color: var(--app-warning);
}

.toast-info .toast-icon {
    color: var(--app-info);
}

.toast-message {
    color: var(--app-muted);
    font-size: 14px;
    overflow-wrap: anywhere;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.toast-slide-enter-from,
.toast-slide-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

@media (max-width: 575.98px) {
    .admin-toast {
        top: 12px;
        right: 12px;
    }
}
</style>
