
<template>
    <main class="products-page">
        <div class="container py-4 py-lg-5">
            <!-- Page heading -->
            <div class="products-heading mb-4">
                <div>
                    <span class="products-eyebrow">EXPLORE OUR COLLECTION</span>
                    <h1 class="fw-bold mt-2 mb-2">All Products</h1>
                    <p class="text-muted mb-0">
                        Find the products you love at prices that work for you.
                    </p>
                </div>

                <div class="results-count">
                    <i class="bi bi-box-seam me-2"></i>
                    {{ pagination.total }} products
                </div>
            </div>

            <!-- Search bar -->
            <div class="search-panel mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <input
                        v-model="filters.search"
                        type="search"
                        class="form-control"
                        placeholder="Search products by name..."
                        @input="handleSearch"
                    >

                    <button
                        v-if="filters.search"
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="clearSearch"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Mobile filter toggle -->
            <button
                class="btn mobile-filter-button d-lg-none w-100 mb-3"
                type="button"
                @click="showMobileFilters = !showMobileFilters"
            >
                <i class="bi bi-sliders me-2"></i>
                {{ showMobileFilters ? 'Hide Filters' : 'Show Filters' }}
                <i
                    class="bi ms-auto"
                    :class="showMobileFilters ? 'bi-chevron-up' : 'bi-chevron-down'"
                ></i>
            </button>

            <div class="row g-4">
                <!-- Sidebar filters -->
                <aside
                    class="col-lg-3"
                    :class="{ 'd-none d-lg-block': !showMobileFilters }"
                >
                    <div class="filter-sidebar">
                        <div class="filter-header">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-sliders me-2"></i>Filters
                            </h5>

                            <button
                                type="button"
                                class="btn btn-link btn-sm text-decoration-none"
                                @click="resetFilters"
                            >
                                Reset
                            </button>
                        </div>

                        <!-- Categories -->
                        <div class="filter-section">
                            <h6 class="filter-title">Categories</h6>

                            <div v-if="categoriesLoading" class="small text-muted">
                                Loading categories...
                            </div>

                            <div v-else class="category-filter-list">
                                <label class="category-filter-option">
                                    <input
                                        v-model="filters.category_id"
                                        type="radio"
                                        name="productCategory"
                                        value=""
                                        @change="applyFilters"
                                    >
                                    <span>All Categories</span>
                                    <span class="category-check">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>

                                <label
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="category-filter-option"
                                >
                                    <input
                                        v-model="filters.category_id"
                                        type="radio"
                                        name="productCategory"
                                        :value="String(category.id)"
                                        @change="applyFilters"
                                    >
                                    <span>{{ category.name }}</span>
                                    <span class="category-check">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>

                                <p
                                    v-if="!categories.length && !categoriesLoading"
                                    class="small text-muted mb-0"
                                >
                                    No categories available.
                                </p>
                            </div>
                        </div>

                        <!-- Price filter -->
                        <div class="filter-section">
                            <h6 class="filter-title">Price Range</h6>

                            <p class="small text-muted mb-3">
                                Enter your minimum and maximum budget.
                            </p>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="minPrice" class="small text-muted mb-1">
                                        Min Price (₹)
                                    </label>

                                    <input
                                        id="minPrice"
                                        v-model="filters.min_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        placeholder="0"
                                        @input="priceError = ''"
                                    >
                                </div>

                                <div class="col-6">
                                    <label for="maxPrice" class="small text-muted mb-1">
                                        Max Price (₹)
                                    </label>

                                    <input
                                        id="maxPrice"
                                        v-model="filters.max_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control"
                                        placeholder="Any"
                                        @input="priceError = ''"
                                    >
                                </div>
                            </div>

                            <p v-if="priceError" class="small text-danger mt-2 mb-0">
                                {{ priceError }}
                            </p>

                            <button
                                type="button"
                                class="btn apply-price-btn w-100 mt-3"
                                @click="applyPriceFilter"
                            >
                                Apply Price
                            </button>
                        </div>

                        <div class="filter-section filter-last-section">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-success fs-5"></i>
                                <div>
                                    <div class="fw-semibold small">Find your next favourite</div>
                                    <div class="text-muted small">Explore our full collection.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Products area -->
                <section class="col-lg-9">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">
                                {{ selectedCategoryName || 'All Products' }}
                            </h5>

                            <p class="small text-muted mb-0">
                                Showing {{ pagination.from ?? 0 }}–{{ pagination.to ?? 0 }}
                                of {{ pagination.total }} products
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            @click="resetFilters"
                        >
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Clear Filters
                        </button>
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="row g-3 g-md-4">
                        <div
                            v-for="n in 6"
                            :key="n"
                            class="col-6 col-md-4"
                        >
                            <div class="product-skeleton"></div>
                        </div>
                    </div>

                    <!-- Product grid -->
                    <div v-else-if="products.length" class="row g-3 g-md-4">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="col-6 col-md-4"
                        >
                            <article class="product-card">
                                <div class="product-image-wrap">
                                    <img
                                        v-if="product.main_image_url"
                                        :src="product.main_image_url"
                                        :alt="product.name"
                                        class="product-image"
                                        loading="lazy"
                                        @error="hideBrokenImage"
                                    >

                                    <div
                                        v-else
                                        class="product-image-placeholder"
                                    >
                                        <i class="bi bi-image"></i>
                                    </div>

                                    <span class="product-category-badge">
                                        {{ product.category?.name || 'General' }}
                                    </span>
                                </div>

                                <div class="product-card-body">
                                    <h6 class="product-name">
                                        {{ product.name }}
                                    </h6>

                                    <div class="product-price-row">
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

                                    <div class="product-stock mt-2">
                                        <span
                                            class="stock-dot"
                                            :class="product.qty > 0 ? 'in-stock' : 'out-stock'"
                                        ></span>

                                        {{ product.qty > 0 ? 'In stock' : 'Out of stock' }}
                                    </div>
                                    <router-link
                                        :to="{ name: 'productdetail', params: { slug: product.slug } }"
                                        class="btn view-product-btn w-100 mt-3"
                                    >
                                        View Product
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </router-link>
                                </div>
                            </article>
                        </div>
                    </div>

                    <!-- Empty results -->
                    <div v-else class="empty-products">
                        <div class="empty-products-icon">
                            <i class="bi bi-search"></i>
                        </div>

                        <h5 class="fw-bold mt-3">No products found</h5>

                        <p class="text-muted">
                            Try another search term, category or price range.
                        </p>

                        <button
                            type="button"
                            class="btn apply-price-btn"
                            @click="resetFilters"
                        >
                            Clear All Filters
                        </button>
                    </div>

                    <!-- Pagination -->
                    <nav
                        v-if="!loading && pagination.last_page > 1"
                        class="mt-4"
                        aria-label="Products pagination"
                    >
                        <ul class="pagination justify-content-center flex-wrap">
                            <li
                                class="page-item"
                                :class="{ disabled: pagination.current_page <= 1 }"
                            >
                                <button
                                    class="page-link"
                                    :disabled="pagination.current_page <= 1"
                                    @click="loadProducts(pagination.current_page - 1)"
                                >
                                    Previous
                                </button>
                            </li>

                            <li
                                v-for="page in visiblePages"
                                :key="page"
                                class="page-item"
                                :class="{ active: page === pagination.current_page }"
                            >
                                <button
                                    class="page-link"
                                    @click="loadProducts(page)"
                                >
                                    {{ page }}
                                </button>
                            </li>

                            <li
                                class="page-item"
                                :class="{
                                    disabled: pagination.current_page >= pagination.last_page
                                }"
                            >
                                <button
                                    class="page-link"
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    @click="loadProducts(pagination.current_page + 1)"
                                >
                                    Next
                                </button>
                            </li>
                        </ul>
                    </nav>
                </section>
            </div>
        </div>
    </main>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const products = ref([]);
