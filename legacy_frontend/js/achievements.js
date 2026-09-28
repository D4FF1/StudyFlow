window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.achievements = (() => {
  const base = [
    { id: 'first_focus', title: 'First Focus', description: 'Complete your first focus session.', icon: '🎯', unlocked: false, unlockedAt: null },
    { id: 'consistent', title: 'Consistent', description: 'Study for 7 consecutive days.', icon: '🔥', unlocked: false, unlockedAt: null },
    { id: 'deep_worker', title: 'Deep Worker', description: 'Complete 5 focus sessions.', icon: '⚡', unlocked: false, unlockedAt: null },
    { id: 'goal_crusher', title: 'Goal Crusher', description: 'Complete 20 tasks.', icon: '🎯', unlocked: false, unlockedAt: null },
    { id: 'bookworm', title: 'Bookworm', description: 'Study for 10 hours.', icon: '📚', unlocked: false, unlockedAt: null },
    { id: 'momentum', title: 'Momentum', description: 'Complete 5 tasks in one day.', icon: '🚀', unlocked: false, unlockedAt: null }
  ];

  function getAchievements() {
    const stored = window.StudyFlow.storage.loadData(window.StudyFlow.storage.KEYS.achievements, base);
    return Array.isArray(stored) && stored.length ? stored : base;
  }

  function saveAchievements(list) {
    window.StudyFlow.storage.saveData(window.StudyFlow.storage.KEYS.achievements, list);
  }

  function refreshAchievements() {
    const tasks = window.StudyFlow.tasks.getTasks();
    const sessions = window.StudyFlow.planner.getSessions();
    const current = getAchievements();
    const updated = current.map((achievement) => {
      let unlocked = achievement.unlocked || false;
      let unlockedAt = achievement.unlockedAt || null;

      if (achievement.id === 'first_focus' && sessions.length > 0) {
        unlocked = true;
        unlockedAt = unlockedAt || new Date().toISOString();
      }
      if (achievement.id === 'consistent') {
        const streak = window.StudyFlow.analytics.calculateStreaks().current;
        unlocked = streak >= 7;
        if (unlocked) unlockedAt = unlockedAt || new Date().toISOString();
      }
      if (achievement.id === 'deep_worker') {
        const focusCount = sessions.length;
        unlocked = focusCount >= 5;
        if (unlocked) unlockedAt = unlockedAt || new Date().toISOString();
      }
      if (achievement.id === 'goal_crusher') {
        const completed = tasks.filter((task) => task.status === 'Completed').length;
        unlocked = completed >= 20;
        if (unlocked) unlockedAt = unlockedAt || new Date().toISOString();
      }
      if (achievement.id === 'bookworm') {
        const totalMinutes = sessions.reduce((sum, session) => sum + Number(session.duration || 0), 0);
        unlocked = totalMinutes >= 600;
        if (unlocked) unlockedAt = unlockedAt || new Date().toISOString();
      }
      if (achievement.id === 'momentum') {
        const byDay = {};
        tasks.filter((task) => task.status === 'Completed').forEach((task) => {
          const day = new Date(task.completedAt || new Date()).toISOString().slice(0, 10);
          byDay[day] = (byDay[day] || 0) + 1;
        });
        unlocked = Object.values(byDay).some((count) => count >= 5);
        if (unlocked) unlockedAt = unlockedAt || new Date().toISOString();
      }

      return { ...achievement, unlocked, unlockedAt };
    });

    saveAchievements(updated);
    return updated;
  }

  return {
    getAchievements,
    saveAchievements,
    refreshAchievements
  };
})();
