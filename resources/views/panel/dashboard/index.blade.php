@extends('panel.layouts.master')

@section('wrapper')
    <div class="dashboard-content">
        <div class="welcome-card">
            <h2>Welcome back, {{ auth()->user()->name }}!</h2>
            <p>Your dashboard summary...</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>1,254</h3>
                <p>Total Orders</p>
                <div class="progress-text">
                    <span>Growth</span>
                    <span>+12%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                </div>
            </div>

            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>5,421</h3>
                <p>Total Customers</p>
                <div class="progress-text">
                    <span>Growth</span>
                    <span>+8%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
                </div>
            </div>

            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>$24,569</h3>
                <p>Total Revenue</p>
                <div class="progress-text">
                    <span>Growth</span>
                    <span>+15%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 85%"></div>
                </div>
            </div>

            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <h3>68.2%</h3>
                <p>Conversion Rate</p>
                <div class="progress-text">
                    <span>Growth</span>
                    <span>+5%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 68%"></div>
                </div>
            </div>
        </div>

        <div class="row">
{{--            <div class="col-lg-8">--}}
{{--                <div class="chart-container">--}}
{{--                    <div class="chart-header">--}}
{{--                        <h3>Revenue Overview</h3>--}}
{{--                        <div class="chart-actions">--}}
{{--                            <select>--}}
{{--                                <option>Last 7 Days</option>--}}
{{--                                <option>Last 30 Days</option>--}}
{{--                                <option>Last 90 Days</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div id="revenueChart" style="height: 300px;">--}}
{{--                        <!-- Chart would be rendered here with a library like Chart.js -->--}}
{{--                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">--}}
{{--                            <div class="text-center">--}}
{{--                                <i class="fas fa-chart-bar fa-3x mb-3"></i>--}}
{{--                                <p>Revenue chart visualization would appear here</p>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="col-lg-4">--}}
{{--                <div class="recent-activities">--}}
{{--                    <h3 class="mb-4">Recent Activities</h3>--}}

{{--                    <div class="activity-item">--}}
{{--                        <div class="activity-icon user">--}}
{{--                            <i class="fas fa-user-plus"></i>--}}
{{--                        </div>--}}
{{--                        <div class="activity-content">--}}
{{--                            <h5>New user registered</h5>--}}
{{--                            <p>John Doe joined the platform</p>--}}
{{--                        </div>--}}
{{--                        <div class="activity-time">2 min ago</div>--}}
{{--                    </div>--}}

{{--                    <div class="activity-item">--}}
{{--                        <div class="activity-icon order">--}}
{{--                            <i class="fas fa-shopping-bag"></i>--}}
{{--                        </div>--}}
{{--                        <div class="activity-content">--}}
{{--                            <h5>New order received</h5>--}}
{{--                            <p>Order #ORD-7854 placed</p>--}}
{{--                        </div>--}}
{{--                        <div class="activity-time">15 min ago</div>--}}
{{--                    </div>--}}

{{--                    <div class="activity-item">--}}
{{--                        <div class="activity-icon payment">--}}
{{--                            <i class="fas fa-credit-card"></i>--}}
{{--                        </div>--}}
{{--                        <div class="activity-content">--}}
{{--                            <h5>Payment received</h5>--}}
{{--                            <p>$245.99 payment completed</p>--}}
{{--                        </div>--}}
{{--                        <div class="activity-time">1 hour ago</div>--}}
{{--                    </div>--}}

{{--                    <div class="activity-item">--}}
{{--                        <div class="activity-icon system">--}}
{{--                            <i class="fas fa-cog"></i>--}}
{{--                        </div>--}}
{{--                        <div class="activity-content">--}}
{{--                            <h5>System updated</h5>--}}
{{--                            <p>Version 2.3.5 deployed</p>--}}
{{--                        </div>--}}
{{--                        <div class="activity-time">3 hours ago</div>--}}
{{--                    </div>--}}

{{--                    <div class="activity-item">--}}
{{--                        <div class="activity-icon user">--}}
{{--                            <i class="fas fa-user-check"></i>--}}
{{--                        </div>--}}
{{--                        <div class="activity-content">--}}
{{--                            <h5>Profile updated</h5>--}}
{{--                            <p>Sarah Johnson updated her profile</p>--}}
{{--                        </div>--}}
{{--                        <div class="activity-time">5 hours ago</div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
@endsection
