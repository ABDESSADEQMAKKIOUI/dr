<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'device' => $this->device,
            'location' => $this->location,
            'status' => $this->status,
            'logged_in_at' => $this->logged_in_at?->format('Y-m-d H:i:s'),
            'logged_out_at' => $this->logged_out_at?->format('Y-m-d H:i:s'),
            'duration' => $this->logged_out_at
                ? $this->logged_in_at->diffForHumans($this->logged_out_at, true)
                : 'Active',
        ];
    }
}
