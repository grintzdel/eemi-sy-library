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
