<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Action Log</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <a href="{{ route('admin.index') }}" class="text-sm text-gray-600 underline">&larr; Back to dashboard</a>

            <div class="bg-white p-6 shadow rounded overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Date</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Target taxpayer</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-b">
                                <td class="py-2">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $log->admin->name ?? '—' }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $log->action)) }}</td>
                                <td>{{ $log->targetUser->name ?? '—' }}</td>
                                <td>{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-gray-500">No admin actions logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>