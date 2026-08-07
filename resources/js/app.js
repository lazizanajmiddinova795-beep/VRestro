import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from '@/App.vue';
import router from '@/router';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Add global fetch interceptor to send X-Branch-Id header
const originalFetch = window.fetch;
window.fetch = async (...args) => {
    let [resource, config] = args;
    const activeBranchStr = localStorage.getItem('vrestro_active_branch');
    if (activeBranchStr) {
        config = config || {};
        config.headers = config.headers || {};
        try {
            const branch = JSON.parse(activeBranchStr);
            if (branch && branch.id) {
                // Determine if headers is Headers object or plain object
                if (config.headers instanceof Headers) {
                    config.headers.append('X-Branch-Id', branch.id);
                } else {
                    config.headers['X-Branch-Id'] = branch.id;
                }
            }
        } catch(e) {}
    }
    return originalFetch(resource, config);
};

app.mount('#app');
