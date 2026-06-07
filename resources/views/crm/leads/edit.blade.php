@extends('layouts.app')
@section('title', 'Edit Lead')
@php
$pageTitle = 'Edit Lead';
$breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard')], ['label' => 'CRM', 'url' => '#'], ['label' => 'Leads', 'url' => route('crm.leads.index')], ['label' => 'Edit', 'url' => '']];
@endphp
@section('content')
<div class="card max-w-4xl">
    <div class="card-header"><h3 class="text-lg font-semibold text-gray-800">Edit Lead: {{ $lead->name }}</h3></div>
    <form method="POST" action="{{ route('crm.leads.update', $lead->id) }}" data-validate>
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group"><label class="form-label">Lead Name *</label><input type="text" name="name" value="{{ old('name', $lead->name) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" value="{{ old('company', $lead->company) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" value="{{ old('email', $lead->email) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Phone *</label><input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" class="form-control" required></div>
            <div class="form-group"><label class="form-label">Job Title</label><input type="text" name="job_title" value="{{ old('job_title', $lead->job_title) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Lead Source *</label><select name="source" class="form-control" required><option value="">Select Source</option><option value="website" {{ $lead->source == 'website' ? 'selected' : '' }}>Website</option><option value="referral" {{ $lead->source == 'referral' ? 'selected' : '' }}>Referral</option><option value="social_media" {{ $lead->source == 'social_media' ? 'selected' : '' }}>Social Media</option><option value="advertising" {{ $lead->source == 'advertising' ? 'selected' : '' }}>Advertising</option><option value="cold_call" {{ $lead->source == 'cold_call' ? 'selected' : '' }}>Cold Call</option><option value="trade_show" {{ $lead->source == 'trade_show' ? 'selected' : '' }}>Trade Show</option><option value="other" {{ $lead->source == 'other' ? 'selected' : '' }}>Other</option></select></div>
            <div class="form-group"><label class="form-label">Estimated Value</label><input type="number" step="0.01" name="estimated_value" value="{{ old('estimated_value', $lead->estimated_value) }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-control"><option value="">Unassigned</option>@foreach($users ?? [] as $u)<option value="{{ $u->id }}" {{ $lead->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach</select></div>
            <div class="form-group md:col-span-2"><label class="form-label">Address</label><textarea name="address" rows="2" class="form-control">{{ old('address', $lead->address) }}</textarea></div>
            <div class="form-group md:col-span-2"><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-control">{{ old('notes', $lead->notes) }}</textarea></div>
            <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option><option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option><option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option><option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Converted</option><option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option></select></div>
        </div>
        <div class="flex justify-end space-x-2 mt-6"><a href="{{ route('crm.leads.index') }}" class="btn btn-outline">Cancel</a><button type="submit" class="btn btn-primary">Update Lead</button></div>
    </form>
</div>
@endsection
