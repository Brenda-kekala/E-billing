<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Return status</h2>
    </x-slot>

    @php
        $steps = ['submitted' => 'Submitted', 'processing' => 'Processing', 'accepted' => 'Accepted'];
        $keys = array_keys($steps);
        $current = array_search($return->status, $keys);
        $periodLabel = \Carbon\Carbon::createFromFormat('Y-m', $return->period)->format('F Y');
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-4 bg-green-50 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">
                    Turnover Tax return: {{ $periodLabel }}{{ $return->is_nil ? ' (nil return)' : '' }}
                </h3>

                <div class="flex items-center gap-2 mb-6">
                    @foreach ($steps as $key => $label)
                        @php $idx = array_search($key, $keys); @endphp
                        <div class="flex-1 text-center p-2 rounded text-sm
                            {{ $idx <= $current ? 'bg-green-100 text-green-800 font-semibold' : 'bg-gray-100 text-gray-400' }}">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>

                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <dt class="text-gray-500">Acknowledgement no.</dt>
                    <dd class="font-mono">{{ $return->acknowledgement_no }}</dd>

                    <dt class="text-gray-500">Filed on</dt>
                    <dd>{{ $return->filed_at->format('d M Y, H:i') }}</dd>

                    <dt class="text-gray-500">Declared income</dt>
                    <dd>KES {{ number_format($return->income, 2) }}</dd>

                    <dt class="text-gray-500">Tax declared (1.5%)</dt>
                    <dd>KES {{ number_format($return->tax, 2) }}</dd>

                    <dt class="text-gray-500">Late filing penalty (est.)</dt>
                    <dd>KES {{ number_format($return->penalty, 2) }}</dd>
                </dl>

                @if ($return->status === 'accepted')
                    <p class="mt-6 text-green-700 text-sm">Your return has been accepted. Pay any balance from your dashboard by the due date.</p>
                @else
                    <p class="mt-6 text-gray-500 text-sm">This page updates automatically.</p>
                @endif

                    <x-back-button :href="route('returns.index')" class="mt-4">Back to returns</x-back-button>
            </div>
        </div>
    </div>

    @if ($return->status !== 'accepted')
        <script>setTimeout(function () { location.reload(); }, 5000);</script>
    @endif
</x-app-layout>