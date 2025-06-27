@extends('admin.layouts.main')

@section('content')
    {{-- Блок для вывода общих сообщений об успехе --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Основной контейнер страницы --}}
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="fas fa-upload"></i> Импорт товаров из Excel</h2>
            </div>

            {{-- Блок для вывода ошибок валидации формы (например, если файл не выбран или неверный тип) --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Блок для вывода ошибок импорта, собранных из ProductsImport (пропущенные строки) --}}
            @if (session('import_failures'))
                <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3">
                    <h5 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Обнаружены ошибки в некоторых строках файла:</h5>
                    <ul class="mb-0">
                        @foreach (session('import_failures') as $failure)
                            <li>Строка **{{ $failure['row'] }}**: Поле '{{ $failure['attribute'] }}' - {{ implode(', ', $failure['errors']) }} (Значение: **{{ $failure['values'][$failure['attribute']] ?? 'N/A' }}**)</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Закрыть">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card-body">
                <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="file">Выберите Excel файл (.xlsx, .xls, .csv): <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            {{-- Input для загрузки файла --}}
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
    </div>

    {{-- Небольшой скрипт для обновления текста "Выберите файл" в custom-file-input --}}
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
@endsection
