import os
import cv2
import numpy as np
from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.responses import JSONResponse
from insightface.app import FaceAnalysis

app = FastAPI(title="Face Recognition Microservice")

# Allow configuring model via environment variable (default buffalo_l, or buffalo_s for low-resource VPS)
MODEL_NAME = os.getenv("FACE_MODEL_NAME", "buffalo_l")

try:
    face_app = FaceAnalysis(name=MODEL_NAME, providers=['CPUExecutionProvider'])
    face_app.prepare(ctx_id=-1, det_size=(640, 640))
except Exception as e:
    print(f"Warning: Model initialization failed. Models might be downloading or there's an environment issue. Error: {e}")

@app.get("/health")
def health():
    return {"status": "ok", "model": MODEL_NAME}

@app.post("/api/extract")
def extract_face(file: UploadFile = File(...)):
    """
    Synchronous handler allows FastAPI to execute heavy CPU-bound ONNX inference
    in a thread pool worker, preventing asyncio event loop starvation on concurrent requests.
    """
    if file.content_type and not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="Invalid file type. Only images are allowed.")
    
    try:
        contents = file.file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return JSONResponse({"success": False, "message": "Failed to decode image."})

        # Run face detection and recognition
        faces = face_app.get(img)

        if len(faces) == 0:
            return JSONResponse({"success": False, "message": "No face detected in the image."})
        
        if len(faces) > 1:
            return JSONResponse({"success": False, "message": f"Multiple faces ({len(faces)}) detected. Please ensure only one face is in the frame."})

        # Get embedding of the single detected face (512-D float array)
        embedding = faces[0].embedding.tolist()

        return JSONResponse({
            "success": True,
            "embedding": embedding,
            "model_name": f"insightface_{MODEL_NAME}"
        })

    except Exception as e:
        return JSONResponse({"success": False, "message": str(e)}, status_code=500)

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8001)
