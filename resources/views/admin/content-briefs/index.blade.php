@extends('layouts.admin')

@section('title', 'İçerik Takvimi')

@section('content')
    <x-admin-page-header title="İçerik Takvimi" :action-url="route('admin.content-briefs.create')" action-label="Yeni Brief" />

    <p class="mb-6 max-w-3xl text-sm text-stone-600">
        Bu modül yalnızca editoryal planlama içindir. Briefler otomatik yayınlanmaz; her yazı insan editör tarafından özgün olarak yazılıp doğrulanmalıdır.
        Editoryal standartlar için depodaki <code class="text-xs">docs/editorial-guide.md</code> dosyasına bakın.
    </p>

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <select name="durum" class="border border-stone-300 px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Tüm durumlar</option>
            @foreach (\App\Enums\BriefStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected($statusFilter === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="kategori" class="border border-stone-300 px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">Tüm kategoriler</option>
            @foreach (\App\Enums\BriefTopicCategory::cases() as $category)
                <option value="{{ $category->value }}" @selected($categoryFilter === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </select>
    </form>

    <div class="admin-table-scroll overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Başlık önerisi</th>
                    <th class="px-5 py-3 font-medium">Kategori</th>
                    <th class="px-5 py-3 font-medium">Durum</th>
                    <th class="px-5 py-3 font-medium">Planlanan tarih</th>
                    <th class="px-5 py-3 font-medium">Editör</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($briefs as $brief)
                    <tr>
                        <td class="px-5 py-3 max-w-xs">
                            <p class="font-medium text-stone-900">{{ $brief->title_suggestion }}</p>
                            <p class="mt-1 line-clamp-2 text-xs text-stone-500">{{ $brief->content_summary }}</p>
                        </td>
                        <td class="px-5 py-3 text-stone-600">{{ $brief->topic_category->label() }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $brief->status->colorClass() }}">
                                {{ $brief->status->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-stone-600">
                            {{ $brief->planned_publish_date?->format('d.m.Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-stone-600">{{ $brief->assignedEditor?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.content-briefs.edit', $brief) }}" class="text-accent-700 hover:text-accent-800">Düzenle</a>
                            @if (auth()->user()?->isSuperAdmin())
                                <form method="POST" action="{{ route('admin.content-briefs.destroy', $brief) }}" class="inline" onsubmit="return confirm('Bu brief silinsin mi?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-700 hover:text-red-800">Sil</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-stone-500">Henüz içerik briefi yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $briefs->links() }}</div>
@endsection
