window.StudyFlow = window.StudyFlow || {};

window.StudyFlow.subjects = (() => {
  const STORAGE_KEY = window.StudyFlow.storage.KEYS.subjects;

  const defaultSubjects = [
    { id: 'subject_networking', name: 'Networking', icon: 'network', color: '#4f8cff', progress: 72, studyTime: 210, completed: 4 },
    { id: 'subject_programming', name: 'Programming', icon: 'code', color: '#8b5cf6', progress: 66, studyTime: 180, completed: 5 },
    { id: 'subject_mathematics', name: 'Mathematics', icon: 'calculator', color: '#10b981', progress: 58, studyTime: 150, completed: 3 },
    { id: 'subject_english', name: 'English', icon: 'book-open', color: '#f59e0b', progress: 63, studyTime: 120, completed: 2 }
  ];

  function getSubjects() {
    const stored = window.StudyFlow.storage.loadData(STORAGE_KEY, defaultSubjects);
    return Array.isArray(stored) && stored.length ? stored : defaultSubjects;
  }

  function saveSubjects(list) {
    window.StudyFlow.storage.saveData(STORAGE_KEY, list);
  }

  function createSubject(data) {
    const subject = {
      id: window.StudyFlow.storage.generateId('subject'),
      name: data.name || 'New Subject',
      icon: data.icon || 'book-open',
      color: data.color || '#4f8cff',
      progress: Number(data.progress || 0),
      studyTime: Number(data.studyTime || 0),
      completed: Number(data.completed || 0),
      createdAt: new Date().toISOString()
    };
    const list = getSubjects();
    list.push(subject);
    saveSubjects(list);
    return subject;
  }

  function updateSubject(subjectId, patch) {
    const subjects = getSubjects();
    const index = subjects.findIndex((subject) => subject.id === subjectId);
    if (index === -1) return null;
    subjects[index] = { ...subjects[index], ...patch };
    saveSubjects(subjects);
    return subjects[index];
  }

  function removeSubject(subjectId) {
    const next = getSubjects().filter((subject) => subject.id !== subjectId);
    saveSubjects(next);
    return next;
  }

  function getSubjectById(subjectId) {
    return getSubjects().find((subject) => subject.id === subjectId) || null;
  }

  return {
    STORAGE_KEY,
    defaultSubjects,
    getSubjects,
    saveSubjects,
    createSubject,
    updateSubject,
    removeSubject,
    getSubjectById
  };
})();
