import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import api from '../api/client';
import type { Project } from '../types';

interface ProjectState {
  projects: Project[];
  currentProject: Project | null;
  loading: boolean;
  fetchProjects: () => Promise<void>;
  createProject: (name: string, ownerId: string, description?: string) => Promise<Project>;
  setCurrentProject: (project: Project) => void;
}

const useProjectStore = create<ProjectState>()(
  persist(
    (set) => ({
      projects: [],
      currentProject: null,
      loading: false,

      fetchProjects: async () => {
        set({ loading: true });
        const response = await api.get('/projects');
        set({
          projects: response.data['hydra:member'] ?? [],
          loading: false,
        });
      },

      createProject: async (name, ownerId, description) => {
        const response = await api.post('/projects', { name, ownerId, description });
        const project = response.data as Project;
        set((state) => ({ projects: [...state.projects, project] }));
        return project;
      },

      setCurrentProject: (project) => set({ currentProject: project }),
    }),
    {
      name: 'synkro-project-storage',
      // Persiste uniquement le projet courant
      partialize: (state) => ({ currentProject: state.currentProject }),
    }
  )
);

export default useProjectStore;