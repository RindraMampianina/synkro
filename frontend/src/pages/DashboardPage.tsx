import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  ArrowRight,
  CheckCircle2,
  Circle,
  FolderKanban,
  LogOut,
  Menu,
  Plus,
  Radio,
  X,
} from 'lucide-react';
import BrandMark from '../components/BrandMark';
import useAuthStore from '../stores/authStore';
import useProjectStore from '../stores/projectStore';
import useTaskStore from '../stores/taskStore';
import { useMercure } from '../hooks/useMercure';
import type { Project, Task, TaskPriority, TaskStatus } from '../types';

const STATUS_COLUMNS: {
  key: TaskStatus;
  label: string;
  icon: typeof Circle;
  accent: string;
  tint: string;
}[] = [
  {
    key: 'todo',
    label: 'À faire',
    icon: Circle,
    accent: 'bg-ink-muted/40',
    tint: 'bg-mist/80',
  },
  {
    key: 'in_progress',
    label: 'En cours',
    icon: ArrowRight,
    accent: 'bg-accent-500',
    tint: 'bg-accent-50/60',
  },
  {
    key: 'done',
    label: 'Terminé',
    icon: CheckCircle2,
    accent: 'bg-emerald-500',
    tint: 'bg-emerald-50/50',
  },
];

const PRIORITY_STYLES: Record<TaskPriority, string> = {
  low: 'bg-mist-deep text-ink-muted',
  medium: 'bg-warn-soft text-warn-text',
  high: 'bg-danger-soft text-danger-text',
};

const PRIORITY_LABELS: Record<TaskPriority, string> = {
  low: 'Basse',
  medium: 'Moyenne',
  high: 'Haute',
};

function getAvailableTransitions(status: TaskStatus): { value: TaskStatus; label: string }[] {
  switch (status) {
    case 'todo':
      return [{ value: 'in_progress', label: 'Démarrer' }];
    case 'in_progress':
      return [{ value: 'done', label: 'Terminer' }];
    case 'done':
      return [];
  }
}

function projectInitials(name: string) {
  return name
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');
}

