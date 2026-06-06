package com.jenincare.skinanalyzer.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = false)
data class UnlockRequest(
    @Json(name = "pin_code") val pinCode: String
)
