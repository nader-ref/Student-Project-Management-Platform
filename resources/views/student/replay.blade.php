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
        <i class="fas fa-sign-out-alt"></i> <a href="Logout">Logout</a>
      </div>
    </div>
  </div>

    <!-- DARK BLUE TABS BAR -->
  <div class="tabs-container">
    <button class="tab-btn active" data-tab="replay">
      <i class="fas fa-th-large"></i> replay
    </button>
    <button class="tab-btn" data-tab="Messages">
      <i class="fas fa-home" style="color: #0a0a0a;"></i>
      Supervisors Messages
    </button>
    
  </div>


  <!-- WHITE CONTENT PANEL (dynamic sections) -->
  <div class="content-panel">
    <!-- SECTION 1: DASHBOARD (active by default) -->
    <div id="replay" class="tab-content active-content">
      <h2 style="color: #0a2942; margin-bottom: 1.5rem; font-weight: 600; display: flex; gap: 0.5rem;">
        <i class="fas fa-home" style="color: #0a0a0a;"></i> Your requests
      </h2>
      <div class="cards-grid">
          <div class="rounded-xl shadow-xl overflow-hidden border border-gray-200/80 bg-white">
            <div class="overflow-x-auto">
              <table class="message-table min-w-[640px] w-full">
                <!-- Table Header: each header cell inherits column color scheme -->
                <thead>
                  <tr class="[&>th]:font-bold [&>th]:tracking-wide">
                    <th scope="col" class="bg-black text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>👤</span> Supervisor
                      </div>
                    </th>
    
                    <th scope="col" class="bg-blue-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-blue-400/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>💬</span> Message
                      </div>
                    </th>
                    <!-- Column 3: WHITE background, black text (standard contrast) -->
                    <th scope="col" class="bg-white text-gray-800 px-5 py-4 text-sm uppercase tracking-wider border-b border-gray-300 text-left">
                      <div class="flex items-center gap-2">
                        <span>⏱️</span> Timestamp
                      </div>
                    </th>
                  </tr>
                </thead>
                
                <!-- Table Body: Dynamic messages with strict column color identity -->
                <tbody class="divide-y divide-gray-200">
                  <!-- Messages -->
                  @foreach ($Messages as $message)
                      
                  @if($message->email == Session::get('email'))
                    <tr class="group">
                      <!-- black column cell -->
                      <td class="bg-black text-white px-5 py-4 border-r border-gray-700/30 text-base font-medium">
                        <div class="flex items-center gap-2">
                          <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-sm">AJ</span>
                          @if($message->Replay)
                              {{$message->Replay}}
                          @else
                             not replied yet 
                          @endif
                        </div>
                      </td>
                      <!-- blue column cell -->
                      <td class="bg-blue-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                         {{$message->Message}}
                      </td>
                      <!-- white column cell -->
                      <td class="bg-white text-gray-800 px-5 py-4 text-base font-mono text-sm">
                        {{$message->created_at}}
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

     <!-- SECTION 1: DASHBOARD (active by default) -->
    <div id="Messages" class="tab-content active-content">
      <h2 style="color: #0a2942; margin-bottom: 1.5rem; font-weight: 600; display: flex; gap: 0.5rem;">
        <i class="fas fa-home" style="color: #0a0a0a;"></i> Your requests
      </h2>
      <div class="cards-grid">
         <div class="rounded-xl shadow-xl overflow-hidden border border-gray-200/80 bg-white">
            <div class="overflow-x-auto">
              <table class="message-table min-w-[640px] w-full">
                <!-- Table Header: each header cell inherits column color scheme -->
                <thead>
                  <tr class="[&>th]:font-bold [&>th]:tracking-wide">
                    <th scope="col" class="bg-black text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>👤</span> Supervisor
                      </div>
                    </th>
                    <th scope="col" class="bg-gray-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-gray-700/40 text-left">
                      <div class="flex items-center gap-2">
                        peoject
                      </div>
                    </th>
    
                    <th scope="col" class="bg-blue-600 text-white px-5 py-4 text-sm uppercase tracking-wider border-b border-r border-blue-400/40 text-left">
                      <div class="flex items-center gap-2">
                        <span>💬</span> Message
                      </div>
                    </th>
                    <!-- Column 3: WHITE background, black text (standard contrast) -->
                    <th scope="col" class="bg-white text-gray-800 px-5 py-4 text-sm uppercase tracking-wider border-b border-gray-300 text-left">
                      <div class="flex items-center gap-2">
                        <span>⏱️</span> Timestamp
                      </div>
                    </th>
                  </tr>
                </thead>
                
                <!-- Table Body: Dynamic messages with strict column color identity -->
                <tbody class="divide-y divide-gray-200">
                  <!-- Messages -->
                  @foreach ($supmessages as $message)
                    <tr class="group">
                      <!-- black column cell -->
                      <td class="bg-black text-white px-5 py-4 border-r border-gray-700/30 text-base font-medium">
                        <div class="flex items-center gap-2">
                          <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-sm">AJ</span>
                              {{$message->supname}}
                              :{{$message->subject}}
                        </div>
                      </td>
                        <td class="bg-gray-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                         {{$message->projectname}}
                      </td>
                      <!-- blue column cell -->
                      <td class="bg-blue-600 text-white px-5 py-4 border-r border-blue-400/30 text-base leading-relaxed">
                         {{$message->Message}}
                      </td>
                      <!-- white column cell -->
                      <td class="bg-white text-gray-800 px-5 py-4 text-base font-mono text-sm">
                        {{$message->created_at}}
                      </td>
                    </tr>
                  @endforeach
           
                </tbody>
              </table>
            </div>
          </div>
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
      replay: document.getElementById('replay'),
      Messages: document.getElementById('Messages'),
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