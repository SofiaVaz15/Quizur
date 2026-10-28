<?php
/**
 * Tela de Cadastro de Usuários
 * Quizur - 1° Química Manhã
 */
require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/helpers/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Criar Conta";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[85vh] flex items-center justify-center px-4 py-12 relative">
    <!-- Luzes decorativas de fundo -->
    <div class="absolute w-[450px] h-[450px] bg-gradient-to-tr from-pink-600/20 via-purple-600/20 to-blue-600/20 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <div class="w-full max-w-md">
        <!-- Card de Registro -->
        <div class="rounded-3xl bg-[#161b2e]/90 border border-white/10 p-8 shadow-2xl backdrop-blur-xl">
            <!-- Header do Card -->
            <div class="text-center mb-8">
                <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-pink-500 via-purple-500 to-blue-500 items-center justify-center text-white text-2xl shadow-lg shadow-pink-500/30 mb-4">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white">Criar Nova Conta</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5">Junte-se à turma do 1° Química Manhã</p>
            </div>

            <!-- Formulário -->
            <form action="actions/auth_register.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Nome Completo -->
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Seu Nome / Apelido</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            required
                            placeholder="Ex: Carlos Silva"
                            class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition"
                        >
                    </div>
                </div>

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">E-mail</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            required
                            placeholder="seu.email@exemplo.com"
                            class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"
                        >
                    </div>
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Senha (Mínimo 6 caracteres)</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required
                            minlength="6"
                            placeholder="••••••••"
                            class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                        >
                    </div>
                </div>

                <!-- Confirmação de Senha -->
                <div>
                    <label for="password_confirm" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Confirmar Senha</label>
                    <div class="relative">
                        <i class="fa-solid fa-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input 
                            type="password" 
                            name="password_confirm" 
                            id="password_confirm" 
                            required
                            minlength="6"
                            placeholder="••••••••"
                            class="w-full bg-[#0d0f1a] border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition"
                        >
                    </div>
                </div>

                <!-- Botão Registrar -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-pink-500 via-purple-600 to-blue-500 hover:opacity-95 shadow-lg shadow-pink-500/25 transition duration-200 mt-4"
                >
                    Criar Minha Conta
                </button>
            </form>

            <!-- Link Login -->
            <p class="text-center text-xs text-slate-400 mt-6 pt-6 border-t border-white/10">
                Já tem cadastro? 
                <a href="login.php" class="text-blue-400 hover:text-blue-300 font-semibold underline underline-offset-4 ml-1">
                    Faça login aqui
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