const categories = ref([]);

const loading = ref(false);
const categoriesLoading = ref(false);
const showMobileFilters = ref(false);
const priceError = ref('');

const filters = reactive({
    search: '',
    category_id: '',
    min_price: '',
    max_price: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
});

let searchTimer = null;
let requestSequence = 0;

const selectedCategoryName = computed(() => {
    const category = categories.value.find(
        item => String(item.id) === String(filters.category_id)
    );

    return category?.name || '';
});

const visiblePages = computed(() => {
    const current = pagination.current_page;
    const last = pagination.last_page;
    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    return Array.from(
        { length: end - start + 1 },
        (_, index) => start + index
    );
});

async function loadCategories() {
    categoriesLoading.value = true;

    try {
        const response = await axios.get('/api/product-categories');
        const data = response.data?.data;

        categories.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Unable to load categories:', error);
        categories.value = [];
    } finally {
        categoriesLoading.value = false;
    }
}

async function loadProducts(page = 1) {
    const currentRequest = ++requestSequence;
    loading.value = true;

    try {
        const params = {
            page,
            per_page: 12,
        };

        if (filters.search.trim()) {
            params.search = filters.search.trim();
        }

        if (filters.category_id !== '') {
            params.category_id = filters.category_id;
        }

        if (filters.min_price !== '') {
            params.min_price = filters.min_price;
        }

        if (filters.max_price !== '') {
            params.max_price = filters.max_price;
        }

        const response = await axios.get('/api/products', { params });

        // Ignore old responses when filters change quickly.
        if (currentRequest !== requestSequence) return;

        const result = response.data?.data;

        products.value = Array.isArray(result?.data) ? result.data : [];

        Object.assign(pagination, {
            current_page: result?.current_page ?? 1,
            last_page: result?.last_page ?? 1,
            from: result?.from ?? 0,
            to: result?.to ?? 0,
            total: result?.total ?? 0,
        });
    } catch (error) {
        if (currentRequest !== requestSequence) return;

        console.error('Unable to load products:', error);
        products.value = [];
        Object.assign(pagination, {
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
        });
    } finally {
        if (currentRequest === requestSequence) {
            loading.value = false;
        }
    }
}

