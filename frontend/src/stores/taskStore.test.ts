import { beforeEach, describe, expect, it, vi } from 'vitest'
import api from '../api/client'
import type { Task } from '../types'
import useTaskStore from './taskStore'

vi.mock('../api/client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
  },
}))

const mockedApi = vi.mocked(api)

const taskTodo: Task = {
  id: 't1',
  title: 'Écrire les tests',
  status: 'todo',
  priority: 'high',
  projectId: 'p1',
  createdAt: '2026-01-01T00:00:00+00:00',
}

const taskProgress: Task = {
  ...taskTodo,
  id: 't2',
  title: 'Review PR',
  status: 'in_progress',
  priority: 'medium',
}

describe('taskStore', () => {
  beforeEach(() => {
    useTaskStore.setState({ tasks: [], loading: false })
    vi.clearAllMocks()
  })

  it('fetches tasks for a project', async () => {
    mockedApi.get.mockResolvedValueOnce({ data: [taskTodo, taskProgress] })

    await useTaskStore.getState().fetchTasks('p1')

    expect(mockedApi.get).toHaveBeenCalledWith('/tasks?projectId=p1')
    expect(useTaskStore.getState().tasks).toHaveLength(2)
    expect(useTaskStore.getState().loading).toBe(false)
  })

  it('creates a task', async () => {
    mockedApi.post.mockResolvedValueOnce({ data: taskTodo })

    const created = await useTaskStore.getState().createTask({
      title: 'Écrire les tests',
      projectId: 'p1',
      priority: 'high',
    })

    expect(created).toEqual(taskTodo)
    expect(useTaskStore.getState().tasks).toEqual([taskTodo])
  })

  it('updates task status via API', async () => {
    useTaskStore.setState({ tasks: [taskTodo] })
    mockedApi.patch.mockResolvedValueOnce({ data: {} })

    await useTaskStore.getState().updateTaskStatus('t1', 'in_progress')

    expect(mockedApi.patch).toHaveBeenCalledWith(
      '/tasks/t1/status',
      { status: 'in_progress' },
      { headers: { 'Content-Type': 'application/merge-patch+json' } },
    )
    expect(useTaskStore.getState().tasks[0].status).toBe('in_progress')
  })

  it('adds a task from Mercure without duplicates', () => {
    useTaskStore.setState({ tasks: [taskTodo] })

    useTaskStore.getState().addTaskFromMercure(taskTodo)
    useTaskStore.getState().addTaskFromMercure(taskProgress)

    expect(useTaskStore.getState().tasks).toHaveLength(2)
  })

  it('updates a task status from Mercure', () => {
    useTaskStore.setState({ tasks: [taskTodo] })

    useTaskStore.getState().updateTaskFromMercure('t1', 'done')

    expect(useTaskStore.getState().tasks[0].status).toBe('done')
  })
})
