@if (session('contact_success'))
    <div class="mt-8 rounded-md border border-stone-200 bg-stone-50 px-5 py-4 text-stone-800" role="status">
        <p class="font-medium text-stone-900">Mesajınız alındı.</p>
        <p class="mt-1 text-base text-stone-600">En kısa sürede size dönüş yapacağız. Teşekkür ederiz.</p>
    </div>
@else
    <form action="{{ route('contact.store') }}" method="POST" class="mt-8 space-y-5" novalidate>
        @csrf

        <div class="hidden" aria-hidden="true">
            <label for="company_website">Web sitesi</label>
            <input type="text" name="company_website" id="company_website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <label for="contact_name" class="block text-sm font-medium text-stone-900">Adınız</label>
            <input
                type="text"
                name="name"
                id="contact_name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                class="mt-1 block w-full rounded-md border border-stone-300 px-3 py-2 text-base text-stone-900 shadow-sm focus:border-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-600/30"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact_email" class="block text-sm font-medium text-stone-900">E-posta</label>
            <input
                type="email"
                name="email"
                id="contact_email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="mt-1 block w-full rounded-md border border-stone-300 px-3 py-2 text-base text-stone-900 shadow-sm focus:border-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-600/30"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="contact_message" class="block text-sm font-medium text-stone-900">Mesaj</label>
            <textarea
                name="message"
                id="contact_message"
                rows="6"
                required
                class="mt-1 block w-full rounded-md border border-stone-300 px-3 py-2 text-base text-stone-900 shadow-sm focus:border-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-600/30"
            >{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="flex items-start gap-3 text-base text-stone-700">
                <input
                    type="checkbox"
                    name="privacy_acknowledged"
                    value="1"
                    @checked(old('privacy_acknowledged'))
                    required
                    class="mt-1 size-4 rounded border-stone-300 text-accent-700 focus:ring-accent-600/30"
                >
                <span>
                    <a href="{{ route('pages.privacy') }}" class="text-accent-700 underline underline-offset-2 hover:text-accent-800">Gizlilik politikasını</a>
                    okudum ve kişisel verilerimin iletişim amacıyla işlenmesini kabul ediyorum.
                </span>
            </label>
            @error('privacy_acknowledged')
                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-accent-800 px-5 py-2.5 text-sm font-medium uppercase tracking-widest text-white hover:bg-accent-900 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2"
        >
            Gönder
        </button>
    </form>
@endif
