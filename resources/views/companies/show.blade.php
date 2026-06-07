@extends('layouts.app')
@section('title', $company->name)
@php $pageTitle = $company->name; $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.companies'),'url'=>route('companies.index')],['label'=>$company->name]]; @endphp
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h3 class="text-lg font-semibold">{{ $company->name }}</h3>
            <div class="flex gap-2">
                <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary btn-sm">{{ __('app.edit') }}</a>
                <form method="POST" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">{{ __('app.delete') }}</button></form>
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500">{{ __('app.email') }}</span>
                <p class="font-medium">{{ $company->email ?? '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">{{ __('app.phone') }}</span>
                <p class="font-medium">{{ $company->phone ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-sm text-gray-500">{{ __('app.address') }}</span>
                <p class="font-medium">{{ $company->address ?? '—' }}</p>
            </div>
        </div>
    </div>
    @if($company->employees->count())
    <div class="card">
        <div class="card-header"><h3 class="font-semibold">{{ __('app.employees') }} ({{ $company->employees->count() }})</h3></div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>{{ __('app.name') }}</th><th>{{ __('app.email') }}</th><th>{{ __('app.phone') }}</th></tr></thead>
                <tbody>
                    @foreach($company->employees as $emp)
                    <tr>
                        <td><a href="{{ route('employees.show', $emp) }}" class="text-blue-600">{{ $emp->first_name }} {{ $emp->last_name }}</a></td>
                        <td>{{ $emp->email ?? '—' }}</td>
                        <td>{{ $emp->phone ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
