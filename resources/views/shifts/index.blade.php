@extends('layouts.app')
@section('title', __('app.office_shifts'))
@php $pageTitle = __('app.office_shifts'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.office_shifts')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Add Shift Form --}}
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">{{ __('app.add_shift') }}</h3></div>
        <form method="POST" action="{{ route('shifts.store') }}" class="p-4 flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[150px]">
                <label class="form-label text-sm">{{ __('app.name') }} *</label>
                <input type="text" name="name" class="form-input" required placeholder="{{ __('app.morning_shift_placeholder') }}">
            </div>
            <div class="w-36">
                <label class="form-label text-sm">{{ __('app.start_time') }} *</label>
                <input type="time" name="start_time" class="form-input" required>
            </div>
            <div class="w-36">
                <label class="form-label text-sm">{{ __('app.end_time') }} *</label>
                <input type="time" name="end_time" class="form-input" required>
            </div>
            <div class="w-32">
                <label class="form-label text-sm">{{ __('app.late_after_min') }}</label>
                <input type="number" name="late_after_minutes" class="form-input" min="0" value="15">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('app.save') }}</button>
        </form>
    </div>

    {{-- Shifts Table --}}
    <div class="card">
        <div class="card-header"><h3 class="text-lg font-semibold">{{ __('app.all_shifts') }}</h3></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.start_time') }}</th><th>{{ __('app.end_time') }}</th><th>{{ __('app.late_after') }}</th><th>{{ __('app.employees') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
                <tbody>
                    @forelse($shifts as $shift)
                    <tr>
                        <form method="POST" action="{{ route('shifts.update', $shift) }}">
                            @csrf @method('PUT')
                            <td><input type="text" name="name" value="{{ $shift->name }}" class="form-input w-full" required></td>
                            <td><input type="time" name="start_time" value="{{ $shift->start_time }}" class="form-input" required></td>
                            <td><input type="time" name="end_time" value="{{ $shift->end_time }}" class="form-input" required></td>
                            <td><input type="number" name="late_after_minutes" value="{{ $shift->late_after_minutes }}" class="form-input w-20" min="0"></td>
                            <td><span class="badge badge-info">{{ $shift->employees_count ?? 0 }}</span></td>
                            <td class="flex space-x-2">
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm">{{ __('app.update') }}</button>
                        </form>
                                <form method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirm('{{ __('app.confirm_delete_shift') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-500 py-8">{{ __('app.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
