<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Returns</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 text-red-700 rounded">{{ $errors->first() }}</div>
            @endif

            @if (! $isTot)
                <div class="p-4 bg-yellow-50 text-yellow-800 rounded">
                    Monthly returns apply to Turnover Tax users. Your annual self-assessment return is handled separately.
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-1">Turnover Tax monthly returns</h3>
                <p class="text-sm text-gray-500 mb-4">
                    A return is filed after the month ends and is due by the 20th of the next month.
                    If you earned nothing in a month, you still file a nil return.
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2 pr-4">Period</th>
                                <th class="py-2 pr-4">Income (KES)</th>
                                <th class="py-2 pr-4">Tax (KES)</th>
                                <th class="py-2 pr-4">Due</th>
                                <th class="py-2 pr-4">Filing status</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $row)
                            <tr class="border-b">
                                <td class="py-2 pr-4">{{ $row['label'] }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['income'], 2) }}</td>
                                <td class="py-2 pr-4">{{ number_format($row['tax'], 2) }}</td>
                                <td class="py-2 pr-4">{{ $row['due']->format('d M Y') }}</td>
                                <td class="py-2 pr-4">
                                    @if ($row['return'])
                                        @php $s = $row['return']->status; @endphp
                                        <span class="px-2 py-1 rounded text-xs
                                            {{ $s === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($s) }}{{ $row['return']->is_nil ? ' (nil)' : '' }}
                                        </span>
                                    @elseif (! $row['ended'])
                                        <span class="text-gray-400">Month not ended</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700">Not filed</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    @if ($row['return'])
                                        <a href="{{ route('returns.show', $row['return']) }}" class="text-indigo-600 hover:underline">Track</a>
                                    @elseif ($row['ended'] && $isTot)
                                        <form method="POST" action="{{ route('returns.file') }}"
                                              onsubmit="this.querySelector('button').disabled = true;">
                                            @csrf
                                            <input type="hidden" name="period" value="{{ $row['period'] }}">
                                            <button type="submit"
                                                class="px-3 py-1 bg-gray-800 text-white rounded text-xs">
                                                {{ $row['income'] > 0 ? 'File return' : 'File nil return' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500 mt-4">
                    Filing here is simulated through a mock iTax service. Confirm real filings on KRA iTax.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>