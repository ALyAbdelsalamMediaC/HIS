@if(!empty($messages))
    <div id="{{ $toastId }}" class="toast-container position-fixed bottom-0 end-0 p-4 z-index-11">
        <div class="toast align-items-center text-bg-{{ $type }} border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    @foreach($messages as $message)
                        <div class="font-size-md">{{ $message }}</div>
                    @endforeach
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toastEl = document.getElementById('{{ $toastId }}').querySelector('.toast');
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        </script>
    @endpush
@endif