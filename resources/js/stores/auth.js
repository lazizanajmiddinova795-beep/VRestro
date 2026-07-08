import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(JSON.parse(localStorage.getItem('vrestro_user') || 'null'));
    const token = ref(localStorage.getItem('vrestro_token') || null);
    const tempUser = ref(null);
    const loginStep = ref('credentials'); // 'credentials' | 'biometrics' | 'success'

    const setTempUser = (userData) => {
        tempUser.value = userData;
        loginStep.value = 'biometrics';
    };

    const setAuth = (userData, userToken) => {
        user.value = userData;
        token.value = userToken;
        localStorage.setItem('vrestro_token', userToken);
        localStorage.setItem('vrestro_user', JSON.stringify(userData));
        loginStep.value = 'success';
        tempUser.value = null;
    };

    const resetLoginFlow = () => {
        tempUser.value = null;
        loginStep.value = 'credentials';
    };

    const logout = () => {
        user.value = null;
        token.value = null;
        tempUser.value = null;
        loginStep.value = 'credentials';
        localStorage.removeItem('vrestro_token');
        localStorage.removeItem('vrestro_user');
    };

    const isAuthenticated = () => {
        return !!token.value;
    };

    return {
        user,
        token,
        tempUser,
        loginStep,
        setTempUser,
        setAuth,
        resetLoginFlow,
        logout,
        isAuthenticated
    };
});
