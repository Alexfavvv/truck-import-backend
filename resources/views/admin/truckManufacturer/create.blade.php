@extends('admin.layouts.main')

@section('content')
    <div class="container mt-4">

        <!-- Общий блок ошибок -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Блок успешного сохранения -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Создание нового производителя грузовиков</h2>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.truck-manufacturers.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="title">Название производителя <small class="text-muted">(уникальное)</small></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title"
                               value="{{ old('title') }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug (URL) <small class="text-muted">(уникальный)</small></label>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror"
                               id="slug" name="slug"
                               value="{{ old('slug') }}" required>
                        @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4 d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('admin.truck-manufacturers.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Сохранить производителя
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
