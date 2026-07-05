import { create } from 'zustand';
import api from '../api/client';
import type { Task, TaskStatus } from '../types';

interface TaskState {
  tasks: Task[];
  loading: boolean;
  fetchTasks: (projectId: string) => Promise<void>;
  createTask: (data: Partial<Task>) => Promise<Task>;
  updateTaskStatus: (taskId: string, newStatus: TaskStatus) => Promise<void>;
  addTaskFromMercure: (task: Task) => void;
  updateTaskFromMercure: (taskId: string, newStatus: TaskStatus) => void;
}

const dedupeTasks = (tasks: Task[]) => {
  const map = new Map<string, Task>();
  tasks.forEach((task) => {
    map.set(task.id, task);
  });
  return Array.from(map.values());
};

const useTaskStore = create<TaskState>((set) => ({
  tasks: [],
  loading: false,

  fetchTasks: async (projectId) => {
    set({ loading: true });
    const response = await api.get(`/tasks?projectId=${projectId}`);
    set({
      tasks: dedupeTasks(response.data['hydra:member'] ?? []),
      loading: false,
    });
  },

  createTask: async (data) => {
    const response = await api.post('/tasks', data);
    const task = response.data as Task;
    set((state) => ({ tasks: dedupeTasks([...state.tasks, task]) }));
    return task;
  },

  updateTaskStatus: async (taskId, newStatus) => {
    await api.patch(
      `/tasks/${taskId}/status`,
      { status: newStatus },
      { headers: { 'Content-Type': 'application/merge-patch+json' } }
    );
    set((state) => ({
      tasks: state.tasks.map((t) =>
        t.id === taskId ? { ...t, status: newStatus } : t
      ),
    }));
  },

  // Appelé par Mercure — met à jour le store sans appel API
  addTaskFromMercure: (task) => {
    set((state) => ({
      tasks: dedupeTasks([...state.tasks, task]),
    }));
  },

  updateTaskFromMercure: (taskId, newStatus) => {
    set((state) => ({
      tasks: state.tasks.map((t) =>
        t.id === taskId ? { ...t, status: newStatus } : t
      ),
    }));
  },
}));

export default useTaskStore;