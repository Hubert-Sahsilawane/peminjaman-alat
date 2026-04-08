<?php

namespace App\Http\Resources\Peminjam;

use App\Http\Resources\Admin\ToolResource;
use App\Http\Resources\Admin\LoanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'available_tools' => ToolResource::collection($this['available_tools']),
            'loan_history' => LoanResource::collection($this['loan_history']),
        ];
    }
}
