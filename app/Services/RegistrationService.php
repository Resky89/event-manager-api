<?php

namespace App\Services;

use App\Models\Registration;

class RegistrationService
{
    public function create(array $data): Registration
    {
        return Registration::create($data);
    }
}
