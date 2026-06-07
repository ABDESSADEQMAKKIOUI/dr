@extends('layouts.app')
@section('title', __('app.recurring_invoices'))
@php $pageTitle = __('app.recurring_invoices'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.recurring_invoices')]]; @endphp
@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-800">{{ __('app.automated_billing') }}</h3>
        <a href="{{ route('recurring-invoices.create') }}" class="btn btn-primary">+ {{ __('app.new_subscription') }}</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.customer') }}</th>
                        <th>{{ __('app.frequency') }}</th>
                        <th>{{ __('app.next_run_at') }}</th>
                        <th>{{ __('app.total_amount') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.email_notification') }}</th>
                        <th class="text-right">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="font-bold text-gray-800">{{ $invoice->customer->name ?? '—' }}</td>
                        <td><span class="badge badge-indigo capitalize">{{ $invoice->frequency }}</span></td>
                        <td class="font-mono text-sm">{{ $invoice->next_run_at ? $invoice->next_run_at->format('d/m/Y') : '—' }}</td>
                        <td class="font-black text-blue-600">{{ number_format($invoice->total, 2) }} DH</td>
                        <td>
                            <span class="badge @if($invoice->status == 'active') badge-success @elseif($invoice->status == 'paused') badge-warning @else badge-secondary @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->send_email)
                                <span class="text-green-500 flex items-center gap-1 text-xs font-black uppercase"><i class="fas fa-check-circle"></i> {{ __('app.enabled') }}</span>
                            @else
                                <span class="text-gray-400 flex items-center gap-1 text-xs font-black uppercase"><i class="fas fa-times-circle"></i> {{ __('app.disabled') }}</span>
                            @endif
                        </td>
                        <td class="text-right flex space-x-2 justify-end">
                            <a href="{{ route('recurring-invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('recurring-invoices.edit', $invoice) }}" class="text-green-600 hover:text-green-900"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('recurring-invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('{{ __('app.confirm_delete_subscription') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-12 italic">{{ __('app.no_recurring_subscriptions_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
