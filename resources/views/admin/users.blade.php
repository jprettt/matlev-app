@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm">
        <h1 class="text-2xl font-extrabold font-display text-stone-900">Manajemen Pengguna</h1>
        <p class="text-sm text-stone-500 mt-1">Kelola peran akun dan reset password pengguna sistem.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <section class="bg-white border border-stone-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-100 text-stone-600 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-stone-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase
                                    @if($user->role === 'admin') bg-blue-100 text-blue-800
                                    @elseif($user->role === 'atasan') bg-amber-100 text-amber-800
                                    @else bg-emerald-100 text-emerald-800 @endif">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 space-y-2 min-w-[420px]">
                                <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="flex gap-2 items-center">
                                    @csrf
                                    <select name="role" class="bg-white border border-stone-300 rounded-lg px-2 py-1.5 text-sm">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="atasan" {{ $user->role === 'atasan' ? 'selected' : '' }}>Atasan</option>
                                    </select>
                                    <button type="submit" class="bg-pln-700 hover:bg-pln-800 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">Update Role</button>
                                </form>
                                <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" class="flex gap-2 items-center">
                                    @csrf
                                    <input type="password" name="password" placeholder="Password baru" class="bg-white border border-stone-300 rounded-lg px-2 py-1.5 text-sm" required>
                                    <input type="password" name="password_confirmation" placeholder="Konfirmasi" class="bg-white border border-stone-300 rounded-lg px-2 py-1.5 text-sm" required>
                                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">Reset Password</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
