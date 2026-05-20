import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'  # Csak a kritikus hibákat mutatja
import cv2
import mediapipe as mp
from mediapipe.tasks import python
from mediapipe.tasks.python import vision
import numpy as np
import sys # Szükséges a parancssori argumentumokhoz
import os

# ==========================================
# 1. DINAMIKUS ELÉRÉS ÉS INICIALIZÁLÁS
# ==========================================

# Ellenőrizzük, hogy kaptunk-e fájlt a PHP-tól
if len(sys.argv) < 2:
    print("error_no_file")
    sys.exit()

image_path = sys.argv[1] # A PHP-tól kapott kép útvonala
model_path = 'models/face_landmarker.task'

base_options = python.BaseOptions(model_asset_path=model_path)
options = vision.FaceLandmarkerOptions(
    base_options=base_options,
    output_face_blendshapes=False,
    num_faces=1
)
detector = vision.FaceLandmarker.create_from_options(options)

# ==========================================
# 2. KÉP FELDOLGOZÁSA
# ==========================================
cv_image = cv2.imread(image_path)

if cv_image is None:
    print("error_not_found")
else:
    mp_image = mp.Image.create_from_file(image_path)
    detection_result = detector.detect(mp_image)

    if detection_result.face_landmarks:
        landmarks = detection_result.face_landmarks[0]
        h, w, _ = cv_image.shape

        # Pontok kinyerése (a te logikád alapján)
        top_y = landmarks[10].y * h
        bottom_y = landmarks[152].y * h
        left_x = landmarks[234].x * w
        right_x = landmarks[454].x * w
        jaw_l_x = landmarks[58].x * w
        jaw_r_x = landmarks[288].x * w

        f_height = bottom_y - top_y
        f_width = right_x - left_x
        j_width = jaw_r_x - jaw_l_x

        ratio = f_height / f_width
        j_ratio = j_width / f_width

        # Arcforma besorolása (a te logikád alapján)
        if j_ratio > 0.89:
            res = "szogletes" # Kisbetűvel, ékezet nélkül az SQL miatt biztonságosabb
        elif ratio > 1.25:
            res = "hosszukas"
        elif 1.15 < ratio <= 1.25:
            res = "ovalis"
        else:
            res = "kerek"

        # CSAK EZT ÍRJUK KI - Ezt olvassa be a PHP
        # print(res)
        print(f"RESULT:{res}")

    else:
        # print("error_no_face")
        print("RESULT:error_no_face")

# Fontos: A cv2.imshow() és waitKey() részeket töröltük,
# mert a PHP-nak nincs képernyője, ahol megjeleníthetné.