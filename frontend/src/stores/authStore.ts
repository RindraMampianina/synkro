import { create } from 'zustand';
import api from '../api/client';
import type { User } from '../types';

interface AuthState {
  token: string | null;
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (email: string, fullName: string, plainPassword: string) => Promise<void>;
  logout: () => void;
  loadCurrentUser: () => Promise<void>;
}

const useAuthStore = create<AuthState>((set, get) => ({
  token: localStorage.getItem('token'),
  user: null,
  isAuthenticated: !!localStorage.getItem('token'),

  login: async (email, password) => {
    const response = await api.post('/auth/login', {
      username: email,
      password,
    });
    const { token } = response.data;
    localStorage.setItem('token', token);
    set({ token, isAuthenticated: true });
    await get().loadCurrentUser();
  },

  register: async (email, fullName, plainPassword) => {
    await api.post('/auth/register', { email, fullName, plainPassword });
  },

  loadCurrentUser: async () => {
    if (!localStorage.getItem('token')) {
      set({ user: null, isAuthenticated: false });
      return;
    }

    try {
      const response = await api.get('/me');
      const data = response.data;
      set({
        user: {
          id: data.id,
          email: data.email,
          fullName: data.fullName,
        },
        isAuthenticated: true,
      });
    } catch {
      localStorage.removeItem('token');
      set({ token: null, user: null, isAuthenticated: false });
    }
  },

  logout: () => {
    localStorage.removeItem('token');
    set({ token: null, user: null, isAuthenticated: false });
  },
}));

export default useAuthStore;
