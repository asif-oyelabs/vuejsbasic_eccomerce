<template>
    <main class="home-page">

        <!-- Hero Section -->
        <section class="home-hero d-flex align-items-center py-5">
            <div class="container py-4 py-lg-5">
                <div class="row align-items-center g-5">

                    <!-- Hero Content -->
                    <div class="col-lg-7 text-center text-lg-start">

                        <span
                            class="badge rounded-pill bg-app-primary-light text-app-primary px-3 py-2 mb-4"
                        >
                            <span class="me-1">✦</span>
                            Laravel 13 + Vue 3
                        </span>

                        <h1 class="display-3 fw-bold lh-sm mb-4">
                            Build Something
                            <span class="text-app-primary">
                                Amazing.
                            </span>
                        </h1>

                        <p class="lead text-app-muted lh-lg mb-4 mx-auto mx-lg-0 home-description">
                            A modern web application built with Laravel
                            and Vue.js. Manage your account, explore features,
                            and experience a clean and simple interface.
                        </p>

                        <div
                            class="d-flex justify-content-center justify-content-lg-start gap-3 flex-wrap"
                        >
                            <router-link
                                to="/login"
                                class="btn btn-success btn-lg px-4 py-3 rounded-3"
                            >
                                Get Started
                                <span class="ms-2">→</span>
                            </router-link>

                            <router-link
                                to="/register"
                                class="btn btn-outline-secondary btn-lg px-4 py-3 rounded-3"
                            >
                                Create Account
                            </router-link>
                        </div>

                        <div
                            class="d-flex align-items-center justify-content-center justify-content-lg-start gap-3 mt-4 pt-2 flex-wrap"
                        >
                            <span class="text-app-muted small">
                                <span class="text-app-primary me-1">✓</span>
                                Secure authentication
                            </span>

                            <span class="text-app-muted small">
                                <span class="text-app-primary me-1">✓</span>
                                Responsive design
                            </span>
                        </div>
                    </div>

                    <!-- Hero Visual -->
                    <div class="col-lg-5">
                        <div class="hero-visual mx-auto">
                            <div class="hero-glow"></div>

                            <div class="hero-main-card card border-0 shadow-lg rounded-4 p-4">
                                <div
                                    class="d-flex align-items-center justify-content-between mb-4"
                                >
                                    <div>
                                        <p class="text-app-muted small mb-1">
                                            YOUR WORKSPACE
                                        </p>
                                        <h5 class="fw-bold mb-0">
                                            Welcome Back!
                                        </h5>
                                    </div>

                                    <div class="hero-icon">
                                        <span>✦</span>
                                    </div>
                                </div>

                                <div class="hero-profile-card rounded-4 p-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="profile-avatar">
                                            U
                                        </div>

                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1">
                                                Your Account
                                            </h6>
                                            <p class="small text-app-muted mb-0">
                                                Everything in one place
                                            </p>
                                        </div>

                                        <span class="status-dot"></span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="feature-mini-card rounded-4 p-3 h-100">
                                            <div class="mini-icon mb-3">
                                                <span>⚡</span>
                                            </div>
                                            <h6 class="fw-bold mb-1">
                                                Fast
                                            </h6>
                                            <p class="small text-app-muted mb-0">
                                                Smooth experience
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="feature-mini-card rounded-4 p-3 h-100">
                                            <div class="mini-icon mb-3">
                                                <span>🛡</span>
                                            </div>
                                            <h6 class="fw-bold mb-1">
                                                Secure
                                            </h6>
                                            <p class="small text-app-muted mb-0">
                                                Protected access
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="hero-footer mt-4 pt-3">
                                    <div
                                        class="d-flex align-items-center justify-content-between"
                                    >
                                        <span class="small text-app-muted">
                                            Powered by
                                        </span>

                                        <div class="d-flex gap-2">
                                            <span class="tech-pill">Laravel</span>
                                            <span class="tech-pill">Vue.js</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="floating-badge floating-badge-top shadow-sm">
                                <span class="text-app-primary me-2">✓</span>
                                Easy to use
                            </div>

                            <div class="floating-badge floating-badge-bottom shadow-sm">
                                <span class="me-2">🚀</span>
                                Ready to explore
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-5">
            <div class="container py-4">

                <div class="text-center mx-auto mb-5 home-section-heading">
                    <span
                        class="badge rounded-pill bg-app-primary-light text-app-primary px-3 py-2 mb-3"
                    >
                        WHAT YOU GET
                    </span>

                    <h2 class="fw-bold mb-3">
                        Everything you need to get started
                    </h2>

                    <p class="text-app-muted lh-lg mb-0">
                        A simple and organized experience designed to make
                        managing your application easier.
                    </p>
                </div>

                <div class="row g-4">

                    <div class="col-md-4">
                        <div class="card feature-card h-100 border-0 shadow-sm rounded-4 p-4">
                            <div class="feature-icon mb-4">
                                <span>🔐</span>
                            </div>

                            <h5 class="fw-bold mb-3">
                                Secure Authentication
                            </h5>

                            <p class="text-app-muted lh-lg mb-0">
                                Sign in securely, create an account, and
                                manage your authenticated session.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card feature-card h-100 border-0 shadow-sm rounded-4 p-4">
                            <div class="feature-icon mb-4">
                                <span>👤</span>
                            </div>

                            <h5 class="fw-bold mb-3">
                                Profile Management
                            </h5>

                            <p class="text-app-muted lh-lg mb-0">
                                Update your personal information and profile
                                details through a simple interface.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card feature-card h-100 border-0 shadow-sm rounded-4 p-4">
                            <div class="feature-icon mb-4">
                                <span>📊</span>
                            </div>

                            <h5 class="fw-bold mb-3">
                                User Management
                            </h5>

                            <p class="text-app-muted lh-lg mb-0">
                                Organize user records with convenient
                                management tools and a responsive layout.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Bottom CTA -->
        <section class="pb-5">
            <div class="container pb-4">
                <div class="home-cta rounded-4 p-4 p-md-5 text-center">
                    <h2 class="fw-bold mb-3">
                        Ready to get started?
                    </h2>

                    <p class="text-app-muted mb-4">
                        Create your account and explore your application.
                    </p>

                    <router-link
                        to="/register"
                        class="btn btn-success btn-lg px-4 py-3 rounded-3"
                    >
                        Create Your Account
                        <span class="ms-2">→</span>
                    </router-link>
                </div>
            </div>
        </section>

    </main>
