<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniProject Hub · student project platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary-gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --accent-gradient: linear-gradient(135deg, #8b5cf6 0%, #c348ec 100%);
            --dark-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0f1e;
        }
        
        .code-font {
            font-family: 'JetBrains Mono', monospace;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #0b1e33 0%, #1a2a44 50%, #162b3a 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%233b82f6" stroke-width="0.5" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            pointer-events: none;
        }
        
        .floating-code {
            position: absolute;
            color: #94a3b8;
            opacity: 0.1;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            animation: float 10s ease-in-out infinite;
            pointer-events: none;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(3deg); }
        }
        
        .skill-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
        }
        
        .skill-card:hover {
            transform: translateY(-10px) scale(1.02);
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 20px 40px rgba(0, 100, 255, 0.2);
        }
        
        .course-card {
            background: linear-gradient(145deg, #0f1a2b, #1a293b);
            border: 1px solid rgba(59, 130, 246, 0.15);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .course-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.15), transparent);
            transition: left 0.5s;
        }
        
        .course-card:hover::before {
            left: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-8px) rotateY(3deg);
            box-shadow: 0 25px 50px rgba(0, 80, 255, 0.2);
        }
        
        .project-card {
            background: rgba(17, 27, 45, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
        }
        
        .project-card:hover {
            border-color: rgba(59, 130, 246, 0.6);
            transform: scale(1.02);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15);
        }
        
        .typing-animation {
            border-right: 2px solid #3b82f6;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { border-color: #3b82f6; }
            51%, 100% { border-color: transparent; }
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #9d48ec);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .neon-glow {
            text-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        
        .tech-icon {
            transition: all 0.3s ease;
        }
        
        .tech-icon:hover {
            transform: scale(1.2) rotate(5deg);
            filter: drop-shadow(0 0 10px currentColor);
        }
        
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, #8b5cf6, #3b82f6, transparent);
            margin: 4rem 0;
        }
        
        .contact-form {
            background: rgba(17, 27, 45, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            background: #3b82f6;
            border-radius: 50%;
            animation: pulse 2s infinite;
            box-shadow: 0 0 10px #3b82f6;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #a78bfa, #e6e5e8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="text-white">
   <x-nav></x-nav>
    <!-- Hero Section - adapted for student projects platform -->
    <section id="home" class="hero-section min-h-screen flex items-center justify-center relative mt-14">
        <!-- Floating Code / Project snippets -->
        <div class="floating-code top-20 left-10" style="animation-delay: -1s;">const team = ['Alice', 'Bob', 'Carol'];</div>
        <div class="floating-code top-40 right-20" style="animation-delay: -3s;">git commit -m "collaborate"</div>
        <div class="floating-code bottom-32 left-20" style="animation-delay: -2s;">&lt;ProjectCard deadline="2025-06-01" /&gt;</div>
        <div class="floating-code bottom-20 right-10" style="animation-delay: -4s;">npm run deploy</div>
        
        <div class="text-center z-10 max-w-4xl mx-auto px-6">
          
            <h1 class="text-6xl md:text-7xl font-bold mb-6 flex flex-wrap justify-center items-center gap-4">
                <div> 
                    <span class="gradient-text">UniProject</span><br>
                    <span class="neon-glow text-white">Hub  </span>
                </div>
                <div>
                     <!-- keep existing image, just change alt -->
                     <img src="{{asset('img/grad1.png')}}" alt="Student project illustration" class="w-[400px] h-[400px] mx-auto " />
               </div>
            </h1>
          
            
            <div class="code-font text-xl md:text-2xl mb-8 text-gray-300">
                <span id="typingText" class="typing-animation">From concept to completion — student projects that matter</span>
            </div>
            
            <p class="text-lg text-gray-400 mb-12 max-w-2xl mx-auto">
                A university platform to publish team projects, find collaborators, and get feedback from peers & professors.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                @if(empty(Session::get('email')))
                    <a href="/signup" class="bg-gradient-to-r from-blue-500 to-purple-600 px-8 py-4 rounded-full font-semibold hover:scale-105 transform transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                    Join as student
                    </a>
                @else
                <a href="/Logout" class="bg-gradient-to-r from-blue-500 to-purple-600 px-8 py-4 rounded-full font-semibold hover:scale-105 transform transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                   Log out
                </a>
                @endif
                <a href="/supervisorSignup" class="border-2 border-blue-400 px-8 py-4 rounded-full font-semibold hover:bg-blue-400/10 transition-all duration-300">
                    Join as Supervisor 
                </a>
            </div>
        </div>
    </section>

     <div class="flex min-h-screen bg-white border-l-24">
            <!-- Sidebar Image -->
            <div class="hidden lg:block lg:w-1/2 relative">
                <img
                src="{{asset('img/grad2.jpg')}}"
                alt="Lost man in desert"
                class="h-full w-full object-cover"
                />
                <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            </div>

            <!-- Main content -->
            <main class="flex flex-1 items-center justify-center px-6 py-24 sm:py-32 lg:px-8">
                <div class="text-center max-w-md">
                <p class="text-base font-semibold text-green-600">SPU</p>
                <h1 class="mt-4 text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl">
                    Graduation Projects
                </h1>
                <p class="mt-6 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">
                      A university platform to publish team projects, find collaborators, and get feedback from peers & professors.
           
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a
                    href="/"
                    class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                    More
                    </a>
                    <a href="/Contact" class="text-sm font-semibold text-gray-900">
                    Contact support <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
                </div>
            </main>
     </div>
            

  
    <div class="section-divider"></div>


  
    <!-- Projects Section (showcase real student projects) -->
    <section id="projects" class="py-20 bg-gray-900/30">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-center mb-16">
                <span class="gradient-text">Featured student projects</span>
               
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Project 1 -->
                <div class="project-card rounded-xl p-6">
                    <div class="bg-gradient-to-br from-blue-600 to-purple-600 h-48 rounded-lg mb-4 flex items-center justify-center text-6xl">
                        🛒
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Campus Marketplace</h3>
                    <p class="text-gray-300 mb-4">Peer‑to‑peer platform for students to buy, sell, and exchange textbooks and gadgets.</p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded text-sm">React</span>
                        <span class="bg-green-500/20 text-green-300 px-2 py-1 rounded text-sm">Django</span>
                        <span class="bg-purple-500/20 text-purple-300 px-2 py-1 rounded text-sm">PostgreSQL</span>
                    </div>
                    <div class="flex space-x-4">
                        <button class="flex-1 bg-blue-600 hover:bg-blue-700 py-2 rounded transition-colors">
                            <i class="fab fa-github mr-2"></i>Repo
                        </button>
                        <button class="flex-1 border border-blue-600 hover:bg-blue-600/10 py-2 rounded transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>Live
                        </button>
                    </div>
                </div>

                <!-- Project 2 -->
                <div class="project-card rounded-xl p-6">
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-500 h-48 rounded-lg mb-4 flex items-center justify-center text-6xl">
                        🧠
                    </div>
                    <h3 class="text-2xl font-bold mb-3">StudyBuddy AI</h3>
                    <p class="text-gray-300 mb-4">Flashcard generator using GPT & spaced repetition – built by CS seniors.</p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded text-sm">Next.js</span>
                        <span class="bg-yellow-500/20 text-yellow-300 px-2 py-1 rounded text-sm">FastAPI</span>
                        <span class="bg-red-500/20 text-red-300 px-2 py-1 rounded text-sm">OpenAI</span>
                    </div>
                    <div class="flex space-x-4">
                        <button class="flex-1 bg-blue-600 hover:bg-blue-700 py-2 rounded transition-colors">
                            <i class="fab fa-github mr-2"></i>Repo
                        </button>
                        <button class="flex-1 border border-blue-600 hover:bg-blue-600/10 py-2 rounded transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>Demo
                        </button>
                    </div>
                </div>

                <!-- Project 3 -->
                <div class="project-card rounded-xl p-6">
                    <div class="bg-gradient-to-br from-pink-500 to-rose-500 h-48 rounded-lg mb-4 flex items-center justify-center text-6xl">
                        🏛️
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Virtual Campus Guide</h3>
                    <p class="text-gray-300 mb-4">AR‑based navigation app for new students – indoor maps and events.</p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded text-sm">Unity</span>
                        <span class="bg-purple-500/20 text-purple-300 px-2 py-1 rounded text-sm">C#</span>
                        <span class="bg-orange-500/20 text-orange-300 px-2 py-1 rounded text-sm">ARCore</span>
                    </div>
                    <div class="flex space-x-4">
                        <button class="flex-1 bg-blue-600 hover:bg-blue-700 py-2 rounded transition-colors">
                            <i class="fab fa-github mr-2"></i>Code
                        </button>
                        <button class="flex-1 border border-blue-600 hover:bg-blue-600/10 py-2 rounded transition-colors">
                            <i class="fas fa-video mr-2"></i>Trailer
                        </button>
                    </div>
                </div>
            </div>

            <!-- call to action for more projects -->
            <div class="text-center mt-12">
                <a href="/projects" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-4 rounded-full font-semibold hover:scale-105 transition-transform">
                    Browse all projects <i class="fas fa-arrow-right"></i>
                </a>
            </div>
    </section>
   

    <!-- Contact / get involved section (keep original structure but adapt text) -->
    <section id="contact" class="py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-center mb-16">
                <span class="code-font text-blue-400">&lt;</span>
                <span class="gradient-text">Get involved</span>
                <span class="code-font text-blue-400">/&gt;</span>
            </h2>
            <div class="max-w-2xl mx-auto contact-form rounded-2xl p-8">
                <p class="text-center text-gray-300 mb-8">Want to showcase your project or collaborate? Reach out to the student coordinators.</p>
                <form class="space-y-4">
                    <input type="text" placeholder="Your name / team" class="w-full bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-3 focus:border-blue-400 outline-none">
                    <input type="email" placeholder="Email address" class="w-full bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-3 focus:border-blue-400 outline-none">
                    <textarea rows="4" placeholder="Tell us about your project or question" class="w-full bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-3 focus:border-blue-400 outline-none"></textarea>
                    <button class="w-full bg-gradient-to-r from-blue-500 to-purple-600 py-3 rounded-lg font-semibold hover:scale-105 transition-transform">
                        Send message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- footer (quick minimal) -->
    <footer class="border-t border-gray-800 py-8 text-center text-gray-500">
        <p>© 2025 UniProject Hub – student project platform</p>
    </footer>
</body>
</html>