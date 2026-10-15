
<template>
    <main class="product-detail-page">
        <div class="container py-4 py-lg-5">
            <router-link
                :to="{ name: 'productlist' }"
                class="back-link"
            >
                <i class="bi bi-arrow-left me-2"></i>
                Back to Products
            </router-link>

            <div v-if="loading" class="loading-panel mt-4">
                <div class="spinner-border text-success"></div>
                <p class="mt-3 mb-0">Loading product details...</p>
            </div>

            <div v-else-if="errorMessage" class="alert alert-warning mt-4">
                {{ errorMessage }}
                <div class="mt-3">
                    <router-link :to="{ name: 'productlist' }">
                        Return to products
                    </router-link>
                </div>
            </div>

            <div v-else-if="product" class="row g-4 g-lg-5 mt-2">
                <!-- Product gallery -->
                <div class="col-lg-6">
                    <section class="gallery-panel">
                        <div class="main-image-wrap">
                            <img
                                v-if="selectedImage"
                                :src="selectedImage"
                                :alt="product.name"
                                class="main-product-image"
                            >
                            <div v-else class="image-placeholder">
                                <i class="bi bi-image"></i>
                                <span>No image available</span>
                            </div>

                            <span
                                v-if="Number(product.original_price) > Number(product.price)"
                                class="sale-badge"
                            >
                                SALE
                            </span>
                        </div>

                        <div
                            v-if="product.gallery.length > 1"
                            class="thumbnail-list"
                        >
                            <button
                                v-for="(image, index) in product.gallery"
                                :key="image"
                                type="button"
                                class="thumbnail-button"
                                :class="{ active: selectedImage === image }"
                                :aria-label="`View product image ${index + 1}`"
                                @click="selectedImage = image"
                            >
                                <img :src="image" :alt="`${product.name} image ${index + 1}`">
                            </button>
                        </div>
                    </section>
                </div>

                <!-- Product information -->
                <div class="col-lg-6">
                    <section class="product-info-panel">
                        <span class="category-label">
                            {{ product.category?.name || 'General' }}
                        </span>

                        <h1 class="product-title">{{ product.name }}</h1>

                        <div class="price-block">
                            <span class="current-price">
                                {{ formatPrice(product.price) }}
                            </span>

                            <span
                                v-if="Number(product.original_price) > Number(product.price)"
                                class="old-price"
                            >
                                {{ formatPrice(product.original_price) }}
                            </span>

                            <span
                                v-if="Number(product.original_price) > Number(product.price)"
                                class="discount-label"
                            >
                                {{ discountPercent }}% OFF
                            </span>
                        </div>

                        <div class="stock-label">
                            <span
                                class="stock-indicator"
                                :class="product.qty > 0 ? 'in-stock' : 'out-stock'"
                            ></span>
                            {{ product.qty > 0
                                ? `${product.qty} available`
                                : 'Currently out of stock' }}
                        </div>

                        <div class="description-block">
                            <h5>Product Description</h5>
                            <p v-if="product.description">
                                {{ product.description }}
                            </p>
                            <p v-else class="text-muted">
                                No description is available for this product.
                            </p>
                        </div>

                        <div v-if="isLoggedIn" class="purchase-panel">
                            <label for="productQuantity" class="quantity-label">
                                Quantity
                            </label>

                            <div class="quantity-control">
                                <button
                                    type="button"
                                    aria-label="Decrease quantity"
                                    :disabled="quantity <= 1 || addingToCart"
                                    @click="quantity--"
                                >
                                    <i class="bi bi-dash-lg"></i>
                                </button>

                                <input
                                    id="productQuantity"
                                    v-model.number="quantity"
                                    type="number"
                                    min="1"
                                    :max="product.qty"
                                    :disabled="addingToCart"
                                    @change="validateQuantity"
                                >

                                <button
                                    type="button"
                                    aria-label="Increase quantity"
                                    :disabled="quantity >= product.qty || addingToCart"
                                    @click="quantity++"
                                >
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>

                            <button
                                type="button"
                                class="add-cart-button"
                                :disabled="addingToCart || product.qty < 1"
                                @click="addToCart"
                            >
                                <span
                                    v-if="addingToCart"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                <i v-else class="bi bi-bag-plus me-2"></i>
                                {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
                            </button>

                            <div
                                v-if="cartMessage"
                                class="alert mt-3 mb-0"
                                :class="cartSuccess ? 'alert-success' : 'alert-danger'"
                                role="status"
                            >
                                {{ cartMessage }}
                            </div>
                        </div>

                        <div v-else class="login-panel">
                            <i class="bi bi-person-lock"></i>
                            <div>
                                <strong>Login to purchase</strong>
                                <p class="mb-2">
                                    Sign in to add this product to your cart.
                                </p>
                                <router-link
                                    :to="{
                                        name: 'login',
                                        query: { redirect: route.fullPath }
                                    }"
                                    class="login-link"
                                >
                                    Login to Continue
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </router-link>
                            </div>
                        </div>

                        <div class="trust-points">
                            <div>
                                <i class="bi bi-shield-check"></i>
                                <span>Secure shopping</span>
                            </div>
                            <div>
                                <i class="bi bi-box-seam"></i>
                                <span>Stock checked at checkout</span>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { auth } from '../auth.js';
