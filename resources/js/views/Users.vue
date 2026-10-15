<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { auth, loadUser } from '../auth';
import UserFormModal from './webmodal/UserFormModal.vue';

const router = useRouter();

const users = ref([]);
const pagination = ref(null);
const searchQuery = ref('');

const saving = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const loading = ref(false);

const errorMessage = ref('');
const successMessage = ref('');

const form = ref({
    name: '',
    email: '',
    password: '',
});

const selectedImage = ref(null);
const imagePreview = ref(null);

let previewUrl = null;

// Fetch users with pagination and search.
async function fetchUsers(page = 1) {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.get('/api/users', {
            params: {
                page,
                search: searchQuery.value.trim(),
            },
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        users.value = response.data.data;
        pagination.value = response.data;
    } catch (error) {
        if (error.response?.status === 401) {
            auth.user = null;
            await router.push('/login');
        } else {
            errorMessage.value =
                error.response?.data?.message || 'Unable to load users.';
        }
    } finally {
        loading.value = false;
    }
}

// Submit search and return to the first page.
function searchUsers() {
    fetchUsers(1);
}

// Clear search and reload all users.
function clearSearch() {
    searchQuery.value = '';
    fetchUsers(1);
}

// Release temporary image preview URLs.
function clearPreviewUrl() {
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    }
}

// Reset form and open Add User modal.
function openAddForm() {
    clearPreviewUrl();

    editingId.value = null;

    form.value = {
        name: '',
        email: '',
        password: '',
    };

    selectedImage.value = null;
    imagePreview.value = null;
    errorMessage.value = '';
    successMessage.value = '';
    showForm.value = true;
}

// Load existing user data into Edit User modal.
function openEditForm(user) {
    clearPreviewUrl();

    editingId.value = user.id;

    form.value = {
        name: user.name,
        email: user.email,
        password: '',
    };

    selectedImage.value = null;

    imagePreview.value = user.profile_image
        ? (
            user.profile_image.startsWith('http')
                ? user.profile_image
                : `/storage/${user.profile_image}`
        )
        : null;

    errorMessage.value = '';
    successMessage.value = '';
    showForm.value = true;
}

// Handle image selection and preview.
function selectImage(event) {
    const file = event.target.files[0];

    if (!file) return;

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    if (!allowedTypes.includes(file.type)) {
        errorMessage.value = 'Please select a JPG, PNG, or WebP image.';
        event.target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        errorMessage.value = 'Image size must not exceed 2 MB.';
        event.target.value = '';
        return;
    }

    clearPreviewUrl();

    selectedImage.value = file;
    previewUrl = URL.createObjectURL(file);
    imagePreview.value = previewUrl;

    errorMessage.value = '';
    successMessage.value = '';
}

// Create or update a user.
async function saveUser() {
    if (saving.value) return;

    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    const data = new FormData();

    data.append('name', form.value.name);
    data.append('email', form.value.email);

    if (form.value.password) {
        data.append('password', form.value.password);
    }

    if (selectedImage.value) {
        data.append('profile_image', selectedImage.value);
    }

    try {
        if (editingId.value) {
            data.append('_method', 'PUT');

            await axios.post(
                `/api/users/${editingId.value}`,
                data,
                {
                    withCredentials: true,
                    headers: { Accept: 'application/json' },
                }
            );

            successMessage.value = 'User updated successfully.';
        } else {
            data.append('password', form.value.password);

            await axios.post('/api/users', data, {
                withCredentials: true,
                headers: { Accept: 'application/json' },
            });

            successMessage.value = 'User added successfully.';
        }

        showForm.value = false;
        clearPreviewUrl();

        selectedImage.value = null;
        imagePreview.value = null;

        await fetchUsers(pagination.value?.current_page || 1);
    } catch (error) {
        const errors = error.response?.data?.errors;

        errorMessage.value = errors
            ? Object.values(errors).flat().join(' ')
            : error.response?.data?.message || 'Unable to save user.';
    } finally {
        saving.value = false;
    }
}

// Delete a user.
async function deleteUser(user) {
    if (user.id === auth.user?.id) return;

    if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
        return;
    }

    errorMessage.value = '';
    successMessage.value = '';

    try {
        await axios.delete(`/api/users/${user.id}`, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        successMessage.value = 'User deleted successfully.';

        const currentPage = pagination.value?.current_page || 1;
        const page = users.value.length === 1 && currentPage > 1
            ? currentPage - 1
            : currentPage;

        await fetchUsers(page);
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'Unable to delete user.';
    }
}

// Load authentication and users when the page opens.
onMounted(async () => {
    if (!auth.loaded || !auth.user) {
        await loadUser();
    }

    if (!auth.user) {
        await router.replace('/login');
        return;
    }

    await fetchUsers();
});

onBeforeUnmount(() => {
    clearPreviewUrl();
});
</script>

