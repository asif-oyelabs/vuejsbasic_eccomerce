
import { reactive } from 'vue';
import axios from 'axios';

export const auth = reactive({
    user: null,
    loaded: false,
});

export async function loadUser() {
    try {
        const response = await axios.get('/api/user', {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        });

        auth.user = response.data.user;
    } catch (error) {
        auth.user = null;
    } finally {
        auth.loaded = true;
    }
}
