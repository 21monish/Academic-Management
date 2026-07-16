@php
    $chatbotBranding = app(\App\Services\SystemSettingService::class)->branding();
    $chatbotUser = auth()->user();
    $chatbotRoleName = $chatbotUser?->role?->role_name ?? 'User';
    $chatbotIsSuperAdmin = strcasecmp($chatbotRoleName, 'Super Admin') === 0;
    try {
        $chatbotUser?->loadMissing('university');
    } catch (\Throwable $exception) {
        report($exception);
    }
    $chatbotUniversity = $chatbotIsSuperAdmin ? null : $chatbotUser?->university;
    $chatbotBrandName = $chatbotUniversity?->name ?? $chatbotBranding['application_name'];
    $chatbotLogoUrl = $chatbotUniversity?->logo_url
        ? (\Illuminate\Support\Str::startsWith($chatbotUniversity->logo_url, ['http://', 'https://', '/']) ? $chatbotUniversity->logo_url : asset($chatbotUniversity->logo_url))
        : $chatbotBranding['logo_url'];
@endphp
<div
    x-data="{
        open: false,
        loading: false,
        teaching: false,
        question: '',
        teachAnswer: '',
        lastQuestion: '',
        starterSuggestions: [
            'How do I add a student?',
            'How do I add staff?',
            'How do I manage plans?',
            'Why is a module locked?',
            'Popular questions'
        ],
        initialMessage: { from: 'bot', text: @js('Hi, I am your '.$chatbotBrandName.' assistant. Ask me anything you have taught me.'), matched: null },
        messages: [],
        storageKey: @js('chatbot.session.'.($chatbotUser?->user_id ?? 'guest')),
        csrf: document.querySelector('meta[name=csrf-token]').getAttribute('content'),
        init() {
            this.loadChat();

            this.$watch('open', value => {
                if (value) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                        this.$refs.questionInput?.focus();
                    });
                }
            });
            this.$watch('teaching', () => this.saveChat());
            this.$watch('teachAnswer', () => this.saveChat());

            document.addEventListener('submit', event => {
                const form = event.target;
                if (form instanceof HTMLFormElement && form.action.includes('/logout')) {
                    sessionStorage.removeItem(this.storageKey);
                }
            });
        },
        loadChat() {
            try {
                const saved = JSON.parse(sessionStorage.getItem(this.storageKey) || '{}');

                if (Array.isArray(saved.messages) && saved.messages.length) {
                    this.messages = saved.messages;
                    this.lastQuestion = saved.lastQuestion || '';
                    this.teaching = Boolean(saved.teaching);
                    this.teachAnswer = saved.teachAnswer || '';
                    return;
                }
            } catch (error) {
                sessionStorage.removeItem(this.storageKey);
            }

            this.messages = [this.initialMessage];
            this.saveChat();
        },
        saveChat() {
            try {
                sessionStorage.setItem(this.storageKey, JSON.stringify({
                    messages: this.messages.slice(-40),
                    lastQuestion: this.lastQuestion,
                    teaching: this.teaching,
                    teachAnswer: this.teachAnswer
                }));
            } catch (error) {
                // Browser storage can be unavailable in strict privacy modes.
            }
        },
        async readJson(response, fallbackMessage) {
            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || fallbackMessage);
                }

                return data;
            }

            if (response.status === 419) {
                throw new Error('Your session expired. Please refresh the page and try again.');
            }

            if (response.status === 401) {
                throw new Error('Please log in again to use the assistant.');
            }

            if (response.status === 403) {
                throw new Error('You do not have permission to use this chatbot action.');
            }

            throw new Error(fallbackMessage);
        },
        async ask() {
            const text = this.question.trim();
            if (!text || this.loading) return;

            this.messages.push({ from: 'user', text });
            this.lastQuestion = text;
            this.question = '';
            this.teachAnswer = '';
            this.teaching = false;
            this.loading = true;
            this.saveChat();
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch('{{ route('chatbot.ask') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf
                    },
                    body: JSON.stringify({ question: text })
                });
                const data = await this.readJson(response, 'Unable to answer right now.');

                this.messages.push({
                    from: 'bot',
                    text: data.answer,
                    suggestions: data.suggestions || [],
                    matched: data.matched_question || null
                });
                this.teaching = !data.learned && @js(hasPermission('chatbot.teach'));
                this.saveChat();
            } catch (error) {
                this.messages.push({ from: 'bot', text: error.message, matched: null });
                this.saveChat();
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.questionInput?.focus();
                });
            }
        },
        async teach() {
            const answer = this.teachAnswer.trim();
            if (!this.lastQuestion || !answer || this.loading) return;

            this.loading = true;
            try {
                const response = await fetch('{{ route('chatbot.teach') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf
                    },
                    body: JSON.stringify({ question: this.lastQuestion, answer })
                });
                const data = await this.readJson(response, 'Unable to learn right now.');

                this.messages.push({ from: 'bot', text: data.message, matched: null });
                this.teachAnswer = '';
                this.teaching = false;
                this.saveChat();
            } catch (error) {
                this.messages.push({ from: 'bot', text: error.message, matched: null });
                this.saveChat();
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        scrollToBottom() {
            if (this.$refs.messages) {
                this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
            }
        },
        askSuggestion(text) {
            this.question = text;
            this.ask();
        },
        resetChat() {
            this.messages = [this.initialMessage];
            this.question = '';
            this.teachAnswer = '';
            this.lastQuestion = '';
            this.teaching = false;
            this.saveChat();
            this.$nextTick(() => this.$refs.questionInput?.focus());
        }
    }"
    class="fixed bottom-5 right-5 z-50"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-3 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-3 opacity-0"
        class="mb-3 flex h-[min(640px,calc(100vh-7rem))] w-[calc(100vw-2.5rem)] max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
        style="display: none;"
    >
        <div class="bg-slate-950 px-4 py-4 text-white">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    @if($chatbotLogoUrl)
                        <img src="{{ $chatbotLogoUrl }}" alt="{{ $chatbotBrandName }} logo" class="h-10 w-10 shrink-0 rounded-full border-2 border-white/80 bg-white object-contain p-0.5 shadow-lg shadow-cyan-950/40">
                    @else
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-cyan-500 text-white shadow-lg shadow-cyan-950/40">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5m8-2a8 8 0 1 1-3.1-6.3L21 4v5h-5l1.8-1.8A6 6 0 1 0 19 12Z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold">{{ $chatbotBrandName }} Assistant</p>
                        <div class="mt-1 flex items-center gap-2 text-xs font-semibold text-slate-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            <span x-text="loading ? 'Thinking' : 'Ready to help'"></span>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="resetChat"
                    class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    aria-label="Reset chatbot"
                    title="Reset chat"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5.5 15A7 7 0 0 0 17 18.2M18.5 9A7 7 0 0 0 7 5.8"/>
                    </svg>
                </button>

                <button
                    type="button"
                    @click="open = false"
                    class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    aria-label="Close chatbot"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-ref="messages" class="flex-1 space-y-4 overflow-y-auto bg-slate-50 px-4 py-4">
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.from === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div class="max-w-[85%]">
                        <div
                            :class="message.from === 'user'
                                ? 'rounded-br-md bg-cyan-700 text-white'
                                : 'rounded-bl-md border border-slate-200 bg-white text-slate-700'"
                            class="whitespace-pre-line rounded-2xl px-3.5 py-2.5 text-sm leading-6 shadow-sm"
                            x-text="message.text"
                        ></div>
                        <div
                            x-show="message.from === 'bot' && message.matched"
                            class="mt-1 px-1 text-[11px] font-semibold text-slate-400"
                            x-text="`Matched: ${message.matched}`"
                        ></div>
                        <div x-show="message.suggestions && message.suggestions.length" class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="suggestion in message.suggestions" :key="suggestion">
                                <button
                                    type="button"
                                    @click="askSuggestion(suggestion)"
                                    class="rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 text-left text-xs font-semibold text-cyan-800 transition hover:bg-cyan-100 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                                    x-text="suggestion"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="messages.length === 1" class="flex flex-wrap gap-1.5">
                <template x-for="suggestion in starterSuggestions" :key="suggestion">
                    <button
                        type="button"
                        @click="askSuggestion(suggestion)"
                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-left text-xs font-semibold text-slate-700 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-200"
                        x-text="suggestion"
                    ></button>
                </template>
            </div>

            <div x-show="loading" class="flex justify-start">
                <div class="flex items-center gap-1.5 rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-slate-400"></span>
                    <span class="h-2 w-2 animate-pulse rounded-full bg-slate-400 [animation-delay:150ms]"></span>
                    <span class="h-2 w-2 animate-pulse rounded-full bg-slate-400 [animation-delay:300ms]"></span>
                    <span class="sr-only">Thinking...</span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 bg-white p-3">
            @if(hasPermission('chatbot.teach'))
            <div x-show="teaching" x-transition class="mb-3 rounded-xl border border-cyan-100 bg-cyan-50 p-3" style="display: none;">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-cyan-900" for="chatbot-teach-answer">Teach this answer</label>
                        <p class="mt-1 text-xs font-semibold text-cyan-800">This reply will be saved for the next matching question.</p>
                    </div>
                    <button
                        type="button"
                        @click="teaching = false; teachAnswer = ''"
                        class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-cyan-800 hover:bg-cyan-100 focus:outline-none focus:ring-2 focus:ring-cyan-300"
                        aria-label="Dismiss teaching form"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <textarea
                    id="chatbot-teach-answer"
                    x-model="teachAnswer"
                    class="mt-3 min-h-24 w-full resize-none rounded-xl border-cyan-200 text-sm shadow-sm focus:border-cyan-600 focus:ring-cyan-600"
                    maxlength="4000"
                    placeholder="Type the answer the bot should remember"
                ></textarea>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold text-cyan-800" x-text="`${teachAnswer.length}/4000`"></span>
                    <button
                        type="button"
                        @click="teach"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="loading || !teachAnswer.trim()"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/>
                        </svg>
                        <span>Save Answer</span>
                    </button>
                </div>
            </div>
            @endif

            <form @submit.prevent="ask" class="flex items-end gap-2">
                <label class="sr-only" for="chatbot-question">Ask a question</label>
                <div class="min-w-0 flex-1">
                    <input
                        id="chatbot-question"
                        x-ref="questionInput"
                        type="text"
                        x-model="question"
                        class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-cyan-600 focus:ring-cyan-600"
                        placeholder="Ask a question"
                        maxlength="500"
                        autocomplete="off"
                    >
                    <div class="mt-1 flex justify-end">
                        <span class="text-[11px] font-semibold text-slate-400" x-text="`${question.length}/500`"></span>
                    </div>
                </div>
                <button
                    type="submit"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-cyan-700 text-white shadow-sm transition hover:bg-cyan-800 focus:outline-none focus:ring-4 focus:ring-cyan-200 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading || !question.trim()"
                    aria-label="Send message"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12 14-7-4 14-3-6-7-1Z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <button
        type="button"
        @click="open = !open"
        class="group relative grid h-14 w-14 place-items-center rounded-full bg-cyan-700 text-white shadow-lg shadow-cyan-950/25 transition hover:bg-cyan-800 focus:outline-none focus:ring-4 focus:ring-cyan-200"
        :aria-label="open ? 'Close chatbot' : 'Open chatbot'"
    >
        <span x-show="!open" class="absolute -top-1 -right-1 h-4 w-4 rounded-full border-2 border-white bg-emerald-400"></span>
        @if($chatbotLogoUrl)
            <img x-show="!open" src="{{ $chatbotLogoUrl }}" alt="{{ $chatbotBrandName }} logo" class="h-10 w-10 rounded-full bg-white object-contain p-0.5" aria-hidden="true">
        @else
            <svg x-show="!open" class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5m8-2a8 8 0 1 1-3.1-6.3L21 4v5h-5l1.8-1.8A6 6 0 1 0 19 12Z"/>
            </svg>
        @endif
        <svg x-show="open" class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