function handleSearch() {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        loadProducts(1);
    }, 350);
}

function clearSearch() {
    clearTimeout(searchTimer);
    filters.search = '';
    loadProducts(1);
}

function applyFilters() {
    priceError.value = '';
    loadProducts(1);
}

function applyPriceFilter() {
    const min = filters.min_price === '' ? null : Number(filters.min_price);
    const max = filters.max_price === '' ? null : Number(filters.max_price);

    if (min !== null && (!Number.isFinite(min) || min < 0)) {
        priceError.value = 'Minimum price must be zero or greater.';
        return;
    }

    if (max !== null && (!Number.isFinite(max) || max < 0)) {
        priceError.value = 'Maximum price must be zero or greater.';
        return;
    }

    if (min !== null && max !== null && min > max) {
        priceError.value = 'Minimum price cannot exceed maximum price.';
        return;
    }

    priceError.value = '';
    loadProducts(1);
}

function resetFilters() {
    clearTimeout(searchTimer);

    filters.search = '';
    filters.category_id = '';
    filters.min_price = '';
    filters.max_price = '';
    priceError.value = '';

    router.replace({ path: '/productlist' });
    loadProducts(1);
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

function syncFiltersFromRoute() {
    filters.search = String(route.query.search ?? '');
    filters.category_id = String(route.query.category_id ?? '');
    filters.min_price = String(route.query.min_price ?? '');
    filters.max_price = String(route.query.max_price ?? '');

    applyPriceFilter();

    if (!priceError.value) {
        loadProducts(1);
    }
}

watch(
    () => [
        route.query.search,
        route.query.category_id,
        route.query.min_price,
        route.query.max_price,
    ],
    syncFiltersFromRoute
);

onMounted(() => {
    loadCategories();

    // Read query parameters when navigating from a category or homepage.
    filters.search = String(route.query.search ?? '');
    filters.category_id = String(route.query.category_id ?? '');
    filters.min_price = String(route.query.min_price ?? '');
    filters.max_price = String(route.query.max_price ?? '');

    applyPriceFilter();

    if (!priceError.value) {
        loadProducts(1);
    }
});

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
    requestSequence++;
});
</script>

<style scoped>
.view-product-btn {
    padding: 10px 12px;
    border: 1px solid var(--app-primary);
    border-radius: 9px;
    color: var(--app-primary-hover);
    background: white;
    font-weight: 700;
    transition: all 0.2s ease;
}

.view-product-btn:hover {
    color: white;
    background: var(--app-primary);
}
.products-page {
    min-height: 70vh;
    background: var(--app-bg);
    color: var(--app-text);
}

.products-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
}

.products-eyebrow {
    color: var(--app-primary-hover);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.products-heading h1 {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    letter-spacing: -1px;
}

.results-count {
    padding: 10px 14px;
    border: 1px solid var(--app-border);
    border-radius: 10px;
    background: white;
    color: var(--app-muted);
    font-size: 0.875rem;
    white-space: nowrap;
}

.search-panel {
    padding: 8px;
    border: 1px solid var(--app-border);
    border-radius: 14px;
    background: white;
    box-shadow: 0 4px 18px rgb(31 41 55 / 3%);
}

.search-panel .input-group-text {
    padding-left: 14px;
    border: 0;
    background: white;
    color: var(--app-muted);
}

.search-panel .form-control {
    min-height: 46px;
    border: 0;
    box-shadow: none;
}

.filter-sidebar {
    position: sticky;
    top: 20px;
    overflow: hidden;
    border: 1px solid var(--app-border);
    border-radius: 15px;
    background: white;
}

.filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 19px;
    border-bottom: 1px solid var(--app-border);
}

.filter-header h5 {
    font-size: 1rem;
}

.filter-header .btn-link {
    color: var(--app-primary-hover);
}

