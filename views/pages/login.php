<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Ops</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { background-color: #020617; }
        .glass-panel {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        .glass-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-[url('public/assets/bg-grid.svg')] bg-fixed bg-cover selection:bg-blue-500/30 selection:text-blue-200">

    <div class="w-full max-w-md p-8 mx-4">
        <div class="text-center mb-10">
            <div class="flex items-center justify-center mb-6">
                <img src="public/assets/img/logo.png" alt="Admin Ops Logo" class="w-[150px] h-auto object-contain logo-glow" />
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Welcome Back</h1>
            <p class="text-slate-400 text-sm mt-2">Sign in to access operational dashboard</p>
        </div>

        <div class="glass-panel rounded-2xl p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/20 rounded-full blur-[50px]"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-purple-500/20 rounded-full blur-[50px]"></div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center gap-3 text-red-400 text-sm animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5 relative z-10">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1 tracking-widest">Username</label>
                    <input type="text" name="username" class="w-full px-5 py-3.5 rounded-xl text-white placeholder-slate-600 outline-none glass-input text-sm transition-all" placeholder="Masukkan username" required autocomplete="username">
                </div>
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1.5 ml-1 tracking-widest">Password</label>
                    <input type="password" name="password" class="w-full px-5 py-3.5 rounded-xl text-white placeholder-slate-600 outline-none glass-input text-sm transition-all" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" name="login" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-blue-500/20 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide mt-2">
                    Sign In
                </button>
            </form>
            
            <div class="mt-8 text-center border-t border-white/5 pt-6">
                <p class="text-xs text-slate-500">&copy; <?php echo date("Y"); ?> Internal Ops System</p>
            </div>
        </div>
    </div>
</body>
</html>