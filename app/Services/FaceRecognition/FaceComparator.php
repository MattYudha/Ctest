<?php

namespace App\Services\FaceRecognition;

class FaceComparator
{
    /**
     * Calculate Cosine Similarity between two embedding vectors.
     *
     * @param array $embeddingA
     * @param array $embeddingB
     * @return float
     */
    public function calculateSimilarity(array $embeddingA, array $embeddingB): float
    {
        if (count($embeddingA) !== count($embeddingB)) {
            throw new \InvalidArgumentException("Embeddings must have the same dimension");
        }

        if (count($embeddingA) === 0) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($embeddingA as $i => $valA) {
            $valB = $embeddingB[$i];
            $dotProduct += $valA * $valB;
            $normA += pow($valA, 2);
            $normB += pow($valB, 2);
        }

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
