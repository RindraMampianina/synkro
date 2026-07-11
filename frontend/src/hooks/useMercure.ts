import { useEffect } from 'react';
import type { MercureEvent, Project, Task, TaskStatus } from '../types';
import useProjectStore from '../stores/projectStore';
import useTaskStore from '../stores/taskStore';

function getJwtUsername(): string | null {
  try {
    const token = localStorage.getItem('token');
    if (!token) return null;
    const payload = JSON.parse(atob(token.split('.')[1]));
    return payload.username ?? payload.email ?? null;
  } catch {
    return null;
  }
}

export const useMercure = (projectId: string | null) => {
  const { addTaskFromMercure, updateTaskFromMercure } = useTaskStore();
  const { addProjectFromMercure, updateProjectFromMercure } = useProjectStore();

  useEffect(() => {
    const username = getJwtUsername();
    const topics: string[] = [];

    if (username) {
      topics.push(`https://synkro.app/users/${username}/projects`);
    }

    if (projectId) {
      topics.push(`https://synkro.app/projects/${projectId}/tasks`);
    }

    if (topics.length === 0) return;

    const params = new URLSearchParams();
    topics.forEach((topic) => params.append('topic', topic));
    const mercureUrl = `/mercure/.well-known/mercure?${params.toString()}`;

    const eventSource = new EventSource(mercureUrl);

    eventSource.onmessage = (event) => {
      const data: MercureEvent = JSON.parse(event.data);

      switch (data.type) {
        case 'task.created':
          addTaskFromMercure(data.payload as Task);
          break;
        case 'task.updated':
          if (data.payload.id && data.payload.status) {
            updateTaskFromMercure(
              data.payload.id,
              data.payload.status as TaskStatus
            );
          }
          break;
        case 'project.created':
          if (data.payload.id && data.payload.name) {
            addProjectFromMercure({
              id: data.payload.id,
              name: data.payload.name,
              description: data.payload.description,
              ownerId: data.payload.ownerId ?? '',
              members: data.payload.members ?? [],
              createdAt: data.payload.createdAt ?? new Date().toISOString(),
            } as Project);
          }
          break;
        case 'project.updated':
          if (data.payload.id) {
            updateProjectFromMercure(
              data.payload as Partial<Project> & { id: string }
            );
          }
          break;
      }
    };

    eventSource.onerror = () => {
      console.error('Mercure connection error');
    };

    return () => eventSource.close();
  }, [projectId]);
};
