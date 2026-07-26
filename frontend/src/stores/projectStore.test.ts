import { beforeEach, describe, expect, it, vi } from 'vitest'
import api from '../api/client'
import type { Project } from '../types'
import useProjectStore from './projectStore'

vi.mock('../api/client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
  },
}))

const mockedApi = vi.mocked(api)

const projectA: Project = {
  id: 'p1',
  name: 'Alpha',
  ownerId: 'u1',
  members: [],
  createdAt: '2026-01-01T00:00:00+00:00',
}

const projectB: Project = {
  id: 'p2',
  name: 'Beta',
  ownerId: 'u1',
  members: [],
  createdAt: '2026-01-02T00:00:00+00:00',
}

describe('projectStore', () => {
  beforeEach(() => {
    localStorage.clear()
    useProjectStore.setState({
      projects: [],
      currentProject: null,
      loading: false,
    })
    vi.clearAllMocks()
  })

  it('fetches projects from the API', async () => {
    mockedApi.get.mockResolvedValueOnce({ data: [projectA, projectB] })

    await useProjectStore.getState().fetchProjects()

    expect(mockedApi.get).toHaveBeenCalledWith('/projects')
    expect(useProjectStore.getState().projects).toEqual([projectA, projectB])
    expect(useProjectStore.getState().loading).toBe(false)
  })

  it('parses hydra member collections', async () => {
    mockedApi.get.mockResolvedValueOnce({
      data: { 'hydra:member': [projectA] },
    })

    await useProjectStore.getState().fetchProjects()

    expect(useProjectStore.getState().projects).toEqual([projectA])
  })

  it('creates a project and appends it to the list', async () => {
    mockedApi.post.mockResolvedValueOnce({ data: projectA })

    const created = await useProjectStore.getState().createProject('Alpha')

    expect(mockedApi.post).toHaveBeenCalledWith('/projects', {
      name: 'Alpha',
      description: undefined,
    })
    expect(created).toEqual(projectA)
    expect(useProjectStore.getState().projects).toEqual([projectA])
  })

  it('deduplicates projects coming from Mercure', () => {
    useProjectStore.setState({ projects: [projectA] })

    useProjectStore.getState().addProjectFromMercure(projectA)
    useProjectStore.getState().addProjectFromMercure(projectB)

    expect(useProjectStore.getState().projects).toHaveLength(2)
    expect(useProjectStore.getState().projects.map((p) => p.id)).toEqual(['p1', 'p2'])
  })

  it('updates a project from Mercure including the current one', () => {
    useProjectStore.setState({
      projects: [projectA, projectB],
      currentProject: projectA,
    })

    useProjectStore.getState().updateProjectFromMercure({
      id: 'p1',
      name: 'Alpha renamed',
    })

    expect(useProjectStore.getState().projects[0].name).toBe('Alpha renamed')
    expect(useProjectStore.getState().currentProject?.name).toBe('Alpha renamed')
  })

  it('sets the current project', () => {
    useProjectStore.getState().setCurrentProject(projectB)
    expect(useProjectStore.getState().currentProject).toEqual(projectB)
  })
})
