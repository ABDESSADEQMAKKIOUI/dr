@extends('layouts.app')
@section('title', __('app.create_opportunity'))
@php
$pageTitle = __('app.create_opportunity');
$breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('dashboard')], ['label' => __('app.crm'), 'url' => '#'], ['label' => __('app.opportunities'), 'url' => route('crm.opportunities.index')], ['label' => __('app.create'), 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">{{ __('app.new_opportunity') }}</h3></div>
    <form method="POST" action="{{ route('crm.opportunities.store') }}" data-validate>
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">{{ __('app.opportunity_name') }} *</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required>@error('name')<span class="form-error">{{ $message }}</span>@enderror</div>
            <div class="form-group"><label class="form-label">{{ __('app.account_name') }} *</label><input type="text" name="account_name" value="{{ old('account_name') }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.amount') }} *</label><input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.expected_close_date') }} *</label><input type="date" name="expected_close_date" value="{{ old('expected_close_date') }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.stage') }} *</label><select name="stage" class="form-control" required><option value="">{{ __('app.select_stage') }}</option><option value="prospecting">{{ __('app.prospecting') }}</option><option value="qualification">{{ __('app.qualification') }}</option><option value="proposal">{{ __('app.proposal') }}</option><option value="negotiation">{{ __('app.negotiation') }}</option><option value="closed_won">{{ __('app.closed_won') }}</option><option value="closed_lost">{{ __('app.closed_lost') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.probability') }} (%) *</label><input type="number" name="probability" value="{{ old('probability', 50) }}" min="0" max="100" class="form-control" required></div>
            <div class="form-group"><label class="form-label">{{ __('app.lead_source') }}</label><select name="lead_source" class="form-control"><option value="">{{ __('app.select_source') }}</option><option value="website">{{ __('app.website') }}</option><option value="referral">{{ __('app.referral') }}</option><option value="social_media">{{ __('app.social_media') }}</option><option value="advertising">{{ __('app.advertising') }}</option><option value="existing_customer">{{ __('app.existing_customer') }}</option></select></div>
            <div class="form-group"><label class="form-label">{{ __('app.assigned_to') }}</label><select name="assigned_to" class="form-control"><option value="">{{ __('app.unassigned') }}</option>@foreach($users ?? [] as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">{{ __('app.contact_name') }}</label><input type="text" name="contact_name" value="{{ old('contact_name') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.contact_email') }}</label><input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ __('app.contact_phone') }}</label><input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">{{ __('app.description') }}</label><textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('crm.opportunities.index') }}" class="btn btn-outline">{{ __('app.cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('app.create_opportunity') }}</button></div>
    </form>
</div>
@endsection
