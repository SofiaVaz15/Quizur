<?php
/**
 * Tela de Login de Usuários
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

// Se já estiver logado, redireciona
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Entrar";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-12 relative">
    <!-- Luzes decorativas de fundo -->
    <div class="absolute w-[450px] h-[450px] bg-gradient-to-tr from-purple-600/20 via-pink-600/20 to-blue-600/20 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <div class="w-full max-w-md">
        <!-- Card de Login -->
        <div class="rounded-3xl bg-[#161b2e]/90 border border-white/10 p-8 shadow-2xl backdrop-blur-xl">
            <!-- Header do Card -->
            <div class="text-center mb-8">
                <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-purple-600 via-pink-500 to-blue-500 items-center justify-center text-white text-2xl shadow-lg shadow-purple-500/30 mb-4">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white">Bem-vindo(a) ao Quizur</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5">Entre com seu e-mail e senha para continuar</p>
            </div>

            <!-- Formulário -->
            <form action="actions/auth_login.php" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">E-mail</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            required
                            placeholder="seu.email@exemplo.com"
                            class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition"
                        >
                    </div>
                </div>

                <!-- Senha -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Senha</label>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required
                            placeholder="Sua senha secreta"
                            class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"
                        >
                    </div>
                </div>

                <!-- Botão Entrar -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 via-pink-500 to-blue-500 hover:opacity-95 shadow-lg shadow-purple-500/25 transition duration-200 mt-2"
                >
                    Entrar na Conta
                </button>
            </form>

            <!-- Card de Credenciais Demo para Testes -->
            <div class="mt-8 pt-6 border-t border-white/10">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 text-center">Contas para Avaliação Rápida</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="document.getElementById('email').value='admin@quizur.com'; document.getElementById('password').value='admin123';" class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20 hover:bg-purple-500/20 text-purple-300 text-left transition">
                        <p class="font-bold">👑 Admin</p>
                        <p class="text-[11px] text-slate-400">admin@quizur.com</p>
                    </button>
                    <button type="button" onclick="document.getElementById('email').value='quimica@quizur.com'; document.getElementById('password').value='quimica123';" class="p-2.5 rounded-xl bg-pink-500/10 border border-pink-500/20 hover:bg-pink-500/20 text-pink-300 text-left transition">
                        <p class="font-bold">🎓 Aluno</p>
                        <p class="text-[11px] text-slate-400">quimica@quizur.com</p>
                    </button>
                </div>
            </div>

            <!-- Link Registro -->
            <p class="text-center text-xs text-slate-400 mt-6">
                Não tem uma conta ainda? 
                <a href="registro.php" class="text-pink-400 hover:text-pink-300 font-semibold underline underline-offset-4 ml-1">
                    Cadastre-se grátis
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
