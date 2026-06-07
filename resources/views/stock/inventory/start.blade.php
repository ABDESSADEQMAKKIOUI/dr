@extends('layouts.app')
@section('title', 'Start Stock Count')
@php $pageTitle = 'Start Stock Count'; $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>'Stock Counts','url'=>route('stock.inventory.counts')],['label'=>'New']]; @endphp
@section('content')
<div class="card max-w-xl mx-auto">
    <div class="card-header"><h3 class="text-lg font-semibold">Start New Stock Count</h3></div>
    <form method="POST" action="{{ route('stock.inventory.count-create') }}" class="p-6 space-y-4">
        @csrf
        <div>
            <label class="form-label">Warehouse <span class="text-red-500">*</span></label>
            <select name="warehouse_id" class="form-select" required>
                <option value="">-- Select Warehouse --</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
            @error('warehouse_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-input" placeholder="Optional notes..."></textarea>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Start Count</button>
            <a href="{{ route('stock.inventory.counts') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
