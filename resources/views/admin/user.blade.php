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

            <x-back-button :href="route('admin.index')">Back to all taxpayers</x-back-button>
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

            <div class="bg-white shadow sm:rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold mb-3">Filed returns</h3>

                @if ($returns->isEmpty())
                    <p class="text-sm text-gray-500">This taxpayer has not filed any returns yet.</p>
                @else
                    <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                        <th class="py-2 pr-4">Period</th>
                        <th class="py-2 pr-4">Income (KES)</th>
                        <th class="py-2 pr-4">Tax (KES)</th>
                        <th class="py-2 pr-4">Penalty (KES)</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Ack. no.</th>
                        <th class="py-2">Filed on</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($returns as $r)
                         <tr class="border-b">
                             <td class="py-2 pr-4">{{ \Carbon\Carbon::createFromFormat('Y-m', $r->period)->format('F Y') }}{{ $r->is_nil ? ' (nil)' : '' }}</td>
                             <td class="py-2 pr-4">{{ number_format($r->income, 2) }}</td>
                             <td class="py-2 pr-4">{{ number_format($r->tax, 2) }}</td>
                             <td class="py-2 pr-4">{{ number_format($r->penalty, 2) }}</td>
                             <td class="py-2 pr-4">{{ ucfirst($r->status) }}</td>
                             <td class="py-2 pr-4 font-mono text-xs">{{ $r->acknowledgement_no }}</td>
                             <td class="py-2">{{ $r->filed_at?->format('d M Y H:i') }}</td>
                         </tr>
                        @endforeach
                    </tbody>
                    </table>
                 @endif
            </div>
        </div>
    </div>
</x-app-layout>