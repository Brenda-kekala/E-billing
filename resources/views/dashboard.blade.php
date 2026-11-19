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
                            <th>Filing</th>
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
                                   <td class="py-2 pr-4">
                                    @if ($row['return'])
                                        <a href="{{ route('returns.show', $row['return']) }}"
                                           class="px-2 py-1 rounded text-xs
                                           {{ $row['return']->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $row['filing'] }}
                                        </a>
                                    @else
                                        <a href="{{ route('returns.index') }}" class="px-2 py-1 rounded text-xs bg-red-100 text-red-700">
                                            Not filed
                                        </a>
                                    @endif
                                </td>
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
         If user doesnt choose any it says TOT, if asks if the users card is self assess if not then TOT. From 30-72 its shows what happens in TOT the from 72-77its shows if not TOT then self-assess -->

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Record a payment</h3>
                <form method="POST" action="{{ route('payments.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm">Tax month</label>
                        <input type="month" name="period" max="{{ now()->subMonth()->format('Y-m') }}" value="{{ now()->subMonth()->format('Y-m') }}" class="w-full border-gray-300 rounded" required>
                        <!-- 86 blocks a user from recording a payment for the current month -->
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
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded text-sm text-gray-700">
                        <p class="font-semibold text-green-800 mb-2">Pay by M-Pesa Paybill</p>
                        <ol class="list-decimal ml-5 space-y-1">
                            <li>Open <strong>M-Pesa</strong> → <strong>Lipa na M-Pesa</strong> → <strong>Pay Bill</strong>.</li>
                            <li>Business number: <strong class="font-mono text-base">{{ config('mpesa.shortcode') }}</strong></li>
                            <li>Account number: <strong class="font-mono text-base">{{ auth()->user()->account_number }}</strong> (your e-Billing account number)</li>
                            <li>Amount: the balance due for the month</li>
                            <li>Enter your M-Pesa PIN and send.</li>
                            <li>Copy the M-Pesa confirmation code from the SMS, put it in the <strong>Reference</strong> field below, and click <strong>Record payment</strong>.</li>
                        </ol>
                        <p class="mt-2 text-xs text-gray-500">
                             You can only pay for a month after it has ended. Always quote your account number so the payment is matched to you.
                        </p>
                    </div>
            
            </div>
            <!-- From 81-122 it covers the record payment part where a user pays but writes it down in the app and a paybill number section 107-120-->
             <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Pay via M-Pesa (simulated)</h3>
                <form method="POST" action="{{ route('mpesa.simulate') }}"
                      onsubmit="this.querySelector('button').disabled = true;"
                      class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                      <label class="block text-sm">Tax month</label>
                      <input type="month" name="period" max="{{ now()->subMonth()->format('Y-m') }}" value="{{ now()->subMonth()->format('Y-m') }}" class="w-full border-gray-300 rounded" required>
                      @error('period') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                      <label class="block text-sm">Amount (KES)</label>
                      <input type="number" step="0.01" name="amount" class="w-full border-gray-300 rounded" required>
                      @error('amount') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                      <label class="block text-sm">Your M-Pesa number</label>
                      <input type="text" name="phone" placeholder="254712345678" class="w-full border-gray-300 rounded" required>
                      @error('phone') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 bg-green-700 text-white rounded">Pay</button>
                    </div>
                </form>
                <p class="text-xs text-gray-500 mt-2">Simulation: a phone-style prompt will appear. No real money moves.</p>
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
                        @forelse ($payments as $payment) <!-- forelse means for each paper in a folder, print one row(date paid, tax month, amount, reference and status) -->
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
                                    <!-- 156-164 checkks status word saved on the payment and picks the appropriate colour as per the status -->
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 text-sm">Delete</button>
                                        <!-- The delete button sends that payment to destroy()in paymentController
                                         and checks you own the payment and hides it -->
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