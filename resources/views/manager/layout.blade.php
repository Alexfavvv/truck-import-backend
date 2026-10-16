@extends('admin.layouts.main')

@section('manager-alerts')
    @if(session('success'))
        <div class="container-fluid pt-4 px-4">
            <div class="alert alert-success mb-0" role="status">{{ session('success') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="container-fluid pt-4 px-4">
            <div class="alert alert-danger mb-0" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-submit-loading]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('[type=submit]');
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Подождите…';
                }
            });
        });
    </script>
@endpush
