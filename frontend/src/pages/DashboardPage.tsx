import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../stores/authStore';
import useProjectStore from '../stores/projectStore';
import useTaskStore from '../stores/taskStore';
import { useMercure } from '../hooks/useMercure';
import type { Task, TaskStatus } from '../types';

const STATUS_COLUMNS: { key: TaskStatus; label: string; color: string }[] = [
  { key: 'todo', label: 'À faire', color: 'bg-gray-100' },
  { key: 'in_progress', label: 'En cours', color: 'bg-blue-50' },
  { key: 'done', label: 'Terminé', color: 'bg-green-50' },
];

const PRIORITY_COLORS = {
  low: 'bg-gray-100 text-gray-600',
  medium: 'bg-yellow-100 text-yellow-700',
  high: 'bg-red-100 text-red-600',
};

const PRIORITY_LABELS = {
  low: 'Basse',
  medium: 'Moyenne',
  high: 'Haute',
};

const getAvailableTransitions = (status: TaskStatus): { value: TaskStatus; label: string }[] => {
  switch (status) {
    case 'todo': return [{ value: 'in_progress', label: 'Démarrer' }];
    case 'in_progress': return [{ value: 'done', label: 'Terminer' }];
    case 'done': return [];
  }
};

export default function DashboardPage() {
  const { logout } = useAuthStore();
  const { projects, fetchProjects, currentProject, setCurrentProject, createProject } = useProjectStore();
  const { tasks, fetchTasks, createTask, updateTaskStatus } = useTaskStore();
  const navigate = useNavigate();

  const [showCreateProject, setShowCreateProject] = useState(false);
  const [showCreateTask, setShowCreateTask] = useState(false);
  const [projectName, setProjectName] = useState('');
  const [taskTitle, setTaskTitle] = useState('');
  const [taskPriority, setTaskPriority] = useState<'low' | 'medium' | 'high'>('medium');

  useMercure(currentProject?.id ?? null);

  useEffect(() => {
    fetchProjects();
  }, []);

  useEffect(() => {
    if (!currentProject && projects.length > 0) {
      setCurrentProject(projects[0]);
    }
  }, [projects, currentProject, setCurrentProject]);

  useEffect(() => {
    if (currentProject) {
      fetchTasks(currentProject.id);
    }
  }, [currentProject?.id]);

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  const handleCreateProject = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const token = localStorage.getItem('token');
      if (!token) return;
      const payload = JSON.parse(atob(token.split('.')[1]));
      const project = await createProject(projectName, payload.id ?? payload.username);
      setCurrentProject(project);
      setProjectName('');
      setShowCreateProject(false);
    } catch (err) {
      console.error(err);
    }
  };

  const handleCreateTask = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentProject) return;
    try {
      await createTask({
        title: taskTitle,
        projectId: currentProject.id,
        priority: taskPriority,
      });
      setTaskTitle('');
      setShowCreateTask(false);
    } catch (err) {
      console.error(err);
    }
  };

  const tasksByStatus = (status: TaskStatus): Task[] =>
    tasks.filter((t) => t.status === status);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <div className="flex items-center gap-4">
          <h1 className="text-xl font-bold text-gray-900">Synkro</h1>
          <div className="flex items-center gap-2">
            {projects.map((p) => (
              <button
                key={p.id}
                onClick={() => setCurrentProject(p)}
                className={`px-3 py-1 rounded-full text-sm font-medium transition-colors ${
                  currentProject?.id === p.id
                    ? 'bg-primary-600 text-white'
                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                }`}
              >
                {p.name}
              </button>
            ))}
            <button
              onClick={() => setShowCreateProject(true)}
              className="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600 hover:bg-gray-200"
            >
              + Projet
            </button>
          </div>
        </div>
        <button
          onClick={handleLogout}
          className="text-sm text-gray-500 hover:text-gray-700"
        >
          Déconnexion
        </button>
      </header>

      {/* Main */}
      <main className="p-6">
        {!currentProject ? (
          <div className="flex flex-col items-center justify-center h-64 text-gray-400">
            <p className="text-lg">Sélectionne ou crée un projet</p>
          </div>
        ) : (
          <>
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold text-gray-800">
                {currentProject.name}
                <span className="ml-2 text-xs text-green-500 font-normal animate-pulse">
                  ● temps réel
                </span>
              </h2>
              <button
                onClick={() => setShowCreateTask(true)}
                className="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
              >
                + Nouvelle tâche
              </button>
            </div>

            {/* Kanban */}
            <div className="grid grid-cols-3 gap-4">
              {STATUS_COLUMNS.map((col) => (
                <div key={col.key} className={`${col.color} rounded-xl p-4`}>
                  <h3 className="text-sm font-semibold text-gray-600 mb-3 flex items-center justify-between">
                    {col.label}
                    <span className="bg-white text-gray-500 text-xs px-2 py-0.5 rounded-full">
                      {tasksByStatus(col.key).length}
                    </span>
                  </h3>
                  <div className="space-y-2">
                    {tasksByStatus(col.key).map((task) => (
                      <div
                        key={task.id}
                        className="bg-white rounded-lg p-3 shadow-sm border border-gray-100"
                      >
                        <p className="text-sm font-medium text-gray-800 mb-2">
                          {task.title}
                        </p>
                        {task.description && (
                          <p className="text-xs text-gray-400 mb-2">
                            {task.description}
                          </p>
                        )}
                        <div className="flex items-center justify-between">
                          <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${PRIORITY_COLORS[task.priority]}`}>
                            {PRIORITY_LABELS[task.priority]}
                          </span>
                          {getAvailableTransitions(task.status).length > 0 ? (
                            <select
                              value={task.status}
                              onChange={(e) =>
                                updateTaskStatus(task.id, e.target.value as TaskStatus)
                              }
                              className="text-xs border border-gray-200 rounded px-1 py-0.5 text-gray-600"
                            >
                              <option value={task.status} disabled>
                                {task.status === 'todo' ? 'À faire' : 'En cours'}
                              </option>
                              {getAvailableTransitions(task.status).map((t) => (
                                <option key={t.value} value={t.value}>
                                  {t.label}
                                </option>
                              ))}
                            </select>
                          ) : (
                            <span className="text-xs text-green-600 font-medium">
                              ✓ Terminé
                            </span>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </>
        )}
      </main>

      {/* Modal créer projet */}
      {showCreateProject && (
        <div className="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
            <h3 className="text-lg font-semibold mb-4">Nouveau projet</h3>
            <form onSubmit={handleCreateProject} className="space-y-4">
              <input
                type="text"
                value={projectName}
                onChange={(e) => setProjectName(e.target.value)}
                placeholder="Nom du projet"
                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                required
              />
              <div className="flex gap-2 justify-end">
                <button
                  type="button"
                  onClick={() => setShowCreateProject(false)}
                  className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700"
                >
                  Créer
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal créer tâche */}
      {showCreateTask && (
        <div className="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
            <h3 className="text-lg font-semibold mb-4">Nouvelle tâche</h3>
            <form onSubmit={handleCreateTask} className="space-y-4">
              <input
                type="text"
                value={taskTitle}
                onChange={(e) => setTaskTitle(e.target.value)}
                placeholder="Titre de la tâche"
                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                required
              />
              <select
                value={taskPriority}
                onChange={(e) => setTaskPriority(e.target.value as 'low' | 'medium' | 'high')}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="low">Basse</option>
                <option value="medium">Moyenne</option>
                <option value="high">Haute</option>
              </select>
              <div className="flex gap-2 justify-end">
                <button
                  type="button"
                  onClick={() => setShowCreateTask(false)}
                  className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700"
                >
                  Créer
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}