<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Projects Hub · Golden Edition</title>
  <!-- Font Awesome & Tailwind for utilities (Tailwind only used for base reset, custom yellow theme overrides) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* ——— YELLOW THEME CORE ——— */
    body {
      background: #fef7e0;  /* warm, creamy yellow backdrop */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    /* main dashboard card — white base, golden accents */
    .dashboard {
      width: 100%;
      max-width: 1300px;
      background-color: #ffffff;
      border-radius: 32px;
      box-shadow: 0 25px 45px -12px rgba(170, 120, 20, 0.25);
      overflow: hidden;
      border: 1px solid #f5e2b6;
      transition: all 0.2s;
    }

    /* --- NAVBAR : deep charcoal + golden glow --- */
    .navbar {
      background-color: #1e1b0f;  /* rich dark brown-black */
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #fff3db;
      border-bottom: 3px solid #f5b042; /* vibrant golden line */
    }

    .site-name {
      font-size: 1.65rem;
      font-weight: 700;
      letter-spacing: -0.3px;
      display: flex;
      align-items: center;
      gap: 0.7rem;
    }
    .site-name i {
      color: #f5b042; /* pure gold */
      font-size: 1.9rem;
      filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
    }
    .site-name span {
      background: linear-gradient(135deg, #ffdd99, #ffcc66);
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
      font-weight: 700;
    }

    .user-area {
      display: flex;
      align-items: center;
      gap: 1.8rem;
    }
    .username {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-weight: 500;
      background: #2c281a;
      padding: 0.5rem 1.3rem;
      border-radius: 60px;
      border: 1px solid #e6b142;
      color: #ffecb3;
    }
    .username i {
      color: #f5b042;
      font-size: 1rem;
    }
    .logout-btn {
      background-color: transparent;
      border: 1.5px solid #f5b042;
      color: #ffdd99;
      padding: 0.5rem 1.6rem;
      border-radius: 60px;
      font-weight: 600;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      cursor: pointer;
      transition: 0.2s;
    }
    .logout-btn i {
      color: #f5b042;
    }
    .logout-btn:hover {
      background-color: #f5b042;
      border-color: #f5b042;
      color: #1e1b0f;
    }
    .logout-btn:hover i {
      color: #1e1b0f;
    }
    .logout-btn a {
      text-decoration: none;
      color: inherit;
    }

    /* --- TABS : warm golden dark background --- */
    .tabs-container {
      background-color: #2f2712;  /* deep amber-brown */
      padding: 0.7rem 2rem 0 2rem;
      display: flex;
      gap: 0.35rem;
      border-bottom: 1px solid #e6b142;
      flex-wrap: wrap;
    }

    .tab-btn {
      background: transparent;
      border: none;
      color: rgba(255, 245, 215, 0.85);
      font-size: 1rem;
      font-weight: 600;
      padding: 0.85rem 2rem 0.85rem 2rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.7rem;
      letter-spacing: 0.2px;
      transition: all 0.2s ease;
      border-top-left-radius: 14px;
      border-top-right-radius: 14px;
      border-bottom: 3px solid transparent;
    }

    .tab-btn i {
      font-size: 1.1rem;
      color: #f7cf86;
    }

    .tab-btn:hover {
      color: #ffffff;
      background-color: #4a3a1a;
      border-bottom-color: #ffcd7e;
    }

    .tab-btn.active {
      background-color: #ffffff;
      color: #b1530f;  /* deep golden amber */
      border-bottom-color: #f5b042;
      font-weight: 700;
      box-shadow: 0 -2px 6px rgba(0,0,0,0.02);
    }

    .tab-btn.active i {
      color: #e69500;
    }

    /* --- CONTENT PANEL (white base, golden accents) --- */
    .content-panel {
      background-color: #ffffff;
      padding: 2rem;
      color: #1f1b10;
    }

    .tab-content {
      display: none;
      animation: fadeSlide 0.28s ease;
    }

    .tab-content.active-content {
      display: block;
    }

    @keyframes fadeSlide {
      from { opacity: 0.4; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* stat cards */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.8rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: #fffdf7;
      border: 1px solid #f7e3b5;
      border-left: 6px solid #f5b042;
      padding: 1.5rem 1.2rem;
      border-radius: 20px;
      box-shadow: 0 8px 18px rgba(245, 176, 66, 0.08);
      transition: 0.2s;
    }
    .stat-card:hover {
      border-left-width: 8px;
      transform: translateY(-2px);
    }
    .stat-card h3 {
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #7a601f;
      margin-bottom: 0.7rem;
      font-weight: 600;
    }
    .stat-card .value {
      font-size: 1.7rem;
      font-weight: 700;
      color: #32270c;
    }
    .stat-card .unit {
      font-size: 1rem;
      color: #c5862b;
      margin-left: 0.25rem;
    }
    .stat-card i {
      margin-right: 0.5rem;
      color: #f5b042;
    }

    /* chart placeholder */
    .chart-placeholder {
      background: linear-gradient(145deg, #fff8ef, #fffae9);
      border: 2px dashed #f5bc5c;
      border-radius: 28px;
      padding: 3rem 2rem;
      text-align: center;
      color: #b27d2e;
      font-weight: 500;
      margin-top: 1.5rem;
    }
    .chart-placeholder i {
      font-size: 3rem;
      color: #f5b042;
      opacity: 0.85;
      margin-bottom: 0.8rem;
    }

    /* table / data rows */
    .data-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #f5e2bc;
      color: #2a2416;
    }
    .data-label i {
      color: #e6a02e;
      width: 2rem;
    }
    .data-value {
      font-weight: 600;
      background: #fff3df;
      padding: 0.3rem 1.2rem;
      border-radius: 60px;
      color: #b1530f;
    }

    /* settings form */
    .settings-form {
      max-width: 560px;
      background: #fffcf3;
      padding: 1.5rem;
      border-radius: 28px;
      border: 1px solid #f7e0b0;
    }
    .settings-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 0;
      border-bottom: 1px solid #f1dfb5;
    }
    .settings-item span {
      font-weight: 600;
      color: #473712;
    }
    .toggle-indicator {
      width: 52px;
      height: 28px;
      background-color: #f5b042;
      border-radius: 60px;
      position: relative;
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
      cursor: default;
    }
    .toggle-indicator::after {
      content: "";
      width: 22px;
      height: 22px;
      background-color: white;
      border-radius: 50%;
      position: absolute;
      top: 3px;
      right: 4px;
    }

    /* project cards, request items, messages */
    .project-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    .project-card {
      background: #fffef7;
      border-radius: 24px;
      border: 1px solid #fee5b5;
      padding: 1.5rem;
      transition: 0.2s;
    }
    .project-card i {
      color: #f5b042;
      font-size: 1.7rem;
      margin-bottom: 0.75rem;
    }
    .project-card h4 {
      font-size: 1.2rem;
      font-weight: 700;
      color: #744f1a;
    }
    .badge-yellow {
      background: #fff0cf;
      color: #bc7c1c;
      padding: 0.25rem 1rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    .request-ticket {
      background: #fffbf2;
      border-left: 5px solid #f5b042;
      padding: 1rem 1.2rem;
      margin-bottom: 1rem;
      border-radius: 20px;
    }
    .message-item {
      background: #ffffff;
      border-radius: 20px;
      padding: 1rem 1.2rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.02);
      border: 1px solid #ffe2a4;
    }
    .footnote {
      margin-top: 2.5rem;
      font-size: 0.8rem;
      color: #ad8a4c;
      border-top: 1px solid #f5e2be;
      padding-top: 1.2rem;
      text-align: right;
    }
    .footnote i {
      color: #f5b042;
    }
    input, textarea {
      background: #fffdf5;
      border: 1px solid #fad48b;
      border-radius: 60px;
      padding: 0.6rem 1rem;
      width: 100%;
    }
    .btn-ghost-yellow {
      background: #f5b04220;
      border: 1px solid #f5b042;
      padding: 0.4rem 1.4rem;
      border-radius: 60px;
      color: #b4641a;
      font-weight: 500;
    }
    @media (max-width: 700px) {
      .navbar { flex-direction: column; align-items: flex-start; gap: 0.8rem; }
      .user-area { width: 100%; justify-content: space-between; }
      .tabs-container { padding: 0.5rem 1rem 0 1rem; }
      .tab-btn { padding: 0.6rem 1rem; font-size: 0.85rem; }
      .content-panel { padding: 1.2rem; }
    }
    hr {
      border-color: #f5dfb3;
    }
  </style>
</head>
<body>
<div class="dashboard">

  <!-- GOLDEN NAVBAR -->
  <div class="navbar">
    <div class="site-name">
      <i class="fas fa-cubes"></i> 
      <span>Projects Hub</span>
    </div>
    <div class="user-area">
      <div class="username">
        <i class="fas fa-user-circle"></i> {{Session::get('email')}}
      </div>
      <div class="logout-btn" title="logout demo">
        <i class="fas fa-sign-out-alt"></i> <a href="supervisorDashboard/logout">Logout</a>
      </div>
    </div>
  </div>

  <!-- TABS - YELLOW THEMED -->
  <div class="tabs-container">
    <button class="tab-btn active" data-tab="dashboard"><i class="fas fa-th-large"></i> Dashboard</button>
     <button class="tab-btn" data-tab="show_pro"><i class="fas fa-code-branch"></i> Projects</button>
    <button class="tab-btn" data-tab="projects"><i class="fas fa-code-branch"></i> Add Projects</button>
    <button class="tab-btn" data-tab="Request"><i class="fas fa-chart-line"></i> Requests</button>
    <button class="tab-btn" data-tab="Idea"><i class="far fa-lightbulb"></i>ideas</button>
    <button class="tab-btn" data-tab="Message"><i class="far fa-envelope"></i> Contact</button>
    <button class="tab-btn" data-tab="settings"><i class="fas fa-sliders-h"></i> Settings</button>
  </div>

  <!-- MAIN CONTENT PANEL (all sections now filled with yellow vivid content) -->
  <div class="content-panel">
    <!-- DASHBOARD -->
    <div id="dashboard" class="tab-content active-content">
      <h2 style="color: #b4570e; margin-bottom: 1.5rem; font-weight: 700; display: flex; gap: 0.6rem;">
        <i class="fas fa-chart-simple" style="color: #f5b042;"></i> Golden overview
      </h2>
      <div class="cards-grid">
        <div class="stat-card"><h3><i class="fas fa-folder-open"></i> Active projects</h3><div class="value">27 <span class="unit">projects</span></div></div>
        <div class="stat-card"><h3><i class="fas fa-users"></i> Team members</h3><div class="value">22 <span class="unit">people</span></div></div>
        <div class="stat-card"><h3><i class="fas fa-clock"></i> Hours this week</h3><div class="value">189 <span class="unit">h</span></div></div>
        <div class="stat-card"><h3><i class="fas fa-check-circle"></i> Completed</h3><div class="value">102 <span class="unit">tasks</span></div></div>
      </div>
      <div class="chart-placeholder">
        <i class="fas fa-chart-pie"></i>
        <div>📊 Distribution by milestone · golden insights</div>
        <div style="font-size:0.85rem; margin-top:0.7rem;">🚀 Sprint velocity +18% this quarter</div>
      </div>
      <div class="footnote"><i class="fas fa-bolt"></i> metrics glow · all systems golden</div>
    </div>

      <!-- SECTION 1: Projects -->
    <div id="show_pro" class="tab-content active-conten">
    
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

    <!-- add PROJECTS (filled) 2 -->
    <div id="projects" class="tab-content">
      <div id="projects" class="max-w-3xl mx-auto border-2 border-black rounded-xl bg-white shadow-2xl overflow-hidden">
        <!-- Header: Yellow gradient background, white text, black border -->
        <div class="bg-gradient-to-r from-yellow-500 to-amber-500 text-white px-6 py-5 text-2xl font-bold border-b-2 border-black flex items-center gap-2">
            <span class="bg-white text-yellow-600 w-8 h-8 rounded-full flex items-center justify-center text-lg border border-black">📋</span>
            Project Registration & Seminar Schedule
        </div>

        <!-- Form body: white background, black text, yellow focus rings -->
        <form method="POST" action="/addproject">
           @csrf
            <div class="p-6 md:p-8 space-y-6 bg-white text-black">
            
            <!-- ================= PROJECT NAME & DESCRIPTION ================= -->
            <div class="space-y-4">
                <div>
                <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span> Project Name <span class="text-yellow-600 text-lg">*</span>
                </label>
                <input type="text" name="project_name" placeholder="e.g., Intelligent Inventory System" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black placeholder-gray-500">
                </div>
                <div>
                <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span> Description <span class="text-yellow-600 text-lg">*</span>
                </label>
                <textarea name="description" rows="3" placeholder="Describe the project goals, technologies, and expected outcomes..." required
                            class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black placeholder-gray-500 resize-vertical"></textarea>
                </div>
            </div>

            <!-- ================= DEPARTMENT + TAKEN (two selects side by side) ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span> Department <span class="text-yellow-600 text-lg">*</span>
                </label>
                <select name="department" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    <option value="" disabled selected>— Select department —</option>
                    <option value="software">1️⃣ Software Engineering</option>
                    <option value="ai">2️⃣ Artificial Intelligence</option>
                    <option value="network">3️⃣ Network & Cybersecurity</option>
                </select>
                </div>
                <div class="space-y-1">
                <label class="text-sm font-semibold uppercase tracking-wide flex items-center gap-1 text-black">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span> Already Taken? <span class="text-yellow-600 text-lg">*</span>
                </label>
                <select name="taken" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    <option value="" disabled selected>— Is project already taken? —</option>
                    <option value="Yes">✅ Yes — already assigned</option>
                    <option value="No">❌ No — available</option>
                </select>
                </div>
            </div>

            <!-- ================= OPTIONAL: STUDENTS SECTION ================= -->
            <div class="border-2 border-black rounded-lg p-5 bg-white/90">
                <div class="flex items-center gap-2 mb-4">
                <span class="bg-yellow-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border border-black">👥</span>
                <span class="font-bold text-lg text-black">Optional — Team Members</span>
                <span class="text-xs text-black/60 italic ml-2">(max 3 students)</span>
                </div>

                <!-- Students number (optional) -->
                <div class="mb-5 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                <label class="text-sm font-semibold flex items-center gap-2 text-black">
                    <span class="text-yellow-600">🔢</span> Number of students (optional)
                </label>
                <input type="number" name="students_number" min="0" max="3" placeholder="e.g., 2"
                        class="mt-1 w-full md:w-64 px-4 py-2 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                <p class="text-xs text-black/60 mt-1">Only for reference — does not validate actual entries below.</p>
                </div>

                <!-- Student 1 (optional) -->
                <div class="border-t border-black/20 pt-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-yellow-700 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">1</span>
                    <span class="font-semibold text-black">Student 1 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                    <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Full name</label>
                    <input type="text" name="student_one_name" placeholder="e.g., Sofia Costa"
                            class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    </div>
                    <div>
                    <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Student ID</label>
                    <input type="text" name="student_one_id" placeholder="e.g., S20240012"
                            class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    </div>
                </div>
                </div>

                <!-- Student 2 (optional) -->
                <div class="border-t border-black/20 pt-4 mt-2">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-yellow-700 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">2</span>
                    <span class="font-semibold text-black">Student 2 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                    <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Full name</label>
                    <input type="text" name="student_two_name" placeholder="e.g., Miguel Oliveira"
                            class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    </div>
                    <div>
                    <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Student ID</label>
                    <input type="text" name="student_two_id" placeholder="e.g., S20244567"
                            class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    </div>
                </div>
                </div>

                <!-- Student 3 (optional) -->
                <div class="border-t border-black/20 pt-4 mt-2">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-white text-yellow-700 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold border-2 border-black">3</span>
                    <span class="font-semibold text-black">Student 3 <span class="text-sm font-normal text-black/60 italic">(optional)</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-2">
                    <div>
                    <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Full name</label>
                    <input type="text" name="student_three_name" placeholder="e.g., Lara Nunes"
                            class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    </div>
                    <div>
                    <label class="text-xs font-medium text-black/70 uppercase tracking-wide">Student ID</label>
                    <input type="text" name="student_three_id" placeholder="e.g., S20247890"
                            class="w-full mt-1 px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                    </div>
                </div>
                </div>
            </div>

            <!-- ================= REQUIRED SEMINAR DATES ================= -->
            <div class="border-2 border-black rounded-lg p-5 bg-white">
                <div class="flex items-center gap-2 mb-4">
                <span class="bg-yellow-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-base border border-black">📅</span>
                <span class="font-bold text-xl text-black">Required Seminar Dates</span>
                <span class="text-red-500 text-sm ml-auto">* All mandatory</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-semibold flex items-center gap-1 text-black">
                    <span class="text-yellow-600">📆</span> Seminar 1 Date <span class="text-yellow-600">*</span>
                    </label>
                    <input type="date" name="seminar1_date" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                </div>
                <div>
                    <label class="text-sm font-semibold flex items-center gap-1 text-black">
                    <span class="text-yellow-600">📆</span> Seminar 2 Date <span class="text-yellow-600">*</span>
                    </label>
                    <input type="date" name="seminar2_date" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                </div>
                <div>
                    <label class="text-sm font-semibold flex items-center gap-1 text-black">
                    <span class="text-yellow-600">📆</span> Seminar 3 Date <span class="text-yellow-600">*</span>
                    </label>
                    <input type="date" name="seminar3_date" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                </div>
                <div>
                    <label class="text-sm font-semibold flex items-center gap-1 text-black">
                    <span class="text-yellow-600">🎓</span> Final Date <span class="text-yellow-600">*</span>
                    </label>
                    <input type="date" name="final_date" required
                        class="w-full px-4 py-3 border-2 border-black rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white text-black">
                </div>
                </div>
                <p class="text-xs text-black/60 mt-3 flex items-center gap-1">
                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full"></span> All seminar dates must be filled. Optional student fields can be left empty.
                </p>
            </div>

            <!-- ================= BUTTONS ================= -->
            <div class="flex justify-between flex-wrap gap-4 pt-4">
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                <span>📬</span> Submit request
                </button>
                <button type="reset" class="bg-gray-200 hover:bg-gray-300 text-black px-6 py-4 text-lg font-bold rounded-lg border-2 border-black shadow-md transition-all flex items-center gap-3">
                <span>🗑️</span> Clear form
                </button>
               
            </div>
            </div>
        </form>
       </div>
    </div>

    <!-- REQUEST SECTION -->
    <div id="Request" class="tab-content">
      <div class="cards-grid">
         <div class="rounded-xl shadow-xl overflow-hidden border border-gray-200/80 bg-white">
            <div class="overflow-x-auto">
              <table class="message-table min-w-[640px] w-full">
                <!-- Table Header: each header cell inherits column color scheme -->
                <thead>
                  <tr class="[&>th]:font-bold [&>th]:tracking-wide">
                    <th scope="col" class="bg-black text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>👤</span> project id
                      </div>
                    </th>
                    <th scope="col" class="bg-gray-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        student count
                      </div>
                    </th>
    
                    <th scope="col" class="bg-yellow-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-blue-400/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>💬</span> students 
                      </div>
                    </th>
                    <!-- Column 3: WHITE background, black text (standard contrast) -->
                    <th scope="col" class="bg-white text-gray-800 px-5 py-4 text-sm uppercase tracking-wider border-b border-gray-300 text-left">
                      <div class="flex items-center gap-2">
                        <span>⏱️</span> Accept
                      </div>
                    </th>
                    
                  </tr>
                </thead>
                
                <!-- Table Body: Dynamic messages with strict column color identity -->
                <tbody class="divide-y divide-gray-200">
                  <!-- Messages -->
                  @foreach ($requests as $req)
                   @if($req->accepted === 0)
                    
                    <tr class="group">
                      <!-- black column cell -->
                      <td class="bg-black text-white px-5 py-4 border-r border-gray-700/30 text-base font-medium">
                        <div class="flex items-center gap-2">
                          <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-sm">AJ</span>
                              {{$req->projectid}}
                      
                        </div>
                      </td>
                        <td class="bg-gray-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                              {{$req->count}}
                      </td>
                      <!-- blue column cell -->
                      <td class="bg-yellow-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                         {{$req->nameone}}:: {{$req->oneid}}
                          <br>
                         @if($req->nametwo)
                         {{$req->nametwo}}:: {{$req->twoid}}
                         @endif
                        <br>
                         @if($req->namethree)
                         {{$req->namethree}}:: {{$req->threeid}}
                         @endif
                      </td>
                      <!-- white column cell -->
                      <td class="bg-white text-gray-800 px-5 py-4 text-base font-mono text-sm">
                        <form action="/acceptrequest" method="POST">
                           @csrf
                           <input type="number" value="{{$req->id}}" name="request"  hidden>
                           <input type="number" value="{{$req->projectid}}" name="project"  hidden>
                           <button type="submit" class="bg-green-200 px-5 hover:bg-green-600">Accept</button>
                        </form>
                      </td>
                    </tr>
                   @endif
                  @endforeach
           
                </tbody>
              </table>
            </div>
          </div>
      </div>
    </div>

    <!-- IDEA / NEW IDEA? -->
    <div id="Idea" class="tab-content">
         <div class="cards-grid">
         <div class="rounded-xl shadow-xl overflow-hidden border border-gray-200/80 bg-white">
            <div class="overflow-x-auto">
              <table class="message-table min-w-[640px] w-full">
                <!-- Table Header: each header cell inherits column color scheme -->
                <thead>
                  <tr class="[&>th]:font-bold [&>th]:tracking-wide">
                    <th scope="col" class="bg-black text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>👤</span> project id
                      </div>
                    </th>
                    <th scope="col" class="bg-gray-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        student count
                      </div>
                    </th>
    
                    <th scope="col" class="bg-yellow-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-blue-400/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>💬</span> students 
                      </div>
                    </th>
                    <!-- Column 3: WHITE background, black text (standard contrast) -->
                    <th scope="col" class="bg-white text-gray-800 px-5 py-4 text-sm uppercase tracking-wider border-b border-gray-300 text-left">
                      <div class="flex items-center gap-2">
                        <span>⏱️</span> Accept
                      </div>
                    </th>
                    
                  </tr>
                </thead>
                
                <!-- Table Body: Dynamic messages with strict column color identity -->
                <tbody class="divide-y divide-gray-200">
                  <!-- Messages -->
                  @foreach ($ideas as $idea)
                   @if($idea->accepted === 0 &&$idea->rejected === 0)
                    @if(Session::get('name')==$idea->supname)
                    <tr class="group">
                      <!-- black column cell -->
                      <td class="bg-black text-white px-5 py-4 border-r border-gray-700/30 text-base font-medium">
                        <div class="flex items-center gap-2">
                          <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-sm">AJ</span>
                              {{$idea->projectname}}
                      
                        </div>
                      </td>
                        <td class="bg-gray-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                              {{$idea->count}}
                      </td>
                      <!-- blue column cell -->
                      <td class="bg-yellow-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                         {{$idea->nameone}}:: {{$idea->oneid}}
                          <br>
                         @if($idea->nametwo)
                         {{$idea->nametwo}}:: {{$idea->twoid}}
                         @endif
                        <br>
                         @if($req->namethree)
                         {{$idea->namethree}}:: {{$idea->threeid}}
                         @endif
                      </td>
                      <!-- white column cell -->
                      <td class="bg-white text-gray-800 px-5 py-4 text-base font-mono text-sm">
                        <form action="/acceptidea" method="POST">
                           @csrf
                           <input type="number" value="{{$idea->id}}" name="idea"  hidden>
                           <button type="submit" class="bg-green-200 px-5 hover:bg-green-600">Accept</button>
                        </form>
                        <br>
                        <form action="/rejectidea" method="POST">
                           @csrf
                           <input type="number" value="{{$idea->id}}" name="idea"  hidden>
                          
                           <button type="submit" class="bg-red-200 px-5 hover:bg-red-600">Reject</button>
                             what is the reason?
                           <input type="text" name="reason" required>
                        </form>
                      </td>
                    </tr>
                    @endif
                   @endif
                  @endforeach
           
                </tbody>
              </table>
            </div>
          </div>
      </div>
    </div>

    <!-- MESSAGE / CONTACT -->
    <div id="Message" class="tab-content">
      <h2 style="color: #b4570e;"><i class="far fa-comment-dots"></i> Team conversations</h2>
      <div class="message-item"><div><i class="fas fa-user-circle text-amber-600"></i> <strong>Marcus Chen</strong> · <span class="text-xs text-stone-500">2h ago</span></div><p class="mt-1">The yellow dashboard looks amazing, love the new energy.</p><div class="flex gap-2 mt-2"><span class="badge-yellow">👍 5</span><span class="badge-yellow">💬 reply</span></div></div>
      <div class="message-item"><div><i class="fas fa-user-circle text-amber-600"></i> <strong>Elena Voss</strong> · <span class="text-xs">yesterday</span></div><p class="mt-1">Request access to the new assets, golden era!</p><div class="flex gap-2 mt-2"><span class="badge-yellow">⭐ 2</span></div></div>
      <div class="bg-amber-50 p-3 rounded-2xl flex gap-2 items-center mt-3"><i class="fas fa-paper-plane text-amber-600"></i><input type="text" placeholder="Write a message..." class="border-0 bg-transparent p-2"><span class="badge-yellow cursor-default">Send</span></div>
    </div>

    <!-- SETTINGS (now fully added with yellow toggles) -->
    <div id="settings" class="tab-content">
      <h2 style="color: #b4570e;"><i class="fas fa-sliders-h"></i> Preference panel</h2>
      <div class="settings-form mt-4">
        <div class="settings-item"><span><i class="fas fa-bell"></i> Push notifications</span><div class="toggle-indicator"></div></div>
        <div class="settings-item"><span><i class="fas fa-palette"></i> Golden theme (active)</span><div class="toggle-indicator" style="background:#f5b042;"></div></div>
        <div class="settings-item"><span><i class="fas fa-chart-simple"></i> Weekly digest</span><div class="toggle-indicator"></div></div>
        <div class="settings-item"><span><i class="fas fa-globe"></i> Language preference</span><span class="text-amber-700 font-medium">English (Golden)</span></div>
      </div>
      <div class="mt-4 p-4 bg-amber-50 rounded-xl"><i class="fas fa-info-circle text-amber-600"></i> Yellow theme mode: fully activated. Bright, warm, and productive.</div>
    </div>
  </div>
  <div style="height: 4px; background: linear-gradient(90deg, #f5b042, #ffde9e, #f5b042);"></div>
</div>

<!-- TAB SWITCHING SCRIPT + ensure settings exists -->
<script>
  (function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    // create content object dynamic with all needed ids
    const contentIds = ['dashboard','show_pro', 'projects', 'Request', 'Idea', 'Message', 'settings'];
    const contents = {};
    contentIds.forEach(id => {
      const el = document.getElementById(id);
      if (el) contents[id] = el;
    });

    function deactivateAll() {
      tabButtons.forEach(btn => btn.classList.remove('active'));
      Object.values(contents).forEach(section => {
        if (section) section.classList.remove('active-content');
      });
    }

    function activateTab(tabId) {
      deactivateAll();
      const targetBtn = Array.from(tabButtons).find(btn => btn.dataset.tab === tabId);
      if (targetBtn) targetBtn.classList.add('active');
      if (contents[tabId]) contents[tabId].classList.add('active-content');
    }

    // attach listeners
    tabButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        const tabId = btn.dataset.tab;
        if (tabId && contents[tabId]) activateTab(tabId);
      });
    });

    // initial check: ensure first active matches dashboard (already active on UI)
    const currentActive = document.querySelector('.tab-btn.active');
    if (currentActive && currentActive.dataset.tab) {
      const activeTabId = currentActive.dataset.tab;
      Object.values(contents).forEach(c => c.classList.remove('active-content'));
      if (contents[activeTabId]) contents[activeTabId].classList.add('active-content');
    } else {
      // fallback
      activateTab('dashboard');
    }

    // Ensure any missing setting content is displayed if needed (already present)
    // extra: adjust icon for projects tab that had inline style removed
    const projectsIcon = document.querySelector('.tab-btn[data-tab="projects"] i');
    if (projectsIcon) projectsIcon.style.color = ''; // let CSS handle
  })();
</script>
</body>
</html>