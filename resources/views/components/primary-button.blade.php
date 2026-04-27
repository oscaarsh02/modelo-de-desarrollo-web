<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full px-5 py-3 text-sm font-semibold text-white transition focus:outline-none focus:ring-4 focus:ring-orange-100']) }} style="background: linear-gradient(135deg, #c4552d 0%, #dd6b3d 100%); box-shadow: 0 18px 35px rgba(196, 85, 45, 0.22);">
    {{ $slot }}
</button>
