<?php
/**
 * Página Inicial
 * Quizur - O melhor grupo do 1° química manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

$pdo = getDB();

// Busca categorias com contagem de quizzes
$categories = $pdo->query("
    SELECT c.*, COUNT(q.id) as total_quizzes 
    FROM categories c 
    LEFT JOIN quizzes q ON q.category_id = c.id 
    GROUP BY c.id 
    ORDER BY c.id ASC
")->fetchAll();

// Busca quizzes em destaque (mais jogados ou com boa avaliação)
$featuredQuizzes = $pdo->query("
    SELECT q.*, c.name as category_name, c.color as category_color, u.name as author_name,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as questions_count
    FROM quizzes q
    LEFT JOIN categories c ON c.id = q.category_id
    LEFT JOIN users u ON u.id = q.user_id
    ORDER BY q.plays_count DESC, q.id DESC
    LIMIT 6
")->fetchAll();

// Estatísticas da plataforma
$statsQuizzes = (int)$pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$statsUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$statsAttempts = (int)$pdo->query("SELECT COUNT(*) FROM quiz_attempts")->fetchColumn();

$pageTitle = "Início - O melhor portal de quizzes";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section com Gradientes Rosa, Azul e Roxo -->
<section class="relative overflow-hidden pt-12 pb-20 sm:pt-20 sm:pb-28">
    <!-- Efeitos de iluminação de fundo (Luzes rosa, roxo e azul) -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] sm:w-[650px] h-[350px] sm:h-[650px] bg-gradient-to-tr from-purple-600/30 via-pink-600/25 to-blue-600/20 blur-[130px] rounded-full pointer-events-none -z-10"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Badge da Turma -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-purple-500/10 via-pink-500/10 to-blue-500/10 border border-pink-500/30 text-pink-300 text-xs sm:text-sm font-semibold mb-6 shadow-sm">
            <i class="fa-solid fa-flask-vial text-pink-400"></i>
            <span>1° Química Manhã • Projeto Quizur</span>
        </div>

        <!-- Título Principal -->
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white mb-6 leading-tight">
            Desafie sua mente no <br class="hidden sm:block">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 via-purple-400 to-blue-400">
                Quizur Interativo
            </span>
        </h1>

        <!-- Subtítulo -->
        <p class="max-w-2xl mx-auto text-base sm:text-xl text-slate-300 mb-10 leading-relaxed">
            Crie seus próprios quizzes, teste seus conhecimentos em química e cultura geral, e acompanhe gráficos completos do seu desempenho!
        </p>

        <!-- Barra de Busca do Tema Desejado (Requisito CORE) -->
        <div class="max-w-2xl mx-auto mb-10">
            <form action="quizzes.php" method="GET" class="relative flex flex-col sm:flex-row items-center gap-2 p-2 rounded-2xl bg-[#161b2e]/90 border border-white/15 shadow-2xl backdrop-blur-xl">
                <div class="relative w-full flex items-center">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 text-slate-400 text-lg"></i>
                    <input 
                        type="text" 
                        name="busca" 
                        placeholder="Qual tema você deseja buscar hoje? (ex: Tabela Periódica, Pop, Geografia...)"
                        class="w-full bg-transparent pl-12 pr-4 py-3.5 text-sm sm:text-base text-white placeholder-slate-400 focus:outline-none"
                    >
                </div>
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-7 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-lg shadow-pink-500/25 transition whitespace-nowrap"
                >
                    Buscar Quiz
                </button>
            </form>
        </div>

        <!-- Ações Rápidas -->
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="quizzes.php" class="px-6 py-3 rounded-xl font-semibold text-sm bg-white/10 hover:bg-white/15 border border-white/15 text-white transition flex items-center gap-2">
                <i class="fa-solid fa-compass text-blue-400"></i> Explorar Todos os Quizzes
            </a>
            <a href="quiz_criar.php" class="px-6 py-3 rounded-xl font-semibold text-sm bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white shadow-lg shadow-pink-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> Criar Meu Próprio Quiz
            </a>
        </div>

        <!-- Estatísticas da Comunidade -->
        <div class="grid grid-cols-3 gap-4 max-w-xl mx-auto mt-16 pt-8 border-t border-white/10 text-center">
            <div>
                <p class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400"><?= $statsQuizzes ?></p>
                <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-medium">Quizzes Criados</p>
            </div>
            <div>
                <p class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-blue-400"><?= $statsAttempts ?></p>
                <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-medium">Partidas Jogadas</p>
            </div>
            <div>
                <p class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400"><?= $statsUsers ?></p>
                <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-medium">Alunos Ativos</p>
            </div>
        </div>
    </div>
</section>

<!-- Categorias Temáticas -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-pink-500"></span> Temas & Categorias
            </h2>
            <p class="text-sm text-slate-400 mt-1">Navegue pelas áreas de conhecimento disponíveis</p>
        </div>
        <a href="quizzes.php" class="text-xs sm:text-sm font-semibold text-pink-400 hover:text-pink-300 transition">
            Ver todas &rarr;
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php foreach ($categories as $cat): ?>
            <a href="quizzes.php?categoria=<?= urlencode($cat['slug']) ?>" class="group p-5 rounded-2xl bg-[#161b2e] border border-white/5 hover:border-pink-500/40 hover:-translate-y-1 transition duration-300 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-purple-600/30 to-pink-500/30 border border-white/10 flex items-center justify-center text-pink-400 text-xl mb-3 group-hover:scale-110 group-hover:text-white transition">
                    <i class="fa-solid <?= e($cat['icon'] ?: 'fa-flask') ?>"></i>
                </div>
                <h3 class="font-bold text-sm text-white group-hover:text-pink-300 transition"><?= e($cat['name']) ?></h3>
                <span class="text-xs text-slate-400 mt-1"><?= $cat['total_quizzes'] ?> <?= $cat['total_quizzes'] == 1 ? 'quiz' : 'quizzes' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Quizzes em Destaque -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-purple-500"></span> Quizzes em Destaque
            </h2>
            <p class="text-sm text-slate-400 mt-1">Os desafios mais populares criados pela turma</p>
        </div>
        <a href="quizzes.php" class="text-xs sm:text-sm font-semibold text-blue-400 hover:text-blue-300 transition">
            Explorar catálogo &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($featuredQuizzes as $q): ?>
            <div class="group rounded-2xl bg-[#161b2e] border border-white/10 overflow-hidden hover:border-purple-500/50 hover:shadow-2xl hover:shadow-purple-500/10 transition-all duration-300 flex flex-col">
                <!-- Imagem com Overlay -->
                <div class="relative h-44 overflow-hidden bg-slate-800">
                    <img 
                        src="<?= e($q['image_url'] ?: 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=800') ?>" 
                        alt="<?= e($q['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-[#161b2e] via-transparent to-black/40"></div>
                    
                    <!-- Tag da Categoria -->
                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-[#0d0f1a]/80 backdrop-blur-md text-pink-300 border border-white/10">
                        <?= e($q['category_name'] ?: 'Geral') ?>
                    </span>

                    <!-- Dificuldade -->
                    <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-500/30 backdrop-blur-md text-purple-200 border border-purple-500/30">
                        <?= e($q['difficulty']) ?>
                    </span>
                </div>

                <!-- Conteúdo do Card -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-pink-400 transition-colors line-clamp-2 mb-2">
                            <?= e($q['title']) ?>
                        </h3>
                        <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-4">
                            <?= e($q['description'] ?: 'Teste seus conhecimentos neste quiz interativo!') ?>
                        </p>
                    </div>

                    <div>
                        <!-- Info Meta -->
                        <div class="flex items-center justify-between text-xs text-slate-400 pt-3 border-t border-white/5 mb-4">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-question text-purple-400"></i>
                                <?= $q['questions_count'] ?> <?= $q['questions_count'] == 1 ? 'pergunta' : 'perguntas' ?>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-gamepad text-pink-400"></i>
                                <?= $q['plays_count'] ?> jogadas
                            </span>
                        </div>

                        <!-- Botão Jogar -->
                        <a href="quiz_play.php?id=<?= $q['id'] ?>" class="w-full py-2.5 rounded-xl font-bold text-xs text-center text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-md flex items-center justify-center gap-2 transition">
                            <i class="fa-solid fa-play"></i> Iniciar Desafio
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Banner Inspiracional / Criação -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="relative rounded-3xl overflow-hidden p-8 sm:p-14 bg-gradient-to-r from-purple-900/60 via-pink-900/50 to-blue-900/60 border border-white/15 text-center">
        <div class="max-w-2xl mx-auto">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-pink-300 mb-4">
                Comunidade 1° Química Manhã
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">
                Tem uma ideia de quiz incrível?
            </h2>
            <p class="text-slate-300 text-sm sm:text-base mb-8">
                Crie um quiz com perguntas personalizadas, adicione explicações para cada resposta e veja seus colegas de classe disputarem pelo melhor desempenho nos gráficos!
            </p>
            <a href="quiz_criar.php" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-pink-500 via-purple-600 to-blue-600 shadow-xl shadow-pink-500/25 hover:scale-105 transition duration-300">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Criar Quiz Agora
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
