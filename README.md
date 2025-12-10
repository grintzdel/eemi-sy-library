# Nouvelle architecture

**✅ Séparation en modules** : `Book/`, `User/`, `Shared/`
**✅ Architecture hexagonale** : Presentation → Application → Domain → Infrastructure
**✅ CQRS** : Commands (écriture) et Queries (lecture) séparées
**✅ ValueObjects** : `UserId`, `BookId` typés et validés
**✅ Services métier** : `BorrowLimitService` pour la règle des 3 livres
**✅ Exceptions typées** : `BookAlreadyBorrowedError`, `BorrowLimitExceededError`
**✅ Controllers minimalistes** : Une seule ligne par méthode

### Mapping des fonctionnalités

| Original | Nouvelle architecture |
|----------|----------------------|
| `Book::$a` (ID) | `BookEntity::$id` (string) + `BookId` ValueObject |
| `Book::g()` (emprunter) | `BorrowBookCommand` + `BorrowBookCommandHandler` |
| `Book::h()` (retourner) | `ReturnBookCommand` + `ReturnBookCommandHandler` |
| `User::$z` (livres) | `UserEntity::$borrowedBooks` (private, encapsulé) |
| `User::i()` (limite 3) | `BorrowLimitService::ensureCanBorrowBook()` |
| `User::j()` (retirer) | `UserEntity::removeBorrowedBook()` |
| `LibraryController::addBook()` | `BookController::addBook()` + `AddBookCommandHandler` |
| Validation dans controller | Validation dans `Command::fromRequest()` |
| Retour de strings | Exceptions métier + ViewModels |

### Exemples de code : Avant / Après

#### 🔴 Avant : Book.php (illisible)
```php
class Book {
    public $a; // ID
    public $b; // Titre
    public $c; // Auteur
    public $d = false; // Statut d'emprunt

    public function g() { // Emprunter un livre
        if ($this->d) {
            return "Déjà pris.";
        }
        $this->d = true;
        $this->e = new \DateTime();
        return "Pris.";
    }
}
```

#### 🟢 Après : Architecture Clean
```php
// BookEntity.php (Infrastructure)
class BookEntity {
    private ?string $id = null;
    private string $title;
    private string $author;
    private bool $borrowed = false;

    public function borrow(): self {
        $this->borrowed = true;
        $this->borrowedAt = new \DateTime();
        return $this;
    }
}

// BorrowBookCommandHandler.php (Application)
public function handle(BorrowBookCommand $command): SuccessViewModel {
    $book = $this->bookRepository->findByTitle($command->bookTitle);
    if (!$book) throw new BookNotFoundError();
    if ($book->isBorrowed()) throw new BookAlreadyBorrowedError();

    $book->borrow();
    return new SuccessViewModel('Book borrowed successfully');
}
```

#### 🔴 Avant : User.php (règle métier mal placée)
```php
class User {
    public $x; // ID
    public $z = []; // Livres empruntés

    public function i($b) {
        if (count($this->z) >= 3) {
            return "Trop de livres.";
        }
        $this->z[] = $b;
        return "OK.";
    }
}
```

#### 🟢 Après : Service métier dédié
```php
// BorrowLimitService.php (Infrastructure/Service)
class BorrowLimitService {
    private const int MAX_BORROWED_BOOKS = 3;

    public function ensureCanBorrowBook(UserEntity $user): void {
        if (count($user->getBorrowedBooks()) >= self::MAX_BORROWED_BOOKS) {
            throw new BorrowLimitExceededError($this->getMaximumBorrowLimit());
        }
    }
}

// UserEntity.php (Infrastructure - sans logique métier)
class UserEntity {
    private array $borrowedBooks = [];

    public function addBorrowedBook(BookEntity $book): self {
        $this->borrowedBooks[] = $book->getId();
        return $this;
    }
}
```

#### 🔴 Avant : LibraryController.php (tout mélangé)
```php
#[Route('/library')]
class LibraryController extends AbstractController {
    #[Route('/add-book', methods: ['POST'])]
    public function addBook(Request $req): JsonResponse {
        $data1 = json_decode($req->getContent(), true);

        if (!isset($data1['t']) || !isset($data1['a'])) {
            return new JsonResponse(['error' => 'Informations incomplètes'], 400);
        }

        $b = new Book();
        $b->b = $data1['t'];
        $b->c = $data1['a'];
        $this->em->persist($b);
        $this->em->flush();
        return new JsonResponse(['m' => 'OK']);
    }
}
```

