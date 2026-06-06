package com.jenincare.skinanalyzer.util

import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import java.io.File
import java.io.FileInputStream
import java.io.FileOutputStream
import java.security.KeyStore
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class SecurityUtil @Inject constructor() {

    companion object {
        private const val KEY_ALIAS = "skin_analyzer_encryption_key"
        private const val ANDROID_KEYSTORE = "AndroidKeyStore"
        private const val TRANSFORMATION = "AES/GCM/NoPadding"
        private const val GCM_TAG_LENGTH = 128
        private const val GCM_IV_LENGTH = 12
    }

    private val secretKey: SecretKey by lazy {
        getOrCreateKey()
    }

    fun encryptFile(inputFile: File): File {
        val cipher = Cipher.getInstance(TRANSFORMATION)
        cipher.init(Cipher.ENCRYPT_MODE, secretKey)

        val iv = cipher.iv
        val encryptedBytes = cipher.doFinal(inputFile.readBytes())

        val encryptedFile = File.createTempFile("encrypted_", ".skin", inputFile.parentFile)
        FileOutputStream(encryptedFile).use { outputStream ->
            outputStream.write(iv)
            outputStream.write(encryptedBytes)
        }

        return encryptedFile
    }

    fun decryptFile(encryptedFile: File): File {
        val fileBytes = encryptedFile.readBytes()
        val iv = fileBytes.copyOfRange(0, GCM_IV_LENGTH)
        val encryptedData = fileBytes.copyOfRange(GCM_IV_LENGTH, fileBytes.size)

        val cipher = Cipher.getInstance(TRANSFORMATION)
        val spec = GCMParameterSpec(GCM_TAG_LENGTH, iv)
        cipher.init(Cipher.DECRYPT_MODE, secretKey, spec)

        val decryptedBytes = cipher.doFinal(encryptedData)

        val decryptedFile = File.createTempFile("decrypted_", ".jpg", encryptedFile.parentFile)
        FileOutputStream(decryptedFile).use { outputStream ->
            outputStream.write(decryptedBytes)
        }

        return decryptedFile
    }

    fun encryptBytes(data: ByteArray): ByteArray {
        val cipher = Cipher.getInstance(TRANSFORMATION)
        cipher.init(Cipher.ENCRYPT_MODE, secretKey)

        val iv = cipher.iv
        val encryptedBytes = cipher.doFinal(data)

        return iv + encryptedBytes
    }

    fun decryptBytes(encryptedData: ByteArray): ByteArray {
        val iv = encryptedData.copyOfRange(0, GCM_IV_LENGTH)
        val actualEncryptedData = encryptedData.copyOfRange(GCM_IV_LENGTH, encryptedData.size)

        val cipher = Cipher.getInstance(TRANSFORMATION)
        val spec = GCMParameterSpec(GCM_TAG_LENGTH, iv)
        cipher.init(Cipher.DECRYPT_MODE, secretKey, spec)

        return cipher.doFinal(actualEncryptedData)
    }

    private fun getOrCreateKey(): SecretKey {
        val keyStore = KeyStore.getInstance(ANDROID_KEYSTORE)
        keyStore.load(null)

        val existingKey = keyStore.getEntry(KEY_ALIAS, null) as? KeyStore.SecretKeyEntry
        if (existingKey != null) {
            return existingKey.secretKey
        }

        val keyGenerator = KeyGenerator.getInstance(
            KeyProperties.KEY_ALGORITHM_AES,
            ANDROID_KEYSTORE
        )

        val spec = KeyGenParameterSpec.Builder(
            KEY_ALIAS,
            KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT
        )
            .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
            .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
            .setKeySize(256)
            .build()

        keyGenerator.init(spec)
        return keyGenerator.generateKey()
    }
}
