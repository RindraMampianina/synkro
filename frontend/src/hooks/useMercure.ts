import { useEffect } from 'react';
import type { MercureEvent, TaskStatus } from '../types';
import useTaskStore from '../stores/taskStore';

export const useMercure = (projectId: string | null) => {
  const { addTaskFromMercure, updateTaskFromMercure } = useTaskStore();

  useEffect(() => {
    if (!projectId) return;

    const topic = `https://synkro.app/projects/${projectId}/tasks`;
    const mercureUrl = `/mercure/.well-known/mercure?topic=${encodeURIComponent(topic)}`;

    const eventSource = new EventSource(mercureUrl);

    eventSource.onmessage = (event) => {
      const data: MercureEvent = JSON.parse(event.data);

      switch (data.type) {
        case 'task.created':
          addTaskFromMercure(data.payload as any);
          break;
        case 'task.updated':
          if (data.payload.id && data.payload.status) {
            updateTaskFromMercure(
              data.payload.id,
              data.payload.status as TaskStatus
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