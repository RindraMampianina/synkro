# Synkro

Kanban collaboratif en temps réel : projets, tâches, transitions de statut, push Mercure.

API Symfony / API Platform / Mercure, front React (Vite + Zustand).

## Démarrage

Prérequis : Docker + Make.

```bash
make install   # build, composer, BDD, clés JWT, cache
make start     # si les containers ne tournent pas déjà
```

Services locaux :

| Service  | URL |
|----------|-----|
| Frontend | http://localhost:5173 |
| API      | http://localhost:8080/api |
| Docs API | http://localhost:8080/api/docs |
| Mercure  | http://localhost:3000 |

Compte de démo (après fixtures) :

```bash
make db-reset
# email: demo@synkro.local
# password: password123
```

## Architecture (API)

```
UI/Api  →  Application (Command/Handler)  →  Domain  →  Infrastructure
```

- **Domain** : `User`, `Project`, `Task`, transitions de statut, accès projet (`isAccessibleBy`)
- **Application** : commandes Messenger (sync) + handlers
- **Infrastructure** : Doctrine, Mercure, résolution de l’utilisateur courant
- **UI** : resources API Platform + providers/processors

Le front consomme l’API en JWT, tient l’état dans Zustand, et s’abonne à Mercure (`EventSource`) pour les tâches du projet courant et la liste des projets.

## Sécurité (état actuel)

- Routes `/api/*` protégées JWT (sauf auth / docs)
- Lecture / écriture tâches et projet : réservées au **owner / membres**
- Création de projet : owner pris côté serveur (pas depuis le body client)
- Mercure : abonnement anonyme en local (à durcir en prod avec JWT subscriber)

## Tests

```bash
make test            # PHPUnit + Vitest
make test-backend
make test-frontend
```

## Limites

- Messenger en transport **sync** (pas de file async pour l’instant)
- Pas encore de CI multi-environnements ni d’auth Mercure en prod
- Pas de gestion fine des invitations membres (owner auto-ajouté à la création)

Ces choix sont volontaires pour garder le projet lisible ; ils sont de bons sujets d’entretien.

## Stack

- PHP 8.3, Symfony 7.4, API Platform, Lexik JWT, Doctrine, Mercure
- React 19, TypeScript, Vite, Tailwind, Zustand, Lucide
- Docker Compose : php, nginx, postgres, mercure, frontend
