# Library Management System

API de gestion de bibliothèque construite avec Symfony, suivant une architecture modulaire avec CQRS et DDD.

## Architecture

### Structure modulaire

```
src/Modules/
├── Book/               # Module de gestion des livres
├── User/               # Module de gestion des utilisateurs
└── Shared/             # Composants partagés entre modules
```

### Couches par module

Chaque module suit une architecture hexagonale :

- **Presentation** : Controllers (API REST)
- **Application** : Commands, Queries, Handlers, ViewModels
- **Domain** : Interfaces de repositories, Erreurs métier
- **Infrastructure** : Implémentations (Repositories SQL, Entities Doctrine)

### Composants partagés

Le module `Shared` contient les ValueObjects utilisés entre modules :
- `UserId` : Identifiant utilisateur (string)
- `BookId` : Identifiant livre (string)

## Use Cases

### Module Book

#### 1. Lister tous les livres
```
GET /api/books
→ GetAllBooksQuery
→ GetAllBooksQueryHandler
→ BookViewModel[]
```

#### 2. Récupérer un livre par ID
```
GET /api/books/{id}
→ GetBookByIdQuery(BookId)
→ GetBookByIdQueryHandler
→ BookViewModel
✗ BookNotFoundError (404)
```

#### 3. Ajouter un livre
```
POST /api/books
Body: {"title": "...", "author": "..."}
→ AddBookCommand::fromRequest()
   ✗ InvalidArgumentException si title/author manquant
→ AddBookCommandHandler
→ SuccessViewModel
```

#### 4. Emprunter un livre
```
POST /api/books/borrow
Body: {"bookTitle": "...", "userId": "..."}
→ BorrowBookCommand::fromRequest()
   ✗ InvalidArgumentException si bookTitle/userId manquant
→ BorrowBookCommandHandler
   ✗ BookNotFoundError (404) si livre inexistant
   ✗ UserNotFoundError (404) si utilisateur inexistant
   ✗ BookAlreadyBorrowedError (400) si livre déjà emprunté
   ✗ BorrowLimitExceededError (400) si utilisateur a déjà 3 livres
→ SuccessViewModel
```

#### 5. Retourner un livre
```
POST /api/books/return
Body: {"bookTitle": "...", "userId": "..."}
→ ReturnBookCommand::fromRequest()
   ✗ InvalidArgumentException si bookTitle/userId manquant
→ ReturnBookCommandHandler
   ✗ BookNotFoundError (404) si livre inexistant
   ✗ UserNotFoundError (404) si utilisateur inexistant
→ SuccessViewModel
```

### Module User

#### 1. Lister tous les utilisateurs
```
GET /api/users
→ GetAllUsersQuery
→ GetAllUsersQueryHandler
→ UserViewModel[]
```

#### 2. Récupérer un utilisateur par ID
```
GET /api/users/{id}
→ GetUserByIdQuery(UserId)
→ GetUserByIdQueryHandler
→ UserViewModel
✗ UserNotFoundError (404)
```

#### 3. Créer un utilisateur
```
POST /api/users
Body: {"name": "..."}
→ CreateUserCommand::fromRequest()
   ✗ InvalidArgumentException si name manquant
→ CreateUserCommandHandler
→ UserViewModel
```

## Flow de données

### Pattern CQRS simplifié

**Commands** (écriture) :
```
Controller
  → Command::fromRequest(Request)     // Validation
  → CommandHandler                     // Logique métier
  → Repository                         // Persistence
  → ViewModel                          // Réponse
```

**Queries** (lecture) :
```
Controller
  → Query(ValueObject)                 // Simple DTO
  → QueryHandler                       // Récupération
  → Repository                         // Lecture
  → ViewModel                          // Réponse
```

### Principe de validation

La validation est déléguée aux Commands/Queries :
- Controllers : routage uniquement (`return $this->dispatch(Command::fromRequest($request))`)
- Commands : parsing et validation des données
- Handlers : logique métier et règles de domaine
- Errors : exceptions métier typées (BookAlreadyBorrowedError, etc.)

## Règles métier

### Livre (Book)
- ID : string généré via `uniqid('book_', true)`
- Un livre ne peut être emprunté qu'une fois à la fois
- Un livre emprunté a une date `borrowedAt`
- Un livre retourné a une date `returnedAt`

### Utilisateur (User)
- ID : string généré via `uniqid('user_', true)`
- Limite : 3 livres empruntés maximum (via `BorrowLimitService`)
- Liste des livres empruntés stockée en JSON

## Technologies

- **Symfony 7.x** : Framework
- **Doctrine ORM** : Persistence
- **Symfony Messenger** : Bus CQRS (CommandBus, QueryBus)
- **Docker + FrankenPHP** : Runtime

## Installation

1. Installer Docker Compose (v2.10+)
2. Builder les images : `docker compose build --pull --no-cache`
3. Démarrer : `docker compose up --wait`
4. Accéder à l'API : `https://localhost/api`

## Exemples de requêtes

```bash
# Créer un utilisateur
curl -X POST https://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe"}'

# Ajouter un livre
curl -X POST https://localhost/api/books \
  -H "Content-Type: application/json" \
  -d '{"title": "Clean Code", "author": "Robert Martin"}'

# Emprunter un livre
curl -X POST https://localhost/api/books/borrow \
  -H "Content-Type: application/json" \
  -d '{"bookTitle": "Clean Code", "userId": "user_xxx"}'

# Retourner un livre
curl -X POST https://localhost/api/books/return \
  -H "Content-Type: application/json" \
  -d '{"bookTitle": "Clean Code", "userId": "user_xxx"}'

# Lister les livres
curl https://localhost/api/books

# Récupérer un livre
curl https://localhost/api/books/book_xxx

# Récupérer un utilisateur
curl https://localhost/api/users/user_xxx
```
