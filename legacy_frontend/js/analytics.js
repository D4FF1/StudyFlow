window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.analytics = (() => {
  function getStudySessions() {
    return window.StudyFlow.storage.loadData(window.StudyFlow.storage.KEYS.sessions, []);
  }

  function getTasks() {
    return window.StudyFlow.storage.loadData(window.StudyFlow.storage.KEYS.tasks, []);
  }

  function toDayKey(date) {
    const d = new Date(date);
    return d.toISOString().slice(0, 10);
  }

  function getWeeklyStudyMinutes() {
    const sessionData = getStudySessions();
    const totals = Array.from({ length: 7 }, (_, index) => {
      const date = new Date();
      date.setDate(date.getDate() - (6 - index));
      const key = toDayKey(date);
      const minuteTotal = sessionData.reduce((sum, session) => {
        const sessionDate = new Date(session.createdAt || new Date());
        const sessionKey = toDayKey(sessionDate);
        return sessionKey === key ? sum + Number(session.duration || 0) : sum;
      }, 0);
      return { label: date.toLocaleDateString('en-US', { weekday: 'short' }), minutes: minuteTotal };
    });
    return totals;
  }

  function getTaskCompletionByDay() {
    const tasks = getTasks();
    const totals = Array.from({ length: 7 }, (_, index) => {
      const date = new Date();
      date.setDate(date.getDate() - (6 - index));
      const key = toDayKey(date);
      const count = tasks.filter((task) => task.completedAt && toDayKey(task.completedAt) === key).length;
      return { label: date.toLocaleDateString('en-US', { weekday: 'short' }), count };
    });
    return totals;
  }

  function getSubjectDistribution() {
    const tasks = getTasks();
    const subjectMap = {};
    const subjects = window.StudyFlow.subjects.getSubjects();

    subjects.forEach((subject) => {
      subjectMap[subject.id] = { name: subject.name, count: 0 };
    });

    tasks.forEach((task) => {
      if (!task.subjectId) return;
      if (!subjectMap[task.subjectId]) {
        subjectMap[task.subjectId] = { name: task.subjectName || 'Unassigned', count: 0 };
      }
      subjectMap[task.subjectId].count += 1;
    });

    return Object.values(subjectMap).filter((item) => item.count > 0 || item.name);
  }

  function calculateCompletionRate() {
    const tasks = getTasks();
    if (!tasks.length) return 0;
    const completed = tasks.filter((task) => task.status === 'Completed').length;
    return Math.round((completed / tasks.length) * 100);
  }

  function calculateStreaks() {
    const tasks = getTasks();
    const sessions = getStudySessions();
    const activeDates = new Set();

    tasks.forEach((task) => {
      if (task.status === 'Completed' && task.completedAt) {
        activeDates.add(toDayKey(task.completedAt));
      }
    });

    sessions.forEach((session) => {
      if (session.createdAt) {
        activeDates.add(toDayKey(session.createdAt));
      }
    });

    const sortedDates = [...activeDates].sort().reverse();
    let streak = 0;
    let previous = null;

    for (const date of sortedDates) {
      const day = new Date(date);
      const today = new Date();
      const diff = Math.round((window.StudyFlow.utils.startOfDay(today) - window.StudyFlow.utils.startOfDay(day)) / (1000 * 60 * 60 * 24));
      if (diff === 0 || diff === streak && previous === null) {
        streak += 1;
      } else if (diff === streak + 1) {
        streak += 1;
      } else {
        break;
      }
      previous = date;
    }

    let longest = 0;
    const uniqueDates = [...activeDates].sort();
    let current = 0;
    for (let i = 1; i < uniqueDates.length; i++) {
      const prev = new Date(uniqueDates[i - 1]);
      const curr = new Date(uniqueDates[i]);
      const diff = Math.round((curr - prev) / (1000 * 60 * 60 * 24));
      if (diff === 1) current += 1;
      else current = 0;
      longest = Math.max(longest, current + 1);
    }
    if (uniqueDates.length === 1) longest = 1;

    return {
      current: streak || 0,
      longest: longest || 0
    };
  }

  function getInsights() {
    const weekly = getWeeklyStudyMinutes();
    const totalMinutes = weekly.reduce((sum, item) => sum + item.minutes, 0);
    const avgMinutes = weekly.length ? Math.round(totalMinutes / weekly.length) : 0;
    const completion = calculateCompletionRate();
    const subjectData = getSubjectDistribution();
    const mostStudied = subjectData.reduce((top, item) => (item.count > (top?.count || 0) ? item : top), null) || { name: 'None', count: 0 };

    let insightText = 'Keep building consistency with a short study sprint today.';
    if (completion >= 80) insightText = 'Your completion rate is strong — keep the momentum going.';
    else if (completion < 50) insightText = 'A goal for this week: complete a few high-impact tasks before the end of the day.';

    const maxDay = weekly.reduce((best, item) => (item.minutes > best.minutes ? item : best), { label: 'Mon', minutes: 0 });

    return {
      totalMinutes,
      averageMinutes: avgMinutes,
      mostProductiveDay: maxDay.label,
      mostStudiedSubject: mostStudied.name,
      completionRate: completion,
      insightText
    };
  }

  return {
    getWeeklyStudyMinutes,
    getTaskCompletionByDay,
    getSubjectDistribution,
    calculateCompletionRate,
    calculateStreaks,
    getInsights
  };
})();
