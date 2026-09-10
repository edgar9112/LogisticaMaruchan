<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class EmailNotifier
{
    /**
     * Envía un correo a todos los usuarios administradores.
     */
    public static function toAdmins(Mailable $mailable): void
    {
        foreach (User::query()->where('role', 'admin')->get() as $admin) {
            Mail::to($admin)->send($mailable);
        }
    }
}