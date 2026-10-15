<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const stats = [
    {
        title: 'Total Categories',
        value: '0',
        description: 'Product categories created',
        icon: 'bi-grid-1x2',
        color: 'green',
    },
    {
        title: 'Total Products',
        value: '0',
        description: 'Products in your catalogue',
        icon: 'bi-box-seam',
        color: 'blue',
    },
    {
        title: 'Total Orders',
        value: '0',
        description: 'Orders received',
        icon: 'bi-bag-check',
        color: 'orange',
    },
    {
        title: 'Total Customers',
        value: '0',
        description: 'Registered customers',
        icon: 'bi-people',
        color: 'purple',
    },
];

const setupItems = [
    {
        title: 'Create product categories',
        description: 'Organize products into categories.',
        icon: 'bi-grid',
    },
    {
        title: 'Add your first product',
        description: 'Set product names, prices and stock.',
        icon: 'bi-box-seam',
    },
    {
        title: 'Prepare your store catalogue',
        description: 'Build the foundation of your store.',
        icon: 'bi-shop',
    },
];
</script>

<template>
    <div class="admin-page">
        <!-- Welcome section -->
        <section class="welcome-banner mb-4">
            <div class="welcome-content">
                <span class="welcome-label">
                    <i class="bi bi-stars me-2"></i>
                    STORE OVERVIEW
                </span>

                <h3 class="fw-bold mt-3 mb-2">
                    Welcome to EasyCommerce!
                </h3>

                <p class="mb-0">
                    Manage your products and build your store from one place.
                </p>
            </div>

            <div class="welcome-art d-none d-md-flex">
                <div class="art-circle art-circle-one"></div>
                <div class="art-circle art-circle-two"></div>
                <i class="bi bi-shop-window"></i>
            </div>
        </section>

        <!-- Page heading -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h5 class="fw-bold mb-1">Dashboard Overview</h5>
                <p class="text-app-muted small mb-0">
                    A quick look at your store setup.
                </p>
            </div>

            <span class="dashboard-status">
                <span class="status-dot"></span>
                Admin workspace active
            </span>
        </div>

        <!-- Summary cards -->
        <div class="row g-3 mb-4">
            <div
                v-for="stat in stats"
                :key="stat.title"
                class="col-12 col-sm-6 col-xl-3"
            >
                <div class="stat-card h-100">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <p class="stat-title mb-2">
                                {{ stat.title }}
                            </p>

                            <h3 class="stat-value mb-2">
                                {{ stat.value }}
                            </h3>
                        </div>

                        <div :class="['stat-icon', `icon-${stat.color}`]">
                            <i :class="['bi', stat.icon]"></i>
                        </div>
                    </div>

                    <div class="stat-description">
                        {{ stat.description }}
                    </div>

                    <div class="stat-bottom-line"></div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <section class="dashboard-panel h-100">
                    <div class="panel-heading">
                        <div>
                            <h6 class="fw-bold mb-1">Getting Started</h6>
                            <p class="text-app-muted small mb-0">
                                Complete these steps to prepare your store.
                            </p>
                        </div>

                        <span class="setup-badge">Setup</span>
                    </div>

                    <div class="setup-list">
                        <div
                            v-for="(item, index) in setupItems"
                            :key="item.title"
                            class="setup-item"
                        >
                            <div class="setup-number">
                                {{ String(index + 1).padStart(2, '0') }}
                            </div>

                            <div class="setup-icon">
                                <i :class="['bi', item.icon]"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-semibold setup-title">
                                    {{ item.title }}
                                </div>

                                <p class="text-app-muted small mb-0 mt-1">
                                    {{ item.description }}
                                </p>
                            </div>

                            <i class="bi bi-arrow-right setup-arrow"></i>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="dashboard-panel h-100">
                    <div class="panel-heading">
                        <div>
                            <h6 class="fw-bold mb-1">Store Management</h6>
                            <p class="text-app-muted small mb-0">
                                Your next steps
                            </p>
                        </div>

                        <i class="bi bi-lightning-charge panel-heading-icon"></i>
                    </div>

                    <div class="management-card">
                        <div class="management-icon">
                            <i class="bi bi-grid-1x2"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">Product Categories</h6>
                            <p class="text-app-muted small mb-0">
                                Organize your store catalogue.
                            </p>
                        </div>

                        <span class="coming-soon">Next</span>
                    </div>

                    <div class="management-card">
                        <div class="management-icon management-icon-blue">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">Product Master</h6>
                            <p class="text-app-muted small mb-0">
                                Manage product details and pricing.
                            </p>
                        </div>

                        <span class="coming-soon">Later</span>
                    </div>

                    <div class="store-note mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Category Master will be the next module we build.
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-page {
    color: var(--app-text);
}

