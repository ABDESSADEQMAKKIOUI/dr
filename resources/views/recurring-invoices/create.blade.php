@extends('layouts.app')
@section('title', __('app.create_recurring_invoice'))
@php $pageTitle = __('app.create_recurring_invoice'); $breadcrumbs = [['label'=>__('app.dashboard'),'url'=>route('dashboard')],['label'=>__('app.recurring_invoices'),'url'=>route('recurring-invoices.index')],['label'=>__('app.create')]]; @endphp
@section('content')
<div class="max-w-5xl mx-auto shadow-2xl rounded-2xl overflow-hidden">
    <div class="bg-indigo-700 p-8 text-white">
        <h3 class="text-2xl font-black italic uppercase tracking-tighter">{{ __('app.setup_subscription_billing') }}</h3>
        <p class="text-indigo-200 text-sm">{{ __('app.invoice_items_will_repeat_based_on_frequency') }}</p>
    </div>
    
    <form method="POST" action="{{ route('recurring-invoices.store') }}" class="bg-white p-8 space-y-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.customer') }} *</label>
                <select name="customer_id" class="form-select border-gray-200 font-bold" required>
                    <option value="">-- {{ __('app.select_customer') }} --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.frequency') }} *</label>
                    <select name="frequency" class="form-select border-gray-200 font-bold" required>
                        <option value="daily">{{ __('app.daily') }}</option>
                        <option value="weekly">{{ __('app.weekly') }}</option>
                        <option value="monthly" selected>{{ __('app.monthly') }}</option>
                        <option value="yearly">{{ __('app.yearly') }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.start_date') }} *</label>
                    <input type="date" name="next_run_at" class="form-input border-gray-200" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <label class="text-[10px] font-black uppercase text-gray-500 tracking-widest">{{ __('app.invoice_items_preset') }}</label>
            <div id="items-container" class="space-y-3">
                <div class="item-row grid grid-cols-12 gap-3 items-end bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="col-span-12 md:col-span-6">
                        <label class="text-[9px] uppercase font-bold text-gray-400 mb-1 block">{{ __('app.product_service') }}</label>
                        <select name="items[0][product_id]" class="form-select text-sm py-2" required>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->sale_price, 2) }} DH)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="text-[9px] uppercase font-bold text-gray-400 mb-1 block">{{ __('app.quantity') }}</label>
                        <input type="number" name="items[0][quantity]" class="form-input text-sm py-2" value="1" min="1" required>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <label class="text-[9px] uppercase font-bold text-gray-400 mb-1 block">{{ __('app.unit_price') }} (DH)</label>
                        <input type="number" step="0.01" name="items[0][price]" class="form-input text-sm py-2" placeholder="0.00" required>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addItem()" class="text-xs font-black text-indigo-600 uppercase tracking-widest hover:underline">+ {{ __('app.add_another_item') }}</button>
        </div>

        <div class="flex items-center gap-4 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl">
            <input type="hidden" name="send_email" value="0">
            <input type="checkbox" name="send_email" id="send_email" value="1" class="form-checkbox h-6 w-6 text-indigo-600 rounded-lg">
            <label for="send_email" class="text-sm font-bold text-indigo-900">{{ __('app.automatically_send_invoice_to_customer_email') }}</label>
        </div>

        <div class="pt-6 flex gap-4 border-t">
            <button type="submit" class="btn btn-primary px-12 py-4 text-lg font-black uppercase tracking-tighter shadow-lg shadow-indigo-200">{{ __('app.set_recurring_invoice') }}</button>
            <a href="{{ route('recurring-invoices.index') }}" class="btn btn-secondary flex items-center px-8">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>

<script>
    let itemIndex = 1;
    function addItem() {
        const container = document.getElementById('items-container');
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        
        // Update input names
        newRow.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('[0]', `[${itemIndex}]`);
            if(input.tagName === 'INPUT') input.value = input.name.includes('quantity') ? '1' : '';
        });

        // Add remove button
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-red-500 text-xs font-bold mt-2 hover:underline block';
        removeBtn.innerText = '{{ __('app.remove_item') }}';
        removeBtn.onclick = function() { this.parentElement.remove(); };
        newRow.appendChild(removeBtn);

        container.appendChild(newRow);
        itemIndex++;
    }
</script>
@endsection
