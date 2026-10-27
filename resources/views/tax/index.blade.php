<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Turnover Tax Calculator</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 shadow rounded">
                <form method="GET" action="{{ route('tax.index') }}" class="flex items-end gap-4">
                    <div>
                        <label class="block text-sm">Select month</label>
                        <input type="month" name="month" value="{{ $month }}" class="border-gray-300 rounded">
                    </div>
                    <button class="px-4 py-2 bg-gray-800 text-white rounded">Calculate</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Gross income this month</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($monthlyTotal, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Turnover Tax ({{ $rate * 100 }}%)</p>
                    <p class="text-2xl font-semibold">KES {{ number_format($taxDue, 2) }}</p>
                </div>
                <div class="bg-white p-6 shadow rounded">
                    <p class="text-sm text-gray-500">Payment due by</p>
                    <p class="text-2xl font-semibold">{{ $dueDate->format('d M Y') }}</p>
                </div>
            </div>

            <div class="bg-white p-6 shadow rounded">
                <p class="text-sm text-gray-500">Total income for {{ substr($month, 0, 4) }}</p>
                <p class="text-xl font-semibold mb-2">KES {{ number_format($annualTotal, 2) }}</p>

                @if ($eligibility === 'below')
                    <p class="text-yellow-700">Your yearly total is below KES {{ number_format(config('tax.tot_min')) }}, so you may be exempt from Turnover Tax.</p>
                @elseif ($eligibility === 'above')
                    <p class="text-red-700">Your yearly total is above KES {{ number_format(config('tax.tot_max')) }}, so you may need to use regular income tax instead.</p>
                @else
                    <p class="text-green-700">Your yearly total is within the Turnover Tax range.</p>
                @endif
            </div>

            <p class="text-xs text-gray-500">
                This is an estimate based on the income you recorded. Rates and thresholds can change with each Finance Act, so confirm them with KRA.
            </p>
        </div>
    </div>
</x-app-layout>