window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.goals = (() => {
  const STORAGE_KEY = window.StudyFlow.storage.KEYS.goals;

  function getGoals() {
    return window.StudyFlow.storage.loadData(STORAGE_KEY, []);
  }

  function saveGoals(list) {
    window.StudyFlow.storage.saveData(STORAGE_KEY, list);
  }

  function createGoal(data) {
    const goal = {
      id: window.StudyFlow.storage.generateId('goal'),
      title: data.title || 'New Goal',
      description: data.description || '',
      deadline: data.deadline || '',
      target: data.target || 100,
      currentProgress: Number(data.currentProgress || 0),
      category: data.category || 'Study',
      milestones: Array.isArray(data.milestones) ? data.milestones : [],
      createdAt: new Date().toISOString()
    };
    const list = getGoals();
    list.push(goal);
    saveGoals(list);
    return goal;
  }

  function updateGoal(goalId, patch) {
    const goals = getGoals();
    const index = goals.findIndex((goal) => goal.id === goalId);
    if (index === -1) return null;
    goals[index] = { ...goals[index], ...patch };
    saveGoals(goals);
    return goals[index];
  }

  function removeGoal(goalId) {
    const next = getGoals().filter((goal) => goal.id !== goalId);
    saveGoals(next);
    return next;
  }

  function calculateGoalProgress(goal) {
    const target = Number(goal.target || 100);
    const value = Number(goal.currentProgress || 0);
    return Math.min(100, Math.round((value / target) * 100));
  }

  return {
    STORAGE_KEY,
    getGoals,
    saveGoals,
    createGoal,
    updateGoal,
    removeGoal,
    calculateGoalProgress
  };
})();
