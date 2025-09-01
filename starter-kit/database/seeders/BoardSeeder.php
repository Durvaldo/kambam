<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Column;
use App\Models\Card;
use App\Models\User;
use App\Models\MoveHistory;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $joao = User::where('email', 'joao@test.com')->first();
        $maria = User::where('email', 'maria@test.com')->first();

        // Criar board demo
        $board = Board::create([
            'title' => 'Projeto Demo',
            'description' => 'Board de demonstração para testes da API',
            'owner_id' => $admin->id,
        ]);

        // Criar as 3 colunas padrão
        $todoColumn = Column::create([
            'board_id' => $board->id,
            'name' => 'To Do',
            'order' => 1,
            'wip_limit' => 999,
        ]);

        $doingColumn = Column::create([
            'board_id' => $board->id,
            'name' => 'Doing',
            'order' => 2,
            'wip_limit' => 3,
        ]);

        $doneColumn = Column::create([
            'board_id' => $board->id,
            'name' => 'Done',
            'order' => 3,
            'wip_limit' => 999,
        ]);

        // Criar cards de exemplo
        $card1 = Card::create([
            'board_id' => $board->id,
            'column_id' => $todoColumn->id,
            'title' => 'Implementar autenticação JWT',
            'description' => 'Configurar sistema de login e logout com tokens JWT',
            'position' => 1,
            'created_by' => $admin->id,
        ]);

        $card2 = Card::create([
            'board_id' => $board->id,
            'column_id' => $todoColumn->id,
            'title' => 'Criar endpoints públicos',
            'description' => 'Implementar rotas GET para visualização dos boards sem autenticação',
            'position' => 2,
            'created_by' => $admin->id,
        ]);

        $card3 = Card::create([
            'board_id' => $board->id,
            'column_id' => $doingColumn->id,
            'title' => 'Validação de WIP Limit',
            'description' => 'Implementar validação para impedir criar cards em colunas que atingiram o limite',
            'position' => 1,
            'created_by' => $joao->id,
        ]);

        $card4 = Card::create([
            'board_id' => $board->id,
            'column_id' => $doneColumn->id,
            'title' => 'Configurar migrations',
            'description' => 'Criar todas as migrations necessárias para o projeto',
            'position' => 1,
            'created_by' => $maria->id,
        ]);

        // Criar histórico inicial
        MoveHistory::logCreated($card1, $admin);
        MoveHistory::logCreated($card2, $admin);
        MoveHistory::logCreated($card3, $joao);
        MoveHistory::logCreated($card4, $maria);

        // Simular uma movimentação
        MoveHistory::create([
            'card_id' => $card4->id,
            'board_id' => $board->id,
            'from_column_id' => $todoColumn->id,
            'to_column_id' => $doneColumn->id,
            'type' => 'moved',
            'by_user_id' => $maria->id,
            'at' => now()->subHours(2),
        ]);

        // Board adicional para testes
        $board2 = Board::create([
            'title' => 'Projeto Pessoal',
            'description' => 'Meu board particular',
            'owner_id' => $joao->id,
        ]);

        // Colunas para o segundo board
        Column::create([
            'board_id' => $board2->id,
            'name' => 'To Do',
            'order' => 1,
            'wip_limit' => 5,
        ]);

        Column::create([
            'board_id' => $board2->id,
            'name' => 'Doing',
            'order' => 2,
            'wip_limit' => 2,
        ]);

        Column::create([
            'board_id' => $board2->id,
            'name' => 'Done',
            'order' => 3,
            'wip_limit' => 999,
        ]);
    }
}