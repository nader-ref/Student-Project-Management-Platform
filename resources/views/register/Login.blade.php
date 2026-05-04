<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Developer Portfolio · Login blue</title>
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
        /* subtle blue glow for heading */
        .neon-glow {
            text-shadow: 0 0 8px rgba(59, 130, 246, 0.6);
        }
    </style>
</head>
<body>
    <!-- changed background to blue-950, all red accents → blue/cyan -->
    <section class="min-h-screen flex items-center justify-center bg-blue-950/100 relative text-white px-6">
        <!-- Floating Code Elements (unchanged, neutral) -->
       
        <!-- Sign In Card (same glass background) -->
        <div class="m-10 bg-gray-900/70 backdrop-blur-md shadow-2xl p-10 rounded-xl w-full max-w-md z-10">
            <div class="text-center mb-10">
                <div class="mb-4"></div>
                <h2 class="text-4xl font-bold neon-glow mb-4">Log In</h2>
               <img src="{{asset('img/grad3.png')}}" alt="Profile" class="w-[150px] h-[150px] mx-auto rounded-full shadow-xl bg-white" />
                <p class="text-gray-400 mt-2 text-sm">Welcome , student 👨</p>
                <div class="mt-4"></div>
            </div>

            <form action="/Login" method="POST" class="space-y-6">
                @csrf
                {{-- <div>
                    <!-- label text changed to blue-400 -->
                    <label class="block text-sm font-semibold text-blue-400 mb-1 code-font">Name</label>
                    <input type="text" name="name" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Hana balkey" required />
                </div>
                @error('name')
                    <span class="text-sm text-red-300">{{$message}}</span>
                @enderror --}}

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
                
               
                <input type="text" name="role" value="user" hidden />

                <div class="flex justify-between items-center text-sm text-gray-400">
                    <label class="flex items-center space-x-2">
                        <!-- changed accent to blue-500 -->
                        <input type="checkbox" class="accent-blue-500" name="remember" />
                        <span class="code-font">Remember me</span>
                    </label>
                    <!-- link color updated to blue-400 -->
                    <a href="/ChangePassword" class="text-blue-400 hover:underline">Change password?</a>
                      <a href="/ForgetPassword" class="text-blue-400 hover:underline">Forget password?</a>
                </div>

                <!-- gradient button: from blue to cyan (replaces red/yellow) -->
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                    Log In 
                </button>
            </form>

            <!-- footer link changed to blue-400 -->
            <p class="text-sm text-center text-gray-400 mt-6">
                Don't have an account?
                <a href="/signup" class="text-blue-400 hover:underline">Sign up</a>
            </p>
        </div>
    </section>
</body>
</html>