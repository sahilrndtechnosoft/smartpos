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
            <h2>Sales Order Invoice</h2>
            <p><strong>SO:</strong> {{ $document->code }}</p>
            <p><strong>Date:</strong> {{ $document->ordered_at?->format('d M Y, h:i A') }}</p>
            <p><strong>Payment:</strong> {{ strtoupper($document->payment_mode) }}</p>
        </div>
    </div>

    <div class="info-grid">
        <div class="card">
            <p class="section-title">Bill To</p>
            <p><strong>{{ $document->customer?->name ?: '—' }}</strong></p>
            <p>{{ $document->customer?->phone }}</p>
            @if ($document->customer?->email)
                <p>{{ $document->customer->email }}</p>
            @endif
        </div>
        <div class="card">
            <p class="section-title">Order Summary</p>
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
                <th>Rate</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Discount</th>
                <th class="num">Tax</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $index => $item)
                @php
                    $rateKey = $item->product_snapshot['applied_rate'] ?? 'rate_a';
                    $rateLabel = \App\Support\ProductRateOptions::label($rateKey);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if ($item->product?->sku)
                            <br><small>{{ $item->product->sku }}</small>
                        @endif
                    </td>
                    <td>{{ $rateLabel }}</td>
                    <td class="num">{{ $item->qty }}</td>
                    <td class="num">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="num">₹{{ number_format((float) $item->discount_amount, 2) }}</td>
                    <td class="num">₹{{ number_format((float) $item->tax_total, 2) }}</td>
                    <td class="num">₹{{ number_format((float) $item->final_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="num">₹{{ number_format((float) $document->total, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="num">₹{{ number_format((float) $document->discount_total, 2) }}</td>
            </tr>
            <tr class="grand">
                <td>Grand Total</td>
                <td class="num">₹{{ number_format((float) $document->grand_total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if ($store['footer'])
        <div class="footer">{{ $store['footer'] }}</div>
    @endif
@endsection
