    <template>
        <main class="cart-page">
            <div class="container py-4 py-lg-5">
                <!-- Page heading -->
                <div class="cart-heading mb-4">
                    <div>
                        <span class="eyebrow">YOUR SHOPPING BAG</span>
                        <h1>My Cart</h1>
                        <p>Review your items before placing your order.</p>
                    </div>

                    <router-link
                        :to="{ name: 'productlist' }"
                        class="continue-shopping"
                    >
                        <i class="bi bi-arrow-left me-2"></i>
                        Continue Shopping
                    </router-link>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="state-card">
                    <div class="spinner-border text-success"></div>
                    <p class="mt-3 mb-0">Loading your cart...</p>
                </div>

                <!-- Cart error -->
                <div v-else-if="pageError" class="state-card">
                    <i class="bi bi-exclamation-circle state-icon"></i>
                    <h4>Unable to load your cart</h4>
                    <p>{{ pageError }}</p>
                    <button class="btn btn-success" @click="fetchCart">
                        Try Again
                    </button>
                </div>

                <!-- Empty cart -->
                <div v-else-if="items.length === 0" class="empty-cart">
                    <div class="empty-icon">
                        <i class="bi bi-cart3"></i>
                    </div>
                    <h2>Your cart is waiting for you</h2>
                    <p>
                        You haven't added any products yet.
                        Explore our products and find something you love.
                    </p>
                    <router-link
                        :to="{ name: 'productlist' }"
                        class="shop-button"
                    >
                        <i class="bi bi-bag me-2"></i>
                        Explore Products
                    </router-link>
                </div>

                <!-- Cart content -->
                <div v-else class="row g-4 align-items-start">
                    <div class="col-lg-8">
                        <section class="cart-items-card">
                            <div class="items-header">
                                <div>
                                    <h5>Your Items</h5>
                                    <span>{{ items.length }} different product(s)</span>
                                </div>
                                <span class="secure-label">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Secure checkout
                                </span>
                            </div>

                            <article
                                v-for="item in items"
                                :key="item.id"
                                class="cart-item"
                            >
                                <div class="product-image">
                                    <img
                                        v-if="item.image"
                                        :src="item.image"
                                        :alt="item.name"
                                        @error="item.image = null"
                                    >
                                    <i v-else class="bi bi-image"></i>
                                </div>

                                <div class="product-details">
                                    <span class="product-category">
                                        {{ item.category || 'Product' }}
                                    </span>
                                    <h6>{{ item.name }}</h6>

                                    <div class="unit-price">
                                        {{ formatPrice(item.price) }}
                                        <span> / item</span>
                                    </div>

                                    <div
                                        v-if="item.stock < item.quantity"
                                        class="stock-warning"
                                    >
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Only {{ item.stock }} available
                                    </div>

                                    <button
                                        type="button"
                                        class="remove-button"
                                        :disabled="busyId === item.id || confirmingOrder"
                                        @click="removeItem(item)"
                                    >
                                        <i class="bi bi-trash3 me-1"></i>
                                        Remove
                                    </button>
                                </div>

                                <div class="item-actions">
                                    <div class="quantity-control">
                                        <button
                                            type="button"
                                            aria-label="Decrease quantity"
                                            :disabled="
                                                item.quantity <= 1 ||
                                                busyId === item.id ||
                                                confirmingOrder
                                            "
                                            @click="changeQuantity(item, -1)"
                                        >
                                            <i class="bi bi-dash"></i>
                                        </button>

                                        <span>{{ item.quantity }}</span>

                                        <button
                                            type="button"
                                            aria-label="Increase quantity"
                                            :disabled="
                                                item.quantity >= item.stock ||
                                                busyId === item.id ||
                                                confirmingOrder
                                            "
                                            @click="changeQuantity(item, 1)"
                                        >
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>

                                    <div class="line-total">
                                        {{ formatPrice(item.price * item.quantity) }}
                                    </div>
                                </div>
                            </article>

                            <div class="cart-bottom-link">
                                <router-link :to="{ name: 'productlist' }">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Add more products
                                </router-link>
                            </div>
                        </section>
                    </div>

                    <!-- Order summary -->
                    <div class="col-lg-4">
                        <aside class="summary-card">
                            <h5>Order Summary</h5>

                            <div class="summary-line">
                                <span>Products ({{ itemCount }} items)</span>
                                <strong>{{ formatPrice(subtotal) }}</strong>
                            </div>

                            <div class="summary-line">
                                <span>Shipping</span>
                                <strong class="free-shipping">FREE</strong>
                            </div>

                            <div class="shipping-note">
                                <i class="bi bi-truck me-2"></i>
                                Shipping charges are currently free.
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-total">
                                <span>Total Amount</span>
                                <strong>{{ formatPrice(totalAmount) }}</strong>
                            </div>

                            <p class="tax-note">
                                Final amount is validated by the server when
                                you confirm your order.
                            </p>

                            <button
                                type="button"
                                class="checkout-button"
                                :disabled="confirmingOrder || busyId !== null || hasStockIssue"
                                @click="confirmOrder"
                            >
                                <span
                                    v-if="confirmingOrder"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                <i v-else class="bi bi-check-circle me-2"></i>
                                {{
                                    confirmingOrder
                                        ? 'Placing Order...'
                                        : 'Confirm Order'
                                }}
                            </button>

                            <p v-if="hasStockIssue" class="checkout-warning">
                                Please correct the quantities before continuing.
                            </p>

                            <div class="checkout-security">
                                <i class="bi bi-lock-fill"></i>
                                <span>Your order is processed securely.</span>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </main>
    </template>

    <script setup>
    import { computed, onMounted, ref } from 'vue';
    import axios from 'axios';
    import { useRouter } from 'vue-router';
    import { useToast } from './composables/useToast';
    import { useCart } from './composables/useCart.js';
    const { setCartCount, clearCart } = useCart();

    const router = useRouter();
    const { showToast } = useToast();

    const items = ref([]);
    const loading = ref(true);
    const pageError = ref('');
    const busyId = ref(null);
    const confirmingOrder = ref(false);

    const subtotal = computed(() =>
        items.value.reduce(
            (sum, item) => sum + Number(item.price) * Number(item.quantity),
            0
        )
    );

    const itemCount = computed(() =>
        items.value.reduce((sum, item) => sum + Number(item.quantity), 0)
    );

    const totalAmount = computed(() => subtotal.value);

    const hasStockIssue = computed(() =>
        items.value.some(
            item =>
                Number(item.stock) < 1 ||
                Number(item.quantity) > Number(item.stock)
        )
    );

    function formatPrice(value) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 2,
        }).format(Number(value || 0));
    }

    function getErrorMessage(error, fallback) {
        const errors = error.response?.data?.errors;
        const firstError = errors
            ? Object.values(errors).flat()[0]
            : null;

        if (firstError) return firstError;

        if (error.response?.status === 401) {
            return 'Your session has expired. Please log in again.';
        }

        return error.response?.data?.message || fallback;
    }

    async function fetchCart() {
        loading.value = true;
        pageError.value = '';

        try {
            const response = await axios.get('/api/cart', {
                withCredentials: true,
                headers: { Accept: 'application/json' },
            });

            items.value = (response.data?.data?.items || []).map(item => ({
                ...item,
                price: Number(item.price),
                quantity: Number(item.quantity),
                stock: Number(item.stock),
            }));
            setCartCount(itemCount.value);
        } catch (error) {
            console.error('Unable to load cart:', error);
            pageError.value = getErrorMessage(
                error,
                'We could not retrieve your cart.'
            );
        } finally {
            loading.value = false;
        }
    }

    async function changeQuantity(item, amount) {
        const newQuantity = item.quantity + amount;

        if (newQuantity < 1 || newQuantity > item.stock) return;

        busyId.value = item.id;

        try {
            await axios.patch(
                `/api/cart/${item.id}`,
                { quantity: newQuantity },
                {
                    withCredentials: true,
                    headers: { Accept: 'application/json' },
                }
            );

            item.quantity = newQuantity;
            setCartCount(itemCount.value);

            showToast('Cart quantity updated.', 'success');
        } catch (error) {
            showToast(
                getErrorMessage(error, 'Unable to update quantity.'),
                'error'
            );

            await fetchCart();
        } finally {
            busyId.value = null;
        }
    }

    async function removeItem(item) {
        const confirmed = window.confirm(
            `Remove "${item.name}" from your cart?`
        );

        if (!confirmed) return;

        busyId.value = item.id;

        try {
            await axios.delete(`/api/cart/${item.id}`, {
                withCredentials: true,
                headers: { Accept: 'application/json' },
            });

            items.value = items.value.filter(
                cartItem => cartItem.id !== item.id
            );
            setCartCount(itemCount.value);

            showToast('Product removed from your cart.', 'success');
        } catch (error) {
            showToast(
                getErrorMessage(error, 'Unable to remove this product.'),
                'error'
            );
        } finally {
            busyId.value = null;
        }
    }

    async function confirmOrder() {
        if (!items.value.length) {
            showToast('Your cart is empty.', 'warning');
            return;
        }

        if (hasStockIssue.value) {
            showToast(
                'Please correct the product quantities before ordering.',
                'warning'
            );
            return;
        }

        const confirmed = window.confirm(
            `Confirm your order for ${formatPrice(totalAmount.value)}?`
        );

        if (!confirmed) return;

        confirmingOrder.value = true;

        try {
            const response = await axios.post(
                '/api/orders',
                {},
                {
                    withCredentials: true,
                    headers: { Accept: 'application/json' },
                }
            );

            const order = response.data?.data;

            items.value = [];
            clearCart();

            showToast(
                response.data?.message || 'Your order has been placed!',
                'success',
                4000
            );

            if (order?.id) {
                    showToast(
                        `Order ${order.order_number} placed successfully!`,
                        'success',
                        4000
                    );

                    await router.push({ name: 'myorders' });
                }
        } catch (error) {
            console.error('Unable to confirm order:', error);

            showToast(
                getErrorMessage(error, 'Unable to place your order.'),
                'error',
                5000
            );

            if (error.response?.status === 401) {
                await router.push({
                    name: 'login',
                    query: { redirect: '/cart' },
                });
            } else if (error.response?.status === 422) {
                await fetchCart();
            }
        } finally {
            confirmingOrder.value = false;
        }
    }

    onMounted(fetchCart);
    </script>

    <style scoped>
    .cart-page {
        min-height: 75vh;
        background: var(--app-bg, #f8fafc);
        color: var(--app-text, #1f2937);
    }

    .cart-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
    }

    .eyebrow {
        color: var(--app-primary-hover, #369b6e);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1.5px;
    }

    .cart-heading h1 {
        margin: 8px 0;
        font-size: clamp(1.8rem, 4vw, 2.4rem);
        font-weight: 800;
    }

    .cart-heading p {
        margin: 0;
        color: var(--app-muted, #6b7280);
    }

    .continue-shopping,
    .cart-bottom-link a {
        color: var(--app-primary-hover, #369b6e);
        font-weight: 700;
        text-decoration: none;
    }

    .cart-items-card,
    .summary-card,
    .state-card,
    .empty-cart {
        border: 1px solid var(--app-border, #e5e7eb);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 28px rgb(31 41 55 / 4%);
    }

    .cart-items-card {
        padding: 22px;
    }

    .items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding-bottom: 18px;
    }

    .items-header h5,
    .summary-card h5 {
        margin-bottom: 5px;
        font-weight: 800;
    }

    .items-header span {
        color: var(--app-muted, #6b7280);
        font-size: 0.85rem;
    }

    .secure-label {
        color: var(--app-success, #198754) !important;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 112px minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        padding: 22px 0;
        border-top: 1px solid var(--app-border, #e5e7eb);
    }

    .product-image {
        display: grid;
        place-items: center;
        width: 112px;
        height: 112px;
        overflow: hidden;
        border-radius: 12px;
        background: #f6f9f7;
        color: #9ca3af;
        font-size: 2rem;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 8px;
    }

    .product-category {
        color: var(--app-muted, #6b7280);
        font-size: 0.75rem;
    }

    .product-details h6 {
        margin: 5px 0 8px;
        font-size: 1rem;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .unit-price {
        font-weight: 700;
    }

    .unit-price span {
        color: var(--app-muted, #6b7280);
        font-size: 0.8rem;
        font-weight: 400;
    }

    .remove-button {
        margin-top: 12px;
        padding: 0;
        border: 0;
        background: transparent;
        color: #dc3545;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .remove-button:disabled {
        opacity: 0.5;
    }

    .stock-warning,
    .checkout-warning {
        margin-top: 8px;
        color: #b45309;
        font-size: 0.8rem;
    }

    .item-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 14px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        height: 38px;
        overflow: hidden;
        border: 1px solid var(--app-border, #e5e7eb);
        border-radius: 9px;
    }

    .quantity-control button {
        width: 36px;
        height: 36px;
        border: 0;
        background: #f8fafc;
        color: #374151;
        font-size: 1.2rem;
    }

    .quantity-control button:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .quantity-control span {
        min-width: 32px;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 800;
    }

    .line-total {
        font-weight: 800;
        white-space: nowrap;
    }

    .cart-bottom-link {
        padding-top: 20px;
        border-top: 1px solid var(--app-border, #e5e7eb);
    }

    .summary-card {
        position: sticky;
        top: 20px;
        padding: 24px;
    }

    .summary-card h5 {
        margin-bottom: 25px;
    }

    .summary-line,
    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
        font-size: 0.9rem;
    }

    .summary-line > span {
        color: var(--app-muted, #6b7280);
    }

    .summary-line strong {
        text-align: right;
    }

    .free-shipping {
        color: var(--app-success, #198754);
    }

    .shipping-note {
        padding: 12px;
        border-radius: 9px;
        background: #f0fdf4;
        color: #166534;
        font-size: 0.78rem;
    }

    .summary-divider {
        height: 1px;
        margin: 22px 0;
        background: var(--app-border, #e5e7eb);
    }

    .summary-total {
        align-items: center;
        margin-bottom: 8px;
        font-weight: 800;
    }

    .summary-total strong {
        color: var(--app-primary-hover, #369b6e);
        font-size: 1.35rem;
        text-align: right;
    }

    .tax-note {
        margin: 12px 0 20px;
        color: var(--app-muted, #6b7280);
        font-size: 0.76rem;
        line-height: 1.6;
    }

    .checkout-button,
    .shop-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 50px;
        padding: 12px 18px;
        border: 0;
        border-radius: 11px;
        background: var(--app-primary, #42b883);
        color: #fff;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .checkout-button:hover:not(:disabled),
    .shop-button:hover {
        background: var(--app-primary-hover, #369b6e);
        color: #fff;
    }

    .checkout-button:disabled {
        cursor: not-allowed;
        opacity: 0.55;
    }

    .checkout-security {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        color: var(--app-muted, #6b7280);
        font-size: 0.78rem;
    }

    .checkout-security i {
        color: var(--app-success, #198754);
    }

    .state-card,
    .empty-cart {
        padding: 60px 22px;
        text-align: center;
    }

    .state-icon {
        color: #dc3545;
        font-size: 2.5rem;
    }

    .state-card p,
    .empty-cart p {
        max-width: 460px;
        margin: 10px auto 22px;
        color: var(--app-muted, #6b7280);
        line-height: 1.7;
    }

    .empty-icon {
        display: grid;
        place-items: center;
        width: 90px;
        height: 90px;
        margin: 0 auto 22px;
        border-radius: 50%;
        background: var(--app-primary-light, #e8f7f0);
        color: var(--app-primary-hover, #369b6e);
        font-size: 2.5rem;
    }

    .empty-cart h2 {
        font-size: 1.6rem;
        font-weight: 800;
    }

    .empty-cart .shop-button {
        width: auto;
        min-width: 200px;
    }

    @media (max-width: 767.98px) {
        .cart-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .cart-items-card {
            padding: 16px;
        }

        .cart-item {
            grid-template-columns: 82px minmax(0, 1fr);
            gap: 13px;
        }

        .product-image {
            width: 82px;
            height: 82px;
        }

        .item-actions {
            grid-column: 2;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }

        .summary-card {
            position: static;
        }
    }

    @media (max-width: 420px) {
        .secure-label {
            display: none;
        }

        .items-header h5 {
            font-size: 1rem;
        }

        .product-details h6 {
            font-size: 0.9rem;
        }

        .summary-card {
            padding: 18px;
        }
    }
    </style>
