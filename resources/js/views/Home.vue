
<template>
    <main class="shop-home">
        <!-- Hero section -->
        <section class="shop-hero">
            <div class="container py-5">
                <div class="row align-items-center g-4 g-lg-5">
                    <div class="col-lg-6">
                        <span class="hero-label">
                            <i class="bi bi-stars me-2"></i>
                            YOUR EVERYDAY SHOPPING DESTINATION
                        </span>

                        <h1 class="hero-title mt-4 mb-3">
                            Find your next
                            <span>favourite thing.</span>
                        </h1>

                        <p class="hero-description mb-4">
                            Discover products you love, explore popular categories,
                            and find great picks for your everyday needs.
                        </p>

                        <div class="d-flex flex-wrap gap-3">
                            <router-link to="/productlist" class="btn shop-primary-btn">
                                Shop Products
                                <i class="bi bi-arrow-right ms-2"></i>
                            </router-link>

                            <a href="#shop-categories" class="btn shop-outline-btn">
                                Explore Categories
                            </a>
                        </div>

                        <div class="hero-benefits mt-4">
                            <span><i class="bi bi-check-circle-fill"></i> Easy shopping</span>
                            <span><i class="bi bi-grid-fill"></i> Multiple categories</span>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="hero-art">
                            <div class="hero-orb hero-orb-one"></div>
                            <div class="hero-orb hero-orb-two"></div>

                            <div class="hero-shopping-card">
                                <div class="shopping-card-top">
                                    <div>
                                        <small>YOUR NEXT FIND</small>
                                        <h5>Made for you</h5>
                                    </div>
                                    <div class="shopping-bag-icon">
                                        <i class="bi bi-bag-heart-fill"></i>
                                    </div>
                                </div>

                                <div class="shopping-illustration">
                                    <div class="illustration-circle">
                                        <i class="bi bi-bag-heart-fill"></i>
                                    </div>
                                    <div class="illustration-spark spark-one">✦</div>
                                    <div class="illustration-spark spark-two">✧</div>
                                    <div class="illustration-spark spark-three">✦</div>
                                </div>

                                <div class="shopping-card-bottom">
                                    <div>
                                        <span class="small text-muted">A little something</span>
                                        <div class="fw-bold">Just your style</div>
                                    </div>
                                    <span class="shopping-arrow">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="hero-floating-tag tag-top">
                                <i class="bi bi-heart-fill me-2"></i>
                                Find what you love
                            </div>

                            <div class="hero-floating-tag tag-bottom">
                                <i class="bi bi-bag-check-fill me-2"></i>
                                Happy shopping
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section id="shop-categories" class="section-space">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">EXPLORE THE STORE</span>
                        <h2>Shop by category</h2>
                        <p>Find the right products for what you need.</p>
                    </div>

                    <router-link to="/productlist" class="section-link">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </router-link>
                </div>

                <div v-if="categoriesLoading" class="row g-3">
                    <div v-for="n in 5" :key="n" class="col-6 col-md-4 col-lg">
                        <div class="category-skeleton"></div>
                    </div>
                </div>

                <div v-else-if="categories.length" class="row g-3">
                    <div
                        v-for="(category, index) in categories"
                        :key="category.id"
                        class="col-6 col-md-4 col-lg"
                    >
                        <router-link
                            :to="{
                                path: '/productlist',
                                query: { category_id: String(category.id) }
                            }"
                            class="category-card"
                        >
                            <div class="category-image-wrap" :class="`category-tone-${index % 5}`">
                                <img
                                    v-if="category.image_url || category.image"
                                    :src="category.image_url || category.image"
                                    :alt="category.name"
                                    class="category-image"
                                    @error="hideBrokenImage"
                                >

                                <div v-if="!category.image_url && !category.image" class="category-placeholder">
                                    <i class="bi bi-grid-1x2"></i>
                                </div>
                            </div>

                            <div class="category-card-info">
                                <h6>{{ category.name }}</h6>
                                <span>Explore <i class="bi bi-arrow-up-right"></i></span>
                            </div>
                        </router-link>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <i class="bi bi-grid fs-2"></i>
                    <p class="mb-0 mt-2">Categories will appear here soon.</p>
                </div>
            </div>
        </section>

        <!-- Latest products -->
        <section class="latest-products-section section-space">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="section-eyebrow">FRESH FINDS</span>
                        <h2>Latest products</h2>
                        <p>Take a look at what's new in our store.</p>
                    </div>

                    <router-link to="/productlist" class="section-link">
                        View all products <i class="bi bi-arrow-right ms-1"></i>
                    </router-link>
                </div>

                <div v-if="productsLoading" class="row g-4">
                    <div v-for="n in 4" :key="n" class="col-6 col-md-4 col-lg-3">
                        <div class="product-skeleton"></div>
                    </div>
                </div>

                <div v-else-if="products.length" class="row g-4">
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="col-6 col-md-4 col-lg-3"
                    >
                        <router-link
                            :to="{
                                path: '/productlist',
                                query: { search: product.name }
                            }"
                            class="product-card"
                        >
                            <div class="product-image-wrap">
                                <img
                                    v-if="product.main_image_url || product.main_image"
                                    :src="product.main_image_url || product.main_image"
                                    :alt="product.name"
                                    class="product-image"
                                    loading="lazy"
                                    @error="hideBrokenImage"
                                >

                                <div v-else class="product-image-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>

                                <span class="product-new-badge">NEW</span>

                                <span class="product-quick-icon">
                                    <i class="bi bi-arrow-up-right"></i>
                                </span>
                            </div>

                            <div class="product-card-body">
                                <span class="product-category">
                                    {{ product.category?.name || 'General' }}
                                </span>

                                <h6 class="product-name">{{ product.name }}</h6>

                                <div class="product-prices">
                                    <span class="product-price">
                                        {{ formatPrice(product.price) }}
                                    </span>

                                    <span
                                        v-if="Number(product.original_price) > Number(product.price)"
                                        class="product-original-price"
                                    >
                                        {{ formatPrice(product.original_price) }}
                                    </span>
                                </div>
                            </div>
                        </router-link>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <i class="bi bi-bag fs-2"></i>
                    <p class="mb-0 mt-2">New products are coming soon.</p>
                </div>

                <div class="text-center mt-5">
                    <router-link to="/productlist" class="btn shop-primary-btn">
                        Explore All Products
                        <i class="bi bi-arrow-right ms-2"></i>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Shopping CTA -->
        <section class="container pb-5">
            <div class="shopping-cta">
                <div>
                    <span class="section-eyebrow">YOUR NEXT DISCOVERY</span>
                    <h2>Something good is waiting for you.</h2>
                    <p class="mb-0">Explore our collection and find your next favourite.</p>
                </div>

                <router-link to="/productlist" class="btn cta-light-btn">
                    Start Exploring <i class="bi bi-arrow-right ms-2"></i>
                </router-link>
            </div>
        </section>
    </main>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const categories = ref([]);
