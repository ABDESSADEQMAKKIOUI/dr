@extends('layouts.app')
@section('title', __('app.error_logs'))
@php $pageTitle = __('app.error_logs'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.system')],['label'=>__('app.error_logs')]]; @endphp
@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-800">{{ __('app.system_health_logs') }}</h3>
        <form action="{{ route('error-logs.clear') }}" method="POST" onsubmit="return confirm('{{ __('app.clear_all_logs_permanently') }}')">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm px-6 font-black uppercase tracking-widest italic flex items-center gap-2">
                <i class="fas fa-trash-alt"></i> {{ __('app.clear_all_logs') }}
            </button>
        </form>
    </div>

    {{-- Filters --}}
    <div class="card p-4 bg-gray-50 border-gray-200">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="{{ __('app.search_by_message_or_stack') }}...">
            </div>
            <div class="flex gap-2">
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-sm">
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-sm">
            </div>
            <button type="submit" class="btn btn-secondary px-8 font-bold">{{ __('app.filter') }}</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table text-xs">
                <thead>
                    <tr class="bg-gray-800 text-white font-bold uppercase tracking-widest">
                        <th class="py-4">{{ __('app.timestamp') }}</th>
                        <th class="py-4">{{ __('app.user') }}</th>
                        <th class="py-4">{{ __('app.error_message') }}</th>
                        <th class="py-4 text-right">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="font-mono">
                    @forelse($logs as $log)
                    <tr class="border-b border-gray-100 hover:bg-red-50/20">
                        <td class="text-gray-400 py-3">{{ $log->created_at->format('d/m H:i:s') }}</td>
                        <td class="font-bold text-blue-600">{{ $log->user->name ?? __('app.guest') }}</td>
                        <td class="max-w-md truncate cursor-help group relative" title="{{ $log->message }}">
                            <span class="text-red-700 font-bold">{{ $log->message }}</span>
                            <div class="hidden group-hover:block absolute z-20 top-full left-0 bg-gray-900 text-white p-4 rounded-xl text-[10px] w-[500px] shadow-2xl overflow-auto max-h-60 leading-relaxed border-2 border-red-500 whitespace-pre">
                                {{ $log->stack_trace }}
                            </div>
                        </td>
                        <td class="text-right">
                            <form action="{{ route('error-logs.destroy', $log) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors"><i class="fas fa-times-circle"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-400 py-12 italic tracking-[0.3em] font-black uppercase">{{ __('app.system_healthy_no_logs') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
