import type { ReactNode } from 'react';
import BrandMark from './BrandMark';

type AuthShellProps = {
  title: string;
  subtitle: string;
  children: ReactNode;
};

export default function AuthShell({ title, subtitle, children }: AuthShellProps) {
  return (
    <div className="min-h-screen grid lg:grid-cols-2">
      <aside className="relative hidden lg:flex flex-col justify-between bg-ink px-12 py-12 text-white">
        <div className="pointer-events-none absolute inset-0 auth-grid opacity-50" aria-hidden />

        <div className="relative z-10 flex items-center gap-3">
          <BrandMark size="md" light />
          <span className="font-display text-2xl font-bold tracking-tight">Synkro</span>
        </div>

        <div className="relative z-10 max-w-sm">
          <h1 className="font-display text-3xl font-bold leading-snug tracking-tight">
            Un board partagé, mis à jour en direct.
          </h1>
          <p className="mt-4 text-sm leading-relaxed text-white/60">
            Projets et tâches synchronisés via Mercure — pour rester aligné sans rafraîchir.
          </p>
        </div>

        <p className="relative z-10 text-xs text-white/35">Synkro</p>
      </aside>

      <main className="flex items-center justify-center bg-mist px-6 py-12 sm:px-10">
        <div className="w-full max-w-[400px]">
          <div className="mb-8 flex items-center gap-3 lg:hidden">
            <BrandMark size="sm" />
            <span className="font-display text-xl font-bold text-ink">Synkro</span>
          </div>

          <h2 className="font-display text-2xl font-bold tracking-tight text-ink">{title}</h2>
          <p className="mt-1.5 text-sm text-ink-muted">{subtitle}</p>

          <div className="mt-8">{children}</div>
        </div>
      </main>
    </div>
  );
}
