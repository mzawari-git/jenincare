package com.ebtikar.skinanalyzer.ai

import android.content.Context
import org.tensorflow.lite.Delegate
import org.tensorflow.lite.Interpreter
import timber.log.Timber
import java.io.FileInputStream
import java.nio.ByteBuffer
import java.nio.ByteOrder
import java.nio.MappedByteBuffer
import java.nio.channels.FileChannel
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class TFLiteEngine @Inject constructor(
    private val context: Context
) {

    @Volatile private var interpreter: Interpreter? = null
    @Volatile private var isInitialized = false
    @Volatile private var activeDelegate: String = "none"

    data class ModelConfig(
        val modelPath: String = "models/yyface-detect.tflite",
        val useGpuDelegate: Boolean = true,
        val useNnApiDelegate: Boolean = true,
        val fallbackToCpu: Boolean = true,
        val numThreads: Int = 4,
        val inputWidth: Int = 512,
        val inputHeight: Int = 512,
        val inputChannels: Int = 3
    )

    fun initialize(config: ModelConfig = ModelConfig()): Result<Unit> {
        return try {
            val modelFile = loadModelFile(config.modelPath)

            val options = Interpreter.Options().apply {
                setNumThreads(config.numThreads)
            }

            var delegateLoaded = false

            if (config.useGpuDelegate) {
                try {
                    val gpuDelegateClass = Class.forName("org.tensorflow.lite.gpu.GpuDelegate")
                    val gpuDelegate = gpuDelegateClass.getDeclaredConstructor().newInstance()
                    options.addDelegate(gpuDelegate as Delegate)
                    activeDelegate = "GPU"
                    delegateLoaded = true
                    Timber.i("GPU delegate enabled for TFLite")
                } catch (e: Throwable) {
                    Timber.w("GPU delegate unavailable: ${e.message}")
                }
            }

            if (!delegateLoaded && config.useNnApiDelegate) {
                try {
                    val nnApiDelegateClass = Class.forName("org.tensorflow.lite.nnapi.NnApiDelegate")
                    val nnApiDelegate = nnApiDelegateClass.getDeclaredConstructor().newInstance()
                    options.addDelegate(nnApiDelegate as Delegate)
                    activeDelegate = "NNAPI"
                    delegateLoaded = true
                    Timber.i("NNAPI delegate enabled for TFLite")
                } catch (e: Throwable) {
                    Timber.w("NNAPI delegate unavailable: ${e.message}")
                }
            }

            if (!delegateLoaded) {
                activeDelegate = "CPU"
                Timber.i("Falling back to CPU for TFLite")
            }

            interpreter = Interpreter(modelFile, options)
            isInitialized = true

            Timber.i("TFLite engine initialized: ${config.modelPath} (delegate: $activeDelegate)")
            Timber.i("Input shape: [1, ${config.inputHeight}, ${config.inputWidth}, ${config.inputChannels}]")
            Result.success(Unit)
        } catch (e: Throwable) {
            Timber.e(e, "Failed to initialize TFLite engine")
            Result.failure(Exception(e.message, e))
        }
    }

    fun getActiveDelegate(): String = activeDelegate

    fun isInitialized(): Boolean = isInitialized

    fun runInference(inputBuffer: ByteBuffer, outputBuffer: ByteBuffer) {
        val interp = interpreter ?: run {
            Timber.e("TFLite runInference called but engine not initialized")
            return
        }
        interp.run(inputBuffer, outputBuffer)
    }

    fun runInferenceMultiple(inputs: Array<Any>, outputs: Map<Int, Any>) {
        val interp = interpreter ?: run {
            Timber.e("TFLite runInferenceMultiple called but engine not initialized")
            return
        }
        interp.runForMultipleInputsOutputs(inputs, outputs)
    }

    fun createInputBuffer(config: ModelConfig = ModelConfig()): ByteBuffer {
        return ByteBuffer.allocateDirect(
            1 * config.inputHeight * config.inputWidth * config.inputChannels * 4
        ).apply {
            order(ByteOrder.nativeOrder())
        }
    }

    fun createOutputBuffer(numClasses: Int, config: ModelConfig = ModelConfig()): ByteBuffer {
        return ByteBuffer.allocateDirect(
            1 * config.inputHeight * config.inputWidth * numClasses * 4
        ).apply {
            order(ByteOrder.nativeOrder())
        }
    }

    fun shutdown() {
        interpreter?.close()
        interpreter = null
        isInitialized = false
        activeDelegate = "none"
        Timber.i("TFLite engine shut down")
    }

    private fun loadModelFile(modelPath: String): MappedByteBuffer {
        val assetFileDescriptor = context.assets.openFd(modelPath)
        assetFileDescriptor.use { afd ->
            val fileInputStream = FileInputStream(afd.fileDescriptor)
            fileInputStream.use { fis ->
                val channel = fis.channel
                return channel.map(FileChannel.MapMode.READ_ONLY, afd.startOffset, afd.declaredLength)
            }
        }
    }
}
