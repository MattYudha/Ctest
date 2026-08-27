<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Recognition Threshold
    |--------------------------------------------------------------------------
    |
    | Default minimum cosine similarity required for a match.
    | Range is -1.0 to 1.0, but practically above 0.85 indicates a match.
    |
    */
    'threshold' => env('FACE_RECOGNITION_THRESHOLD', 0.85),

    /*
    |--------------------------------------------------------------------------
    | Python Microservice Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for communicating with the InsightFace Python service.
    |
    */
    'service_url' => env('FACE_SERVICE_URL', 'http://127.0.0.1:8001'),
    'service_timeout' => env('FACE_SERVICE_TIMEOUT', 10), // seconds

    /*
    |--------------------------------------------------------------------------
    | Original Image Storage
    |--------------------------------------------------------------------------
    |
    | Whether to store original images on disk.
    | If false, files are discarded after embeddings are generated.
    |
    */
    'store_original_image' => env('FACE_STORE_ORIGINAL', true),

    /*
    |--------------------------------------------------------------------------
    | Image Upload Limits
    |--------------------------------------------------------------------------
    */
    'max_file_size' => env('FACE_MAX_FILE_SIZE', 5120), // 5MB in KB
    'allowed_extensions' => ['jpg', 'jpeg', 'png'],
    'min_resolution' => [
        'width' => 640,
        'height' => 480,
    ],
    'enrollment_min_photos' => 5,
];
