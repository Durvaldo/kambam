# Middleware no Laravel

## O que é Middleware?

Middleware é uma camada intermediária entre a requisição HTTP e a resposta da aplicação. Ele atua como um "filtro" que intercepta requisições antes que cheguem aos controllers, permitindo executar validações, autenticação, logs, ou qualquer outra lógica.

## Como Funciona?

```
Request → Middleware → Controller → Response
         ↓
    Verifica auth
    Valida dados  
    Registra logs
    etc...
```

## Tipos de Middleware

### 1. **Global Middleware**
Executado em todas as requisições da aplicação.

### 2. **Route Middleware** 
Aplicado a rotas específicas ou grupos de rotas.

### 3. **Middleware de Grupo**
Aplicado a um conjunto de rotas (ex: 'web', 'api').

## Criando Middleware

### Comando Artisan
```bash
php artisan make:middleware NomeDoMiddleware
```

### Estrutura Básica
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExemploMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Código executado ANTES do controller
        
        if (!$algumaCond Podeicao) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        
        $response = $next($request); // Executa o próximo middleware/controller
        
        // Código executado DEPOIS do controller
        
        return $response;
    }
}
```

## Middleware de Autenticação - Exemplo Prático

### 1. Criar o Middleware
```bash
php artisan make:middleware AuthenticateToken
```

### 2. Implementar a Lógica
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AuthenticateToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'error' => [
                    'code' => 'TOKEN_MISSING',
                    'message' => 'Token de acesso é obrigatório'
                ]
            ], 401);
        }
        
        $user = User::findByToken($token);
        
        if (!$user) {
            return response()->json([
                'error' => [
                    'code' => 'TOKEN_INVALID',
                    'message' => 'Token inválido ou expirado'
                ]
            ], 401);
        }
        
        // Disponibilizar usuário para o controller
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}
```

### 3. Registrar o Middleware

**Em `app/Http/Kernel.php`:**
```php
protected $middlewareAliases = [
    'auth.token' => \App\Http\Middleware\AuthenticateToken::class,
    // outros middlewares...
];
```

### 4. Usar nas Rotas

**Rota individual:**
```php
Route::post('/boards', [BoardController::class, 'store'])
    ->middleware('auth.token');
```

**Grupo de rotas:**
```php
Route::middleware('auth.token')->group(function () {
    Route::post('/boards', [BoardController::class, 'store']);
    Route::patch('/boards/{id}', [BoardController::class, 'update']);
    Route::delete('/boards/{id}', [BoardController::class, 'destroy']);
});
```

**Prefixo com middleware:**
```php
Route::prefix('api')->middleware('auth.token')->group(function () {
    // Todas essas rotas precisarão de autenticação
    Route::apiResource('boards', BoardController::class);
});
```

## Middleware de Autorização (Owner Only)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Board;

class EnsureOwner
{
    public function handle(Request $request, Closure $next)
    {
        $boardId = $request->route('board') ?? $request->route('id');
        $board = Board::find($boardId);
        
        if (!$board) {
            return response()->json([
                'error' => [
                    'code' => 'BOARD_NOT_FOUND',
                    'message' => 'Board não encontrado'
                ]
            ], 404);
        }
        
        if (!$board->isOwnedBy($request->user())) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Apenas o dono do board pode realizar esta ação'
                ]
            ], 403);
        }
        
        return $next($request);
    }
}
```

## Middleware com Parâmetros

```php
public function handle(Request $request, Closure $next, $role)
{
    if ($request->user()->role !== $role) {
        return response()->json(['error' => 'Permissão insuficiente'], 403);
    }
    
    return $next($request);
}
```

**Uso:**
```php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:admin');
```

## Aplicação no Projeto Kanban

### Estrutura Sugerida

```php
// Rotas públicas (sem middleware)
Route::get('/boards', [BoardController::class, 'index']);
Route::get('/boards/{id}', [BoardController::class, 'show']);

// Rotas que precisam autenticação
Route::middleware('auth.token')->group(function () {
    
    // Cards - qualquer usuário autenticado
    Route::post('/boards/{id}/cards', [CardController::class, 'store']);
    Route::patch('/cards/{id}', [CardController::class, 'update']);
    
    // Boards - apenas owners
    Route::middleware('ensure.owner')->group(function () {
        Route::patch('/boards/{id}', [BoardController::class, 'update']);
        Route::delete('/boards/{id}', [BoardController::class, 'destroy']);
    });
});
```

## Vantagens do Middleware

### ✅ **Reutilização**
Mesmo código de autenticação em várias rotas.

### ✅ **Separação de Responsabilidades**
Controller foca na lógica de negócio, middleware na segurança.

### ✅ **Flexibilidade**
Pode ser aplicado seletivamente a rotas específicas.

### ✅ **Manutenibilidade**
Mudanças na lógica de auth ficam centralizadas.

## Dicas Importantes

1. **Ordem importa**: Middlewares são executados na ordem definida
2. **Performance**: Evite consultas desnecessárias ao banco
3. **Logs**: Registre tentativas de acesso negado
4. **Cache**: Consider cache para tokens válidos
5. **Testing**: Teste cenários com e sem autenticação

## Exemplo Completo de Uso

```php
// routes/api.php
Route::prefix('api')->group(function () {
    
    // Autenticação
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Rotas públicas
    Route::get('/boards', [BoardController::class, 'index']);
    Route::get('/boards/{id}', [BoardController::class, 'show']);
    
    // Rotas protegidas
    Route::middleware(['auth.token'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Boards - apenas owner
        Route::middleware(['ensure.owner:board'])->group(function () {
            Route::patch('/boards/{id}', [BoardController::class, 'update']);
        });
        
        // Cards - qualquer usuário
        Route::apiResource('cards', CardController::class)->except(['index']);
    });
});
```

O middleware é uma ferramenta essencial para manter a segurança e organização da API!