<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $user->email }} — {{ config('tax.occupations')[$user->occupation] ?? 'No occupation set' }}</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <a href="{{ route('admin.index') }}" class="text-sm text-gray-600 underline">&larr; Back to all taxpayers</a>
            <a href="{{ route('admin.user.edit', $user) }}" class="text-sm text-blue-700 underline ml-4">Edit this taxpayer</a>

            <form method="POST" action="{{ route('admin.user.toggle-active', $user) }}" class="inline ml-4">
                @csrf
                <button class="text-sm underline {{ $user->is_active ? 'text-red-700' : 'text-green-700' }}">
                   {{ $user->is_active ? 'Deactivate account' : 'Reactivate account' }}
                </button>
            </form>

            @if (! $user->is_active)
                <span class="ml-4 text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">Deactivated</span>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Total income</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($totalIncome, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Estimated TOT (1.5%)</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($estimatedTax, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Total paid</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($totalPaid, 2) }}</p>
                </div>
            </div>

            <div class="bg-white p-6 shadow rounded overflow-x-auto">
                <h3 class="font-semibold mb-4">Income entries</h3>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Date</th>
                            <th>Amount (KES)</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incomes as $income)
                            <tr class="border-b {{ $income->flagged ? 'bg-red-50' : '' }}">
                                <td class="py-2">{{ $income->income_date }}</td>
                                <td>{{ number_format($income->amount, 2) }}</td>
                                <td>{{ $income->description }}</td>
                                <td>
                                    @if ($income->flagged)
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Flagged</span>
                                        @if ($income->flag_note)
                                            <div class="text-xs text-gray-500 mt-1">{{ $income->flag_note }}</div>
                                        @endif
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.incomes.flag', $income) }}">
                                        @csrf
                                        @if (! $income->flagged)
                                            <input type="text" name="flag_note" placeholder="Reason (optional)" class="border-gray-300 rounded text-xs mr-2">
                                        @endif
                                        <button class="text-sm {{ $income->flagged ? 'text-green-700' : 'text-red-700' }} underline">
                                            {{ $income->flagged ? 'Unflag' : 'Flag' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-gray-500">No income recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-6 shadow rounded overflow-x-auto">
                <h3 class="font-semibold mb-4">Payment history</h3>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Date paid</th>
                            <th>Tax month</th>
                            <th>Amount (KES)</th>
                            <th>Reference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr class="border-b">
                                <td class="py-2">{{ $payment->paid_on }}</td>
                                <td>{{ $payment->period }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->reference }}</td>
                                <td>{{ $payment->status ?? 'manual' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-gray-500">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>