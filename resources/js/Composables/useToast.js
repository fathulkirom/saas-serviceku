let toastInstance = null;

export function setToastInstance(instance) {
  toastInstance = instance;
}

export function useToast() {
  function show(type, message, title) {
    if (toastInstance) {
      toastInstance.add(type, message, title);
    }
  }

  return {
    success: (msg, title) => show('success', msg, title),
    error: (msg, title) => show('error', msg, title),
    warning: (msg, title) => show('warning', msg, title),
    info: (msg, title) => show('info', msg, title),
  };
}
