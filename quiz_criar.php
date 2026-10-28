<?php
/**
 * Interface para Criação de Novos Quizzes
 * Mobile First - Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

requireLogin('login.php');

$pdo = getDB();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = "Criar Novo Quiz";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Cabeçalho -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/10 border border-pink-500/20 text-pink-300 text-xs font-semibold mb-3">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            Criador de Quizzes • 1° Química Manhã
        </div>
        <h1 class="text-3xl font-extrabold text-white">Criar Novo Quiz</h1>
        <p class="text-slate-400 text-sm mt-1">Crie perguntas desafiadoras para testar o conhecimento dos seus colegas!</p>
    </div>

    <!-- Formulário Principal -->
    <form action="actions/quiz_create.php" method="POST" id="create-quiz-form" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <!-- Bloco 1: Informações Gerais do Quiz -->
        <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8 shadow-xl backdrop-blur-xl space-y-6">
            <h2 class="text-lg font-bold text-white flex items-center gap-2 pb-3 border-b border-white/10">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> 1. Dados Básicos do Quiz
            </h2>

            <!-- Título do Quiz -->
            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Título do Quiz *</label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    required 
                    placeholder="Ex: Tabela Periódica: Você domina a química?"
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition"
                >
            </div>

            <!-- Categoria e Dificuldade em Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Categoria -->
                <div class="sm:col-span-2">
                    <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Categoria / Tema *</label>
                    <select 
                        name="category_id" 
                        id="category_id" 
                        required
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-purple-500 transition"
                    >
                        <option value="">Selecione a categoria</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dificuldade -->
                <div>
                    <label for="difficulty" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Dificuldade</label>
                    <select 
                        name="difficulty" 
                        id="difficulty" 
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 transition"
                    >
                        <option value="Fácil">Fácil</option>
                        <option value="Médio" selected>Médio</option>
                        <option value="Difícil">Difícil</option>
                    </select>
                </div>
            </div>

            <!-- Descrição -->
            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Descrição / Instruções</label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="3"
                    placeholder="Explique sobre o que é o quiz e dê dicas para a turma..."
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition"
                ></textarea>
            </div>

            <!-- Imagem e Pontos por Pergunta -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="image_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">URL da Imagem de Capa (Opcional)</label>
                    <input 
                        type="url" 
                        name="image_url" 
                        id="image_url" 
                        placeholder="https://exemplo.com/imagem.jpg"
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition"
                    >
                </div>
                <div>
                    <label for="points_per_question" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Pontos / Pergunta</label>
                    <input 
                        type="number" 
                        name="points_per_question" 
                        id="points_per_question" 
                        value="10" 
                        min="1" 
                        max="100"
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 transition"
                    >
                </div>
            </div>
        </div>

        <!-- Bloco 2: Construtor Dinâmico de Perguntas e Alternativas -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> 2. Perguntas do Quiz
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Adicione perguntas e marque a bolinha da alternativa correta</p>
                </div>

                <button 
                    type="button" 
                    id="add-question-btn" 
                    onclick="addQuestion()"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-95 shadow-md flex items-center gap-2 transition"
                >
                    <i class="fa-solid fa-plus"></i> Adicionar Pergunta
                </button>
            </div>

            <!-- Container das Perguntas Dinâmicas -->
            <div id="questions-container" class="space-y-6">
                <!-- Perguntas serão inseridas via JavaScript -->
            </div>
        </div>

        <!-- Botão Salvar / Publicar -->
        <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-end gap-3">
            <a href="quizzes.php" class="w-full sm:w-auto px-6 py-3.5 rounded-xl text-sm font-semibold text-slate-300 bg-white/5 hover:bg-white/10 border border-white/10 transition text-center">
                Cancelar
            </a>
            <button 
                type="submit" 
                class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-xl shadow-pink-500/25 transition flex items-center justify-center gap-2"
            >
                <i class="fa-solid fa-cloud-arrow-up"></i> Publicar Quiz Agora
            </button>
        </div>
    </form>
</div>

<!-- Template e Script do Construtor de Perguntas -->
<script>
    let questionCount = 0;

    function addQuestion(initialData = null) {
        questionCount++;
        const qIndex = questionCount;
        const container = document.getElementById('questions-container');

        const card = document.createElement('div');
        card.className = 'rounded-3xl bg-[#161b2e] border border-white/10 p-6 sm:p-8 shadow-xl question-block transition';
        card.id = `question-card-${qIndex}`;

        card.innerHTML = `
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/10">
                <span class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400">
                    Pergunta #${qIndex}
                </span>
                <button type="button" onclick="removeQuestion(${qIndex})" class="text-xs text-rose-400 hover:text-rose-300 p-1 flex items-center gap-1 transition">
                    <i class="fa-solid fa-trash-can"></i> Remover
                </button>
            </div>

            <!-- Enunciado da Pergunta -->
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Enunciado da Pergunta *</label>
                <input 
                    type="text" 
                    name="questions[${qIndex}][text]" 
                    required 
                    placeholder="Digite a pergunta aqui..."
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition"
                >
            </div>

            <!-- Explicação Didática -->
            <div class="mb-6">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Explicação Didática (Aparece no resultado final)</label>
                <input 
                    type="text" 
                    name="questions[${qIndex}][explanation]" 
                    placeholder="Ex: De acordo com a tabela periódica..."
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 transition"
                >
            </div>

            <!-- Alternativas -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Alternativas (Selecione a bolinha da resposta CORRETA)
                </label>
                <div class="space-y-2.5">
                    ${[0, 1, 2, 3].map(aIdx => `
                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-[#0d0f1a] border border-white/5 hover:border-purple-500/30 transition">
                            <input 
                                type="radio" 
                                name="questions[${qIndex}][correct]" 
                                value="${aIdx}" 
                                ${aIdx === 0 ? 'checked' : ''}
                                class="w-4 h-4 accent-pink-500 cursor-pointer"
                                title="Marcar como correta"
                            >
                            <span class="text-xs font-bold text-slate-400 w-4">${String.fromCharCode(65 + aIdx)}</span>
                            <input 
                                type="text" 
                                name="questions[${qIndex}][answers][${aIdx}]" 
                                required
                                placeholder="Alternativa ${String.fromCharCode(65 + aIdx)}"
                                class="flex-1 bg-transparent text-sm text-white placeholder-slate-500 focus:outline-none"
                            >
                        </div>
                    `).join('')}
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

    // Inicializa com 3 perguntas por padrão
    document.addEventListener('DOMContentLoaded', () => {
        addQuestion();
        addQuestion();
        addQuestion();
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
