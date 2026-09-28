window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.search = (() => {
  function searchAll(query) {
    const q = String(query || '').trim().toLowerCase();
    if (!q) {
      return { tasks: [], subjects: [], goals: [] };
    }

    const tasks = window.StudyFlow.tasks.getTasks().filter((task) =>
      task.title.toLowerCase().includes(q) ||
      (task.description || '').toLowerCase().includes(q) ||
      (task.notes || '').toLowerCase().includes(q)
    );

    const subjects = window.StudyFlow.subjects.getSubjects().filter((subject) =>
      subject.name.toLowerCase().includes(q)
    );

    const goals = window.StudyFlow.goals.getGoals().filter((goal) =>
      goal.title.toLowerCase().includes(q) ||
      (goal.description || '').toLowerCase().includes(q)
    );

    return { tasks, subjects, goals };
  }

  return { searchAll };
})();
