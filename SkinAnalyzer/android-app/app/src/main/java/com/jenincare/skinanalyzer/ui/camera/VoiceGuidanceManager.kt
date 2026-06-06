package com.jenincare.skinanalyzer.ui.camera

import android.content.Context
import android.speech.tts.TextToSpeech
import android.speech.tts.UtteranceProgressListener
import java.util.Locale

enum class PoseType {
    CENTER,
    TOO_CLOSE,
    TOO_FAR,
    TOO_LEFT,
    TOO_RIGHT,
    TOO_HIGH,
    TOO_LOW,
    TILTED,
    PERFECT
}

class VoiceGuidanceManager(private val context: Context) {
    private var tts: TextToSpeech? = null
    private var isInitialized = false
    private var lastInstruction: String? = null
    private var lastInstructionTime: Long = 0
    private val minInstructionInterval = 2000L

    private val instructions = mapOf(
        PoseType.CENTER to "من فضلك ضع وجهك في منتصف الإطار",
        PoseType.TOO_CLOSE to "أنت قريب جداً من الكاميرا. ابتعد قليلاً",
        PoseType.TOO_FAR to "أنت بعيد جداً عن الكاميرا. اقترب قليلاً",
        PoseType.TOO_LEFT to "تحرك قليلاً إلى اليمين",
        PoseType.TOO_RIGHT to "تحرك قليلاً إلى اليسار",
        PoseType.TOO_HIGH to "اخفض وجهك قليلاً",
        PoseType.TOO_LOW to "ارفع وجهك قليلاً",
        PoseType.TILTED to "من فضلك اجعل وجهك مستقيماً",
        PoseType.PERFECT to "ممتاز! اثبت على هذا الوضع"
    )

    init {
        initializeTTS()
    }

    private fun initializeTTS() {
        tts = TextToSpeech(context) { status ->
            if (status == TextToSpeech.SUCCESS) {
                val result = tts?.setLanguage(Locale("ar"))
                if (result == TextToSpeech.LANG_MISSING_DATA || result == TextToSpeech.LANG_NOT_SUPPORTED) {
                    tts?.setLanguage(Locale.forLanguageTag("ar-SA"))
                }
                isInitialized = true

                tts?.setOnUtteranceProgressListener(object : UtteranceProgressListener() {
                    override fun onStart(utteranceId: String?) {}
                    override fun onDone(utteranceId: String?) {}
                    @Deprecated("Deprecated in Java")
                    override fun onError(utteranceId: String?) {}
                })
            }
        }
    }

    fun speak(instruction: String) {
        if (!isInitialized || tts == null) return

        val now = System.currentTimeMillis()
        if (instruction == lastInstruction && (now - lastInstructionTime) < minInstructionInterval) {
            return
        }

        lastInstruction = instruction
        lastInstructionTime = now

        tts?.speak(
            instruction,
            TextToSpeech.QUEUE_FLUSH,
            null,
            "skin_analyzer_guidance_$now"
        )
    }

    fun playGuidanceForPose(poseType: PoseType) {
        instructions[poseType]?.let { speak(it) }
    }

    fun determinePose(
        faceCenterX: Float,
        faceCenterY: Float,
        faceWidth: Float,
        faceHeight: Float,
        roll: Float
    ): PoseType {
        val idealCX = 0.5f
        val idealCY = 0.45f
        val idealSize = 0.35f
        val tolerance = 0.12f

        val faceSize = (faceWidth + faceHeight) / 2f

        if (kotlin.math.abs(roll) > 25f) return PoseType.TILTED
        if (faceSize > idealSize + 0.20f) return PoseType.TOO_CLOSE
        if (faceSize < idealSize - 0.30f) return PoseType.TOO_FAR
        if (faceCenterX < idealCX - tolerance) return PoseType.TOO_LEFT
        if (faceCenterX > idealCX + tolerance) return PoseType.TOO_RIGHT
        if (faceCenterY < idealCY - tolerance) return PoseType.TOO_HIGH
        if (faceCenterY > idealCY + tolerance) return PoseType.TOO_LOW

        if (kotlin.math.abs(faceCenterX - idealCX) < 0.08f &&
            kotlin.math.abs(faceCenterY - idealCY) < 0.08f &&
            kotlin.math.abs(faceSize - idealSize) < 0.15f &&
            kotlin.math.abs(roll) < 10f
        ) {
            return PoseType.PERFECT
        }

        return PoseType.CENTER
    }

    fun release() {
        tts?.stop()
        tts?.shutdown()
        tts = null
        isInitialized = false
    }
}
