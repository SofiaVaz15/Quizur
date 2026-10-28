<?php
/**
 * Interface para Edição de Quizzes Existentes
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

requireLogin('login.php');

$pdo = getDB();
$quizId = (int)($_GET['id'] ?? 0);
$currentUser = getCurrentUser();

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    setToast('error', 'Quiz não encontrado.');
    header('Location: quizzes.php');
    exit;
}

if ($quiz['user_id'] != $currentUser['id'] && !isAdmin()) {
    setToast('error', 'Você não tem permissão para editar este quiz.');
    header("Location: quiz_view.php?id={$quizId}");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Busca perguntas e alternativas
$stmtQ = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY order_num ASC, id ASC");
$stmtQ->execute([$quizId]);
$questions = $stmtQ->fetchAll();

$stmtA = $pdo->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY order_num ASC, id ASC");
foreach ($questions as &$q) {
    $stmtA->execute([$q['id']]);
    $q['answers'] = $stmtA->fetchAll();
}
unset($q);

$pageTitle = "Editar Quiz: " . $quiz['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-white">Editar Quiz</h1>
        <p class="text-slate-400 text-sm mt-1">Atualize informações, perguntas e gabarito do seu quiz</p>
    </div>

    <form action="actions/quiz_edit.php" method="POST" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">

        <!-- Informações Gerais -->
        <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8 shadow-xl space-y-6">
            <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/10">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> 1. Dados Básicos
            </h2>

            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Título do Quiz</label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    required 
                    value="<?= e($quiz['title']) ?>"
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-pink-500 transition"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Categoria</label>
                    <select name="category_id" id="category_id" required class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-purple-500">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $quiz['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="difficulty" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Dificuldade</label>
                    <select name="difficulty" id="difficulty" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="Fácil" <?= $quiz['difficulty'] === 'Fácil' ? 'selected' : '' ?>>Fácil</option>
                        <option value="Médio" <?= $quiz['difficulty'] === 'Médio' ? 'selected' : '' ?>>Médio</option>
                        <option value="Difícil" <?= $quiz['difficulty'] === 'Difícil' ? 'selected' : '' ?>>Difícil</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Descrição</label>
                <textarea name="description" id="description" rows="3" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-pink-500"><?= e($quiz['description']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="image_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">URL da Imagem</label>
                    <input type="url" name="image_url" id="image_url" value="<?= e($quiz['image_url']) ?>" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-purple-500">
                </div>
                <div>
                    <label for="points_per_question" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Pontos / Questão</label>
                    <input type="number" name="points_per_question" id="points_per_question" value="<?= (int)$quiz['points_per_question'] ?>" min="1" max="100" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Perguntas -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> 2. Perguntas
                </h2>
                <button type="button" onclick="addQuestion()" class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-95 shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Adicionar Pergunta
                </button>
            </div>

            <div id="questions-container" class="space-y-6">
                <!-- Populado por JS -->
            </div>
        </div>

        <div class="pt-6 border-t border-white/10 flex items-center justify-end gap-3">
            <a href="quiz_view.php?id=<?= $quiz['id'] ?>" class="px-6 py-3.5 rounded-xl text-sm font-semibold text-slate-300 bg-white/5 hover:bg-white/10 border border-white/10">Cancelar</a>
            <button type="submit" class="px-8 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-xl shadow-pink-500/25">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
    let questionCount = 0;
    const initialQuestions = <?= json_encode($questions) ?>;

    function addQuestion(qData = null) {
        questionCount++;
        const qIndex = questionCount;
        const container = document.getElementById('questions-container');

        const card = document.createElement('div');
        card.className = 'rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8 shadow-xl question-block transition';
        card.id = `question-card-${qIndex}`;

        const qText = qData ? qData.question_text : '';
        const qExpl = qData ? (qData.explanation || '') : '';
        const answers = qData ? qData.answers : [];

        card.innerHTML = `
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/10">
                <span class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400">
                    Pergunta #${qIndex}
                </span>
                <button type="button" onclick="removeQuestion(${qIndex})" class="text-xs text-rose-400 hover:text-rose-300 p-1 flex items-center gap-1 transition">
                    <i class="fa-solid fa-trash-can"></i> Remover
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Enunciado *</label>
                <input type="text" name="questions[${qIndex}][text]" required value="${escapeHtml(qText)}" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500">
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Explicação Didática</label>
                <input type="text" name="questions[${qIndex}][explanation]" value="${escapeHtml(qExpl)}" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-pink-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Alternativas (Marque a CORRETA)</label>
                <div class="space-y-2.5">
                    ${[0, 1, 2, 3].map(aIdx => {
                        const aObj = answers[aIdx] || null;
                        const aVal = aObj ? aObj.answer_text : '';
                        const isCorrect = aObj ? (aObj.is_correct == 1) : (aIdx === 0);
                        return `
                            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-[#0d0f1a] border border-white/5">
                                <input type="radio" name="questions[${qIndex}][correct]" value="${aIdx}" ${isCorrect ? 'checked' : ''} class="w-4 h-4 accent-pink-500 cursor-pointer">
                                <span class="text-xs font-bold text-slate-400 w-4">${String.fromCharCode(65 + aIdx)}</span>
                                <input type="text" name="questions[${qIndex}][answers][${aIdx}]" required value="${escapeHtml(aVal)}" placeholder="Alternativa ${String.fromCharCode(65 + aIdx)}" class="flex-1 bg-transparent text-sm text-white focus:outline-none">
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;

        container.appendChild(card);
    }

    function removeQuestion(index) {
        const card = document.getElementById(`question-card-${index}`);
        if (card) {
            if (document.querySelectorAll('.question-block').length <= 1) {
                QuizurModal.alert('Atenção', 'O quiz deve conter no mínimo 1 pergunta!');
                return;
            }
            card.remove();
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (initialQuestions && initialQuestions.length > 0) {
            initialQuestions.forEach(q => addQuestion(q));
        } else {
            addQuestion();
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
