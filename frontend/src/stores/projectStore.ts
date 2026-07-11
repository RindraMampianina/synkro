import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import api from '../api/client';
import type { Project } from '../types';

interface ProjectState {
  projects: Project[];
  currentProject: Project | null;
  loading: boolean;
  fetchProjects: () => Promise<void>;
  createProject: (name: string, description?: string) => Promise<Project>;
  setCurrentProject: (project: Project) => void;
  addProjectFromMercure: (project: Project) => void;
  updateProjectFromMercure: (project: Partial<Project> & { id: string }) => void;
}

const dedupeProjects = (projects: Project[]) => {
  const map = new Map<string, Project>();
  projects.forEach((project) => {
    map.set(project.id, project);
  });
  return Array.from(map.values());
};

const useProjectStore = create<ProjectState>()(
  persist(
    (set) => ({
      projects: [],
      currentProject: null,
      loading: false,

      fetchProjects: async () => {
        set({ loading: true });
        const response = await api.get('/projects');
        const data = response.data as any;
        const items: Project[] = Array.isArray(data)
          ? data
          : (data?.member ?? data?.['hydra:member'] ?? []);
        set({
          projects: dedupeProjects(items),
          loading: false,
        });
      },

      createProject: async (name, description) => {
        const response = await api.post('/projects', { name, description });
        const project = response.data as Project;
        set((state) => ({ projects: dedupeProjects([...state.projects, project]) }));
        return project;
      },

      setCurrentProject: (project) => set({ currentProject: project }),

      addProjectFromMercure: (project) => {
        set((state) => ({
          projects: dedupeProjects([...state.projects, project]),
        }));
      },

      updateProjectFromMercure: (project) => {
        set((state) => ({
          projects: state.projects.map((p) =>
            p.id === project.id ? { ...p, ...project } : p
          ),
          currentProject:
            state.currentProject?.id === project.id
              ? { ...state.currentProject, ...project }
              : state.currentProject,
        }));
      },
    }),
    {
      name: 'synkro-project-storage',
      partialize: (state) => ({ currentProject: state.currentProject }),
    }
  )
);

export default useProjectStore;
