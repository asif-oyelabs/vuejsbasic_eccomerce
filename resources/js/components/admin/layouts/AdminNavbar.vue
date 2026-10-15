<script setup>
import { auth } from '../../../auth.js';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

async function logout() {
    try {
        await axios.post('/api/logout', {}, {
            withCredentials: true,
            headers: {
                Accept: 'application/json',
            },
        });
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        auth.user = null;
        auth.loaded = true;

        await router.push({ name: 'admin.login' });
    }
}
</script>

<template>
    <header class="admin-navbar">
        <div class="navbar-heading">
            <div class="navbar-eyebrow">
                EASYCOMMERCE / ADMIN
            </div>

            <h5 class="fw-bold mb-0">Dashboard</h5>

            <p class="text-app-muted small mb-0 mt-1">
                Overview of your store
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="navbar-profile-icon">
                <i class="bi bi-person-fill"></i>
            </div>

            <div class="profile-details d-none d-sm-block">
                <div class="fw-semibold small">
                    {{ auth.user?.name || 'Admin' }}
                </div>

                <span class="admin-role">
                    <span class="status-dot"></span>
                    Administrator
                </span>
            </div>

            <div class="navbar-divider d-none d-sm-block"></div>

            <button
                type="button"
                class="btn btn-outline-danger logout-button"
                @click="logout"
            >
                <i class="bi bi-box-arrow-right me-sm-2"></i>
                <span class="d-none d-sm-inline">Logout</span>
            </button>
        </div>
    </header>
</template>

<style scoped>
.admin-navbar {
    min-height: 96px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 20px 30px;
    background: var(--app-white);
    border-bottom: 1px solid var(--app-border);
}

.navbar-eyebrow {
    margin-bottom: 6px;
    color: var(--app-primary-hover);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
}

.navbar-heading h5 {
    color: var(--app-text);
}

.navbar-profile-icon {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--app-primary-light);
    color: var(--app-primary-hover);
    font-size: 21px;
}

.admin-role {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    color: var(--app-muted);
    font-size: 11px;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--app-success);
}

.navbar-divider {
    width: 1px;
    height: 36px;
    background: var(--app-border);
}

.logout-button {
    border-radius: 9px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .admin-navbar {
        min-height: 82px;
        padding: 16px;
    }
}
</style>