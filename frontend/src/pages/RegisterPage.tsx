import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { AlertCircle, ArrowRight, Loader2 } from 'lucide-react';
import AuthShell from '../components/AuthShell';
import useAuthStore from '../stores/authStore';

export default function RegisterPage() {
  const [email, setEmail] = useState('');
  const [fullName, setFullName] = useState('');
  const [plainPassword, setPlainPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { register } = useAuthStore();
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await register(email, fullName, plainPassword);
      navigate('/login');
    } catch {
      setError('Une erreur est survenue. Vérifie tes informations.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AuthShell title="Créer un compte" subtitle="Quelques infos pour démarrer avec Synkro.">
      {error && (
        <div className="mb-5 flex items-start gap-2.5 rounded-lg border border-danger-text/15 bg-danger-soft px-3.5 py-3 text-sm text-danger-text">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" strokeWidth={2} />
          <span>{error}</span>
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="fullName" className="mb-1.5 block text-sm font-medium text-ink">
            Nom complet
          </label>
          <input
            id="fullName"
            type="text"
            value={fullName}
            onChange={(e) => setFullName(e.target.value)}
            className="input-field"
            placeholder="Rindra Mampianina"
            autoComplete="name"
            required
          />
        </div>

        <div>
          <label htmlFor="email" className="mb-1.5 block text-sm font-medium text-ink">
            Email
          </label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="input-field"
            placeholder="rindra@synkro.com"
            autoComplete="email"
            required
          />
        </div>

        <div>
          <label htmlFor="password" className="mb-1.5 block text-sm font-medium text-ink">
            Mot de passe
          </label>
          <input
            id="password"
            type="password"
            value={plainPassword}
            onChange={(e) => setPlainPassword(e.target.value)}
            className="input-field"
            placeholder="8 caractères minimum"
            autoComplete="new-password"
            minLength={8}
            required
          />
        </div>

        <button type="submit" disabled={loading} className="btn-primary w-full mt-2">
          {loading ? (
            <>
              <Loader2 className="h-4 w-4 animate-spin" />
              Création…
            </>
          ) : (
            <>
              Créer mon compte
              <ArrowRight className="h-4 w-4" />
            </>
          )}
        </button>
      </form>

      <p className="mt-6 text-center text-sm text-ink-muted">
        Déjà un compte ?{' '}
        <Link to="/login" className="font-medium text-accent-700 hover:text-accent-800">
          Se connecter
        </Link>
      </p>
    </AuthShell>
  );
}
