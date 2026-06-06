package com.jenincare.skinanalyzer.util

sealed class Result<out T> {
    data class Success<out T>(val data: T) : Result<T>()
    data class Error(val exception: Throwable) : Result<Nothing>()
    data class Loading(val progress: Float = 0f) : Result<Nothing>()
}
