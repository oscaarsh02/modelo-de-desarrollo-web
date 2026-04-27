<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-full border px-5 py-3 text-sm font-semibold transition focus:outline-none focus:ring-4 focus:ring-slate-100 disabled:opacity-25']) }} style="background: rgba(255,255,255,0.75); border-color: rgba(15,23,42,0.1); color: #102a43;">
    {{ $slot }}
</button>
