<?php
/**
 * Explorar e Buscar Quizzes pelo Tema Desejado
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

$pdo = getDB();

$busca = trim($_GET['busca'] ?? '');
$categoriaSlug = trim($_GET['categoria'] ?? '');
$dificuldade = trim($_GET['dificuldade'] ?? '');

// Categorias para a barra de filtros
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Montagem da query com PDO preparado contra SQL Injection
$sql = "
    SELECT q.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.name as author_name,
           (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as questions_count
    FROM quizzes q
    LEFT JOIN categories c ON c.id = q.category_id
    LEFT JOIN users u ON u.id = q.user_id
    WHERE 1=1
";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (q.title LIKE :buscaTitle OR q.description LIKE :buscaDesc OR c.name LIKE :buscaCat)";
    $params[':buscaTitle'] = "%{$busca}%";
    $params[':buscaDesc'] = "%{$busca}%";
    $params[':buscaCat'] = "%{$busca}%";
}

if (!empty($categoriaSlug)) {
    $sql .= " AND c.slug = :categoriaSlug";
    $params[':categoriaSlug'] = $categoriaSlug;
}

if (!empty($dificuldade)) {
    $sql .= " AND q.difficulty = :dificuldade";
    $params[':dificuldade'] = $dificuldade;
}

$sql .= " ORDER BY q.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$quizzes = $stmt->fetchAll();

$pageTitle = "Explorar Quizzes por Tema";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Cabeçalho da Página -->
    <div class="mb-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white">
            Explorar Quizzes por Tema
        </h1>
        <p class="text-slate-400 text-sm mt-1">
            Pesquise pelo tema desejado ou filtre pelas categorias da turma do 1° Química
        </p>
    </div>

    <!-- Barra de Filtros e Busca (Mobile First) -->
    <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-5 sm:p-6 mb-10 shadow-xl backdrop-blur-xl">
        <form action="quizzes.php" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <!-- Campo de Busca do Tema -->
                <div class="md:col-span-6 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input 
                        type="text" 
                        name="busca" 
                        value="<?= e($busca) ?>"
                        placeholder="Buscar por tema ou palavra-chave (ex: Química, Pop...)"
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:border-pink-500 transition"
                    >
                </div>

                <!-- Filtro de Categoria -->
                <div class="md:col-span-3">
                    <select 
                        name="categoria" 
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-purple-500 transition"
                    >
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>" <?= $categoriaSlug === $cat['slug'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro de Dificuldade -->
                <div class="md:col-span-2">
                    <select 
                        name="dificuldade" 
                        class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 transition"
                    >
                        <option value="">Dificuldade</option>
                        <option value="Fácil" <?= $dificuldade === 'Fácil' ? 'selected' : '' ?>>Fácil</option>
                        <option value="Médio" <?= $dificuldade === 'Médio' ? 'selected' : '' ?>>Médio</option>
                        <option value="Difícil" <?= $dificuldade === 'Difícil' ? 'selected' : '' ?>>Difícil</option>
                    </select>
                </div>

                <!-- Botão Filtrar -->
                <div class="md:col-span-1">
                    <button 
                        type="submit" 
                        class="w-full h-full min-h-[46px] rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 flex items-center justify-center shadow-lg transition"
                        title="Buscar"
                    >
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </div>
            </div>

            <!-- Tags Rápidas de Categorias -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-2 scrollbar-none text-xs">
                <span class="text-slate-400 whitespace-nowrap font-medium mr-1">Temas rápidos:</span>
                <a href="quizzes.php" class="px-3 py-1.5 rounded-full whitespace-nowrap transition <?= empty($categoriaSlug) && empty($busca) ? 'bg-pink-500 text-white font-bold' : 'bg-white/5 text-slate-300 hover:bg-white/10' ?>">
                    Todos
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="quizzes.php?categoria=<?= urlencode($cat['slug']) ?>" class="px-3 py-1.5 rounded-full whitespace-nowrap transition <?= $categoriaSlug === $cat['slug'] ? 'bg-purple-600 text-white font-bold' : 'bg-white/5 text-slate-300 hover:bg-white/10' ?>">
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-slate-400">
                Mostrando <strong class="text-white"><?= count($quizzes) ?></strong> <?= count($quizzes) == 1 ? 'quiz encontrado' : 'quizzes encontrados' ?>
                <?= !empty($busca) ? ' para "' . e($busca) . '"' : '' ?>
            </p>
            <?php if (!empty($busca) || !empty($categoriaSlug) || !empty($dificuldade)): ?>
                <a href="quizzes.php" class="text-xs text-pink-400 hover:underline">Limpar filtros</a>
            <?php endif; ?>
        </div>

        <?php if (empty($quizzes)): ?>
            <!-- Estado Vazio -->
            <div class="rounded-3xl bg-[#161b2e] border border-white/10 p-12 text-center max-w-xl mx-auto my-12">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-pink-500/20 to-purple-500/20 flex items-center justify-center text-pink-400 text-3xl mx-auto mb-4">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Nenhum quiz encontrado</h3>
                <p class="text-slate-400 text-sm mb-6 leading-relaxed">
                    Não encontramos quizzes com esse tema no momento. Que tal você mesmo criar um e compartilhar com a turma?
                </p>
                <a href="quiz_criar.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-pink-500 to-purple-600 hover:opacity-95 shadow-lg transition">
                    <i class="fa-solid fa-plus-circle"></i> Criar Quiz deste Tema
                </a>
            </div>
        <?php else: ?>
            <!-- Grid de Quizzes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($quizzes as $q): ?>
                    <div class="group rounded-2xl bg-[#161b2e] border border-white/10 overflow-hidden hover:border-pink-500/40 hover:shadow-2xl hover:shadow-pink-500/10 transition-all duration-300 flex flex-col justify-between">
                        <!-- Imagem -->
                        <div>
                            <div class="relative h-44 overflow-hidden bg-slate-800">
                                <img 
                                    src="<?= e($q['image_url'] ?: 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=800') ?>" 
                                    alt="<?= e($q['title']) ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-[#161b2e] via-transparent to-black/40"></div>

                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold bg-[#0d0f1a]/80 backdrop-blur-md text-pink-300 border border-white/10">
                                    <?= e($q['category_name'] ?: 'Geral') ?>
                                </span>

                                <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-500/30 backdrop-blur-md text-purple-200 border border-purple-500/30">
                                    <?= e($q['difficulty']) ?>
                                </span>
                            </div>

                            <div class="p-5">
                                <h3 class="text-lg font-bold text-white group-hover:text-pink-400 transition-colors line-clamp-2 mb-2">
                                    <?= e($q['title']) ?>
                                </h3>
                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-4">
                                    <?= e($q['description'] ?: 'Desafie seus conhecimentos!') ?>
                                </p>
                            </div>
                        </div>

                        <!-- Rodapé do Card -->
                        <div class="px-5 pb-5">
                            <div class="flex items-center justify-between text-xs text-slate-400 pt-3 border-t border-white/5 mb-4">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-question text-purple-400"></i>
                                    <?= $q['questions_count'] ?> <?= $q['questions_count'] == 1 ? 'pergunta' : 'perguntas' ?>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-pen text-blue-400"></i>
                                    <?= e(explode(' ', $q['author_name'])[0] ?: 'Autor') ?>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="quiz_view.php?id=<?= $q['id'] ?>" class="flex-1 py-2 rounded-xl font-semibold text-xs text-center text-slate-300 bg-white/5 hover:bg-white/10 border border-white/10 transition">
                                    Ver Detalhes
                                </a>
                                <a href="quiz_play.php?id=<?= $q['id'] ?>" class="flex-1 py-2 rounded-xl font-bold text-xs text-center text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-md flex items-center justify-center gap-1.5 transition">
                                    <i class="fa-solid fa-play text-[10px]"></i> Jogar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
