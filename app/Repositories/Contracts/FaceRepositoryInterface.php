<?php

namespace App\Repositories\Contracts;

use App\Models\FaceProfile;
use Illuminate\Database\Eloquent\Collection;

interface FaceRepositoryInterface
{
    public function create(array $data): FaceProfile;
    public function deleteByUser(int $userId): void;
    public function getByUser(int $userId): ?FaceProfile;
    public function getAll(): Collection;
}
