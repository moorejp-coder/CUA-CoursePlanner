@extends('admin.layout')

@section('title', 'System Prompt')
@section('heading', 'System Prompt Editor')

@section('content')

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.25rem;align-items:start;">

    {{-- Editor --}}
    <div>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Current Prompt</span>
                <div style="display:flex;gap:0.75rem;align-items:center;font-size:12px;color:#6b7280;">
                    <span id="char-count">{{ number_format($charCount) }} chars</span>
                    <span>~{{ number_format($tokenEstimate) }} tokens</span>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.system-prompt.save') }}" id="prompt-form">
                    @csrf
                    <textarea
                        name="content"
                        id="prompt-textarea"
                        rows="30"
                        style="font-family:monospace;font-size:12.5px;resize:vertical;line-height:1.6;"
                    >{{ $content }}</textarea>
                    @error('content')
                        <p style="color:var(--red);font-size:12px;margin-top:0.25rem;">{{ $message }}</p>
                    @enderror
                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;">
                        <button type="submit" class="btn btn-primary">Save Prompt</button>
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('prompt-textarea').value=originalContent">Discard Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Version History --}}
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">Version History</span></div>
            @if(empty($versions))
                <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:13px;">No saved versions yet.</div>
            @else
            <div>
                @foreach($versions as $v)
                <div style="padding:0.75rem 1.25rem;border-bottom:1px solid #f0ede9;">
                    <div style="font-size:12.5px;font-weight:500;color:#374151;margin-bottom:0.2rem;">{{ $v['saved_at'] }}</div>
                    <div style="font-size:11.5px;color:#9ca3af;margin-bottom:0.5rem;">{{ $v['size'] }} — {{ $v['filename'] }}</div>
                    <form method="POST" action="{{ route('admin.system-prompt.restore') }}" onsubmit="return confirm('Restore this version? The current prompt will be archived first.')">
                        @csrf
                        <input type="hidden" name="filename" value="{{ $v['filename'] }}">
                        <button type="submit" class="btn btn-secondary btn-sm">Restore</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="card" style="margin-top:1rem;">
            <div class="card-header"><span class="card-title">Tips</span></div>
            <div class="card-body" style="font-size:12.5px;color:#6b7280;line-height:1.7;">
                <p style="margin:0 0 0.5rem;">The system prompt is sent with every chat request. Keep it accurate and concise to minimize token usage.</p>
                <p style="margin:0;">Versions are saved automatically when you save. Up to 10 versions are retained.</p>
            </div>
        </div>
    </div>

</div>

<script>
const originalContent = {{ Js::from($content) }};
const textarea = document.getElementById('prompt-textarea');
const charCountEl = document.getElementById('char-count');

textarea.addEventListener('input', () => {
    const len = textarea.value.length;
    charCountEl.textContent = len.toLocaleString() + ' chars';
});
</script>

@endsection
