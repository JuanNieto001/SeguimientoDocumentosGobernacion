<x-app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-bold text-gray-900">Documentos por Vencer</h1>
    </x-slot>

    <div class="p-6 space-y-4">
        <form method="GET" class="bg-white rounded-2xl border p-4 flex items-end gap-4" style="border-color:#e2e8f0">
            <div>
                <label class="block text-xs text-gray-500 uppercase tracking-wide mb-1">Días de anticipación</label>
                <input type="number" name="days" value="{{ $days }}" min="1" max="90"
                       class="rounded-lg border-gray-300 text-sm w-24">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
        </form>

        <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:#e2e8f0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Documento</th>
                        <th class="px-4 py-3">Proceso</th>
                        <th class="px-4 py-3">Subido por</th>
                        <th class="px-4 py-3">Vence</th>
                        <th class="px-4 py-3 text-center">Días restantes</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:#f1f5f9">
                    @forelse ($documents as $doc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $doc->document_name ?? $doc->file_name }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->document_type ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($doc->process)
                            <a href="{{ route('contract-processes.show', $doc->process) }}" class="text-blue-600 hover:underline">
                                {{ $doc->process->process_number ?? 'Proceso #'.$doc->process->id }}
                            </a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ optional($doc->uploadedBy)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ optional($doc->expires_at)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $diasRestantes = now()->diffInDays($doc->expires_at, false); @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $diasRestantes <= 3 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $diasRestantes }} días
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">No hay documentos por vencer en los próximos {{ $days }} días.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
