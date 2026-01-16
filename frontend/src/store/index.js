import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useAuthStore = create(
  persist(
    (set) => ({
      user: null,
      token: null,
      isAuthenticated: false,
      
      login: (user, token) => {
        localStorage.setItem('auth_token', token);
        set({ user, token, isAuthenticated: true });
      },
      
      logout: () => {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('active_department_id');
        set({ user: null, token: null, isAuthenticated: false });
      },
      
      updateUser: (user) => set({ user }),
    }),
    {
      name: 'auth-storage',
    }
  )
);

export const useDepartmentStore = create(
  persist(
    (set) => ({
      activeDepartment: null,
      departments: [],
      
      setActiveDepartment: (department) => {
        localStorage.setItem('active_department_id', department.id);
        set({ activeDepartment: department });
      },
      
      setDepartments: (departments) => set({ departments }),
    }),
    {
      name: 'department-storage',
    }
  )
);
