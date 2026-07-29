{{--
    Flash Messages — live region for screen readers, auto-dismiss success.
--}}
@php
    $hasFlash = session('success') || session('error') || session('warning') || session('status')
        || (isset($errors) && $errors->any());
@endphp

@if ($hasFlash)
    <div
        class="mb-6 space-y-3"
        aria-live="polite"
        aria-atomic="true"
    >
        @if (session('success'))
            <x-ui.alert variant="success" title="تم بنجاح" :dismissible="true">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="danger" title="حدث خطأ" :dismissible="true">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if (session('warning'))
            <x-ui.alert variant="warning" title="تنبيه" :dismissible="true">
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if (session('status'))
            <x-ui.alert variant="info" :dismissible="true">
                {{ session('status') }}
            </x-ui.alert>
        @endif

        @if (isset($errors) && $errors->any())
            <x-ui.alert variant="danger" title="يرجى تصحيح الحقول التالية">
                <ul class="mt-1 list-disc space-y-1.5 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>
@endif