.filter-section {
    padding: 20px;
    border-bottom: 1px solid var(--app-border);
}

.filter-last-section {
    border-bottom: 0;
}

.filter-title {
    margin-bottom: 16px;
    font-size: 0.9rem;
    font-weight: 800;
}

.category-filter-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.category-filter-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    color: var(--app-muted);
    font-size: 0.875rem;
    cursor: pointer;
    transition: background 0.15s ease;
}

.category-filter-option:hover {
    background: var(--app-bg);
}

.category-filter-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.category-check {
    display: grid;
    place-items: center;
    width: 19px;
    height: 19px;
    margin-left: auto;
    border: 1px solid var(--app-border);
    border-radius: 50%;
    color: white;
    font-size: 0.75rem;
}

.category-filter-option:has(input:checked) {
    color: var(--app-primary-hover);
    background: var(--app-primary-light);
    font-weight: 700;
}

.category-filter-option input:checked + span + .category-check {
    border-color: var(--app-primary);
    background: var(--app-primary);
}

.filter-section .form-control {
    min-width: 0;
    min-height: 42px;
    border-color: var(--app-border);
    font-size: 0.875rem;
}

.filter-section .form-control:focus {
    border-color: var(--app-primary);
    box-shadow: 0 0 0 0.2rem rgb(66 184 131 / 14%);
}

.apply-price-btn {
    padding: 10px 14px;
    border: 1px solid var(--app-primary);
    border-radius: 9px;
    color: white;
    background: var(--app-primary);
    font-weight: 700;
}

.apply-price-btn:hover {
    color: white;
    border-color: var(--app-primary-hover);
    background: var(--app-primary-hover);
}

.mobile-filter-button {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border: 1px solid var(--app-border);
    border-radius: 10px;
    background: white;
    color: var(--app-text);
    font-weight: 700;
}

.product-card {
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--app-border);
    border-radius: 14px;
    background: white;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgb(31 41 55 / 8%);
}

.product-image-wrap {
    position: relative;
    display: grid;
    place-items: center;
    height: 220px;
    overflow: hidden;
    background: #f5f8f6;
}

.product-image {
    width: 100%;
    height: 100%;
    padding: 14px;
    object-fit: contain;
    transition: transform 0.25s ease;
}

.product-card:hover .product-image {
    transform: scale(1.04);
}

.product-image-placeholder {
    color: #b7c6bd;
    font-size: 3rem;
}

.product-category-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    max-width: calc(100% - 20px);
    overflow: hidden;
    padding: 5px 8px;
    border-radius: 6px;
    color: var(--app-primary-hover);
    background: white;
    font-size: 0.68rem;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-card-body {
    padding: 14px;
}

.product-name {
    display: -webkit-box;
    min-height: 42px;
    overflow: hidden;
    margin-bottom: 12px;
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.45;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.product-price-row {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 7px;
}

.product-price {
    color: var(--app-primary-hover);
    font-size: 1rem;
    font-weight: 800;
}

.product-original-price {
    color: var(--app-muted);
    font-size: 0.78rem;
    text-decoration: line-through;
}

.product-stock {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--app-muted);
    font-size: 0.75rem;
}

.stock-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

.in-stock {
    background: var(--app-success);
}

.out-stock {
    background: var(--app-danger);
}

.product-skeleton {
    height: 330px;
    border-radius: 14px;
    background: linear-gradient(90deg, #edf1ee 25%, #f8faf8 50%, #edf1ee 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.4s infinite;
}

@keyframes skeleton-loading {
    to {
        background-position: -200% 0;
    }
}

.empty-products {
    padding: 65px 20px;
    border: 1px dashed var(--app-border);
    border-radius: 15px;
    background: white;
    text-align: center;
}

.empty-products-icon {
    display: inline-grid;
    place-items: center;
    width: 65px;
    height: 65px;
    border-radius: 50%;
    color: var(--app-primary-hover);
    background: var(--app-primary-light);
    font-size: 1.6rem;
}

.page-link {
    color: var(--app-primary-hover);
}

.page-item.active .page-link {
    border-color: var(--app-primary);
    background: var(--app-primary);
    color: white;
}

.page-item.disabled .page-link {
    pointer-events: none;
}

@media (max-width: 991.98px) {
    .filter-sidebar {
        position: static;
    }
}

@media (max-width: 575.98px) {
    .products-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .results-count {
        padding: 7px 10px;
    }

    .product-image-wrap {
        height: 165px;
    }

    .product-card-body {
        padding: 10px;
    }

    .product-name {
        font-size: 0.85rem;
    }

    .product-price {
        font-size: 0.9rem;
    }
}
</style>
