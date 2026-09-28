<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Face Recognition Threshold
    |--------------------------------------------------------------------------
    |
    | Default minimum cosine similarity required for a match.
    | Set to 0.45 (45%) according to InsightFace buffalo_l benchmark standards
    | to accommodate various lighting and angle conditions.
    |
    */
    'threshold' => env('FACE_RECOGNITION_THRESHOLD', 0.45),

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
