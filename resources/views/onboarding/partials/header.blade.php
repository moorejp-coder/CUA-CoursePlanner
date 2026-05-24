<div class="wizard-header">
    <img src="/images/busch_logo.jpg" alt="Busch School" class="wizard-logo">
    <h2 class="wizard-title">Academic Profile Setup</h2>
    <p class="wizard-subtitle">Step {{ $step }} of {{ $totalSteps }}</p>
</div>

<div class="progress-bar-container">
    <div class="progress-bar-fill" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
</div>

<div class="step-labels">
    @foreach(['Basic Info', 'Specializations', 'Liberal Arts', 'Business Core', 'Spec. Courses', 'Current Status'] as $i => $label)
        <span class="step-label {{ $step === $i + 1 ? 'active' : ($step > $i + 1 ? 'done' : '') }}">
            {{ $label }}
        </span>
    @endforeach
</div>
