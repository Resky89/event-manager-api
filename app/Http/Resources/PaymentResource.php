<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'registration_id' => $this->registration_id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'transaction_ref' => $this->transaction_ref,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
