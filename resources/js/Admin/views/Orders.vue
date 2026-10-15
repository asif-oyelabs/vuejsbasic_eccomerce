
<template>
    <div class="container-fluid py-4">
        <!-- Heading -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Orders</h2>
                <p class="text-secondary mb-0">
                    Manage customer orders and view purchased products.
                </p>
            </div>

            <button
                class="btn btn-outline-success"
                type="button"
                @click="loadOrders(pagination.current_page)"
                :disabled="loading"
            >
                <i class="bi bi-arrow-clockwise me-1"></i>
                Refresh
            </button>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <label class="form-label">Search orders</label>
                        <input
                            v-model="filters.search"
                            class="form-control"
                            type="search"
                            placeholder="Order number, customer, email..."
                            @input="scheduleSearch"
                        >
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label">Order Status</label>
                        <select
                            v-model="filters.status"
                            class="form-select"
                            @change="loadOrders(1)"
                        >
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label">Payment Status</label>
                        <select
                            v-model="filters.payment_status"
                            class="form-select"
                            @change="loadOrders(1)"
                        >
                            <option value="">All payments</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div class="col-lg-1 d-flex align-items-end">
                        <button
                            class="btn btn-light border w-100"
                            title="Clear filters"
                            @click="resetFilters"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Order List</h5>
                <span class="badge text-bg-light">
                    {{ pagination.total }} orders
                </span>
            </div>

            <div v-if="errorMessage" class="alert alert-danger m-3">
                {{ errorMessage }}
                <button
                    class="btn btn-sm btn-outline-danger ms-2"
                    @click="loadOrders(pagination.current_page)"
                >
                    Retry
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Order Status</th>
                            <th>Payment</th>
                            <th class="text-end">Total</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="loading">
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-success"></div>
                                <p class="text-secondary mb-0 mt-2">
                                    Loading orders...
                                </p>
                            </td>
                        </tr>

                        <tr v-else-if="!orders.length">
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No orders found.
                            </td>
                        </tr>

                        <tr v-for="(order, index) in orders" :key="order.id">
                            <td class="ps-3">
                                {{ (pagination.current_page - 1) * perPage + index + 1 }}
                            </td>

                            <td>
                                <span class="fw-semibold text-dark">
                                    {{ order.order_number }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ order.user?.name || 'Customer' }}
                                </div>
                                <small class="text-secondary">
                                    {{ order.user?.email || '—' }}
                                </small>
                            </td>

                            <td class="text-nowrap">
                                {{ formatDate(order.ordered_at || order.created_at) }}
                            </td>

                            <td>
                                <span :class="statusClass(order.status)">
                                    {{ formatLabel(order.status) }}
                                </span>
                            </td>

                            <td>
                                <span :class="paymentClass(order.payment_status)">
                                    {{ formatLabel(order.payment_status) }}
                                </span>
                            </td>

                            <td class="text-end fw-bold text-nowrap">
                                {{ formatPrice(order.total_amount) }}
                            </td>

                            <td class="text-center pe-3">
                                <button
                                    class="btn btn-sm btn-outline-success"
                                    :disabled="viewingId === order.id"
                                    @click="viewOrder(order)"
                                >
                                    <span
                                        v-if="viewingId === order.id"
                                        class="spinner-border spinner-border-sm me-1"
                                    ></span>
                                    <i v-else class="bi bi-eye me-1"></i>
                                    View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.last_page > 1"
                class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-3"
            >
                <small class="text-secondary">
                    Showing {{ pagination.from || 0 }}–{{ pagination.to || 0 }}
                    of {{ pagination.total }} orders
                </small>

                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li
                            class="page-item"
                            :class="{ disabled: pagination.current_page <= 1 }"
                        >
                            <button
                                class="page-link"
                                :disabled="pagination.current_page <= 1 || loading"
                                @click="loadOrders(pagination.current_page - 1)"
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
                                @click="loadOrders(page)"
                                :disabled="loading"
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
                                :disabled="pagination.current_page >= pagination.last_page || loading"
                                @click="loadOrders(pagination.current_page + 1)"
                            >
                                Next
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Order Details Modal -->
        <Teleport to="body">
            <div
                v-if="showDetails"
                class="order-modal-backdrop"
                @click.self="closeDetails"
                @keydown.esc="closeDetails"
            >
                <div
                    class="order-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="orderModalTitle"
                >
                    <div class="modal-header px-4 py-3">
                        <div>
                            <h5 id="orderModalTitle" class="modal-title fw-bold">
                                Order Details
                            </h5>
                            <small class="text-secondary">
                                {{ selectedOrder?.order_number }}
                            </small>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Close"
                            @click="closeDetails"
                        ></button>
                    </div>

                    <div v-if="detailsLoading" class="text-center py-5">
                        <div class="spinner-border text-success"></div>
                        <p class="text-secondary mt-2 mb-0">
                            Loading order details...
                        </p>
                    </div>

                    <div v-else-if="selectedOrder" class="modal-body p-4">
                        <!-- Customer information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="detail-box">
                                    <small class="text-secondary">Customer</small>
                                    <div class="fw-semibold mt-1">
                                        {{ selectedOrder.user?.name || 'Customer' }}
                                    </div>
                                    <div class="text-secondary small">
                                        {{ selectedOrder.user?.email || '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="detail-box">
                                    <small class="text-secondary">Order Date</small>
                                    <div class="fw-semibold mt-1">
                                        {{ formatDate(selectedOrder.ordered_at || selectedOrder.created_at) }}
                                    </div>
                                    <div class="mt-2">
                                        <span :class="statusClass(selectedOrder.status)">
                                            {{ formatLabel(selectedOrder.status) }}
                                        </span>
                                        <span :class="paymentClass(selectedOrder.payment_status)" class="ms-1">
                                            {{ formatLabel(selectedOrder.payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Products -->
                        <h6 class="fw-bold mb-3">Purchased Products</h6>

                        <div v-if="!selectedOrder.items?.length" class="alert alert-light">
                            No product items were found for this order.
                        </div>

                        <div v-else class="table-responsive order-items-table">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="item in selectedOrder.items"
                                        :key="item.id"
                                    >
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img
                                                    v-if="item.product_image"
                                                    :src="item.product_image"
                                                    :alt="item.product_name"
                                                    class="order-product-image"
                                                >
                                                <div
                                                    v-else
                                                    class="order-product-placeholder"
                                                >
                                                    <i class="bi bi-image"></i>
                                                </div>

                                                <span class="fw-semibold">
                                                    {{ item.product_name }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            {{ item.quantity }}
                                        </td>

                                        <td class="text-end text-nowrap">
                                            {{ formatPrice(item.price) }}
                                        </td>

                                        <td class="text-end fw-semibold text-nowrap">
                                            {{ formatPrice(item.total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="order-totals ms-auto mt-4">
                            <div class="d-flex justify-content-between gap-4 mb-2">
                                <span class="text-secondary">Subtotal</span>
                                <strong>{{ formatPrice(selectedOrder.subtotal) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between gap-4 mb-3">
                                <span class="text-secondary">Shipping</span>
                                <strong>{{ formatPrice(selectedOrder.shipping_amount) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between gap-4 border-top pt-3">
                                <span class="fw-bold">Total Amount</span>
                                <strong class="text-success fs-5">
                                    {{ formatPrice(selectedOrder.total_amount) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light px-4 py-3">
                        <button
                            class="btn btn-secondary"
                            @click="closeDetails"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { showAdminToast } from '../composables/useAdminToast';

const orders = ref([]);
const loading = ref(false);
const errorMessage = ref('');
const viewingId = ref(null);

const showDetails = ref(false);
const detailsLoading = ref(false);
const selectedOrder = ref(null);

const perPage = 10;

const filters = reactive({
    search: '',
    status: '',
    payment_status: '',
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

    return Array.from(
        { length: end - start + 1 },
        (_, index) => start + index
    );
});

async function loadOrders(page = 1) {
    loading.value = true;
    errorMessage.value = '';

    try {
        const params = {
            page,
            per_page: perPage,
        };

        if (filters.search.trim()) {
            params.search = filters.search.trim();
        }

        if (filters.status) {
            params.status = filters.status;
        }

        if (filters.payment_status) {
            params.payment_status = filters.payment_status;
        }

        const response = await axios.get('/api/admin/orders', {
            params,
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        const result = response.data?.data;

        orders.value = Array.isArray(result?.data) ? result.data : [];

        Object.assign(pagination, {
            current_page: result?.current_page ?? 1,
            last_page: result?.last_page ?? 1,
            from: result?.from ?? 0,
            to: result?.to ?? 0,
            total: result?.total ?? 0,
        });
    } catch (error) {
        console.error('Unable to load admin orders:', error);

        orders.value = [];
        errorMessage.value =
            error.response?.data?.message || 'Unable to load orders.';

        showAdminToast(errorMessage.value, 'error');
    } finally {
        loading.value = false;
    }
}

function scheduleSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadOrders(1), 350);
}

function resetFilters() {
    filters.search = '';
    filters.status = '';
    filters.payment_status = '';
    clearTimeout(searchTimer);
    loadOrders(1);
}

async function viewOrder(order) {
    showDetails.value = true;
    detailsLoading.value = true;
    selectedOrder.value = null;
    viewingId.value = order.id;

    try {
        const response = await axios.get(`/api/admin/orders/${order.id}`, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        selectedOrder.value = response.data?.data || null;

        if (!selectedOrder.value) {
            throw new Error('Order details were not returned by the server.');
        }
    } catch (error) {
        console.error('Unable to load order details:', error);

        showDetails.value = false;

        showAdminToast(
            error.response?.data?.message || 'Unable to load order details.',
            'error'
        );
    } finally {
        detailsLoading.value = false;
        viewingId.value = null;
    }
}

function closeDetails() {
    showDetails.value = false;
    selectedOrder.value = null;
}

function formatPrice(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function formatDate(value) {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('en-IN', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

function formatLabel(value) {
    if (!value) return 'Unknown';

    return String(value)
        .replace(/[_-]/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function statusClass(status) {
    const classes = {
        pending: 'badge rounded-pill text-bg-warning',
        processing: 'badge rounded-pill text-bg-info',
        completed: 'badge rounded-pill text-bg-success',
        cancelled: 'badge rounded-pill text-bg-danger',
    };

    return classes[String(status || '').toLowerCase()]
        || 'badge rounded-pill text-bg-secondary';
}

function paymentClass(status) {
    const classes = {
        paid: 'badge rounded-pill text-bg-success',
        unpaid: 'badge rounded-pill text-bg-warning',
        failed: 'badge rounded-pill text-bg-danger',
        refunded: 'badge rounded-pill text-bg-info',
    };

    return classes[String(status || '').toLowerCase()]
        || 'badge rounded-pill text-bg-secondary';
}

onMounted(() => loadOrders());

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
});
</script>

<style scoped>
.form-control:focus,
.form-select:focus {
    border-color: #42b883;
    box-shadow: 0 0 0 0.2rem rgb(66 184 131 / 15%);
}

.table th {
    white-space: nowrap;
    font-size: 0.82rem;
    font-weight: 700;
    color: #4b5563;
}

.table td {
    font-size: 0.88rem;
}

.btn-outline-success {
    --bs-btn-color: #369b6e;
    --bs-btn-border-color: #369b6e;
    --bs-btn-hover-bg: #369b6e;
    --bs-btn-hover-border-color: #369b6e;
}

.order-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
    background: rgb(15 23 42 / 60%);
}

.order-modal {
    width: 100%;
    max-width: 950px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 24px 70px rgb(0 0 0 / 20%);
}

.order-modal .modal-header {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
}

.order-modal .modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 2;
    border-top: 1px solid #e5e7eb;
}

.detail-box {
    height: 100%;
    padding: 15px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
    overflow-wrap: anywhere;
}

.order-product-image,
.order-product-placeholder {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: #f8fafc;
}

.order-product-image {
    object-fit: contain;
    border: 1px solid #e5e7eb;
    padding: 3px;
}

.order-product-placeholder {
    display: grid;
    place-items: center;
    color: #9ca3af;
    font-size: 1.2rem;
}

.order-items-table {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.order-items-table .table {
    margin-bottom: 0;
}

.order-totals {
    width: 100%;
    max-width: 330px;
}

@media (max-width: 575.98px) {
    .order-modal-backdrop {
        padding: 8px;
    }

    .order-modal {
        max-height: calc(100vh - 16px);
        border-radius: 12px;
    }

    .order-modal .modal-body {
        padding: 16px !important;
    }
}
</style>
