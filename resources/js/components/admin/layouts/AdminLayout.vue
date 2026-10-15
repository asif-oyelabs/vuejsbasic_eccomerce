<script setup>
import AdminSidebar from './AdminSidebar.vue';
import AdminNavbar from './AdminNavbar.vue';
</script>

<template>
    <div class="admin-layout">
        <AdminSidebar />

        <div class="admin-main">
            <AdminNavbar />

            <main class="admin-content">
                <RouterView />
            </main>

            <footer class="admin-footer">
                <span>© {{ new Date().getFullYear() }} EasyCommerce</span>
                <span>Admin Panel</span>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.admin-layout {
    display: flex;
    min-height: 100vh;
    background: var(--app-bg);
}

.admin-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.admin-content {
    flex: 1;
    padding: 30px;
}

.admin-footer {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 30px;
    border-top: 1px solid var(--app-border);
    background: var(--app-white);
    color: var(--app-muted);
    font-size: 12px;
}

@media (max-width: 767.98px) {
    .admin-layout {
        flex-direction: column;
    }

    .admin-content {
        padding: 20px 16px;
    }

    .admin-footer {
        padding: 16px;
    }
}
</style>