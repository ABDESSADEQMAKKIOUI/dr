@extends('layouts.app')
@section('title', __('app.log_activity'))
@php
$pageTitle = __('app.log_activity');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.crm'), 'url' => '#'], ['label' => __('app.activities'), 'url' => route('crm.activities.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-3xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.log_new_activity') }}</h3></div>
    <form method="POST" action="{{ route('crm.activities.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.activity_type') }} *</label><select name="type" class="form-control" required><option value="">{{ __('app.select_type') }}</option><option value="call">{{ __('app.call') }}</option><option value="meeting">{{ __('app.meeting') }}</option><option value="email">{{ __('app.email') }}</option><option value="task">{{ __('app.task') }}</option><option value="note">{{ __('app.note') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.subject') }} *</label><input type="text" name="subject" value="{{ old('subject') }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.related_to') }}</label><select name="related_to_type" class="form-control"><option value="">{{ __('app.select_type') }}</option><option value="lead">{{ __('app.lead') }}</option><option value="opportunity">{{ __('app.opportunity') }}</option><option value="customer">{{ __('app.customer') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.related_record') }}</label><select name="related_to_id" class="form-control"><option value="">{{ __('app.select_record') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.activity_date') }} *</label><input type="datetime-local" name="activity_date" value="{{ old('activity_date', date('Y-m-d\TH:i')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.duration_minutes') }}</label><input type="number" name="duration" value="{{ old('duration', 30) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.assigned_to') }}</label><select name="assigned_to" class="form-control"><option value="">{{ __('app.unassigned') }}</option>@foreach($users ?? [] as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.priority') }}</label><select name="priority" class="form-control"><option value="low">{{ __('app.low') }}</option><option value="medium" selected>{{ __('app.medium') }}</option><option value="high">{{ __('app.high') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.status') }}</label><select name="status" class="form-control"><option value="pending" selected>{{ __('app.pending') }}</option><option value="completed">{{ __('app.completed') }}</option><option value="cancelled">{{ __('app.cancelled') }}</option></select></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.description') }}</label><textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="flex items-center"><input type="checkbox" name="send_reminder" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="ml-2 text-sm text-gray-700">{{ __('app.send_reminder_notification') }}</span></label></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('crm.activities.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.log_activity') }}</button></div>
    </form>
</div>
@endsection
