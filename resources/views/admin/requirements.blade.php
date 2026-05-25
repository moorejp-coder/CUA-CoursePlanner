@extends('admin.layout')

@section('title', 'Requirements')
@section('heading', 'Degree Requirements')

@section('content')

@php
$sections = [
    ['key' => 'liberal_arts',          'label' => 'Liberal Arts Core',             'desc' => 'All required liberal arts courses (one per line).'],
    ['key' => 'business_core',         'label' => 'Business Core',                 'desc' => 'Required business foundation courses (one per line).'],
    ['key' => 'spec.marketing',        'label' => 'Specialization: Marketing',     'desc' => 'Required courses for Marketing specialization.'],
    ['key' => 'spec.finance',          'label' => 'Specialization: Finance',       'desc' => 'Required courses for Finance specialization.'],
    ['key' => 'spec.management',       'label' => 'Specialization: Management',    'desc' => 'Required courses for Management specialization.'],
    ['key' => 'spec.accounting',       'label' => 'Specialization: Accounting',    'desc' => 'Required courses for Accounting specialization.'],
    ['key' => 'spec.business_econ',    'label' => 'Specialization: Business Econ', 'desc' => 'Required courses for Business Economics specialization.'],
    ['key' => 'spec.entrepreneurship', 'label' => 'Specialization: Entrepreneurship', 'desc' => 'Required courses for Entrepreneurship specialization.'],
    ['key' => 'spec.real_estate',      'label' => 'Specialization: Real Estate',   'desc' => 'Required courses for Real Estate specialization.'],
];
@endphp

<form method="POST" action="{{ route('admin.requirements.save') }}">
@csrf

<div style="display:flex;justify-content:flex-end;margin-bottom:1rem;">
    <button type="submit" class="btn btn-primary">Save All Changes</button>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
@foreach($sections as $i => $section)
@php
    $keyParts = explode('.', $section['key']);
    $value = count($keyParts) === 1
        ? ($requirements[$keyParts[0]] ?? [])
        : ($requirements[$keyParts[0]][$keyParts[1]] ?? []);
    $text = is_array($value) ? implode("\n", $value) : '';
@endphp
<div class="card">
    <div class="card-header">
        <span class="card-title">{{ $section['label'] }}</span>
    </div>
    <div class="card-body">
        <p style="font-size:12px;color:#6b7280;margin:0 0 0.5rem;">{{ $section['desc'] }}</p>
        <input type="hidden" name="sections[{{ $i }}][key]" value="{{ $section['key'] }}">
        <textarea
            name="sections[{{ $i }}][courses]"
            rows="8"
            style="font-family:monospace;font-size:12.5px;resize:vertical;"
        >{{ $text }}</textarea>
    </div>
</div>
@endforeach
</div>

<div style="margin-top:1rem;display:flex;justify-content:flex-end;">
    <button type="submit" class="btn btn-primary">Save All Changes</button>
</div>

</form>

@endsection
