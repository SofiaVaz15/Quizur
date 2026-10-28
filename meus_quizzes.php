<?php
/**
 * Painel Pessoal do Usuário: Meus Quizzes e Meu Histórico de Partidas
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

requireLogin('login.php');

$currentUser = getCurrentUser();
$pdo = getDB();

// 1. Quizzes criados pelo usuário
$stmtMyQuizzes = $pdo->prepare("
    SELECT q.*, c.name as category_name,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as questions_count
    FROM quizzes q
    LEFT JOIN categories c ON c.id = q.category_id
    WHERE q.user_id = ?
    ORDER BY q.id DESC
");
$stmtMyQuizzes->execute([$currentUser['id']]);
$myQuizzes = $stmtMyQuizzes->fetchAll();

// 2. Partidas jogadas pelo usuário
$stmtMyAttempts = $pdo->prepare("
    SELECT a.*, q.title as quiz_title, c.name as category_name
    FROM quiz_attempts a
    JOIN quizzes q ON q.id = a.quiz_id
    LEFT JOIN categories c ON c.id = q.category_id
    WHERE a.user_id = ?
    ORDER BY a.id DESC
");
$stmtMyAttempts->execute([$currentUser['id']]);
$myAttempts = $stmtMyAttempts->fetchAll();

$pageTitle = "Meus Quizzes e Desempenho";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header do Painel -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs font-semibold mb-2">
                <i class="fa-solid fa-user-graduate text-pink-400"></i> Painel do Aluno
            </div>
            <h1 class="text-3xl font-extrabold text-white">Olá, <?= e($currentUser['name']) ?>!</h1>
            <p class="text-slate-400 text-sm mt-0.5">Gerencie os quizzes que você criou e veja seu histórico de aprendizado</p>
        </div>

        <a href="quiz_criar.php" class="px-5 py-3 rounded-xl font-bold text-xs sm:text-sm text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-lg shadow-purple-500/25 transition flex items-center gap-2">
            <i class="fa-solid fa-plus-circle"></i> Criar Novo Quiz
        </a>
    </div>

    <!-- Abas / Tabs -->
    <div class="space-y-10">
        <!-- Seção 1: Quizzes Criados por Mim -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> Quizzes Criados por Você (<?= count($myQuizzes) ?>)
                </h2>
            </div>

            <?php if (empty($myQuizzes)): ?>
                <div class="rounded-2xl bg-[#161b2e] border border-white/5 p-8 text-center">
                    <p class="text-slate-400 text-sm mb-4">Você ainda não criou nenhum quiz.</p>
                    <a href="quiz_criar.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-pink-500 to-purple-600 shadow transition">
                        <i class="fa-solid fa-plus"></i> Criar Meu Primeiro Quiz
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($myQuizzes as $q): ?>
                        <div class="rounded-2xl bg-[#161b2e] border border-white/10 p-5 flex flex-col justify-between hover:border-purple-500/40 transition">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-500/10 text-pink-300 border border-pink-500/20">
                                        <?= e($q['category_name'] ?: 'Geral') ?>
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        <i class="fa-solid fa-gamepad text-purple-400"></i> <?= $q['plays_count'] ?> jogadas
                                    </span>
                                </div>
                                <h3 class="font-bold text-white text-base mb-1 line-clamp-2"><?= e($q['title']) ?></h3>
                                <p class="text-xs text-slate-400 line-clamp-2 mb-4"><?= e($q['description']) ?></p>
                            </div>

                            <div class="pt-4 border-t border-white/5 flex items-center justify-between gap-2">
                                <a href="quiz_view.php?id=<?= $q['id'] ?>" class="text-xs font-semibold text-blue-400 hover:underline">
                                    Ver
                                </a>
                                <div class="flex items-center gap-2">
                                    <a href="quiz_editar.php?id=<?= $q['id'] ?>" class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 transition" title="Editar">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <form action="actions/quiz_delete.php" method="POST" class="inline">
                                        <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">
                                        <button 
                                            type="button" 
                                            data-confirm="Tem certeza que deseja excluir o quiz &quot;<?= e($q['title']) ?>&quot;?"
                                            data-confirm-title="Confirmar Exclusão"
                                            class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition" 
                                            title="Excluir"
                                        >
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Seção 2: Meu Histórico de Partidas Realizadas -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Meu Histórico de Desempenho (<?= count($myAttempts) ?>)
                </h2>
            </div>

            <?php if (empty($myAttempts)): ?>
                <div class="rounded-2xl bg-[#161b2e] border border-white/5 p-8 text-center">
                    <p class="text-slate-400 text-sm mb-4">Você ainda não completou nenhum quiz.</p>
                    <a href="quizzes.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-blue-600 to-purple-600 shadow transition">
                        <i class="fa-solid fa-compass"></i> Escolher um Quiz para Jogar
                    </a>
                </div>
            <?php else: ?>
                <div class="rounded-2xl bg-[#161b2e] border border-white/10 overflow-hidden shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-[#0d0f1a] text-slate-400 uppercase text-[11px] font-semibold border-b border-white/10">
                                <tr>
                                    <th class="px-5 py-3.5">Quiz</th>
                                    <th class="px-5 py-3.5">Acertos / Total</th>
                                    <th class="px-5 py-3.5">Pontuação</th>
                                    <th class="px-5 py-3.5">Precisão</th>
                                    <th class="px-5 py-3.5">Data</th>
                                    <th class="px-5 py-3.5 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($myAttempts as $att): ?>
                                    <tr class="hover:bg-white/5 transition">
                                        <td class="px-5 py-4 font-semibold text-white">
                                            <?= e($att['quiz_title']) ?>
                                            <span class="block text-[11px] font-normal text-slate-400"><?= e($att['category_name']) ?></span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-300">
                                            <?= $att['correct_answers'] ?> de <?= $att['total_questions'] ?>
                                        </td>
                                        <td class="px-5 py-4 font-bold text-pink-400">
                                            <?= $att['score'] ?> pts
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold <?= $att['percentage'] >= 70 ? 'bg-emerald-500/20 text-emerald-400' : ($att['percentage'] >= 50 ? 'bg-blue-500/20 text-blue-400' : 'bg-pink-500/20 text-pink-400') ?>">
                                                <?= round($att['percentage']) ?>%
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-slate-400 text-xs">
                                            <?= date('d/m/Y H:i', strtotime($att['created_at'])) ?>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <a href="quiz_resultado.php?attempt_id=<?= $att['id'] ?>" class="px-3 py-1.5 rounded-lg font-semibold text-xs text-white bg-purple-600/30 hover:bg-purple-600/50 border border-purple-500/30 transition">
                                                Ver Gráfico
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
