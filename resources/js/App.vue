<script setup>
import { onMounted, watch, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { auth, loadUser } from './auth';
import AppFooter from './views/Weblayout/AppFooter.vue';
import AppToast from './views/composables/AppToast.vue';


const router = useRouter();
const route = useRoute();
const isAdminRoute = computed(() => route.path.startsWith('/admin'));

// Load the currently authenticated user when the app starts.
onMounted(() => {
    loadUser();
});

// Refresh authentication state after navigation.
watch(() => route.fullPath, () => {
    loadUser();
});

function goHome() {
    router.push('/');
}

// Logout the current user.
async function logout() {
    try {
        await axios.post('/api/logout', {}, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        auth.user = null;
        await router.push('/login');
    } catch (error) {
        console.error('Logout failed:', error);
        alert(error.response?.data?.message || 'Unable to log out.');
    }
}
</script>

<template>
    <router-view />
    <AppToast />
</template>

