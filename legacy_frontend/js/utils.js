window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.utils = (() => {
  const dayMs = 24 * 60 * 60 * 1000;

  function escapeHtml(value = '') {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatDate(dateString, options = { month: 'short', day: 'numeric' }) {
    if (!dateString) return 'No deadline';
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return 'No deadline';
    return new Intl.DateTimeFormat('en-US', options).format(date);
  }

  function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }).format(date);
  }

  function formatMinutes(minutes) {
    if (!Number.isFinite(minutes) || minutes <= 0) return '0m';
    const hrs = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hrs > 0 && mins > 0) return `${hrs}h ${mins}m`;
    if (hrs > 0) return `${hrs}h`;
    return `${mins}m`;
  }

  function formatWeekday(dateString) {
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat('en-US', { weekday: 'short' }).format(date);
  }

  function formatSubjectProgress(subject) {
    const tasks = window.StudyFlow.tasks.getTasks().filter((task) => task.subjectId === subject.id);
    if (!tasks.length) return 0;
    const total = tasks.reduce((sum, task) => sum + Number(task.progress || 0), 0) / tasks.length;
    return Math.round(total);
  }

  function startOfDay(date = new Date()) {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    return d;
  }

  function addDays(date, days) {
    const output = new Date(date);
    output.setDate(output.getDate() + days);
    return output;
  }

  function isSameDay(a, b) {
    const dateA = new Date(a);
    const dateB = new Date(b);
    return dateA.getFullYear() === dateB.getFullYear() && dateA.getMonth() === dateB.getMonth() && dateA.getDate() === dateB.getDate();
  }

  function daysBetween(start, end) {
    const first = startOfDay(start);
    const second = startOfDay(end);
    return Math.round((second - first) / dayMs);
  }

  function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
  }

  function priorityColor(priority) {
    const map = {
      Low: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
      Medium: 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
      High: 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300',
      Urgent: 'bg-violet-100 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300'
    };
    return map[priority] || map.Medium;
  }

  function getProgressBarStyle(percent) {
    return {
      width: `${clamp(percent, 0, 100)}%`
    };
  }

  function uniqueBy(arr, key) {
    const seen = new Set();
    return arr.filter((item) => {
      const value = item[key];
      if (seen.has(value)) return false;
      seen.add(value);
      return true;
    });
  }

  return {
    escapeHtml,
    formatDate,
    formatDateTime,
    formatMinutes,
    formatWeekday,
    formatSubjectProgress,
    startOfDay,
    addDays,
    isSameDay,
    daysBetween,
    clamp,
    priorityColor,
    getProgressBarStyle,
    uniqueBy
  };
})();
