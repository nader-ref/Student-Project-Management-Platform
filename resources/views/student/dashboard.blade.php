<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Projects Hub · dashboard</title>
  <!-- Font Awesome for subtle icons (professional touch) -->
   <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    body {
      background-color: #f4f7fb;  /* very light grey backdrop, keeps white/black/blue crisp */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    /* main dashboard card — white base, black text, dark blue accents */
    .dashboard {
      width: 100%;
      max-width: 1300px;
      background-color: #ffffff;
      border-radius: 28px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      overflow: hidden;
      border: 1px solid #e9eef3;
    }

    /* --- NAVBAR (black, white, dark blue hover) --- */
    .navbar {
      background-color: #0a0a0a; /* pure black */
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: white;
      border-bottom: 2px solid #0a2942; /* dark blue accent line */
    }

    .site-name {
      font-size: 1.6rem;
      font-weight: 600;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .site-name i {
      color: #1e4a7a; /* dark blue icon */
      font-size: 1.8rem;
    }

    .user-area {
      display: flex;
      align-items: center;
      gap: 2rem;
    }
    .username {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 500;
      background: #1a1a1a; /* slightly lighter black */
      padding: 0.5rem 1.2rem;
      border-radius: 40px;
      border: 1px solid #2a3f5a; /* dark blue grey */
    }
    .username i {
      color: #2c628b; /* medium dark blue */
      font-size: 1rem;
    }
    .logout-btn {
      background-color: transparent;
      border: 1.5px solid #1e4a7a; /* dark blue */
      color: white;
      padding: 0.5rem 1.5rem;
      border-radius: 40px;
      font-weight: 600;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      cursor: default;   /* non-functional as requested, but styled */
      transition: 0.15s;
    }
    .logout-btn i {
      color: #1e4a7a;
    }
    .logout-btn:hover {
      background-color: #1e4a7a;  /* dark blue fill on hover */
      border-color: #1e4a7a;
    }
    .logout-btn:hover i {
      color: white;
    }

    /* --- TABS (dark blue bar) --- */
    .tabs-container {
      background-color: #0a2942; /* deep dark blue */
      padding: 0.75rem 2rem 0 2rem;
      display: flex;
      gap: 0.25rem;
      border-bottom: 1px solid #1e3f60;
    }

    .tab-btn {
      background: transparent;
      border: none;
      color: rgba(255, 255, 255, 0.75);
      font-size: 1rem;
      font-weight: 600;
      padding: 0.9rem 2rem 0.9rem 2rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      letter-spacing: 0.3px;
      transition: all 0.15s ease;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
      border-bottom: 3px solid transparent;
    }

    .tab-btn i {
      font-size: 1.1rem;
      color: #8bb9e0; /* lighter blue for icons */
    }

    .tab-btn:hover {
      color: white;
      background-color: #123456; /* slightly lighter dark blue */
      border-bottom-color: #8bb9e0;
    }

    .tab-btn.active {
      background-color: #ffffff;   /* white background for active tab */
      color: #0a2942;              /* dark blue text */
      border-bottom-color: #0a2942;
      font-weight: 700;
    }

    .tab-btn.active i {
      color: #0a2942; /* dark blue icon */
    }

    /* --- CONTENT SECTIONS (white background, black text) --- */
    .content-panel {
      background-color: #ffffff;
      padding: 2rem;
      color: #1e1e1e; /* near black */
    }

    .tab-content {
      display: none;
      animation: fade 0.25s ease;
    }

    .tab-content.active-content {
      display: block;
    }

    @keyframes fade {
      from { opacity: 0.5; transform: translateY(5px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* dashboard cards (example) */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.8rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: #f9fcff;
      border: 1px solid #e2eaf2;
      border-left: 5px solid #0a2942; /* dark blue accent */
      padding: 1.5rem 1.2rem;
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }

    .stat-card h3 {
      font-size: 0.95rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #4a5568; /* greyish black */
      margin-bottom: 0.5rem;
    }

    .stat-card .value {
      font-size: 1.5rem;
      font-weight: 600;
      color: #0a0a0a; /* black */
    }

    .stat-card .unit {
      font-size: 1rem;
      color: #2d4968; /* dark blue */
      margin-left: 0.25rem;
    }

    .chart-placeholder {
      background: linear-gradient(145deg, #f1f6fb, #ffffff);
      border: 2px dashed #0a2942;
      border-radius: 24px;
      padding: 3rem 2rem;
      text-align: center;
      color: #0a2942;
      font-weight: 500;
      margin-top: 1.5rem;
    }
    .chart-placeholder i {
      font-size: 3rem;
      color: #1e4a7a;
      opacity: 0.5;
      margin-bottom: 1rem;
    }

    /* analytics / dummy content */
    .data-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #dde5ed;
      color: #1e1e1e;
    }
    .data-label i {
      color: #0a2942;
      width: 2rem;
    }
    .data-value {
      font-weight: 600;
      background: #edf3f9;
      padding: 0.25rem 1rem;
      border-radius: 40px;
      color: #0a2942;
    }

    .settings-form {
      max-width: 500px;
    }
    .settings-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 0;
      border-bottom: 1px solid #ddd;
    }
    .settings-item span {
      font-weight: 500;
      color: #0a0a0a;
    }
    .toggle-indicator {
      width: 48px;
      height: 24px;
      background-color: #0a2942;
      border-radius: 40px;
      position: relative;
      box-shadow: inset 0 1px 4px rgba(0,0,0,0.2);
    }
    .toggle-indicator::after {
      content: "";
      width: 20px;
      height: 20px;
      background-color: white;
      border-radius: 50%;
      position: absolute;
      top: 2px;
      right: 3px;
    }

    /* footer / dummy */
    .footnote {
      margin-top: 2.5rem;
      font-size: 0.8rem;
      color: #4a617c;
      border-top: 1px solid #e0e8f0;
      padding-top: 1.5rem;
      text-align: right;
    }
    .footnote i {
      color: #0a2942;
    }

    /* responsive */
    @media (max-width: 700px) {
      .navbar {
        flex-direction: column;
        align-items: start;
        gap: 0.8rem;
      }
      .user-area {
        width: 100%;
        justify-content: space-between;
      }
      .tabs-container {
        flex-wrap: wrap;
        padding: 0.5rem 1rem 0 1rem;
      }
      .tab-btn {
        padding: 0.7rem 1rem;
        font-size: 0.9rem;
      }
      .content-panel {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
<div class="dashboard">

  <!-- BLACK NAVBAR with site name, username, logout -->
  <div class="navbar">
    <div class="site-name">
      <i class="fas fa-cubes"></i> 
      <span>Projects Hub</span>
    </div>
    <div class="user-area">
      <div class="username">
        <i class="fas fa-user-circle"></i> {{Session::get('email')}}
      </div>
      <div class="logout-btn" title="logout (non-functional demo)">
        <i class="fas fa-sign-out-alt"></i> <a href="/Logout">Logout</a>
      </div>
    </div>
  </div>

  <!-- DARK BLUE TABS BAR -->
  <div class="tabs-container">
    <button class="tab-btn active" data-tab="dashboard">
      <i class="fas fa-th-large"></i> Dashboard
    </button>
    <button class="tab-btn" data-tab="projects">
      <i class="fas fa-home" style="color: #0a0a0a;"></i>
      Projects
    </button>
    <button class="tab-btn" data-tab="Request">
      <i class="fas fa-chart-line"></i> Request
    </button>
     <button class="tab-btn" data-tab="Idea">
      <i class="far fa-lightbulb"></i> New idea?
    </button>
    <button class="tab-btn" data-tab="Message">
      <i class="far fa-envelope"></i> Contact
    </button>
    <button class="tab-btn" data-tab="settings">
      <i class="fas fa-sliders-h"></i> Settings
    </button>
  </div>

  <!-- WHITE CONTENT PANEL (dynamic sections) -->
  <div class="content-panel">
    <!-- SECTION 1: DASHBOARD (active by default) -->
    <div id="dashboard" class="tab-content active-content">
      <h2 style="color: #0a2942; margin-bottom: 1.5rem; font-weight: 600; display: flex; gap: 0.5rem;">
        <i class="fas fa-home" style="color: #0a0a0a;"></i> Overview
      </h2>
      <div class="cards-grid">
        <div class="stat-card">
          <h3><i class="fas fa-folder-open" style="color: #0a2942;"></i> Active projects</h3>
          <div class="value">24 <span class="unit">projects</span></div>
        </div>
        <div class="stat-card">
          <h3><i class="fas fa-users" style="color: #0a2942;"></i> Team members</h3>
          <div class="value">18 <span class="unit">people</span></div>
        </div>
        <div class="stat-card">
          <h3><i class="fas fa-clock" style="color: #0a2942;"></i> Hours this week</h3>
          <div class="value">142 <span class="unit">h</span></div>
        </div>
        <div class="stat-card">
          <h3><i class="fas fa-check-circle" style="color: #0a2942;"></i> Completed</h3>
          <div class="value">89 <span class="unit">tasks</span></div>
        </div>
      </div>
      <div class="chart-placeholder">
        <i class="fas fa-chart-pie"></i>
        <div>📊 Project distribution chart (dark blue / white theme)</div>
        <div style="font-size:0.9rem; margin-top:0.8rem; color:#2b4f73;">dummy interactive area</div>
      </div>
      <div class="footnote">
        <i class="fas fa-bolt"></i> last updated today  •  all metrics in black & dark blue
      </div>
    </div>

    <!-- SECTION 2: Projects -->
    <div id="projects" class="tab-content active-conten">
    
      <h2 style="color: #0a2942; margin-bottom: 1.5rem; font-weight: 600; display: flex; gap: 0.5rem;">
        <i class="fas fa-home" style="color: #0a0a0a;"></i> Project 
      </h2>
      <div class="cards-grid">
          @foreach($projects as $project)
          
            <div class="stat-card">
              <h3><i class="fas fa-folder-open" style="color: #0a2942;"></i> {{$project->name}}</h3>
              <div class="value ">- {{$project->id}}<span class="unit">identity</span></div>
              <div class="value ">- {{$project->description}}<span class="unit">description</span></div>
              <div class="value">- {{$project->supervisor->name}}<span class="unit"> supervisor</span></div>
              <div class="value">- {{$project->department}}<span class="unit"> department</span></div>
              <div class="value">- {{$project->seminar_1}}<span class="unit"> seminar_1</span></div>
              <div class="value">- {{$project->seminar_2}}<span class="unit"> seminar_2</span></div>
              <div class="value">- {{$project->seminar_3}}<span class="unit"> seminar_3</span></div>
              <div class="value">- {{$project->final}}<span class="unit"> final</span></div>
              <div class="value"> Taken?
                @if($project->taken== 0)
                <span class="text-green-600"> No</span>   
                @else               
                <span class="text-red-600"> Yes</span>
                @endif
              </div>
            </div>
      
          
          @endforeach
      </div>
    </div>
    <!-- SECTION 2: requests -->
    <div id="Request" class="tab-content active-conten">
       <div id="Request" class="max-w-3xl mx-auto border-2 border-black rounded-xl bg-white shadow-2xl overflow-hidden">
        <!-- Header: blue background, black border, white text -->
        <div class="bg-gray-900 text-white px-6 py-5 text-2xl font-bold border-b-2 border-black flex items-center gap-2">
          <span class="bg-white text-blue-800 w-8 h-8 rounded-full flex items-center justify-center text-lg border border-black">📋</span>
          Request to add a project
        </div>

        <!-- Form body: white background, black text, blue focus rings -->
       <form method="POST" action="/RequstAdd">
        @csrf
        <div class="p-6 md:p-8 space-y-6 bg-white text-black">
            <!-- Project ID and Message (both required) -->
            @if(!empty(Session::get('faild')))     
             <h3 class="text-red-800">{{Session::get('faild')}}</h3>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                        <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Project ID <span class="text-blue-600 text-lg">*</span>
                    </label>
                    <input type="number" name="projectid" placeholder="e.g. PRJ-2025-01" required
                          class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black placeholder-gray-500">
                </div>
                <div class="space-y-1 mt-2 md:col-span-1">
                    <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                        <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Number of Student <span class="text-blue-600">*</span>
                    </label>
                    <input type="number" name="count" placeholder="e.g. PRJ-2025-01" required
                          class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black placeholder-gray-500">
                </div>
            </div>
             @error('projectid')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- Main student (required) -->
            <div class="border-2 border-black rounded-lg p-5 bg-white">
                <div class="flex items-center gap-2 mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border border-black">1</span>
                    <span class="font-bold text-lg text-black">Main student <span class="text-blue-600 text-base">(required)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-black/80 uppercase tracking-wide">Full name</label>
                        <input type="text" name="nameone" placeholder="e.g. Maria Santos" required
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-black/80 uppercase tracking-wide">Student ID</label>
                        <input type="number" name="oneid" placeholder="e.g. S2200123" required
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>

            <!-- Friend 1 (optional) -->
            <div class="border-t border-black/20 pt-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-blue-800 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">2</span>
                    <span class="font-semibold text-black">Friend 1 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's name</label>
                        <input type="text" name="nametwo" placeholder="e.g. João Pereira"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's ID</label>
                        <input type="number" name="twoid" placeholder="e.g. S2200456"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>
            @error('twoid')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- Friend 2 (optional) -->
            <div class="border-t border-black/20 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-blue-800 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">3</span>
                    <span class="font-semibold text-black">Friend 2 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's name</label>
                        <input type="text" name="namethree" placeholder="e.g. Clara Lee"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's ID</label>
                        <input type="number" name ="threeid" placeholder="e.g. S2200789"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>
            @error('threeid')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- Hint: min 1 max 3 -->
            <div class="flex items-center gap-2 text-sm text-black/70 border-t border-black/20 pt-4">
                <div class="w-5 h-5 rounded-full border-2 border-blue-600 flex items-center justify-center text-blue-800 font-bold text-xs">i</div>
                <span>At least one student (main), maximum three members in total. Leave friend fields empty if not applicable.</span>
            </div>

            <!-- Submit button: blue with black border -->
            <div class="flex justify-between pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                    <span>📬</span> Submit request
                </button>
                <a href="/StudentDashboard/acceptance" class="bg-gray-600 hover:bg-blue-800 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                    <span>📬</span> check your request
                </a>
                
            </div>
        </div>
       </form>
      </div>
    </div>

    <!-- SECTION 2: ideas-->
    <div id="Idea" class="tab-content active-conten">
       <div id="Idea" class="max-w-3xl mx-auto border-2 border-black rounded-xl bg-white shadow-2xl overflow-hidden">
        <!-- Header: blue background, black border, white text -->
        <div class="bg-gray-900 text-white px-6 py-5 text-2xl font-bold border-b-2 border-black flex items-center gap-2">
          <span class="bg-white text-blue-800 w-8 h-8 rounded-full flex items-center justify-center text-lg border border-black">📋</span>
          Request to add a New project idea
        </div>

        <!-- Form body: white background, black text, blue focus rings -->
       <form method="POST" action="/RequstIdea">
        @csrf
        <div class="p-6 md:p-8 space-y-6 bg-white text-black">
            <!-- Project ID and Message (both required) -->
            @if(!empty(Session::get('faild2')))     
             <h3 class="text-red-800">{{Session::get('faild2')}}</h3>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                        <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Project Name<span class="text-blue-600 text-lg">*</span>
                    </label>
                    <input type="text" name="projectname" placeholder="hotel reservation" required
                          class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black placeholder-gray-500">
                </div>
                <div class="space-y-1 mt-2 md:col-span-1">
                    <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                        <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Number of Student <span class="text-blue-600">*</span>
                    </label>
                    <input type="number" name="count" placeholder="e.g. PRJ-2025-01" required
                          class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black placeholder-gray-500">
                </div>
                <div class="space-y-1 mt-2 md:col-span-1">
                  <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                      <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Supervisor Name <span class="text-blue-600">*</span>
                  </label>
                  <select name="supname" required
                          class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                      <option value="" disabled selected>Select a supervisor</option>
                      <option value="John Smith">John Smith</option>
                      <option value="Jane Doe">Jane Doe</option>
                      <option value="Michael Johnson">Michael Johnson</option>
                      <option value="Emily Davis">Emily Davis</option>
                      <option value="David Wilson">David Wilson</option>
                  </select>
              </div>
            </div>
             @error('supname')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- Main student (required) -->
            <div class="border-2 border-black rounded-lg p-5 bg-white">
                <div class="flex items-center gap-2 mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border border-black">1</span>
                    <span class="font-bold text-lg text-black">Main student <span class="text-blue-600 text-base">(required)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-black/80 uppercase tracking-wide">Full name</label>
                        <input type="text" name="nameone" placeholder="e.g. Maria Santos" required
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-black/80 uppercase tracking-wide">Student ID</label>
                        <input type="number" name="oneid" placeholder="e.g. S2200123" required
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>

            <!-- Friend 1 (optional) -->
            <div class="border-t border-black/20 pt-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-blue-800 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">2</span>
                    <span class="font-semibold text-black">Friend 1 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's name</label>
                        <input type="text" name="nametwo" placeholder="e.g. João Pereira"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's ID</label>
                        <input type="number" name="twoid" placeholder="e.g. S2200456"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>
            @error('twoid')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- Friend 2 (optional) -->
            <div class="border-t border-black/20 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-blue-800 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">3</span>
                    <span class="font-semibold text-black">Friend 2 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's name</label>
                        <input type="text" name="namethree" placeholder="e.g. Clara Lee"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's ID</label>
                        <input type="number" name ="threeid" placeholder="e.g. S2200789"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>
            @error('threeid')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- Hint: min 1 max 3 -->
            <div class="flex items-center gap-2 text-sm text-black/70 border-t border-black/20 pt-4">
                <div class="w-5 h-5 rounded-full border-2 border-blue-600 flex items-center justify-center text-blue-800 font-bold text-xs">i</div>
                <span>At least one student (main), maximum three members in total. Leave friend fields empty if not applicable.</span>
            </div>

            <!-- Submit button: blue with black border -->
            <div class="flex justify-between pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                    <span>📬</span> Submit request
                </button>
                <a href="/StudentDashboard/acceptanceidea" class="bg-gray-600 hover:bg-blue-800 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                    <span>📬</span> check your request
                </a>
                
            </div>
        </div>
       </form>
      </div>
    </div>

    <!-- SECTION 2: ideas-->
    <div id="Message" class="tab-content active-conten">
       <div id="Message" class="max-w-3xl mx-auto border-2 border-black rounded-xl bg-white shadow-2xl overflow-hidden">
        <!-- Header: blue background, black border, white text -->
        <div class="bg-gray-900 text-white px-6 py-5 text-2xl font-bold border-b-2 border-black flex items-center gap-2">
          <span class="bg-white text-blue-800 w-8 h-8 rounded-full flex items-center justify-center text-lg border border-black">📋</span>
          Send a Message 
        </div>
      
        <!-- Form body: white background, black text, blue focus rings -->
       <form method="POST" action="/Message">
        @csrf
        <div class="p-6 md:p-8 space-y-6 bg-white text-black">
            <!-- Project ID and Message (both required) -->
            @if(!empty(Session::get('success')))     
             <h3 class="text-green-800">{{Session::get('success')}}</h3>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1 mt-2 md:col-span-1">
                  <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                      <span class="w-2 h-2 bg-blue-600 rounded-full"></span> Supervisor Name <span class="text-blue-600">*</span>
                  </label>
                  <select name="supname" required
                          class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                      <option value="" disabled selected>Select a supervisor</option>
                      <option value="John Smith">John Smith</option>
                      <option value="Jane Doe">Jane Doe</option>
                      <option value="Michael Johnson">Michael Johnson</option>
                      <option value="Emily Davis">Emily Davis</option>
                      <option value="David Wilson">David Wilson</option>
                  </select>
              </div>
            </div>
             @error('supname')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
            <!-- subject -->
            <div class="border-2 border-black rounded-lg p-5 bg-white">
                <div class="flex items-center gap-2 mb-4">
                    <span class="bg-blue-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border border-black">1</span>
                    <span class="font-bold text-lg text-black">Subject <span class="text-blue-600 text-base">(required)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs font-medium text-black/80 uppercase tracking-wide">Subject</label>
                        <input type="text" name="subject" placeholder="Seminar one requriments" required
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                 
                </div>
            </div>

            <!-- Message -->
            <div class="border-t border-black/20 pt-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-blue-800 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">2</span>
                    <span class="font-semibold text-black">Message <span class="text-sm font-normal text-black/60 italic"></span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-1 gap-4 pl-2">
                    <div>
                        <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Friend's name</label>
                        <input type="text" name="Message" placeholder="e.g. João Pereira"
                              class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 bg-white text-black">
                    </div>
                </div>
            </div>
            @error('Message')
                <span class="text-red-600">{{ $message }}</span>
            @enderror
          
            <!-- Submit button: blue with black border -->
            <div class="flex justify-between pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                    <span>📬</span> Submit request
                </button>
                <a href="/StudentDashboard/replay" class="bg-gray-600 hover:bg-blue-800 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                    <span>📬</span> your messages
                </a>
                
            </div>
        </div>
       </form>
      </div>
    </div>

    <!-- SECTION 3: SETTINGS -->
    <div id="settings" class="tab-content">
      <h2 style="color: #0a2942; margin-bottom: 1.5rem;"><i class="fas fa-cog" style="color: black;"></i> workspace settings</h2>
      <div class="settings-form">
        <div class="settings-item">
          <span><i class="fas fa-globe" style="color: #0a2942; width: 2rem;"></i> Dark mode (black/white)</span>
          <div class="toggle-indicator"></div>
        </div>
        <div class="settings-item">
          <span><i class="fas fa-bell" style="color: #0a2942;"></i> Notifications</span>
          <div class="toggle-indicator" style="background-color: #aaa;"></div>
        </div>
        <div class="settings-item">
          <span><i class="fas fa-lock" style="color: #0a2942;"></i> Privacy mode</span>
          <div class="toggle-indicator"></div>
        </div>
        <div class="settings-item">
          <span><i class="fas fa-palette" style="color: #0a2942;"></i> Accent color (dark blue)</span>
          <span style="background: #0a2942; color:white; padding:0.2rem 1rem; border-radius:40px;">default</span>
        </div>
      </div>
      <div style="margin-top: 2.5rem; background: #eaf1fa; padding: 1rem; border-radius: 20px; color: #0a1c2f;">
        <i class="fas fa-info-circle" style="color: #0a2942;"></i>  these are dummy settings – logout tab is non‑functional, username displayed.
      </div>
    </div>
  </div>
  <!-- tiny extra black line (design detail) -->
  <div style="height: 4px; background: #0a0a0a;"></div>
</div>

<!-- simple tab switching script -->
<script>
  (function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const contents = {
      dashboard: document.getElementById('dashboard'),
      projects: document.getElementById('projects'),
      Request: document.getElementById('Request'),
      Idea: document.getElementById('Idea'),
      Message: document.getElementById('Message'),
      settings: document.getElementById('settings')
    };

    // remove active class from all tabs & contents
    function deactivateAll() {
      tabButtons.forEach(btn => btn.classList.remove('active'));
      Object.values(contents).forEach(section => {
        if (section) section.classList.remove('active-content');
      });
    }

    // set active tab/content based on data-tab value
    function activateTab(tabId) {
      deactivateAll();
      // active tab button
      const activeBtn = Array.from(tabButtons).find(btn => btn.dataset.tab === tabId);
      if (activeBtn) activeBtn.classList.add('active');
      // active content
      if (contents[tabId]) contents[tabId].classList.add('active-content');
    }

    // add click listeners
    tabButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        const tabId = this.dataset.tab;
        if (tabId) activateTab(tabId);
      });
    });

    // initial state: first tab (dashboard) is active via static classes, 
    // but ensure consistency (in case of mismatch)
    // (already set in html, but double-check)
    if (!document.querySelector('.tab-btn.active')) {
      activateTab('dashboard');
    } else {
      // if there's an active class on a tab but content mismatch, fix
      const currentActiveTab = document.querySelector('.tab-btn.active');
      if (currentActiveTab) {
        const activeTabId = currentActiveTab.dataset.tab;
        Object.values(contents).forEach(s => s.classList.remove('active-content'));
        if (contents[activeTabId]) contents[activeTabId].classList.add('active-content');
      }
    }
  })();
</script>
<!-- subtle extra: ensures that even if html classes interfere, dashboard visible -->
</body>
</html>