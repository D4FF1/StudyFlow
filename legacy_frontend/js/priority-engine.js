window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.priorityEngine = (() => {
  function calculateTaskPriority(task, now = new Date()) {
    const days = task.deadline ? Math.max(0, window.StudyFlow.utils.daysBetween(now, new Date(task.deadline))) : 30;
    const deadlineScore = days <= 0 ? 100 : days <= 1 ? 86 : days <= 3 ? 68 : days <= 7 ? 48 : 25;
    const importanceMap = { Low: 25, Medium: 45, High: 70, Urgent: 90 };
    const importanceScore = importanceMap[task.priority] || 45;
    const difficultyMap = { Easy: 20, Medium: 50, Hard: 75, Intense: 90 };
    const difficultyScore = difficultyMap[task.difficulty] || 50;
    const urgencyScore = task.status === 'Completed' ? 5 : task.isOverdue ? 92 : task.priority === 'Urgent' ? 80 : 40;
    const remainingWorkScore = Math.max(0, 100 - Number(task.progress || 0));

    const raw = (
      deadlineScore * 0.35 +
      importanceScore * 0.25 +
      difficultyScore * 0.15 +
      urgencyScore * 0.15 +
      remainingWorkScore * 0.10
    );

    const score = Math.max(0, Math.min(100, Math.round(raw)));
    const reasons = [];

    if (days <= 1) reasons.push('Deadline is close');
    if ((task.progress || 0) < 55) reasons.push('Progress is still low');
    if (task.priority === 'High' || task.priority === 'Urgent') reasons.push('High importance');
    if (task.estimatedMinutes <= 90) reasons.push('Estimated duration fits your available time');
    if (task.status === 'In Progress') reasons.push('You already started this task');
    if (!reasons.length) reasons.push('This is a solid next step');

    return {
      score,
      reasons: reasons.slice(0, 4),
      deadlineDays: days
    };
  }

  function getRecommendedTask(tasks = []) {
    const activeTasks = tasks.filter((task) => task.status !== 'Completed');
    if (!activeTasks.length) return null;

    const now = new Date();
    const scored = activeTasks.map((task) => {
      const info = calculateTaskPriority(task, now);
      return { task, ...info };
    }).sort((a, b) => b.score - a.score);

    return scored[0];
  }

  function explainTask(task) {
    const info = calculateTaskPriority(task, new Date());
    return {
      ...info,
      title: task.title,
      scoreLabel: `${info.score}`
    };
  }

  return {
    calculateTaskPriority,
    getRecommendedTask,
    explainTask
  };
})();
