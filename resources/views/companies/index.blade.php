@extends('layouts.app')
@section('title', __('app.companies'))
@php $pageTitle = __('app.companies'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.companies')]]; @endphp
@section('content')
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">{{ __('app.companies') }}</h3>
        <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">+ {{ __('app.add_company') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>#</th><th>{{ __('app.logo') }}</th><th>{{ __('app.name') }}</th><th>{{ __('app.email') }}</th><th>{{ __('app.phone') }}</th><th>{{ __('app.employees') }}</th><th>{{ __('app.actions') }}</th></tr></thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td>{{ $loop->iteration + ($companies->currentPage()-1) * $companies->perPage() }}</td>
                    <td>
                        @if($company->logo)
                            <img src="{{ asset('storage/'.$company->logo) }}" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs">—</div>
                        @endif
                    </td>
                    <td class="font-medium">{{ $company->name }}</td>
                    <td>{{ $company->email ?? '—' }}</td>
                    <td>{{ $company->phone ?? '—' }}</td>
                    <td><span class="badge badge-info">{{ $company->employees_count }}</span></td>
                    <td class="flex space-x-2">
                        <a href="{{ route('companies.show', $company) }}" class="text-blue-600 hover:text-blue-800">{{ __('app.view') }}</a>
                        <a href="{{ route('companies.edit', $company) }}" class="text-green-600 hover:text-green-800">{{ __('app.edit') }}</a>
                        <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('app.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-gray-500 py-8">{{ __('app.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $companies->links() }}</div>
</div>
@endsection