#### 🟢 Après : Controllers minimalistes + CQRS
```php
// BookController.php (Presentation)
#[Route('/api/books')]
final class BookController extends AppController {
    #[Route('', methods: ['POST'])]
    public function addBook(Request $request): JsonResponse {
        return $this->dispatch(AddBookCommand::fromRequest($request));
    }
}

// AddBookCommand.php (Application)
final readonly class AddBookCommand {
    private function __construct(
        public string $title,
        public string $author
    ) {}

    public static function fromRequest(Request $request): self {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['title']) || !isset($data['author'])) {
            throw new \InvalidArgumentException('Title and author are required');
        }
        return new self($data['title'], $data['author']);
    }
}

// AddBookCommandHandler.php (Application)
public function handle(AddBookCommand $command): SuccessViewModel {
    $book = new BookEntity();
    $book->setTitle($command->title);
    $book->setAuthor($command->author);

    $this->bookRepository->save($book);
    $this->bookRepository->flush();

    return new SuccessViewModel('Book added successfully');
}
```

## Respect des principes SOLID

Cette codebase applique rigoureusement les 5 principes SOLID pour garantir maintenabilité, évolutivité et testabilité.

### S - Single Responsibility Principle (Responsabilité unique)

Chaque classe a une seule raison de changer.

#### ✅ Exemples concrets

**1. Séparation des préoccupations dans les Handlers**
- `BorrowBookCommandHandler` : src/Modules/Book/Application/Command/BorrowBookCommandHandler.php:16-56
  - **Une seule responsabilité** : orchestrer l'emprunt d'un livre
  - Ne gère PAS la validation (déléguée à `Command::fromRequest()`)
  - Ne gère PAS la règle des 3 livres (déléguée à `BorrowLimitService`)
  - Ne gère PAS la persistance (déléguée à `Repository`)

