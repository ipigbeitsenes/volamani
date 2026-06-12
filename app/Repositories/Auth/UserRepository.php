<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function findByReferralCode(string $code): ?User
    {
        return $this->model->where('referral_code', $code)->first();
    }

    public function updateProfile(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function getUsersWithRole(string $role): Collection
    {
        return $this->model->role($role)->get();
    }
}
