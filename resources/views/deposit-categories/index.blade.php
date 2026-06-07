@extends('layouts.app')
@section('title', __('app.deposit_categories'))
@php $pageTitle = __('app.deposit_categories'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.deposit_categories')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Add Category Form --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">{{ __('app.add_category') }}</h3></div>
        <form method="POST" action="{{ route('deposit-categories.store') }}" class="p-4 flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label class="form-label text-sm">{{ __('app.name') }} *</label>
                <input type="text" name="name" class="form-input" required placeholder="e.g. Interest, Investment">
            </div>
            <div class="flex-2 min-w-[300px]">
                <label class="form-label text-sm">{{ __('app.description') }}</label>
                <input type="text" name="description" class="form-input" placeholder="Optional description">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('app.save') }}</button>
        </form>
    </div>

    {{-- Categories Table --}}
    <div class="card">
        <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.all_categories') }}</h3></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.description') }}</th><th>{{ __('app.deposits') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <form method="POST" action="{{ route('deposit-categories.update', $category) }}">
                            @csrf @method('PUT')
                            <td><input type="text" name="name" value="{{ $category->name }}" class="form-input w-full" required></td>
                            <td><input type="text" name="description" value="{{ $category->description }}" class="form-input w-full"></td>
                            <td class="text-center font-bold">{{ $category->deposits_count }}</td>
                            <td class="flex space-x-2">
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm">{{ __('app.update') }}</button>
                        </form>
                                <form method="POST" action="{{ route('deposit-categories.destroy', $category) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-500 py-8">{{ __('app.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
