
import { ref } from 'vue';
import axios from 'axios';

const cartCount = ref(0);
const cartItems = ref([]);

async function refreshCart() {
    try {
        const response = await axios.get('/api/cart', {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        const data = response.data?.data || {};

        cartItems.value = Array.isArray(data.items)
            ? data.items
            : [];

        cartCount.value = cartItems.value.reduce(
            (total, item) => total + Number(item.quantity || 0),
            0
        );
    } catch (error) {
        console.error('Unable to refresh cart:', error);
    }
}

function setCartCount(count) {
    cartCount.value = Math.max(0, Number(count) || 0);
}

function clearCart() {
    cartCount.value = 0;
    cartItems.value = [];
}

export function useCart() {
    return {
        cartCount,
        cartItems,
        refreshCart,
        setCartCount,
        clearCart,
    };
}
