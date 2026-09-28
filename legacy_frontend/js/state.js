window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.state = (() => {
  const defaultSettings = {
    theme: 'system',
    defaultStudyDuration: 45,
    notificationsEnabled: true,
    soundEnabled: true,
    compactMode: false
  };

  const defaultUser = {
    id: 'user_1',
    name: 'Student',
    email: '',
    goal: 'Build stronger study habits',
    learningFocus: 'Programming',
    studyMinutesPerDay: 120,
    avatar: 'S'
  };

  const defaultNotifications = [];

  function initializeState() {
    const storage = window.StudyFlow.storage;
    const user = storage.loadData(storage.KEYS.user, defaultUser);
    const settings = storage.loadData(storage.KEYS.settings, defaultSettings);
    const notifications = storage.loadData(storage.KEYS.notifications, defaultNotifications);
    const onboarding = storage.loadData(storage.KEYS.onboarding, { complete: false });

    const state = {
      user: { ...defaultUser, ...user },
      settings: { ...defaultSettings, ...settings },
      notifications: Array.isArray(notifications) ? notifications : [],
      onboarding: { complete: false, ...onboarding }
    };

    if (state.user.avatar && state.user.avatar.length <= 2) {
      state.user.avatar = state.user.avatar.toUpperCase();
    }

    saveState();
    return state;
  }

  function getState() {
    const storage = window.StudyFlow.storage;
    const user = storage.loadData(storage.KEYS.user, defaultUser);
    const settings = storage.loadData(storage.KEYS.settings, defaultSettings);
    const notifications = storage.loadData(storage.KEYS.notifications, defaultNotifications);
    const onboarding = storage.loadData(storage.KEYS.onboarding, { complete: false });

    return {
      user: { ...defaultUser, ...user },
      settings: { ...defaultSettings, ...settings },
      notifications: Array.isArray(notifications) ? notifications : [],
      onboarding: { complete: false, ...onboarding }
    };
  }

  function saveState() {
    const storage = window.StudyFlow.storage;
    const state = getState();
    storage.saveData(storage.KEYS.user, state.user);
    storage.saveData(storage.KEYS.settings, state.settings);
    storage.saveData(storage.KEYS.notifications, state.notifications);
    storage.saveData(storage.KEYS.onboarding, state.onboarding);
    return state;
  }

  function setUser(userPatch) {
    const state = getState();
    state.user = { ...state.user, ...userPatch };
    saveData(state);
    return state.user;
  }

  function setSettings(settingsPatch) {
    const state = getState();
    state.settings = { ...state.settings, ...settingsPatch };
    saveData(state);
    return state.settings;
  }

  function saveData(state) {
    const storage = window.StudyFlow.storage;
    storage.saveData(storage.KEYS.user, state.user);
    storage.saveData(storage.KEYS.settings, state.settings);
    storage.saveData(storage.KEYS.notifications, state.notifications);
    storage.saveData(storage.KEYS.onboarding, state.onboarding);
  }

  return {
    defaultSettings,
    defaultUser,
    initializeState,
    getState,
    saveState,
    setUser,
    setSettings
  };
})();
