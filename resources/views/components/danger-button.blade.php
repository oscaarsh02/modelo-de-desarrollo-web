<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-semibold text-white transition focus:outline-none focus:ring-4 focus:ring-rose-100']) }} style="background: linear-gradient(135deg, #b42318 0%, #d92d20 100%); box-shadow: 0 16px 30px rgba(185, 28, 28, 0.22);">
    {{ $slot }}
</button>
