@php
    $year    = date('Y');
    $appName = config('app.name', 'Billing System');
    $links   = [
        ['label' => 'Documentation', 'href' => '#'],
        ['label' => 'Privacy',       'href' => '#'],
        ['label' => 'Terms',         'href' => '#'],
        ['label' => 'Support',       'href' => '#'],
    ];
@endphp

<footer class="flex-shrink-0 border-t border-gray-100 dark:border-zinc-800
               bg-white dark:bg-zinc-900 px-5 py-3">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2">

        {{-- Brand --}}
        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <div class="w-5 h-5 bg-gradient-to-br from-cyan-500 to-purple-600 rounded-md
                        flex items-center justify-center text-white font-medium text-[9px] flex-shrink-0">B</div>
            &copy; {{ $year }}
            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $appName }}</span>.
            All rights reserved.
        </div>

        {{-- Links + version --}}
        <div class="flex items-center gap-4">
            <nav class="flex items-center gap-4" aria-label="Footer">
                @foreach($links as $link)
                    <a href="{{ $link['href'] }}"
                       class="text-xs text-gray-500 dark:text-gray-400 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                         bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-gray-400
                         border border-gray-200 dark:border-zinc-700">
                <svg class="w-2 h-2 text-emerald-500 flex-shrink-0" viewBox="0 0 8 8" fill="currentColor" aria-hidden="true">
                    <circle cx="4" cy="4" r="3"/>
                </svg>
                v 1.0.0
            </span>
        </div>

    </div>
</footer>