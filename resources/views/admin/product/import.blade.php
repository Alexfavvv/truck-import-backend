@extends('admin.layouts.main')

@section('content')
    {{-- Основной контейнер страницы --}}
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h2 class="mb-0"><i class="fas fa-upload"></i> Импорт товаров из Excel</h2>
            </div>

            <div class="card-body">
                {{-- Форма импорта: добавлен id="importForm" --}}
                <form id="importForm" action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="file">Выберите Excel файл (.xlsx, .xls, .csv): <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror"
                                   id="file" name="file" required>
                            <label class="custom-file-label" for="file" data-browse="Обзор">Выберите файл</label>
                            @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted mt-2">Допустимые форматы: XLSX, XLS, CSV. </small>
                    </div>

                    <div class="mt-4 d-flex justify-content-end border-top pt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> Загрузить и импортировать
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header"><h2 class="mb-0">Импорт supplier XLSX</h2></div>
            <div class="card-body">
                <form action="{{ route('admin.products.import.ready.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="ready_file">XLSX файл</label>
                        <input id="ready_file" name="file" type="file" accept=".xlsx" required class="form-control-file">
                        @error('file') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="currency">Supplier currency</label>
                            <input id="currency" name="currency" value="{{ old('currency', $pricing['supplier_currency'] ?? 'EUR') }}" required class="form-control">
                            @error('currency') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="exchange_rate">Exchange rate</label>
                            <input id="exchange_rate" name="exchange_rate" type="number" step="any" min="0.00000001" value="{{ old('exchange_rate', $pricing['exchange_rate'] ?? '95') }}" required class="form-control">
                            @error('exchange_rate') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="price_multiplier">Price multiplier</label>
                            <input id="price_multiplier" name="price_multiplier" type="number" step="any" min="0.00000001" value="{{ old('price_multiplier', $pricing['price_multiplier'] ?? '2') }}" required class="form-control">
                            @error('price_multiplier') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="markup_percent">Markup percent</label>
                            <input id="markup_percent" name="markup_percent" type="number" step="any" min="0" value="{{ old('markup_percent', $pricing['markup_percent'] ?? '2.5') }}" required class="form-control">
                            @error('markup_percent') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Предпросмотр</button>
                </form>
            </div>
        </div>

        @isset($preview)
            <div class="card shadow-sm mt-4">
                <div class="card-header"><h2 class="mb-0">Предпросмотр: {{ $filename }}</h2></div>
                <div class="card-body">
                    <p class="text-muted">Статистика относится только к выборке: до 50 валидных строк среди первых 500 строк данных.</p>
                    <p>Просмотрено: {{ $preview['summary']['rows_scanned'] }};
                        валидных: {{ $preview['summary']['valid_rows'] }};
                        ошибочных: {{ $preview['summary']['invalid_rows'] }};
                        существующих SKU: {{ $preview['summary']['existing_sku'] }};
                        новых SKU: {{ $preview['summary']['new_sku'] }}.</p>
                    @if($preview['errors'])
                        <div class="alert alert-warning">
                            @foreach($preview['errors'] as $error)
                                <div>Строка {{ $error['row'] }}: {{ $error['reason'] }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr>
                                <th>SKU</th><th>Name</th><th>Supplier price</th><th>Currency</th>
                                <th>Exchange rate</th><th>Multiplier</th><th>Markup %</th>
                                <th>Automatic price</th><th>Final price</th><th>Weight</th>
                                <th>Width</th><th>Height</th><th>Length</th><th>Replacement</th>
                            </tr></thead>
                            <tbody>
                            @foreach($preview['rows'] as $row)
                                <tr>
                                    <td>{{ $row['sku'] }}</td><td>{{ $row['title'] }}</td>
                                    <td>{{ $row['supplier_price'] }}</td><td>{{ $row['currency'] }}</td>
                                    <td>{{ $row['exchange_rate'] }}</td><td>{{ $row['price_multiplier'] }}</td>
                                    <td>{{ $row['markup_percent'] }}</td><td>{{ $row['automatic_price'] }}</td>
                                    <td>{{ $row['final_price'] }}</td><td>{{ $row['weight'] ?? '—' }}</td>
                                    <td>{{ $row['width'] ?? '—' }}</td><td>{{ $row['height'] ?? '—' }}</td>
                                    <td>{{ $row['length'] ?? '—' }}</td><td>{{ $row['replacement'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($preview['summary']['valid_rows'] > 0)
                        <form action="{{ route('admin.products.import.ready.confirm') }}" method="POST">
                            @csrf
                            <input type="hidden" name="preview_token" value="{{ $token }}">
                            <button type="submit" class="btn btn-success">Запустить импорт</button>
                        </form>
                    @endif
                </div>
            </div>
        @endisset

        {{-- Блок для отображения статуса и прогресса импорта через JavaScript --}}
        <div id="statusArea" class="mt-4 card shadow-sm" style="display:none;"> {{-- Изначально скрыт --}}
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Статус импорта</h5>
            </div>
            <div class="card-body">
                <p id="statusMessage" class="fw-bold mb-3"></p>

                {{-- Контейнер прогресс-бара --}}
                <div id="progressBarContainer" class="progress mb-3" style="display:none;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>

                {{-- Детали импорта (обработано/всего) --}}
                <div id="importDetails" class="mt-2" style="display:none;">
                    <p class="mb-1">Обработано строк: <span id="processedRows" class="fw-bold">0</span></p>
                    <p class="mb-0">Всего строк: <span id="totalRows" class="fw-bold">0</span></p>
                </div>

                {{-- Контейнер для ошибок импорта (из ProductsImport) --}}
                <div id="errorList" class="alert alert-warning mt-3 mb-0" style="display:none;">
                    <h5 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle"></i> Обнаружены ошибки:</h5>
                    <ul id="failuresList" class="mb-0"></ul>
                </div>
            </div>
        </div>

        <div id="jobsArea" class="mt-4 card shadow-sm" style="display:none;">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-tasks"></i> Задачи по импорту</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                        <tr>
                            <th>Файл</th>
                            <th>Статус</th>
                            <th>Job ID</th>
                            <th>Обновлено</th>
                        </tr>
                        </thead>
                        <tbody id="jobsTableBody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Задачи импорта пока отсутствуют.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Небольшой скрипт для обновления текста "Выберите файл" в custom-file-input --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('file');
                if (fileInput) {
                    fileInput.addEventListener('change', function() {
                        const fileName = this.files[0] ? this.files[0].name : 'Выберите файл';
                        const label = this.nextElementSibling;
                        if (label && label.classList.contains('custom-file-label')) {
                            label.textContent = fileName;
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
