<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\User\AllUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Successfully Login',
            'accessToken' => $this['token'],
            'user' => new AllUserResource($this['user']),
        ];
    }
}