import { useToast } from './composables/useToast';
import { useCart } from './composables/useCart.js';
const { refreshCart } = useCart();

const { showToast } = useToast();

const route = useRoute();

const product = ref(null);
const selectedImage = ref('');
const quantity = ref(1);
const loading = ref(true);
const addingToCart = ref(false);
const errorMessage = ref('');
const cartMessage = ref('');
const cartSuccess = ref(false);

const isLoggedIn = computed(() => Boolean(auth.user));

const discountPercent = computed(() => {
    const original = Number(product.value?.original_price || 0);
    const price = Number(product.value?.price || 0);

    if (original <= price || original <= 0) return 0;

    return Math.round(((original - price) / original) * 100);
});

function formatPrice(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function validateQuantity() {
    const max = Number(product.value?.qty || 0);
    const value = Number(quantity.value);

    if (!Number.isFinite(value)) {
        quantity.value = 1;
        return;
    }

    quantity.value = Math.min(max, Math.max(1, Math.floor(value)));
}


async function loadProduct() {
    loading.value = true;
    errorMessage.value = '';
    product.value = null;
    selectedImage.value = '';
    quantity.value = 1;

    try {
        const response = await axios.get(
            `/api/products/${route.params.slug}`
        );

        const data = response.data?.data;

        if (!data) {
            throw new Error('Product details were not returned by the server.');
        }

        product.value = {
            ...data,
            gallery: Array.isArray(data.gallery) ? data.gallery : [],
        };

        selectedImage.value =
            product.value.gallery[0] ||
            product.value.main_image_url ||
            '';

    } catch (error) {
        console.error('Unable to load product:', error);

        showToast(
            error.response?.data?.message ||
            error.message ||
            'Unable to load product details.',
            'error'
        );

        errorMessage.value =
            error.response?.status === 404
                ? 'This product could not be found or is no longer available.'
                : error.response?.data?.message ||
                  'Unable to load product details.';

    } finally {
        loading.value = false;
    }
}

async function addToCart() {
    if (!isLoggedIn.value) {
        showToast('Please log in before adding this product to your cart.', 'error');
        return;
    }

    validateQuantity();

    if (!product.value || product.value.qty < 1) {
        showToast('This product is currently out of stock.', 'error');
        return;
    }

    addingToCart.value = true;
    cartMessage.value = '';

    try {
        const response = await axios.post(
            '/api/cart',
            {
                product_id: product.value.id,
                quantity: quantity.value,
            },
            {
                withCredentials: true,
                headers: { Accept: 'application/json' },
            }
        );

        await refreshCart();

        showToast(response.data?.message || 'Product added to your cart successfully.', 'success');
      
    } catch (error) {
        console.error('Unable to add product to cart:', error);

        showToast(
            error.response?.data?.message ||
            (error.response?.status === 401
                ? 'Your session has expired. Please log in again.'
                : 'Unable to add this product to your cart.'), 'error');

        if (error.response?.status === 422 && error.response.data?.errors) {
            const firstError = Object.values(error.response.data.errors).flat()[0];
            if (firstError) showToast(firstError, 'error');
        }
    } finally {
        addingToCart.value = false;
    }
}

watch(() => route.params.slug, loadProduct);

onMounted(loadProduct);

</script>

<style scoped>
.product-detail-page {
    min-height: 75vh;
    background: var(--app-bg);
    color: var(--app-text);
}

.back-link {
    display: inline-flex;
    align-items: center;
    color: var(--app-muted);
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    color: var(--app-primary-hover);
}

.loading-panel {
    padding: 80px 20px;
    text-align: center;
    color: var(--app-muted);
}

.gallery-panel,
.product-info-panel {
    padding: 20px;
    border: 1px solid var(--app-border);
    border-radius: 20px;
    background: white;
    box-shadow: 0 10px 35px rgb(31 41 55 / 4%);
}

.main-image-wrap {
    position: relative;
    display: grid;
    place-items: center;
    min-height: 380px;
    overflow: hidden;
    border-radius: 15px;
    background: #f6f9f7;
}

.main-product-image {
    width: 100%;
    height: 420px;
    object-fit: contain;
    padding: 18px;
}

.image-placeholder {
    display: grid;
    justify-items: center;
    gap: 8px;
    padding: 70px 10px;
    color: var(--app-muted);
    font-size: 3rem;
}

.image-placeholder span {
    font-size: 0.9rem;
}

.sale-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 7px 11px;
    border-radius: 8px;
    color: white;
    background: var(--app-danger);
    font-size: 0.75rem;
    font-weight: 800;
}

