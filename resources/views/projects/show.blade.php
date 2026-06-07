@extends('layouts.app')
@section('title', $project->name)
@php $pageTitle = $project->name; $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.projects'),'url'=>route('projects.index')],['label'=>$project->name]]; @endphp
@section('content')
<div class="space-y-6">
    {{-- Project Header Details --}}
    <div class="card p-6 bg-gradient-to-r from-gray-900 to-gray-800 text-white border-none rounded-2xl">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <span class="badge @if($project->status == 'active') badge-primary @elseif($project->status == 'completed') badge-success @else badge-secondary @endif text-[10px] uppercase font-black">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                    <span class="text-xs font-mono text-gray-400">#PRJ-{{ $project->id }}</span>
                </div>
                <h2 class="text-3xl font-black tracking-tighter">{{ $project->name }}</h2>
                <p class="text-blue-400 font-bold">{{ $project->customer->name ?? __('app.internal_project') }}</p>
            </div>
            <div class="flex flex-wrap gap-4 text-right">
                <div class="px-4 py-2 bg-white/10 rounded-xl">
                    <p class="text-[10px] uppercase font-black text-gray-400 mb-1">{{ __('app.budget') }}</p>
                    <p class="text-xl font-black">{{ number_format($project->budget, 2) }} DH</p>
                </div>
                <div class="px-4 py-2 bg-white/10 rounded-xl">
                    <p class="text-[10px] uppercase font-black text-gray-400 mb-1">{{ __('app.manager') }}</p>
                    <p class="text-sm font-bold">{{ $project->user->full_name ?? '—' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary btn-sm px-4">{{ __('app.edit_settings') }}</a>
                </div>
            </div>
        </div>
        @if($project->description)
        <div class="mt-6 p-4 bg-white/5 rounded-lg border border-white/10">
            <p class="text-sm text-gray-300 leading-relaxed italic">{{ $project->description }}</p>
        </div>
        @endif
    </div>

    {{-- Kanban Board Section --}}
    <div class="flex flex-col md:flex-row gap-6 items-center justify-between">
        <h3 class="text-xl font-black uppercase italic tracking-widest text-gray-700">Project Board</h3>
        <button onclick="document.getElementById('taskFormModal').classList.remove('hidden')" class="btn btn-primary btn-sm">+ Add Task</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 min-h-[500px]">
        @foreach($statuses as $status)
        <div class="flex flex-col space-y-4">
            <div class="flex justify-between items-center px-2">
                <h4 class="text-xs font-black uppercase tracking-widest text-gray-500">
                    {{ str_replace('_', ' ', $status) }} 
                    <span class="ml-2 px-2 py-0.5 bg-gray-200 rounded-full text-[10px]">{{ count($tasks[$status] ?? []) }}</span>
                </h4>
            </div>
            
            <div class="flex-1 bg-gray-100/50 p-3 rounded-2xl space-y-3 border-2 border-dashed border-gray-200">
                @foreach($tasks[$status] ?? [] as $task)
                <div class="card p-4 shadow hover:shadow-lg transition-all cursor-pointer group relative">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[8px] uppercase font-black tracking-tighter px-2 py-0.5 rounded @if($task->priority == 'urgent') bg-red-100 text-red-600 @elseif($task->priority == 'high') bg-orange-100 text-orange-600 @else bg-blue-100 text-blue-600 @endif">
                            {{ $task->priority ?: 'low' }}
                        </span>
                        <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ route('projects.tasks.destroy', [$project, $task]) }}" method="POST" onsubmit="return confirm('Delete task?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 text-[10px]"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <h5 class="font-bold text-gray-800 text-sm leading-tight mb-3">{{ $task->title }}</h5>
                    <div class="flex justify-between items-center mt-4">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[8px] font-black text-indigo-600 border border-indigo-200 uppercase" title="{{ $task->assignee->name ?? 'Unassigned' }}">
                            {{ substr($task->assignee->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="text-[9px] font-bold text-gray-400 italic">
                            {{ $task->due_date ? $task->due_date->format('d/m') : 'No Date' }}
                        </span>
                    </div>
                </div>
                @endforeach
                @if(count($tasks[$status] ?? []) == 0)
                    <div class="py-10 text-center opacity-30 select-none">
                        <i class="fas fa-layer-group text-2xl mb-2"></i>
                        <p class="text-[10px] font-bold uppercase">{{ __('app.empty_column') }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Add Task Modal --}}
<div id="taskFormModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-blue-600 p-6 text-white flex justify-between items-center">
            <h3 class="text-xl font-black uppercase tracking-tighter">{{ __('app.new_task') }}</h3>
            <button onclick="document.getElementById('taskFormModal').classList.add('hidden')" class="text-white/60 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('projects.tasks.store', $project) }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Task Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input font-bold" required placeholder="What needs to be done?">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Status</label>
                    <select name="status" class="form-select font-bold">
                        @foreach($statuses as $st)
                            <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Priority</label>
                    <select name="priority" class="form-select font-bold text-red-600">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Assigned To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">-- Unassigned --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Due Date</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn btn-primary flex-1 py-3 font-black uppercase tracking-widest shadow-lg shadow-blue-500/20">Add Task</button>
                <button type="button" onclick="document.getElementById('taskFormModal').classList.add('hidden')" class="btn btn-secondary px-8 font-black uppercase tracking-widest">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
