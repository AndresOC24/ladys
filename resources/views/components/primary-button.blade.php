<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 bg-gradient-to-r from-primary-500 to-accent-500 border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide shadow-sm shadow-primary-200 hover:from-primary-600 hover:to-accent-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-50 transition ease-out duration-200 cursor-pointer']) }}>
    {{ $slot }}
</button>
