<template>
    <footer class="app-footer mt-auto">
        <div class="container py-4">

            <div class="row align-items-center gy-3">

                <!-- Brand -->
                <div class="col-md-5 text-center text-md-start">
                    <router-link
                        to="/"
                        class="footer-brand text-decoration-none fw-bold"
                    >
                        <span class="footer-logo me-2">✦</span>
                        Laravel + Vue
                    </router-link>

                    <p class="text-app-muted small mt-2 mb-0">
                        Building modern web experiences.
                    </p>
                </div>

                <!-- Navigation -->
                <div class="col-md-7">
                    <nav
                        class="d-flex justify-content-center justify-content-md-end gap-3 gap-md-4 flex-wrap"
                    >
                        <router-link to="/" class="footer-link">
                            Home
                        </router-link>

                        <router-link to="/login" class="footer-link">
                            Login
                        </router-link>

                        <router-link to="/register" class="footer-link">
                            Register
                        </router-link>
                    </nav>
                </div>

            </div>

            <hr class="footer-divider my-4">

            <!-- Copyright -->
            <div
                class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-center"
            >
                <p class="small text-app-muted mb-0">
                    © {{ currentYear }} Laravel + Vue. All rights reserved.
                </p>

                <p class="small text-app-muted mb-0">
                    Made with
                    <span class="footer-heart">♥</span>
                    using Laravel & Vue.js
                </p>
            </div>

        </div>
    </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>

<style scoped>
.app-footer {
    background: var(--app-white);
    border-top: 1px solid var(--app-border);
}

.footer-brand {
    color: var(--app-text);
    font-size: 1.1rem;
}

.footer-brand:hover {
    color: var(--app-primary);
}

.footer-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: var(--app-primary-light);
    color: var(--app-primary);
}

.footer-link {
    color: var(--app-muted);
    font-size: 0.9rem;
    text-decoration: none;
    transition: color 0.2s ease;
}

.footer-link:hover,
.footer-link.router-link-exact-active {
    color: var(--app-primary);
}

.footer-divider {
    border-color: var(--app-border);
    opacity: 1;
}

.footer-heart {
    color: var(--app-danger);
}
</style>
