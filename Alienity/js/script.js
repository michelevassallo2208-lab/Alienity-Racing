document.addEventListener('DOMContentLoaded', () => {
  const toggleButtons = document.querySelectorAll('[data-toggle]');
  toggleButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-toggle');
      if (!targetId) return;
      const target = document.getElementById(targetId);
      if (target) {
        target.classList.toggle('is-visible');
      }
    });
  });

  const tabButtons = document.querySelectorAll('[data-panel-target]');
  if (tabButtons.length > 0) {
    const panels = document.querySelectorAll('[data-panel]');
    tabButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-panel-target');
        if (!targetId) return;

        tabButtons.forEach((btn) => {
          btn.classList.toggle('is-active', btn === button);
        });

        panels.forEach((panel) => {
          panel.classList.toggle('is-active', panel.getAttribute('data-panel') === targetId);
        });
      });
    });
  }
});
