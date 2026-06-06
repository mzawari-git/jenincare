package com.ebtikar.skinanalyzer.ai

import android.content.Context
import org.tensorflow.lite.Interpreter
import org.tensorflow.lite.gpu.GpuDelegate
import org.tensorflow.lite.nnapi.NnApiDelegate
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

    private var interpreter: Interpreter? = null
    private var gpuDelegate: GpuDelegate? = null
    private var nnApiDelegate: NnApiDelegate? = null
    private var isInitialized = false
    private var activeDelegate: String = "none"

    data class ModelConfig(
        val modelPath: String = "models/skin_segmentation_quantized.tflite",
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

            if (config.useGpuDelegate) {
                try {
                    gpuDelegate = GpuDelegate()
                    options.addDelegate(gpuDelegate)
                    activeDelegate = "GPU"
                    Timber.i("GPU delegate enabled for TFLite")
                } catch (e: Exception) {
                    Timber.w(e, "GPU delegate unavailable")
                    gpuDelegate = null
                }
            }

            if (gpuDelegate == null && config.useNnApiDelegate) {
                try {
                    nnApiDelegate = NnApiDelegate()
                    options.addDelegate(nnApiDelegate)
                    activeDelegate = "NNAPI"
                    Timber.i("NNAPI delegate enabled for TFLite")
                } catch (e: Exception) {
                    Timber.w(e, "NNAPI delegate unavailable")
                    nnApiDelegate = null
                }
            }

            if (gpuDelegate == null && nnApiDelegate == null && !config.fallbackToCpu) {
                return Result.failure(IllegalStateException("No hardware accelerator available"))
            }

            if (gpuDelegate == null && nnApiDelegate == null) {
                activeDelegate = "CPU"
                Timber.i("Falling back to CPU for TFLite")
            }

            interpreter = Interpreter(modelFile, options)
            isInitialized = true

            Timber.i("TFLite engine initialized: ${config.modelPath} (delegate: $activeDelegate)")
            Timber.i("Input shape: [1, ${config.inputHeight}, ${config.inputWidth}, ${config.inputChannels}]")
            Result.success(Unit)
        } catch (e: Exception) {
            Timber.e(e, "Failed to initialize TFLite engine")
            Result.failure(e)
        }
    }

    fun getActiveDelegate(): String = activeDelegate

    fun isInitialized(): Boolean = isInitialized

    fun runInference(inputBuffer: ByteBuffer, outputBuffer: ByteBuffer) {
        val interp = interpreter ?: throw IllegalStateException("TFLite engine not initialized")
        interp.run(inputBuffer, outputBuffer)
    }

    fun runInferenceMultiple(inputs: Array<Any>, outputs: Map<Int, Any>) {
        val interp = interpreter ?: throw IllegalStateException("TFLite engine not initialized")
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
        gpuDelegate?.close()
        gpuDelegate = null
        nnApiDelegate?.close()
        nnApiDelegate = null
        interpreter?.close()
        interpreter = null
        isInitialized = false
        activeDelegate = "none"
        Timber.i("TFLite engine shut down")
    }

    private fun loadModelFile(modelPath: String): MappedByteBuffer {
        val assetFileDescriptor = context.assets.openFd(modelPath)
        val fileInputStream = FileInputStream(assetFileDescriptor.fileDescriptor)
        val channel = fileInputStream.channel
        return channel.map(FileChannel.MapMode.READ_ONLY, assetFileDescriptor.startOffset, assetFileDescriptor.declaredLength)
    }
}
