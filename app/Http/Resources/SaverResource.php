<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'account_number'  => $this->account_number,
            'student_id'      => $this->student_id,
            'nis'             => $this->student_id,
            'student_name'    => $this->student_name,
            'name'            => $this->student_name,
            'grade_class'     => $this->grade_class ?? '-',
            'saldo'           => (float) $this->saldo,
            'balance'         => (float) $this->saldo,
            'current_balance' => (float) $this->saldo,
            'formatted_saldo' => 'Rp ' . number_format($this->saldo, 0, ',', '.'),
            'created_at'      => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
