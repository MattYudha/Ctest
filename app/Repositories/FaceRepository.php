<?php

namespace App\Repositories;

use App\Models\FaceProfile;
use App\Repositories\Contracts\FaceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FaceRepository implements FaceRepositoryInterface
{
    public function create(array $data): FaceProfile
    {
        return FaceProfile::create($data);
    }

    public function deleteByUser(int $userId): void
    {
        FaceProfile::where('user_id', $userId)->delete();
    }

    public function getByUser(int $userId): ?FaceProfile
    {
        return FaceProfile::where('user_id', $userId)->first();
    }

    public function getAll(): Collection
    {
        return FaceProfile::all();
    }
}
