<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-slate-900">System Settings</h2>
            <p class="text-sm text-slate-500">Manage application branding, support details, and read-only runtime configuration.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('system.settings.update') }}" enctype="multipart/form-data" class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            @csrf
            @method('PUT')

            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <h3 class="text-sm font-bold text-slate-900">Application Branding</h3>
                <p class="mt-1 text-xs font-semibold text-slate-500">These values control the public/login fallback brand and footer credit. University logos are saved on each university record.</p>
            </div>

            <div class="grid gap-6 p-4 lg:grid-cols-[1fr_280px]">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="application_name" value="Application Name" />
                        <input id="application_name" name="application_name" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('application_name', $settings['application_name']) }}" placeholder="Application name" required @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('application_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="application_short_name" value="Application Subtitle" />
                        <input id="application_short_name" name="application_short_name" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('application_short_name', $settings['application_short_name']) }}" placeholder="Application subtitle" required @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('application_short_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="created_by" value="Created / Developed By" />
                        <input id="created_by" name="created_by" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('created_by', $settings['created_by']) }}" placeholder="Developer or creator name" required @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('created_by')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="created_by_contact" value="Creator Contact" />
                        <input id="created_by_contact" name="created_by_contact" type="text" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('created_by_contact', $settings['created_by_contact']) }}" placeholder="10 digit phone number" @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('created_by_contact')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="support_email" value="Support Email" />
                        <input id="support_email" name="support_email" type="email" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('support_email', $settings['support_email']) }}" placeholder="support@example.com" @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="support_phone" value="Support Phone" />
                        <input id="support_phone" name="support_phone" type="text" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('support_phone', $settings['support_phone']) }}" placeholder="10 digit support phone number" @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('support_phone')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="footer_text" value="Footer Text" />
                        <input id="footer_text" name="footer_text" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('footer_text', $settings['footer_text']) }}" placeholder="Footer credit text" @disabled(! $canUpdate)>
                        <x-input-error :messages="$errors->get('footer_text')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="logo_url" value="Logo URL / Saved Path" />
                        <input id="logo_url" name="logo_url" type="text" class="mt-1 block w-full rounded-md border-2 border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm focus:border-cyan-600 focus:ring-cyan-600 disabled:bg-slate-100 disabled:text-slate-500" value="{{ old('logo_url', $settings['logo_url']) }}" placeholder="uploads/logos/logo.png or https://example.com/logo.png" @disabled(! $canUpdate)>
                        <p class="mt-1 text-xs text-slate-500">Used only when no university-specific logo applies, such as public/login pages.</p>
                        <x-input-error :messages="$errors->get('logo_url')" class="mt-2" />
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-900">Logo Preview</p>
                    <div class="mt-3 grid h-32 place-items-center rounded-lg border border-dashed border-slate-300 bg-white">
                        @if($logoPreviewUrl)
                            <img src="{{ $logoPreviewUrl }}" alt="{{ $settings['application_name'] }} logo" class="max-h-24 max-w-48 object-contain">
                        @else
                            <span class="grid h-16 w-16 place-items-center rounded-lg bg-cyan-700 text-lg font-bold text-white">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($settings['application_name'], 0, 2)) }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-4">
                        <x-input-label for="logo" value="Upload Logo" />
                        <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*" class="mt-1 block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-bold file:text-white" @disabled(! $canUpdate)>
                        <p class="mt-1 text-xs text-slate-500">Global fallback logo only. Upload university logos from Institution > Universities.</p>
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-4 py-3">
                @if($canUpdate)
                    <x-primary-button>Save Settings</x-primary-button>
                @else
                    <p class="text-sm font-semibold text-slate-500">You can view settings, but you do not have update permission.</p>
                @endif
            </div>
        </form>

        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400">Environment</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ config('app.env') }}</p>
            </div>
            <div class="rounded-lg border {{ config('app.debug') ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }} p-4 shadow-sm">
                <p class="text-xs font-bold uppercase {{ config('app.debug') ? 'text-amber-700' : 'text-emerald-700' }}">Debug Mode</p>
                <p class="mt-2 text-2xl font-black {{ config('app.debug') ? 'text-amber-800' : 'text-emerald-800' }}">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</p>
            </div>
            <div class="rounded-lg border {{ $maintenance['enabled'] ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-white' }} p-4 shadow-sm">
                <p class="text-xs font-bold uppercase {{ $maintenance['enabled'] ? 'text-red-700' : 'text-slate-400' }}">Maintenance</p>
                <p class="mt-2 text-2xl font-black {{ $maintenance['enabled'] ? 'text-red-800' : 'text-slate-900' }}">{{ $maintenance['enabled'] ? 'Enabled' : 'Disabled' }}</p>
                <p class="mt-1 text-xs text-slate-500">Driver: {{ $maintenance['driver'] }}</p>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-cyan-100 bg-cyan-50 p-4 text-sm text-cyan-900">
            <p class="font-bold">Runtime configuration</p>
            <p class="mt-1">The details below are read-only Laravel configuration values from <span class="font-mono">.env</span> and config files. After changing <span class="font-mono">.env</span>, run <span class="font-mono">php artisan config:clear</span>.</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            @foreach ($sections as $section => $settings)
                <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h3 class="text-sm font-bold text-slate-900">{{ $section }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($settings as $setting)
                            <div class="grid gap-2 p-4 sm:grid-cols-3 sm:items-start">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $setting['label'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ $setting['source'] }}</p>
                                </div>
                                <div class="break-words rounded-md bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 sm:col-span-2">
                                    {{ filled($setting['value']) ? $setting['value'] : 'Not configured' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <h3 class="text-sm font-bold text-slate-900">Useful Commands</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($commands as $label => $command)
                    <div class="grid gap-2 p-4 sm:grid-cols-3 sm:items-center">
                        <p class="text-sm font-bold text-slate-900">{{ $label }}</p>
                        <code class="break-all rounded-md bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-100 sm:col-span-2">{{ $command }}</code>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
