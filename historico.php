<?php
/**
 * Página de Histórico e Auditoria Geral do Sistema
 * Regra CORE dos Requisitos:
 * "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer
 *  alteração feita por qualquer usuário, para auditorias futuras. onde apenas o adm do site vê essas informações."
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

// Verificação de Segurança Estrita: Apenas Administrador pode acessar
requireAdmin('index.php');

$pdo = getDB();

// Filtros
$filterAction = trim($_GET['action'] ?? '');
$filterSearch = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM audit_logs WHERE 1=1";
$params = [];

if (!empty($filterAction)) {
    $sql .= " AND action = :action";
    $params[':action'] = $filterAction;
}

if (!empty($filterSearch)) {
    $sql .= " AND (user_name LIKE :search OR details LIKE :search OR ip_address LIKE :search)";
    $params[':search'] = "%{$filterSearch}%";
}

$sql .= " ORDER BY id DESC LIMIT 150";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Métricas de Auditoria
$totalLogs = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$totalLogins = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'LOGIN'")->fetchColumn();
$totalQuizzesCriados = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'CRIAR_QUIZ'")->fetchColumn();
$totalPartidasJogadas = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'JOGAR_QUIZ'")->fetchColumn();

// Ações distintas para o filtro
$actionsList = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Auditoria e Histórico de Logs (ADM)";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Cabeçalho Administrativo -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/10 border border-pink-500/30 text-pink-300 text-xs font-semibold mb-2">
                <i class="fa-solid fa-shield-halved text-pink-400"></i> Painel Restrito de Auditoria (Admin)
            </div>
            <h1 class="text-3xl font-extrabold text-white">Histórico de Alterações & Logs</h1>
            <p class="text-slate-400 text-sm mt-0.5">
                Rastreabilidade completa de todas as atividades e operações realizadas por usuários no sistema
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Auditoria Ativa
            </span>
        </div>
    </div>

    <!-- Cards de Métricas de Auditoria -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-[#161b2e] border border-white/10">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total de Logs</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">
                <?= $totalLogs ?>
            </span>
        </div>
        <div class="p-5 rounded-2xl bg-[#161b2e] border border-white/10">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Acessos / Logins</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">
                <?= $totalLogins ?>
            </span>
        </div>
        <div class="p-5 rounded-2xl bg-[#161b2e] border border-white/10">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Quizzes Criados</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-rose-400">
                <?= $totalQuizzesCriados ?>
            </span>
        </div>
        <div class="p-5 rounded-2xl bg-[#161b2e] border border-white/10">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Partidas Auditadas</span>
            <span class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-indigo-400">
                <?= $totalPartidasJogadas ?>
            </span>
        </div>
    </div>

    <!-- Barra de Filtros e Busca de Logs -->
    <div class="rounded-2xl bg-[#161b2e] border border-white/10 p-4 sm:p-5 mb-8 shadow-xl">
        <form action="historico.php" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Busca Geral -->
            <div class="sm:col-span-6 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= e($filterSearch) ?>"
                    placeholder="Buscar por usuário, detalhes da ação ou IP..."
                    class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-9 pr-4 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:outline-none focus:border-pink-500"
                >
            </div>

            <!-- Filtro de Ação -->
            <div class="sm:col-span-4">
                <select name="action" class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl px-3 py-2.5 text-xs sm:text-sm text-white focus:outline-none focus:border-purple-500">
                    <option value="">Todas as Ações</option>
                    <?php foreach ($actionsList as $act): ?>
                        <option value="<?= e($act) ?>" <?= $filterAction === $act ? 'selected' : '' ?>>
                            <?= e($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Botões Filtrar / Limpar -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-95 shadow transition">
                    Filtrar
                </button>
                <?php if (!empty($filterAction) || !empty($filterSearch)): ?>
                    <a href="historico.php" class="px-3 py-2.5 rounded-xl text-xs bg-white/5 hover:bg-white/10 text-slate-300 transition" title="Limpar Filtro">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabela de Logs (Mobile First com scroll horizontal limpo) -->
    <div class="rounded-3xl bg-[#161b2e] border border-white/10 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-[#0d0f1a] text-slate-400 uppercase text-[11px] font-semibold border-b border-white/10 tracking-wider">
                    <tr>
                        <th class="px-5 py-4">ID</th>
                        <th class="px-5 py-4">Ação</th>
                        <th class="px-5 py-4">Usuário</th>
                        <th class="px-5 py-4">Alvo / Tipo</th>
                        <th class="px-5 py-4">Detalhes da Alteração</th>
                        <th class="px-5 py-4">Endereço IP</th>
                        <th class="px-5 py-4">Data e Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                Nenhum log encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            // Definição de cores de badge por ação
                            $badgeStyle = 'bg-white/10 text-slate-300 border-white/10';
                            $act = $log['action'];
                            if (strpos($act, 'LOGIN') !== false) {
                                $badgeStyle = 'bg-blue-500/20 text-blue-300 border-blue-500/30';
                            } elseif (strpos($act, 'CADASTRO') !== false) {
                                $badgeStyle = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
                            } elseif (strpos($act, 'CRIAR') !== false) {
                                $badgeStyle = 'bg-purple-500/20 text-purple-300 border-purple-500/30';
                            } elseif (strpos($act, 'EDITAR') !== false) {
                                $badgeStyle = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
                            } elseif (strpos($act, 'EXCLUIR') !== false) {
                                $badgeStyle = 'bg-rose-500/20 text-rose-300 border-rose-500/30';
                            } elseif (strpos($act, 'JOGAR') !== false) {
                                $badgeStyle = 'bg-pink-500/20 text-pink-300 border-pink-500/30';
                            }
                        ?>
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-5 py-3.5 font-mono text-slate-500 text-xs">
                                    #<?= $log['id'] ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold border <?= $badgeStyle ?>">
                                        <?= e($log['action']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-white">
                                    <?= e($log['user_name'] ?: 'Sistema') ?>
                                    <?php if ($log['user_id']): ?>
                                        <span class="text-[10px] text-slate-400 block font-mono">UID: <?= $log['user_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-slate-300 font-mono text-xs">
                                    <?= e($log['target_type'] ?: '-') ?>
                                    <?= $log['target_id'] ? '#' . $log['target_id'] : '' ?>
                                </td>
                                <td class="px-5 py-3.5 max-w-xs sm:max-w-md truncate text-slate-300 text-xs" title="<?= e($log['details']) ?>">
                                    <?= e($log['details'] ?: 'Sem detalhes') ?>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-400">
                                    <?= e($log['ip_address']) ?>
                                </td>
                                <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap text-xs">
                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
