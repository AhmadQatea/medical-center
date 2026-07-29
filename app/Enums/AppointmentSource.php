<?php

namespace App\Enums;

enum AppointmentSource: string
{
    case Instant = 'instant';
    case Public = 'public';
    case Whatsapp = 'whatsapp';
}
