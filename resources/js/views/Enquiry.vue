<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { auth, loadUser } from '../auth';
import EnquiryFormModal from './webmodal/EnquiryFormModal.vue';

const router = useRouter();

const enquiries = ref([]);
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

// Fetch enquiries with pagination and search.
async function fetchEnquiries(page = 1) {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.get('/api/enquiries', {
            params: {
                page,
                search: searchQuery.value.trim(),
            },
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        enquiries.value = response.data.data;
        pagination.value = response.data;
    } catch (error) {
        if (error.response?.status === 401) {
            auth.user = null;
            await router.push('/login');
        } else {
            errorMessage.value =
                error.response?.data?.message || 'Unable to load enquiries.';
        }
    } finally {
        loading.value = false;
    }
}

// Submit search and return to the first page.
function searchEnquiries() {
    fetchEnquiries(1);
}

// Clear search and reload all enquiries.
function clearSearch() {
    searchQuery.value = '';
    fetchEnquiries(1);
}

// Release temporary image preview URLs.
function clearPreviewUrl() {
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
        previewUrl = null;
    }
}

// Reset form and open Add Enquiry modal.
function openAddForm() {
    clearPreviewUrl();

    editingId.value = null;

    form.value = {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
        status: '',
    };

    selectedImage.value = null;
    imagePreview.value = null;
    errorMessage.value = '';
    successMessage.value = '';
    showForm.value = true;
}

// Load existing enquiry data into Edit Enquiry modal.
function openEditForm(enquiry) {
    clearPreviewUrl();

    editingId.value = enquiry.id;

    form.value = {
        name: enquiry.name,
        email: enquiry.email,
        phone: enquiry.phone,
        subject: enquiry.subject,
        message: enquiry.message,
        status: enquiry.status,
    };

    selectedImage.value = null;

    imagePreview.value = enquiry.profile_image
        ? (
            enquiry.profile_image.startsWith('http')
                ? enquiry.profile_image
                : `/storage/${enquiry.profile_image}`
        )
        : null;

    errorMessage.value = '';
    successMessage.value = '';
    showForm.value = true;
}




// Create or update a user.
async function saveEnquiry() {
    if (saving.value) return;

    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    const data = new FormData();

    data.append('name', form.value.name);
    data.append('email', form.value.email);
    data.append('phone', form.value.phone);
    data.append('subject', form.value.subject);
    data.append('message', form.value.message);
    data.append('status', form.value.status);

    if (selectedImage.value) {
        data.append('profile_image', selectedImage.value);
    }

    try {
        if (editingId.value) {
            data.append('_method', 'PUT');

            await axios.post(
                `/api/enquiries/${editingId.value}`,
                data,
                {
                    withCredentials: true,
                    headers: { Accept: 'application/json' },
                }
            );

            successMessage.value = 'Enquiry updated successfully.';
        } else {
            await axios.post('/api/enquiries', data, {
                withCredentials: true,
                headers: { Accept: 'application/json' },
            });

            successMessage.value = 'Enquiry added successfully.';
        }

        showForm.value = false;
        clearPreviewUrl();

        selectedImage.value = null;
        imagePreview.value = null;

        await fetchEnquiries(pagination.value?.current_page || 1);
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
        await axios.delete(`/api/enquiries/${user.id}`, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        successMessage.value = 'Enquiry deleted successfully.';

        const currentPage = pagination.value?.current_page || 1;
        const page = enquiries.value.length === 1 && currentPage > 1
            ? currentPage - 1
            : currentPage;

        await fetchEnquiries(page);
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

    await fetchEnquiries();
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
                    Enquiries Management
                </h1>

                <p class="text-app-muted mb-0">
                    Manage and search your enquiries from one place.
                </p>
            </div>

         
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


        <EnquiryFormModal
            :show="showForm"
            :editing-id="editingId"
            :form="form"
            :error-message="errorMessage"
            :saving="saving"
            @close="showForm = false"
            @submit="saveEnquiry"
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
                            All Enquiries
                        </h2>

                        <p class="small text-app-muted mb-0">
                            Search enquiries by name or email address.
                        </p>
                    </div>

                    <!-- Search Form -->
                    <form
                        class="d-flex flex-column flex-sm-row gap-2 user-search-form"
                        role="search"
                        @submit.prevent="searchEnquiries"
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
                        Loading enquiries...
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
                                    Phone
                                </th>

                                <th scope="col" class="py-3">
                                    Subject
                                </th>

                                <th scope="col" class="py-3">
                                    Message
                                </th>
                                   <th scope="col" class="py-3">
                                    Date
                                </th>


                                <th scope="col" class="py-3">
                                    Status
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
                                v-for="enquiry in enquiries"
                                :key="enquiry.id"
                            >
                                <td class="px-3">{{ enquiry.id }}</td>
                                <td class="fw-semibold">{{ enquiry.name }}</td>
                                <td>{{ enquiry.email }}</td>
                                <td>{{ enquiry.phone || '-' }}</td>
                                <td>{{ enquiry.subject || '-' }}</td>
                                <td>
                                    <span class="text-break">
                                       {{ enquiry.message.length > 30 ? enquiry.message.substring(0, 30) + '...' : enquiry.message }}
                                    </span>
                                </td>
                                   <td> {{ new Date(enquiry.created_at).getDate() + '-' + (new Date(enquiry.created_at).getMonth() + 1) + '-' + new Date(enquiry.created_at).getFullYear() }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        style="padding: 0.5rem 1rem; width: 100%;"
                                        :class="{
                                            'btn btn-sm btn-warning text-dark': enquiry.status === 'pending',
                                            'btn btn-sm btn-success': enquiry.status === 'read',
                                            'btn btn-sm btn-secondary': !['pending', 'read'].includes(enquiry.status)
                                        }"
                                    >
                                        {{ enquiry.status }}
                                    </span>
                                </td>
                             
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            @click="openEditForm(enquiry)"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            @click="deleteEnquiry(enquiry)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Empty State -->
                            <tr v-if="!enquiries.length">
                                <td
                                    colspan="8"
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
                v-if="pagination && !loading && enquiries.length"
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
                                    @click="fetchEnquiries(pagination.current_page - 1)"
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
                                    @click="fetchEnquiries(pagination.current_page + 1)"
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
