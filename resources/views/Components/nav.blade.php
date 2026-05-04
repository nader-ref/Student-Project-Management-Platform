 <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-black/80 backdrop-blur-md z-50 border-b border-gray-800">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="status-indicator"></div>
                    <span class="code-font text-white">UNIProject Hub</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-whitehover:text-red-400 transition-colors">Home</a>
                    <a href="#about" class="hover:text-red-400 transition-colors">About</a>
                    <a href="#courses" class="hover:text-red-400 transition-colors">Courses</a>
                    <a href="#projects" class="hover:text-red-400 transition-colors">Projects</a>
                    @if(Session::has('email'))
                    <a href="/list" class="text-red-400 hover:text-red-800 transition-colors">My List</a>
                    <a href="/notifications" class="text-red-400 hover:text-red-800 transition-colors">Notifications</a>
                    @endif
                    <a href="#contact" class="hover:text-red-400 transition-colors">Contact</a>
                </div>
                <button class="md:hidden text-xl" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-black/90 backdrop-blur-md">
            <div class="px-6 py-4 space-y-4">
                <a href="#home" class="block hover:text-green-400 transition-colors">Home</a>
                <a href="#about" class="block hover:text-green-400 transition-colors">About</a>
                <a href="#courses" class="block hover:text-green-400 transition-colors">Courses</a>
                <a href="#projects" class="block hover:text-green-400 transition-colors">Projects</a>
                <a href="#skills" class="block hover:text-green-400 transition-colors">Skills</a>
                <a href="#contact" class="block hover:text-green-400 transition-colors">Contact</a>
            </div>
        </div>
    </nav>
