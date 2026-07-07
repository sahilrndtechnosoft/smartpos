<div class="brand">
    @if ($store['logo'])
        <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}">
    @endif
    <div>
        <h1>{{ $store['name'] }}</h1>
        @if ($store['address'])
            <p>{{ $store['address'] }}</p>
        @endif
        @if ($store['phone'])
            <p>{{ $store['phone'] }}</p>
        @endif
        @if ($store['email'])
            <p>{{ $store['email'] }}</p>
        @endif
        @if ($store['website'])
            <p>{{ $store['website'] }}</p>
        @endif
        @if ($store['gst_number'] || $store['pan_number'] || $store['fssai_license'])
            <p>
                @if ($store['gst_number'])
                    <span>GST: {{ $store['gst_number'] }}</span>
                @endif
                @if ($store['pan_number'])
                    <span>@if ($store['gst_number']) | @endif PAN: {{ $store['pan_number'] }}</span>
                @endif
                @if ($store['fssai_license'])
                    <span>@if ($store['gst_number'] || $store['pan_number']) | @endif FSSAI: {{ $store['fssai_license'] }}</span>
                @endif
            </p>
        @endif
    </div>
</div>
