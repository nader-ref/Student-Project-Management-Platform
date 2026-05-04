<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Portfolio Login · Yellow & White Theme</title>
    <!-- Tailwind CSS + Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom yellow/white glow and smoothness */
        .neon-glow {
            text-shadow: 0 0 8px rgba(234, 179, 8, 0.5), 0 0 2px rgba(253, 224, 71, 0.7);
        }
        .gold-border-glow {
            transition: box-shadow 0.2s ease;
        }
        input:focus {
            box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
        }
        /* Optional floating particles style – preserved but made subtle yellow-ish */
        .floating-code {
            position: absolute;
            font-family: monospace;
            color: #facc15;
            opacity: 0.3;
            animation: floatUpDown 6s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(2deg); }
        }
        /* custom card interior smoothness */
        .soft-card {
            backdrop-filter: blur(2px);
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(253, 224, 71, 0.3);
        }
    </style>
</head>
<body class="antialiased">

  
    <section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-amber-50 via-yellow-50 to-white relative px-6 py-8">
        
   
        <div class="absolute top-20 left-10 w-64 h-64 bg-yellow-200/30 rounded-full blur-3xl -z-5"></div>
        <div class="absolute bottom-20 right-10 w-80 h-80 bg-amber-100/40 rounded-full blur-3xl -z-5"></div>
        
        <div class="floating-code text-2xl absolute top-[15%] left-[5%] opacity-20"> &lt;/&gt; </div>
        <div class="floating-code text-xl absolute bottom-[12%] right-[8%] opacity-25"> {💻} </div>
        <div class="floating-code text-lg absolute top-[30%] right-[12%] opacity-15"> # </div>
        <div class="floating-code text-base absolute bottom-[25%] left-[10%] opacity-20"> &lt;dev&gt; </div>

        
        <div class="soft-card shadow-2xl rounded-2xl w-full max-w-md z-10 transition-all duration-300 hover:shadow-yellow-200/40">
            <div class="p-8 md:p-10">
                <div class="text-center mb-8">
                    <div class="flex justify-center mb-5">
                        
                        <div class="w-28 h-28 rounded-full bg-white shadow-md flex items-center justify-center border-2 border-yellow-300 overflow-hidden">
                            <img src="{{asset('img/grad3.png')}}" alt="Profile" class="w-full h-full object-cover rounded-full" onerror="this.src='https://via.placeholder.com/120?text=👤';">
                        </div>
                    </div>
                    <h2 class="text-4xl font-extrabold tracking-tight neon-glow text-yellow-600 mb-2">Log In</h2>
                    <p class="text-gray-500 text-sm font-medium mt-1">Welcome back, Supervisor</p>
                    <div class="w-16 h-1 bg-yellow-400 rounded-full mx-auto mt-4"></div>
                </div>

               
                <form action="/supervisorSignup" method="POST" class="space-y-6">
                    @csrf
                     <div class="space-y-1 mt-2 md:col-span-1">
                        <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                            <span class="w-2 h-2 bg-yellow-600 rounded-full"></span> Supervisor Name <span class="text-yellow-600">*</span>
                        </label>
                        <select name="name" required
                                class="w-full px-4 py-3 border-2 border-gray-100 rounded-lg focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 bg-white text-black">
                            <option value="" disabled selected>Select your name</option>
                            <option value="John Smith">John Smith</option>
                            <option value="Jane Doe">Jane Doe</option>
                            <option value="Michael Johnson">Michael Johnson</option>
                            <option value="Emily Davis">Emily Davis</option>
                            <option value="David Wilson">David Wilson</option>
                        </select>
                    </div>
                    @error('name')
                        <span class="text-sm text-rose-500 block -mt-3">{{$message}}</span>
                    @enderror
                    <!-- Email field -->
                    <div>
                        <label class="block text-sm font-semibold text-yellow-700 mb-1.5 tracking-wide">
                            <i class="fas fa-envelope mr-1 text-yellow-500 text-xs"></i> Email
                        </label>
                        <input type="email" name="email" 
                               class="w-full px-4 py-3 bg-white border border-gray-200 text-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all duration-200 placeholder:text-gray-400" 
                               placeholder="you@example.com" required />
                    </div>
                    @error('email')
                        <span class="text-sm text-rose-500 block -mt-3">{{$message}}</span>
                    @enderror

                    <!-- Password field -->
                    <div>
                        <label class="block text-sm font-semibold text-yellow-700 mb-1.5 tracking-wide">
                            <i class="fas fa-lock mr-1 text-yellow-500 text-xs"></i> Password
                        </label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 bg-white border border-gray-200 text-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all duration-200 placeholder:text-gray-400" 
                               placeholder="••••••••" required />
                    </div>
                    @error('password')
                        <span class="text-sm text-rose-500 block -mt-3">{{$message}}</span>
                    @enderror
                    
                    <!-- Hidden role field -->
                    <input type="text" name="role" value="supervisor" hidden />
                 
                   
                    
                    <!-- Log In Button: sunny yellow gradient with white text -->
                    <button type="submit" 
                            class="w-full py-3.5 bg-gradient-to-r from-yellow-400 to-amber-500 rounded-xl font-bold text-white tracking-wide shadow-md hover:shadow-yellow-500/30 transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-right-to-bracket text-sm"></i> Log In
                    </button>
                </form>

                <!-- Sign up link with yellow theme -->
               

                <!-- extra micro info (optional security) -->
                <div class="flex items-center justify-center gap-2 mt-6 text-[11px] text-gray-400">
                    <i class="fas fa-shield-alt text-yellow-400"></i>
                    <span> Secure login • Role: Supervisor</span>
                </div>
            </div>
        </div>
    </section>

    <!-- small inline script to ensure any placeholder errors or dynamic behavior (optional) -->
    <script>
      
        (function() {
            const profileImg = document.querySelector('img[alt="Profile"]');
            if (profileImg && profileImg.complete && profileImg.naturalWidth === 0) {
                profileImg.src = 'https://ui-avatars.com/api/?background=facc15&color=fff&bold=true&size=120&name=SP';
            }
        })();
    </script>
</body>
</html>