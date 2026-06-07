@extends('layouts.app')
@section('title', __('app.combo_products'))
@php $pageTitle = __('app.combo_products'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.combo_products')]]; @endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.all_combos') }}</h3>
        <a href="{{ route('combos.create') }}" class="btn btn-primary btn-sm">+ {{ __('app.add_combo') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.sku') }}</th>
                    <th>{{ __('app.price') }}</th>
                    <th>{{ __('app.items_count') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($combos as $combo)
                <tr>
                    <td>{{ $loop->iteration + ($combos->currentPage() - 1) * $combos->perPage() }}</td>
                    <td class="font-medium">{{ $combo->name }}</td>
                    <td><code>{{ $combo->sku }}</code></td>
                    <td>{{ number_format($combo->price, 2) }}</td>
                    <td>
                        <span class="badge badge-info">{{ $combo->items_count }} {{ __('app.products') }}</span>
                    </td>
                    <td>
                        @if($combo->is_active)
                            <span class="badge badge-success">{{ __('app.active') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('app.inactive') }}</span>
                        @endif
                    </td>
                    <td class="flex space-x-2">
                        <a href="{{ route('combos.show', $combo) }}" class="text-blue-600 hover:text-blue-800">{{ __('app.view') }}</a>
                        <a href="{{ route('combos.edit', $combo) }}" class="text-green-600 hover:text-green-800">{{ __('app.edit') }}</a>
                        <form method="POST" action="{{ route('combos.destroy', $combo) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('app.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-gray-500 py-8">{{ __('app.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $combos->links() }}</div>
</div>
@endsection
