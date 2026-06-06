package com.jenincare.skinanalyzer.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = false)
data class AppUpdateResponse(
    @Json(name = "latest_version") val latestVersion: String?,
    @Json(name = "version_code") val versionCode: Int?,
    @Json(name = "download_url") val downloadUrl: String?,
    @Json(name = "release_notes") val releaseNotes: String?,
    @Json(name = "force_update") val forceUpdate: Boolean,
    @Json(name = "update_available") val updateAvailable: Boolean
)
