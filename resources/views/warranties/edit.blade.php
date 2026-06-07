@extends('layouts.app')
@section('title', 'Edit Warranty')
@php $pageTitle = 'Edit Warranty'; $breadcrumbs = [['label'=>'Dashboard','url'=>route('dashboard')],['label'=>'Warranties','url'=>route('warranties.index')],['label'=>'Edit']]; @endphp
@section('content')
<div class="card max-w-2xl mx-auto">
    <div class="card-header"><h3 class="text-lg font-semibold">Edit Warranty #{{ $warranty->id }}</h3></div>
    <form method="POST" action="{{ route('warranties.update', $warranty) }}" class="p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                @foreach(['active','expired','claimed','voided'] as $s)
                <option value="{{ $s }}" @selected($warranty->status===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="3" class="form-input">{{ old('notes', $warranty->notes) }}</textarea>
        </div>
        @if($errors->any())
        <div class="text-red-600 text-sm">{{ $errors->first() }}</div>
        @endif
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('warranties.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
