@extends('print.layout')

@section('content')
    <div class="header">
        <div class="brand">
            @if ($store['logo'])
                <img src="{{ $store['logo'] }}" alt="{{ $store['name'] }}">
            @endif
            <div>
                <h1>{{ $store['name'] }}</h1>
                @if ($store['phone'])
                    <p>{{ $store['phone'] }}</p>
                @endif
            </div>
        </div>
        <div class="meta">
            <h2>Inventory Receipt</h2>
            <p><strong>Receipt:</strong> {{ $document->code }}</p>
            <p><strong>Date:</strong> {{ $document->date?->format('d M Y, h:i A') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($document->status) }}</p>
        </div>
    </div>

    <div class="info-grid">
        <div class="card">
            <p class="section-title">Supplier</p>
            <p><strong>{{ $document->supplier?->name ?: '—' }}</strong></p>
            <p>{{ $document->supplier?->phone }}</p>
            @if ($document->supplier?->email)
                <p>{{ $document->supplier->email }}</p>
            @endif
        </div>
        <div class="card">
            <p class="section-title">Receipt Summary</p>
            <p>Items: {{ $document->items->count() }}</p>
            @if ($document->notes)
                <p><strong>Notes:</strong> {{ $document->notes }}</p>
            @endif
        </div>
    </div>

    <p class="section-title">Line Items</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="num">Qty</th>
                <th class="num">Cost Price</th>
                <th class="num">Sale Price</th>
                <th class="num">MRP</th>
                <th>Expiry</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong>
                        @if ($item->product?->sku)
                            <br><small>{{ $item->product->sku }}</small>
                        @endif
                    </td>
                    <td class="num">{{ $item->qty }}</td>
                    <td class="num">₹{{ number_format((float) $item->purchase_rate, 2) }}</td>
                    <td class="num">₹{{ number_format((float) $item->rate_a, 2) }}</td>
                    <td class="num">₹{{ number_format((float) $item->mrp, 2) }}</td>
                    <td>{{ $item->expiry_date?->format('d M Y') ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($store['footer'])
        <div class="footer">{{ $store['footer'] }}</div>
    @endif
@endsection
