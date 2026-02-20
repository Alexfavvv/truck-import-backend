@extends('admin.layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Создать заказ</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>К списку
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('admin.orders.store') }}" id="orderForm">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Позиции заказа</h3>
                            <button type="button" class="btn btn-success btn-sm" id="addItem">
                                <i class="fas fa-plus mr-1"></i>Добавить позицию
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th style="width:100px">Кол-во</th>
                                            <th style="width:130px">Цена (₽)</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsList">
                                        <tr id="emptyRow" class="text-center text-muted">
                                            <td colspan="4" class="py-4">
                                                Нажмите «Добавить позицию» для добавления товаров
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Итого:</th>
                                            <th id="totalAmount">0.00 ₽</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Примечание</h3></div>
                        <div class="card-body">
                            <textarea name="notes" class="form-control" rows="4"
                                      placeholder="Примечание к заказу...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>Создать заказ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Шаблон строки товара (скрытый) --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <select name="items[__IDX__][product_id]" class="form-control form-control-sm product-select" required>
                <option value="">— Выберите товар —</option>
                @foreach(\App\Models\Product::select('id','title','price')->orderBy('title')->get() as $p)
                    <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->title }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="items[__IDX__][quantity]" class="form-control form-control-sm item-qty"
                   value="1" min="1" required>
        </td>
        <td>
            <input type="number" name="items[__IDX__][price]" class="form-control form-control-sm item-price"
                   step="0.01" min="0" required>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
    let idx = 0;

    document.getElementById('addItem').addEventListener('click', function () {
        document.getElementById('emptyRow').style.display = 'none';
        const template = document.getElementById('itemRowTemplate').innerHTML.replace(/__IDX__/g, idx++);
        const tbody = document.getElementById('itemsList');
        const div = document.createElement('tbody');
        div.innerHTML = template;
        const row = div.firstElementChild;
        tbody.appendChild(row);

        row.querySelector('.product-select').addEventListener('change', onProductChange);
        row.querySelector('.item-qty').addEventListener('input', updateTotal);
        row.querySelector('.item-price').addEventListener('input', updateTotal);
        row.querySelector('.remove-item').addEventListener('click', function () {
            row.remove();
            if (!document.querySelectorAll('.item-row').length) {
                document.getElementById('emptyRow').style.display = '';
            }
            updateTotal();
        });
    });

    function onProductChange(e) {
        const price = e.target.selectedOptions[0]?.dataset.price || 0;
        const row = e.target.closest('tr');
        row.querySelector('.item-price').value = parseFloat(price).toFixed(2);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            total += qty * price;
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2) + ' ₽';
    }
</script>
@endpush
