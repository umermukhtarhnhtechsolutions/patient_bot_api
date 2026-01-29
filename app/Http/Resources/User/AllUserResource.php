<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = ((array) $this)['resource']->toArray();
        return [
            'id' => $this->id ?? '',
            'role' => $this->role ?? '',
            'email' => $this->email ?? '',
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'phone_no' => $this->phone_no ?? '',
            'image' => (!empty($this->image)) ? request()->getSchemeAndHttpHost() . '/storage/' . $this->image : '',
            'is_active' => $this->is_active ?? false,
            'created_at' => date('Y-m-d', strtotime($this->created_at)) ?? '',
        ];
    }
}