**2. Service dédié pour la règle métier**
- `BorrowLimitService` : src/Modules/User/Infrastructure/Service/BorrowLimitService.php:10-38
  - **Une seule responsabilité** : vérifier la limite d'emprunt
  - Encapsule uniquement la règle "maximum 3 livres"
  - Peut évoluer indépendamment (ex: limite variable par type d'utilisateur)

**3. ViewModels pour la présentation**
- `BookViewModel` : src/Modules/Book/Application/ViewModel/BookViewModel.php:7-18
  - **Une seule responsabilité** : représenter un livre pour l'API
  - Sépare la représentation API de l'entité Doctrine
  - Permet de modifier l'API sans toucher à la base de données

**4. Controllers minimalistes**
- `BookController` : src/Modules/Book/Presentation/Controller/BookController.php:20-66
  - **Une seule responsabilité** : router les requêtes HTTP
  - Aucune logique métier
  - Une ligne par méthode : `return $this->dispatch(Command::fromRequest($request))`

#### 🔴 Avant (violation SRP)
```php
class LibraryController {
    public function borrowBook(Request $req) {
        // ❌ Parsing, validation, logique métier, persistance = 4 responsabilités
        $data = json_decode($req->getContent(), true);
        if (!isset($data['bookTitle'])) return new JsonResponse(['error' => '...'], 400);

        $book = $this->em->getRepository(Book::class)->findByTitle($data['bookTitle']);
        if ($book->isBorrowed()) return new JsonResponse(['error' => '...'], 400);

        if (count($user->getBorrowedBooks()) >= 3) return new JsonResponse(['error' => '...'], 400);

        $book->borrow();
        $this->em->flush();
    }
}
```

---

### O - Open/Closed Principle (Ouvert/Fermé)

Les classes sont ouvertes à l'extension, fermées à la modification.

#### ✅ Exemples concrets

**1. Interfaces de repositories**
- `IBookRepository` : src/Modules/Book/Domain/Repository/IBookRepository.php:9-20
- `IUserRepository` : src/Modules/User/Domain/Repository/IUserRepository.php:9-18

```php
interface IBookRepository {
    public function save(BookEntity $book): void;
    public function findByTitle(string $title): ?BookEntity;
    public function findById(string $id): ?BookEntity;
    public function findAll(): array;
}
```

**Extension possible sans modification** :
- Implémenter `DoctrineBookRepository` (SQL)
- Implémenter `MongoBookRepository` (NoSQL)
- Implémenter `InMemoryBookRepository` (tests)
- Les handlers ne changent pas !

**2. Message Bus extensible**
- `AppController` : src/Modules/Shared/Presentation/Controllers/AppController.php:13-41

```php
abstract class AppController {
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {}
}
```

**Extension possible** :
- Ajouter des middlewares (logging, caching, validation)
- Passer en asynchrone (RabbitMQ, Redis)
- Ajouter des event listeners
- Aucune modification des contrôleurs ou handlers

**3. ValueObjects extensibles**
- `UserId` : src/Modules/Shared/Domain/ValueObject/UserId.php:7-31

```php
final readonly class UserId {
    public function __construct(public string $value) {
        if (empty($value)) throw new \InvalidArgumentException('...');
    }
}
```

**Extension possible** :
- Créer `UuidUserId` avec validation UUID
- Créer `EmailUserId` avec validation email
- Sans toucher au code existant (polymorphisme)

---

### L - Liskov Substitution Principle (Substitution de Liskov)

Les classes dérivées doivent pouvoir remplacer leurs classes de base.

#### ✅ Exemples concrets

**1. Tous les contrôleurs sont substituables**
- `BookController extends AppController`
- `UserController extends AppController`

Les deux peuvent être utilisés de manière interchangeable partout où `AppController` est attendu, car ils respectent le contrat :
- `dispatch(Command): JsonResponse`
- `dispatchQuery(Query): JsonResponse`

**2. Les repositories sont interchangeables**

```php
// Le handler dépend de l'interface, pas de l'implémentation
final readonly class BorrowBookCommandHandler {
    public function __construct(
        private IBookRepository $bookRepository,  // Interface, pas classe concrète
        private IUserRepository $userRepository
    ) {}
}
```

**Substitution garantie** :
```php
// Production
$handler = new BorrowBookCommandHandler(
    new DoctrineBookRepository($entityManager),
    new DoctrineUserRepository($entityManager)
);

// Tests
$handler = new BorrowBookCommandHandler(
    new InMemoryBookRepository(),
    new InMemoryUserRepository()
);
```

Le handler fonctionne identiquement dans les deux cas !

---

### I - Interface Segregation Principle (Ségrégation des interfaces)

Les clients ne doivent pas dépendre d'interfaces qu'ils n'utilisent pas.

#### ✅ Exemples concrets

**1. Interfaces spécifiques par besoin**

Au lieu d'une interface monolithique :
```php
// ❌ Interface trop large
interface IRepository {
    public function find($id);
    public function findAll();
    public function save($entity);
    public function delete($entity);
    public function flush();
    public function clear();
    public function detach($entity);
    public function merge($entity);
    // ... 15 méthodes de plus
}
```

On a des interfaces ciblées :
```php
// ✅ Interface minimale
interface IBookRepository {
    public function save(BookEntity $book): void;
    public function findByTitle(string $title): ?BookEntity;
    public function findById(string $id): ?BookEntity;
    public function findAll(): array;
    public function flush(): void;
}
```

**2. Handlers avec dépendances précises**
- `GetBookByIdQueryHandler` : src/Modules/Book/Application/Query/GetBookByIdQueryHandler.php:11-35

```php
// ✅ Ne dépend QUE de ce dont il a besoin
final readonly class GetBookByIdQueryHandler {
    public function __construct(
        private IBookRepository $bookRepository  // Pas de UserRepository, pas de Services inutiles
    ) {}
}
```

**3. Séparation Command/Query dans AppController**

Au lieu de :
```php
// ❌ Une seule méthode pour tout
public function dispatch($message): JsonResponse
```

On a :
```php
// ✅ Deux méthodes spécialisées
public function dispatch($command): JsonResponse       // Pour les writes
public function dispatchQuery($query): JsonResponse    // Pour les reads
```

Les contrôleurs en lecture n'ont pas accès au `commandBus` !

---

### D - Dependency Inversion Principle (Inversion des dépendances)

Dépendre des abstractions, pas des implémentations concrètes.

#### ✅ Exemples concrets

**1. Handlers dépendent d'interfaces**
- `BorrowBookCommandHandler` : src/Modules/Book/Application/Command/BorrowBookCommandHandler.php:16-23

```php
// ✅ Dépendances via interfaces
final readonly class BorrowBookCommandHandler {
    public function __construct(
        private IBookRepository     $bookRepository,     // Interface
        private IUserRepository     $userRepository,     // Interface
        private BorrowLimitService  $borrowLimitService  // Service
    ) {}
}
```

**Avantages** :
- Testable avec des mocks
- L'implémentation peut changer (Doctrine → MongoDB)
- Aucun couplage fort

**2. Architecture en couches respectant DIP**

```
┌─────────────────────────────────┐
│   Presentation (Controllers)     │ ← Dépend de
└─────────────────────────────────┘
              ↓
┌─────────────────────────────────┐
│   Application (Handlers)         │ ← Dépend de
└─────────────────────────────────┘
              ↓
┌─────────────────────────────────┐
│   Domain (Interfaces)            │ ← Abstraction
└─────────────────────────────────┘
              ↑
┌─────────────────────────────────┐
│   Infrastructure (Implémentation)│ ← Implémente
└─────────────────────────────────┘
```

**Inversion** : Infrastructure dépend de Domain, pas l'inverse !

**3. Injection de dépendances via constructeur**
- `AppController` : src/Modules/Shared/Presentation/Controllers/AppController.php:15-18

```php
public function __construct(
    private readonly MessageBusInterface $commandBus,  // Interface Symfony
    private readonly MessageBusInterface $queryBus     // Interface Symfony
) {}
```

**Configuration dans services.yaml** :
```yaml
services:
    _defaults:
        autowire: true      # Injection automatique
        autoconfigure: true # Configuration automatique

    App\Modules\Book\Domain\Repository\IBookRepository:
        class: App\Modules\Book\Infrastructure\Repository\DoctrineBookRepository
```

Symfony injecte automatiquement l'implémentation concrète !

---

### Résumé : Avant/Après SOLID

#### 🔴 Avant (violations multiples)
```php
class LibraryController {
    private $em;  // ❌ Dépend de l'implémentation Doctrine (violation D)

    public function borrowBook(Request $req) {
        // ❌ 5 responsabilités dans une méthode (violation S)
        // ❌ Impossible de changer la persistance sans modifier le code (violation O)
        // ❌ Logique métier dans le contrôleur (violation I)

        $data = json_decode($req->getContent(), true);
        $book = $this->em->getRepository(Book::class)->findByTitle($data['bookTitle']);

        if (count($user->getBorrowedBooks()) >= 3) {
            return new JsonResponse(['error' => 'Limite atteinte'], 400);
        }

        $book->borrow();
        $this->em->flush();
    }
}
```

#### 🟢 Après (SOLID complet)
```php
// ✅ S : Une responsabilité par classe
// ✅ O : Extension via interfaces
// ✅ L : Substituabilité garantie
// ✅ I : Interfaces minimales
// ✅ D : Dépendances inversées

// Controller (routing uniquement)
final class BookController extends AppController {
    public function borrowBook(Request $request): JsonResponse {
        return $this->dispatch(BorrowBookCommand::fromRequest($request));
    }
}

// Command (validation)
final readonly class BorrowBookCommand {
    public static function fromRequest(Request $request): self { /* ... */ }
}

// Handler (orchestration)
final readonly class BorrowBookCommandHandler {
    public function __construct(
        private IBookRepository $bookRepository,
        private BorrowLimitService $borrowLimitService
    ) {}

    public function handle(BorrowBookCommand $command): SuccessViewModel { /* ... */ }
}

// Service (règle métier)
final readonly class BorrowLimitService {
    public function ensureCanBorrowBook(UserEntity $user): void { /* ... */ }
}
```

---

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
