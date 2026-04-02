# Credit Wallet API - Laravel

## Informations Étudiant
* **Nom :** Bouhaya Wael
* **Établissement :** ENSAM Casablanca - Université Hassan II
* **Filière :** Département Génie Informatique et IA
* **Enseignant :** Dr. WARDI Ahmed

---

## Présentation du Projet
Ce projet consiste en la création d'une API REST de gestion de portefeuille de crédits avec authentification JWT.  
L'objectif est de mettre en pratique :

- L'authentification sécurisée avec **JWT (JSON Web Token)**
- Le contrôle d'accès par **rôle (user / admin)**
- La gestion d'un **portefeuille de points** avec règles métier
- Le respect des standards REST
- Une structure de réponse **JSON normalisée**

---

## Technologies Utilisées

- **Laravel**
- **PHP**
- **MySQL**
- **php-open-source-saver/jwt-auth**
- **Postman** (tests API)

---

## Aperçu du Code Source

### 1️. Modèle & Migrations (`User`)

Deux migrations séparées : la migration par défaut de Laravel et une migration dédiée pour les colonnes métier.
```php
// database/migrations/xxxx_add_solde_role_to_users_table.php
public function up(): void {
    Schema::table('users', function (Blueprint $table) {
        $table->integer('solde')->default(0);
        $table->enum('role', ['user', 'admin'])->default('user');
    });
}
```
```php
// app/Models/User.php
class User extends Authenticatable implements JWTSubject
{
    protected $fillable = ['name', 'email', 'password', 'role', 'solde'];

    public function getJWTIdentifier() { return $this->getKey(); }
    public function getJWTCustomClaims() { return []; }
}
```

---

### 2️. Routes API (`api.php`)
```php
// Auth — public
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Wallet — utilisateur authentifié
Route::middleware('auth:api')->prefix('wallet')->group(function () {
    Route::get('/',       [WalletController::class, 'index']);
    Route::post('/spend', [WalletController::class, 'spend']);
});

// Admin uniquement
Route::middleware(['auth:api', 'role:admin'])->prefix('admin/wallet')->group(function () {
    Route::post('/{user}/credit', [AdminWalletController::class, 'credit']);
    Route::post('/{user}/debit',  [AdminWalletController::class, 'debit']);
});
```

---

### 3️. Contrôleurs

#### ➤ Authentification (POST /api/auth/login)
```php
public function login(Request $request) {
    $credentials = $request->only('email', 'password');

    if (!$token = auth('api')->attempt($credentials)) {
        return response()->json(['error' => 'Identifiants invalides'], 401);
    }

    return response()->json([
        'token' => $token,
        'role'  => auth('api')->user()->role,
    ]);
}
```

#### ➤ Dépense de points (POST /api/wallet/spend)
```php
public function spend(Request $request) {
    $user = auth('api')->user();

    if ($request->montant > $user->solde) {
        return response()->json(['error' => 'Solde insuffisant'], 422);
    }

    $user->solde -= $request->montant;
    $user->save();

    return response()->json(['solde' => $user->solde]);
}
```

#### ➤ Crédit admin (POST /api/admin/wallet/{user}/credit)
```php
public function credit(Request $request, User $user) {
    $user->solde += $request->montant;
    $user->save();

    return response()->json(['solde' => $user->solde]);
}
```

---

### 4️. Middleware CheckRole
```php
public function handle(Request $request, Closure $next, string $role) {
    if (auth('api')->user()->role !== $role) {
        return response()->json(['error' => 'Accès refusé'], 403);
    }
    return $next($request);
}
```

---

## Tests Postman (Endpoints)

### Cas de Succès

| Méthode | URI | Action | Status |
|---------|-----|--------|--------|
| **POST** | `/api/auth/register` | Inscription | `201 Created` |
| **POST** | `/api/auth/login` | Connexion + token JWT | `200 OK` |
| **GET** | `/api/auth/me` | Profil utilisateur connecté | `200 OK` |
| **POST** | `/api/auth/logout` | Déconnexion | `200 OK` |
| **GET** | `/api/wallet` | Consulter le solde | `200 OK` |
| **POST** | `/api/wallet/spend` | Dépenser des points | `200 OK` |
| **POST** | `/api/admin/wallet/{id}/credit` | Créditer un utilisateur | `200 OK` |
| **POST** | `/api/admin/wallet/{id}/debit` | Débiter un utilisateur | `200 OK` |

---

### Gestion des Erreurs

| Scénario | Status |
|----------|--------|
| Accès sans token | `401 Unauthorized` |
| Montant inférieur à 10 points | `422 Unprocessable` |
| Solde insuffisant (spend) | `422 Unprocessable` |
| Solde insuffisant (debit admin) | `422 Unprocessable` |
| Accès route admin avec rôle user | `403 Forbidden` |

---

## Structure des Livrables
```
app/
 ├── Models/
 │   └── User.php
 ├── Http/
 │   ├── Controllers/
 │   │   ├── AuthController.php
 │   │   ├── WalletController.php
 │   │   └── AdminWalletController.php
 │   └── Middleware/
 │       └── CheckRole.php

database/
 └── migrations/
     ├── xxxx_create_users_table.php
     └── xxxx_add_solde_role_to_users_table.php

routes/
 └── api.php

config/
 └── auth.php
```

---

## Lancement du Projet
```bash
git clone <repository_url>
cd credit-wallet-api
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```

L'API sera accessible via :
```
http://127.0.0.1:8000/api
```
