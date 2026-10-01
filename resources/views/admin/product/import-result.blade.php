@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header"><h2 class="mb-0">Результат supplier XLSX импорта</h2></div>
            <div class="card-body">
                <p><strong>Статус:</strong> {{ $import->status }}</p>
                <p><strong>Файл:</strong> {{ $import->filename }}</p>
                <p><strong>Начат:</strong> {{ $import->started_at?->format('d.m.Y H:i:s') ?? '—' }}</p>
                <p><strong>Завершён:</strong> {{ $import->finished_at?->format('d.m.Y H:i:s') ?? '—' }}</p>
                <p><strong>Всего строк:</strong> {{ $import->rows_total }}</p>
                <p><strong>Создано:</strong> {{ $import->rows_created }}</p>
                <p><strong>Обновлено:</strong> {{ $import->rows_updated }}</p>
                <p><strong>Ошибочных:</strong> {{ $import->rows_failed }}</p>
                @if(($import->metadata['errors'] ?? []) !== [])
                    <div class="alert alert-warning">
                        @foreach(array_slice($import->metadata['errors'], 0, 10) as $error)
                            <div>Строка {{ $error['row'] }}: {{ $error['reason'] }}</div>
                        @endforeach
                    </div>
                @endif
                @if(isset($import->metadata['error']))
                    <div class="alert alert-danger">{{ $import->metadata['error'] }}</div>
                @endif
                <a href="{{ route('admin.products.import.form') }}" class="btn btn-secondary">К форме импорта</a>
            </div>
        </div>
    </div>
    @if(in_array($import->status, ['processing', 'queued'], true))
        <script>setTimeout(() => window.location.reload(), 3000);</script>
    @endif
@endsection
