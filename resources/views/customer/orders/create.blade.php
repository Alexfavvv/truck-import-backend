@extends('customer.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">Новый заказ</h1></div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Назад
                </a>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('customer.orders.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Позиции заказа</h3>
                            <button type="button" class="btn btn-success btn-sm" id="addItem">
                                <i class="fas fa-plus mr-1"></i>Добавить товар
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr><th>Товар</th><th style="width:90px">Кол-во</th>
                                        <th style="width:120px">Цена (₽)</th><th style="width:40px"></th></tr>
                                </thead>
                                <tbody id="itemsList">
                                    <tr id="emptyRow" class="text-center text-muted">
                                        <td colspan="4" class="py-4">Нажмите «Добавить товар»</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Итого:</th>
                                        <th id="total">0.00 ₽</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Примечание</h3></div>
                        <div class="card-body">
                            <textarea name="notes" class="form-control" rows="4"
                                      placeholder="Укажите особые требования...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane mr-1"></i>Отправить заказ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<template id="rowTpl">
    <tr class="item-row">
        <td>
            <select name="items[__I__][product_id]" class="form-control form-control-sm prod-sel" required>
                <option value="">— Товар —</option>
                @foreach(\App\Models\Product::select('id','title','price')->orderBy('title')->get() as $p)
                    <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->title }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[__I__][quantity]" class="form-control form-control-sm item-qty" value="1" min="1" required></td>
        <td><input type="number" name="items[__I__][price]" class="form-control form-control-sm item-price" step="0.01" min="0" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger rm"><i class="fas fa-times"></i></button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let idx = 0;
document.getElementById('addItem').onclick = function() {
    document.getElementById('emptyRow').style.display = 'none';
    const html = document.getElementById('rowTpl').innerHTML.replace(/__I__/g, idx++);
    const wrap = document.createElement('tbody');
    wrap.innerHTML = html;
    const row = wrap.firstElementChild;
    document.getElementById('itemsList').appendChild(row);
    row.querySelector('.prod-sel').onchange = function() {
        const price = this.selectedOptions[0]?.dataset.price || 0;
        row.querySelector('.item-price').value = parseFloat(price).toFixed(2);
        recalc();
    };
    row.querySelector('.item-qty').oninput = recalc;
    row.querySelector('.item-price').oninput = recalc;
    row.querySelector('.rm').onclick = function() {
        row.remove();
        if (!document.querySelectorAll('.item-row').length) document.getElementById('emptyRow').style.display = '';
        recalc();
    };
};
function recalc() {
    let t = 0;
    document.querySelectorAll('.item-row').forEach(r => {
        t += (parseFloat(r.querySelector('.item-qty').value)||0) * (parseFloat(r.querySelector('.item-price').value)||0);
    });
    document.getElementById('total').textContent = t.toFixed(2) + ' ₽';
}
</script>
@endpush
