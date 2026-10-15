
<template>
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1 fw-bold">Product Master</h4>
                <p class="text-muted mb-0">Manage products, pricing, stock and images.</p>
            </div>

            <button class="btn btn-primary" @click="openCreateModal">
                <i class="bi bi-plus-lg me-2"></i>Add Product
            </button>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label">Search product</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                v-model="filters.search"
                                type="search"
                                class="form-control"
                                placeholder="Search by name or slug..."
                                @input="handleSearch"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label">Category</label>
                        <select
                            v-model="filters.category_id"
                            class="form-select"
                            @change="applyFilters"
                        >
                            <option value="">All categories</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="String(category.id)"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label">Status</label>
                        <select
                            v-model="filters.status"
                            class="form-select"
                            @change="applyFilters"
                        >
                            <option value="">All statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <button
                            class="btn btn-outline-secondary w-100"
                            @click="resetFilters"
                        >
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0 fw-bold">Products</h6>

                    <div class="d-flex align-items-center gap-2">
                        <label for="perPage" class="small text-muted">Show</label>
                        <select
                            id="perPage"
                            v-model.number="perPage"
                            class="form-select form-select-sm"
                            style="width: 85px"
                            @change="loadProducts(1)"
                        >
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-success" role="status"></div>
                <p class="text-muted mt-2 mb-0">Loading products...</p>
            </div>

            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 70px">Image</th>
                            <th style="min-width: 190px">Product</th>
                            <th style="min-width: 140px">Category</th>
                            <th>Original Price</th>
                            <th>Selling Price</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th class="text-end" style="min-width: 110px">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="product in products" :key="product.id">
                            <td>
                                <img
                                    v-if="product.main_image_url"
                                    :src="product.main_image_url"
                                    :alt="product.name"
                                    class="product-thumbnail"
                                >
                                <div v-else class="product-thumbnail image-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">{{ product.name }}</div>
                                <small class="text-muted">{{ product.slug }}</small>
                            </td>

                            <td>{{ product.category?.name || '—' }}</td>

                            <td>{{ formatPrice(product.original_price) }}</td>

                            <td class="fw-semibold">
                                {{ formatPrice(product.price) }}
                            </td>

                            <td>{{ product.qty }}</td>

                            <td>
                                <span
                                    class="badge"
                                    :class="product.status ? 'text-bg-success' : 'text-bg-secondary'"
                                >
                                    {{ product.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-end">
                                <button
                                    class="btn btn-sm btn-outline-primary me-1"
                                    title="Edit product"
                                    @click="openEditModal(product)"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    title="Delete product"
                                    @click="deleteProduct(product)"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <tr v-if="products.length === 0">
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-box-seam fs-2 text-muted"></i>
                                <p class="fw-semibold mt-2 mb-1">No products found</p>
                                <p class="text-muted small mb-0">
                                    Try changing the filters or add a new product.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="!loading && pagination.last_page > 0"
                class="card-footer bg-white border-0 py-3"
            >
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <small class="text-muted">
                        Showing {{ pagination.from ?? 0 }} to
                        {{ pagination.to ?? 0 }} of
                        {{ pagination.total }} products
                    </small>

                    <nav aria-label="Product pagination">
                        <ul class="pagination pagination-sm mb-0">
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
                                <button class="page-link" @click="loadProducts(page)">
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
                </div>
            </div>
        </div>

        <ProductFormModal
            :show="showModal"
            :product="selectedProduct"
            :categories="categories"
            :saving="saving"
            @close="closeModal"
            @submit="saveProduct"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import ProductFormModal from '../components/ProductFormModal.vue';
import { showAdminToast } from '../../composables/useAdminToast';

const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const selectedProduct = ref(null);
const perPage = ref(10);

const filters = reactive({
    search: '',
    category_id: '',
    status: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
});

let searchTimer = null;

const visiblePages = computed(() => {
    const current = pagination.current_page;
    const last = pagination.last_page;
    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
});

async function loadCategories() {
    try {
        const response = await axios.get('/api/admin/categories', {
            params: { per_page: 100 },
        });

        const result = response.data?.data;
        categories.value = Array.isArray(result)
            ? result
            : (result?.data ?? []);
    } catch (error) {
        showAdminToast(
            error.response?.data?.message || 'Unable to load categories.',
            'error'
        );
    }
}

async function loadProducts(page = 1) {
    loading.value = true;

    try {
        const params = {
            page,
            per_page: perPage.value,
        };

        if (filters.search.trim()) {
            params.search = filters.search.trim();
        }

        if (filters.category_id !== '') {
            params.category_id = filters.category_id;
        }

        if (filters.status !== '') {
            params.status = filters.status;
        }

        const response = await axios.get('/api/admin/products', { params });
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
        showAdminToast(
            error.response?.data?.message || 'Unable to load products.',
            'error'
        );
    } finally {
        loading.value = false;
    }
}

function handleSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadProducts(1), 350);
}

