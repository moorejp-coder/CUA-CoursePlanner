@php
    $status      = 429;
    $title       = 'Too Many Requests';
    $message     = "You've sent too many requests in a short period. Please wait a moment and try again — the limit resets automatically.";
    $iconBg      = '#fffbea';
    $iconSvg     = '<svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>';
    $actionUrl   = route('chat');
    $actionLabel = 'Try Again';
    $secondaryUrl   = null;
    $secondaryLabel = null;
@endphp
@include('errors.layout')
