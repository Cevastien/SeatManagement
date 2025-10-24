<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary btn-md']) }}>
    {{ $slot }}
</button>
