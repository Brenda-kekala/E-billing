<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Taxpayers by occupation</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-back-button :href="route('admin.index')">Back to admin overview</x-back-button>

            <div class="bg-white p-6 shadow rounded overflow-x-auto">
                <h3 class="font-semibold mb-4">Income by occupation category</h3>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Occupation</th>
                            <th>Taxpayers</th>
                            <th>Total income (KES)</th>
                            <th>Average per taxpayer</th>
                            <th>Share of total</th>
                            <th>Est. tax (KES)</th>
                            <th>Paid (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groups as $g)
                            @php $share = $totalIncome > 0 ? round($g['income'] / $totalIncome * 100, 1) : 0; @endphp
                            <tr class="border-b">
                                <td class="py-2 font-medium">{{ $g['label'] }}</td>
                                <td>{{ $g['count'] }}</td>
                                <td>{{ number_format($g['income'], 2) }}</td>
                                <td>{{ number_format($g['average'], 2) }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 h-2 bg-gray-200 rounded">
                                            <div class="h-2 bg-gray-800 rounded" style="width: {{ $share }}%"></div>
                                        </div>
                                        <span>{{ $share }}%</span>
                                    </div>
                                </td>
                                <td>{{ number_format($g['estimated_tax'], 2) }}</td>
                                <td>{{ number_format($g['paid'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-gray-500">No taxpayers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                
            </div>

            @foreach ($groups as $g)
                <details class="bg-white shadow rounded p-4">
                    <summary class="cursor-pointer font-semibold">
                        {{ $g['label'] }}
                        <span class="text-sm font-normal text-gray-500">
                            ({{ $g['count'] }} taxpayer{{ $g['count'] === 1 ? '' : 's' }}, KES {{ number_format($g['income'], 2) }})
                        </span>
                    </summary>

                    <table class="w-full text-left text-sm mt-4">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Name</th>
                                <th>Email</th>
                                <th>Total income (KES)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($g['members'] as $m)
                                <tr class="border-b">
                                    <td class="py-2">{{ $m['user']->name }}</td>
                                    <td>{{ $m['user']->email }}</td>
                                    <td>{{ number_format($m['income'], 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.user', $m['user']) }}" class="text-indigo-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            @endforeach
        </div>
    </div>
</x-app-layout>