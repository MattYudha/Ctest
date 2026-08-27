<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use App\Services\FaceRecognition\FaceComparator;

class FaceComparatorTest extends TestCase
{
    public function test_calculate_similarity_identical_vectors()
    {
        $comparator = new FaceComparator();
        $vec = [0.1, 0.2, 0.3, 0.4];
        
        $similarity = $comparator->calculateSimilarity($vec, $vec);
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
    }

    public function test_calculate_similarity_orthogonal_vectors()
    {
        $comparator = new FaceComparator();
        $vecA = [1.0, 0.0];
        $vecB = [0.0, 1.0];
        
        $similarity = $comparator->calculateSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);
    }

    public function test_calculate_similarity_opposite_vectors()
    {
        $comparator = new FaceComparator();
        $vecA = [1.0, 2.0];
        $vecB = [-1.0, -2.0];
        
        $similarity = $comparator->calculateSimilarity($vecA, $vecB);
        $this->assertEqualsWithDelta(-1.0, $similarity, 0.0001);
    }

    public function test_throws_exception_on_dimension_mismatch()
    {
        $comparator = new FaceComparator();
        $this->expectException(\InvalidArgumentException::class);
        $comparator->calculateSimilarity([1.0, 2.0], [1.0]);
    }
}
