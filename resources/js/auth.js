import { reactive } from 'vue';
import axios from 'axios';

export const auth = reactive({
    user: null,
    loaded: false,
});

let userRequest = null;

export async function loadUser() {
    if (auth.loaded) {
        return auth.user;
    }

    // Reuse an ongoing request if multiple navigation checks occur.
    if (!userRequest) {
        userRequest = axios.get('/api/user', {
            withCredentials: true,
            headers: { Accept: 'application/json' },
        })
        .then((response) => {
            auth.user = response.data.user ?? response.data;
            return auth.user;
        })
        .catch(() => {
            auth.user = null;
            return null;
        })
        .finally(() => {
            auth.loaded = true;
            userRequest = null;
        });
    }

    return userRequest;
}
