@extends('admin.layouts.main')

@section('content')
    <div class="container-fluid py-3">
        <h1 class="mb-4">Импорт двигателей и блоков</h1>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <p>Загрузите файл XLSX или CSV с колонками шаблона. Обработка выполняется только в каталоге двигателей.</p>
                <p><strong>Обязательные колонки:</strong> sku, title, manufacturer, type.</p>
                <a class="btn btn-outline-secondary mb-3" href="{{ route('admin.engines.import.template') }}">Скачать шаблон</a>

                <form action="{{ route('admin.engines.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="engine-import-file">Файл XLSX или CSV</label>
                        <input id="engine-import-file" name="file" type="file" accept=".xlsx,.csv" required
                               class="form-control-file @error('file') is-invalid @enderror">
                        @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Загрузить каталог</button>
                </form>
            </div>
        </div>

        @if($report = session('engine_import_report'))
            <div class="card mt-4">
                <div class="card-header"><h2 class="h5 mb-0">Результат импорта</h2></div>
                <div class="card-body">
                    <p><strong>Всего строк:</strong> {{ $report['total'] }}</p>
                    <p><strong>Создано:</strong> {{ $report['created'] }}</p>
                    <p><strong>Обновлено:</strong> {{ $report['updated'] }}</p>
                    <p><strong>Пропущено:</strong> {{ $report['skipped'] }}</p>
                    <p><strong>Ошибок:</strong> {{ count($report['errors']) }}</p>
                    @if($report['errors'])
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead><tr><th>Строка</th><th>SKU</th><th>Причина</th></tr></thead>
                                <tbody>
                                @foreach($report['errors'] as $error)
                                    <tr>
                                        <td>{{ $error['row'] }}</td>
                                        <td>{{ $error['sku'] ?? '—' }}</td>
                                        <td>{{ $error['reason'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
