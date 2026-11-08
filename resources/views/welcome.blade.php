<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>TrioRCM · Provider Credentialing</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
            </style>
        @endif

        <style>
            .brand-blue { color: #2563eb; }
            .btn-blue { background-color: #2563eb; color: #fff; }
            .btn-blue:hover { background-color: #1d4ed8; }
        </style>
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] antialiased">
        <nav class="sticky top-0 z-50 bg-[#FDFDFC]/80 backdrop-blur border-b border-[#e3e3e0]">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="/" class="flex items-center gap-2 brand-blue font-semibold text-lg tracking-tight">
                        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-6 shrink-0">
                            <rect width="32" height="32" rx="9" fill="#2563eb" />
                            <path d="M9 21.5 L16 9.5 L23 21.5" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="9" cy="21.5" r="2.7" fill="white" />
                            <circle cx="16" cy="9.5" r="2.7" fill="white" />
                            <circle cx="23" cy="21.5" r="2.7" fill="white" />
                        </svg>
                        <span>Trio<span class="text-gray-900">RCM</span></span>
                    </a>

                    <div class="hidden lg:flex items-center gap-6 text-sm font-medium text-[#706f6c]">
                        <a href="#features" class="hover:text-[#2563eb] transition">Features</a>
                        <a href="#how-it-works" class="hover:text-[#2563eb] transition">How it works</a>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ url('/admin') }}" class="btn-blue px-5 py-2 rounded-sm text-sm font-medium shadow-sm hover:shadow-md transition">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('filament.admin.auth.login') }}" class="btn-blue px-5 py-2 rounded-sm text-sm font-medium shadow-sm hover:shadow-md transition">
                                Log in
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <header class="max-w-7xl mx-auto px-6 lg:px-8 py-20 lg:py-32 text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold mb-6">
                Provider Credentialing Platform
            </span>
            <h1 class="text-4xl lg:text-6xl font-bold text-[#1b1b18] tracking-tight mb-6">
                Streamline provider credentialing<br>
                <span class="brand-blue">from intake to approval.</span>
            </h1>
            <p class="text-lg text-[#706f6c] max-w-2xl mx-auto mb-10 lg:mb-12 leading-relaxed">
                Manage enrollments, track stages, and monitor payer requirements in one place built for credentialing teams.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('filament.admin.auth.login') }}" class="btn-blue px-8 py-3 rounded-sm text-sm font-medium shadow-md hover:shadow-lg transition w-full sm:w-auto">
                    Log in to dashboard
                </a>
                <a href="#features" class="px-8 py-3 rounded-sm text-sm font-medium border border-[#e3e3e0] text-[#1b1b18] bg-white hover:bg-[#f5f5f4] transition w-full sm:w-auto">
                    Explore features
                </a>
            </div>
        </header>

        <section id="features" class="bg-white border-y border-[#e3e3e0] py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-[#1b1b18] mb-4">Built for credentialing teams</h2>
                    <p class="text-[#706f6c] max-w-2xl mx-auto">Everything you need to track provider enrollments end to end.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="p-8 rounded-xl border border-[#e3e3e0] bg-[#FDFDFC] hover:border-blue-200 transition">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-6 text-blue-600 font-bold text-xl">📋</div>
                        <h3 class="text-lg font-semibold mb-3">Enrollment tracking</h3>
                        <p class="text-[#706f6c] text-sm leading-relaxed">Monitor every enrollment from intake through verification, submission, approval, or denial.</p>
                    </div>
                    <div class="p-8 rounded-xl border border-[#e3e3e0] bg-[#FDFDFC] hover:border-blue-200 transition">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-6 text-blue-600 font-bold text-xl">🔗</div>
                        <h3 class="text-lg font-semibold mb-3">Payer &amp; provider links</h3>
                        <p class="text-[#706f6c] text-sm leading-relaxed">Connect providers to payers with reference numbers, effective dates, and portal URLs.</p>
                    </div>
                    <div class="p-8 rounded-xl border border-[#e3e3e0] bg-[#FDFDFC] hover:border-blue-200 transition">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-6 text-blue-600 font-bold text-xl">👤</div>
                        <h3 class="text-lg font-semibold mb-3">Assignment &amp; audit</h3>
                        <p class="text-[#706f6c] text-sm leading-relaxed">Assign specialists and keep a full activity log of every stage change.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-bold text-[#1b1b18] mb-6">From intake to approval, in minutes</h2>
                        <ul class="space-y-6 text-[#706f6c] leading-relaxed">
                            <li class="flex items-start gap-3">
                                <span class="flex shrink-0 items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-600 font-bold text-sm">1</span>
                                <div>
                                    <h4 class="font-semibold text-[#1b1b18] mb-1">Create an enrollment</h4>
                                    <p class="text-sm">Link a provider to a payer and set the initial stage.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex shrink-0 items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-600 font-bold text-sm">2</span>
                                <div>
                                    <h4 class="font-semibold text-[#1b1b18] mb-1">Track through stages</h4>
                                    <p class="text-sm">Move enrollments across intake, verification, submitted, approved, or denied.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex shrink-0 items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-600 font-bold text-sm">3</span>
                                <div>
                                    <h4 class="font-semibold text-[#1b1b18] mb-1">Stay compliant</h4>
                                    <p class="text-sm">Record reference numbers, effective dates, and keep an audit trail.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="hidden lg:block">
                        <div class="aspect-[4/5] rounded-2xl border border-[#e3e3e0] bg-[#FDFDFC] shadow-lg overflow-hidden relative">
                            <div class="bg-[#FDFDFC] px-6 py-4 border-b border-[#e3e3e0] flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 font-bold">T</div>
                                    <span class="text-sm font-semibold text-[#1b1b18]">TrioRCM</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-[#706f6c]">Admin · Credentialing</span>
                                    <button class="w-9 h-9 rounded-full border border-[#e3e3e0] flex items-center justify-center hover:bg-[#f5f5f4] transition">
                                        <svg class="w-4 h-4 text-[#1b1b18]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="p-6 space-y-6">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="p-4 rounded-xl bg-white border border-[#e3e3e0]">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs text-[#706f6c] font-medium uppercase tracking-wider">Total enrollments</p>
                                                <p class="text-2xl font-bold text-[#1b1b18] mt-1">1,248</p>
                                            </div>
                                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            </div>
                                        </div>
                                        <p class="text-xs text-[#706f6c] mt-2">+12% vs last month</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-white border border-[#e3e3e0]">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs text-[#706f6c] font-medium uppercase tracking-wider">Pending review</p>
                                                <p class="text-2xl font-bold text-[#1b1b18] mt-1">84</p>
                                            </div>
                                            <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                        </div>
                                        <p class="text-xs text-[#706f6c] mt-2">-3 vs yesterday</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-white border border-[#e3e3e0]">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs text-[#706f6c] font-medium uppercase tracking-wider">Approved</p>
                                                <p class="text-2xl font-bold text-[#1b1b18] mt-1">612</p>
                                            </div>
                                            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                        <p class="text-xs text-[#706f6c] mt-2">+24 this week</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-white border border-[#e3e3e0]">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs text-[#706f6c] font-medium uppercase tracking-wider">Denied</p>
                                                <p class="text-2xl font-bold text-[#1b1b18] mt-1">28</p>
                                            </div>
                                            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </div>
                                        </div>
                                        <p class="text-xs text-[#706f6c] mt-2">Steady</p>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#e3e3e0] bg-white overflow-hidden">
                                    <div class="p-4 border-b border-[#e3e3e0] flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-[#1b1b18]">Recent enrollments</h3>
                                        <a href="#" class="text-xs text-blue-600 font-medium hover:underline">View all</a>
                                    </div>
                                    <div class="divide-y divide-[#e3e3e0]">
                                        <div class="p-4 flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-[#f5f5f4] flex items-center justify-center text-[#706f6c] text-sm">JP</div>
                                                <div>
                                                    <p class="text-sm font-medium text-[#1b1b18"]>John Peterson</p>
                                                    <p class="text-xs text-[#706f6c]">Aetna · Verification</p>
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">Pending</span>
                                        </div>
                                        <div class="p-4 flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-[#f5f5f4] flex items-center justify-center text-[#706f6c] text-sm">SM</div>
                                                <div>
                                                    <p class="text-sm font-medium text-[#1b1b18"]>Sarah Mitchell</p>
                                                    <p class="text-xs text-[#706f6c]">Blue Cross · Approved</p>
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">Approved</span>
                                        </div>
                                        <div class="p-4 flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-[#f5f5f4] flex items-center justify-center text-[#706f6c] text-sm">RM</div>
                                                <div>
                                                    <p class="text-sm font-medium text-[#1b1b18"]>Robert Martinez</p>
                                                    <p class="text-xs text-[#706f6c]">Cigna · Intake</p>
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">Intake</span>
                                        </div>
                                        <div class="p-4 flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-[#f5f5f4] flex items-center justify-center text-[#706f6c] text-sm">LC</div>
                                                <div>
                                                    <p class="text-sm font-medium text-[#1b1b18"]>Lisa Chen</p>
                                                    <p class="text-xs text-[#706f6c]">UnitedHealth · Submitted</p>
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">Submitted</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-[#e3e3e0] bg-[#FDFDFC] py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-sm font-medium">
                    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-blue-600">
                        <rect width="32" height="32" rx="9" fill="#2563eb" />
                        <path d="M9 21.5 L16 9.5 L23 21.5" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="9" cy="21.5" r="2.7" fill="white" />
                        <circle cx="16" cy="9.5" r="2.7" fill="white" />
                        <circle cx="23" cy="21.5" r="2.7" fill="white" />
                    </svg>
                    <span>TrioRCM</span>
                </div>
                <p class="text-sm text-[#706f6c]">© {{ now()->year }} TrioRCM. Provider credentialing made simple.</p>
                <div class="flex items-center gap-4 text-sm">
                    @guest
                        <a href="{{ route('filament.admin.auth.login') }}" class="text-[#2563eb] hover:underline font-medium">Log in</a>
                    @else
                        <a href="{{ url('/admin') }}" class="text-[#2563eb] hover:underline font-medium">Go to dashboard</a>
                    @endguest
                </div>
            </div>
        </footer>
    </body>
</html>
