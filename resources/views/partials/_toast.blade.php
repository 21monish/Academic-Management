@php
    $toastMessages = collect();
    $toastLabel = function (mixed $message): string {
        return match ($message) {
            'profile-updated' => 'Profile updated successfully.',
            'password-updated' => 'Password updated successfully.',
            'verification-link-sent' => 'A new verification link has been sent to your email address.',
            default => (string) $message,
        };
    };

    foreach (['success' => 'success', 'status' => 'success', 'error' => 'error'] as $key => $type) {
        if (session($key)) {
            $toastMessages->push([
                'type' => $type,
                'message' => $toastLabel(session($key)),
            ]);
        }
    }

    if ($errors->any()) {
        $toastMessages->push([
            'type' => 'error',
            'message' => $errors->count() === 1
                ? $errors->first()
                : 'Please fix '.$errors->count().' form errors.',
        ]);
    }
@endphp

@if($toastMessages->isNotEmpty())
    <div class="pointer-events-none fixed right-4 top-4 space-y-3 sm:right-6 sm:top-6" style="z-index: 80;" aria-live="polite" aria-atomic="true">
        @foreach($toastMessages as $toast)
            @php($isSuccess = $toast['type'] === 'success')
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5200)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-6 opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-6 opacity-0"
                class="pointer-events-auto flex w-[calc(100vw-2rem)] max-w-sm items-start gap-3 rounded-lg border bg-white p-4 shadow-xl shadow-slate-950/10 {{ $isSuccess ? 'border-emerald-200' : 'border-red-200' }}"
                role="{{ $isSuccess ? 'status' : 'alert' }}"
            >
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $isSuccess ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    @if($isSuccess)
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/>
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 4.6 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.6a2 2 0 0 0-3.4 0Z"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold {{ $isSuccess ? 'text-emerald-900' : 'text-red-900' }}">
                        {{ $isSuccess ? 'Success' : 'Error' }}
                    </p>
                    <p class="mt-1 text-sm leading-5 text-slate-700">{{ $toast['message'] }}</p>
                </div>
                <button
                    type="button"
                    @click="show = false"
                    class="grid h-7 w-7 shrink-0 place-items-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Dismiss notification"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
