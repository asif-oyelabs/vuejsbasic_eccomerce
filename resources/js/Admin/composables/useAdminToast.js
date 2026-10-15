
import { reactive } from 'vue';

export const adminToast = reactive({
    visible: false,
    message: '',
    type: 'success',
});

let toastTimer = null;

export function showAdminToast(message, type = 'success') {
    if (toastTimer) {
        clearTimeout(toastTimer);
    }

    adminToast.message = message;
    adminToast.type = ['success', 'error', 'warning', 'info'].includes(type)
        ? type
        : 'success';
    adminToast.visible = true;

    toastTimer = setTimeout(() => {
        adminToast.visible = false;
        toastTimer = null;
    }, 3500);
}

export function hideAdminToast() {
    if (toastTimer) {
        clearTimeout(toastTimer);
        toastTimer = null;
    }

    adminToast.visible = false;
}
