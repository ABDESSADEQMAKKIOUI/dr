@extends('layouts.app')
@section('title', __('app.module_management'))
@php $pageTitle = __('app.module_management'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.system')],['label'=>__('app.modules')]]; @endphp
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden mb-10">
        <div class="relative z-10">
            <h2 class="text-3xl font-black italic tracking-tighter uppercase mb-2">{{ __('app.feature_control_panel') }}</h2>
            <p class="text-blue-100 max-w-lg leading-relaxed">{{ __('app.enable_or_disable_major_modules_to_customize_your_erp_experience') }}</p>
        </div>
        <i class="fas fa-cubes absolute right-10 top-1/2 -translate-y-1/2 text-8xl text-white/10 italic"></i>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($modules as $module)
        <div class="card p-6 flex items-center justify-between hover:shadow-2xl transition-all duration-300 border-l-4 @if($module->is_enabled) border-green-500 @else border-gray-300 @endif">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl @if($module->is_enabled) bg-green-50 text-green-600 @else bg-gray-50 text-gray-400 @endif flex items-center justify-center text-2xl shadow-inner italic">
                    <i class="fas @if($module->key == 'hrm') fa-users @elseif($module->key == 'crm') fa-handshake @elseif($module->key == 'projects') fa-tasks @elseif($module->key == 'inventory') fa-boxes @else fa-puzzle-piece @endif"></i>
                </div>
                <div>
                    <h4 class="text-lg font-black text-gray-800 uppercase tracking-tight">{{ $module->name }}</h4>
                    <p class="text-xs font-bold text-gray-400">{{ $module->key }}</p>
                </div>
            </div>

            <form action="{{ route('modules.toggle', $module) }}" method="POST">
                @csrf
                <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none @if($module->is_enabled) bg-green-500 @else bg-gray-300 @endif">
                  <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform @if($module->is_enabled) translate-x-6 @else translate-x-1 @endif"></span>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    @if($modules->isEmpty())
        <div class="card p-20 text-center opacity-50 border-dashed border-2">
            <i class="fas fa-ghost text-4xl mb-4 text-gray-400"></i>
            <p class="font-black italic text-gray-400 uppercase tracking-widest">{{ __('app.no_modules_registered') }}</p>
        </div>
    @endif
</div>
@endsection
