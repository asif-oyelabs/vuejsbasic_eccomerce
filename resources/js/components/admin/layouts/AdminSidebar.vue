<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const menuItems = [
    {
        label: 'Dashboard',
        path: '/admin/dashboard',
        icon: 'bi-speedometer2',
    },
];
</script>

<template>
    <aside class="admin-sidebar">
        <RouterLink
            to="/admin/dashboard"
            class="sidebar-brand text-decoration-none"
        >
            <span class="brand-icon">
                <i class="bi bi-shop"></i>
            </span>

            <span class="brand-text">
                <span class="brand-name">EasyCommerce</span>
                <small>Store Management</small>
            </span>
        </RouterLink>

        <div class="sidebar-divider"></div>

        <div class="sidebar-section-title">
            WORKSPACE
        </div>

        <nav class="nav flex-column gap-2">
            <RouterLink
                v-for="item in menuItems"
                :key="item.path"
                :to="item.path"
                class="sidebar-link"
                :class="{ active: route.path === item.path }"
            >
                <span class="sidebar-link-icon">
                    <i :class="['bi', item.icon]"></i>
                </span>

                <span>{{ item.label }}</span>

                <i
                    v-if="route.path === item.path"
                    class="bi bi-chevron-right ms-auto active-arrow"
                ></i>
            </RouterLink>
        </nav>

        <div class="sidebar-bottom">
            <div class="sidebar-help-icon">
                <i class="bi bi-shield-check"></i>
            </div>

            <div>
                <div class="fw-semibold small">Admin Workspace</div>
                <small>Manage your store</small>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.admin-sidebar {
    width: 260px;
    flex: 0 0 260px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 24px 18px;
    background: var(--app-white);
    border-right: 1px solid var(--app-border);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 4px;
    color: var(--app-text);
}

.brand-icon {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: 13px;
    background: var(--app-primary);
    color: white;
    font-size: 21px;
    box-shadow: 0 5px 12px rgba(66, 184, 131, 0.18);
}

.brand-text {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.brand-name {
    font-size: 17px;
    font-weight: 750;
    letter-spacing: -0.4px;
}

.brand-text small {
    color: var(--app-muted);
    font-size: 11px;
}

.sidebar-divider {
    height: 1px;
    margin: 26px 0;
    background: var(--app-border);
}

.sidebar-section-title {
    margin: 0 0 14px 10px;
    color: var(--app-muted);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.3px;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 48px;
    padding: 10px 12px;
    border: 1px solid transparent;
    border-radius: 10px;
    color: var(--app-muted);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
}

.sidebar-link:hover {
    background: var(--app-bg);
    color: var(--app-primary-hover);
}

.sidebar-link.active {
    background: var(--app-primary-light);
    border-color: var(--app-primary-light);
    color: var(--app-primary-hover);
    font-weight: 700;
}

.sidebar-link-icon {
    display: grid;
    place-items: center;
    width: 25px;
    font-size: 18px;
}

.active-arrow {
    font-size: 12px;
}

.sidebar-bottom {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: auto;
    padding: 15px 12px;
    background: var(--app-bg);
    border: 1px solid var(--app-border);
    border-radius: 12px;
}

.sidebar-help-icon {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 10px;
    background: var(--app-primary-light);
    color: var(--app-primary-hover);
    font-size: 19px;
}

.sidebar-bottom small {
    color: var(--app-muted);
    font-size: 11px;
}

@media (max-width: 767.98px) {
    .admin-sidebar {
        width: 100%;
        min-height: auto;
        flex: auto;
        padding: 16px;
        border-right: 0;
        border-bottom: 1px solid var(--app-border);
    }

    .sidebar-divider {
        margin: 16px 0;
    }

    .sidebar-bottom {
        display: none;
    }
}
</style>