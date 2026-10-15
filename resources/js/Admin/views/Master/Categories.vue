
<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import CategoryFormModal from '../components/CategoryFormModal.vue';
import { showAdminToast } from '../../composables/useAdminToast';

const categories = ref([]);
const loading = ref(false);
const saving = ref(false);
const deletingId = ref(null);

const search = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const perPage = ref(10);

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0,
});

const modalOpen = ref(false);
const selectedCategory = ref(null);
const modalError = ref('');
const pageError = ref('');
const successMessage = ref('');

let searchTimer = null;

const apiUrl = '/api/admin/categories';

async function fetchCategories(page = 1) {
    loading.value = true;
    pageError.value = '';
    successMessage.value = '';

    try {
        const params = {
            page,
            per_page: perPage.value,
        };

        if (search.value.trim()) {
            params.search = search.value.trim();
        }

        if (statusFilter.value !== '') {
            params.status = statusFilter.value;
        }

        const response = await axios.get(apiUrl, {
            params,
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        // Controller wraps the Laravel paginator inside response.data.data.
        const paginator = response.data.data;

        categories.value = paginator.data ?? [];
        pagination.value = {
            current_page: paginator.current_page ?? 1,
            last_page: paginator.last_page ?? 1,
            total: paginator.total ?? 0,
            from: paginator.from ?? 0,
            to: paginator.to ?? 0,
        };

        currentPage.value = pagination.value.current_page;
    } catch (error) {
        pageError.value =
            error.response?.data?.message ||
            'Unable to load categories. Please try again.';
    } finally {
        loading.value = false;
    }
}

function openAddModal() {
    selectedCategory.value = null;
    modalError.value = '';
    modalOpen.value = true;
}

function openEditModal(category) {
    selectedCategory.value = { ...category };
    modalError.value = '';
    modalOpen.value = true;
}

function closeModal() {
    if (saving.value) return;
    modalOpen.value = false;
    selectedCategory.value = null;
    modalError.value = '';
}

function getValidationMessage(error) {
    const errors = error.response?.data?.errors;

    if (errors) {
        return Object.values(errors).flat().join(' ');
    }

    return error.response?.data?.message ||
        'Unable to save the category. Please try again.';
}

async function saveCategory(form) {
    const isEditing = !!selectedCategory.value;
    saving.value = true;
    modalError.value = '';
    
    try {
        await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
        });

        const formData = new FormData();

        formData.append('name', form.name.trim());
        formData.append('slug', form.slug.trim());
        formData.append('sort_order', String(form.sort_order));
        formData.append('status', form.status ? '1' : '0');

        if (form.image instanceof File) {
            formData.append('image', form.image);
        }

        if (selectedCategory.value) {
            // Laravel supports method spoofing for multipart form data.
            formData.append('_method', 'PUT');

            await axios.post(
                `${apiUrl}/${selectedCategory.value.id}`,
                formData,
                {
                    withCredentials: true,
                    headers: { Accept: 'application/json' },
                }
            );

            showAdminToast('Category updated successfully.', 'success');
        } else {
            await axios.post(apiUrl, formData, {    
                withCredentials: true,
                headers: { Accept: 'application/json' },
            });

            showAdminToast('Category created successfully.', 'success');
        }

        modalOpen.value = false;
        selectedCategory.value = null;

        await fetchCategories(currentPage.value);
    } catch (error) {
        modalError.value = getValidationMessage(error);
    } finally {
        saving.value = false;
    }
}

async function deleteCategory(category) {
    const confirmed = window.confirm(
        `Are you sure you want to delete "${category.name}"? This action cannot be undone.`
    );

    if (!confirmed) return;

    deletingId.value = category.id;
    pageError.value = '';
    successMessage.value = '';

    try {
        await axios.delete(`${apiUrl}/${category.id}`, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        showAdminToast('Category deleted successfully.', 'success');
        
        const pageToLoad =
            categories.value.length === 1 && currentPage.value > 1
                ? currentPage.value - 1
                : currentPage.value;

        await fetchCategories(pageToLoad);
    } catch (error) {
        pageError.value =
            error.response?.data?.message ||
            'Unable to delete this category.';
    } finally {
        deletingId.value = null;
    }
}

function changePage(page) {
    if (
        page < 1 ||
        page > pagination.value.last_page ||
        page === currentPage.value
    ) {
        return;
    }

    fetchCategories(page);
}

function clearFilters() {
    search.value = '';
    statusFilter.value = '';
    currentPage.value = 1;
    fetchCategories(1);
}

watch(search, () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        fetchCategories(1);
    }, 350);
});

watch(statusFilter, () => {
    fetchCategories(1);
});

watch(perPage, () => {
    fetchCategories(1);
});

onMounted(() => {
    fetchCategories();
});
</script>

