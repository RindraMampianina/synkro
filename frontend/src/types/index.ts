export interface User {
  id: string;
  email: string;
  fullName: string;
}

export interface Project {
  id: string;
  name: string;
  description?: string;
  ownerId: string;
  members: string[];
  createdAt: string;
}

export interface Task {
  id: string;
  title: string;
  description?: string;
  status: TaskStatus;
  priority: TaskPriority;
  projectId: string;
  assigneeId?: string;
  dueDate?: string;
  createdAt: string;
}

export type TaskStatus = 'todo' | 'in_progress' | 'done';
export type TaskPriority = 'low' | 'medium' | 'high';

export interface AuthResponse {
  token: string;
}

export interface MercureEvent {
  type: 'task.created' | 'task.updated' | 'project.updated';
  payload: Partial<Task> & Partial<Project>;
}

export interface ApiError {
  detail: string;
  status: number;
}