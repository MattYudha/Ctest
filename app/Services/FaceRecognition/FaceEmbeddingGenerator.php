<?php

namespace App\Services\FaceRecognition;

use App\DTO\FaceEmbeddingDTO;
use Illuminate\Support\Facades\Http;
use Exception;

class FaceEmbeddingGenerator
{
    /**
     * @param string $imagePath Absolute path to the image
     * @return FaceEmbeddingDTO
     * @throws Exception
     */
    public function generate(string $imagePath): FaceEmbeddingDTO
    {
        $url = config('face.service_url') . '/api/extract';
        $timeout = config('face.service_timeout');

        $response = Http::timeout($timeout)
            ->attach('file', file_get_contents($imagePath), basename($imagePath))
            ->post($url);

        if ($response->failed()) {
            throw new Exception("Face microservice HTTP error: " . $response->body());
        }

        $data = $response->json();

        if (empty($data['success']) || $data['success'] === false) {
            throw new Exception($data['message'] ?? 'Failed to extract face embedding');
        }

        return new FaceEmbeddingDTO(
            embedding: $data['embedding'],
            modelName: $data['model_name'] ?? 'insightface'
        );
    }
}
