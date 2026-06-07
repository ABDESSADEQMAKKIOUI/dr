@extends('layouts.app')
@section('title', __('app.deposits'))
@php $pageTitle = __('app.deposits'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.deposits')]]; @endphp
@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-800">{{ __('app.deposits') }}</h3>
        <a href="{{ route('deposits.create') }}" class="btn btn-primary">+ {{ __('app.add_deposit') }}</a>
    </div>

    {{-- Filter Card --}}
    <div class="card p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="form-label text-xs uppercase text-gray-500 font-bold mb-1">{{ __('app.category') }}</label>
                <select name="category_id" class="form-select text-sm">
                    <option value="">{{ __('app.all_categories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label text-xs uppercase text-gray-500 font-bold mb-1">{{ __('app.from_date') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs uppercase text-gray-500 font-bold mb-1">{{ __('app.to_date') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-sm">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="btn btn-secondary flex-1">{{ __('app.filter') }}</button>
                <a href="{{ route('deposits.index') }}" class="btn btn-light" title="Reset"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <p class="text-blue-100 text-sm uppercase font-bold">{{ __('app.total_amount') }}</p>
            <h4 class="text-3xl font-extrabold mt-1">{{ number_format($total, 2) }} DH</h4>
        </div>
        <!-- Add more stats if needed -->
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.date') }}</th>
                        <th>{{ __('app.reference') }}</th>
                        <th>{{ __('app.category') }}</th>
                        <th>{{ __('app.account') }}</th>
                        <th>{{ __('app.payment_method') }}</th>
                        <th class="text-right">{{ __('app.amount') }}</th>
                        <th>{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                    <tr>
                        <td>{{ $deposit->date->format('d/m/Y') }}</td>
                        <td><span class="font-mono text-gray-600">{{ $deposit->reference ?? '—' }}</span></td>
                        <td><span class="badge badge-secondary">{{ $deposit->category->name ?? 'General' }}</span></td>
                        <td>{{ $deposit->account->name ?? '—' }}</td>
                        <td>{{ $deposit->payment_method ?? '—' }}</td>
                        <td class="text-right font-bold text-green-600">{{ number_format($deposit->amount, 2) }} DH</td>
                        <td class="flex space-x-3">
                            <a href="{{ route('deposits.show', $deposit) }}" class="text-blue-600 hover:underline text-sm">{{ __('app.view') }}</a>
                            <a href="{{ route('deposits.edit', $deposit) }}" class="text-green-600 hover:underline text-sm">{{ __('app.edit') }}</a>
                            <form action="{{ route('deposits.destroy', $deposit) }}" method="POST" onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">{{ __('app.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-500 py-12">{{ __('app.no_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $deposits->links() }}</div>
    </div>
</div>
@endsection
