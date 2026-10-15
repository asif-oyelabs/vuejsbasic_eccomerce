<template>
    <main class="orders-page">
        <div class="container py-4 py-lg-5">
            <div class="page-heading">
                <div>
                    <span class="eyebrow">YOUR ACCOUNT</span>
                    <h1>My Orders</h1>
                    <p>Track your purchases and view your order details.</p>
                </div>

                <router-link
                    :to="{ name: 'productlist' }"
                    class="continue-link"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Continue Shopping
                </router-link>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="state-card">
                <div class="spinner-border text-success"></div>
                <p class="mt-3 mb-0">Loading your orders...</p>
            </div>

            <!-- Error -->
            <div v-else-if="pageError" class="state-card">
                <i class="bi bi-exclamation-circle error-icon"></i>
                <h5>Unable to load orders</h5>
                <p>{{ pageError }}</p>
                <button class="btn btn-success" @click="fetchOrders">
                    Try Again
                </button>
            </div>

            <!-- Empty -->
            <div v-else-if="orders.length === 0" class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-bag"></i>
                </div>
                <h3>No orders yet</h3>
                <p>Your placed orders will appear here.</p>
                <router-link
                    :to="{ name: 'productlist' }"
                    class="shop-button"
                >
                    Explore Products
                </router-link>
            </div>

            <!-- Order list -->
            <section v-else class="orders-card">
                <div class="orders-card-heading">
                    <h5>Order History</h5>
                    <span>{{ orders.length }} order(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="order in orders" :key="order.id">
                                <td>
                                    <strong>{{ order.order_number }}</strong>
                                    <small>
                                        {{ order.items?.length || 0 }} product type(s)
                                    </small>
                                </td>

                                <td>{{ formatDate(order.ordered_at || order.created_at) }}</td>

                                <td>
                                    <span
                                        class="status-badge"
                                        :class="statusClass(order.status)"
                                    >
                                        {{ formatLabel(order.status) }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        class="status-badge"
                                        :class="statusClass(order.payment_status)"
                                    >
                                        {{ formatLabel(order.payment_status) }}
                                    </span>
                                </td>

                                <td class="text-end amount">
                                    {{ formatPrice(order.total_amount) }}
                                </td>

                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="view-button"
                                        :disabled="loadingOrderId === order.id"
                                        @click="openOrder(order)"
                                    >
                                        <span
                                            v-if="loadingOrderId === order.id"
                                            class="spinner-border spinner-border-sm"
                                        ></span>
                                        <template v-else>
                                            View <i class="bi bi-arrow-up-right ms-1"></i>
                                        </template>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.lastPage > 1"
                    class="pagination-row"
                >
                    <span>
                        Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
                    </span>

                    <div class="pagination-buttons">
                        <button
                            :disabled="pagination.currentPage <= 1 || loading"
                            @click="fetchOrders(pagination.currentPage - 1)"
                        >
                            Previous
                        </button>

                        <button
                            :disabled="
                                pagination.currentPage >= pagination.lastPage ||
                                loading
                            "
                            @click="fetchOrders(pagination.currentPage + 1)"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- Order Details Modal -->
        <Teleport to="body">
            <div
                v-if="selectedOrder"
                class="modal-backdrop-custom"
                @click.self="closeModal"
                @keydown.esc="closeModal"
            >
                <section
                    class="order-modal"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="orderModalTitle"
                >
                    <header class="modal-header-custom">
                        <div>
                            <span class="eyebrow">ORDER DETAILS</span>
                            <h4 id="orderModalTitle">
                                {{ selectedOrder.order_number }}
                            </h4>
                            <p>
                                Placed on
                                {{ formatDate(selectedOrder.ordered_at || selectedOrder.created_at) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="close-modal"
                            aria-label="Close order details"
                            @click="closeModal"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </header>

                    <div class="modal-body-custom">
                        <div class="modal-status-row">
                            <div>
                                <span class="detail-label">Order Status</span>
                                <span
                                    class="status-badge"
                                    :class="statusClass(selectedOrder.status)"
                                >
                                    {{ formatLabel(selectedOrder.status) }}
                                </span>
                            </div>

                            <div>
                                <span class="detail-label">Payment Status</span>
                                <span
                                    class="status-badge"
                                    :class="statusClass(selectedOrder.payment_status)"
                                >
                                    {{ formatLabel(selectedOrder.payment_status) }}
                                </span>
                            </div>
                        </div>

                        <h6 class="section-title">
                            Products in this order
                        </h6>

                        <div
                            v-for="item in selectedOrder.items || []"
                            :key="item.id"
                            class="detail-product"
                        >
                            <div class="detail-image">
                                <img
                                    v-if="item.product_image"
                                    :src="item.product_image"
                                    :alt="item.product_name"
                                    @error="item.product_image = null"
                                >
                                <i v-else class="bi bi-image"></i>
                            </div>

                            <div class="detail-product-info">
                                <strong>{{ item.product_name }}</strong>
                                <span>
                                    {{ formatPrice(item.price) }} × {{ item.quantity }}
                                </span>
                            </div>

                            <strong class="detail-line-total">
                                {{ formatPrice(item.total) }}
                            </strong>
                        </div>

                        <div v-if="!selectedOrder.items?.length" class="empty-items">
                            No product details are available for this order.
                        </div>

                        <div class="order-totals">
                            <div>
                                <span>Subtotal</span>
                                <strong>{{ formatPrice(selectedOrder.subtotal) }}</strong>
                            </div>

                            <div>
                                <span>Shipping</span>
                                <strong>
                                    {{
                                        Number(selectedOrder.shipping_amount) === 0
                                            ? 'FREE'
                                            : formatPrice(selectedOrder.shipping_amount)
                                    }}
                                </strong>
                            </div>

                            <div class="grand-total">
                                <span>Total Amount</span>
                                <strong>{{ formatPrice(selectedOrder.total_amount) }}</strong>
                            </div>
                        </div>
                    </div>

                    <footer class="modal-footer-custom">
                        <button
                            type="button"
                            class="close-button"
                            @click="closeModal"
                        >
                            Close
                        </button>
                    </footer>
                </section>
            </div>
        </Teleport>
    </main>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToast } from './composables/useToast';

const { showToast } = useToast();

const orders = ref([]);
const loading = ref(true);
const pageError = ref('');
const loadingOrderId = ref(null);
const selectedOrder = ref(null);

const pagination = ref({
    currentPage: 1,
    lastPage: 1,
});

function formatPrice(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}

function formatDate(value) {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

function formatLabel(value) {
    if (!value) return 'Unknown';

    return String(value)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, character => character.toUpperCase());
}

function statusClass(value) {
    const status = String(value || '').toLowerCase();

    if (['completed', 'delivered', 'paid', 'success'].includes(status)) {
        return 'status-success';
    }

    if (['cancelled', 'failed', 'refunded'].includes(status)) {
        return 'status-danger';
    }

    if (['processing', 'shipped'].includes(status)) {
        return 'status-info';
    }

    return 'status-pending';
}

function getErrorMessage(error, fallback) {
    if (error.response?.status === 401) {
        return 'Please log in to view your orders.';
    }

    return error.response?.data?.message || fallback;
}

async function fetchOrders(page = 1) {
    loading.value = true;
    pageError.value = '';

    try {
        const response = await axios.get('/api/orders', {
            params: { page },
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        const result = response.data?.data;

        // Laravel paginate() returns data.data for the list of orders.
        orders.value = Array.isArray(result?.data)
            ? result.data
            : Array.isArray(result)
                ? result
                : [];

        pagination.value = {
            currentPage: Number(result?.current_page || page),
            lastPage: Number(result?.last_page || 1),
        };
    } catch (error) {
        console.error('Unable to load orders:', error);
        pageError.value = getErrorMessage(
            error,
            'Unable to retrieve your order history.'
        );
    } finally {
        loading.value = false;
    }
}

async function openOrder(order) {
    loadingOrderId.value = order.id;

    try {
        const response = await axios.get(`/api/orders/${order.id}`, {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        selectedOrder.value = response.data?.data;

        if (!selectedOrder.value) {
            throw new Error('Order details were not returned by the server.');
        }
    } catch (error) {
        console.error('Unable to load order details:', error);

        showToast(
            getErrorMessage(error, 'Unable to load order details.'),
            'error'
        );
    } finally {
        loadingOrderId.value = null;
    }
}

function closeModal() {
    selectedOrder.value = null;
}

onMounted(fetchOrders);
</script>

<style scoped>
.orders-page {
    min-height: 75vh;
    background: var(--app-bg, #f8fafc);
    color: var(--app-text, #1f2937);
}

.page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.eyebrow {
    color: var(--app-primary-hover, #369b6e);
    font-size: 0.73rem;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.page-heading h1 {
    margin: 8px 0;
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 800;
}

.page-heading p,
.modal-header-custom p {
    margin: 0;
    color: var(--app-muted, #6b7280);
}

.continue-link {
    color: var(--app-primary-hover, #369b6e);
    font-weight: 700;
    text-decoration: none;
}

.orders-card,
.state-card,
.empty-state {
    overflow: hidden;
    border: 1px solid var(--app-border, #e5e7eb);
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 8px 28px rgb(31 41 55 / 4%);
}

.orders-card-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px;
}

.orders-card-heading h5 {
    margin: 0;
    font-weight: 800;
}

.orders-card-heading span,
.orders-table small {
    color: var(--app-muted, #6b7280);
    font-size: 0.82rem;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.orders-table th {
    padding: 14px 18px;
    background: #f8fafc;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
}

.orders-table td {
    padding: 18px;
    border-top: 1px solid var(--app-border, #e5e7eb);
    font-size: 0.86rem;
}

.orders-table td:first-child strong,
.orders-table small {
    display: block;
}

.orders-table small {
    margin-top: 5px;
}

.amount {
    font-weight: 800;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 9px;
    border-radius: 7px;
    font-size: 0.75rem;
    font-weight: 800;
    white-space: nowrap;
}

.status-success {
    background: #dcfce7;
    color: #166534;
}

.status-danger {
    background: #fee2e2;
    color: #991b1b;
}

.status-info {
    background: #e0f2fe;
    color: #075985;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.view-button {
    padding: 7px 11px;
    border: 1px solid #cceedd;
    border-radius: 8px;
    background: #f0fdf4;
    color: #15803d;
    font-size: 0.8rem;
    font-weight: 800;
}

.view-button:disabled {
    opacity: 0.5;
}

.pagination-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 18px 22px;
    border-top: 1px solid var(--app-border, #e5e7eb);
    color: var(--app-muted, #6b7280);
    font-size: 0.85rem;
}

.pagination-buttons {
    display: flex;
    gap: 8px;
}

.pagination-buttons button,
.close-button {
    padding: 8px 13px;
    border: 1px solid var(--app-border, #e5e7eb);
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-weight: 700;
}

.pagination-buttons button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.state-card,
.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.state-card p,
.empty-state p {
    margin: 10px 0 20px;
    color: var(--app-muted, #6b7280);
}

.error-icon {
    display: block;
    margin-bottom: 12px;
    color: #dc3545;
    font-size: 2.5rem;
}

.empty-icon {
    display: grid;
    place-items: center;
    width: 78px;
    height: 78px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: var(--app-primary-light, #e8f7f0);
    color: var(--app-primary-hover, #369b6e);
    font-size: 2.1rem;
}

.empty-state h3 {
    font-weight: 800;
}

.shop-button {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 10px;
    background: var(--app-primary, #42b883);
    color: white;
    font-weight: 800;
    text-decoration: none;
}

/* Modal */
.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    overflow-y: auto;
    background: rgb(15 23 42 / 60%);
}

.order-modal {
    width: 100%;
    max-width: 700px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    border-radius: 18px;
    background: white;
    box-shadow: 0 25px 80px rgb(0 0 0 / 25%);
}

.modal-header-custom {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 24px;
    border-bottom: 1px solid var(--app-border, #e5e7eb);
}

.modal-header-custom h4 {
    margin: 7px 0;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.modal-header-custom p {
    font-size: 0.85rem;
}

.close-modal {
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 9px;
    background: #f3f4f6;
    color: #374151;
}

.modal-body-custom {
    padding: 24px;
}

.modal-status-row {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 28px;
}

.modal-status-row > div {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
}

.detail-label {
    color: var(--app-muted, #6b7280);
    font-size: 0.8rem;
}

.section-title {
    margin-bottom: 12px;
    font-weight: 800;
}

.detail-product {
    display: grid;
    grid-template-columns: 68px minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    padding: 15px 0;
    border-bottom: 1px solid var(--app-border, #e5e7eb);
}

.detail-image {
    display: grid;
    place-items: center;
    width: 68px;
    height: 68px;
    overflow: hidden;
    border-radius: 10px;
    background: #f8fafc;
    color: #9ca3af;
    font-size: 1.5rem;
}

.detail-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 5px;
}

.detail-product-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.detail-product-info strong {
    overflow-wrap: anywhere;
}

.detail-product-info span {
    color: var(--app-muted, #6b7280);
    font-size: 0.85rem;
}

.detail-line-total {
    text-align: right;
    white-space: nowrap;
}

.empty-items {
    padding: 20px 0;
    color: var(--app-muted, #6b7280);
}

.order-totals {
    display: grid;
    gap: 14px;
    margin-top: 24px;
}

.order-totals > div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.grand-total {
    padding-top: 18px;
    border-top: 1px solid var(--app-border, #e5e7eb);
    font-size: 1.05rem;
    font-weight: 800;
}

.grand-total strong {
    color: var(--app-primary-hover, #369b6e);
}

.modal-footer-custom {
    display: flex;
    justify-content: flex-end;
    padding: 16px 24px;
    border-top: 1px solid var(--app-border, #e5e7eb);
}

@media (max-width: 767.98px) {
    .page-heading {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .orders-card-heading {
        padding: 16px;
    }

    .modal-backdrop-custom {
        padding: 10px;
    }

    .modal-header-custom,
    .modal-body-custom {
        padding: 17px;
    }

    .detail-product {
        grid-template-columns: 54px minmax(0, 1fr);
        gap: 10px;
    }

    .detail-image {
        width: 54px;
        height: 54px;
    }

    .detail-line-total {
        grid-column: 2;
        text-align: left;
    }

    .pagination-row {
        padding: 15px;
    }
}
</style>