.thumbnail-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 18px;
}

.thumbnail-button {
    width: 78px;
    height: 78px;
    padding: 4px;
    overflow: hidden;
    border: 2px solid var(--app-border);
    border-radius: 11px;
    background: white;
}

.thumbnail-button.active,
.thumbnail-button:hover {
    border-color: var(--app-primary);
}

.thumbnail-button img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.category-label {
    display: inline-block;
    padding: 7px 11px;
    border-radius: 7px;
    color: var(--app-primary-hover);
    background: var(--app-primary-light);
    font-size: 0.8rem;
    font-weight: 700;
}

.product-title {
    margin: 20px 0 18px;
    font-size: clamp(1.8rem, 3.5vw, 2.5rem);
    font-weight: 800;
    line-height: 1.2;
    overflow-wrap: anywhere;
}

.price-block {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.current-price {
    color: var(--app-primary-hover);
    font-size: 1.8rem;
    font-weight: 800;
}

.old-price {
    color: var(--app-muted);
    text-decoration: line-through;
}

.discount-label {
    padding: 5px 8px;
    border-radius: 6px;
    color: #b42318;
    background: #fef3f2;
    font-size: 0.75rem;
    font-weight: 800;
}

.stock-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 18px;
    color: var(--app-muted);
    font-size: 0.9rem;
}

.stock-indicator {
    width: 9px;
    height: 9px;
    border-radius: 50%;
}

.in-stock {
    background: var(--app-success);
}

.out-stock {
    background: var(--app-danger);
}

.description-block {
    margin-top: 28px;
    padding-top: 25px;
    border-top: 1px solid var(--app-border);
}

.description-block h5 {
    margin-bottom: 12px;
    font-weight: 800;
}

.description-block p {
    color: var(--app-muted);
    line-height: 1.8;
    white-space: pre-line;
}

.purchase-panel {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    margin-top: 28px;
}

.quantity-label {
    flex-basis: 100%;
    font-weight: 700;
}

.quantity-control {
    display: flex;
    height: 48px;
    border: 1px solid var(--app-border);
    border-radius: 10px;
    overflow: hidden;
}

.quantity-control button {
    width: 42px;
    border: 0;
    background: var(--app-bg);
    color: var(--app-text);
}

.quantity-control button:disabled {
    opacity: 0.4;
}

.quantity-control input {
    width: 55px;
    border: 0;
    outline: none;
    text-align: center;
    font-weight: 700;
}

.quantity-control input::-webkit-inner-spin-button,
.quantity-control input::-webkit-outer-spin-button {
    margin: 0;
    appearance: none;
}

.add-cart-button {
    flex: 1;
    min-height: 48px;
    padding: 12px 18px;
    border: 0;
    border-radius: 10px;
    color: white;
    background: var(--app-primary);
    font-weight: 800;
}

.add-cart-button:hover:not(:disabled) {
    background: var(--app-primary-hover);
}

.add-cart-button:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.login-panel {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 28px;
    padding: 20px;
    border: 1px solid #cceedd;
    border-radius: 12px;
    background: #f3fbf6;
}

.login-panel > i {
    color: var(--app-primary-hover);
    font-size: 1.7rem;
}

.login-panel p {
    margin-top: 5px;
    color: var(--app-muted);
    font-size: 0.9rem;
}

.login-link {
    color: var(--app-primary-hover);
    font-weight: 800;
    text-decoration: none;
}

.trust-points {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid var(--app-border);
    color: var(--app-muted);
    font-size: 0.8rem;
}

.trust-points div {
    display: flex;
    align-items: center;
    gap: 7px;
}

.trust-points i {
    color: var(--app-primary-hover);
    font-size: 1.1rem;
}

@media (max-width: 575.98px) {
    .gallery-panel,
    .product-info-panel {
        padding: 14px;
        border-radius: 15px;
    }

    .main-image-wrap {
        min-height: 270px;
    }

    .main-product-image {
        height: 290px;
    }

    .thumbnail-button {
        width: 64px;
        height: 64px;
    }

    .purchase-panel {
        align-items: stretch;
    }

    .quantity-control {
        width: fit-content;
    }

    .add-cart-button {
        flex-basis: 100%;
    }
}
</style>
