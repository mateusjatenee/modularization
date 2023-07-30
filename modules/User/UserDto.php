<?php

namespace Modules\User;

use App\Models\User;

readonly class UserDto
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name
    ) {
    }

    public static function fromEloquentModel(User $user): self
    {
        return new self($user->id, $user->email, $user->name);
    }
}
