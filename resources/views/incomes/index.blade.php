<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Income Records</h2>
    </x-slot>

    <!-- This is the add form for income entries.Contains date , amount and description
     the description dropdown fills itself with a loop meaning for each category in the settings file
     make one option and to add a category you change config/tax.php and nothing else  -->

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-2">Your tax regime</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Choose how you want to be taxed. TOT is charged on gross income monthly; self-assessment is charged on net profit annually. You can switch at any time.
                </p>
                <form method="POST" action="{{ route('regime.update') }}" class="flex items-center gap-4">
                    @csrf
                    <select name="tax_regime" class="border-gray-300 rounded">
                        <option value="tot" @selected(auth()->user()->tax_regime !== 'self_assessment')>Turnover Tax (TOT)</option>
                        <option value="self_assessment" @selected(auth()->user()->tax_regime === 'self_assessment')>Self-Assessment</option>
                    </select>
                    <button class="px-4 py-2 bg-gray-800 text-white rounded">Save</button>
                </form>
                <!-- 17-29 this adds the tax regime box in incomes page -->
            </div>


            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Add income</h3>
                <form method="POST" action="{{ route('incomes.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm">Date</label>
                        <input type="date" name="income_date" value="{{ old('income_date', date('Y-m-d')) }}" class="w-full border-gray-300 rounded" required>
                        @error('income_date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm">Amount (KES)</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="w-full border-gray-300 rounded" required>
                        @error('amount') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm">Description</label>
                        <select name="description" class="w-full border-gray-300 rounded" required>
                           <option value="">Select category</option>
                           @foreach (config('tax.income_categories') as $value => $label)
                               <option value="{{ $label }}" @selected(old('description') === $label)>{{ $label }}</option>
                           @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Save</button>

                    </div>
                </form>
            </div>

            <div class="bg-white p-6 shadow rounded">
                <h3 class="font-semibold mb-4">Your entries</h3>
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Date</th>
                            <th>Amount (KES)</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($incomes as $income)
                        <!-- this makes one row per entry -->
                            <tr class="border-b">
                                <td class="py-2">{{ $income->income_date }}</td>
                                <td>{{ number_format($income->amount, 2) }}</td>
                                <td>{{ $income->description }}</td>
                                <td>
                                    <form method="POST" action="{{ route('incomes.destroy', $income) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-gray-500">No entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>