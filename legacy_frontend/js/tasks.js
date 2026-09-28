window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.tasks = (() => {
  const STORAGE_KEY = window.StudyFlow.storage.KEYS.tasks;

  function getTasks() {
    return window.StudyFlow.storage.loadData(STORAGE_KEY, []);
  }

  function saveTasks(list) {
    window.StudyFlow.storage.saveData(STORAGE_KEY, list);
  }

  function upsert(task) {
    const tasks = getTasks();
    const index = tasks.findIndex((item) => item.id === task.id);
    if (index >= 0) {
      tasks[index] = { ...tasks[index], ...task };
    } else {
      tasks.push(task);
    }
    saveTasks(tasks);
    return task;
  }

  function removeTask(taskId) {
    const next = getTasks().filter((task) => task.id !== taskId);
    saveTasks(next);
    return next;
  }

  function createTask(data) {
    const task = {
      id: window.StudyFlow.storage.generateId('task'),
      title: data.title || 'Untitled task',
      description: data.description || '',
      subjectId: data.subjectId || '',
      priority: data.priority || 'Medium',
      deadline: data.deadline || '',
      estimatedMinutes: Number(data.estimatedMinutes || 45),
      progress: Number(data.progress || 0),
      status: data.status || 'Todo',
      difficulty: data.difficulty || 'Medium',
      createdAt: data.createdAt || new Date().toISOString(),
      completedAt: data.completedAt || '',
      notes: data.notes || '',
      isOverdue: false
    };
    upsert(task);
    return task;
  }

  function updateTask(taskId, patch) {
    const tasks = getTasks();
    const index = tasks.findIndex((task) => task.id === taskId);
    if (index === -1) return null;
    const updated = { ...tasks[index], ...patch };
    tasks[index] = updated;
    if (updated.status === 'Completed' && !updated.completedAt) {
      updated.completedAt = new Date().toISOString();
    }
    if (updated.status !== 'Completed') {
      updated.completedAt = '';
    }
    saveTasks(tasks);
    return updated;
  }

  function completeTask(taskId) {
    return updateTask(taskId, {
      status: 'Completed',
      progress: 100,
      completedAt: new Date().toISOString()
    });
  }

  function restoreTask(taskId) {
    return updateTask(taskId, {
      status: 'Todo',
      progress: 0,
      completedAt: ''
    });
  }

  function toggleTask(taskId) {
    const task = getTasks().find((item) => item.id === taskId);
    if (!task) return null;
    return task.status === 'Completed' ? restoreTask(taskId) : completeTask(taskId);
  }

  function getTaskById(taskId) {
    return getTasks().find((task) => task.id === taskId) || null;
  }

  function getTodayTasks() {
    const now = new Date();
    const dateString = now.toISOString().slice(0, 10);
    return getTasks().filter((task) => {
      if (!task.deadline) return false;
      return task.deadline.slice(0, 10) === dateString;
    });
  }

  function getPriorityTasks() {
    const tasks = getTasks();
    return tasks
      .filter((task) => task.status !== 'Completed')
      .sort((a, b) => (window.StudyFlow.priorityEngine.calculateTaskPriority(b).score || 0) - (window.StudyFlow.priorityEngine.calculateTaskPriority(a).score || 0));
  }

  return {
    STORAGE_KEY,
    getTasks,
    saveTasks,
    upsert,
    removeTask,
    createTask,
    updateTask,
    completeTask,
    restoreTask,
    toggleTask,
    getTaskById,
    getTodayTasks,
    getPriorityTasks
  };
})();
