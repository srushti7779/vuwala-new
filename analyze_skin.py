import sys
import json
import numpy as np
from PIL import Image
import cv2
import dlib
import os

# Load Dlib's facial landmark predictor
predictor_path = os.path.join(os.path.dirname(__file__), "shape_predictor_68_face_landmarks.dat")
detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor(predictor_path)

# ---------- ADDITIONAL FUNCTION ----------

def detect_facial_landmarks(image):
    """Detects 68 facial landmarks using dlib."""
    gray = cv2.cvtColor(image, cv2.COLOR_RGB2GRAY)
    faces = detector(gray)

    if len(faces) == 0:
        return {
            "landmarks_detected": False,
            "message": "No face detected"
        }

    face = faces[0]  # Take the first detected face
    landmarks = predictor(gray, face)

    points = [{"x": landmarks.part(i).x, "y": landmarks.part(i).y} for i in range(68)]
    return {
        "landmarks_detected": True,
        "total_points": len(points),
        "points": points
    }

# ---------- EXISTING ANALYSIS FUNCTIONS ----------

# ... keep your detect_acne, detect_wrinkles, detect_skin_tone, detect_hair_density, and recommend_services here unchanged ...

# ---------- MAIN EXECUTION ----------

if len(sys.argv) < 2:
    print(json.dumps({"error": "No image path provided"}))
    sys.exit(1)

image_path = sys.argv[1]

try:
    img = Image.open(image_path).convert('RGB')
    img_array = np.array(img)

    results = {
        "acne": detect_acne(img_array),
        "wrinkles": detect_wrinkles(img_array),
        "skin_tone": detect_skin_tone(img_array),
        "hair_density": detect_hair_density(img_array),
        "landmarks": detect_facial_landmarks(img_array)
    }

    results["recommendations"] = recommend_services(results)

    print(json.dumps(results))

except Exception as e:
    print(json.dumps({"error": str(e)}))
    sys.exit(1)
