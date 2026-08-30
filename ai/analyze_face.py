import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
import cv2
import mediapipe as mp
from mediapipe.tasks import python
from mediapipe.tasks.python import vision
import numpy as np
import sys
import os

# Ellenőrizzük, hogy kaptunk-e fájlt a PHP-tól
if len(sys.argv) < 2:
    print("error_no_file")
    sys.exit()

image_path = sys.argv[1] # php-tól kapott kép elérési útja
model_path = 'models/face_landmarker.task'

base_options = python.BaseOptions(model_asset_path=model_path)
options = vision.FaceLandmarkerOptions(
    base_options=base_options,
    output_face_blendshapes=False,
    num_faces=1
)
detector = vision.FaceLandmarker.create_from_options(options)

# 2. kép feldolgozása

cv_image = cv2.imread(image_path)

if cv_image is None:
    print("error_not_found")
else:
    mp_image = mp.Image.create_from_file(image_path)
    detection_result = detector.detect(mp_image)

    if detection_result.face_landmarks:
        landmarks = detection_result.face_landmarks[0]
        h, w, _ = cv_image.shape

        # Pontok kinyerése
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

        # Arcforma besorolása
        if j_ratio > 0.89:
            res = "szogletes"
        elif ratio > 1.25:
            res = "hosszukas"
        elif 1.15 < ratio <= 1.25:
            res = "ovalis"
        else:
            res = "kerek"

        # Kerekítjük az értékeket 2 tizedesjegyre
        ratio_rounded = round(ratio, 2)
        j_ratio_rounded = round(j_ratio, 2)

        # csak ezt írjuk ki, mert ezt olvassa be a php
        # print(res)
        print(f"RESULT:{res};{ratio_rounded};{j_ratio_rounded}")

    else:
        print("RESULT:error_no_face")