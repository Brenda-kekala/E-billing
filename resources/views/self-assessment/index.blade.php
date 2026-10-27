<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Self-Assessment Tax Return</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-4 shadow rounded text-sm text-gray-600">
                Your current tax regime: <strong>{{ auth()->user()->tax_regime === 'self_assessment' ? 'Self-Assessment' : 'Turnover Tax (TOT)' }}</strong>.
                You can change this on your <a href="{{ route('dashboard') }}" class="text-blue-700 underline">Dashboard</a>.
            </div>

            <div class="bg-white p-6 shadow rounded">
                <form method="GET" action="{{ route('sa.index') }}" class="flex items-end gap-4">
                    <div>
                        <label class="block text-sm">Year of income</label>
                        <input type="number" name="year" value="{{ $year }}" min="2000" max="2100" class="border-gray-300 rounded">
                    </div>
                    <button class="px-4 py-2 bg-gray-800 text-white rounded">Calculate</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Gross income</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($gross, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Business expenses</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($expenseTotal, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Taxable profit</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($profit, 2) }}</p>
                </div>
            </div>

            <div class="bg-white p-6 shadow rounded overflow-x-auto">
                <h3 class="font-semibold mb-4">Tax calculation</h3>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Portion of profit (KES)</th>
                            <th>Rate</th>
                            <th>Tax (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($breakdown as $row)
                            <tr class="border-b">
                                <td class="py-2">{{ number_format($row['slice'], 2) }}</td>
                                <td>{{ $row['rate'] * 100 }}%</td>
                                <td>{{ number_format($row['tax'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-3 text-gray-500">No taxable profit for this year.</td></tr>
                        @endforelse
                        <tr>
                            <td class="py-2" colspan="2">Tax before relief</td>
                            <td>{{ number_format($grossTax, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2" colspan="2">Less: personal relief</td>
                            <td>- {{ number_format($relief, 2) }}</td>
                        </tr>
                        <tr class="font-semibold border-t">
                            <td class="py-2" colspan="2">Tax payable</td>
                            <td>{{ number_format($netTax, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-3">Deadlines</h3>
                <p>Return filing deadline: <strong>{{ $filingDeadline->format('d M Y') }}</strong></p>
                @if ($latePenalty > 0)
                    <p class="text-red-700 mt-1">Deadline passed. Estimated late filing penalty: KES {{ number_format($latePenalty, 2) }}</p>
                @endif

                @if (count($instalments))
                    <p class="mt-4 mb-2">Your tax is above the instalment threshold, so pay in four instalments:</p>
                    <ul class="list-disc ml-6 text-sm">
                        @foreach ($instalments as $inst)
                            <li>{{ $inst['due']->format('d M Y') }}: KES {{ number_format($inst['amount'], 2) }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-gray-600">No instalments needed. Pay any balance when you file.</p>
                @endif
            </div>

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-3">Compare with Turnover Tax</h3>
                <p>Self-assessment tax: <strong>KES {{ number_format($netTax, 2) }}</strong></p>
                <p>Turnover Tax on gross income: <strong>KES {{ number_format($totTax, 2) }}</strong></p>
                @if (! $totEligible)
                    <p class="text-sm text-yellow-700 mt-2">Your gross income is outside the Turnover Tax range, so TOT may not be available to you.</p>
                @elseif ($netTax < $totTax)
                    <p class="text-sm text-green-700 mt-2">Self-assessment looks cheaper on these figures.</p>
                @else
                    <p class="text-sm text-green-700 mt-2">Turnover Tax looks cheaper on these figures.</p>
                @endif
            </div>

           
            <p class="text-xs text-gray-500">
                This is a simplified estimate. It does not include other reliefs such as insurance, pension or mortgage interest,
                and only expenses wholly incurred for the business should be entered. Confirm the final figures on iTax.
            </p>
        </div>
    </div>
</x-app-layout>