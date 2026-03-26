<?php

namespace App\DTOs;

readonly class LanCoreUser
{
    public function __construct(
        public int $id,
        public string $username,
        public ?string $locale,
        public ?string $avatar,
        public ?string $createdAt,
        public ?string $email,
        public ?array $roles,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            username: (string) $data['username'],
            locale: $data['locale'] ?? null,
            avatar: $data['avatar_url'] ?? $data['avatar'] ?? null,
            createdAt: $data['created_at'] ?? null,
            email: $data['email'] ?? null,
            roles: $data['roles'] ?? null,
        );
    }
}
