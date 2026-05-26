@php
    $status      = 419;
    $title       = 'Session Expired';
    $message     = 'Your session has timed out for security reasons. Please sign in again to continue where you left off.';
    $iconBg      = '#fffbea';
    $iconSvg     = '<svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $actionUrl   = route('login');
    $actionLabel = 'Sign In Again';
    $secondaryUrl   = null;
    $secondaryLabel = null;
@endphp
@include('errors.layout')
