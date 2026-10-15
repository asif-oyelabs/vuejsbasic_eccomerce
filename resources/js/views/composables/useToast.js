
import { reactive } from 'vue';

const toast = reactive({
    visible: false,
    message: '',
    type: 'success',
});

let hideTimer = null;

function showToast(message, type = 'success', duration = 3000) {
    toast.message = message;
    toast.type = type;
    toast.visible = true;

    if (hideTimer) {
        clearTimeout(hideTimer);
    }

    hideTimer = setTimeout(() => {
        toast.visible = false;
        hideTimer = null;
    }, duration);
}

function hideToast() {
    toast.visible = false;

    if (hideTimer) {
        clearTimeout(hideTimer);
        hideTimer = null;
    }
}

export function useToast() {
    return { toast, showToast, hideToast };
}
