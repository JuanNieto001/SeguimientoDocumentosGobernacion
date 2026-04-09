@csrf
<div class="grid md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="text-xs font-semibold text-gray-600">Aplicación</label>
        <input name="aplicacion" value="{{ old('aplicacion', $contrato->aplicacion) }}" required
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Número de contrato</label>
        <input name="numero_contrato" value="{{ old('numero_contrato', $contrato->numero_contrato) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Proveedor</label>
        <input name="proveedor" value="{{ old('proveedor', $contrato->proveedor) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Fecha de inicio</label>
        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', optional($contrato->fecha_inicio)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Fecha de finalización</label>
        <input type="date" name="fecha_fin" value="{{ old('fecha_fin', optional($contrato->fecha_fin)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Valor total</label>
        <input type="number" step="0.01" min="0" name="valor_total" value="{{ old('valor_total', $contrato->valor_total) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Estado</label>
        <select name="estado" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
            @foreach(['vigente' => 'Vigente', 'vencido' => 'Vencido', 'por_vencer' => 'Por vencer', 'suspendido' => 'Suspendido'] as $value => $label)
                <option value="{{ $value }}" @selected(old('estado', $contrato->estado ?: 'vigente') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label class="text-xs font-semibold text-gray-600">Objeto / información relevante</label>
        <textarea name="objeto" rows="3" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">{{ old('objeto', $contrato->objeto) }}</textarea>
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">ID Proceso SECOP</label>
        <input name="secop_proceso_id" value="{{ old('secop_proceso_id', $contrato->secop_proceso_id) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">URL SECOP</label>
        <input name="secop_url" value="{{ old('secop_url', $contrato->secop_url) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div>
        <label class="text-xs font-semibold text-gray-600">Responsable</label>
        <input name="responsable" value="{{ old('responsable', $contrato->responsable) }}"
               class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $contrato->activo ?? true))>
            Activo
        </label>
    </div>

    <div class="md:col-span-2">
        <label class="text-xs font-semibold text-gray-600">Observaciones</label>
        <textarea name="observaciones" rows="3" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm" style="border-color:#e2e8f0">{{ old('observaciones', $contrato->observaciones) }}</textarea>
    </div>
</div>

@if($errors->any())
    <div class="mt-4 p-3 rounded-xl text-sm" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
