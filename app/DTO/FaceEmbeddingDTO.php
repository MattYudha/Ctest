<?php

namespace App\DTO;

class FaceEmbeddingDTO
{
    public function __construct(
        public readonly array $embedding,
        public readonly string $modelName = 'insightface'
    ) {}
}
