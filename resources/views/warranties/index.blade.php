@extends('layouts.app')
@section('title', __('app.warranties'))
@php $pageTitle = __('app.warranties'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.warranties'),'url'=>route('warranties.index')]]; @endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_warranties') }}</h3>
        <a href="{{ route('warranties.create') }}" class="btn btn-primary btn-sm">+ {{ __('app.add_warranty') }}</a>
    </div>
    <div class="p-4 flex gap-3">
        <form method="GET" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_serial_product_customer') }}" class="form-input w-64">
            <select name="status" class="form-select w-40">
                <option value="">{{ __('app.all') }} {{ __('app.status') }}</option>
                @foreach(['active','expired','claimed','voided'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ __('app.' . $s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">{{ __('app.filter') }}</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>#</th><th>{{ __('app.product') }}</th><th>{{ __('app.customer') }}</th><th>{{ __('app.serial') }}</th><th>{{ __('app.start') }}</th><th>{{ __('app.end') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
            <tbody>
                @forelse($warranties as $w)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $w->product->name ?? '—' }}</td>
                    <td>{{ $w->customer->name ?? '—' }}</td>
                    <td><code>{{ $w->serial_number ?? '—' }}</code></td>
                    <td>{{ $w->start_date->format('M d, Y') }}</td>
                    <td>{{ $w->end_date->format('M d, Y') }}</td>
                    <td>
                        @php $colors = ['active'=>'success','expired'=>'warning','claimed'=>'info','voided'=>'danger']; @endphp
                        <span class="badge badge-{{ $colors[$w->status] ?? 'secondary' }}">{{ __('app.' . $w->status) }}</span>
                    </td>
                    <td class="flex space-x-2">
                        <a href="{{ route('warranties.show', $w) }}" class="text-blue-600 hover:text-blue-800">{{ __('app.view') }}</a>
                        <a href="{{ route('warranties.edit', $w) }}" class="text-green-600 hover:text-green-800">{{ __('app.edit') }}</a>
                        <form method="POST" action="{{ route('warranties.destroy', $w) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('app.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-gray-500 py-8">{{ __('app.no_warranties_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $warranties->withQueryString()->links() }}</div>
</div>
@endsection
