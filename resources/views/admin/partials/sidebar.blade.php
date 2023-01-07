<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="https://placehold.it/160x160/00a65a/ffffff/&text=Name" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                @if(!empty($userData))
                    <p>{{ $userData['first_name'] . ' ' . $userData['last_name'] }}</p>
                @endif
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('adminDashboard') }}">
                    <i class="fa fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('keywords') }}">
                    <i class="fa fa-line-chart"></i>
                    <span>Keywords</span>
                </a>
            </li>
            <li>
                <a href="{{ route('csm') }}">
                    <i class="fa fa-user"></i>
                    <span>CSM</span>
                </a>
            </li>

        </ul>
    </section>
</aside>
