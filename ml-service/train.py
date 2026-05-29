import pandas as pd
import numpy as np
import sys
import json
from model import LTVModel

def generate_synthetic_data(n_samples=1000):
    np.random.seed(42)

    aov = np.random.lognormal(mean=4.5, sigma=0.8, size=n_samples)
    aov = np.clip(aov, 10, 5000)

    category_encoded = np.random.randint(0, 5, size=n_samples).astype(float)

    cod_ratio = np.random.uniform(0, 1, size=n_samples)

    location_encoded = np.random.randint(0, 10, size=n_samples).astype(float)

    device_encoded = np.random.randint(0, 3, size=n_samples).astype(float)

    day_of_week = np.random.randint(0, 7, size=n_samples).astype(float)

    month = np.random.randint(1, 13, size=n_samples).astype(float)

    channel_encoded = np.random.randint(0, 5, size=n_samples).astype(float)

    X = np.column_stack([
        aov, category_encoded, cod_ratio, location_encoded,
        device_encoded, day_of_week, month, channel_encoded,
    ])

    y = (aov * np.random.uniform(0.5, 3, size=n_samples)) + \
        np.random.normal(0, 50, size=n_samples)
    y = np.clip(y, 0, 10000)

    return X, y

if __name__ == '__main__':
    n = int(sys.argv[1]) if len(sys.argv) > 1 else 1000

    model = LTVModel()
    X, y = generate_synthetic_data(n)
    model.train(X, y)

    result = json.dumps({
        'status': 'trained',
        'samples': n,
        'mean_ltv': float(np.mean(y)),
    })
    print(result)
