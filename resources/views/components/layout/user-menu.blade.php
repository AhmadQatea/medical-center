{{-- Layout: User menu — CarePoint header profile --}}
@props([
    'user' => auth()->user(),
])

@if ($user)
    <div class="flex items-center gap-2 sm:gap-3">
        <a
            href="{{ route('booking.index') }}"
            target="_blank"
            rel="noopener"
            class="hidden items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-foreground-muted transition hover:bg-surface-subtle hover:text-primary sm:inline-flex"
            title="صفحة الحجز العامة"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            <span>واتساب</span>
        </a>

        <a
            href="{{ route('doctor.profile.index') }}"
            class="flex items-center gap-2 rounded-xl px-2 py-1.5 transition hover:bg-surface-subtle sm:gap-3 sm:px-3"
        >
            <x-ui.avatar :name="$user->name" size="sm" />
            <div class="hidden min-w-0 text-start sm:block">
                <p class="truncate text-sm font-semibold text-foreground">{{ $user->name }}</p>
                <p class="truncate text-xs text-foreground-muted">
                    {{ $user->isAdmin() ? 'مسؤول المركز' : 'طبيب' }}
                </p>
            </div>
        </a>
    </div>
@endif
