(function() {
	var root = document.documentElement;
	var key = 'bf_theme';
	var btn = document.getElementById('themeToggle');
	var saved = localStorage.getItem(key);
	if (saved === 'light') {
		root.setAttribute('data-theme', 'light');
	}
	if (btn) {
		btn.addEventListener('click', function() {
			var isLight = root.getAttribute('data-theme') === 'light';
			if (isLight) {
				root.removeAttribute('data-theme');
				localStorage.setItem(key, 'dark');
			} else {
				root.setAttribute('data-theme', 'light');
				localStorage.setItem(key, 'light');
			}
		});
	}
})();


