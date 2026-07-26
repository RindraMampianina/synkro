import { beforeEach, describe, expect, it, vi } from 'vitest'
import api from '../api/client'
import useAuthStore from './authStore'

vi.mock('../api/client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
  },
}))

const mockedApi = vi.mocked(api)

describe('authStore', () => {
  beforeEach(() => {
    localStorage.clear()
    useAuthStore.setState({
      token: null,
      user: null,
      isAuthenticated: false,
    })
    vi.clearAllMocks()
  })

  it('logs in, stores the token and loads the current user', async () => {
    mockedApi.post.mockResolvedValueOnce({ data: { token: 'jwt-token' } })
    mockedApi.get.mockResolvedValueOnce({
      data: { id: 'u1', email: 'rindra@synkro.com', fullName: 'Rindra' },
    })

    await useAuthStore.getState().login('rindra@synkro.com', 'secret')

    expect(mockedApi.post).toHaveBeenCalledWith('/auth/login', {
      username: 'rindra@synkro.com',
      password: 'secret',
    })
    expect(mockedApi.get).toHaveBeenCalledWith('/me')
    expect(localStorage.getItem('token')).toBe('jwt-token')
    expect(useAuthStore.getState().isAuthenticated).toBe(true)
    expect(useAuthStore.getState().token).toBe('jwt-token')
    expect(useAuthStore.getState().user).toEqual({
      id: 'u1',
      email: 'rindra@synkro.com',
      fullName: 'Rindra',
    })
  })

  it('registers a new user', async () => {
    mockedApi.post.mockResolvedValueOnce({ data: {} })

    await useAuthStore.getState().register('rindra@synkro.com', 'Rindra', 'password123')

    expect(mockedApi.post).toHaveBeenCalledWith('/auth/register', {
      email: 'rindra@synkro.com',
      fullName: 'Rindra',
      plainPassword: 'password123',
    })
  })

  it('logs out and clears auth state', () => {
    localStorage.setItem('token', 'jwt-token')
    useAuthStore.setState({ token: 'jwt-token', isAuthenticated: true })

    useAuthStore.getState().logout()

    expect(localStorage.getItem('token')).toBeNull()
    expect(useAuthStore.getState()).toMatchObject({
      token: null,
      user: null,
      isAuthenticated: false,
    })
  })
})
