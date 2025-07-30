<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Barbero;

class BarberoPolicy
{
    /**
     * Permite que el dueño de la barbería pueda actualizar al barbero.
     */
    public function update(User $user, Barbero $barbero): bool
    {
        return $user->id === $barbero->barberia->owner_id;
    }

    /**
     * Permite que el dueño de la barbería pueda eliminar al barbero.
     */
    public function delete(User $user, Barbero $barbero): bool
    {
        return $user->id === $barbero->barberia->owner_id;
    }
}
