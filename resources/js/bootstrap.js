import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// In production, default to same-origin. Avoid hardcoding localhost,
// otherwise browser requests will go to the user's own machine.
const apiUrl = import.meta.env.VITE_API_URL;
window.axios.defaults.baseURL = apiUrl ? apiUrl.replace(/\/$/, '') : '';

// Set auth token from localStorage if exists
const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}
