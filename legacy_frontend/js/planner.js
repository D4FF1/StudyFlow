window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.planner = (() => {
  const STORAGE_KEY = window.StudyFlow.storage.KEYS.sessions;

  function getSessions() {
    return window.StudyFlow.storage.loadData(STORAGE_KEY, []);
  }

  function saveSessions(list) {
    window.StudyFlow.storage.saveData(STORAGE_KEY, list);
  }

  function createSession(data) {
    const session = {
      id: window.StudyFlow.storage.generateId('session'),
      subjectId: data.subjectId || '',
      topic: data.topic || 'Study block',
      startTime: data.startTime || '09:00',
      duration: Number(data.duration || 60),
      color: data.color || '#4f8cff',
      day: data.day || 'Monday',
      createdAt: new Date().toISOString()
    };
    const list = getSessions();
    list.push(session);
    saveSessions(list);
    return session;
  }

  function updateSession(sessionId, patch) {
    const sessions = getSessions();
    const index = sessions.findIndex((session) => session.id === sessionId);
    if (index === -1) return null;
    sessions[index] = { ...sessions[index], ...patch };
    saveSessions(sessions);
    return sessions[index];
  }

  function removeSession(sessionId) {
    const next = getSessions().filter((session) => session.id !== sessionId);
    saveSessions(next);
    return next;
  }

  function getSessionsForDay(dayName) {
    return getSessions().filter((session) => session.day === dayName);
  }

  return {
    STORAGE_KEY,
    getSessions,
    saveSessions,
    createSession,
    updateSession,
    removeSession,
    getSessionsForDay
  };
})();
