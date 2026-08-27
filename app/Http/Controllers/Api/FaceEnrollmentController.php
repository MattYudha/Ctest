<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFaceRequest;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FaceEnrollmentController extends Controller
{
    public function __construct(private FaceRecognitionService $service) {}

    public function register(RegisterFaceRequest $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $imagePaths = [];
        $storeOriginal = config('face.store_original_image', true);
        $savedPaths = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('face_enrollment', 'public');
            $savedPaths[] = $path;
            $imagePaths[] = storage_path('app/public/' . $path);
        }

        $result = $this->service->enrollFace($userId, $imagePaths);

        // Cleanup or store
        if ($result['success']) {
            $facesDir = public_path('faces');
            if (!file_exists($facesDir)) {
                mkdir($facesDir, 0777, true);
            }
            
            // Move the first valid image to be the user's face photo
            $finalPath = $facesDir . '/user_' . $userId . '.jpg';
            if (file_exists($imagePaths[0])) {
                rename($imagePaths[0], $finalPath);
            }
            
            return response()->json($result);
        }

        if (!$storeOriginal) {
            foreach ($imagePaths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        return response()->json($result, 400);
    }

    public function destroy(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        if ($user->faceProfile) {
            $user->faceProfile()->delete();
        }

        // Try to delete the image if it exists
        $extensions = ['jpeg', 'png', 'jpg'];
        foreach ($extensions as $ext) {
            $filePath = public_path('faces/user_' . $user->id . '.' . $ext);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Face data removed successfully.']);
        }
        return redirect()->back()->with('success', 'Face data removed successfully.');
    }

    public function verify(\Illuminate\Http\Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'image' => 'required|string'
        ]);

        // Decode base64 image
        $imageParts = explode(";base64,", $request->image);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'jpeg';
        $imageBase64 = base64_decode($imageParts[1]);

        $fileName = 'verify_' . $userId . '_' . time() . '.' . $imageType;
        $path = storage_path('app/public/' . $fileName);
        
        file_put_contents($path, $imageBase64);

        $result = $this->service->verifyFace($userId, $path);

        // Delete temporary file
        if (file_exists($path)) {
            unlink($path);
        }

        return response()->json($result);
    }
}
