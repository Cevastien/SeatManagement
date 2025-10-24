<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary btn-md']) }}>
    {{ $slot }}
</button>
