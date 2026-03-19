<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        line-height: 1.5;
    }

    .page { padding: 28px 36px; }

    /* ── Шапка ── */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .header-left { width: 55%; vertical-align: top; }
    .header-right { width: 45%; vertical-align: top; text-align: right; }

    .brand { font-size: 20px; font-weight: bold; color: #1a3a5c; letter-spacing: 0.5px; }
    .brand-sub { color: #777; font-size: 10px; margin-top: 2px; }

    .invoice-label { font-size: 17px; font-weight: bold; color: #1a3a5c; text-transform: uppercase; }
    .invoice-meta { color: #555; font-size: 10px; margin-top: 4px; }

    /* ── Статус ── */
    .status-wrap { margin-top: 8px; }
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 0.3px;
    }
    .badge-pending  { background: #fff3cd; color: #7d6000; border: 1px solid #ffc107; }
    .badge-paid     { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .badge-cancelled{ background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

    /* ── Разделитель ── */
    .divider { border: none; border-top: 2px solid #1a3a5c; margin: 14px 0; }
    .divider-light { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }

    /* ── Стороны ── */
    .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .party-cell { width: 50%; vertical-align: top; padding-right: 16px; }
    .party-cell:last-child { padding-right: 0; padding-left: 16px; border-left: 1px solid #e5e7eb; }
    .party-title {
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        color: #9ca3af;
        letter-spacing: 0.8px;
        margin-bottom: 5px;
    }
    .party-name { font-size: 12px; font-weight: bold; color: #1a3a5c; margin-bottom: 3px; }
    .party-line { color: #555; font-size: 10px; margin-bottom: 2px; }

    /* ── Срок оплаты ── */
    .due-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 4px;
        padding: 7px 12px;
        margin-bottom: 14px;
        font-size: 10px;
    }
    .due-box strong { color: #1e40af; }

    /* ── Таблица позиций ── */
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    table.items thead tr { background: #1a3a5c; }
    table.items thead th {
        padding: 7px 9px;
        text-align: left;
        font-size: 10px;
        color: #fff;
        font-weight: bold;
    }
    table.items thead th.r { text-align: right; }
    table.items tbody tr { border-bottom: 1px solid #f0f0f0; }
    table.items tbody tr:nth-child(even) { background: #f8fafc; }
    table.items tbody td { padding: 6px 9px; font-size: 10px; color: #333; }
    table.items tbody td.r { text-align: right; }
    table.items tfoot tr { background: #e8f0fb; }
    table.items tfoot td {
        padding: 8px 9px;
        font-weight: bold;
        font-size: 11px;
        border-top: 2px solid #1a3a5c;
    }
    table.items tfoot td.r { text-align: right; }

    /* ── Итого ── */
    .total-block {
        background: #1a3a5c;
        color: #fff;
        padding: 12px 16px;
        margin-top: 14px;
        margin-bottom: 16px;
        border-radius: 4px;
    }
    .total-table { width: 100%; border-collapse: collapse; }
    .total-label { font-size: 10px; opacity: 0.75; }
    .total-amount { font-size: 19px; font-weight: bold; text-align: right; }

    /* ── Реквизиты для оплаты ── */
    .payment-box {
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 10px 14px;
        margin-bottom: 16px;
        background: #f9fafb;
    }
    .payment-title {
        font-size: 10px;
        font-weight: bold;
        color: #1a3a5c;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
        padding-bottom: 5px;
        border-bottom: 1px solid #e5e7eb;
    }
    .payment-table { width: 100%; border-collapse: collapse; }
    .payment-table td { padding: 2px 0; font-size: 10px; vertical-align: top; }
    .payment-table td.label { color: #6b7280; width: 160px; }

    /* ── Примечание ── */
    .note-box {
        border-left: 3px solid #1a3a5c;
        padding: 6px 10px;
        font-size: 10px;
        color: #555;
        margin-bottom: 16px;
        background: #f8fafc;
    }

    /* ── Подписи ── */
    .sign-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
    .sign-cell { width: 50%; vertical-align: top; }
    .sign-cell:last-child { text-align: right; }
    .sign-line { border-bottom: 1px solid #9ca3af; margin-bottom: 3px; display: inline-block; width: 180px; }
    .sign-label { font-size: 9px; color: #9ca3af; }

    /* ── Подвал ── */
    .footer {
        margin-top: 24px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
        font-size: 9px;
        color: #9ca3af;
        text-align: center;
    }
</style>
</head>
<body>
<div class="page">

    {{-- ── ШАПКА ── --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="brand">ТРАК ИМПОРТ</div>
                <div class="brand-sub">Поставщик автомобильных запчастей</div>
            </td>
            <td class="header-right">
                <div class="invoice-label">Счёт на оплату</div>
                <div class="invoice-meta">№ {{ $invoice->invoice_number }}</div>
                <div class="invoice-meta">от {{ $invoice->created_at->format('d.m.Y') }}</div>
                <div class="status-wrap">
                    @php
                        $badgeClass = match($invoice->truck_status->value) {
                            'paid'      => 'badge-paid',
                            'cancelled' => 'badge-cancelled',
                            default     => 'badge-pending',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $invoice->truck_status->label() }}</span>
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ── СТОРОНЫ ── --}}
    <table class="parties-table">
        <tr>
            <td class="party-cell">
                <div class="party-title">Поставщик</div>
                <div class="party-name">ООО «Трак Импорт»</div>
                <div class="party-line">ИНН: 0000000000 / КПП: 000000000</div>
                <div class="party-line">г. Москва, ул. Примерная, д. 1</div>
                <div class="party-line">Тел.: +7 (000) 000-00-00</div>
                <div class="party-line">info@truck-import.ru</div>
            </td>
            <td class="party-cell">
                <div class="party-title">Заказчик</div>
                @php
                    $customer = $invoice->order?->companyAdmin ?? $invoice->order?->user;
                @endphp
                @if($customer)
                    <div class="party-name">{{ $customer->company ?? ($customer->full_name ?? $customer->name) }}</div>
                    @if($customer->customer_id)
                        <div class="party-line">ID заказчика: {{ $customer->customer_id }}</div>
                    @endif
                    @if($customer->requisites)
                        <div class="party-line" style="white-space:pre-wrap">{{ $customer->requisites }}</div>
                    @elseif($customer->inn ?? $customer->ogrn ?? $customer->bank_account ?? $customer->bank ?? $customer->bik)
                        @if($customer->inn)
                            <div class="party-line">ИНН: {{ $customer->inn }}@if($customer->ogrn) / ОГРН: {{ $customer->ogrn }}@endif</div>
                        @endif
                        @if($customer->bank_account)
                            <div class="party-line">Р/с: {{ $customer->bank_account }}@if($customer->bank) в {{ $customer->bank }}@endif</div>
                        @endif
                        @if($customer->bik)
                            <div class="party-line">БИК: {{ $customer->bik }}@if($customer->correspondent_account) / К/с: {{ $customer->correspondent_account }}@endif</div>
                        @endif
                        @if($customer->full_name ?? $customer->name)
                            <div class="party-line">{{ $customer->full_name ?? $customer->name }}</div>
                        @endif
                        @if($customer->phone)
                            <div class="party-line">Тел.: {{ $customer->phone }}</div>
                        @endif
                        @if($customer->email)
                            <div class="party-line">{{ $customer->email }}</div>
                        @endif
                    @else
                        @if($customer->full_name ?? $customer->name)
                            <div class="party-line">{{ $customer->full_name ?? $customer->name }}</div>
                        @endif
                        @if($customer->phone)
                            <div class="party-line">Тел.: {{ $customer->phone }}</div>
                        @endif
                        @if($customer->email)
                            <div class="party-line">{{ $customer->email }}</div>
                        @endif
                    @endif
                @else
                    <div class="party-line">—</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── СРОК ОПЛАТЫ ── --}}
    @if($invoice->due_date)
    <div class="due-box">
        <strong>Срок оплаты:</strong> {{ $invoice->due_date->format('d.m.Y') }}
        @if($invoice->due_date->isPast() && $invoice->truck_status->value !== 'paid')
            &nbsp;— <span style="color:#dc2626; font-weight:bold">просрочен</span>
        @endif
    </div>
    @endif

    {{-- ── ПОЗИЦИИ ── --}}
    @if($invoice->order?->items->count() > 0)
    <table class="items">
        <thead>
            <tr>
                <th style="width:26px">#</th>
                <th>Наименование</th>
                <th style="width:90px">Артикул</th>
                <th class="r" style="width:52px">Кол-во</th>
                <th class="r" style="width:90px">Цена, ₽</th>
                <th class="r" style="width:100px">Сумма, ₽</th>
            </tr>
        </thead>
        <tbody>
        @foreach($invoice->order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->title ?? '—' }}</td>
                <td style="color:#6b7280">{{ $item->product->sku ?? '—' }}</td>
                <td class="r">{{ $item->quantity }}</td>
                <td class="r">{{ number_format($item->price, 2, '.', ' ') }}</td>
                <td class="r">{{ number_format($item->subtotal, 2, '.', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right; font-size:11px">Итого:</td>
                <td class="r">{{ number_format($invoice->order->total, 2, '.', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- ── ИТОГО К ОПЛАТЕ ── --}}
    <div class="total-block">
        <table class="total-table">
            <tr>
                <td class="total-label">ИТОГО К ОПЛАТЕ ПО СЧЁТУ</td>
                <td class="total-amount">{{ number_format($invoice->amount, 2, '.', ' ') }} ₽</td>
            </tr>
        </table>
    </div>

    {{-- ── РЕКВИЗИТЫ ДЛЯ ОПЛАТЫ ── --}}
    <div class="payment-box">
        <div class="payment-title">Реквизиты для оплаты</div>
        <table class="payment-table">
            <tr>
                <td class="label">Получатель:</td>
                <td><strong>ООО «Трак Импорт»</strong></td>
            </tr>
            <tr>
                <td class="label">Банк получателя:</td>
                <td>ПАО «Сбербанк России»</td>
            </tr>
            <tr>
                <td class="label">БИК:</td>
                <td>044525225</td>
            </tr>
            <tr>
                <td class="label">Корр. счёт:</td>
                <td>30101810400000000225</td>
            </tr>
            <tr>
                <td class="label">Расч. счёт:</td>
                <td>40702810000000000000</td>
            </tr>
            <tr>
                <td class="label">Назначение платежа:</td>
                <td>Оплата по счёту № {{ $invoice->invoice_number }} от {{ $invoice->created_at->format('d.m.Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- ── ПРИМЕЧАНИЕ ── --}}
    @if($invoice->notes)
    <div class="note-box">
        <strong>Примечание:</strong> {{ $invoice->notes }}
    </div>
    @endif

    {{-- ── ПОДПИСИ ── --}}
    <table class="sign-table">
        <tr>
            <td class="sign-cell">
                <div style="font-size:10px; margin-bottom:14px">
                    Выставил:
                    <strong>{{ $invoice->creator->full_name ?? $invoice->creator->name }}</strong>
                    <span style="color:#9ca3af">({{ $invoice->creator->role?->label() }})</span>
                </div>
                <div class="sign-line">&nbsp;</div><br>
                <div class="sign-label">Подпись / М.П.</div>
            </td>
            <td class="sign-cell">
                @if($invoice->paid_at)
                <div style="font-size:10px; color:#065f46; font-weight:bold; margin-bottom:14px">
                    ✓ Оплачен {{ $invoice->paid_at->format('d.m.Y') }}
                </div>
                @else
                <div style="height:32px"></div>
                @endif
                <div class="sign-line" style="float:right">&nbsp;</div><br>
                <div class="sign-label">Подпись получателя</div>
            </td>
        </tr>
    </table>

    {{-- ── ПОДВАЛ ── --}}
    <div class="footer">
        Документ сформирован {{ now()->format('d.m.Y H:i') }} &nbsp;·&nbsp; Трак Импорт &nbsp;·&nbsp; truck-import.ru
    </div>

</div>
</body>
</html>
