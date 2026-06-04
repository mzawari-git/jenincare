import numpy as np
from sklearn.ensemble import RandomForestRegressor
import joblib
import os

MODEL_PATH = os.path.join(os.path.dirname(__file__), 'ltv_model.joblib')

class LTVModel:
    def __init__(self):
        self.model = None
        self._load_or_init()

    def _load_or_init(self):
        if os.path.exists(MODEL_PATH):
            self.model = joblib.load(MODEL_PATH)
        else:
            self.model = RandomForestRegressor(
                n_estimators=100,
                max_depth=10,
                random_state=42,
                n_jobs=-1,
            )

    def predict(self, features: np.ndarray) -> dict:
        if self.model is None:
            return {'ltv_30d': 0, 'ltv_90d': 0, 'ltv_365d': 0, 'segment': 'unknown'}

        ltv_30d = float(self.model.predict(features)[0])
        ltv_90d = ltv_30d * 2.5
        ltv_365d = ltv_30d * 6.0

        if ltv_30d > 500:
            segment = 'b2b'
        elif ltv_30d > 100:
            segment = 'b2c'
        else:
            segment = 'one_time'

        return {
            'ltv_30d': round(ltv_30d, 2),
            'ltv_90d': round(ltv_90d, 2),
            'ltv_365d': round(ltv_365d, 2),
            'segment': segment,
        }

    def train(self, X: np.ndarray, y: np.ndarray):
        self.model.fit(X, y)
        joblib.dump(self.model, MODEL_PATH)
