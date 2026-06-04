from fastapi import FastAPI
from pydantic import BaseModel, Field
from typing import Optional
import numpy as np
from model import LTVModel

app = FastAPI(title="شركة جنين للتجميل LTV Prediction Service", version="1.0.0")
model = LTVModel()


class Features(BaseModel):
    aov: float = Field(..., description="Average order value")
    category_encoded: int = Field(0, ge=0, le=10, description="Product category encoding")
    cod_ratio: float = Field(0.0, ge=0, le=1, description="COD payment ratio")
    location_encoded: int = Field(0, ge=0, le=50, description="Location encoding")
    device_encoded: int = Field(0, ge=0, le=5, description="Device type encoding")
    day_of_week: int = Field(0, ge=0, le=6, description="Day of week (0=Mon)")
    month: int = Field(1, ge=1, le=12, description="Month")
    channel_encoded: int = Field(0, ge=0, le=10, description="Marketing channel encoding")


class TrainData(BaseModel):
    samples: list[list[float]]
    targets: list[float]


@app.get("/health")
def health():
    return {"status": "ok", "service": "ltv-prediction"}


@app.post("/predict-ltv")
def predict_ltv(features: Features):
    X = np.array([[
        features.aov,
        float(features.category_encoded),
        features.cod_ratio,
        float(features.location_encoded),
        float(features.device_encoded),
        float(features.day_of_week),
        float(features.month),
        float(features.channel_encoded),
    ]])

    result = model.predict(X)
    return {"success": True, "data": result}


@app.post("/train")
def train_model(data: TrainData):
    X = np.array(data.samples)
    y = np.array(data.targets)
    model.train(X, y)
    return {"success": True, "message": f"Model trained on {len(y)} samples"}


@app.get("/model-info")
def model_info():
    n_estimators = model.model.n_estimators if model.model else 0
    return {
        "model_type": type(model.model).__name__,
        "n_estimators": n_estimators,
        "is_trained": model.model is not None,
    }
