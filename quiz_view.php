<?php
/**
 * Visualização e Detalhes do Quiz
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

$pdo = getDB();
$quizId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT q.*, c.name as category_name, c.color as category_color, u.name as author_name, u.email as author_email,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as questions_count
    FROM quizzes q
    LEFT JOIN categories c ON c.id = q.category_id
    LEFT JOIN users u ON u.id = q.user_id
    WHERE q.id = ?
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    setToast('error', 'Quiz não encontrado no sistema.');
    header('Location: quizzes.php');
    exit;
}

$currentUser = getCurrentUser();
$isOwner = $currentUser && ($currentUser['id'] == $quiz['user_id']);
$canManage = $isOwner || isAdmin();

// Incrementa visualização
$pdo->prepare("UPDATE quizzes SET views_count = views_count + 1 WHERE id = ?")->execute([$quizId]);

// Busca melhores pontuações recentes deste quiz
$stmtLeaderboard = $pdo->prepare("
    SELECT a.*, u.name as user_name
    FROM quiz_attempts a
    LEFT JOIN users u ON u.id = a.user_id
    WHERE a.quiz_id = ?
    ORDER BY a.score DESC, a.time_spent_seconds ASC, a.id DESC
    LIMIT 5
");
$stmtLeaderboard->execute([$quizId]);
$leaderboard = $stmtLeaderboard->fetchAll();

$pageTitle = $quiz['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
        <a href="index.php" class="hover:text-pink-400">Início</a>
        <span>/</span>
        <a href="quizzes.php" class="hover:text-pink-400">Quizzes</a>
        <span>/</span>
        <span class="text-slate-200 truncate max-w-xs"><?= e($quiz['title']) ?></span>
    </div>

    <!-- Card Principal do Quiz -->
    <div class="rounded-3xl bg-[#161b2e] border border-white/10 overflow-hidden shadow-2xl mb-10">
        <!-- Imagem de Capa com Gradiente -->
        <div class="relative h-64 sm:h-80 w-full overflow-hidden bg-slate-800">
            <img 
                src="<?= e($quiz['image_url'] ?: 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=800') ?>" 
                alt="<?= e($quiz['title']) ?>"
                class="w-full h-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-[#161b2e] via-[#161b2e]/60 to-transparent"></div>

            <!-- Tags sobrepostas -->
            <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#0d0f1a]/80 backdrop-blur-md text-pink-300 border border-white/10">
                    <?= e($quiz['category_name'] ?: 'Geral') ?>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/30 backdrop-blur-md text-purple-200 border border-purple-500/30">
                    Nível <?= e($quiz['difficulty']) ?>
                </span>
            </div>

            <?php if ($canManage): ?>
                <div class="absolute top-4 right-4 flex items-center gap-2">
                    <a href="quiz_editar.php?id=<?= $quiz['id'] ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/20 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Editar
                    </a>
                    <form action="actions/quiz_delete.php" method="POST" class="inline">
                        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                        <button 
                            type="button" 
                            data-confirm="Tem certeza que deseja excluir o quiz &quot;<?= e($quiz['title']) ?>&quot;? Esta ação não pode ser desfeita."
                            data-confirm-title="Confirmar Exclusão de Quiz"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-600/80 hover:bg-rose-600 backdrop-blur-md text-white shadow transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-trash"></i> Excluir
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detalhes do Quiz -->
        <div class="p-6 sm:p-10 -mt-12 relative z-10">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white mb-4">
                <?= e($quiz['title']) ?>
            </h1>

            <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8">
                <?= nl2br(e($quiz['description'])) ?>
            </p>

            <!-- Grid de Características -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-[#0d0f1a]/80 border border-white/5 mb-8 text-center">
                <div>
                    <span class="block text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                        <?= $quiz['questions_count'] ?>
                    </span>
                    <span class="text-xs text-slate-400">Perguntas</span>
                </div>
                <div>
                    <span class="block text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-blue-400">
                        <?= (int)$quiz['points_per_question'] * $quiz['questions_count'] ?> pts
                    </span>
                    <span class="text-xs text-slate-400">Pontuação Total</span>
                </div>
                <div>
                    <span class="block text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                        <?= $quiz['plays_count'] ?>
                    </span>
                    <span class="text-xs text-slate-400">Partidas Jogadas</span>
                </div>
                <div>
                    <span class="block text-xl font-bold text-emerald-400">
                        <?= e(explode(' ', $quiz['author_name'])[0]) ?>
                    </span>
                    <span class="text-xs text-slate-400">Criado por</span>
                </div>
            </div>

            <!-- Botão de Iniciar -->
            <div class="flex flex-col sm:flex-row items-center gap-4">
                <a href="quiz_play.php?id=<?= $quiz['id'] ?>" class="w-full sm:flex-1 py-4 rounded-2xl font-extrabold text-base text-center text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-xl shadow-pink-500/25 transition duration-300 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> Iniciar Desafio Agora
                </a>
                <button type="button" onclick="navigator.clipboard.writeText(window.location.href); QuizurToast.show('success', 'Link do quiz copiado para a área de transferência!');" class="w-full sm:w-auto px-6 py-4 rounded-2xl font-bold text-sm bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-share-nodes"></i> Compartilhar Quiz
                </button>
            </div>
        </div>
    </div>

    <!-- Ranking / Desempenho Recente da Turma -->
    <?php if (!empty($leaderboard)): ?>
        <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-white mb-1 flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-400"></i> Melhores Desempenhos no Quiz
            </h2>
            <p class="text-xs text-slate-400 mb-6">Veja quem mais pontuou e concluiu este desafio</p>

            <div class="space-y-3">
                <?php foreach ($leaderboard as $idx => $att): ?>
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#0d0f1a] border border-white/5 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs <?= $idx === 0 ? 'bg-amber-400 text-slate-950' : ($idx === 1 ? 'bg-slate-300 text-slate-950' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-white/10 text-slate-400')) ?>">
                                <?= $idx + 1 ?>
                            </span>
                            <div>
                                <span class="font-bold text-white block">
                                    <?= e($att['user_name'] ?: ($att['guest_name'] ?: 'Visitante')) ?>
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    Acertou <?= $att['correct_answers'] ?>/<?= $att['total_questions'] ?> (<?= round($att['percentage']) ?>%) • <?= $att['time_spent_seconds'] ?>s
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="font-extrabold text-pink-400 text-base">
                                <?= $att['score'] ?> pts
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
