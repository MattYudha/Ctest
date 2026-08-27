<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckAttendanceRequest;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(private FaceRecognitionService $service) {}

    public function check(CheckAttendanceRequest $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $image = $request->file('image');
        $relativePath = $image->store('attendance_photos', 'public');
        $absolutePath = storage_path('app/public/' . $relativePath);
        $storeOriginal = config('face.store_original_image', true);

        $result = $this->service->checkAttendance($userId, $absolutePath);

        if (!$storeOriginal) {
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }
}
