
<template>
    <Transition name="toast-slide">
        <div
            v-if="toast.visible"
            class="app-toast"
            :class="`toast-${toast.type}`"
            role="status"
            aria-live="polite"
        >
            <div class="toast-icon">
                <i :class="iconClass"></i>
            </div>

            <div class="toast-content">
                <strong>{{ title }}</strong>
                <p>{{ toast.message }}</p>
            </div>

            <button
                type="button"
                class="toast-close"
                aria-label="Close notification"
                @click="hideToast"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { useToast } from './useToast';

const { toast, hideToast } = useToast();

const title = computed(() => {
    const titles = {
        success: 'Success',
        error: 'Something went wrong',
        warning: 'Warning',
        info: 'Information',
    };

    return titles[toast.type] || 'Notification';
});

const iconClass = computed(() => {
    const icons = {
        success: 'bi bi-check-circle-fill',
        error: 'bi bi-x-circle-fill',
        warning: 'bi bi-exclamation-triangle-fill',
        info: 'bi bi-info-circle-fill',
    };

    return icons[toast.type] || icons.info;
});
</script>

<style scoped>
.app-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 99999;
    display: flex;
    align-items: flex-start;
    gap: 13px;
    width: min(410px, calc(100vw - 32px));
    padding: 17px;
    border: 1px solid var(--app-border, #e5e7eb);
    border-left: 4px solid #198754;
    border-radius: 13px;
    background: #fff;
    box-shadow: 0 12px 35px rgb(0 0 0 / 12%);
}

.toast-success {
    border-left-color: #198754;
}

.toast-error {
    border-left-color: #dc3545;
}

.toast-warning {
    border-left-color: #f59e0b;
}

.toast-info {
    border-left-color: #0ea5e9;
}

.toast-icon {
    padding-top: 2px;
    color: #198754;
    font-size: 1.35rem;
}

.toast-error .toast-icon {
    color: #dc3545;
}

.toast-warning .toast-icon {
    color: #d97706;
}

.toast-info .toast-icon {
    color: #0284c7;
}

.toast-content {
    flex: 1;
    min-width: 0;
}

.toast-content strong {
    display: block;
    margin-bottom: 4px;
    color: #1f2937;
    font-size: 0.95rem;
}

.toast-content p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.toast-close {
    padding: 3px;
    border: 0;
    background: transparent;
    color: #6b7280;
}

.toast-close:hover {
    color: #111827;
}

.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.toast-slide-enter-from,
.toast-slide-leave-to {
    opacity: 0;
    transform: translateX(25px);
}

@media (max-width: 575px) {
    .app-toast {
        top: 12px;
        right: 12px;
    }
}
</style>
