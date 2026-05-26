{{-- Bot trap: visually hidden, ignored by screen readers, invisible to real users.
     Any submission that fills this field is rejected as automated. --}}
<div aria-hidden="true" style="position:absolute;left:-9999px;height:0;overflow:hidden;">
    <label for="{{ \App\Http\Middleware\Honeypot::FIELD }}">Phone</label>
    <input type="text"
           id="{{ \App\Http\Middleware\Honeypot::FIELD }}"
           name="{{ \App\Http\Middleware\Honeypot::FIELD }}"
           value=""
           tabindex="-1"
           autocomplete="off">
</div>
