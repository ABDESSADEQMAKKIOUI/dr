@extends('layouts.app')
@section('title', __('app.combo_details'))
@php $pageTitle = $combo->name; $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.combo_products'),'url'=>route('combos.index')],['label'=>$combo->name]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Combo Info --}}
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h3 class="text-lg font-semibold">{{ $combo->name }}</h3>
            <div class="flex gap-2">
                <a href="{{ route('combos.edit', $combo) }}" class="btn btn-primary btn-sm">{{ __('app.edit') }}</a>
                <form method="POST" action="{{ route('combos.destroy', $combo) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('app.delete') }}</button>
                </form>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-sm text-gray-500">{{ __('app.sku') }}</span>
                    <p class="font-medium"><code>{{ $combo->sku }}</code></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ __('app.price') }}</span>
                    <p class="font-medium text-green-600">{{ number_format($combo->price, 2) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ __('app.status') }}</span>
                    <p>
                        @if($combo->is_active)
                            <span class="badge badge-success">{{ __('app.active') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('app.inactive') }}</span>
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">{{ __('app.items_count') }}</span>
                    <p class="font-medium">{{ $combo->items->count() }} {{ __('app.products') }}</p>
                </div>
            </div>
            @if($combo->description)
            <div class="mt-4">
                <span class="text-sm text-gray-500">{{ __('app.description') }}</span>
                <p class="text-gray-700 mt-1">{{ $combo->description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Component Products --}}
    <div class="card">
        <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.component_products') }}</h3></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('app.product') }}</th>
                        <th>{{ __('app.sku') }}</th>
                        <th>{{ __('app.quantity') }}</th>
                        <th>{{ __('app.unit_price') }}</th>
                        <th>{{ __('app.subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $componentTotal = 0; @endphp
                    @foreach($combo->items as $item)
                    @php $sub = ($item->product->price ?? 0) * $item->quantity; $componentTotal += $sub; @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $item->product->name ?? '—' }}</td>
                        <td><code>{{ $item->product->sku ?? '—' }}</code></td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->product->price ?? 0, 2) }}</td>
                        <td>{{ number_format($sub, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold bg-gray-50">
                        <td colspan="5" class="text-right">{{ __('app.component_total') }}:</td>
                        <td>{{ number_format($componentTotal, 2) }}</td>
                    </tr>
                    <tr class="font-semibold text-green-600">
                        <td colspan="5" class="text-right">{{ __('app.combo_price') }}:</td>
                        <td>{{ number_format($combo->price, 2) }}</td>
                    </tr>
                    <tr class="font-semibold {{ $combo->price < $componentTotal ? 'text-blue-600' : 'text-red-600' }}">
                        <td colspan="5" class="text-right">{{ __('app.savings') }}:</td>
                        <td>{{ number_format($componentTotal - $combo->price, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
