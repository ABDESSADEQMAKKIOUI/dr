@extends('layouts.app')
@section('title', __('app.holidays'))
@php $pageTitle = __('app.holidays'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.holidays')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Add Holiday Form --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">{{ __('app.add_holiday') }}</h3></div>
        <form method="POST" action="{{ route('holidays.store') }}" class="p-4 flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[180px]">
                <label class="form-label text-sm">{{ __('app.name') }} *</label>
                <input type="text" name="name" class="form-input" required placeholder="{{ __('app.eid_al_fitr_placeholder') }}">
            </div>
            <div class="w-44">
                <label class="form-label text-sm">{{ __('app.date') }} *</label>
                <input type="date" name="date" class="form-input" required>
            </div>
            <div class="flex items-center gap-2 pb-1">
                <input type="checkbox" name="is_recurring" value="1" class="form-checkbox" id="recurring-new">
                <label for="recurring-new" class="text-sm">{{ __('app.recurring_yearly') }}</label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('app.save') }}</button>
        </form>
    </div>

    {{-- Holidays Table --}}
    <div class="card">
        <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.all_holidays') }}</h3></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.date') }}</th><th>{{ __('app.recurring') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
                <tbody>
                    @forelse($holidays as $holiday)
                    <tr>
                        <form method="POST" action="{{ route('holidays.update', $holiday) }}">
                            @csrf @method('PUT')
                            <td><input type="text" name="name" value="{{ $holiday->name }}" class="form-input w-full" required></td>
                            <td><input type="date" name="date" value="{{ $holiday->date instanceof \Carbon\Carbon ? $holiday->date->format('Y-m-d') : $holiday->date }}" class="form-input" required></td>
                            <td class="text-center">
                                <input type="checkbox" name="is_recurring" value="1" class="form-checkbox" {{ $holiday->is_recurring ? 'checked' : '' }}>
                            </td>
                            <td class="flex space-x-2">
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm">{{ __('app.update') }}</button>
                        </form>
                                <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-500 py-8">{{ __('app.no_holidays_defined') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $holidays->links() }}</div>
    </div>
</div>
@endsection
