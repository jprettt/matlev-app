<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Matlev K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
<div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-400">Manajemen Pengguna</p>
            <h1 class="text-3xl font-bold mt-2">Kelola Hak Akses</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="bg-cyan-600 hover:bg-cyan-500 px-4 py-2 rounded-lg font-semibold">Kembali</a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-700 rounded-2xl overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-800 text-slate-300 uppercase text-[11px]">
            <tr>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Unit Kerja</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-left">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr class="border-t border-slate-700">
                    <td class="px-4 py-3">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->unit_kerja ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase
                            @if($user->role === 'admin') bg-cyan-900 text-cyan-300
                            @elseif($user->role === 'atasan') bg-violet-900 text-violet-300
                            @else bg-emerald-900 text-emerald-300 @endif">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-4 py-3 space-y-3">
                        <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="flex gap-2 items-center">
                            @csrf
                            <select name="role" class="bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="atasan" {{ $user->role === 'atasan' ? 'selected' : '' }}>Atasan</option>
                            </select>
                            <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 px-3 py-1.5 rounded text-sm font-semibold">Update</button>
                        </form>
                        <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" class="flex gap-2 items-center">
                            @csrf
                            <input type="password" name="password" placeholder="Password baru" class="bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm" required>
                            <input type="password" name="password_confirmation" placeholder="Konfirmasi" class="bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm" required>
                            <button type="submit" class="bg-amber-600 hover:bg-amber-500 px-3 py-1.5 rounded text-sm font-semibold">Reset</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
