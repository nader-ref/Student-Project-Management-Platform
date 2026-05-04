<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password · Blue Theme</title>
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
        .neon-glow {
            text-shadow: 0 0 8px rgba(59, 130, 246, 0.6);
        }
    </style>
</head>
<body>
    <section class="min-h-screen flex items-center justify-center bg-blue-950 relative text-white px-6">
        <!-- Floating Code Elements can be added here if needed -->
        
        <div class="m-10 bg-gray-900/70 backdrop-blur-md shadow-2xl p-10 rounded-xl w-full max-w-md z-10">
            <div class="text-center mb-10">
                <h2 class="text-4xl font-bold neon-glow mb-4">Change Password</h2>
                <img src="{{asset('img/grad3.png')}}" alt="Profile" class="w-[150px] h-[150px] mx-auto rounded-full shadow-xl bg-white" />
                <p class="text-gray-400 mt-2 text-sm">Welcome, student 👨</p>
            </div>

            <form action="/change" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="you@gmail.com" required />
                </div>
                @error('email')
                    <span class="text-sm text-red-300">{{$message}}</span>
                @enderror

                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1">Old Password</label>
                    <input type="password" name="old" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="••••••••" required />
                </div>
                @error('old')
                    <span class="text-sm text-red-300">{{$message}}</span>
                @enderror

                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1">New password</label>
                    <input type="password" name="new" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="••••••••" required />
                </div>
             

                <div>
                    <label class="block text-sm font-semibold text-blue-400 mb-1 code-font">Confirm Your Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="••••••••" required />
                </div>
                @if(!empty(Session::get('failed')))
                    password and password confirmation does not match
                @endif

                <input type="hidden" name="role" value="user" />

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                    Change Password
                </button>
            </form>

            <p class="text-sm text-center text-gray-400 mt-6">
                Don't have an account?
                <a href="/signup" class="text-blue-400 hover:underline">Sign up</a>
            </p>
        </div>
    </section>
</body>
</html>