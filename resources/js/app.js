import './bootstrap';
import 'trix'
import 'trix/dist/trix.css';

import 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom/model';

// plugins you use:
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/code';
import 'tinymce/plugins/table';
import Alpine from 'alpinejs';

window.Alpine = Alpine;



// init after DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Dark mode management
  const initializeDarkMode = () => {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Determine if dark mode should be active
    const shouldBeDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark);

    if (shouldBeDark) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }

    return shouldBeDark;
  };

  // Initialize dark mode on page load
  initializeDarkMode();

  // Global function to toggle dark mode
  window.toggleDarkMode = () => {
    const isCurrentlyDark = document.documentElement.classList.contains('dark');
    const newTheme = isCurrentlyDark ? 'light' : 'dark';

    localStorage.setItem('theme', newTheme);

    if (newTheme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }

    // Update button text
    updateDarkModeButtonText();

    // Re-initialize TinyMCE with new theme
    setTimeout(() => {
      location.reload();
    }, 100);
  };

  // Function to update the dark mode button text
  const updateDarkModeButtonText = () => {
    const buttonText = document.getElementById('darkModeText');
    if (buttonText) {
      const isDark = document.documentElement.classList.contains('dark');
      buttonText.textContent = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }
  };

  // Initialize button text on page load
  updateDarkModeButtonText();

  // Listen for system preference changes (only if no manual preference is set)
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    const savedTheme = localStorage.getItem('theme');
    if (!savedTheme) {
      if (e.matches) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
      // Update button text
      updateDarkModeButtonText();
      // Re-initialize TinyMCE with new theme
      setTimeout(() => {
        location.reload();
      }, 100);
    }
  });

  // Check if dark mode is active for TinyMCE
  const isDark = document.documentElement.classList.contains('dark') ||
                 (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !localStorage.getItem('theme'));

  tinymce.init({
    selector: 'textarea.rich-desc',
    license_key: 'gpl',
    menubar: false,
    plugins: 'lists link code table advlist',
    toolbar: 'bold italic forecolor backcolor | bullist numlist | link | removeformat | code',
    height: 220,
    base_url: '/tinymce',
    skin: isDark ? 'oxide-dark' : 'oxide',
  });
});

  // init one editor by id (call when the row opens)
window.initTinyById = function (id) {
  if (!window.tinymce) return;                 // TinyMCE not loaded yet
  if (tinymce.get(id)) return;                 // already initialized

  // Check if dark mode is active
  const isDark = document.documentElement.classList.contains('dark') ||
                 (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !localStorage.getItem('theme'));

  tinymce.init({
    selector: '#'+id,
    license_key: 'gpl',
    menubar: false,
    plugins: 'lists link code table advlist',
    toolbar: 'bold italic forecolor backcolor | bullist numlist | link | removeformat | code',
    height: 220,
    base_url: '/tinymce',
    skin: isDark ? 'oxide-dark' : 'oxide',
    setup(editor){
      // ensure the <textarea> is kept in sync
      editor.on('change input', () => editor.save());
    }
  });
};

// destroy when row closes (prevents duplicate editors)
window.destroyTinyById = function (id) {
  if (window.tinymce) {
    const ed = tinymce.get(id);
    if (ed) ed.remove();
  }
};

Alpine.start();