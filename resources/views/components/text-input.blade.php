@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-[44px] border-primary-200 bg-white text-slate-800 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm transition duration-200']) }}>
