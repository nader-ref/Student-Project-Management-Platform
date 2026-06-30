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
                <h2 class="text-4xl font-bold neon-glow mb-4">Reset Your Password</h2>
               <img src="{{asset('img/grad3.png')}}" alt="Profile" class="w-[150px] h-[150px] mx-auto rounded-full shadow-xl bg-white" />
                <p class="text-gray-400 mt-2 text-sm">Welcome , student 👨</p>
                <div class="mt-4"></div>
            </div>


            @if (session('status'))
                <p style="color: green;">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div style="color: red;" >
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required class="w-full px-4 py-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="submit" class="mt-4 w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full font-semibold hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-blue-500/25">Send Reset Link</button>
            </form>

         
        </div>
    </section>
</body>
</html>


{{-- <!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body class="bg-black">
    <h1>Reset Your Password</h1>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div style="color: red;" >
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html> --}}

