<?php

namespace App\Policies;

use App\Models\ContractProcess;
use App\Models\User;

class ContractProcessPolicy
{
    /**
     * Determina si el usuario puede ver cualquier proceso
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Super Admin',
            'Jefe Unidad',
            'Abogado Unidad',
            'Abogado Enlace Jurídica',
            'Apoyo Estructuración',
            'Presupuesto',
        ]);
    }

    /**
     * Determina si el usuario puede ver el proceso
     */
    public function view(User $user, ContractProcess $process): bool
    {
        // Super Admin puede ver todo
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Puede ver si está relacionado al proceso
        return $process->contractor_id === $user->id
            || $process->supervisor_id === $user->id
            || $process->ordering_officer_id === $user->id
            || $process->unit_head_id === $user->id
            || $process->unit_lawyer_id === $user->id
            || $process->link_lawyer_id === $user->id
            || $process->created_by === $user->id
            || $user->hasRole(['Jefe Unidad', 'Abogado Enlace Jurídica']);
    }

    /**
     * Determina si el usuario puede crear procesos
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Super Admin',
            'Jefe Unidad',
            'Apoyo Estructuración',
        ]);
    }

    /**
     * Determina si el usuario puede actualizar el proceso
     */
    public function update(User $user, ContractProcess $process): bool
    {
        // Super Admin puede editar todo
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Jefe de Unidad puede editar sus procesos
        if ($user->hasRole('Jefe Unidad') && $process->unit_head_id === $user->id) {
            return true;
        }

        // El creador puede editar
        if ($process->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede avanzar el proceso
     */
    public function advance(User $user, ContractProcess $process): bool
    {
        // Super Admin siempre puede
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Validar según etapa
        $step = $process->current_step;

        return match($step) {
            0 => $user->hasRole(['Jefe Unidad', 'Apoyo Estructuración'])
                || $process->unit_head_id === $user->id
                || $process->created_by === $user->id,
                
            1 => $user->hasRole(['Jefe Unidad', 'Apoyo Estructuración', 'Presupuesto'])
                || $process->unit_head_id === $user->id,
                
            2 => $user->hasRole(['Abogado Unidad', 'Abogado Enlace Jurídica'])
                || $process->unit_lawyer_id === $user->id,
                
            3 => $user->hasRole(['Abogado Unidad', 'Apoyo Estructuración'])
                || $process->unit_lawyer_id === $user->id,
                
            4 => $user->hasRole(['Jefe Unidad', 'Apoyo Estructuración'])
                || $process->unit_head_id === $user->id,
                
            5 => $user->hasRole('Abogado Enlace Jurídica')
                || $process->link_lawyer_id === $user->id,
                
            6 => $user->hasRole(['Abogado Enlace Jurídica', 'Apoyo Estructuración'])
                || $process->link_lawyer_id === $user->id,
                
            7 => $user->hasRole(['Jefe Unidad', 'Presupuesto'])
                || $process->unit_head_id === $user->id,
                
            8 => $user->hasRole('Abogado Enlace Jurídica')
                || $process->link_lawyer_id === $user->id,
                
            9 => $user->hasRole(['Supervisor', 'Jefe Unidad'])
                || $process->supervisor_id === $user->id
                || $process->unit_head_id === $user->id,
                
            default => false,
        };
    }

    /**
     * Determina si el usuario puede devolver el proceso a etapa anterior
     */
    public function return(User $user, ContractProcess $process): bool
    {
        return $user->hasAnyRole([
            'Super Admin',
            'Abogado Enlace Jurídica',
            'Jefe Unidad',
        ]);
    }

    /**
     * Determina si el usuario puede subir documentos
     */
    public function uploadDocument(User $user, ContractProcess $process): bool
    {
        // Super Admin siempre puede
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Usuarios relacionados al proceso pueden subir
        return $process->unit_head_id === $user->id
            || $process->unit_lawyer_id === $user->id
            || $process->link_lawyer_id === $user->id
            || $process->supervisor_id === $user->id
            || $process->contractor_id === $user->id
            || $process->created_by === $user->id
            || $user->hasRole(['Apoyo Estructuración', 'Presupuesto']);
    }

    /**
     * Determina si el usuario puede aprobar documentos
     */
    public function approveDocument(User $user, ContractProcess $process): bool
    {
        return $user->hasAnyRole([
            'Super Admin',
            'Abogado Unidad',
            'Abogado Enlace Jurídica',
            'Jefe Unidad',
        ]) || $process->unit_lawyer_id === $user->id
           || $process->link_lawyer_id === $user->id
           || $process->unit_head_id === $user->id;
    }

    /**
     * Determina si el usuario puede eliminar documentos
     */
    public function deleteDocument(User $user, ContractProcess $process): bool
    {
        // Solo Super Admin y el que subió el documento
        return $user->hasRole('Super Admin')
            || $process->created_by === $user->id;
    }

    /**
     * Determina si el usuario puede firmar documentos
     */
    public function signDocument(User $user, ContractProcess $process): bool
    {
        return $process->ordering_officer_id === $user->id
            || $process->supervisor_id === $user->id
            || $process->contractor_id === $user->id
            || $process->link_lawyer_id === $user->id
            || $user->hasRole(['Abogado Enlace Jurídica', 'Secretario']);
    }

    /**
     * Determina si el usuario puede cancelar el proceso
     */
    public function cancel(User $user, ContractProcess $process): bool
    {
        return $user->hasAnyRole([
            'Super Admin',
            'Jefe Unidad',
            'Abogado Enlace Jurídica',
        ]);
    }

    /**
     * Determina si el usuario puede eliminar el proceso
     */
    public function delete(User $user, ContractProcess $process): bool
    {
        return $user->hasRole('Super Admin');
    }
}
