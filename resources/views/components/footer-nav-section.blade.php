@props([
    'title',
    'sectionId',
])

<div class="border-b border-stone-100 pb-1 md:border-0 md:pb-0">
    <button
        type="button"
        class="flex min-h-11 w-full items-center justify-between py-4 text-left md:hidden"
        @click="toggleSection('{{ $sectionId }}')"
        :aria-expanded="isSectionOpen('{{ $sectionId }}')"
        aria-controls="footer-section-{{ $sectionId }}"
    >
        <span class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">{{ $title }}</span>
        <svg
            class="size-5 shrink-0 text-stone-400 transition-transform duration-200"
            :class="{ 'rotate-180': isSectionOpen('{{ $sectionId }}') }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <h2 class="hidden text-xs font-medium uppercase tracking-[0.2em] text-stone-500 md:block">{{ $title }}</h2>

    <div
        id="footer-section-{{ $sectionId }}"
        :class="sectionPanelClass('{{ $sectionId }}')"
    >
        {{ $slot }}
    </div>
</div>
