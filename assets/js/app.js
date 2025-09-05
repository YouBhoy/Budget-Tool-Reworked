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
	
	// Form validation and UX improvements
	var forms = document.querySelectorAll('form');
	forms.forEach(function(form) {
		form.addEventListener('submit', function(e) {
			var submitBtn = form.querySelector('button[type="submit"]');
			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.textContent = 'Processing...';
				
				// Re-enable button after 3 seconds as fallback
				setTimeout(function() {
					submitBtn.disabled = false;
					submitBtn.textContent = submitBtn.getAttribute('data-original-text') || 'Submit';
				}, 3000);
			}
		});
		
		// Store original button text
		var submitBtn = form.querySelector('button[type="submit"]');
		if (submitBtn && !submitBtn.getAttribute('data-original-text')) {
			submitBtn.setAttribute('data-original-text', submitBtn.textContent);
		}
	});
	
	// Input focus effects
	var inputs = document.querySelectorAll('input, textarea, select');
	inputs.forEach(function(input) {
		input.addEventListener('focus', function() {
			this.parentElement.classList.add('focused');
		});
		
		input.addEventListener('blur', function() {
			this.parentElement.classList.remove('focused');
		});
	});
	
	// Auto-hide success messages
	var successMessages = document.querySelectorAll('.success-message');
	successMessages.forEach(function(msg) {
		setTimeout(function() {
			msg.style.opacity = '0';
			msg.style.transform = 'translateY(-10px)';
			setTimeout(function() { msg.remove(); }, 300);
		}, 5000);
	});
	
	// Tab switching functionality
	window.switchTab = function(tabName) {
		// Hide all tab contents
		var tabContents = document.querySelectorAll('.tab-content');
		tabContents.forEach(function(content) {
			content.style.display = 'none';
		});
		
		// Remove active class from all tab buttons
		var tabButtons = document.querySelectorAll('.tab-button');
		tabButtons.forEach(function(button) {
			button.classList.remove('active');
			button.style.borderBottom = '2px solid transparent';
			button.style.color = 'var(--muted)';
		});
		
		// Show selected tab content
		var selectedTab = document.getElementById(tabName + '-tab');
		if (selectedTab) {
			selectedTab.style.display = 'block';
		}
		
		// Add active class to selected tab button
		var selectedButton = document.getElementById('tab-' + tabName);
		if (selectedButton) {
			selectedButton.classList.add('active');
			selectedButton.style.borderBottom = '2px solid var(--accent)';
			selectedButton.style.color = 'var(--accent)';
		}
	};
})();


