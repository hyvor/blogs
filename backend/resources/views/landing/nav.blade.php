<nav>

	<div class="container">
		<a class="nav-brand" href="/">
			<img src="<?= config('blogs.logo') ?>" alt="Hyvor Blogs LOGO" width="30" height="30" />
			<span class="brand-name">Hyvor Blogs</span>
		</a>

		<div class="navbar-content">
			<div class="navbar-nav">
				<a class="nav-item nav-link{{ request()->is('customers') ? " active" : ""  }}" href="/customers">Customers</a>
				<a class="nav-item nav-link{{ request()->is('themes*') ? " active" : ""  }}" href="/themes">Themes</a>
				<a class="nav-item nav-link{{ request()->is('docs*') ? " active" : ""  }}" href="/docs">Docs</a>
				<a class="nav-item nav-link{{ request()->is('pricing') ? " active" : ""  }}" href="/pricing">Pricing</a>
				<a class="nav-item nav-link" href="/console" data-flashload-skip-link>Console</a>
			</div>
			<button id="nav-mobile" onclick="toggleNavBar(event)" aria-label="Navigation">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
				</svg>
			</button>
		</div>
	</div>

	<script>
		function toggleNavBar(e) {
			var navbar = document.querySelector('.navbar-nav');
			e && e.stopPropagation();
			if (navbar.classList.contains('open')) {
				navbar.classList.remove('open');
				window.removeEventListener('click', handleNavBarOut);
			} else {
				navbar.classList.add('open')
				window.addEventListener('click', handleNavBarOut)
			}
		}
		function handleNavBarOut(e) {
			if (!document.querySelector('.navbar-nav').contains(e.target)) {
				toggleNavBar();
			}
		}
	</script>

</nav>