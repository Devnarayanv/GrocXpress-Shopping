// Theme toggle: unified across pages
(function(){
	try {
		const STORAGE_KEY = 'grocxpress:theme'; // 'dark' or 'light'

		const log = (...args) => { try { console.debug('[gxc-theme]', ...args); } catch(e){} };

		const setTheme = (theme) => {
			if (theme === 'dark') {
				document.documentElement.classList.add('dark');
				document.body.classList.add('dark-mode');
				document.documentElement.classList.remove('light');
				document.body.classList.remove('light-mode');
			} else {
				document.documentElement.classList.remove('dark');
				document.body.classList.remove('dark-mode');
				document.documentElement.classList.add('light');
				document.body.classList.add('light-mode');
			}
			// No toggle elements are present (site forces dark)
			log('setTheme ->', theme);
		};

		// Support legacy keys used previously: 'theme' (index.php) and 'grocxpress:dark' (older script)
		const getStored = () => {
			const v = localStorage.getItem(STORAGE_KEY);
			if (v) return v;
			const legacy1 = localStorage.getItem('theme'); // e.g. 'dark-mode' or 'light-mode'
			if (legacy1) {
				if (legacy1 === 'dark-mode' || legacy1 === 'dark') return 'dark';
				if (legacy1 === 'light-mode' || legacy1 === 'light') return 'light';
			}
			const legacy2 = localStorage.getItem('grocxpress:dark'); // older code used '1' or '0'
			if (legacy2) return legacy2 === '1' ? 'dark' : 'light';
			return null;
		};

		// Toggle handler - toggles between 'dark' and 'light'
		const toggleTheme = () => {
			const current = getStored() || (document.body.classList.contains('dark-mode') ? 'dark' : 'light');
			const next = current === 'dark' ? 'light' : 'dark';
			localStorage.setItem(STORAGE_KEY, next);
			setTheme(next);
			log('toggled ->', next);
		};

		const setToggleIcons = (theme) => {
			const toggles = Array.from(document.querySelectorAll('#theme-toggle'));
			toggles.forEach(btn => {
				try {
					if (btn.tagName === 'BUTTON') {
						btn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
						btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
					} else {
						btn.textContent = theme === 'dark' ? '🌙' : '☀️';
					}
				} catch (e) {}
			});
		};

		// Create floating toggle if not already present
		const createFloatingToggle = (initialTheme) => {
			if (document.getElementById('gxc-dark-toggle')) return;
			const btn = document.createElement('button');
			btn.setAttribute('aria-label','Toggle dark mode');
			btn.id = 'gxc-dark-toggle';
			btn.style.position = 'fixed';
			btn.style.right = '16px';
			btn.style.bottom = '16px';
			btn.style.zIndex = 99999;
			btn.style.border = 'none';
			btn.style.padding = '10px 12px';
			btn.style.borderRadius = '999px';
			btn.style.cursor = 'pointer';
			btn.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
			btn.style.background = initialTheme === 'dark' ? '#1f2937' : '#fff';
			btn.style.color = initialTheme === 'dark' ? '#fff' : '#111';
			btn.innerHTML = initialTheme === 'dark' ? '🌙' : '☀️';
			btn.addEventListener('click', toggleTheme);
			document.body.appendChild(btn);
		};

		const init = () => {
			// determine initial theme: stored preference > body/html classes > default light
			let stored = getStored();
			if (!stored) {
				if (document.documentElement.classList.contains('dark') || document.body.classList.contains('dark-mode')) stored = 'dark';
				else if (document.documentElement.classList.contains('light') || document.body.classList.contains('light-mode')) stored = 'light';
				else stored = 'light';
				localStorage.setItem(STORAGE_KEY, stored);
			}
			setTheme(stored);

			// If there is a header toggle (#theme-toggle) wire it up so index.php's button works everywhere
			const headerToggle = document.getElementById('theme-toggle') || document.querySelector('[data-theme-toggle]');
			if (headerToggle) {
				// wire all theme-toggle buttons (there may be multiple on page)
				Array.from(document.querySelectorAll('#theme-toggle')).forEach(btn => {
					btn.addEventListener('click', (e) => {
						e.preventDefault();
						toggleTheme();
					});
				});
			}

			// Sync theme across tabs/windows
			window.addEventListener('storage', (ev) => {
				if (!ev.key) return;
				if (ev.key === STORAGE_KEY || ev.key === 'theme' || ev.key === 'grocxpress:dark') {
					const newVal = getStored();
					if (newVal) setTheme(newVal);
				}
			});

			// Ensure icons reflect the initial theme
			setToggleIcons(getStored() || (document.body.classList.contains('dark-mode') ? 'dark' : 'light'));

			// Update icons whenever theme is set via setTheme
			const originalSetTheme = setTheme;
			// wrap setTheme to also update icons
			window.__gxc_setTheme = (theme) => {
				originalSetTheme(theme);
				setToggleIcons(theme);
			};
		};

		// Run init immediately (script is included with defer so DOM is available). Also keep DOMContentLoaded fallback.
		try { init(); } catch(e) { log('init immediate failed', e); }
		document.addEventListener('DOMContentLoaded', ()=>{ try { init(); } catch(e) { log('init on DOMContentLoaded failed', e); } });
	} catch (e) { console.error('Theme init error', e); }
})();
