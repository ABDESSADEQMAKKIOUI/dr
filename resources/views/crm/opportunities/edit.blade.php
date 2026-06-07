@extends('layouts.app')
@section('title', 'Edit Opportunity')
@php
$pageTitle = 'Edit Opportunity';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'CRM', 'url' => '#'], ['label' => 'Opportunities', 'url' => route('crm.opportunities.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Opportunity: {{ $opportunity->name }}</h3></div>
    <form method="POST" action="{{ route('crm.opportunities.update', $opportunity->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Opportunity Name *</label><input type="text" name="name" value="{{ old('name', $opportunity->name) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Account Name *</label><input type="text" name="account_name" value="{{ old('account_name', $opportunity->account_name) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" value="{{ old('amount', $opportunity->amount) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Expected Close Date *</label><input type="date" name="expected_close_date" value="{{ old('expected_close_date', $opportunity->expected_close_date->format('Y-m-d')) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Stage *</label><select name="stage" class="form-control" required><option value="">Select Stage</option><option value="prospecting" {{ $opportunity->stage == 'prospecting' ? 'selected' : '' }}>Prospecting</option><option value="qualification" {{ $opportunity->stage == 'qualification' ? 'selected' : '' }}>Qualification</option><option value="proposal" {{ $opportunity->stage == 'proposal' ? 'selected' : '' }}>Proposal</option><option value="negotiation" {{ $opportunity->stage == 'negotiation' ? 'selected' : '' }}>Negotiation</option><option value="closed_won" {{ $opportunity->stage == 'closed_won' ? 'selected' : '' }}>Closed Won</option><option value="closed_lost" {{ $opportunity->stage == 'closed_lost' ? 'selected' : '' }}>Closed Lost</option></select></div>
            <div class="form-group"><label class="form-label">Probability (%) *</label><input type="number" name="probability" value="{{ old('probability', $opportunity->probability) }}" min="0" max="100" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Lead Source</label><select name="lead_source" class="form-control"><option value="">Select Source</option><option value="website" {{ $opportunity->lead_source == 'website' ? 'selected' : '' }}>Website</option><option value="referral" {{ $opportunity->lead_source == 'referral' ? 'selected' : '' }}>Referral</option><option value="social_media" {{ $opportunity->lead_source == 'social_media' ? 'selected' : '' }}>Social Media</option><option value="advertising" {{ $opportunity->lead_source == 'advertising' ? 'selected' : '' }}>Advertising</option><option value="existing_customer" {{ $opportunity->lead_source == 'existing_customer' ? 'selected' : '' }}>Existing Customer</option></select></div>
            <div class="form-group"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-control"><option value="">Unassigned</option>@foreach($users ?? [] as $u)<option value="{{ $u->id }}" {{ $opportunity->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Contact Name</label><input type="text" name="contact_name" value="{{ old('contact_name', $opportunity->contact_name) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="contact_email" value="{{ old('contact_email', $opportunity->contact_email) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Contact Phone</label><input type="text" name="contact_phone" value="{{ old('contact_phone', $opportunity->contact_phone) }}" class="form-control"></div>
            <div class="form-group md:col-span-2"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $opportunity->description) }}</textarea></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('crm.opportunities.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Opportunity</button></div>
    </form>
</div>
@endsection
