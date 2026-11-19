<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">M-Pesa Prompt (Simulation)</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-sm mx-auto px-4">
            <div class="bg-gray-900 rounded-3xl p-4 shadow-xl">
                <div class="bg-white rounded-2xl p-5">
                    <p class="text-xs text-gray-500 text-center mb-3">Simulated phone screen</p>

                    <div class="text-center mb-4">
                        <p class="text-green-700 font-bold text-lg">M-PESA</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $payment->reference }}</p>
                    </div>

                    <p class="text-sm text-gray-800 mb-4">
                        Do you want to pay <strong>KES {{ number_format($payment->amount, 2) }}</strong>
                        to <strong>E-Billing</strong> for account <strong>{{ $payment->period }}</strong>?
                        Enter your M-PESA PIN.
                    </p>

                    <form method="POST" action="{{ route('mpesa.confirm', $payment) }}">
                        @csrf
                        <input type="password" name="pin" inputmode="numeric" maxlength="4"
                               autocomplete="off" placeholder="****"
                               class="w-full text-center text-2xl tracking-widest border-gray-300 rounded" autofocus>
                        @error('pin') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <button name="action" value="cancel" class="px-4 py-2 border border-gray-300 rounded text-gray-700">Cancel</button>
                            <button name="action" value="ok" class="px-4 py-2 bg-green-700 text-white rounded">Send</button>
                        </div>
                    </form>

                    <p class="text-xs text-gray-400 text-center mt-4">Demo only. Any 4 digits will work. No money moves and the PIN is not saved.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>