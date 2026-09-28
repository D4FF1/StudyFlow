window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.notifications = (() => {
  const storageKey = window.StudyFlow.storage.KEYS.notifications;

  function getNotifications() {
    return window.StudyFlow.storage.loadData(storageKey, []);
  }

  function addNotification(message, type = 'info') {
    const notifications = getNotifications();
    const item = {
      id: window.StudyFlow.storage.generateId('note'),
      type,
      message,
      createdAt: new Date().toISOString()
    };
    notifications.unshift(item);
    window.StudyFlow.storage.saveData(storageKey, notifications.slice(0, 20));
    return item;
  }

  function clearNotifications() {
    window.StudyFlow.storage.saveData(storageKey, []);
  }

  function renderPanel() {
    const panel = document.getElementById('notification-panel');
    if (!panel) return;
    const notes = getNotifications();
    if (!notes.length) {
      panel.innerHTML = `
        <div class="p-3 text-sm text-slate-500 dark:text-slate-400">No notifications yet.</div>
      `;
      return;
    }

    panel.innerHTML = notes.slice(0, 6).map((note) => `
      <div class="mb-2 rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-start gap-2">
          <span class="mt-1 h-2.5 w-2.5 rounded-full ${note.type === 'success' ? 'bg-emerald-500' : note.type === 'warning' ? 'bg-amber-500' : note.type === 'danger' ? 'bg-rose-500' : 'bg-blue-500'}"></span>
          <div class="text-slate-700 dark:text-slate-200">${window.StudyFlow.utils.escapeHtml(note.message)}</div>
        </div>
      </div>
    `).join('');
  }

  return {
    getNotifications,
    addNotification,
    clearNotifications,
    renderPanel
  };
})();
