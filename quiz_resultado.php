<?php
/**
 * Sessão de Resultados com Gráfico de Desempenho
 * Atende aos requisitos:
 * - "Ao final de cada quiz terá uma sessão com um gráfico que mostrará o desempenho do usuário."
 * - "cada quiz vai ter sua pontuação baseado na quantidade de perguntas."
 * - "As cores do site serão majoritariamente rosa, azul e roxo."
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

$pdo = getDB();
$attemptId = (int)($_GET['attempt_id'] ?? 0);

// Busca a tentativa com dados do quiz
$stmt = $pdo->prepare("
    SELECT a.*, q.title as quiz_title, q.difficulty, q.points_per_question,
           c.name as category_name, u.name as user_name
    FROM quiz_attempts a
    JOIN quizzes q ON q.id = a.quiz_id
    LEFT JOIN categories c ON c.id = q.category_id
    LEFT JOIN users u ON u.id = a.user_id
    WHERE a.id = ?
");
$stmt->execute([$attemptId]);
$attempt = $stmt->fetch();

if (!$attempt) {
    setToast('error', 'Resultado não encontrado.');
    header('Location: quizzes.php');
    exit;
}

// Busca o detalhamento de cada questão e a resposta dada
$stmtDetails = $pdo->prepare("
    SELECT d.*, q.question_text, q.explanation,
           ansSelected.answer_text as selected_text,
           ansCorrect.answer_text as correct_text
    FROM quiz_attempt_details d
    JOIN questions q ON q.id = d.question_id
    LEFT JOIN answers ansSelected ON ansSelected.id = d.selected_answer_id
    LEFT JOIN answers ansCorrect ON ansCorrect.question_id = q.id AND ansCorrect.is_correct = 1
    WHERE d.attempt_id = ?
    ORDER BY q.order_num ASC, q.id ASC
");
$stmtDetails->execute([$attemptId]);
$details = $stmtDetails->fetchAll();

$correctCount = (int)$attempt['correct_answers'];
$wrongCount = (int)$attempt['wrong_answers'];
$totalQuestions = (int)$attempt['total_questions'];
$percentage = (float)$attempt['percentage'];
$score = (int)$attempt['score'];
$maxScore = (int)$attempt['max_score'];

// Mensagem de Feedback
if ($percentage >= 90) {
    $feedbackTitle = "Brilhante! Desempenho Perfeito!";
    $feedbackClass = "text-emerald-400";
    $feedbackIcon = "fa-trophy";
} elseif ($percentage >= 70) {
    $feedbackTitle = "Muito Bom! Conhecimento em Alta!";
    $feedbackClass = "text-pink-400";
    $feedbackIcon = "fa-medal";
} elseif ($percentage >= 50) {
    $feedbackTitle = "Bom Trabalho! Na Média!";
    $feedbackClass = "text-blue-400";
    $feedbackIcon = "fa-star";
} else {
    $feedbackTitle = "Continue Praticando! Não Desista!";
    $feedbackClass = "text-purple-400";
    $feedbackIcon = "fa-book-open-reader";
}

$pageTitle = "Desempenho: " . $attempt['quiz_title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header do Resultado -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs font-semibold mb-4">
            <i class="fa-solid fa-graduation-cap text-pink-400"></i>
            Relatório de Desempenho • 1° Química Manhã
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2">
            Resultado do Quiz
        </h1>
        <p class="text-slate-400 text-sm sm:text-base">
            <?= e($attempt['quiz_title']) ?>
        </p>
    </div>

    <!-- Card de Destaque da Pontuação (Baseado no número de perguntas) -->
    <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-10 shadow-2xl backdrop-blur-xl mb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            
            <!-- Resumo das Métricas -->
            <div class="space-y-6 text-center md:text-left">
                <div>
                    <div class="inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider <?= $feedbackClass ?> mb-1">
                        <i class="fa-solid <?= $feedbackIcon ?>"></i>
                        <?= $feedbackTitle ?>
                    </div>
                    <div class="text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-500 via-purple-400 to-blue-400">
                        <?= $score ?> <span class="text-2xl text-slate-400">/ <?= $maxScore ?> pts</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Pontuação calculada com base na quantidade de perguntas (<?= $attempt['points_per_question'] ?> pts por questão).
                    </p>
                </div>

                <!-- Estatísticas Rápidas -->
                <div class="grid grid-cols-3 gap-3 pt-4 border-t border-white/10 text-center">
                    <div class="p-3 rounded-2xl bg-[#0d0f1a] border border-emerald-500/20">
                        <span class="block text-xl font-bold text-emerald-400"><?= $correctCount ?></span>
                        <span class="text-[11px] text-slate-400 uppercase font-medium">Acertos</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0d0f1a] border border-pink-500/20">
                        <span class="block text-xl font-bold text-pink-400"><?= $wrongCount ?></span>
                        <span class="text-[11px] text-slate-400 uppercase font-medium">Erros</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-[#0d0f1a] border border-purple-500/20">
                        <span class="block text-xl font-bold text-purple-400"><?= round($percentage) ?>%</span>
                        <span class="text-[11px] text-slate-400 uppercase font-medium">Precisão</span>
                    </div>
                </div>

                <div class="flex items-center justify-center md:justify-start gap-4 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-clock text-blue-400"></i>
                        Tempo total: <strong class="text-white"><?= $attempt['time_spent_seconds'] ?> segundos</strong>
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-purple-400"></i>
                        Nível <?= e($attempt['difficulty']) ?>
                    </span>
                </div>
            </div>

            <!-- Gráfico de Desempenho (Chart.js com Cores Rosa, Azul e Roxo) -->
            <div class="flex flex-col items-center justify-center p-4 rounded-2xl bg-[#0d0f1a] border border-white/5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-pink-400"></i> Gráfico de Desempenho
                </h3>
                <div class="relative w-48 h-48 sm:w-56 sm:h-56">
                    <canvas id="quizPerformanceChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-white"><?= round($percentage) ?>%</span>
                        <span class="text-[10px] uppercase font-semibold text-slate-400">Aproveitamento</span>
                    </div>
                </div>
                <div class="flex items-center gap-6 mt-4 text-xs">
                    <span class="flex items-center gap-2 text-emerald-400 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Acertos (<?= $correctCount ?>)
                    </span>
                    <span class="flex items-center gap-2 text-pink-400 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-pink-500"></span> Erros (<?= $wrongCount ?>)
                    </span>
                </div>
            </div>

        </div>

        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row items-center gap-3 mt-8 pt-8 border-t border-white/10">
            <a href="quiz_play.php?id=<?= $attempt['quiz_id'] ?>" class="w-full sm:flex-1 py-3.5 rounded-xl font-bold text-sm text-center text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-lg shadow-purple-500/25 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-rotate-right"></i> Jogar Novamente
            </a>
            <a href="quizzes.php" class="w-full sm:flex-1 py-3.5 rounded-xl font-semibold text-sm text-center text-slate-300 bg-white/5 hover:bg-white/10 border border-white/10 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-compass"></i> Explorar Mais Quizzes
            </a>
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); QuizurToast.show('success', 'Link do seu desempenho copiado!');" class="w-full sm:w-auto px-5 py-3.5 rounded-xl font-semibold text-sm text-slate-300 bg-white/5 hover:bg-white/10 border border-white/10 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-share-nodes"></i>
            </button>
        </div>
    </div>

    <!-- Gabarito e Revisão Detalhada das Respostas -->
    <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-white mb-2 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-400"></i> Revisão das Questões
        </h2>
        <p class="text-xs text-slate-400 mb-6">Confira as alternativas assinaladas e as explicações didáticas</p>

        <div class="space-y-4">
            <?php foreach ($details as $idx => $d): 
                $isCorrect = (bool)$d['is_correct'];
            ?>
                <div class="p-5 rounded-2xl bg-[#0d0f1a] border <?= $isCorrect ? 'border-emerald-500/30' : 'border-pink-500/30' ?> transition">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            Pergunta <?= $idx + 1 ?>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold <?= $isCorrect ? 'bg-emerald-500/20 text-emerald-400' : 'bg-pink-500/20 text-pink-400' ?>">
                            <i class="fa-solid <?= $isCorrect ? 'fa-check' : 'fa-xmark' ?>"></i>
                            <?= $isCorrect ? 'Acertou' : 'Errou' ?>
                        </span>
                    </div>

                    <h4 class="text-sm sm:text-base font-bold text-white mb-4">
                        <?= e($d['question_text']) ?>
                    </h4>

                    <!-- Comparação de Respostas -->
                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="p-3 rounded-xl <?= $isCorrect ? 'bg-emerald-950/40 border border-emerald-500/30 text-emerald-300' : 'bg-pink-950/40 border border-pink-500/30 text-pink-300' ?>">
                            <strong class="block text-[11px] uppercase font-bold text-slate-400 mb-0.5">Sua Resposta:</strong>
                            <?= e($d['selected_text'] ?: 'Não respondeu') ?>
                        </div>

                        <?php if (!$isCorrect): ?>
                            <div class="p-3 rounded-xl bg-purple-950/40 border border-purple-500/30 text-purple-300">
                                <strong class="block text-[11px] uppercase font-bold text-slate-400 mb-0.5">Resposta Correta:</strong>
                                <?= e($d['correct_text']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($d['explanation'])): ?>
                            <div class="p-3 rounded-xl bg-white/5 border border-white/5 text-slate-300 text-xs mt-2">
                                <strong class="block text-pink-400 mb-1"><i class="fa-solid fa-lightbulb"></i> Explicação Didática:</strong>
                                <?= e($d['explanation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Inicialização do Gráfico com Chart.js usando as cores Rosa, Azul e Roxo -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('quizPerformanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Acertos', 'Erros'],
                datasets: [{
                    data: [<?= $correctCount ?>, <?= $wrongCount ?>],
                    backgroundColor: [
                        '#10b981', // Verde Esmeralda para Acertos
                        '#ec4899'  // Rosa vibrante para Erros
                    ],
                    borderColor: '#161b2e',
                    borderWidth: 4,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#161b2e',
                        titleColor: '#ffffff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true
                    }
                }
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
