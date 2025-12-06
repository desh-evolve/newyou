{{-- resources/views/layouts/partials/sidebar.blade.php --}}

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

                <!-- ============================================== -->
                <!-- APPOINTMENTS SECTION -->
                <!-- ============================================== -->
                <li class="nav-header">APPOINTMENTS</li>

                <!-- Appointments Menu -->
                <li class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>
                            Appointments
                            <i class="right fas fa-angle-left"></i>
                            @if(($sidebarData['pending_appointments'] ?? 0) > 0)
                                <span class="badge badge-warning right mr-2">{{ $sidebarData['pending_appointments'] }}</span>
                            @endif
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.appointments.index') }}" class="nav-link {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }}">
                                <i class="far fa-calendar-alt nav-icon"></i>
                                <p>All Appointments</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.appointments.calendar') }}" class="nav-link {{ request()->routeIs('admin.appointments.calendar') ? 'active' : '' }}">
                                <i class="fas fa-calendar nav-icon"></i>
                                <p>Calendar View</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.appointments.today') }}" class="nav-link {{ request()->routeIs('admin.appointments.today') ? 'active' : '' }}">
                                <i class="fas fa-calendar-day nav-icon"></i>
                                <p>
                                    Today
                                    @if(($sidebarData['today_appointments'] ?? 0) > 0)
                                        <span class="badge badge-info right">{{ $sidebarData['today_appointments'] }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.appointments.create') }}" class="nav-link {{ request()->routeIs('admin.appointments.create') ? 'active' : '' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>New Appointment</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Time Slots -->
                <li class="nav-item {{ request()->routeIs('admin.time-slots.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.time-slots.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-clock"></i>
                        <p>
                            Time Slots
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.time-slots.index') }}" class="nav-link {{ request()->routeIs('admin.time-slots.index') ? 'active' : '' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>All Slots</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.time-slots.calendar') }}" class="nav-link {{ request()->routeIs('admin.time-slots.calendar') ? 'active' : '' }}">
                                <i class="fas fa-th nav-icon"></i>
                                <p>Slots Calendar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.time-slots.create') }}" class="nav-link {{ request()->routeIs('admin.time-slots.create') ? 'active' : '' }}">
                                <i class="fas fa-cog nav-icon"></i>
                                <p>Generate Slots</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Clients -->
                <li class="nav-item {{ request()->routeIs('admin.clients.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>
                            Clients
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.clients.index') }}" class="nav-link {{ request()->routeIs('admin.clients.index') ? 'active' : '' }}">
                                <i class="fas fa-users nav-icon"></i>
                                <p>All Clients</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.clients.create') }}" class="nav-link {{ request()->routeIs('admin.clients.create') ? 'active' : '' }}">
                                <i class="fas fa-user-plus nav-icon"></i>
                                <p>Add Client</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ============================================== -->
                <!-- CONTENT SECTION -->
                <!-- ============================================== -->
                <li class="nav-header">CONTENT</li>
                
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

                <!-- ============================================== -->
                <!-- SERVICES SECTION -->
                <!-- ============================================== -->
                <li class="nav-header">SERVICES</li>

                <!-- Services Menu -->
                <li class="nav-item {{ request()->routeIs('admin.services.*') || request()->routeIs('admin.packages.*') || request()->routeIs('admin.package-services.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.services.*') || request()->routeIs('admin.packages.*') || request()->routeIs('admin.package-services.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-concierge-bell"></i>
                        <p>
                            Services & Packages
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.services.index') }}" class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                                <i class="fas fa-boxes nav-icon"></i>
                                <p>Services</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                                <i class="fas fa-box nav-icon"></i>
                                <p>Packages</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.package-services.index') }}" class="nav-link {{ request()->routeIs('admin.package-services.*') ? 'active' : '' }}">
                                <i class="fas fa-link nav-icon"></i>
                                <p>Package Services</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ============================================== -->
                <!-- ADMINISTRATION SECTION -->
                <!-- ============================================== -->
                <li class="nav-header">ADMINISTRATION</li>

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

                <!-- Notifications -->
                <li class="nav-item">
                    <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>
                            Notifications
                            @if(($sidebarData['unread_notifications'] ?? 0) > 0)
                                <span class="badge badge-danger right">{{ $sidebarData['unread_notifications'] }}</span>
                            @endif
                        </p>
                    </a>
                </li>

                <!-- Testimonials -->
                <li class="nav-item">
                    <a href="{{ route('admin.testimonials.index') }}" class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-star"></i>
                        <p>
                            Testimonials
                        </p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>