const products = ref([]);

const categoriesLoading = ref(true);
const productsLoading = ref(true);

async function loadCategories() {
    categoriesLoading.value = true;

    try {
        const response = await axios.get('/api/home/categories', {
            params: {
                per_page: 5,
                sort: 'latest',
            },
        });

        const result = response.data?.data;

        const items = Array.isArray(result)
            ? result
            : (result?.data ?? []);

        categories.value = items.slice(0, 5);
    } catch (error) {
        console.error('Unable to load homepage categories:', error);
        categories.value = [];
    } finally {
        categoriesLoading.value = false;
    }
}

async function loadLatestProducts() {
    productsLoading.value = true;

    try {
        const response = await axios.get('/api/home/products', {
            params: {
                per_page: 8,
                sort: 'latest',
            },
        });

        const result = response.data?.data;

        const items = Array.isArray(result)
            ? result
            : (result?.data ?? []);

        products.value = items.slice(0, 8);
    } catch (error) {
        console.error('Unable to load homepage products:', error);
        products.value = [];
    } finally {
        productsLoading.value = false;
    }
}

function formatPrice(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function hideBrokenImage(event) {
    event.target.style.display = 'none';
}

onMounted(() => {
    loadCategories();
    loadLatestProducts();
});
</script>

<style scoped>
.shop-home {
    color: var(--app-text);
    background: var(--app-white);
}

.shop-hero {
    overflow: hidden;
    background:
        radial-gradient(circle at 85% 20%, #dff7e9 0, transparent 28%),
        linear-gradient(135deg, #ffffff 0%, #f3fbf6 100%);
}

.hero-label,
.section-eyebrow {
    display: inline-block;
    color: var(--app-primary-hover);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.hero-label {
    padding: 10px 14px;
    border: 1px solid #cceedd;
    border-radius: 30px;
    background: #ffffff;
}

.hero-title {
    max-width: 650px;
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    line-height: 1.08;
    letter-spacing: -2px;
    font-weight: 800;
}

.hero-title span {
    display: block;
    color: var(--app-primary);
}

.hero-description {
    max-width: 530px;
    color: var(--app-muted);
    font-size: 1.08rem;
    line-height: 1.8;
}

.shop-primary-btn {
    padding: 13px 23px;
    border-radius: 10px;
    color: white;
    background: var(--app-primary);
    border: 1px solid var(--app-primary);
    font-weight: 700;
}

.shop-primary-btn:hover {
    color: white;
    background: var(--app-primary-hover);
    border-color: var(--app-primary-hover);
}

.shop-outline-btn {
    padding: 13px 20px;
    border: 1px solid var(--app-border);
    border-radius: 10px;
    background: white;
    color: var(--app-text);
    font-weight: 600;
}

.shop-outline-btn:hover {
    border-color: var(--app-primary);
    color: var(--app-primary-hover);
}

.hero-benefits {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    color: var(--app-muted);
    font-size: 0.85rem;
}

.hero-benefits i {
    margin-right: 5px;
    color: var(--app-primary);
}

.hero-art {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 420px;
}

.hero-orb {
    position: absolute;
    border-radius: 50%;
}

.hero-orb-one {
    width: 330px;
    height: 330px;
    background: #d9f3e4;
}

.hero-orb-two {
    width: 240px;
    height: 240px;
    border: 1px solid #bce8cf;
}

.hero-shopping-card {
    position: relative;
    z-index: 1;
    width: min(360px, 88%);
    padding: 24px;
    border: 1px solid rgb(255 255 255 / 90%);
    border-radius: 24px;
    background: white;
    box-shadow: 0 25px 70px rgb(28 89 56 / 13%);
    transform: rotate(-2deg);
}

.shopping-card-top,
.shopping-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.shopping-card-top small {
    color: var(--app-muted);
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 1px;
}

.shopping-card-top h5 {
    margin: 5px 0 0;
    font-weight: 800;
}

.shopping-bag-icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    border-radius: 15px;
    color: var(--app-primary-hover);
    background: var(--app-primary-light);
    font-size: 1.4rem;
}

.shopping-illustration {
    position: relative;
    display: grid;
    place-items: center;
    height: 220px;
    margin: 18px 0;
    overflow: hidden;
    border-radius: 18px;
    background: linear-gradient(140deg, #e8f7f0, #f7fcf8);
}

.illustration-circle {
    display: grid;
    place-items: center;
    width: 135px;
    height: 135px;
    border-radius: 40px;
    color: white;
    background: linear-gradient(145deg, #54c58e, #25875b);
    box-shadow: 0 18px 35px rgb(37 135 91 / 25%);
    font-size: 4rem;
    transform: rotate(-8deg);
}

.illustration-spark {
    position: absolute;
    color: var(--app-primary);
    font-size: 2rem;
}

.spark-one {
    top: 25px;
    left: 28px;
}

.spark-two {
    right: 32px;
    top: 45px;
}

.spark-three {
    bottom: 25px;
    right: 48px;
}

.shopping-card-bottom {
    padding-top: 14px;
    border-top: 1px solid var(--app-border);
}

.shopping-arrow {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    color: white;
    background: var(--app-primary);
}

.hero-floating-tag {
    position: absolute;
    z-index: 2;
    padding: 12px 15px;
    border: 1px solid var(--app-border);
    border-radius: 12px;
    background: white;
    box-shadow: 0 8px 25px rgb(0 0 0 / 7%);
    font-size: 0.8rem;
    font-weight: 700;
}

.hero-floating-tag i {
    color: var(--app-primary);
}

.tag-top {
    top: 65px;
    right: 0;
}

.tag-bottom {
    bottom: 55px;
    left: 0;
}

.section-space {
    padding: 70px 0;
}

.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 30px;
}

.section-heading h2 {
    margin: 8px 0;
    font-size: clamp(1.6rem, 3vw, 2.2rem);
    font-weight: 800;
    letter-spacing: -0.7px;
}

.section-heading p {
    margin: 0;
    color: var(--app-muted);
}

.section-link {
    flex-shrink: 0;
    color: var(--app-primary-hover);
    text-decoration: none;
    font-weight: 700;
}

.section-link:hover {
    color: var(--app-primary);
}

.category-card {
    display: block;
    height: 100%;
    padding: 10px;
    border: 1px solid var(--app-border);
    border-radius: 16px;
    color: inherit;
    background: white;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.category-card:hover {
    color: inherit;
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgb(31 41 55 / 8%);
}

.category-image-wrap {
    display: grid;
    place-items: center;
    height: 125px;
    overflow: hidden;
    border-radius: 11px;
}

.category-tone-0 {
    background: #e8f7f0;
}

.category-tone-1 {
    background: #fff2e5;
}

.category-tone-2 {
    background: #eaf0ff;
}

.category-tone-3 {
    background: #f7eafb;
}

.category-tone-4 {
    background: #fff5d9;
}

.category-image {
    width: 140px;
    height: 140px;
    padding: 10px;
    object-fit: contain;
}

.category-placeholder {
    color: var(--app-primary);
    font-size: 2.3rem;
}

.category-card-info {
    padding: 14px 4px 5px;
}

.category-card-info h6 {
    overflow: hidden;
    margin-bottom: 8px;
    font-weight: 800;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.category-card-info span {
    color: var(--app-muted);
    font-size: 0.78rem;
}

.category-card-info span i {
    margin-left: 4px;
    color: var(--app-primary);
}

.latest-products-section {
    background: var(--app-bg);
}

.product-card {
    display: block;
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--app-border);
    border-radius: 16px;
    background: white;
    color: inherit;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    color: inherit;
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgb(31 41 55 / 9%);
}

.product-image-wrap {
    position: relative;
    display: grid;
    place-items: center;
    height: 235px;
    overflow: hidden;
    background: #f7f9f8;
}

.product-image {
    width: 100%;
    height: 100%;
    padding: 15px;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.04);
}

.product-image-placeholder {
    color: #b7c6bd;
    font-size: 3rem;
}

.product-new-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 5px 8px;
    border-radius: 6px;
    color: var(--app-primary-hover);
    background: var(--app-primary-light);
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.product-quick-icon {
    position: absolute;
    right: 12px;
    bottom: 12px;
    display: grid;
    place-items: center;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    color: var(--app-text);
    background: white;
    box-shadow: 0 3px 12px rgb(0 0 0 / 8%);
}

.product-card-body {
    padding: 16px;
}

.product-category {
    display: block;
    overflow: hidden;
    margin-bottom: 7px;
    color: var(--app-muted);
    font-size: 0.75rem;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-name {
    display: -webkit-box;
    min-height: 42px;
    overflow: hidden;
    margin-bottom: 12px;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.45;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.product-prices {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.product-price {
    color: var(--app-primary-hover);
    font-size: 1.05rem;
    font-weight: 800;
}

.product-original-price {
    color: var(--app-muted);
    font-size: 0.8rem;
    text-decoration: line-through;
}

.category-skeleton,
.product-skeleton {
    border-radius: 16px;
    background: linear-gradient(90deg, #edf1ee 25%, #f8faf8 50%, #edf1ee 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.4s infinite;
}

.category-skeleton {
    height: 205px;
}

.product-skeleton {
    height: 340px;
}

@keyframes skeleton-loading {
    to {
        background-position: -200% 0;
    }
}

.empty-state {
    padding: 50px 20px;
    border: 1px dashed var(--app-border);
    border-radius: 16px;
    color: var(--app-muted);
    text-align: center;
    background: white;
}

.shopping-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    padding: 42px;
    border-radius: 22px;
    background:
        radial-gradient(circle at 90% 10%, #d7f2e3 0, transparent 35%),
        #f1faf5;
    border: 1px solid #dcefe3;
}

.shopping-cta h2 {
    margin: 10px 0;
    font-size: clamp(1.5rem, 3vw, 2.3rem);
    font-weight: 800;
}

.shopping-cta p {
    color: var(--app-muted);
}

.cta-light-btn {
    flex-shrink: 0;
    padding: 13px 20px;
    border-radius: 10px;
    color: white;
    background: var(--app-primary);
    font-weight: 700;
}

.cta-light-btn:hover {
    color: white;
    background: var(--app-primary-hover);
}

@media (max-width: 767.98px) {
    .hero-art {
        min-height: 360px;
    }

    .hero-orb-one {
        width: 280px;
        height: 280px;
    }

    .hero-orb-two {
        width: 210px;
        height: 210px;
    }

    .hero-shopping-card {
        padding: 18px;
    }

    .shopping-illustration {
        height: 175px;
    }

    .section-space {
        padding: 48px 0;
    }

    .category-image-wrap {
        height: 110px;
    }

    .product-image-wrap {
        height: 190px;
    }

    .product-card-body {
        padding: 12px;
    }

    .shopping-cta {
        align-items: flex-start;
        flex-direction: column;
        padding: 28px;
    }
}

@media (max-width: 400px) {
    .hero-title {
        letter-spacing: -1px;
    }

    .product-image-wrap {
        height: 155px;
    }

    .section-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .hero-floating-tag {
        font-size: 0.7rem;
    }
}
</style>
