package com.jenincare.skinanalyzer.ui.report

import androidx.compose.runtime.Composable
import androidx.compose.ui.tooling.preview.Preview
import com.jenincare.skinanalyzer.ui.theme.SkinAnalyzerTheme

@Preview(showBackground = true, backgroundColor = 0xFF0D1117)
@Composable
fun ScoreGaugePreview() {
    SkinAnalyzerTheme {
        ScoreGauge(score = 85)
    }
}
