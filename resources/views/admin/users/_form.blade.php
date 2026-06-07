<div>
    <label class="block text-sm font-medium text-stone-700">Ad</label>
    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">E-posta</label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Rol</label>
    <select name="role" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
        @foreach ($roles as $role)
            <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) == $role->value)>{{ $role->label() }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Parola{{ isset($user) ? ' (değiştirmek için doldurun)' : '' }}</label>
    <input type="password" name="password" {{ isset($user) ? '' : 'required' }} class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Parola Tekrar</label>
    <input type="password" name="password_confirmation" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
    Aktif
</label>
