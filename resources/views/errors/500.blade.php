@php
    $status      = 500;
    $title       = 'Server Error';
    $message     = "We're experiencing a technical difficulty. The problem has been logged and our team has been notified. Please try again in a moment.";
    $iconBg      = '#fff1f2';
    $iconSvg     = '<svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#e11d48" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>';
    $actionUrl   = route('chat');
    $actionLabel = 'Back to Course Planner';
    $secondaryUrl   = 'mailto:busch-academic-services@cua.edu';
    $secondaryLabel = 'Contact Support';
@endphp
@include('errors.layout')