</template>

<style scoped>
.home-page {
    overflow: hidden;
}

.home-hero {
    position: relative;
    background:
        radial-gradient(
            circle at 85% 15%,
            var(--app-primary-light),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            var(--app-white) 0%,
            var(--app-bg) 100%
        );
}

.home-description {
    max-width: 560px;
}

.home-section-heading {
    max-width: 650px;
}

.hero-visual {
    position: relative;
    max-width: 430px;
    padding: 25px 10px;
}

.hero-glow {
    position: absolute;
    inset: 12% 5%;
    border-radius: 50%;
    background: var(--app-primary-light);
    filter: blur(35px);
    opacity: 0.9;
}

.hero-main-card {
    position: relative;
    z-index: 1;
    background: var(--app-white);
}

.hero-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--app-primary-light);
    color: var(--app-primary);
    font-size: 24px;
}

.hero-profile-card,
.feature-mini-card {
    background: var(--app-bg);
    border: 1px solid var(--app-border);
}

.profile-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 16px;
    background: var(--app-primary);
    color: var(--app-white);
    font-size: 22px;
    font-weight: 700;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--app-success);
    box-shadow: 0 0 0 4px var(--app-primary-light);
}

.mini-icon,
.feature-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: var(--app-primary-light);
    font-size: 25px;
}

.hero-footer {
    border-top: 1px solid var(--app-border);
}

.tech-pill {
    padding: 5px 10px;
    border-radius: 8px;
    background: var(--app-primary-light);
    color: var(--app-primary);
    font-size: 12px;
    font-weight: 600;
}

.floating-badge {
    position: absolute;
    z-index: 2;
    padding: 12px 16px;
    border: 1px solid var(--app-border);
    border-radius: 12px;
    background: var(--app-white);
    font-size: 13px;
    font-weight: 600;
}

.floating-badge-top {
    top: 2px;
    right: -5px;
}

.floating-badge-bottom {
    bottom: 2px;
    left: -8px;
}

.feature-card {
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.feature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 1rem 2rem rgb(31 41 55 / 8%) !important;
}

.home-cta {
    border: 1px solid var(--app-border);
    background:
        radial-gradient(
            circle at top right,
            var(--app-primary-light),
            transparent 45%
        ),
        var(--app-white);
}

@media (max-width: 575.98px) {
    .home-hero h1 {
        font-size: 2.6rem;
    }

    .floating-badge {
        padding: 9px 11px;
        font-size: 11px;
    }

    .floating-badge-top {
        right: -3px;
    }

    .floating-badge-bottom {
        left: -3px;
    }
}
</style>
