@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Создать счёт</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>К списку
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('admin.invoices.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Данные счёта</h3></div>
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label>Заказ <span class="text-danger">*</span></label>
                                <select name="order_id" class="form-control @error('order_id') is-invalid @enderror" required>
                                    <option value="">— Выберите заказ —</option>
                                    @foreach($orders as $o)
                                        <option value="{{ $o->id }}"
                                            data-amount="{{ $o->total }}"
                                            {{ (old('order_id', $order?->id) == $o->id) ? 'selected' : '' }}>
                                            {{ $o->order_number }}
                                            ({{ $o->user->full_name ?? $o->user->name }},
                                            {{ number_format($o->total, 2, '.', ' ') }} ₽)
                                        </option>
                                    @endforeach
                                </select>
                                @error('order_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Сумма счёта (₽) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="0.01" min="0"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $order?->total) }}" required>
                                @error('amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Срок оплаты</label>
                                <input type="date" name="due_date"
                                       class="form-control @error('due_date') is-invalid @enderror"
                                       value="{{ old('due_date') }}">
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Примечание</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Дополнительная информация...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Создать счёт
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelector('select[name="order_id"]').addEventListener('change', function() {
        const amount = this.selectedOptions[0]?.dataset.amount;
        if (amount) {
            document.querySelector('input[name="amount"]').value = parseFloat(amount).toFixed(2);
        }
    });
</script>
@endpush
