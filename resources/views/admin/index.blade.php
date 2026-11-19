<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin — All Taxpayers</h2>
    </x-slot>
    <a href="{{ route('admin.occupations') }}"
        class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
        View by occupation
    </a>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Registered taxpayers</p>
                    <p class="text-2xl font-semibold">{{ $totalUsers }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Total income recorded (all users)</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($totalIncome, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Flagged income entries</p>
                    <p class="text-2xl font-semibold text-red-700">{{ $totalFlagged }}</p>
                </div>
            </div>

                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">Taxpayers</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.export') }}" class="text-sm bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700">
                           Export CSV
                        </a>
                        <a href="{{ route('admin.export.pdf') }}" class="text-sm bg-red-600 text-white px-3 py-1.5 rounded hover:bg-red-700">
                           Export PDF
                        </a>
                        
                        <a href="{{ route('admin.logs') }}" class="text-sm text-gray-700 underline">View admin action log</a>
                    </div>
               </div>

                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Name</th>
                            <th>Occupation</th>
                            <th>Total income (KES)</th>
                            <th>Est. tax (KES)</th>
                            <th>Paid (KES)</th>
                            <th>Flagged entries</th>
                            <th class="py-2 pr-4 text-left">Returns filed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $row)
                            <tr class="border-b">
                                <td class="py-2">{{ $row['user']->name }}</td>
                                <td>{{ config('tax.occupations')[$row['user']->occupation] ?? '—' }}</td>
                                <td>{{ number_format($row['income'], 2) }}</td>
                                <td>{{ number_format($row['estimated_tax'], 2) }}</td>
                                <td>{{ number_format($row['paid'], 2) }}</td>
                                <td>
                                    @if ($row['flagged'] > 0)
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">{{ $row['flagged'] }}</span>
                                    @else
                                        <span class="text-gray-400">0</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    {{ $row['returns_filed'] }}
                                    @if ($row['last_return'])
                                        <span class="text-xs px-2 py-1 rounded
                                            {{ $row['last_return']->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $row['last_return']->period)->format('M Y') }}:
                                            {{ ucfirst($row['last_return']->status) }}
                                        </span>
                                    @else
                                        <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">None filed</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.user', $row['user']) }}" class="text-blue-700 underline text-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-gray-500">No taxpayers registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>