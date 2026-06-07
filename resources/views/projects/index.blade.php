@extends('layouts.app')
@section('title', __('app.projects'))
@php $pageTitle = __('app.projects'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.projects')]]; @endphp
@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-800">{{ __('app.projects_management') }}</h3>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">+ {{ __('app.create_new_project') }}</a>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="{{ __('app.search_projects') }}...">
            </div>
            <div class="w-48">
                <select name="status" class="form-select">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    @foreach(['planning', 'active', 'on_hold', 'completed', 'cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ __('app.' . $st) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">{{ __('app.search') }}</button>
            <a href="{{ route('projects.index') }}" class="btn btn-light"><i class="fas fa-undo"></i></a>
        </form>
    </div>

    {{-- Projects Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
        <div class="card hover:shadow-2xl transition-all duration-300 border-t-4 @if($project->status == 'active') border-blue-500 @elseif($project->status == 'completed') border-green-500 @else border-gray-300 @endif">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <span class="badge @if($project->status == 'active') badge-primary @elseif($project->status == 'completed') badge-success @elseif($project->status == 'on_hold') badge-warning @else badge-secondary @endif">
                        {{ __('app.' . $project->status) }}
                    </span>
                    <p class="text-xs font-bold text-gray-400 font-mono">#PRJ-{{ $project->id }}</p>
                </div>
                
                <h4 class="text-xl font-black text-gray-900 mb-1 line-clamp-1">{{ $project->name }}</h4>
                <p class="text-sm text-blue-600 font-bold mb-4">{{ $project->customer->name ?? __('app.internal_project') }}</p>
                
                <div class="space-y-3">
                    <div class="flex justify-between text-xs text-gray-500 uppercase font-black">
                        <span>{{ __('app.tasks_progress') }}</span>
                        <span>{{ $project->tasks_count }} {{ __('app.tasks') }}</span>
                    </div>
                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                        @php $progress = $project->tasks_count > 0 ? (rand(20, 90)) : 0; @endphp {{-- In real app, calculate based on finished tasks --}}
                        <div class="h-full bg-blue-500" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between border-t pt-4">
                    <div class="flex -space-x-2">
                        @for($i=0; $i<3; $i++)
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-[10px] uppercase font-bold text-gray-500">U</div>
                        @endfor
                    </div>
                    <a href="{{ route('projects.show', $project) }}" class="text-blue-600 font-black text-sm uppercase tracking-widest hover:underline">{{ __('app.view_board') }} →</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-20 text-center card bg-gray-50 border-dashed border-2">
            <p class="text-gray-400 font-bold italic">{{ __('app.no_projects_found') }}</p>
            <a href="{{ route('projects.create') }}" class="mt-4 inline-block text-blue-500 font-black underline">{{ __('app.start_your_first_project') }}</a>
        </div>
        @endforelse
    </div>
    <div class="pt-6">{{ $projects->links() }}</div>
</div>
@endsection
