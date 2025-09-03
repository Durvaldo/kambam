# Projeto Kanban API

Sistema de quadros Kanban com autenticação token. Boards são públicos para leitura, mas modificações requerem autenticação.

## Configuração do Projeto

### 1. Criar projeto Laravel
```bash
composer create-project laravel/laravel kanban-backend "9.*"
cd kanban-backend
```

### 2. Copiar arquivos do starter kit
Copie as seguintes pastas do starter kit para seu projeto:

- `database/migrations/` → `database/migrations/`
- `database/seeders/` → `database/seeders/`  
- `app/Models/` → `app/Models/`
- `.env` → sobrescrever o existente

### 4. Configurar banco e executar migrations
```bash
php artisan migrate
php artisan db:seed
```

### 5. Servir aplicação
```bash
php artisan serve
```

## Instruções Importantes

- **Migrations e Seeds**: Já estão prontos e configurados
- **Implementação**: Todos os endpoints devem ser criados no arquivo `routes/api.php`
- **Banco**: Use as tabelas já criadas pelas migrations

## Regras de Negócio

1. **Boards públicos**: Qualquer pessoa pode visualizar (GET) sem login
2. **Modificações privadas**: Criar/editar/deletar requer token
3. **WIP Limit**: Cada coluna tem limite de cards; bloquear se exceder
4. **Colunas padrão**: Todo board nasce com "To Do", "Doing", "Done" (WIP: 999)

## Entidades

- **User**: id, name, email, password_hash
- **Board**: id, title, description, owner_id  
- **Column**: id, board_id, name, order, wip_limit
- **Card**: id, board_id, column_id, title, description, position, created_by

## Autenticação (Token Customizado)

Sistema de tokens. Tokens são strings aleatórias armazenadas no banco de dados.

**POST** `/login` - Login com email/senha → retorna access_token e refresh_token
**POST** `/refresh` - Renovar access_token usando refresh_token
**POST** `/logout` - Invalidar tokens atuais

**Header necessário**: `Authorization: Bearer {access_token}`

**Tempos de expiração**:
- Access token: 1 hora
- Refresh token: 14 dias

## Endpoints Públicos (sem token)

**GET** `/boards` - Lista todos os boards com resumo
**GET** `/boards/{id}` - Detalhe completo do board (Trazendo nome do board, colunas e card em cada coluna)
**GET** `/cards/{id}` - Detalhe do card

## Endpoints Privados (com token)

### Boards (apenas owner)
**POST** `/boards` - Criar board
**PATCH** `/boards/{id}` - Editar título/descrição
**DELETE** `/boards/{id}` - Deletar board

### Colunas (apenas owner)
**POST** `/boards/{id}/columns` - Adicionar coluna
**PATCH** `/columns/{id}` - Editar coluna/WIP
**DELETE** `/columns/{id}` - Deletar coluna

### Cards (qualquer usuário logado)
**POST** `/boards/{id}/cards` - Criar card
**PATCH** `/cards/{id}` - Editar ou mover card
**DELETE** `/cards/{id}` - Deletar card

## Códigos de Erro

- **400**: Dados inválidos
- **401**: Token ausente/expirado  
- **403**: Ação restrita ao owner
- **404**: Recurso não encontrado
- **422**: Validação (incluindo WIP_LIMIT_REACHED)

## Validações

- **Board**: título obrigatório (1-80 chars)
- **Column**: nome obrigatório (1-40 chars), wip_limit ≥ 0
- **Card**: título obrigatório (1-120 chars)
- **WIP**: Verificar limite antes de criar/mover cards

## Checklist de Implementação

1. Configurar sistema de autenticação com tokens customizados
2. Implementar middleware de autorização (Bearer token)
3. Criar endpoints de autenticação (login/refresh/logout)
4. Criar endpoints públicos (GETs)
5. Implementar CRUD de boards (owner only)
6. Implementar CRUD de colunas (owner only)  
7. Implementar CRUD de cards (usuários logados)
8. Adicionar validação de WIP limit
9. Implementar sistema de histórico automático
10. Padronizar respostas de erro JSON
11. Testar todos os endpoints com Postman/Insomnia