<template>
    <section class="container py-4 py-md-5">

        <!-- Page Header -->
        <div
            class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4"
        >
            <div>
                <h1 class="h3 fw-bold mb-2">
                    Users Management
                </h1>

                <p class="text-app-muted mb-0">
                    Manage and search your users from one place.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-success align-self-start align-self-sm-center"
                @click="openAddForm"
            >
                <span class="me-1">+</span>
                Add User
            </button>
        </div>

        <!-- Success Message -->
        <div
            v-if="successMessage"
            class="alert alert-success alert-dismissible fade show"
            role="status"
        >
            {{ successMessage }}

            <button
                type="button"
                class="btn-close"
                aria-label="Close"
                @click="successMessage = ''"
            ></button>
        </div>

        <!-- Error Message -->
        <div
            v-if="errorMessage && !showForm"
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            {{ errorMessage }}

            <button
                type="button"
                class="btn-close"
                aria-label="Close"
                @click="errorMessage = ''"
            ></button>
        </div>

        <!-- Add/Edit User Modal -->
        <UserFormModal
            :show="showForm"
            :editing-id="editingId"
            :form="form"
            :image-preview="imagePreview"
            :error-message="errorMessage"
            :saving="saving"
            @close="showForm = false"
            @submit="saveUser"
            @select-image="selectImage"
        />

        <!-- Users Table Card -->
        <div class="card border-app shadow-sm rounded-3 p-3 p-md-4">

            <!-- Table Header -->
            <div
                class="card-header bg-white border-app p-3 p-md-4"
            >
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
                >
                    <div>
                        <h2 class="h5 fw-bold mb-1">
                            All Users
                        </h2>

                        <p class="small text-app-muted mb-0">
                            Search users by name or email address.
                        </p>
                    </div>

                    <!-- Search Form -->
                    <form
                        class="d-flex flex-column flex-sm-row gap-2 user-search-form"
                        role="search"
                        @submit.prevent="searchUsers"
                    >
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </span>

                            <input
                                v-model="searchQuery"
                                type="search"
                                class="form-control"
                                placeholder="Search name or email..."
                                aria-label="Search users by name or email"
                            />
                        </div>

                        <div class="d-flex gap-2">
                            <button
                                type="submit"
                                class="btn btn-success flex-grow-1"
                                :disabled="loading"
                            >
                                Search
                            </button>

                            <button
                                v-if="searchQuery"
                                type="button"
                                class="btn btn-outline-secondary"
                                :disabled="loading"
                                @click="clearSearch"
                            >
                                Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body p-0">

                <!-- Loading State -->
                <div
                    v-if="loading"
                    class="d-flex justify-content-center align-items-center gap-2 py-5"
                    role="status"
                >
                    <span
                        class="spinner-border spinner-border-sm text-success"
                    ></span>

                    <span class="text-app-muted">
                        Loading users...
                    </span>
                </div>

                <!-- Responsive Table -->
                <div v-else class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-3 py-3">
                                    ID
                                </th>

                                <th scope="col" class="py-3">
                                    Name
                                </th>

                                <th scope="col" class="py-3">
                                    Email
                                </th>

                                <th scope="col" class="py-3">
                                    Image
                                </th>

                                <th
                                    scope="col"
                                    class="py-3 text-end pe-3"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="user in users"
                                :key="user.id"
                            >
                                <td class="px-3">
                                    {{ user.id }}
                                </td>

                                <td class="fw-semibold">
                                    {{ user.name }}
                                </td>

                                <td>
                                    {{ user.email }}
                                </td>

                                <td>
                                    <img
                                        v-if="user.profile_image"
                                        :src="
                                            user.profile_image.startsWith('http')
                                                ? user.profile_image
                                                : `/storage/${user.profile_image}`
                                        "
                                        alt="Profile"
                                        class="rounded-circle border object-fit-cover user-avatar"
                                    />

                                    <span
                                        v-else
                                        class="text-app-muted small"
                                    >
                                        No image
                                    </span>
                                </td>

                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            @click="openEditForm(user)"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            :disabled="user.id === auth.user?.id"
                                            :title="
                                                user.id === auth.user?.id
                                                    ? 'You cannot delete your own account'
                                                    : 'Delete user'
                                            "
                                            @click="deleteUser(user)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!users.length">
                                <td
                                    colspan="5"
                                    class="text-center text-app-muted py-5"
                                >
                                    <div class="mb-2">
                                        <span class="fs-3">⌕</span>
                                    </div>

                                    <div class="fw-semibold">
                                        No users found
                                    </div>

                                    <p class="small mb-0 mt-1">
                                        Try another name or email address.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination && !loading && users.length"
                class="card-footer bg-white border-app"
            >
                <div
                    class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3"
                >
                    <span class="text-app-muted small">
                        Page {{ pagination.current_page }}
                        of {{ pagination.last_page }}
                    </span>

                    <nav aria-label="Users pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <li
                                class="page-item"
                                :class="{
                                    disabled: !pagination.prev_page_url
                                }"
                            >
                                <button
                                    type="button"
                                    class="page-link"
                                    :disabled="!pagination.prev_page_url"
                                    @click="fetchUsers(pagination.current_page - 1)"
                                >
                                    Previous
                                </button>
                            </li>

                            <li
                                class="page-item"
                                :class="{
                                    disabled: !pagination.next_page_url
                                }"
                            >
                                <button
                                    type="button"
                                    class="page-link"
                                    :disabled="!pagination.next_page_url"
                                    @click="fetchUsers(pagination.current_page + 1)"
                                >
                                    Next
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

        </div>
    </section>
</template>

<style scoped>
.user-search-form {
    width: 100%;
    max-width: 520px;
}

.user-search-form .input-group {
    min-width: 0;
}

.user-search-form .input-group-text {
    border-color: var(--app-border);
    color: var(--app-muted);
}

.user-search-form .form-control {
    min-width: 0;
}

.user-avatar {
    width: 42px;
    height: 42px;
}

@media (max-width: 575.98px) {
    .user-search-form {
        max-width: 100%;
    }
}
</style>
