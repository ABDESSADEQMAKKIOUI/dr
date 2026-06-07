@extends('layouts.app')
@section('title', __('app.create_project'))
@php $pageTitle = __('app.create_project'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.projects'),'url'=>route('projects.index')],['label'=>__('app.create')]]; @endphp
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card shadow-2xl overflow-hidden border-none">
        <div class="bg-gray-800 p-8 text-white">
            <h3 class="text-2xl font-black italic uppercase tracking-tighter">{{ __('app.initialize_new_project') }}</h3>
            <p class="text-gray-400 text-sm">{{ __('app.set_goals_assign_leads_track_time') }}</p>
        </div>
        
        <form method="POST" action="{{ route('projects.store') }}" class="p-8 space-y-8 bg-white">
            @csrf
            
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.project_title') }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="form-input text-2xl font-black border-b-2 border-t-0 border-x-0 rounded-none focus:border-blue-500 px-0" placeholder="e.g. Website Redesign 2024" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.customer_client') }}</label>
                    <select name="customer_id" class="form-select border-gray-200">
                        <option value="">-- {{ __('app.internal_no_client') }} --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.project_manager_lead') }} <span class="text-red-500">*</span></label>
                    <select name="user_id" class="form-select border-gray-200" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.timeline_status') }} <span class="text-red-500">*</span></label>
                    <select name="status" class="form-select font-bold">
                        <option value="planning">Planning</option>
                        <option value="active" selected>Active</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.budget_dh') }}</label>
                    <input type="number" name="budget" step="0.01" class="form-input font-bold" placeholder="0.00">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.deadline_target') }}</label>
                    <input type="date" name="end_date" class="form-input">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ __('app.description_scope') }}</label>
                <textarea name="description" rows="4" class="form-input rounded-xl border-gray-200" placeholder="{{ __('app.describe_project_goals_constraints') }}"></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="btn btn-primary px-12 py-4 text-lg font-black uppercase tracking-tighter">{{ __('app.create_project') }}</button>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary px-12 py-4 text-xs font-black uppercase tracking-widest flex items-center">{{ __('app.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
