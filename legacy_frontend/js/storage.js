window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.storage = (() => {
  const KEYS = {
    user: 'studyflow_user',
    tasks: 'studyflow_tasks',
    subjects: 'studyflow_subjects',
    sessions: 'studyflow_sessions',
    goals: 'studyflow_goals',
    achievements: 'studyflow_achievements',
    settings: 'studyflow_settings',
    notifications: 'studyflow_notifications',
    onboarding: 'studyflow_onboarding',
    theme: 'studyflow_theme'
  };

  function safeParse(value, fallback) {
    try {
      const parsed = JSON.parse(value);
      return parsed ?? fallback;
    } catch (error) {
      return fallback;
    }
  }

  function saveData(key, data) {
    try {
      localStorage.setItem(key, JSON.stringify(data));
      return true;
    } catch (error) {
      console.error('Storage write failed:', error);
      return false;
    }
  }

  function loadData(key, fallback = null) {
    try {
      const value = localStorage.getItem(key);
      if (value === null || value === undefined) {
        return fallback;
      }
      return safeParse(value, fallback);
    } catch (error) {
      console.error('Storage read failed:', error);
      return fallback;
    }
  }

  function updateData(key, updater) {
    const current = loadData(key, []);
    const next = typeof updater === 'function' ? updater(current) : updater;
    saveData(key, next);
    return next;
  }

  function deleteData(key) {
    localStorage.removeItem(key);
  }

  function generateId(prefix = 'id') {
    return `${prefix}_${Date.now()}_${Math.random().toString(16).slice(2, 10)}`;
  }

  return {
    KEYS,
    saveData,
    loadData,
    updateData,
    deleteData,
    generateId
  };
})();
