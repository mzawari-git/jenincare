package com.ebtikar.skinanalyzer.util

import android.content.Context
import android.speech.tts.TextToSpeech
import android.speech.tts.UtteranceProgressListener
import dagger.hilt.android.qualifiers.ApplicationContext
import timber.log.Timber
import java.util.Locale
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class VoiceGuideManager @Inject constructor(
    @ApplicationContext private val context: Context
) {
    private var tts: TextToSpeech? = null
    @Volatile private var isReady = false
    private var isEnabled = true

    private val onInitListener = TextToSpeech.OnInitListener { status ->
        if (status == TextToSpeech.SUCCESS) {
            val result = tts?.setLanguage(Locale("ar"))
            if (result == TextToSpeech.LANG_MISSING_DATA || result == TextToSpeech.LANG_NOT_SUPPORTED) {
                Timber.w("Arabic TTS not available, falling back to default locale")
                tts?.language = Locale.getDefault()
            }
            isReady = true
            tts?.setOnUtteranceProgressListener(object : UtteranceProgressListener() {
                override fun onStart(utteranceId: String?) {}
                override fun onDone(utteranceId: String?) {}
                override fun onError(utteranceId: String?) {
                    Timber.w("TTS utterance failed: $utteranceId")
                }
            })
            Timber.i("VoiceGuide TTS initialized successfully")
        } else {
            Timber.e("VoiceGuide TTS initialization failed with status: $status")
        }
    }

    fun initialize() {
        if (tts == null) {
            tts = TextToSpeech(context, onInitListener)
        }
    }

    fun setEnabled(enabled: Boolean) {
        isEnabled = enabled
        if (!enabled) {
            tts?.stop()
        }
    }

    fun isEnabled(): Boolean = isEnabled

    fun speak(text: String, utteranceId: String = System.currentTimeMillis().toString()) {
        if (!isEnabled || !isReady || tts == null) return
        tts?.speak(text, TextToSpeech.QUEUE_ADD, null, utteranceId)
    }

    fun speakSpectrumActivation(spectrumNameAr: String) {
        speak("جاري تفعيل $spectrumNameAr")
    }

    fun speakCaptureReady() {
        speak("ثبّت الإضاءة — جاري التقاط الصورة")
    }

    fun speakCaptureComplete(spectrumNameAr: String) {
        speak("تم — $spectrumNameAr")
    }

    fun speakFaceDetected() {
        speak("تم التحقق من وضع الوجه")
    }

    fun speakAnalysisComplete() {
        speak("اكتمل التحليل — جاري عرض النتائج")
    }

    fun speakPositionGuide(message: String) {
        speak(message)
    }

    fun shutdown() {
        tts?.stop()
        tts?.shutdown()
        tts = null
        isReady = false
        Timber.i("VoiceGuide TTS shut down")
    }
}
