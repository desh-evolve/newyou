<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link" target="_blank">
                <i class="fas fa-external-link-alt mr-1"></i> View Site
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">3 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file mr-2"></i> New post published
                    <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>

        <!-- User Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                <i class="fas fa-user-circle mr-1"></i> {{ auth()->user()->name ?? 'Admin' }}
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user mr-2"></i> Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>