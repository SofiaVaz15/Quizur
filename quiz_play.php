<?php
/**
 * Interface Interativa para Jogar o Quiz (Passo a Passo)
 * Mobile First - Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

$pdo = getDB();
$quizId = (int)($_GET['id'] ?? 0);

// Busca quiz
$stmtQuiz = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmtQuiz->execute([$quizId]);
$quiz = $stmtQuiz->fetch();

if (!$quiz) {
    setToast('error', 'Quiz não localizado.');
    header('Location: quizzes.php');
    exit;
}

// Busca perguntas e suas alternativas
$stmtQ = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY order_num ASC, id ASC");
$stmtQ->execute([$quizId]);
$questions = $stmtQ->fetchAll();

if (empty($questions)) {
    setToast('error', 'Este quiz ainda não possui perguntas ativas.');
    header("Location: quiz_view.php?id={$quizId}");
    exit;
}

// Busca alternativas de cada pergunta
$questionsWithAnswers = [];
$stmtA = $pdo->prepare("SELECT id, question_id, answer_text, order_num FROM answers WHERE question_id = ? ORDER BY order_num ASC, id ASC");

foreach ($questions as $q) {
    $stmtA->execute([$q['id']]);
    $q['answers'] = $stmtA->fetchAll();
    $questionsWithAnswers[] = $q;
}

$currentUser = getCurrentUser();
$pageTitle = "Jogando: " . $quiz['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8 sm:py-12">
    <!-- Header do Quiz em Execução -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <span class="text-xs uppercase font-bold tracking-wider text-pink-400">
                <?= e($quiz['title']) ?>
            </span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-0.5">
                Pergunta <span id="current-step-num">1</span> de <?= count($questionsWithAnswers) ?>
            </h1>
        </div>

        <!-- Cronômetro -->
        <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#161b2e] border border-white/10 text-xs font-semibold text-purple-300">
            <i class="fa-solid fa-stopwatch text-pink-400"></i>
            <span id="timer-display">00:00</span>
        </div>
    </div>

    <!-- Barra de Progresso Superior com Gradiente Rosa, Azul e Roxo -->
    <div class="w-full bg-[#161b2e] h-2.5 rounded-full overflow-hidden mb-8 border border-white/5">
        <div 
            id="quiz-progress-bar" 
            class="h-full bg-gradient-to-r from-purple-500 via-pink-500 to-blue-500 transition-all duration-300"
            style="width: <?= (1 / count($questionsWithAnswers)) * 100 ?>%"
        ></div>
    </div>

    <!-- Formulário do Quiz -->
    <form id="quiz-form" action="actions/quiz_submit.php" method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
        <input type="hidden" name="time_spent" id="time-spent-input" value="0">

        <?php if (!$currentUser): ?>
            <!-- Campo de identificação caso seja visitante -->
            <div id="guest-name-card" class="mb-6 p-4 rounded-2xl bg-[#161b2e] border border-white/10 text-xs">
                <label for="guest_name" class="block font-semibold text-slate-300 mb-1">Seu Nome para o Ranking (Opcional):</label>
                <input 
                    type="text" 
                    name="guest_name" 
                    id="guest_name" 
                    placeholder="Como quer ser chamado nos resultados?" 
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:outline-none focus:border-purple-500"
                >
            </div>
        <?php endif; ?>

        <!-- Lista de Perguntas (Exibidas uma a uma com navegação dinâmica) -->
        <?php foreach ($questionsWithAnswers as $qIndex => $q): ?>
            <div class="question-step <?= $qIndex === 0 ? '' : 'hidden' ?>" data-step="<?= $qIndex + 1 ?>">
                <!-- Card da Pergunta -->
                <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8 shadow-2xl backdrop-blur-xl mb-6">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-3">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                        <span>Questão <?= $qIndex + 1 ?></span>
                    </div>

                    <h2 class="text-lg sm:text-xl font-bold text-white leading-relaxed mb-6">
                        <?= e($q['question_text']) ?>
                    </h2>

                    <?php if (!empty($q['image_url'])): ?>
                        <div class="mb-6 rounded-2xl overflow-hidden max-h-64 bg-slate-900">
                            <img src="<?= e($q['image_url']) ?>" alt="Imagem da questão" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>

                    <!-- Alternativas -->
                    <div class="space-y-3">
                        <?php 
                        $letters = ['A', 'B', 'C', 'D', 'E'];
                        foreach ($q['answers'] as $aIndex => $ans): 
                            $letter = $letters[$aIndex] ?? ($aIndex + 1);
                        ?>
                            <label class="quiz-option flex items-center p-4 rounded-2xl bg-[#0d0f1a] border border-white/10 hover:border-pink-500/50 transition cursor-pointer select-none">
                                <input 
                                    type="radio" 
                                    name="answers[<?= $q['id'] ?>]" 
                                    value="<?= $ans['id'] ?>" 
                                    class="hidden option-radio"
                                    onchange="handleOptionSelect(this)"
                                >
                                <span class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-bold text-xs text-purple-300 mr-3.5 option-badge">
                                    <?= $letter ?>
                                </span>
                                <span class="text-sm font-medium text-slate-200 option-text">
                                    <?= e($ans['answer_text']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Controles de Navegação (Mobile First) -->
        <div class="flex items-center justify-between gap-4 pt-2">
            <button 
                type="button" 
                id="btn-prev" 
                onclick="changeStep(-1)" 
                class="hidden px-5 py-3 rounded-xl font-semibold text-sm bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 transition"
            >
                &larr; Anterior
            </button>

            <div class="flex-1"></div>

            <button 
                type="button" 
                id="btn-next" 
                onclick="changeStep(1)" 
                class="px-7 py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-lg shadow-pink-500/25 transition"
            >
                Próxima Pergunta &rarr;
            </button>

            <button 
                type="submit" 
                id="btn-finish" 
                class="hidden px-7 py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-emerald-500 via-teal-500 to-blue-600 hover:opacity-95 shadow-lg shadow-emerald-500/25 transition flex items-center gap-2"
            >
                <i class="fa-solid fa-flag-checkered"></i> Concluir e Ver Gráficos
            </button>
        </div>
    </form>
</div>

<!-- Lógica Interativa do Quiz em JavaScript -->
<script>
    let currentStep = 1;
    const totalSteps = <?= count($questionsWithAnswers) ?>;
    let secondsElapsed = 0;
    let timerInterval = null;

    // Cronômetro de tempo gasto
    timerInterval = setInterval(() => {
        secondsElapsed++;
        document.getElementById('time-spent-input').value = secondsElapsed;
        
        const mins = String(Math.floor(secondsElapsed / 60)).padStart(2, '0');
        const secs = String(secondsElapsed % 60).padStart(2, '0');
        document.getElementById('timer-display').textContent = `${mins}:${secs}`;
    }, 1000);

    // Seleção de alternativa com destaque visual
    function handleOptionSelect(radio) {
        const parentCard = radio.closest('.question-step');
        parentCard.querySelectorAll('.quiz-option').forEach(opt => {
            opt.classList.remove('selected', 'border-purple-500', 'bg-purple-500/10');
            opt.querySelector('.option-badge').classList.remove('bg-purple-600', 'text-white');
            opt.querySelector('.option-badge').classList.add('bg-white/5', 'text-purple-300');
        });

        const label = radio.closest('.quiz-option');
        label.classList.add('selected', 'border-purple-500', 'bg-purple-500/10');
        const badge = label.querySelector('.option-badge');
        badge.classList.remove('bg-white/5', 'text-purple-300');
        badge.classList.add('bg-purple-600', 'text-white');
    }

    // Mudança de passo / pergunta
    function changeStep(direction) {
        const nextStep = currentStep + direction;

        if (nextStep < 1 || nextStep > totalSteps) return;

        // Esconde passo atual
        const currentEl = document.querySelector(`.question-step[data-step="${currentStep}"]`);
        if (currentEl) currentEl.classList.add('hidden');

        // Exibe novo passo
        currentStep = nextStep;
        const nextEl = document.querySelector(`.question-step[data-step="${currentStep}"]`);
        if (nextEl) nextEl.classList.remove('hidden');

        // Atualiza barra de progresso e textos
        document.getElementById('current-step-num').textContent = currentStep;
        const progressPercent = (currentStep / totalSteps) * 100;
        document.getElementById('quiz-progress-bar').style.width = `${progressPercent}%`;

        // Controle dos botões
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        const btnFinish = document.getElementById('btn-finish');

        if (currentStep > 1) {
            btnPrev.classList.remove('hidden');
        } else {
            btnPrev.classList.add('hidden');
        }

        if (currentStep === totalSteps) {
            btnNext.classList.add('hidden');
            btnFinish.classList.remove('hidden');
        } else {
            btnNext.classList.remove('hidden');
            btnFinish.classList.add('hidden');
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Validação ao enviar
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
        clearInterval(timerInterval);
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
