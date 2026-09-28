window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.focus = (() => {
  const state = {
    activeTaskId: null,
    timerSeconds: 0,
    isRunning: false,
    isPaused: false,
    startedAt: null,
    lastTick: null,
    sessionLog: []
  };

  function getState() {
    return state;
  }

  function start(taskId, durationMinutes = 45) {
    const task = window.StudyFlow.tasks.getTaskById(taskId);
    if (!task) return null;
    state.activeTaskId = taskId;
    state.timerSeconds = durationMinutes * 60;
    state.isRunning = true;
    state.isPaused = false;
    state.startedAt = Date.now();
    state.lastTick = Date.now();
    return { task, remaining: state.timerSeconds };
  }

  function pause() {
    if (!state.activeTaskId) return;
    state.isRunning = false;
    state.isPaused = true;
  }

  function resume() {
    if (!state.activeTaskId) return;
    state.isRunning = true;
    state.isPaused = false;
    state.startedAt = Date.now();
    state.lastTick = Date.now();
  }

  function reset() {
    state.isRunning = false;
    state.isPaused = false;
    state.startedAt = null;
    state.lastTick = null;
    state.timerSeconds = 0;
  }

  function tick() {
    if (!state.activeTaskId || !state.isRunning) return state.timerSeconds;

    const now = Date.now();
    const elapsed = Math.floor((now - state.lastTick) / 1000);
    if (elapsed <= 0) return state.timerSeconds;
    state.lastTick = now;
    state.timerSeconds = Math.max(0, state.timerSeconds - elapsed);
    return state.timerSeconds;
  }

  function completeSession() {
    if (!state.activeTaskId) return null;
    const task = window.StudyFlow.tasks.getTaskById(state.activeTaskId);
    if (task) {
      const durationMinutes = Math.max(1, Math.round((task.estimatedMinutes || 45) / 2));
      window.StudyFlow.tasks.updateTask(task.id, {
        status: 'In Progress',
        progress: Math.min(100, Number(task.progress || 0) + 10)
      });
      window.StudyFlow.notifications.addNotification(`Focus session saved for ${task.title}.`, 'success');
    }
    const completed = { taskId: state.activeTaskId, completedAt: new Date().toISOString(), durationMinutes };
    state.sessionLog.unshift(completed);
    reset();
    return completed;
  }

  return {
    getState,
    start,
    pause,
    resume,
    reset,
    tick,
    completeSession
  };
})();
