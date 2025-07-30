<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CitaPolicy
{
    /**
     * Determine whether the user can view the model.
     * Un cliente puede ver su cita, un barbero la suya, y un dueño las de su barbería.
     */
    public function view(User $user, Cita $cita): bool
    {
        return $user->id === $cita->cliente_id
            || $user->id === $cita->barbero->user_id
            || $user->id === $cita->barberia->owner_id;
    }

    /**
     * Determine whether the user can update the model.
     * Solo el cliente que creó la cita puede actualizarla (ej. cancelar).
     */
    public function update(User $user, Cita $cita): bool
    {
        return $user->id === $cita->cliente_id
            || ($cita->barbero && $user->id === $cita->barbero->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cita $cita): bool
    {
        // Por lo general, solo el cliente puede eliminarla (si se permite)
        return $user->id === $cita->cliente_id;
    }
    
    /**
     * Determine whether the user can manage the payment of the appointment.
     * Solo el dueño de la barbería puede gestionar el pago.
     */
    public function managePayment(User $user, Cita $cita): bool
    {
        return $user->id === $cita->barberia->owner_id;
    }
}
