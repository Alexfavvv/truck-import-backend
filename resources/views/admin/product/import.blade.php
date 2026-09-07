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
