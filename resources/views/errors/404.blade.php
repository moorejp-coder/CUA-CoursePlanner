@php
    $status      = 404;
    $title       = 'Page Not Found';
    $message     = "The page you're looking for doesn't exist or may have been moved. Double-check the URL, or head back to the course planner.";
    $iconBg      = '#f3f4f6';
    $iconSvg     = '<svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#6b7280" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/></svg>';
    $actionUrl   = route('chat');
    $actionLabel = 'Back to Course Planner';
    $secondaryUrl   = null;
    $secondaryLabel = null;
@endphp
@include('errors.layout')
