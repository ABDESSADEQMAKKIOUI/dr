@extends('layouts.app')
@section('title', __('app.create_lead'))
@php
$pageTitle = __('app.create_lead');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.crm'), 'url' => '#'], ['label' => __('app.leads'), 'url' => route('crm.leads.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.new_lead') }}</h3></div>
    <form method="POST" action="{{ route('crm.leads.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.lead_name') }} *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.company') }}</label><input type="text" name="company" value="{{ old('company') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.email') }} *</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required>@error('email')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.phone') }} *</label><input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.job_title') }}</label><input type="text" name="job_title" value="{{ old('job_title') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.lead_source') }} *</label><select name="source" class="form-control" required><option value="">{{ __('app.select_source') }}</option><option value="website">{{ __('app.website') }}</option><option value="referral">{{ __('app.referral') }}</option><option value="social_media">{{ __('app.social_media') }}</option><option value="advertising">{{ __('app.advertising') }}</option><option value="cold_call">{{ __('app.cold_call') }}</option><option value="trade_show">{{ __('app.trade_show') }}</option><option value="other">{{ __('app.other') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.estimated_value') }}</label><input type="number" step="0.01" name="estimated_value" value="{{ old('estimated_value', 0) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.assigned_to') }}</label><select name="assigned_to" class="form-control"><option value="">{{ __('app.unassigned') }}</option>@foreach($users ?? [] as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.address') }}</label><textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.notes') }}</label><textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div>
            <div class="form-group"><label class="form-label">{{ __('app.status') }}</label><select name="status" class="form-control"><option value="new" selected>{{ __('app.new') }}</option><option value="contacted">{{ __('app.contacted') }}</option><option value="qualified">{{ __('app.qualified') }}</option><option value="converted">{{ __('app.converted') }}</option><option value="lost">{{ __('app.lost') }}</option></select></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('crm.leads.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.create_lead') }}</button></div>
    </form>
</div>
@endsection
