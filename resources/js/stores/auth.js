import { defineStore } from 'pinia';
import axios from 'axios';
import { supabase } from '../supabaseClient';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    emailVerified: localStorage.getItem('emailVerified') === 'true',
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isFullyAuthenticated: (state) => !!state.token && state.emailVerified,
  },

  actions: {
    async login(email, password) {
      try {
        const { data: supabaseData, error: supabaseError } = await supabase.auth.signInWithPassword({
          email,
          password,
        });

        if (supabaseError) {
          throw new Error(supabaseError.message || 'Unable to sign in.');
        }

        if (!supabaseData?.user?.email_confirmed_at) {
          await supabase.auth.signOut();
          this.emailVerified = false;
          localStorage.setItem('emailVerified', 'false');

          const verificationError = new Error('Please verify your email before logging in.');
          verificationError.verificationRequired = true;
          throw verificationError;
        }

        let response;
        try {
          response = await axios.post('/api/login', {
            email,
            password,
            supabase_user_id: supabaseData?.user?.id || null,
          });
        } catch (loginError) {
          // If backend account does not exist yet but Supabase auth is valid,
          // attempt auto-provision and retry login.
          const pendingProvisionEmail = localStorage.getItem('pendingBackendProvisionEmail');
          if (loginError?.response?.status === 422 && pendingProvisionEmail === email) {
            const fallbackName = supabaseData?.user?.user_metadata?.name || email.split('@')[0] || 'User';

            try {
              await axios.post('/api/register', {
                name: fallbackName,
                email,
                password,
                password_confirmation: password,
                supabase_user_id: supabaseData?.user?.id || null,
              });
            } catch (registerError) {
              // Ignore "email already exists" and proceed to retry login.
              if (registerError?.response?.status !== 422) {
                throw registerError;
              }
            }

            response = await axios.post('/api/login', {
              email,
              password,
              supabase_user_id: supabaseData?.user?.id || null,
            });
            localStorage.removeItem('pendingBackendProvisionEmail');
          } else {
            const accountError = new Error('Account not available. If you deleted your account, please register again.');
            accountError.accountMissing = true;
            throw accountError;
          }
        }

        this.token = response.data.data.token;
        this.user = response.data.data.user;
        this.emailVerified = true;
        localStorage.setItem('token', this.token);
        localStorage.setItem('emailVerified', 'true');
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async register(name, email, password, password_confirmation, supabase_user_id = null) {
      try {
        const response = await axios.post('/api/register', {
          name,
          email,
          password,
          password_confirmation,
          supabase_user_id,
        });
        this.token = response.data.data.token;
        this.user = response.data.data.user;
        this.emailVerified = true;
        localStorage.setItem('token', this.token);
        localStorage.setItem('emailVerified', 'true');
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async fetchProfile() {
      try {
        const response = await axios.get('/api/profile');
        this.user = response.data.data;
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async logout() {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        this.emailVerified = false;
        localStorage.removeItem('token');
        localStorage.removeItem('emailVerified');
        delete axios.defaults.headers.common['Authorization'];
        await supabase.auth.signOut();
      }
    },

    initAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }

      if (!this.token) {
        this.emailVerified = false;
      }
    }
  }
});
