<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">M-Pesa Payment Status</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-blue-100 text-blue-800 rounded">{{ session('status') }}</div>
            @endif

            @if ($payment->status === 'completed')
                <div class="bg-green-50 border border-green-300 rounded-lg p-4 font-mono text-sm text-green-900">
                    <p class="font-semibold mb-1">M-PESA Confirmation</p>
                    <p>{{ $payment->mpesa_receipt }} Confirmed. KES{{ number_format($payment->amount, 2) }} paid to E-Billing for account {{ $payment->period }} on {{ $payment->paid_on }}. New M-PESA balance is KES{{ number_format(rand(500, 50000), 2) }}.</p>
                    <p class="text-xs text-green-700 mt-2">(Simulated sandbox confirmation — no real money moved.)</p>
                </div>
            @endif

            <div class="bg-white p-6 shadow rounded">
                <p class="text-sm text-gray-500">Tax month</p>
                <p class="font-semibold mb-4">{{ $payment->period }}</p>

                <p class="text-sm text-gray-500">Amount</p>
                <p class="font-semibold mb-4">KES {{ number_format($payment->amount, 2) }}</p>

                <p class="text-sm text-gray-500">Status</p>
                <p class="mb-4">
                    @if ($payment->status === 'completed')
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Completed</span>
                    @elseif ($payment->status === 'failed')
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Failed</span>
                    @else
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Pending</span>
                    @endif
                </p>

                @if ($payment->mpesa_receipt)
                    <p class="text-sm text-gray-500">M-Pesa receipt</p>
                    <p class="font-semibold mb-4">{{ $payment->mpesa_receipt }}</p>
                @endif

                @if ($payment->status === 'pending')
                    <a href="{{ route('mpesa.status', $payment) }}" class="inline-block px-4 py-2 bg-gray-800 text-white rounded">Refresh status</a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 underline">Back to dashboard</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>