<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
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