.welcome-banner {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 190px;
    padding: 30px 34px;
    overflow: hidden;
    border: 1px solid var(--app-primary-light);
    border-radius: 18px;
    background: linear-gradient(
        110deg,
        var(--app-primary-light),
        var(--app-white) 85%
    );
}

.welcome-content {
    position: relative;
    z-index: 1;
    max-width: 540px;
}

.welcome-label {
    color: var(--app-primary-hover);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.welcome-content h3 {
    color: var(--app-text);
    letter-spacing: -0.7px;
}

.welcome-content p {
    color: var(--app-muted);
    font-size: 14px;
    line-height: 1.7;
}

.welcome-art {
    position: relative;
    width: 180px;
    height: 145px;
    align-items: center;
    justify-content: center;
}

.welcome-art > i {
    position: relative;
    z-index: 1;
    display: grid;
    place-items: center;
    width: 88px;
    height: 88px;
    border-radius: 24px;
    background: var(--app-primary);
    color: white;
    font-size: 42px;
    box-shadow: 0 12px 28px rgba(66, 184, 131, 0.22);
    transform: rotate(-7deg);
}

.art-circle {
    position: absolute;
    border-radius: 50%;
    background: var(--app-primary-light);
}

.art-circle-one {
    width: 135px;
    height: 135px;
    right: 10px;
}

.art-circle-two {
    width: 65px;
    height: 65px;
    top: 0;
    left: 5px;
    opacity: 0.8;
}

.dashboard-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 11px;
    border: 1px solid var(--app-border);
    border-radius: 30px;
    background: var(--app-white);
    color: var(--app-muted);
    font-size: 11px;
    font-weight: 600;
}

.status-dot {
    width: 7px;
    height: 7px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--app-success);
}

.stat-card {
    position: relative;
    padding: 22px;
    overflow: hidden;
    border: 1px solid var(--app-border);
    border-radius: 14px;
    background: var(--app-white);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(31, 41, 55, 0.06);
}

.stat-title {
    color: var(--app-muted);
    font-size: 12px;
    font-weight: 600;
}

.stat-value {
    color: var(--app-text);
    font-size: 29px;
    font-weight: 800;
    letter-spacing: -1px;
}

.stat-description {
    color: var(--app-muted);
    font-size: 11px;
}

.stat-icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 12px;
    font-size: 21px;
}

.icon-green {
    background: var(--app-primary-light);
    color: var(--app-primary-hover);
}

.icon-blue {
    background: #eaf4ff;
    color: #2879c7;
}

.icon-orange {
    background: #fff2e5;
    color: #d97706;
}

.icon-purple {
    background: #f1eaff;
    color: #7951c9;
}

.stat-bottom-line {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    height: 3px;
    background: var(--app-primary);
    opacity: 0.65;
}

.dashboard-panel {
    padding: 24px;
    border: 1px solid var(--app-border);
    border-radius: 14px;
    background: var(--app-white);
}

.panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--app-border);
}

.panel-heading h6 {
    color: var(--app-text);
}

.setup-badge,
.coming-soon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 9px;
    border-radius: 7px;
    background: var(--app-primary-light);
    color: var(--app-primary-hover);
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.setup-list {
    padding-top: 5px;
}

.setup-item {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 17px 0;
    border-bottom: 1px solid var(--app-border);
}

.setup-item:last-child {
    border-bottom: 0;
}

.setup-number {
    color: var(--app-muted);
    font-size: 10px;
    font-weight: 700;
}

.setup-icon,
.management-icon {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: 10px;
    background: var(--app-primary-light);
    color: var(--app-primary-hover);
    font-size: 18px;
}

.setup-title {
    font-size: 12px;
}

.setup-arrow {
    color: var(--app-muted);
    font-size: 14px;
}

.panel-heading-icon {
    color: var(--app-primary-hover);
    font-size: 23px;
}

.management-card {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 18px 0;
    border-bottom: 1px solid var(--app-border);
}

.management-card h6 {
    font-size: 12px;
}

.management-icon-blue {
    background: #eaf4ff;
    color: #2879c7;
}

.coming-soon {
    background: var(--app-bg);
    color: var(--app-muted);
}

.store-note {
    padding: 12px;
    border-radius: 9px;
    background: var(--app-primary-light);
    color: var(--app-primary-hover);
    font-size: 11px;
    line-height: 1.6;
}

@media (max-width: 575.98px) {
    .welcome-banner {
        min-height: 170px;
        padding: 24px 20px;
    }

    .welcome-content h3 {
        font-size: 22px;
    }

    .dashboard-panel {
        padding: 18px;
    }

    .stat-card {
        padding: 18px;
    }
}
</style>