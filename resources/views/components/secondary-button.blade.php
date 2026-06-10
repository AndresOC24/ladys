<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 bg-white border border-primary-200 rounded-xl font-semibold text-sm text-slate-700 tracking-wide shadow-sm hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50 transition ease-out duration-200 cursor-pointer']) }}>
    {{ $slot }}
</button>
