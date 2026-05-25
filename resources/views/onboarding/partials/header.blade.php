@php
    $isAcct   = ($degree ?? 'bsba') === 'bs_accounting';
    $wStep    = $wizardStep ?? $step;
    $wTotal   = $wizardTotal ?? $totalSteps;
    $labels   = $isAcct
        ? ['Basic Info', 'Liberal Arts', 'Business Core', 'Acct. Requirements', 'Current Status']
        : ['Basic Info', 'Specializations', 'Liberal Arts', 'Business Core', 'Spec. Courses', 'Current Status'];
@endphp

<div class="wizard-header">
    <img src="/images/busch_logo.jpg" alt="Busch School" class="wizard-logo">
    <h2 class="wizard-title">Academic Profile Setup</h2>
    <p class="wizard-subtitle">Step {{ $wStep }} of {{ $wTotal }}</p>
</div>

<div class="progress-bar-container">
    <div class="progress-bar-fill" style="width: {{ ($wStep / $wTotal) * 100 }}%"></div>
</div>

<div class="step-labels">
    @foreach($labels as $i => $label)
        <span class="step-label {{ $wStep === $i + 1 ? 'active' : ($wStep > $i + 1 ? 'done' : '') }}">
            {{ $label }}
        </span>
    @endforeach
</div>