export default function DashboardPage() {
  const { logout } = useAuthStore();
  const { projects, fetchProjects, currentProject, setCurrentProject, createProject } =
    useProjectStore();
  const { tasks, fetchTasks, createTask, updateTaskStatus } = useTaskStore();
  const navigate = useNavigate();

  const [showCreateProject, setShowCreateProject] = useState(false);
  const [showCreateTask, setShowCreateTask] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [projectName, setProjectName] = useState('');
  const [taskTitle, setTaskTitle] = useState('');
  const [taskPriority, setTaskPriority] = useState<TaskPriority>('medium');

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

  const selectProject = (project: Project) => {
    setCurrentProject(project);
    setSidebarOpen(false);
  };

  const handleCreateProject = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const project = await createProject(projectName);
      setCurrentProject(project);
      setProjectName('');
      setShowCreateProject(false);
      setSidebarOpen(false);
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
      setTaskPriority('medium');
      setShowCreateTask(false);
    } catch (err) {
      console.error(err);
    }
  };

  const tasksByStatus = (status: TaskStatus): Task[] =>
    tasks.filter((t) => t.status === status);

  const sidebar = (
    <aside className="flex h-full w-[260px] flex-col border-r border-white/10 bg-ink text-white">
      <div className="flex h-14 shrink-0 items-center gap-2.5 px-5">
        <BrandMark size="sm" light />
        <span className="font-display text-lg font-bold tracking-tight">Synkro</span>
      </div>

      <div className="flex items-center justify-between px-5 pb-3 pt-2">
        <p className="text-[11px] font-medium uppercase tracking-[0.14em] text-white/40">
          Projets
        </p>
        <span className="rounded-md bg-white/10 px-1.5 py-0.5 text-[11px] tabular-nums text-white/55">
          {projects.length}
        </span>
      </div>

      <nav className="flex-1 space-y-0.5 overflow-y-auto px-3 pb-3">
        {projects.length === 0 ? (
          <p className="px-2 py-6 text-center text-xs leading-relaxed text-white/40">
            Aucun projet pour l&apos;instant.
          </p>
        ) : (
          projects.map((project) => {
            const active = currentProject?.id === project.id;
            return (
              <button
                key={project.id}
                onClick={() => selectProject(project)}
                className={`flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left transition-colors ${
                  active
                    ? 'bg-white/10 text-white'
                    : 'text-white/65 hover:bg-white/5 hover:text-white'
                }`}
              >
                <span
                  className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[11px] font-semibold ${
                    active ? 'bg-accent-500 text-ink' : 'bg-white/10 text-white/80'
                  }`}
                >
                  {projectInitials(project.name) || <FolderKanban className="h-3.5 w-3.5" />}
                </span>
                <span className="min-w-0 flex-1 truncate text-sm font-medium">{project.name}</span>
                {active && (
                  <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-accent-400" aria-hidden />
                )}
              </button>
            );
          })
        )}
      </nav>

      <div className="shrink-0 space-y-1 border-t border-white/10 p-3">
        <button
          onClick={() => setShowCreateProject(true)}
          className="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2.5 text-sm font-medium text-white/80 transition-colors hover:bg-white/5 hover:text-white"
        >
          <span className="flex h-8 w-8 items-center justify-center rounded-md border border-dashed border-white/25 text-white/70">
            <Plus className="h-4 w-4" />
          </span>
          Nouveau projet
        </button>
        <button
          onClick={handleLogout}
          className="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-white/45 transition-colors hover:bg-white/5 hover:text-white/80"
        >
          <span className="flex h-8 w-8 items-center justify-center">
            <LogOut className="h-4 w-4" />
          </span>
          Déconnexion
        </button>
      </div>
    </aside>
  );

  return (
    <div className="flex h-screen overflow-hidden bg-mist">
      <div className="hidden lg:block">{sidebar}</div>

      {sidebarOpen && (
        <div className="fixed inset-0 z-40 lg:hidden">
          <button
            className="absolute inset-0 bg-ink/50 backdrop-blur-[2px]"
            onClick={() => setSidebarOpen(false)}
            aria-label="Fermer le menu"
          />
          <div className="absolute inset-y-0 left-0 animate-fade-up shadow-lift">{sidebar}</div>
        </div>
      )}

      <div className="flex min-w-0 flex-1 flex-col">
        <header className="flex h-14 shrink-0 items-center justify-between gap-3 border-b border-mist-deep/80 bg-white/75 px-4 backdrop-blur-md sm:px-6">
          <div className="flex min-w-0 items-center gap-3">
            <button
              onClick={() => setSidebarOpen(true)}
              className="btn-ghost !p-2 lg:hidden"
              aria-label="Ouvrir les projets"
            >
              <Menu className="h-5 w-5" />
            </button>
            <div className="min-w-0">
              <p className="truncate font-display text-base font-bold tracking-tight text-ink sm:text-lg">
                {currentProject?.name ?? 'Synkro'}
              </p>
            </div>
          </div>

          {currentProject && (
            <div className="flex items-center gap-3">
              <div className="hidden items-center gap-1.5 text-xs text-accent-700 sm:flex">
                <Radio className="h-3.5 w-3.5 animate-pulse-dot" strokeWidth={2.5} />
                <span className="font-medium">Temps réel</span>
              </div>
              <button onClick={() => setShowCreateTask(true)} className="btn-primary !py-2">
                <Plus className="h-4 w-4" />
                <span className="hidden sm:inline">Nouvelle tâche</span>
              </button>
            </div>
          )}
        </header>

        <main className="flex-1 overflow-y-auto p-4 sm:p-6">
          {!currentProject ? (
            <div className="flex h-full min-h-[320px] flex-col items-center justify-center rounded-2xl border border-dashed border-mist-deep bg-white/50 px-6 text-center animate-fade-up">
              <span className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-accent-50 text-accent-700">
                <FolderKanban className="h-5 w-5" />
              </span>
              <h2 className="font-display text-xl font-bold text-ink">Choisis un projet</h2>
              <p className="mt-2 max-w-sm text-sm text-ink-muted">
                Sélectionne un projet dans la barre latérale, ou crée-en un pour démarrer.
              </p>
              <button onClick={() => setShowCreateProject(true)} className="btn-primary mt-6">
                <Plus className="h-4 w-4" />
                Nouveau projet
              </button>
            </div>
          ) : (
            <div className="grid h-full gap-4 md:grid-cols-3 md:items-start">
              {STATUS_COLUMNS.map((col, index) => {
                const columnTasks = tasksByStatus(col.key);
                const Icon = col.icon;

                return (
                  <section
                    key={col.key}
                    className={`flex max-h-full flex-col rounded-2xl border border-mist-deep/80 ${col.tint} p-3 sm:p-4 animate-fade-up`}
                    style={{ animationDelay: `${index * 50}ms` }}
                  >
                    <header className="mb-3 flex items-center gap-2.5 px-1">
                      <span className={`h-4 w-1 rounded-full ${col.accent}`} aria-hidden />
                      <Icon className="h-3.5 w-3.5 text-ink-muted" strokeWidth={2} />
                      <h3 className="flex-1 text-sm font-semibold text-ink">{col.label}</h3>
                      <span className="rounded-md bg-white/80 px-2 py-0.5 text-xs font-medium tabular-nums text-ink-muted shadow-sm">
                        {columnTasks.length}
                      </span>
                    </header>

                    <div className="min-h-0 flex-1 space-y-2.5 overflow-y-auto">
                      {columnTasks.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-mist-deep/80 px-3 py-10 text-center">
                          <p className="text-xs text-ink-muted/70">Aucune tâche</p>
                        </div>
                      ) : (
                        columnTasks.map((task) => {
                          const transitions = getAvailableTransitions(task.status);
                          return (
                            <article
                              key={task.id}
                              className="group rounded-xl border border-mist-deep/70 bg-white p-3.5 shadow-panel transition-all hover:-translate-y-0.5 hover:shadow-lift"
                            >
                              <p className="text-sm font-medium leading-snug text-ink">
                                {task.title}
                              </p>
                              {task.description && (
                                <p className="mt-1.5 text-xs leading-relaxed text-ink-muted line-clamp-2">
                                  {task.description}
                                </p>
                              )}
                              <div className="mt-3 flex items-center justify-between gap-2">
                                <span
                                  className={`rounded-md px-2 py-0.5 text-[11px] font-medium ${PRIORITY_STYLES[task.priority]}`}
                                >
                                  {PRIORITY_LABELS[task.priority]}
                                </span>

                                {transitions.length > 0 ? (
                                  <button
                                    onClick={() =>
                                      updateTaskStatus(task.id, transitions[0].value)
                                    }
                                    className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-medium text-accent-700 transition-colors hover:bg-accent-50"
                                  >
                                    {transitions[0].label}
                                    <ArrowRight className="h-3 w-3" />
                                  </button>
                                ) : (
                                  <span className="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700">
                                    <CheckCircle2 className="h-3 w-3" />
                                    Fait
                                  </span>
                                )}
                              </div>
                            </article>
                          );
                        })
                      )}
                    </div>
                  </section>
                );
              })}
            </div>
          )}
        </main>
      </div>

      {showCreateProject && (
        <Modal title="Nouveau projet" onClose={() => setShowCreateProject(false)}>
          <form onSubmit={handleCreateProject} className="space-y-4">
            <div>
              <label htmlFor="projectName" className="mb-1.5 block text-sm font-medium text-ink">
                Nom du projet
              </label>
              <input
                id="projectName"
                type="text"
                value={projectName}
                onChange={(e) => setProjectName(e.target.value)}
                className="input-field"
                placeholder="Ex. Refonte landing"
                autoFocus
                required
              />
            </div>
            <div className="flex justify-end gap-2 pt-1">
              <button
                type="button"
                onClick={() => setShowCreateProject(false)}
                className="btn-ghost"
              >
                Annuler
              </button>
              <button type="submit" className="btn-primary">
                Créer
              </button>
            </div>
          </form>
        </Modal>
      )}

      {showCreateTask && (
        <Modal title="Nouvelle tâche" onClose={() => setShowCreateTask(false)}>
          <form onSubmit={handleCreateTask} className="space-y-4">
            <div>
              <label htmlFor="taskTitle" className="mb-1.5 block text-sm font-medium text-ink">
                Titre
              </label>
              <input
                id="taskTitle"
                type="text"
                value={taskTitle}
                onChange={(e) => setTaskTitle(e.target.value)}
                className="input-field"
                placeholder="Ce qu'il faut faire"
                autoFocus
                required
              />
            </div>
            <div>
              <label htmlFor="taskPriority" className="mb-1.5 block text-sm font-medium text-ink">
                Priorité
              </label>
              <select
                id="taskPriority"
                value={taskPriority}
                onChange={(e) => setTaskPriority(e.target.value as TaskPriority)}
                className="input-field"
              >
                <option value="low">Basse</option>
                <option value="medium">Moyenne</option>
                <option value="high">Haute</option>
              </select>
            </div>
            <div className="flex justify-end gap-2 pt-1">
              <button
                type="button"
                onClick={() => setShowCreateTask(false)}
                className="btn-ghost"
              >
                Annuler
              </button>
              <button type="submit" className="btn-primary">
                Créer
              </button>
            </div>
          </form>
        </Modal>
      )}
    </div>
  );
}

function Modal({
  title,
  onClose,
  children,
}: {
  title: string;
  onClose: () => void;
  children: React.ReactNode;
}) {
  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 p-4 backdrop-blur-[2px]"
      onClick={onClose}
      role="presentation"
    >
      <div
        className="w-full max-w-md rounded-xl bg-white p-6 shadow-lift animate-fade-up"
        onClick={(e) => e.stopPropagation()}
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
      >
        <div className="mb-5 flex items-start justify-between gap-3">
          <h3 id="modal-title" className="font-display text-lg font-bold text-ink">
            {title}
          </h3>
          <button onClick={onClose} className="btn-ghost !p-1.5" aria-label="Fermer">
            <X className="h-4 w-4" />
          </button>
        </div>
        {children}
      </div>
    </div>
  );
}
