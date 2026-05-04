<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Developer Portfolio · Blue theme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .floating-code {
            position: absolute;
            font-family: monospace;
            color: white;
            opacity: 0.8;
            animation: floatUpDown 6s ease-in-out infinite;
        }
        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        /* subtle blue glow for heading (kept minimal) */
        .neon-glow {
            text-shadow: 0 0 8px rgba(59, 130, 246, 0.6);
        }
    </style>
</head>
<body>
    <!-- Main section: changed background to blue‑950, all red accents → blue & cyan -->
    <section class="min-h-screen flex items-center justify-center bg-blue-950/100 relative text-white px-6">
    

        <!-- Sign In Card (background unchanged, only text & ring colours) -->
        <div class="bg-gray-900/70 backdrop-blur-md shadow-2xl p-10 rounded-xl w-full max-w-md z-10 m-10">
            <div class="text-center mb-10">
                <div class="mb-4"></div>
                <h2 class="text-4xl font-bold neon-glow mb-4">Sign Up</h2>
                <!-- profile image (kept as is) -->
                <img src="{{asset('img/grad3.png')}}" alt="Profile" class="w-[150px] h-[150px] mx-auto rounded-full shadow-xl bg-white" />
                <p class="text-gray-400 mt-2 text-sm">Welcome ,Student 👨</p>
                <div class="mt-4"></div>
            </div>

            <!-- Form: labels, focus rings, accent colors updated to blue & cyan -->
            <form action="/signup" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1 code-font">Name</label>
                    <input type="text" name="name" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Nader" required />
                </div>
                @error('name')
                    <span class="text-sm text-red-300">{{$message}}</span>
                @enderror

                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1 code-font">Email</label>
                    <input type="email" name="email" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="you@example.com" required />
                </div>
                @error('email')
                    <span class="text-sm text-red-300">{{$message}}</span>
                @enderror

                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1 code-font">Password</label>
                    <input type="password" name="password" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="••••••••" required />
                </div>
                @error('password')
                    <span class="text-sm text-red-300">{{$message}}</span>
                @enderror

                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1 code-font">Confirm Your Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="••••••••" required />
                </div>

                <div class="flex justify-between items-center text-sm text-gray-400">
                    <label class="flex items-center space-x-2">
                        <!-- changed accent to blue-500 -->
                        <input type="checkbox" class="accent-blue-500" name="remember" />
                        <span class="code-font">Remember me</span>
                    </label>
                    {{-- <a href="/forgot-password" class="text-blue-400 hover:underline">Forgot password?</a> --}}
                </div>

                <!-- Gradient button: from blue to cyan (instead of red/yellow) -->
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                    Sign up
                </button>
            </form>

            <!-- Footer link: updated to blue-400 -->
            <p class="text-sm text-center text-gray-400 mt-6">
                Already have an account?
                <a href="/Login" class="text-blue-400 hover:underline">Log in</a>
            </p>
        </div>
    </section>
</body>
</html>