window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.app = (() => {
  const routes = ['dashboard', 'tasks', 'subjects', 'planner', 'focus', 'goals', 'analytics', 'achievements', 'settings'];
  const state = { currentView: 'dashboard', charts: {}, focusInterval: null };

  function initialize() {
    const storage = window.StudyFlow.storage;
    if (!storage.loadData(storage.KEYS.user)) {
      storage.saveData(storage.KEYS.user, window.StudyFlow.state.defaultUser);
    }
    if (!storage.loadData(storage.KEYS.tasks)) {
      storage.saveData(storage.KEYS.tasks, []);
    }
    if (!storage.loadData(storage.KEYS.subjects)) {
      storage.saveData(storage.KEYS.subjects, window.StudyFlow.subjects.defaultSubjects);
    }
    if (!storage.loadData(storage.KEYS.sessions)) {
      storage.saveData(storage.KEYS.sessions, []);
    }
    if (!storage.loadData(storage.KEYS.goals)) {
      storage.saveData(storage.KEYS.goals, []);
    }
    if (!storage.loadData(storage.KEYS.settings)) {
      storage.saveData(storage.KEYS.settings, window.StudyFlow.state.defaultSettings);
    }

    applyTheme();
    window.StudyFlow.state.initializeState();
    window.StudyFlow.achievements.refreshAchievements();
    wireGlobalEvents();

    if (!window.StudyFlow.state.getState().onboarding.complete) {
      openOnboarding();
    } else {
      navigateTo('dashboard');
    }

    renderSidebar();
    bindKeyboardShortcuts();
    renderNotifications();
    setInterval(() => {
      if (window.StudyFlow.focus.getState().isRunning) {
        window.StudyFlow.focus.tick();
        renderFocusTimer();
      }
    }, 1000);
  }

  function wireGlobalEvents() {
    document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
    document.getElementById('notification-toggle').addEventListener('click', () => {
      const panel = document.getElementById('notification-panel');
      panel.classList.toggle('hidden');
    });
    document.getElementById('global-search-trigger').addEventListener('click', openSearchModal);
    document.getElementById('demo-data-btn').addEventListener('click', loadDemoData);
    document.getElementById('mobile-overlay').addEventListener('click', closeMobileSidebar);
    document.getElementById('close-sidebar').addEventListener('click', closeMobileSidebar);
    document.getElementById('open-sidebar').addEventListener('click', openMobileSidebar);
    document.getElementById('settings-nav').addEventListener('click', () => navigateTo('settings'));

    window.addEventListener('keydown', (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        openSearchModal();
      }
      if (event.key === 'Escape') {
        closeModal();
        closeMobileSidebar();
      }
    });
  }

  function bindKeyboardShortcuts() {
    document.addEventListener('keydown', (event) => {
      if (event.target.matches('input, textarea, select')) return;
      const map = {
        d: 'dashboard',
        t: 'tasks',
        s: 'subjects',
        p: 'planner',
        f: 'focus',
        g: 'goals',
        a: 'analytics',
        h: 'achievements'
      };
      if (map[event.key]) navigateTo(map[event.key]);
    });
  }

  function applyTheme() {
    const settings = window.StudyFlow.state.getState().settings;
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const desired = settings.theme === 'system' ? (prefersDark ? 'dark' : 'light') : settings.theme;
    document.documentElement.classList.toggle('dark', desired === 'dark');
    const icon = document.querySelector('#theme-toggle i');
    if (icon) {
      icon.setAttribute('data-lucide', desired === 'dark' ? 'moon' : 'sun-medium');
      lucide.createIcons();
    }
  }

  function toggleTheme() {
    const stateSettings = window.StudyFlow.state.getState().settings;
    const nextTheme = stateSettings.theme === 'dark' ? 'light' : 'dark';
    window.StudyFlow.state.setSettings({ theme: nextTheme });
    applyTheme();
    window.StudyFlow.notifications.addNotification('Theme updated successfully.', 'success');
    renderToast('Theme updated successfully.');
  }

  function renderSidebar() {
    const nav = document.getElementById('nav-menu');
    const items = [
      { id: 'dashboard', label: 'Dashboard', icon: 'layout-dashboard' },
      { id: 'tasks', label: 'Tasks', icon: 'list-checks' },
      { id: 'subjects', label: 'Subjects', icon: 'book-open' },
      { id: 'planner', label: 'Planner', icon: 'calendar-days' },
      { id: 'focus', label: 'Focus', icon: 'timer' },
      { id: 'goals', label: 'Goals', icon: 'target' },
      { id: 'analytics', label: 'Analytics', icon: 'bar-chart-3' },
      { id: 'achievements', label: 'Achievements', icon: 'trophy' }
    ];

    nav.innerHTML = items.map((item) => `
      <button class="nav-item flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 ${state.currentView === item.id ? 'active' : ''}" data-nav="${item.id}">
        <i data-lucide="${item.icon}" class="h-4 w-4"></i>
        <span>${item.label}</span>
      </button>
    `).join('');

    nav.querySelectorAll('[data-nav]').forEach((button) => {
      button.addEventListener('click', () => {
        navigateTo(button.dataset.nav);
        closeMobileSidebar();
      });
    });

    const user = window.StudyFlow.state.getState().user;
    document.getElementById('sidebar-user-name').textContent = user.name || 'Student';
    document.getElementById('sidebar-profile-avatar').textContent = (user.name || 'S').slice(0, 2).toUpperCase();

    lucide.createIcons();
  }

  function navigateTo(view) {
    if (!routes.includes(view)) return;
    state.currentView = view;
    renderSidebar();
    document.getElementById('page-title').textContent = formatViewTitle(view);
    renderView();
  }

  function formatViewTitle(view) {
    const map = {
      dashboard: 'Dashboard',
      tasks: 'Tasks',
      subjects: 'Subjects',
      planner: 'Planner',
      focus: 'Focus Mode',
      goals: 'Goals',
      analytics: 'Analytics',
      achievements: 'Achievements',
      settings: 'Settings'
    };
    return map[view] || 'Dashboard';
  }

  function renderView() {
    const container = document.getElementById('app-content');
    switch (state.currentView) {
      case 'dashboard':
        container.innerHTML = renderDashboard();
        bindDashboardEvents();
        break;
      case 'tasks':
        container.innerHTML = renderTasksView();
        bindTasksEvents();
        break;
      case 'subjects':
        container.innerHTML = renderSubjectsView();
        bindSubjectsEvents();
        break;
      case 'planner':
        container.innerHTML = renderPlannerView();
        bindPlannerEvents();
        break;
      case 'focus':
        container.innerHTML = renderFocusView();
        bindFocusEvents();
        break;
      case 'goals':
        container.innerHTML = renderGoalsView();
        bindGoalsEvents();
        break;
      case 'analytics':
        container.innerHTML = renderAnalyticsView();
        bindAnalyticsEvents();
        break;
      case 'achievements':
        container.innerHTML = renderAchievementsView();
        bindAchievementsEvents();
        break;
      case 'settings':
        container.innerHTML = renderSettingsView();
        bindSettingsEvents();
        break;
      default:
        container.innerHTML = renderDashboard();
    }
    if (window.lucide) lucide.createIcons();
    renderNotifications();
  }

  function renderDashboard() {
    const tasks = window.StudyFlow.tasks.getTasks();
    const user = window.StudyFlow.state.getState().user;
    const recommended = window.StudyFlow.priorityEngine.getRecommendedTask(tasks);
    const today = new Date();
    const todaysTasks = tasks.filter((task) => task.deadline && task.deadline.slice(0, 10) === today.toISOString().slice(0, 10));
    const completedToday = tasks.filter((task) => task.status === 'Completed' && task.completedAt && task.completedAt.slice(0, 10) === today.toISOString().slice(0, 10)).length;
    const totalMinutes = window.StudyFlow.planner.getSessions().reduce((sum, session) => sum + Number(session.duration || 0), 0);
    const streak = window.StudyFlow.analytics.calculateStreaks().current;

    const taskList = tasks.filter((task) => task.status !== 'Completed').slice(0, 4);
    const weeklyData = window.StudyFlow.analytics.getWeeklyStudyMinutes();

    return `
      <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Good ${getGreeting()}, ${user.name || 'Student'} 👋</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight">Let's turn today's plans into progress.</h2>
          </div>
          <button data-action="create-task" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">+ Create Task</button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          ${metricCard('Tasks Today', todaysTasks.length || 0, 'View plan', 'check-square')}
          ${metricCard('Completed Today', completedToday, 'Today', 'circle-check')}
          ${metricCard('Study Time', formatTime(totalMinutes), 'This week', 'clock-3')}
          ${metricCard('Current Streak', `${streak} days`, 'Keep it going', 'flame')}
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.6fr,1fr]">
          <div class="card p-5">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Today's Focus</p>
                <h3 class="mt-2 text-xl font-bold">Your next best task</h3>
              </div>
              ${recommended ? `<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">Priority ${recommended.score}</span>` : ''}
            </div>
            ${recommended ? `
              <div class="mt-5 rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-blue-50 p-4 dark:border-violet-900/60 dark:from-violet-950/30 dark:to-slate-900">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <h4 class="text-xl font-bold">${escapeHtml(recommended.task.title)}</h4>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">${escapeHtml(recommended.task.description || 'Focused study block')}</p>
                  </div>
                  <button data-start-task="${recommended.task.id}" class="rounded-xl bg-slate-900 px-3.5 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">Start Focus</button>
                </div>
                <div class="mt-5 flex flex-wrap gap-2 text-xs">
                  <span class="rounded-full ${priorityClass(recommended.task.priority)} px-2.5 py-1">${recommended.task.priority}</span>
                  <span class="rounded-full bg-slate-200 px-2.5 py-1 text-slate-700 dark:bg-slate-700 dark:text-slate-200">${formatMinutes(recommended.task.estimatedMinutes || 45)}</span>
                  <span class="rounded-full bg-slate-200 px-2.5 py-1 text-slate-700 dark:bg-slate-700 dark:text-slate-200">Progress ${Number(recommended.task.progress || 0)}%</span>
                </div>
                <div class="mt-4">
                  <div class="mb-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>Priority Score</span>
                    <span>${recommended.score}/100</span>
                  </div>
                  <div class="progress-bar h-2.5 bg-slate-200 dark:bg-slate-700">
                    <div class="progress-fill h-full rounded-full bg-gradient-to-r from-violet-500 to-blue-500" style="width:${recommended.score}%"></div>
                  </div>
                </div>
                <div class="mt-4 rounded-xl border border-violet-200 bg-white/60 p-3 dark:border-violet-900 dark:bg-slate-900/30">
                  <div class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Why this task?</div>
                  <ul class="space-y-1 text-sm text-slate-600 dark:text-slate-300">
                    ${recommended.reasons.map((reason) => `<li>• ${escapeHtml(reason)}</li>`).join('')}
                  </ul>
                </div>
              </div>
            ` : `<div class="empty-state mt-5 p-8 text-center text-sm text-slate-500 dark:text-slate-400"><p>No active tasks to recommend.</p></div>`}
          </div>

          <div class="card p-5">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold">Upcoming Deadlines</h3>
              <button data-action="view-tasks" class="text-sm font-medium text-accent-600 dark:text-accent-400">View all</button>
            </div>
            <div class="mt-4 space-y-3">
              ${renderDeadlineList(tasks)}
            </div>
          </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.5fr,1fr]">
          <div class="card p-5">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold">Today's Tasks</h3>
              <span class="text-sm text-slate-500 dark:text-slate-400">${taskList.length} active</span>
            </div>
            <div class="mt-4 space-y-3">
              ${taskList.length ? taskList.map((task) => taskRow(task)).join('') : emptyState('No tasks yet.', 'Add your first task and let StudyFlow help you decide what to work on.', 'create-task')}
            </div>
          </div>

          <div class="card p-5">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold">Weekly Progress</h3>
              <span class="text-sm text-slate-500 dark:text-slate-400">Study activity</span>
            </div>
            <div class="mt-4 h-64">
              <canvas id="weekly-progress-chart"></canvas>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function renderTasksView() {
    const tasks = window.StudyFlow.tasks.getTasks();
    const subjects = window.StudyFlow.subjects.getSubjects();
    const searchTerm = '';

    return `
      <div class="space-y-5">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Task management</p>
            <h2 class="text-2xl font-bold">Your tasks</h2>
          </div>
          <button data-action="create-task" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">+ New Task</button>
        </div>

        <div class="card p-4">
          <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Search</span>
              <input id="task-search" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800" placeholder="Search tasks" />
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Sort</span>
              <select id="task-sort" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="deadline">Deadline</option>
                <option value="priority">Priority</option>
                <option value="created">Newest</option>
              </select>
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Priority</span>
              <select id="task-priority-filter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="all">All</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Urgent">Urgent</option>
              </select>
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Subject</span>
              <select id="task-subject-filter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="all">All</option>
                ${subjects.map((subject) => `<option value="${subject.id}">${escapeHtml(subject.name)}</option>`).join('')}
              </select>
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Status</span>
              <select id="task-status-filter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="all">All</option>
                <option value="Todo">Todo</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>
            </label>
          </div>
        </div>

        <div id="task-list" class="space-y-3">
          ${renderTaskRecords(tasks)}
        </div>
      </div>
    `;
  }

  function renderSubjectsView() {
    const subjects = window.StudyFlow.subjects.getSubjects();

    return `
      <div class="space-y-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Study subjects</p>
            <h2 class="text-2xl font-bold">Subjects</h2>
          </div>
          <button data-action="create-subject" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">+ New Subject</button>
        </div>

        <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
          ${subjects.length ? subjects.map((subject) => subjectCard(subject)).join('') : emptyState('No subjects yet.', 'Add your first subject to organize your study plan.', 'create-subject')}
        </div>
      </div>
    `;
  }

  function renderPlannerView() {
    const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const sessions = window.StudyFlow.planner.getSessions();
    return `
      <div class="space-y-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Weekly plan</p>
            <h2 class="text-2xl font-bold">Study planner</h2>
          </div>
          <button data-action="create-session" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">+ Add Session</button>
        </div>

        <div class="grid gap-4 xl:grid-cols-7">
          ${weekDays.map((day) => plannerDayColumn(day, sessions.filter((session) => session.day === day))).join('')}
        </div>
      </div>
    `;
  }

  function renderFocusView() {
    const tasks = window.StudyFlow.tasks.getTasks().filter((task) => task.status !== 'Completed');
    const recommended = window.StudyFlow.priorityEngine.getRecommendedTask(tasks) || { task: null };
    const activeTask = recommended.task || tasks[0] || null;
    const focusState = window.StudyFlow.focus.getState();
    const totalSeconds = focusState.timerSeconds || (activeTask ? Number(activeTask.estimatedMinutes || 45) * 60 : 0);
    const progress = activeTask ? Math.min(100, Number(activeTask.progress || 0)) : 0;

    return `
      <div class="space-y-5">
        <div class="card overflow-hidden p-6">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <p class="text-sm uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Focus mode</p>
              <h2 class="mt-2 text-3xl font-bold">${activeTask ? escapeHtml(activeTask.title) : 'No active task'}</h2>
            </div>
            <div class="flex gap-2">
              <button data-focus-action="start" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">${focusState.isRunning ? 'Restart' : 'Start'}</button>
              <button data-focus-action="pause" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Pause</button>
              <button data-focus-action="resume" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Resume</button>
            </div>
          </div>

          <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr,0.8fr]">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-900">
              <div class="text-center">
                <div id="focus-timer" class="text-5xl font-black tracking-tight">${formatFocusTime(totalSeconds)}</div>
                <p class="mt-3 text-sm uppercase tracking-[0.25em] text-slate-500 dark:text-slate-400">Current objective</p>
              </div>
              <div class="mt-6">
                <div class="mb-2 flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
                  <span>Progress</span>
                  <span>${progress}%</span>
                </div>
                <div class="progress-bar h-3 bg-slate-200 dark:bg-slate-700">
                  <div class="progress-fill h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" style="width:${progress}%"></div>
                </div>
              </div>
            </div>

            <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
              <div class="text-sm text-slate-500 dark:text-slate-400">Session controls</div>
              <button data-focus-action="reset" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Reset</button>
              <button data-focus-action="finish" class="w-full rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-600">Finish Session</button>
              <button data-focus-action="toggle-sound" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Sound: On</button>
              <button data-focus-action="fullscreen" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Fullscreen</button>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function renderGoalsView() {
    const goals = window.StudyFlow.goals.getGoals();
    return `
      <div class="space-y-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Study goals</p>
            <h2 class="text-2xl font-bold">Goals</h2>
          </div>
          <button data-action="create-goal" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">+ New Goal</button>
        </div>

        <div class="space-y-4">
          ${goals.length ? goals.map((goal) => goalCard(goal)).join('') : emptyState('No goals yet.', 'Create a goal to track your learning milestones.', 'create-goal')}
        </div>
      </div>
    `;
  }

  function renderAnalyticsView() {
    const weekly = window.StudyFlow.analytics.getWeeklyStudyMinutes();
    const completion = window.StudyFlow.analytics.calculateCompletionRate();
    const streaks = window.StudyFlow.analytics.calculateStreaks();
    const insights = window.StudyFlow.analytics.getInsights();

    return `
      <div class="space-y-6">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">Performance overview</p>
          <h2 class="text-2xl font-bold">Analytics</h2>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          ${metricCard('Total Study Time', `${Math.round((insights.totalMinutes || 0) / 60)}h`, 'This week', 'timer-reset')}
          ${metricCard('Tasks Completed', window.StudyFlow.tasks.getTasks().filter((task) => task.status === 'Completed').length, 'All time', 'check-circle')}
          ${metricCard('Completion Rate', `${completion}%`, 'This month', 'trend-up')}
          ${metricCard('Current Streak', `${streaks.current} days`, 'Best ${streaks.longest}', 'flame')}
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
          <div class="card p-5">
            <h3 class="text-lg font-bold">Study time by day</h3>
            <div class="mt-4 h-72">
              <canvas id="study-time-chart"></canvas>
            </div>
          </div>
          <div class="card p-5">
            <h3 class="text-lg font-bold">Tasks completed by day</h3>
            <div class="mt-4 h-72">
              <canvas id="tasks-day-chart"></canvas>
            </div>
          </div>
        </div>

        <div class="card p-5">
          <h3 class="text-lg font-bold">Insights</h3>
          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
              <div class="text-xs uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">Most productive day</div>
              <div class="mt-2 text-xl font-bold">${escapeHtml(insights.mostProductiveDay)}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
              <div class="text-xs uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">Most studied subject</div>
              <div class="mt-2 text-xl font-bold">${escapeHtml(insights.mostStudiedSubject)}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
              <div class="text-xs uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">Summary</div>
              <div class="mt-2 text-sm font-medium text-slate-600 dark:text-slate-300">${escapeHtml(insights.insightText)}</div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function renderAchievementsView() {
    const achievements = window.StudyFlow.achievements.refreshAchievements();
    return `
      <div class="space-y-5">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">Milestones & wins</p>
          <h2 class="text-2xl font-bold">Achievements</h2>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          ${achievements.map((achievement) => achievementCard(achievement)).join('')}
        </div>
      </div>
    `;
  }

  function renderSettingsView() {
    const stateSettings = window.StudyFlow.state.getState().settings;
    const user = window.StudyFlow.state.getState().user;

    return `
      <div class="space-y-6">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">Personalize your setup</p>
          <h2 class="text-2xl font-bold">Settings</h2>
        </div>

        <div class="card p-5">
          <h3 class="text-lg font-bold">Profile</h3>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Name</span>
              <input id="settings-name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" value="${escapeHtml(user.name || '')}" />
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Learning focus</span>
              <input id="settings-focus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" value="${escapeHtml(user.learningFocus || '')}" />
            </label>
          </div>
        </div>

        <div class="card p-5">
          <h3 class="text-lg font-bold">Appearance</h3>
          <div class="mt-4 flex flex-wrap gap-3">
            <button data-theme-option="light" class="rounded-xl border px-3 py-2 text-sm ${stateSettings.theme === 'light' ? 'border-accent-500 bg-accent-50 text-accent-700' : 'border-slate-200 dark:border-slate-700'}">Light mode</button>
            <button data-theme-option="dark" class="rounded-xl border px-3 py-2 text-sm ${stateSettings.theme === 'dark' ? 'border-accent-500 bg-accent-50 text-accent-700' : 'border-slate-200 dark:border-slate-700'}">Dark mode</button>
            <button data-theme-option="system" class="rounded-xl border px-3 py-2 text-sm ${stateSettings.theme === 'system' ? 'border-accent-500 bg-accent-50 text-accent-700' : 'border-slate-200 dark:border-slate-700'}">System</button>
          </div>
        </div>

        <div class="card p-5">
          <h3 class="text-lg font-bold">Preferences</h3>
          <div class="mt-4 grid gap-4 md:grid-cols-3">
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Default study duration</span>
              <input id="settings-duration" type="number" min="15" step="15" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" value="${stateSettings.defaultStudyDuration || 45}" />
            </label>
            <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
              <input id="settings-notifications" type="checkbox" ${stateSettings.notificationsEnabled ? 'checked' : ''} />
              <span class="text-sm">Notifications</span>
            </label>
            <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
              <input id="settings-sound" type="checkbox" ${stateSettings.soundEnabled ? 'checked' : ''} />
              <span class="text-sm">Sound</span>
            </label>
          </div>
        </div>

        <div class="card p-5">
          <h3 class="text-lg font-bold">Data</h3>
          <div class="mt-4 flex flex-wrap gap-3">
            <button id="export-data" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Export Data</button>
            <label class="cursor-pointer rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
              <input id="import-data" type="file" accept="application/json" class="hidden" />
              Import Data
            </label>
            <button id="reset-data" class="rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-600">Reset Data</button>
          </div>
        </div>
      </div>
    `;
  }

  function renderNotifications() {
    window.StudyFlow.notifications.renderPanel();
    const count = window.StudyFlow.notifications.getNotifications().length;
    const dot = document.getElementById('notification-dot');
    if (dot) dot.classList.toggle('hidden', count === 0);
  }

  function renderToast(message) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast rounded-xl border border-emerald-200 bg-white p-3 text-sm font-medium text-emerald-700 shadow-soft dark:border-emerald-900 dark:bg-slate-900 dark:text-emerald-300';
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 2200);
  }

  function bindDashboardEvents() {
    document.querySelectorAll('[data-start-task]').forEach((button) => {
      button.addEventListener('click', () => {
        const taskId = button.dataset.startTask;
        const selectedTask = window.StudyFlow.tasks.getTaskById(taskId);
        if (!selectedTask) return;
        state.currentView = 'focus';
        window.StudyFlow.focus.start(taskId, Number(selectedTask.estimatedMinutes || 45));
        renderView();
        navigateTo('focus');
      });
    });

    document.querySelectorAll('[data-action="create-task"]').forEach((button) => {
      button.addEventListener('click', openTaskModal);
    });
    document.querySelectorAll('[data-action="view-tasks"]').forEach((button) => {
      button.addEventListener('click', () => navigateTo('tasks'));
    });

    renderChart('weekly-progress-chart', 'bar', {
      labels: window.StudyFlow.analytics.getWeeklyStudyMinutes().map((item) => item.label),
      datasets: [{
        label: 'Study time',
        data: window.StudyFlow.analytics.getWeeklyStudyMinutes().map((item) => item.minutes),
        backgroundColor: ['#4f8cff', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#25c2a0', '#2563eb'],
        borderRadius: 8
      }]
    });
  }

  function bindTasksEvents() {
    document.querySelectorAll('[data-action="create-task"]').forEach((button) => button.addEventListener('click', openTaskModal));
    document.querySelectorAll('[data-edit-task]').forEach((button) => button.addEventListener('click', () => openTaskModal(button.dataset.editTask)));
    document.querySelectorAll('[data-delete-task]').forEach((button) => button.addEventListener('click', () => deleteTask(button.dataset.deleteTask)));
    document.querySelectorAll('[data-toggle-task]').forEach((button) => button.addEventListener('click', () => toggleTask(button.dataset.toggleTask)));

    const search = document.getElementById('task-search');
    const sort = document.getElementById('task-sort');
    const priorityFilter = document.getElementById('task-priority-filter');
    const subjectFilter = document.getElementById('task-subject-filter');
    const statusFilter = document.getElementById('task-status-filter');

    [search, sort, priorityFilter, subjectFilter, statusFilter].forEach((el) => {
      if (!el) return;
      el.addEventListener('input', applyTaskFilters);
      el.addEventListener('change', applyTaskFilters);
    });

    if (search) search.focus();
  }

  function bindSubjectsEvents() {
    document.querySelectorAll('[data-action="create-subject"]').forEach((button) => button.addEventListener('click', openSubjectModal));
    document.querySelectorAll('[data-edit-subject]').forEach((button) => button.addEventListener('click', () => openSubjectModal(button.dataset.editSubject)));
    document.querySelectorAll('[data-delete-subject]').forEach((button) => button.addEventListener('click', () => deleteSubject(button.dataset.deleteSubject)));
  }

  function bindPlannerEvents() {
    document.querySelectorAll('[data-action="create-session"]').forEach((button) => button.addEventListener('click', openSessionModal));
    document.querySelectorAll('[data-edit-session]').forEach((button) => button.addEventListener('click', () => openSessionModal(button.dataset.editSession)));
    document.querySelectorAll('[data-delete-session]').forEach((button) => button.addEventListener('click', () => deleteSession(button.dataset.deleteSession)));
  }

  function bindFocusEvents() {
    document.querySelectorAll('[data-focus-action]').forEach((button) => {
      button.addEventListener('click', () => handleFocusAction(button.dataset.focusAction));
    });
    renderFocusTimer();
  }

  function bindGoalsEvents() {
    document.querySelectorAll('[data-action="create-goal"]').forEach((button) => button.addEventListener('click', openGoalModal));
    document.querySelectorAll('[data-edit-goal]').forEach((button) => button.addEventListener('click', () => openGoalModal(button.dataset.editGoal)));
    document.querySelectorAll('[data-delete-goal]').forEach((button) => button.addEventListener('click', () => deleteGoal(button.dataset.deleteGoal)));
  }

  function bindAnalyticsEvents() {
    renderChart('study-time-chart', 'bar', {
      labels: window.StudyFlow.analytics.getWeeklyStudyMinutes().map((item) => item.label),
      datasets: [{
        label: 'Minutes studied',
        data: window.StudyFlow.analytics.getWeeklyStudyMinutes().map((item) => item.minutes),
        backgroundColor: '#4f8cff',
        borderRadius: 8
      }]
    });
    renderChart('tasks-day-chart', 'line', {
      labels: window.StudyFlow.analytics.getTaskCompletionByDay().map((item) => item.label),
      datasets: [{
        label: 'Tasks completed',
        data: window.StudyFlow.analytics.getTaskCompletionByDay().map((item) => item.count),
        borderColor: '#8b5cf6',
        pointBackgroundColor: '#8b5cf6',
        fill: false,
        tension: 0.35
      }]
    });
  }

  function bindAchievementsEvents() {
    document.querySelectorAll('[data-achievement-detail]').forEach((button) => {
      button.addEventListener('click', () => {
        const id = button.dataset.achievementDetail;
        const item = window.StudyFlow.achievements.getAchievements().find((ach) => ach.id === id);
        if (!item) return;
        renderToast(item.unlocked ? `${item.title} unlocked` : `${item.title} locked`);
      });
    });
  }

  function bindSettingsEvents() {
    document.querySelectorAll('[data-theme-option]').forEach((button) => {
      button.addEventListener('click', () => {
        const theme = button.dataset.themeOption;
        window.StudyFlow.state.setSettings({ theme });
        applyTheme();
        renderSettingsView();
        bindSettingsEvents();
      });
    });

    const saveButton = document.createElement('button');
    saveButton.id = 'save-settings';
    saveButton.className = 'mt-4 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-accent-500';
    saveButton.textContent = 'Save Settings';
    saveButton.addEventListener('click', saveSettings);
    const card = document.querySelector('.card:last-of-type');
    if (card) card.appendChild(saveButton);

    document.getElementById('export-data')?.addEventListener('click', exportData);
    document.getElementById('import-data')?.addEventListener('change', importData);
    document.getElementById('reset-data')?.addEventListener('click', resetData);
  }

  function saveSettings() {
    const newName = document.getElementById('settings-name')?.value.trim() || 'Student';
    const learningFocus = document.getElementById('settings-focus')?.value.trim() || 'Study';
    const duration = Number(document.getElementById('settings-duration')?.value || 45);
    const notifications = document.getElementById('settings-notifications')?.checked ?? true;
    const sound = document.getElementById('settings-sound')?.checked ?? true;

    window.StudyFlow.state.setUser({ name: newName, learningFocus, avatar: newName.slice(0, 2).toUpperCase() });
    window.StudyFlow.state.setSettings({ defaultStudyDuration: duration, notificationsEnabled: notifications, soundEnabled: sound });
    renderSidebar();
    renderToast('Settings updated.');
    window.StudyFlow.notifications.addNotification('Settings updated.', 'success');
  }

  function exportData() {
    const exportDataObject = {
      user: window.StudyFlow.state.getState().user,
      settings: window.StudyFlow.state.getState().settings,
      tasks: window.StudyFlow.tasks.getTasks(),
      subjects: window.StudyFlow.subjects.getSubjects(),
      sessions: window.StudyFlow.planner.getSessions(),
      goals: window.StudyFlow.goals.getGoals(),
      notifications: window.StudyFlow.notifications.getNotifications(),
      exportedAt: new Date().toISOString()
    };
    const blob = new Blob([JSON.stringify(exportDataObject, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `studyflow-backup-${new Date().toISOString().slice(0, 10)}.json`;
    link.click();
    URL.revokeObjectURL(url);
    renderToast('StudyFlow data exported.');
  }

  function importData(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
      try {
        const parsed = JSON.parse(reader.result);
        if (!parsed || typeof parsed !== 'object') throw new Error('Invalid backup');
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.user, parsed.user || window.StudyFlow.state.defaultUser);
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.settings, parsed.settings || window.StudyFlow.state.defaultSettings);
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.tasks, parsed.tasks || []);
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.subjects, parsed.subjects || window.StudyFlow.subjects.defaultSubjects);
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.sessions, parsed.sessions || []);
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.goals, parsed.goals || []);
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.notifications, parsed.notifications || []);
        renderToast('Data imported successfully.');
        navigateTo('dashboard');
      } catch (error) {
        renderToast('Invalid JSON backup.');
      }
    };
    reader.readAsText(file);
  }

  function resetData() {
    if (!window.confirm('Are you sure you want to reset all StudyFlow data?')) return;
    Object.values(window.StudyFlow.storage.KEYS).forEach((key) => window.StudyFlow.storage.deleteData(key));
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.settings, window.StudyFlow.state.defaultSettings);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.user, window.StudyFlow.state.defaultUser);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.subjects, window.StudyFlow.subjects.defaultSubjects);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.tasks, []);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.sessions, []);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.goals, []);
    renderToast('Data reset.');
    navigateTo('dashboard');
  }

  function applyTaskFilters() {
    const search = document.getElementById('task-search')?.value || '';
    const sort = document.getElementById('task-sort')?.value || 'deadline';
    const priority = document.getElementById('task-priority-filter')?.value || 'all';
    const subject = document.getElementById('task-subject-filter')?.value || 'all';
    const status = document.getElementById('task-status-filter')?.value || 'all';

    let tasks = window.StudyFlow.tasks.getTasks();
    const q = search.toLowerCase();
    tasks = tasks.filter((task) => {
      const matchesSearch = !q || task.title.toLowerCase().includes(q) || (task.description || '').toLowerCase().includes(q);
      const matchesPriority = priority === 'all' || task.priority === priority;
      const matchesSubject = subject === 'all' || task.subjectId === subject;
      const matchesStatus = status === 'all' || task.status === status;
      return matchesSearch && matchesPriority && matchesSubject && matchesStatus;
    });

    tasks.sort((a, b) => {
      if (sort === 'priority') return priorityWeight(b.priority) - priorityWeight(a.priority);
      if (sort === 'created') return new Date(b.createdAt) - new Date(a.createdAt);
      return new Date(a.deadline || '9999-12-31') - new Date(b.deadline || '9999-12-31');
    });

    document.getElementById('task-list').innerHTML = renderTaskRecords(tasks);
    document.querySelectorAll('[data-edit-task]').forEach((button) => button.addEventListener('click', () => openTaskModal(button.dataset.editTask)));
    document.querySelectorAll('[data-delete-task]').forEach((button) => button.addEventListener('click', () => deleteTask(button.dataset.deleteTask)));
    document.querySelectorAll('[data-toggle-task]').forEach((button) => button.addEventListener('click', () => toggleTask(button.dataset.toggleTask)));
  }

  function deleteTask(taskId) {
    const task = window.StudyFlow.tasks.getTaskById(taskId);
    if (!task) return;
    if (!window.confirm(`Delete "${task.title}"?`)) return;
    window.StudyFlow.tasks.removeTask(taskId);
    renderToast('Task deleted.');
    renderView();
  }

  function toggleTask(taskId) {
    const updated = window.StudyFlow.tasks.toggleTask(taskId);
    if (updated) {
      renderToast(updated.status === 'Completed' ? 'Task completed.' : 'Task restored.');
      window.StudyFlow.notifications.addNotification(updated.status === 'Completed' ? 'Task completed successfully.' : 'Task restored to your list.', 'success');
      renderView();
    }
  }

  function deleteSubject(subjectId) {
    if (!window.confirm('Delete this subject?')) return;
    window.StudyFlow.subjects.removeSubject(subjectId);
    renderToast('Subject removed.');
    renderView();
  }

  function deleteSession(sessionId) {
    if (!window.confirm('Delete this session?')) return;
    window.StudyFlow.planner.removeSession(sessionId);
    renderToast('Session deleted.');
    renderView();
  }

  function deleteGoal(goalId) {
    if (!window.confirm('Delete this goal?')) return;
    window.StudyFlow.goals.removeGoal(goalId);
    renderToast('Goal removed.');
    renderView();
  }

  function openTaskModal(taskId = null) {
    const task = taskId ? window.StudyFlow.tasks.getTaskById(taskId) : null;
    const subjects = window.StudyFlow.subjects.getSubjects();
    const form = `
      <div class="modal-backdrop fixed inset-0 z-40 bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="modal-panel mx-auto mt-14 max-w-2xl rounded-3xl border border-slate-200 bg-white p-6 shadow-soft dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold">${task ? 'Edit task' : 'Create task'}</h3>
            <button data-close-modal class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="x" class="h-4 w-4"></i></button>
          </div>
          <form id="task-form" class="mt-5 space-y-4">
            <input type="hidden" name="taskId" value="${task ? task.id : ''}" />
            <div class="grid gap-4 md:grid-cols-2">
              <label class="md:col-span-2 block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Title</span>
                <input name="title" required value="${escapeHtml(task?.title || '')}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="md:col-span-2 block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Description</span>
                <textarea name="description" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">${escapeHtml(task?.description || '')}</textarea>
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Subject</span>
                <select name="subjectId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
                  <option value="">Unassigned</option>
                  ${subjects.map((subject) => `<option value="${subject.id}" ${task && task.subjectId === subject.id ? 'selected' : ''}>${escapeHtml(subject.name)}</option>`).join('')}
                </select>
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Priority</span>
                <select name="priority" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
                  ${['Low', 'Medium', 'High', 'Urgent'].map((priority) => `<option value="${priority}" ${task && task.priority === priority ? 'selected' : ''}>${priority}</option>`).join('')}
                </select>
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Deadline</span>
                <input type="date" name="deadline" value="${task?.deadline ? task.deadline.slice(0, 10) : ''}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Estimated Minutes</span>
                <input type="number" name="estimatedMinutes" value="${task?.estimatedMinutes || 45}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Progress</span>
                <input type="number" min="0" max="100" name="progress" value="${task?.progress || 0}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Status</span>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
                  ${['Todo', 'In Progress', 'Completed'].map((status) => `<option value="${status}" ${task && task.status === status ? 'selected' : ''}>${status}</option>`).join('')}
                </select>
              </label>
              <label class="md:col-span-2 block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Notes</span>
                <textarea name="notes" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">${escapeHtml(task?.notes || '')}</textarea>
              </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
              <button type="button" data-close-modal class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Cancel</button>
              <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">Save Task</button>
            </div>
          </form>
        </div>
      </div>
    `;
    openModal(form);
    document.getElementById('task-form').addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(event.currentTarget);
      const payload = {
        id: formData.get('taskId') || undefined,
        title: formData.get('title').toString().trim(),
        description: formData.get('description').toString().trim(),
        subjectId: formData.get('subjectId').toString(),
        priority: formData.get('priority').toString(),
        deadline: formData.get('deadline').toString(),
        estimatedMinutes: Number(formData.get('estimatedMinutes')) || 45,
        progress: Number(formData.get('progress')) || 0,
        status: formData.get('status').toString() || 'Todo',
        notes: formData.get('notes').toString().trim(),
        difficulty: 'Medium'
      };

      if (!payload.title) {
        renderToast('Task title is required.');
        return;
      }

      if (payload.deadline && Number.isNaN(new Date(payload.deadline).getTime())) {
        renderToast('Please choose a valid deadline.');
        return;
      }

      if (payload.id) {
        window.StudyFlow.tasks.updateTask(payload.id, payload);
      } else {
        window.StudyFlow.tasks.createTask(payload);
      }
      renderToast('Task saved successfully.');
      window.StudyFlow.notifications.addNotification('Task saved successfully.', 'success');
      closeModal();
      renderView();
    });
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
  }

  function openSubjectModal(subjectId = null) {
    const subject = subjectId ? window.StudyFlow.subjects.getSubjectById(subjectId) : null;
    const form = `
      <div class="modal-backdrop fixed inset-0 z-40 bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="modal-panel mx-auto mt-24 max-w-lg rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold">${subject ? 'Edit subject' : 'Create subject'}</h3>
            <button data-close-modal class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="x" class="h-4 w-4"></i></button>
          </div>
          <form id="subject-form" class="mt-5 space-y-4">
            <input type="hidden" name="subjectId" value="${subject ? subject.id : ''}" />
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Name</span>
              <input name="name" required value="${escapeHtml(subject?.name || '')}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
            </label>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Icon</span>
                <input name="icon" value="${escapeHtml(subject?.icon || 'book-open')}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Color</span>
                <input type="color" name="color" value="${subject?.color || '#4f8cff'}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" />
              </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
              <button type="button" data-close-modal class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Cancel</button>
              <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">Save Subject</button>
            </div>
          </form>
        </div>
      </div>
    `;
    openModal(form);
    document.getElementById('subject-form').addEventListener('submit', (event) => {
      event.preventDefault();
      const data = new FormData(event.currentTarget);
      const payload = {
        name: data.get('name').toString().trim(),
        icon: data.get('icon').toString().trim(),
        color: data.get('color').toString().trim()
      };
      if (!payload.name) {
        renderToast('Subject name is required.');
        return;
      }
      const subjectId = data.get('subjectId').toString();
      if (subjectId) {
        window.StudyFlow.subjects.updateSubject(subjectId, payload);
      } else {
        window.StudyFlow.subjects.createSubject(payload);
      }
      closeModal();
      renderToast('Subject saved successfully.');
      renderView();
    });
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
  }

  function openSessionModal(sessionId = null) {
    const session = sessionId ? window.StudyFlow.planner.getSessions().find((item) => item.id === sessionId) : null;
    const subjects = window.StudyFlow.subjects.getSubjects();
    const form = `
      <div class="modal-backdrop fixed inset-0 z-40 bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="modal-panel mx-auto mt-24 max-w-lg rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold">${session ? 'Edit session' : 'Create session'}</h3>
            <button data-close-modal class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="x" class="h-4 w-4"></i></button>
          </div>
          <form id="session-form" class="mt-5 space-y-4">
            <input type="hidden" name="sessionId" value="${session ? session.id : ''}" />
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Subject</span>
              <select name="subjectId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
                ${subjects.map((subject) => `<option value="${subject.id}" ${session && session.subjectId === subject.id ? 'selected' : ''}>${escapeHtml(subject.name)}</option>`).join('')}
              </select>
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Topic</span>
              <input name="topic" value="${escapeHtml(session?.topic || '')}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
            </label>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Day</span>
                <select name="day" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
                  ${['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'].map((day) => `<option value="${day}" ${session && session.day === day ? 'selected' : ''}>${day}</option>`).join('')}
                </select>
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Start Time</span>
                <input type="time" name="startTime" value="${session?.startTime || '09:00'}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Duration (minutes)</span>
                <input type="number" name="duration" value="${session?.duration || 60}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Color</span>
                <input type="color" name="color" value="${session?.color || '#4f8cff'}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 dark:border-slate-700 dark:bg-slate-800" />
              </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
              <button type="button" data-close-modal class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Cancel</button>
              <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">Save Session</button>
            </div>
          </form>
        </div>
      </div>
    `;
    openModal(form);
    document.getElementById('session-form').addEventListener('submit', (event) => {
      event.preventDefault();
      const data = new FormData(event.currentTarget);
      const payload = {
        subjectId: data.get('subjectId').toString(),
        topic: data.get('topic').toString().trim(),
        startTime: data.get('startTime').toString(),
        duration: Number(data.get('duration')) || 60,
        color: data.get('color').toString(),
        day: data.get('day').toString()
      };
      const sessionId = data.get('sessionId').toString();
      if (sessionId) {
        window.StudyFlow.planner.updateSession(sessionId, payload);
      } else {
        window.StudyFlow.planner.createSession(payload);
      }
      closeModal();
      renderToast('Session saved.');
      renderView();
    });
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
  }

  function openGoalModal(goalId = null) {
    const goal = goalId ? window.StudyFlow.goals.getGoals().find((item) => item.id === goalId) : null;
    const form = `
      <div class="modal-backdrop fixed inset-0 z-40 bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="modal-panel mx-auto mt-24 max-w-lg rounded-3xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold">${goal ? 'Edit goal' : 'Create goal'}</h3>
            <button data-close-modal class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="x" class="h-4 w-4"></i></button>
          </div>
          <form id="goal-form" class="mt-5 space-y-4">
            <input type="hidden" name="goalId" value="${goal ? goal.id : ''}" />
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Title</span>
              <input name="title" required value="${escapeHtml(goal?.title || '')}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
            </label>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Description</span>
              <textarea name="description" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">${escapeHtml(goal?.description || '')}</textarea>
            </label>
            <div class="grid gap-4 md:grid-cols-2">
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Deadline</span>
                <input type="date" name="deadline" value="${goal?.deadline ? goal.deadline.slice(0,10) : ''}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
              <label class="block">
                <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Target</span>
                <input type="number" name="target" value="${goal?.target || 100}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
              </label>
            </div>
            <label class="block">
              <span class="mb-1.5 block text-xs font-medium text-slate-500 dark:text-slate-400">Current progress</span>
              <input type="number" name="currentProgress" value="${goal?.currentProgress || 0}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
            </label>
            <div class="mt-6 flex justify-end gap-3">
              <button type="button" data-close-modal class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Cancel</button>
              <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">Save Goal</button>
            </div>
          </form>
        </div>
      </div>
    `;
    openModal(form);
    document.getElementById('goal-form').addEventListener('submit', (event) => {
      event.preventDefault();
      const data = new FormData(event.currentTarget);
      const payload = {
        title: data.get('title').toString().trim(),
        description: data.get('description').toString().trim(),
        deadline: data.get('deadline').toString(),
        target: Number(data.get('target')) || 100,
        currentProgress: Number(data.get('currentProgress')) || 0,
        category: 'Study'
      };
      if (!payload.title) {
        renderToast('Goal title is required.');
        return;
      }
      const goalId = data.get('goalId').toString();
      if (goalId) {
        window.StudyFlow.goals.updateGoal(goalId, payload);
      } else {
        window.StudyFlow.goals.createGoal(payload);
      }
      closeModal();
      renderToast('Goal saved.');
      renderView();
    });
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
  }

  function openOnboarding() {
    const steps = [
      { key: 'name', label: 'What should we call you?', type: 'text', placeholder: 'Alex Jordan' },
      { key: 'learningFocus', label: 'What are you currently learning?', type: 'text', placeholder: 'Networking and JavaScript' },
      { key: 'goal', label: 'What is your main goal?', type: 'text', placeholder: 'Build a strong portfolio and pass my exams' },
      { key: 'studyMinutesPerDay', label: 'How much time can you study per day?', type: 'select', options: ['30 minutes', '1 hour', '2 hours', '3+ hours'] }
    ];

    let stepIndex = 0;
    const modal = document.createElement('div');
    modal.className = 'modal-backdrop fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm';
    modal.innerHTML = `
      <div class="modal-panel w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 shadow-soft dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-6 flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Welcome</p>
            <h3 class="mt-2 text-2xl font-bold">Set up StudyFlow</h3>
          </div>
          <button data-close-modal class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
        <div class="space-y-4">
          <div class="mb-3 h-2 rounded-full bg-slate-200 dark:bg-slate-700">
            <div id="onboarding-progress" class="h-full rounded-full bg-gradient-to-r from-accent-500 to-violet-500" style="width:25%"></div>
          </div>
          <div id="onboarding-content"></div>
          <div class="flex justify-between gap-3 pt-4">
            <button id="onboarding-back" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Back</button>
            <button id="onboarding-next" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">Next</button>
          </div>
        </div>
      </div>
    `;
    document.getElementById('modal-root').appendChild(modal);
    const formData = {};

    function renderOnboardingStep() {
      const step = steps[stepIndex];
      const content = document.getElementById('onboarding-content');
      const progress = (stepIndex + 1) / steps.length * 100;
      document.getElementById('onboarding-progress').style.width = `${progress}%`;
      document.getElementById('onboarding-back').style.visibility = stepIndex === 0 ? 'hidden' : 'visible';
      document.getElementById('onboarding-next').textContent = stepIndex === steps.length - 1 ? 'Finish' : 'Next';

      if (step.type === 'select') {
        content.innerHTML = `
          <label class="block">
            <span class="mb-2 block text-lg font-semibold">${step.label}</span>
            <select id="onboarding-input" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800">
              ${step.options.map((option) => `<option value="${option}">${option}</option>`).join('')}
            </select>
          </label>
        `;
      } else {
        content.innerHTML = `
          <label class="block">
            <span class="mb-2 block text-lg font-semibold">${step.label}</span>
            <input id="onboarding-input" type="text" value="${escapeHtml(formData[step.key] || '')}" placeholder="${step.placeholder}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" />
          </label>
        `;
      }
    }

    function nextStep() {
      const input = document.getElementById('onboarding-input');
      const step = steps[stepIndex];
      const value = input.value.trim();
      if (!value) {
        renderToast('Please fill this in to continue.');
        return;
      }
      formData[step.key] = value;
      if (stepIndex < steps.length - 1) {
        stepIndex += 1;
        renderOnboardingStep();
      } else {
        window.StudyFlow.state.setUser({
          name: formData.name || 'Student',
          learningFocus: formData.learningFocus || 'Programming',
          goal: formData.goal || 'Build stronger study habits',
          studyMinutesPerDay: valueToMinutes(formData.studyMinutesPerDay)
        });
        window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.onboarding, { complete: true });
        loadDemoData();
        closeModal();
        renderToast('Welcome to StudyFlow!');
        navigateTo('dashboard');
      }
    }

    document.getElementById('onboarding-next').addEventListener('click', nextStep);
    document.getElementById('onboarding-back').addEventListener('click', () => {
      if (stepIndex > 0) {
        stepIndex -= 1;
        renderOnboardingStep();
      }
    });
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
    renderOnboardingStep();
  }

  function valueToMinutes(value) {
    if (value.includes('3+')) return 180;
    if (value.includes('2')) return 120;
    if (value.includes('1')) return 60;
    return 30;
  }

  function openSearchModal() {
    const results = window.StudyFlow.search.searchAll(document.getElementById('global-search-input')?.value || '');
    const modal = `
      <div class="modal-backdrop fixed inset-0 z-40 bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="modal-panel mx-auto mt-20 max-w-2xl rounded-3xl border border-slate-200 bg-white p-5 shadow-soft dark:border-slate-700 dark:bg-slate-900">
          <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
            <i data-lucide="search" class="h-4 w-4 text-slate-500"></i>
            <input id="global-search-input" class="w-full bg-transparent text-sm outline-none" placeholder="Search tasks, subjects, goals" />
          </div>
          <div class="mt-5 space-y-4">
            ${renderSearchResults(results)}
          </div>
        </div>
      </div>
    `;
    openModal(modal);
    const input = document.getElementById('global-search-input');
    input.addEventListener('input', (event) => {
      const result = window.StudyFlow.search.searchAll(event.target.value);
      const panel = document.querySelector('#modal-root .modal-panel');
      if (!panel) return;
      const target = panel.querySelector('.mt-5');
      target.innerHTML = renderSearchResults(result);
    });
    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', closeModal));
  }

  function renderSearchResults(results) {
    const sections = [];
    ['tasks', 'subjects', 'goals'].forEach((type) => {
      const items = results[type];
      sections.push(`<div><h4 class="mb-2 text-sm font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">${type.charAt(0).toUpperCase() + type.slice(1)}</h4>${items.length ? items.map((item) => `<div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm dark:border-slate-700 dark:bg-slate-800">${escapeHtml(item.title || item.name || item.topic || '')}</div>`).join('') : '<div class="rounded-xl border border-dashed border-slate-200 p-3 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">No results</div>'}</div>`);
    });
    return sections.join('');
  }

  function openModal(content) {
    document.getElementById('modal-root').innerHTML = content;
    if (window.lucide) lucide.createIcons();
  }

  function closeModal() {
    document.getElementById('modal-root').innerHTML = '';
  }

  function openMobileSidebar() {
    document.getElementById('sidebar').classList.remove('-translate-x-full');
    document.getElementById('mobile-overlay').classList.remove('hidden');
  }

  function closeMobileSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('mobile-overlay').classList.add('hidden');
  }

  function handleFocusAction(action) {
    if (action === 'start') {
      const task = window.StudyFlow.priorityEngine.getRecommendedTask(window.StudyFlow.tasks.getTasks())?.task || window.StudyFlow.tasks.getTasks().find((item) => item.status !== 'Completed');
      if (!task) {
        renderToast('Create a task before starting focus mode.');
        return;
      }
      window.StudyFlow.focus.start(task.id, Number(task.estimatedMinutes || 45));
      renderView();
      renderToast('Focus session started.');
    }
    if (action === 'pause') {
      window.StudyFlow.focus.pause();
      renderFocusTimer();
      renderToast('Focus session paused.');
    }
    if (action === 'resume') {
      window.StudyFlow.focus.resume();
      renderFocusTimer();
      renderToast('Focus session resumed.');
    }
    if (action === 'reset') {
      window.StudyFlow.focus.reset();
      renderFocusTimer();
      renderToast('Timer reset.');
    }
    if (action === 'finish') {
      const completion = window.StudyFlow.focus.completeSession();
      if (completion) {
        renderToast('Focus session complete!');
        window.StudyFlow.notifications.addNotification('Focus session complete.', 'success');
        const tasks = window.StudyFlow.tasks.getTasks();
        const task = tasks.find((item) => item.id === completion.taskId);
        if (task) {
          window.StudyFlow.tasks.updateTask(task.id, { progress: Math.min(100, Number(task.progress || 0) + 15) });
        }
        renderView();
      }
    }
    if (action === 'toggle-sound') {
      renderToast('Sound toggle ready.');
    }
    if (action === 'fullscreen') {
      document.documentElement.requestFullscreen?.();
    }
  }

  function renderFocusTimer() {
    const timerEl = document.getElementById('focus-timer');
    if (!timerEl) return;
    const stateObj = window.StudyFlow.focus.getState();
    const seconds = stateObj.timerSeconds || 0;
    timerEl.textContent = formatFocusTime(seconds);
  }

  function renderChart(id, type, config) {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (state.charts[id]) state.charts[id].destroy();
    const ctx = canvas.getContext('2d');
    state.charts[id] = new Chart(ctx, {
      type,
      data: { labels: config.labels, datasets: config.datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { color: '#64748b' } },
          x: { grid: { display: false }, ticks: { color: '#64748b' } }
        }
      }
    });
  }

  function metricCard(label, value, subtitle, iconName) {
    return `
      <div class="metric-card p-4">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm text-slate-500 dark:text-slate-400">${label}</p>
            <div class="mt-3 text-3xl font-bold tracking-tight">${value}</div>
          </div>
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <i data-lucide="${iconName}" class="h-4 w-4"></i>
          </div>
        </div>
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">${subtitle}</p>
      </div>
    `;
  }

  function taskRow(task) {
    const subject = window.StudyFlow.subjects.getSubjectById(task.subjectId);
    return `
      <div class="task-row card flex flex-col gap-3 p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <button data-toggle-task="${task.id}" class="mt-1 flex h-5 w-5 items-center justify-center rounded border border-slate-300 bg-white text-xs text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
              ${task.status === 'Completed' ? '✓' : ''}
            </button>
            <div>
              <div class="font-semibold">${escapeHtml(task.title)}</div>
              <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>${subject ? escapeHtml(subject.name) : 'Unassigned'}</span>
                <span>•</span>
                <span>${escapeHtml(task.status)}</span>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span class="rounded-full ${priorityClass(task.priority)} px-2 py-1 text-[10px] font-semibold">${task.priority}</span>
            <button data-edit-task="${task.id}" class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button>
            <button data-delete-task="${task.id}" class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="trash" class="h-3.5 w-3.5"></i></button>
          </div>
        </div>
        <div class="grid gap-2 text-sm text-slate-600 md:grid-cols-4 dark:text-slate-300">
          <div><span class="text-slate-500 dark:text-slate-400">Deadline</span><div class="mt-1 font-medium">${task.deadline ? formatDate(task.deadline) : 'No deadline'}</div></div>
          <div><span class="text-slate-500 dark:text-slate-400">Duration</span><div class="mt-1 font-medium">${formatMinutes(task.estimatedMinutes || 45)}</div></div>
          <div><span class="text-slate-500 dark:text-slate-400">Progress</span><div class="mt-1 font-medium">${Number(task.progress || 0)}%</div></div>
          <div><span class="text-slate-500 dark:text-slate-400">Notes</span><div class="mt-1 font-medium text-xs">${escapeHtml((task.notes || '').slice(0, 30) || 'No notes')}</div></div>
        </div>
        <div class="progress-bar h-2 bg-slate-200 dark:bg-slate-700">
          <div class="progress-fill h-full rounded-full bg-gradient-to-r from-sky-500 to-violet-500" style="width:${Number(task.progress || 0)}%"></div>
        </div>
      </div>
    `;
  }

  function renderTaskRecords(tasks) {
    if (!tasks.length) {
      return emptyState('No tasks yet.', 'Add your first task and let StudyFlow help you decide what to work on.', 'create-task');
    }
    return tasks.map((task) => taskRow(task)).join('');
  }

  function renderDeadlineList(tasks) {
    const upcoming = tasks.filter((task) => task.deadline).sort((a, b) => new Date(a.deadline) - new Date(b.deadline)).slice(0, 4);
    if (!upcoming.length) return '<div class="text-sm text-slate-500 dark:text-slate-400">No upcoming deadlines.</div>';
    return upcoming.map((task) => {
      const date = new Date(task.deadline);
      const today = new Date();
      let stateLabel = 'upcoming';
      if (date < today) stateLabel = 'overdue';
      else if (date.toDateString() === today.toDateString()) stateLabel = 'today';
      else if (date.getDate() === today.getDate() + 1) stateLabel = 'tomorrow';
      return `
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
          <div class="flex items-center justify-between gap-2">
            <div class="font-medium">${escapeHtml(task.title)}</div>
            <span class="rounded-full ${deadlineClass(stateLabel)} px-2 py-1 text-[10px] font-semibold uppercase">${stateLabel}</span>
          </div>
          <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">${formatDate(task.deadline, { month: 'short', day: 'numeric' })}</div>
        </div>
      `;
    }).join('');
  }

  function subjectCard(subject) {
    const taskCount = window.StudyFlow.tasks.getTasks().filter((task) => task.subjectId === subject.id).length;
    const totalStudy = window.StudyFlow.planner.getSessions().filter((session) => session.subjectId === subject.id).reduce((sum, session) => sum + Number(session.duration || 0), 0);
    return `
      <div class="subject-card card p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl text-white" style="background:${subject.color || '#4f8cff'}">
              <i data-lucide="${subject.icon || 'book-open'}" class="h-5 w-5"></i>
            </div>
            <div>
              <div class="font-bold">${escapeHtml(subject.name)}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">${taskCount} tasks</div>
            </div>
          </div>
          <div class="flex gap-2">
            <button data-edit-subject="${subject.id}" class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button>
            <button data-delete-subject="${subject.id}" class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="trash" class="h-3.5 w-3.5"></i></button>
          </div>
        </div>
        <div class="mt-5">
          <div class="mb-2 flex justify-between text-sm"><span>Progress</span><span>${window.StudyFlow.utils.formatSubjectProgress(subject)}%</span></div>
          <div class="progress-bar h-2 bg-slate-200 dark:bg-slate-700">
            <div class="progress-fill h-full rounded-full" style="width:${window.StudyFlow.utils.formatSubjectProgress(subject)}%; background:${subject.color || '#4f8cff'}"></div>
          </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><div class="text-slate-500 dark:text-slate-400">Study time</div><div class="mt-1 font-semibold">${formatMinutes(totalStudy)}</div></div>
          <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><div class="text-slate-500 dark:text-slate-400">Done</div><div class="mt-1 font-semibold">${window.StudyFlow.tasks.getTasks().filter((task) => task.subjectId === subject.id && task.status === 'Completed').length}</div></div>
        </div>
      </div>
    `;
  }

  function plannerDayColumn(day, sessions) {
    return `
      <div class="card p-3">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold">${day}</h3>
          <button data-action="create-session" class="rounded-lg border border-slate-200 px-2 py-1 text-xs dark:border-slate-700">+</button>
        </div>
        <div class="space-y-2">
          ${sessions.length ? sessions.map((session) => `
            <div class="rounded-xl border border-slate-200 p-2 dark:border-slate-700" style="background:${session.color || '#4f8cff'}20; border-left:4px solid ${session.color || '#4f8cff'}">
              <div class="flex items-center justify-between">
                <div class="font-medium text-sm">${escapeHtml(session.topic || 'Study block')}</div>
                <div class="flex gap-1">
                  <button data-edit-session="${session.id}" class="rounded p-1 text-xs"><i data-lucide="pencil" class="h-3 w-3"></i></button>
                  <button data-delete-session="${session.id}" class="rounded p-1 text-xs"><i data-lucide="trash" class="h-3 w-3"></i></button>
                </div>
              </div>
              <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">${escapeHtml(window.StudyFlow.subjects.getSubjectById(session.subjectId)?.name || 'Subject')} • ${session.startTime} • ${session.duration}m</div>
            </div>
          `).join('') : '<div class="rounded-xl border border-dashed border-slate-200 p-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">No sessions</div>'}
        </div>
      </div>
    `;
  }

  function goalCard(goal) {
    const percent = window.StudyFlow.goals.calculateGoalProgress(goal);
    return `
      <div class="goal-card card p-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <div class="text-lg font-bold">${escapeHtml(goal.title)}</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">${escapeHtml(goal.category || 'Study')}</div>
          </div>
          <div class="flex gap-2">
            <button data-edit-goal="${goal.id}" class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></button>
            <button data-delete-goal="${goal.id}" class="rounded-lg border border-slate-200 p-2 dark:border-slate-700"><i data-lucide="trash" class="h-3.5 w-3.5"></i></button>
          </div>
        </div>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">${escapeHtml(goal.description || 'Keep progressing toward this outcome.')}</p>
        <div class="mt-4">
          <div class="mb-2 flex items-center justify-between text-sm"><span>Progress</span><span>${percent}%</span></div>
          <div class="progress-bar h-3 bg-slate-200 dark:bg-slate-700">
            <div class="progress-fill h-full rounded-full bg-gradient-to-r from-emerald-500 to-green-500" style="width:${percent}%"></div>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
          ${(goal.milestones || []).length ? (goal.milestones || []).map((milestone, index) => `<span class="rounded-full ${index < Math.max(1, Math.round(percent / 33)) ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'} px-2.5 py-1 text-[11px]">${escapeHtml(milestone)}</span>`).join('') : '<span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] text-slate-700 dark:bg-slate-700 dark:text-slate-200">Goal in progress</span>'}
        </div>
      </div>
    `;
  }

  function achievementCard(achievement) {
    return `
      <div class="achievement-card card flex h-full flex-col p-4 ${achievement.unlocked ? 'border-emerald-200 bg-emerald-50/40 dark:border-emerald-900 dark:bg-emerald-950/20' : 'opacity-75'}">
        <div class="flex items-center justify-between">
          <div class="text-3xl">${achievement.icon}</div>
          <span class="rounded-full ${achievement.unlocked ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-200'} px-2.5 py-1 text-[10px] font-semibold uppercase">${achievement.unlocked ? 'Unlocked' : 'Locked'}</span>
        </div>
        <div class="mt-4 text-lg font-bold">${escapeHtml(achievement.title)}</div>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">${escapeHtml(achievement.description)}</p>
        <div class="mt-4 text-xs text-slate-500 dark:text-slate-400">${achievement.unlockedAt ? `Unlocked ${formatDate(achievement.unlockedAt)}` : 'Not yet unlocked'}</div>
      </div>
    `;
  }

  function priorityWeight(priority) {
    const weights = { Low: 1, Medium: 2, High: 3, Urgent: 4 };
    return weights[priority] || 0;
  }

  function priorityClass(priority) {
    return {
      Low: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
      Medium: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
      High: 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
      Urgent: 'bg-violet-100 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300'
    }[priority] || 'bg-slate-100 text-slate-700';
  }

  function deadlineClass(label) {
    const classes = {
      overdue: 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
      today: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
      tomorrow: 'bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
      upcoming: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
    };
    return classes[label] || 'bg-slate-100 text-slate-700';
  }

  function emptyState(title, description, action) {
    return `
      <div class="empty-state p-8 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-200">
          <i data-lucide="sparkles" class="h-7 w-7"></i>
        </div>
        <h3 class="text-lg font-bold">${title}</h3>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">${description}</p>
        <button data-action="${action}" class="mt-5 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-accent-500 dark:hover:bg-accent-600">+ Create</button>
      </div>
    `;
  }

  function formatMinutes(value) {
    if (!value) return '0m';
    const minutes = Number(value);
    const hours = Math.floor(minutes / 60);
    const remainder = minutes % 60;
    if (hours && remainder) return `${hours}h ${remainder}m`;
    if (hours) return `${hours}h`;
    return `${remainder}m`;
  }

  function formatTime(totalMinutes) {
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    if (hours && minutes) return `${hours}h ${minutes}m`;
    if (hours) return `${hours}h`;
    return `${minutes}m`;
  }

  function formatFocusTime(totalSeconds) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  }

  function getGreeting() {
    const hour = new Date().getHours();
    return hour < 12 ? 'morning' : hour < 18 ? 'afternoon' : 'evening';
  }

  function escapeHtml(value = '') {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatDate(dateString, options = { month: 'short', day: 'numeric' }) {
    if (!dateString) return 'No deadline';
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return 'No deadline';
    return new Intl.DateTimeFormat('en-US', options).format(date);
  }

  function loadDemoData() {
    const subjects = [
      { id: 'subject_networking', name: 'Networking', icon: 'network', color: '#4f8cff' },
      { id: 'subject_programming', name: 'Programming', icon: 'code', color: '#8b5cf6' },
      { id: 'subject_mathematics', name: 'Mathematics', icon: 'calculator', color: '#10b981' },
      { id: 'subject_english', name: 'English', icon: 'book-open', color: '#f59e0b' }
    ];

    const tasks = [
      { id: 'task_1', title: 'Subnetting Practice', description: 'Review CIDR notation and subnetting exercises', subjectId: 'subject_networking', priority: 'High', deadline: new Date(Date.now() + 86400000).toISOString(), estimatedMinutes: 45, progress: 60, status: 'In Progress', difficulty: 'Medium', createdAt: new Date().toISOString(), notes: 'Complete 10 questions.' },
      { id: 'task_2', title: 'Build JavaScript Calculator', description: 'Finish calculator UI and logic', subjectId: 'subject_programming', priority: 'Urgent', deadline: new Date(Date.now() + 172800000).toISOString(), estimatedMinutes: 90, progress: 40, status: 'In Progress', difficulty: 'Hard', createdAt: new Date().toISOString(), notes: 'Review DOM and event handling.' },
      { id: 'task_3', title: 'Complete Algebra Exercises', description: 'Solve chapter 4 problem set', subjectId: 'subject_mathematics', priority: 'Medium', deadline: new Date(Date.now() + 259200000).toISOString(), estimatedMinutes: 60, progress: 20, status: 'Todo', difficulty: 'Medium', createdAt: new Date().toISOString(), notes: 'Focus on quadratic equations.' },
      { id: 'task_4', title: 'English Presentation', description: 'Prepare a compelling 5-minute presentation', subjectId: 'subject_english', priority: 'High', deadline: new Date(Date.now() + 432000000).toISOString(), estimatedMinutes: 50, progress: 80, status: 'In Progress', difficulty: 'Easy', createdAt: new Date().toISOString(), notes: 'Prepare slides and rehearsal notes.' }
    ];

    const sessions = [
      { id: 'session_1', subjectId: 'subject_networking', topic: 'Subnetting fundamentals', startTime: '09:00', duration: 45, color: '#4f8cff', day: 'Monday' },
      { id: 'session_2', subjectId: 'subject_programming', topic: 'JavaScript DOM', startTime: '11:00', duration: 60, color: '#8b5cf6', day: 'Tuesday' },
      { id: 'session_3', subjectId: 'subject_mathematics', topic: 'Algebra review', startTime: '15:00', duration: 50, color: '#10b981', day: 'Wednesday' },
      { id: 'session_4', subjectId: 'subject_english', topic: 'Presentation draft', startTime: '18:00', duration: 40, color: '#f59e0b', day: 'Thursday' }
    ];

    const goals = [
      { id: 'goal_1', title: 'Master Networking Fundamentals', description: 'Complete the networking basics course and labs', deadline: new Date(Date.now() + 1209600000).toISOString(), target: 100, currentProgress: 68, category: 'Study', milestones: ['Subnetting', 'Routing', 'Protocols'] },
      { id: 'goal_2', title: 'Build Portfolio Website', description: 'Develop and publish a polished personal web portfolio', deadline: new Date(Date.now() + 2419200000).toISOString(), target: 100, currentProgress: 52, category: 'Career', milestones: ['Landing page', 'Projects', 'Deploy'] },
      { id: 'goal_3', title: 'Complete JavaScript Course', description: 'Finish structured fundamentals and practice projects', deadline: new Date(Date.now() + 1814400000).toISOString(), target: 100, currentProgress: 74, category: 'Study', milestones: ['Variables', 'Functions', 'DOM', 'Async'] }
    ];

    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.subjects, subjects);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.tasks, tasks);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.sessions, sessions);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.goals, goals);
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.notifications, [{ id: 'note_1', type: 'success', message: 'Demo data loaded. Start studying!', createdAt: new Date().toISOString() }]);
    renderToast('Demo data loaded.');
    window.StudyFlow.notifications.addNotification('Demo data loaded successfully.', 'success');
    navigateTo('dashboard');
  }

  return {
    initialize
  };
})();

document.addEventListener('DOMContentLoaded', () => {
  window.StudyFlow.app.initialize();
});
