<?php

namespace App\Http\Resources\Petugas;

use App\Http\Resources\Admin\LoanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetugasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pending_loans' => LoanResource::collection($this['pending_loans']),
            'active_loans' => LoanResource::collection($this['active_loans']),
            'completed_loans' => LoanResource::collection($this['completed_loans']),
            'stats' => [
                'total_pending' => $this['pending_loans']->count(),
                'total_active' => $this['active_loans']->count(),
                'total_completed' => $this['completed_loans']->count(),
            ]
        ];
    }
}
