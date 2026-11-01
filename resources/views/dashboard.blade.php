<x-app-layout>
    <x-slot name="header">
        <p class="text-sm text-gray-500 mt-1">{{ config('tax.occupations')[auth()->user()->occupation] ?? '' }}</p>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Compliance Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-2">Your tax regime</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Choose how you want to be taxed. TOT is charged on gross income monthly; self-assessment is charged on net profit annually. You can switch at any time.
                </p>
                <form method="POST" action="{{ route('regime.update') }}" class="flex items-center gap-4">
                <!-- when save is clicked, send this slip to route regime.update  -->
                @csrf  
                    <select name="tax_regime" class="border-gray-300 rounded">
                        <!-- The dropdown -->
                        <option value="tot" @selected(auth()->user()->tax_regime !== 'self_assessment')>Turnover Tax (TOT)</option>
                        <option value="self_assessment" @selected(auth()->user()->tax_regime === 'self_assessment')>Self-Assessment</option>
                     </select>
                     <button class="px-4 py-2 bg-gray-800 text-white rounded">Save</button>
                 </form>
            </div>
            @if (auth()->user()->tax_regime !== 'self_assessment')
                  <div class="bg-white p-6 shadow rounded overflow-x-auto">
                  <h3 class="font-semibold mb-4">Turnover Tax status (last 3 months)</h3>
                  <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Period</th>
                            <th>Income (KES)</th>
                            <th>Tax due (KES)</th>
                            <th>Paid (KES)</th>
                            <th>Balance (KES)</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Est. penalty (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr class="border-b">
                                <td class="py-2">{{ $row['period'] }}</td>
                                <td>{{ number_format($row['income'], 2) }}</td>
                                <td>{{ number_format($row['tax'], 2) }}</td>
                                <td>{{ number_format($row['paid'], 2) }}</td>
                                <td>{{ number_format($row['balance'], 2) }}</td>
                                <td>{{ $row['due']->format('d M Y') }}</td>
                                <td>
                                    @if ($row['status'] === 'Paid')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Paid</span>
                                    @elseif ($row['status'] === 'Overdue')
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Overdue</span>
                                    @elseif ($row['status'] === 'Due')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Due</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">No tax</span>
                                    @endif
                                </td>
                                <td>{{ $row['penalty'] > 0 ? number_format($row['penalty'], 2) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-xs text-gray-500 mt-3">Penalties are estimates. Confirm amounts with KRA.</p>
            </div>
        @else
            <div class="bg-white p-6 shadow rounded">
                <p class="text-gray-600">You're on self-assessment. Visit the <a href="{{ route('sa.index') }}" class="text-blue-700 underline">Self-Assessment</a> page to calculate and compare your tax.</p>
            </div>
        @endif 
        <!-- from 30 to 78 its if/ else and this aids show the content depending on what tax type they choose. 
         If user doesnt choose any it says TOT, @if asks if the users card is self assess if not then TOT. From 30-72 its shows what happens in TOT the from 72-77its shows if not TOT then self-assess -->

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Record a payment</h3>
                <form method="POST" action="{{ route('payments.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm">Tax month</label>
                        <input type="month" name="period" value="{{ old('period', now()->subMonth()->format('Y-m')) }}" class="w-full border-gray-300 rounded" required>
                        @error('period') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm">Amount (KES)</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="w-full border-gray-300 rounded" required>
                        @error('amount') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm">Date paid</label>
                        <input type="date" name="paid_on" value="{{ old('paid_on', date('Y-m-d')) }}" class="w-full border-gray-300 rounded" required>
                        @error('paid_on') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="M-Pesa / PRN" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
            <!-- From 81-108 it covers the record payment part where a user pays but writes it down in the app -->
            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Pay via M-Pesa (sandbox)</h3>
                <form method="POST" action="{{ route('mpesa.pay') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm">Tax month</label>
                        <input type="month" name="period" value="{{ now()->subMonth()->format('Y-m') }}" class="w-full border-gray-300 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm">Amount (KES)</label>
                        <input type="number" step="0.01" name="amount" class="w-full border-gray-300 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm">Phone (sandbox test number)</label>
                        <input type="text" name="phone" value="254708374149" class="w-full border-gray-300 rounded" required>
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 bg-green-700 text-white rounded">Pay</button>
                    </div>
                </form>
                <p class="text-xs text-gray-500 mt-2">Sandbox mode: no real money moves. Use the pre-filled test number.</p>
            </div>
            <!-- from 110-131 is for pay via mpesa, it has the tax month, amount and phone. Phone is pre-filled with a number which
             is safs sandbox test number. Pay button sends everything to the route mpesa.pay -->

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Payment history</h3>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Date paid</th>
                            <th>Tax month</th>
                            <th>Amount (KES)</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr class="border-b">
                                <td class="py-2">{{ $payment->paid_on }}</td>
                                <td>{{ $payment->period }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->reference }}</td>
                                <td>
                                    @if ($payment->status === 'completed')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Completed</span>
                                    @elseif ($payment->status === 'pending')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Pending</span>
                                    @elseif ($payment->status === 'failed')
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Failed</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">Manual</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-gray-500">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>