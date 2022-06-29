<nav>

	<div class="container">
		<a class="nav-brand" href="/">
			<img src="<?= config('blogs.logo') ?>" alt="Hyvor Blogs LOGO" width="30" height="30" />
			<span class="brand-name">Hyvor Blogs</span>
		</a>

		<div class="navbar-content">
			<div class="navbar-nav">
				<a class="nav-item nav-link{{ request()->is('themes*') ? " active" : ""  }}" href="/themes">Themes</a>
				<a class="nav-item nav-link{{ request()->is('docs*') ? " active" : ""  }}" href="/docs">Docs</a>
				<a class="nav-item nav-link{{ request()->is('pricing') ? " active" : ""  }}" href="/pricing">Pricing</a>
				<a class="nav-item nav-link" href="/console" data-flashload-skip>Console</a>
			</div>
		</div>
	</div>

</nav>