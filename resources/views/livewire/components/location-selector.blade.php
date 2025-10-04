<div class="location-selector {{ $compact ? 'compact' : '' }}">
    @if($showLabels && !$compact)
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">📍 Location Selection</h3>
        <p class="text-sm text-gray-600">Select your complete address using the dropdown menus below</p>
    </div>
    @endif

    @if($errorMessage)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
        <div class="flex items-center">
            <div class="text-red-400 mr-3">⚠️</div>
            <div>
                <h4 class="text-sm font-medium text-red-800">Error</h4>
                <p class="text-sm text-red-600">{{ $errorMessage }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="space-y-4">
        <!-- Region Selection -->
        <div class="form-group">
            @if($showLabels)
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Region {{ $required ? '*' : '' }}
            </label>
            @endif
            <select 
                wire:model.live="selectedRegion" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $required && empty($selectedRegion) ? 'border-red-300' : '' }}"
                {{ $required ? 'required' : '' }}
            >
                <option value="">{{ $placeholder }}</option>
                @foreach($regions as $region)
                <option value="{{ $region['code'] }}">{{ $region['name'] }}</option>
                @endforeach
            </select>
            @if($required && empty($selectedRegion))
            <p class="text-red-500 text-sm mt-1">Region is required</p>
            @endif
        </div>

        <!-- Province Selection -->
        @if($showProvinces || !empty($selectedProvince))
        <div class="form-group">
            @if($showLabels)
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Province {{ $required ? '*' : '' }}
            </label>
            @endif
            <select 
                wire:model.live="selectedProvince" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $required && empty($selectedProvince) ? 'border-red-300' : '' }}"
                {{ $required ? 'required' : '' }}
                @if($loading) disabled @endif
            >
                <option value="">Select Province</option>
                @foreach($provinces as $province)
                <option value="{{ $province['code'] }}">{{ $province['name'] }}</option>
                @endforeach
            </select>
            @if($required && empty($selectedProvince))
            <p class="text-red-500 text-sm mt-1">Province is required</p>
            @endif
        </div>
        @endif

        <!-- City/Municipality Selection -->
        @if($showCities || !empty($selectedCity))
        <div class="form-group">
            @if($showLabels)
            <label class="block text-sm font-medium text-gray-700 mb-2">
                City/Municipality {{ $required ? '*' : '' }}
            </label>
            @endif
            <select 
                wire:model.live="selectedCity" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $required && empty($selectedCity) ? 'border-red-300' : '' }}"
                {{ $required ? 'required' : '' }}
                @if($loading) disabled @endif
            >
                <option value="">Select City/Municipality</option>
                @foreach($cities as $city)
                <option value="{{ $city['code'] }}">{{ $city['name'] }}</option>
                @endforeach
            </select>
            @if($required && empty($selectedCity))
            <p class="text-red-500 text-sm mt-1">City/Municipality is required</p>
            @endif
        </div>
        @endif

        <!-- Barangay Selection -->
        @if($showBarangays || !empty($selectedBarangay))
        <div class="form-group">
            @if($showLabels)
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Barangay {{ $required ? '*' : '' }}
            </label>
            @endif
            <select 
                wire:model.live="selectedBarangay" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $required && empty($selectedBarangay) ? 'border-red-300' : '' }}"
                {{ $required ? 'required' : '' }}
                @if($loading) disabled @endif
            >
                <option value="">Select Barangay</option>
                @foreach($barangays as $barangay)
                <option value="{{ $barangay['code'] }}">{{ $barangay['name'] }}</option>
                @endforeach
            </select>
            @if($required && empty($selectedBarangay))
            <p class="text-red-500 text-sm mt-1">Barangay is required</p>
            @endif
        </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    @if($loading)
    <div class="mt-4 flex items-center justify-center">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        <span class="ml-2 text-sm text-gray-600">Loading location data...</span>
    </div>
    @endif

    <!-- Selected Location Summary -->
    @if($this->validateSelection())
    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <div class="text-green-400 mr-3">✅</div>
            <div>
                <h4 class="text-sm font-medium text-green-800">Complete Address Selected</h4>
                <p class="text-sm text-green-600 font-medium">{{ $this->getFormattedLocation() }}</p>
            </div>
        </div>
    </div>
    @elseif(!empty($selectedRegion))
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-center">
            <div class="text-yellow-400 mr-3">⚠️</div>
            <div>
                <h4 class="text-sm font-medium text-yellow-800">Incomplete Selection</h4>
                <p class="text-sm text-yellow-600">Please select all required location fields</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Compact Mode Summary -->
    @if($compact && $this->validateSelection())
    <div class="mt-3 text-sm text-gray-600">
        📍 {{ $this->getFormattedLocation() }}
    </div>
    @endif

    <!-- Action Buttons -->
    @if(!$compact)
    <div class="mt-6 flex gap-3">
        <button 
            wire:click="resetSelection"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200"
        >
            Reset Selection
        </button>
        
        @if($this->validateSelection())
        <button 
            wire:click="emitLocationChanged"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
        >
            Confirm Selection
        </button>
        @endif
    </div>
    @endif
</div>

<!-- Compact Mode Styles -->
@if($compact)
<style>
.location-selector.compact .form-group {
    margin-bottom: 0.75rem;
}

.location-selector.compact select {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.location-selector.compact label {
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
}
</style>
@endif

<!-- Loading State Styles -->
<style>
.location-selector select:disabled {
    background-color: #f9fafb;
    cursor: not-allowed;
    opacity: 0.6;
}

.location-selector .form-group {
    transition: all 0.2s ease-in-out;
}

.location-selector .form-group:has(select:focus) {
    transform: translateY(-1px);
}
</style>
