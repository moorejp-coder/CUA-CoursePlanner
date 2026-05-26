@php
    $status      = 403;
    $title       = 'Access Denied';
    $message     = "You don't have permission to view this page. If you believe this is a mistake, please sign in with the correct account or contact support.";
    $iconBg      = '#eff6ff';
    $iconSvg     = '<svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>';
    $actionUrl   = route('login');
    $actionLabel = 'Sign In';
    $secondaryUrl   = route('chat');
    $secondaryLabel = 'Back to Course Planner';
@endphp
@include('errors.layout')
