@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 focus:border-[#B41100] focus:ring-[#B41100] rounded-lg shadow-sm text-sm py-2.5 w-full']) }}>
