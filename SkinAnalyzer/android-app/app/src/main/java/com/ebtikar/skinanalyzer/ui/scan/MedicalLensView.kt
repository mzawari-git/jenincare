package com.ebtikar.skinanalyzer.ui.scan

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.RectF
import android.graphics.SweepGradient
import android.util.AttributeSet
import android.view.View
import android.view.animation.LinearInterpolator
import kotlin.math.min

class MedicalLensView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0
) : View(context, attrs, defStyleAttr) {

    private var lensCx = 0f
    private var lensCy = 0f
    private var lensRadius = 0f
    private var ring1Angle = 0f
    private var ring2Angle = 0f
    private var ring3Angle = 0f
    private var scanSweep = 0f
    private var corePulse = 0f
    private var progress = 0

    private val outerRingPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }
    private val innerRingPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }
    private val glassPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val corePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val sweepPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.STROKE
        strokeCap = Paint.Cap.ROUND
    }
    private val glarePaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        style = Paint.Style.FILL
    }
    private val progressPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
        isFakeBoldText = true
    }
    private val labelPaint = Paint(Paint.ANTI_ALIAS_FLAG).apply {
        textAlign = Paint.Align.CENTER
    }
    private val animators = mutableListOf<ValueAnimator>()

    init {
        startAnimations()
    }

    private fun startAnimations() {
        animators.clear()
        listOf(
            ValueAnimator.ofFloat(0f, 360f).apply {
                duration = 4000L; repeatCount = ValueAnimator.INFINITE; interpolator = LinearInterpolator()
                addUpdateListener { ring1Angle = it.animatedValue as Float; invalidate() }
            },
            ValueAnimator.ofFloat(360f, 0f).apply {
                duration = 6000L; repeatCount = ValueAnimator.INFINITE; interpolator = LinearInterpolator()
                addUpdateListener { ring2Angle = it.animatedValue as Float; invalidate() }
            },
            ValueAnimator.ofFloat(0f, 360f).apply {
                duration = 3000L; repeatCount = ValueAnimator.INFINITE; interpolator = LinearInterpolator()
                addUpdateListener { ring3Angle = it.animatedValue as Float; invalidate() }
            },
            ValueAnimator.ofFloat(0f, 360f).apply {
                duration = 2500L; repeatCount = ValueAnimator.INFINITE; interpolator = LinearInterpolator()
                addUpdateListener { scanSweep = it.animatedValue as Float; invalidate() }
            },
            ValueAnimator.ofFloat(0.8f, 1.2f).apply {
                duration = 2000L; repeatCount = ValueAnimator.INFINITE; interpolator = LinearInterpolator()
                addUpdateListener { corePulse = it.animatedValue as Float; invalidate() }
            }
        ).forEach { animators.add(it); it.start() }
    }

    override fun onDetachedFromWindow() {
        super.onDetachedFromWindow()
        animators.forEach { it.cancel() }
        animators.clear()
    }

    override fun onSizeChanged(w: Int, h: Int, oldw: Int, oldh: Int) {
        lensCx = w / 2f
        lensCy = h / 2f
        lensRadius = min(w, h) * 0.12f
    }

    fun setProgress(pct: Int) {
        progress = pct.coerceIn(0, 100)
        invalidate()
    }

    override fun onDraw(canvas: Canvas) {
        val density = resources.displayMetrics.density
        val r = lensRadius

        // Outer metal ring
        val outerR = r * 1.0f
        outerRingPaint.strokeWidth = 6f * density
        outerRingPaint.color = Color.parseColor("#FFD4AF37")
        outerRingPaint.alpha = 200
        canvas.drawCircle(lensCx, lensCy, outerR, outerRingPaint)

        // Ring 1 - rotating gold dots
        val r1 = r * 0.92f
        outerRingPaint.strokeWidth = 2f * density
        outerRingPaint.color = Color.parseColor("#66D4AF37")
        outerRingPaint.alpha = 120
        val r1Rect = RectF(lensCx - r1, lensCy - r1, lensCx + r1, lensCy + r1)
        canvas.drawArc(r1Rect, ring1Angle, 270f, false, outerRingPaint)

        // Ring 2 - rotating cyan dashes
        val r2 = r * 0.84f
        innerRingPaint.strokeWidth = 3f * density
        innerRingPaint.color = Color.parseColor("#66FFFFFF")
        innerRingPaint.alpha = 100
        val r2Rect = RectF(lensCx - r2, lensCy - r2, lensCx + r2, lensCy + r2)
        canvas.drawArc(r2Rect, ring2Angle, 180f, false, innerRingPaint)

        // Glass inner
        val glassR = r * 0.78f
        glassPaint.color = Color.parseColor("#1AFFFFFF")
        canvas.drawCircle(lensCx, lensCy, glassR, glassPaint)

        // Glass border
        innerRingPaint.strokeWidth = 1f * density
        innerRingPaint.color = Color.parseColor("#33FFFFFF")
        canvas.drawCircle(lensCx, lensCy, glassR, innerRingPaint)

        // Green AI Core
        val coreR = r * 0.28f * corePulse
        corePaint.color = Color.parseColor("#FF52B788")
        corePaint.alpha = 180
        canvas.drawCircle(lensCx, lensCy, coreR, corePaint)

        // Core glow
        corePaint.color = Color.parseColor("#3352B788")
        canvas.drawCircle(lensCx, lensCy, coreR * 1.8f, corePaint)

        // Scanning sweep ring
        val sweepR = r * 0.68f
        sweepPaint.strokeWidth = 2f * density
        sweepPaint.color = Color.parseColor("#4DD4AF37")
        sweepPaint.alpha = 150
        val sweepRect = RectF(lensCx - sweepR, lensCy - sweepR, lensCx + sweepR, lensCy + sweepR)
        canvas.drawArc(sweepRect, scanSweep - 10f, 20f, false, sweepPaint)

        // Inner ring 3 - pulse dots
        val r3 = r * 0.55f
        innerRingPaint.strokeWidth = 2f * density
        innerRingPaint.color = Color.parseColor("#4D00D4FF")
        innerRingPaint.alpha = 100
        val r3Rect = RectF(lensCx - r3, lensCy - r3, lensCx + r3, lensCy + r3)
        canvas.drawArc(r3Rect, ring3Angle, 120f, false, innerRingPaint)

        // Glare reflection
        glarePaint.color = Color.parseColor("#1AFFFFFF")
        canvas.drawCircle(lensCx - r * 0.2f, lensCy - r * 0.25f, r * 0.3f, glarePaint)
        glarePaint.color = Color.parseColor("#0DFFFFFF")
        canvas.drawCircle(lensCx - r * 0.15f, lensCy - r * 0.2f, r * 0.15f, glarePaint)

        // Sweep gradient ring
        if (progress > 0) {
            val sweepGrad = SweepGradient(lensCx, lensCy,
                intArrayOf(Color.parseColor("#33D4AF37"), Color.parseColor("#FFD4AF37"), Color.parseColor("#33D4AF37")),
                floatArrayOf(0f, 0.5f, 1f))
            val pg = Paint(Paint.ANTI_ALIAS_FLAG).apply {
                style = Paint.Style.STROKE
                strokeWidth = 3f * density
                strokeCap = Paint.Cap.ROUND
                shader = sweepGrad
            }
            val pRect = RectF(lensCx - r * 0.95f, lensCy - r * 0.95f, lensCx + r * 0.95f, lensCy + r * 0.95f)
            canvas.drawArc(pRect, -90f, progress * 3.6f, false, pg)
        }

        // Progress text below
        progressPaint.textSize = 24f * density
        progressPaint.color = Color.parseColor("#FFD4AF37")
        canvas.drawText("$progress%", lensCx, lensCy + r + 40f * density, progressPaint)

        labelPaint.textSize = 12f * density
        labelPaint.color = Color.parseColor("#88FFFFFF")
        canvas.drawText("AI SCANNING", lensCx, lensCy + r + 60f * density, labelPaint)
    }
}
