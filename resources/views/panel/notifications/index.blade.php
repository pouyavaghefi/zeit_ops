@extends('panel.layouts.master')

@section('wrapper')
    <div class="dashboard-content">
        <div class="notifications-page">
            <h2>Your Notifications</h2>

            @if($notifications->count())
                <div class="notification-list">
                    @foreach($notifications as $notification)
                        <div class="notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                            <div class="notification-icon info">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="notification-content">
                                <h6>{{ $notification->activity }}</h6>
                                <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p>No notifications yet.</p>
            @endif
        </div>
    </div>
@endsection