<template>
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1">Category Master</h3>
                <p class="text-app-muted mb-0">
                    Create and manage your store categories.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-success"
                @click="openAddModal"
            >
                <i class="bi bi-plus-lg me-2"></i>
                Add Category
            </button>
        </div>

        <div
            v-if="successMessage"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            {{ successMessage }}
            <button
                type="button"
                class="btn-close"
                aria-label="Close"
                @click="successMessage = ''"
            ></button>
        </div>

        <div v-if="pageError" class="alert alert-danger" role="alert">
            {{ pageError }}
            <button
                type="button"
                class="btn btn-sm btn-outline-danger ms-2"
                @click="fetchCategories(currentPage)"
            >
                Retry
            </button>
        </div>

        <div class="card border-0 shadow-sm category-card">
            <div class="card-header bg-white border-0 p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                v-model="search"
                                type="search"
                                class="form-control"
                                placeholder="Search by name or slug..."
                                aria-label="Search categories"
                            />
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <select
                            v-model="statusFilter"
                            class="form-select"
                            aria-label="Filter by status"
                        >
                            <option value="">All statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <select
                            v-model.number="perPage"
                            class="form-select"
                            aria-label="Items per page"
                        >
                            <option :value="10">10 per page</option>
                            <option :value="25">25 per page</option>
                            <option :value="50">50 per page</option>
                            <option :value="100">100 per page</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2 text-md-end">
                        <button
                            type="button"
                            class="btn btn-outline-secondary w-100"
                            @click="clearFilters"
                        >
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="text-center py-5">
                                <div
                                    class="spinner-border text-success"
                                    role="status"
                                ></div>
                                <p class="text-app-muted small mt-2 mb-0">
                                    Loading categories...
                                </p>
                            </td>
                        </tr>

                        <tr v-else-if="categories.length === 0">
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-folder2-open empty-icon"></i>
                                <h6 class="fw-semibold mt-3">No categories found</h6>
                                <p class="text-app-muted small mb-3">
                                    Add a category or change your search filters.
                                </p>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-success"
                                    @click="openAddModal"
                                >
                                    Add Category
                                </button>
                            </td>
                        </tr>

                        <tr
                            v-for="(category, index) in categories"
                            v-else
                            :key="category.id"
                        >
                            <td class="ps-4 text-app-muted">
                                {{ (currentPage - 1) * perPage + index + 1 }}
                            </td>

                            <td>
                                <div class="category-image">
                                    <img
                                        v-if="category.image_url"
                                        :src="category.image_url"
                                        :alt="category.name"
                                    />
                                    <i
                                        v-else
                                        class="bi bi-image text-app-muted"
                                    ></i>
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ category.name }}
                                </div>
                            </td>

                          
                            <td>{{ category.sort_order }}</td>

                            <td>
                                <span
                                    class="badge rounded-pill"
                                    :class="category.status
                                        ? 'text-bg-success'
                                        : 'text-bg-secondary'"
                                >
                                    {{ category.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary action-btn"
                                        title="Edit category"
                                        aria-label="Edit category"
                                        @click="openEditModal(category)"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger action-btn"
                                        title="Delete category"
                                        aria-label="Delete category"
                                        :disabled="deletingId === category.id"
                                        @click="deleteCategory(category)"
                                    >
                                        <span
                                            v-if="deletingId === category.id"
                                            class="spinner-border spinner-border-sm"
                                        ></span>
                                        <i
                                            v-else
                                            class="bi bi-trash"
                                        ></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-0 p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <small class="text-app-muted">
                        Showing {{ pagination.from ?? 0 }}–{{ pagination.to ?? 0 }}
                        of {{ pagination.total }} categories
                    </small>

                    <nav aria-label="Category pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                class="page-item"
                                :class="{ disabled: currentPage <= 1 || loading }"
                            >
                                <button
                                    type="button"
                                    class="page-link"
                                    :disabled="currentPage <= 1 || loading"
                                    @click="changePage(currentPage - 1)"
                                >
                                    Previous
                                </button>
                            </li>

                            <li class="page-item disabled">
                                <span class="page-link">
                                    {{ currentPage }} / {{ pagination.last_page }}
                                </span>
                            </li>

                            <li
                                class="page-item"
                                :class="{
                                    disabled:
                                        currentPage >= pagination.last_page || loading
                                }"
                            >
                                <button
                                    type="button"
                                    class="page-link"
                                    :disabled="
                                        currentPage >= pagination.last_page || loading
                                    "
                                    @click="changePage(currentPage + 1)"
                                >
                                    Next
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <CategoryFormModal
            :show="modalOpen"
            :category="selectedCategory"
            :saving="saving"
            :error-message="modalError"
            @close="closeModal"
            @submit="saveCategory"
        />
    </div>
</template>

<style scoped>
.text-app-muted {
    color: var(--app-muted);
}

.category-card {
    border-radius: 14px;
    overflow: hidden;
}

.category-image {
    display: grid;
    place-items: center;
    width: 52px;
    height: 52px;
    overflow: hidden;
    border: 1px solid var(--app-border);
    border-radius: 10px;
    background: var(--app-bg);
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.empty-icon {
    color: var(--app-muted);
    font-size: 36px;
}

.slug-text {
    color: var(--app-primary-hover);
    background: var(--app-primary-light);
    border-radius: 5px;
    padding: 4px 7px;
    font-size: 12px;
}

.action-btn {
    width: 34px;
    height: 32px;
    display: inline-grid;
    place-items: center;
}

.pagination {
    --bs-pagination-color: var(--app-primary-hover);
    --bs-pagination-hover-color: var(--app-primary-hover);
    --bs-pagination-focus-color: var(--app-primary-hover);
    --bs-pagination-active-bg: var(--app-primary);
    --bs-pagination-active-border-color: var(--app-primary);
}

@media (max-width: 767.98px) {
    .category-card .card-header,
    .category-card .card-footer {
        padding: 16px;
    }
}
</style>
