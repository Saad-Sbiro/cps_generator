# CpsGen - Project Setup

## Stack

- **Backend**: Laravel
- **Frontend**: React
- **Database**: PostgreSQL
- **Auth**: Laravel Sanctum
- **Containerization**: Docker

---

## Current Progress

### Done
- Docker environment setup (backend, frontend, database)
- Database migrations & seeders
- Models: `Projet`, `Article`, `ArticleVariant`, `PrixCatalogue`, `ProjectArticle`, `ProjectPrix`, `ExportDocument`
- Export services: CPS, RC, BRD document generation
- Full REST API with Sanctum authentication

### API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/status` | No | Health check + DB status |
| POST | `/api/auth/register` | No | Register |
| POST | `/api/auth/login` | No | Login |
| POST | `/api/auth/logout` | Yes | Logout |
| GET | `/api/auth/me` | Yes | Current user |
| GET | `/api/catalogue-articles/categories` | Yes | Article categories |
| GET/POST/PUT/DELETE | `/api/catalogue-articles` | Yes | Catalogue CRUD |
| GET/POST/PUT/DELETE | `/api/articles` | Yes | Articles CRUD |
| GET/POST/PUT/DELETE | `/api/projets` | Yes | Projects CRUD |
| GET/POST/PUT/DELETE | `/api/projets/{projet}/articles` | Yes | Project articles |
| GET/POST/PUT/DELETE | `/api/projets/{projet}/prix` | Yes | Project prices |
| POST | `/api/projets/{projet}/exports/cps` | Yes | Export CPS |
| POST | `/api/projets/{projet}/exports/rc` | Yes | Export RC |
| POST | `/api/projets/{projet}/exports/brd` | Yes | Export BRD |
| GET | `/api/projets/{projet}/exports` | Yes | List exports |
| GET | `/api/exports/{export}/download` | No | Download export |
| DELETE | `/api/exports/{export}` | Yes | Delete export |

---

## Next Steps

- Frontend (React) setup
