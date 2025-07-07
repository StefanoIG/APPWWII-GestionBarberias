@component('mail::message')
# ¡Tu cita está confirmada!

Hola **{{ $nombreCliente }}**,

Nos complace informarte que tu cita ha sido confirmada con éxito.

**Detalles de la cita:**
- **Servicio:** {{ $nombreServicio }}
- **Barbero:** {{ $nombreBarbero }}
- **Fecha:** {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
- **Hora:** {{ \Carbon\Carbon::parse($hora)->format('h:i A') }}

Te esperamos puntualmente. Si necesitas reprogramar, por favor hazlo con al menos 1 hora de antelación desde nuestra plataforma.

@component('mail::button', ['url' => $url, 'color' => 'success'])
Ver mis citas
@endcomponent

Gracias por tu preferencia,<br>
El equipo de Barberia S.A.
@endcomponent
