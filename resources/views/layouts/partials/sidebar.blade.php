<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <i class="fas fa-cogs brand-image img-circle elevation-3 ml-3" style="opacity: .8; font-size: 24px; line-height: 33px;"></i>
        <span class="brand-text font-weight-light">Admin Panel</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-light"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name ?? 'Admin' }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Blog Menu -->
                <li class="nav-item {{ request()->routeIs('admin.blog.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-blog"></i>
                        <p>
                            Blog
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.blog.posts.index') }}" class="nav-link {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
                                <i class="far fa-file-alt nav-icon"></i>
                                <p>Posts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.blog.categories.index') }}" class="nav-link {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
                                <i class="far fa-folder nav-icon"></i>
                                <p>Categories</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.blog.tags.index') }}" class="nav-link {{ request()->routeIs('admin.blog.tags.*') ? 'active' : '' }}">
                                <i class="fas fa-tags nav-icon"></i>
                                <p>Tags</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Services Menu -->
                <li class="nav-item {{ request()->routeIs('admin.services.*') || request()->routeIs('admin.packages.*') || request()->routeIs('admin.package-services.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.services.*') || request()->routeIs('admin.packages.*') || request()->routeIs('admin.package-services.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list"></i>
                        <p>
                            Services
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!-- Services -->
                        <li class="nav-item">
                            <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-boxes"></i>
                                <p>Services</p>
                            </a>
                        </li>
                        
                        <!-- Packages -->
                        <li class="nav-item">
                            <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-box"></i>
                                <p>Packages</p>
                            </a>
                        </li>

                         <!-- Package Services -->
                        <li class="nav-item {{ request()->routeIs('admin.package-services.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.package-services.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-link"></i>
                                <p>
                                    Package Services
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.package-services.index') }}" class="nav-link {{ request()->routeIs('admin.package-services.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Assignments</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.package-services.assign') }}" class="nav-link {{ request()->routeIs('admin.package-services.assign') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Assign Services</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- User Management -->
                <li class="nav-item {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>
                            User Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users nav-icon"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i class="fas fa-user-tag nav-icon"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                <i class="fas fa-key nav-icon"></i>
                                <p>Permissions</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>