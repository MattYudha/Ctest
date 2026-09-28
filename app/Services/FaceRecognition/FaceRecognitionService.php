<?php

namespace App\Services\FaceRecognition;

use App\Repositories\Contracts\FaceRepositoryInterface;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FaceRecognitionService
{
    public function __construct(
        private FaceEmbeddingGenerator $generator,
        private FaceComparator $comparator,
        private FaceRepositoryInterface $repository
    ) {}

    /**
     * @param int $userId
     * @param array $imagePaths
     * @return array
     */
    public function enrollFace(int $userId, array $imagePaths): array
    {
        DB::beginTransaction();
        try {
            $this->repository->deleteByUser($userId);

            $embeddings = [];
            foreach ($imagePaths as $path) {
                $dto = $this->generator->generate($path);
                $embeddings[] = $dto->embedding;
            }
            
            $averageEmbedding = $this->averageEmbeddings($embeddings);

            $this->repository->create([
                'user_id' => $userId,
                'embedding' => $averageEmbedding,
                'model_name' => 'insightface',
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Face registered successfully.'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Face Enrollment Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param int $userId
     * @param string $imagePath
     * @return array
     */
    public function checkAttendance(int $userId, string $imagePath): array
    {
        try {
            $profile = $this->repository->getByUser($userId);
            if (!$profile) {
                return ['success' => false, 'message' => 'Face profile not found for user.'];
            }

            $currentDto = $this->generator->generate($imagePath);
            $similarity = $this->comparator->calculateSimilarity($profile->embedding, $currentDto->embedding);
            $threshold = config('face.threshold', 0.45);

            if ($similarity >= $threshold) {
                DB::beginTransaction();
                Attendance::create([
                    'user_id' => $userId,
                    'similarity' => $similarity,
                    'photo_path' => $imagePath,
                ]);
                DB::commit();

                Log::info("Face recognition checkAttendance passed for user {$userId}: similarity={$similarity}, threshold={$threshold}");

                return [
                    'success' => true, 
                    'similarity' => $similarity,
                    'threshold' => $threshold,
                    'attendance_created' => true,
                ];
            }

            Log::warning("Face recognition checkAttendance failed for user {$userId}: similarity={$similarity}, threshold={$threshold}");

            return [
                'success' => false,
                'similarity' => $similarity,
                'threshold' => $threshold,
                'message' => 'Face mismatch or similarity too low. (Score: ' . round($similarity * 100, 1) . '%, Required: ' . round($threshold * 100, 1) . '%)',
            ];

        } catch (Exception $e) {
            Log::error("Attendance Check Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify face without creating attendance record
     *
     * @param int $userId
     * @param string $imagePath
     * @return array
     */
    public function verifyFace(int $userId, string $imagePath): array
    {
        try {
            $profile = $this->repository->getByUser($userId);
            if (!$profile) {
                return ['success' => false, 'message' => 'Face profile not found for user.'];
            }

            $currentDto = $this->generator->generate($imagePath);
            $similarity = $this->comparator->calculateSimilarity($profile->embedding, $currentDto->embedding);
            $threshold = config('face.threshold', 0.45);

            if ($similarity >= $threshold) {
                Log::info("Face verification passed for user {$userId}: similarity={$similarity}, threshold={$threshold}");
                return [
                    'success' => true, 
                    'similarity' => $similarity,
                    'threshold' => $threshold,
                ];
            }

            Log::warning("Face verification failed for user {$userId}: similarity={$similarity}, threshold={$threshold}");

            return [
                'success' => false,
                'similarity' => $similarity,
                'threshold' => $threshold,
                'message' => 'Face mismatch or similarity too low. (Score: ' . round($similarity * 100, 1) . '%, Required: ' . round($threshold * 100, 1) . '%)',
            ];
        } catch (Exception $e) {
            Log::error("Face Verification Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function averageEmbeddings(array $embeddings): array
    {
        $count = count($embeddings);
        if ($count === 0) return [];
        $length = count($embeddings[0]);
        $averaged = array_fill(0, $length, 0.0);

        foreach ($embeddings as $emb) {
            foreach ($emb as $i => $val) {
                $averaged[$i] += $val;
            }
        }

        for ($i = 0; $i < $length; $i++) {
            $averaged[$i] /= $count;
        }

        return $averaged;
    }
}
