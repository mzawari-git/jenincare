package com.ebtikar.skinanalyzer.ui.splash

import android.animation.AnimatorSet
import android.animation.ObjectAnimator
import android.annotation.SuppressLint
import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.animation.DecelerateInterpolator
import android.view.animation.OvershootInterpolator
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import com.ebtikar.skinanalyzer.databinding.ActivitySplashBinding
import com.ebtikar.skinanalyzer.ui.home.HomeActivity
import com.ebtikar.skinanalyzer.util.Constants

@SuppressLint("CustomSplashScreen")
class SplashActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySplashBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivitySplashBinding.inflate(layoutInflater)
        setContentView(binding.root)

        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.updatePadding(
                left = systemBars.left,
                top = systemBars.top,
                right = systemBars.right,
                bottom = systemBars.bottom
            )
            insets
        }

        binding.tvDeviceName.text = Constants.DEVICE_NAME
        binding.tvDeviceModel.text = "${Constants.DEVICE_BRAND} ${Constants.DEVICE_MODEL} - ${Constants.DEVICE_EDITION}"

        startEntranceAnimation()

        Handler(Looper.getMainLooper()).postDelayed({
            startActivity(Intent(this, HomeActivity::class.java))
            finish()
            overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out)
        }, Constants.SPLASH_DELAY_MS)
    }

    private fun startEntranceAnimation() {
        val logoScaleX = ObjectAnimator.ofFloat(binding.logoCard, "scaleX", 0.5f, 1f)
        val logoScaleY = ObjectAnimator.ofFloat(binding.logoCard, "scaleY", 0.5f, 1f)
        val logoAlpha = ObjectAnimator.ofFloat(binding.logoCard, "alpha", 0f, 1f)
        
        val glowAlpha = ObjectAnimator.ofFloat(binding.glowRing, "alpha", 0f, 0.6f)
        val glowPulse = ObjectAnimator.ofFloat(binding.glowRing, "alpha", 0.6f, 0.3f, 0.6f)
        glowPulse.repeatCount = ObjectAnimator.INFINITE
        glowPulse.duration = 2000

        val contentAlpha = ObjectAnimator.ofFloat(binding.splashContent, "alpha", 0f, 1f)
        val contentTranslateY = ObjectAnimator.ofFloat(binding.splashContent, "translationY", 50f, 0f)

        val titleAlpha = ObjectAnimator.ofFloat(binding.tvSplashTitle, "alpha", 0f, 1f)
        val titleTranslateY = ObjectAnimator.ofFloat(binding.tvSplashTitle, "translationY", 30f, 0f)

        val taglineAlpha = ObjectAnimator.ofFloat(binding.tvSplashTagline, "alpha", 0f, 1f)
        val taglineTranslateY = ObjectAnimator.ofFloat(binding.tvSplashTagline, "translationY", 20f, 0f)

        val featuresAlpha = ObjectAnimator.ofFloat(binding.splashFeatures, "alpha", 0f, 1f)
        val featuresTranslateY = ObjectAnimator.ofFloat(binding.splashFeatures, "translationY", 20f, 0f)

        val progressAlpha = ObjectAnimator.ofFloat(binding.progressSplash, "alpha", 0f, 1f)
        val statusAlpha = ObjectAnimator.ofFloat(binding.tvSplashStatus, "alpha", 0f, 1f)

        val footerAlpha = ObjectAnimator.ofFloat(binding.splashFooter, "alpha", 0f, 1f)
        val versionAlpha = ObjectAnimator.ofFloat(binding.tvVersion, "alpha", 0f, 1f)

        val logoSet = AnimatorSet().apply {
            playTogether(logoScaleX, logoScaleY, logoAlpha)
            duration = 600
            interpolator = OvershootInterpolator(1.5f)
        }

        val contentSet = AnimatorSet().apply {
            playTogether(contentAlpha, contentTranslateY)
            duration = 500
            interpolator = DecelerateInterpolator()
        }

        val titleSet = AnimatorSet().apply {
            playTogether(titleAlpha, titleTranslateY)
            duration = 400
            interpolator = DecelerateInterpolator()
        }

        val taglineSet = AnimatorSet().apply {
            playTogether(taglineAlpha, taglineTranslateY)
            duration = 400
            interpolator = DecelerateInterpolator()
        }

        val featuresSet = AnimatorSet().apply {
            playTogether(featuresAlpha, featuresTranslateY)
            duration = 400
            interpolator = DecelerateInterpolator()
        }

        val progressSet = AnimatorSet().apply {
            playTogether(progressAlpha, statusAlpha)
            duration = 300
            interpolator = DecelerateInterpolator()
        }

        val footerSet = AnimatorSet().apply {
            playTogether(footerAlpha, versionAlpha)
            duration = 400
            interpolator = DecelerateInterpolator()
        }

        val mainSet = AnimatorSet().apply {
            play(logoSet)
            play(glowAlpha).with(logoSet)
            play(contentSet).after(200)
            play(titleSet).after(400)
            play(taglineSet).after(550)
            play(featuresSet).after(700)
            play(progressSet).after(900)
            play(footerSet).after(600)
            play(glowPulse).after(800)
            start()
        }
    }
}
