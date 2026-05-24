<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-3 bg-[#B41100] border border-transparent rounded-lg font-oswald font-bold text-sm text-white uppercase tracking-widest hover:bg-[#8C0D00] focus:bg-[#8C0D00] active:bg-[#8C0D00] focus:outline-none focus:ring-2 focus:ring-[#B41100] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