function applyFilters() {
    loadProducts(1);
}

function resetFilters() {
    filters.search = '';
    filters.category_id = '';
    filters.status = '';
    loadProducts(1);
}

function openCreateModal() {
    selectedProduct.value = null;
    showModal.value = true;
}

function openEditModal(product) {
    selectedProduct.value = product;
    showModal.value = true;
}

function closeModal() {
    if (!saving.value) {
        showModal.value = false;
        selectedProduct.value = null;
    }
}

async function saveProduct(formData) {
    saving.value = true;

    try {
        await axios.get('/sanctum/csrf-cookie');

        const productId = selectedProduct.value?.id;

        if (productId) {
            formData.append('_method', 'PUT');

            await axios.post(
                `/api/admin/products/${productId}`,
                formData
            );

            showAdminToast('Product updated successfully.', 'success');
        } else {
            await axios.post('/api/admin/products', formData);

            showAdminToast('Product created successfully.', 'success');
        }

        showModal.value = false;
        selectedProduct.value = null;
        await loadProducts(productId ? pagination.current_page : 1);
    } catch (error) {
        const message = error.response?.data?.message
            || 'Unable to save product. Please try again.';

        if (error.response?.status === 422) {
            const errors = error.response.data.errors || {};
            showAdminToast(
                Object.values(errors).flat()[0] || message,
                'error'
            );
        } else {
            showAdminToast(message, 'error');
        }
    } finally {
        saving.value = false;
    }
}

async function deleteProduct(product) {
    if (!window.confirm(`Are you sure you want to delete "${product.name}"?`)) {
        return;
    }

    try {
        await axios.get('/sanctum/csrf-cookie');

        await axios.delete(`/api/admin/products/${product.id}`);

        showAdminToast('Product deleted successfully.', 'success');

        const page = products.value.length === 1 && pagination.current_page > 1
            ? pagination.current_page - 1
            : pagination.current_page;

        await loadProducts(page);
    } catch (error) {
        showAdminToast(
            error.response?.data?.message || 'Unable to delete product.',
            'error'
        );
    }
}

function formatPrice(value) {
    const amount = Number(value ?? 0);

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2,
    }).format(amount);
}

onMounted(() => {
    loadCategories();
    loadProducts();
});
</script>

<style scoped>
.product-thumbnail {
    width: 54px;
    height: 54px;
    border: 1px solid var(--app-border);
    border-radius: 8px;
    object-fit: contain;
    background: var(--app-white);
    padding: 3px;
}

.image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--app-muted);
    background: var(--app-bg);
    font-size: 1.25rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--app-text);
}

.card {
    border-radius: 12px;
}

.table th {
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    color: var(--app-text);
}

.table td {
    font-size: 0.875rem;
}

.btn-primary {
    background-color: var(--app-primary);
    border-color: var(--app-primary);
}

.btn-primary:hover {
    background-color: var(--app-primary-hover);
    border-color: var(--app-primary-hover);
}

.page-link {
    color: var(--app-primary);
}

.active > .page-link {
    background-color: var(--app-primary);
    border-color: var(--app-primary);
}
</style>
