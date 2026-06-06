package com.jenincare.skinanalyzer.ui.settings

import android.content.Intent
import android.net.Uri
import android.widget.Toast
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ExitToApp
import androidx.compose.material.icons.filled.Brightness6
import androidx.compose.material.icons.filled.Cached
import androidx.compose.material.icons.filled.DarkMode
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Dns
import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Language
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Policy
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Switch
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.jenincare.skinanalyzer.BuildConfig
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicBodyMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicLabelLarge
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleMedium
import com.jenincare.skinanalyzer.ui.theme.ArabicTitleSmall
import com.jenincare.skinanalyzer.ui.theme.JeninBlue

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SettingsScreen(
    onNavigateBack: () -> Unit,
    onLogout: () -> Unit,
    viewModel: SettingsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    val context = LocalContext.current
    var isDarkTheme by remember { mutableStateOf(false) }
    var showLanguageDialog by remember { mutableStateOf(false) }
    var showServerDialog by remember { mutableStateOf(false) }
    var serverUrl by remember { mutableStateOf(viewModel.getServerUrl()) }
    var selectedLanguage by remember { mutableStateOf("ar") }
    var showLogoutDialog by remember { mutableStateOf(false) }
    var showAboutDialog by remember { mutableStateOf(false) }
    var showClearCacheDialog by remember { mutableStateOf(false) }

    LaunchedEffect(state.logoutComplete) {
        if (state.logoutComplete) {
            onLogout()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("الإعدادات", style = ArabicTitleMedium) },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "رجوع")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.primaryContainer,
                    titleContentColor = MaterialTheme.colorScheme.onPrimaryContainer
                )
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .verticalScroll(rememberScrollState())
                .background(MaterialTheme.colorScheme.background)
        ) {
            Spacer(modifier = Modifier.height(8.dp))

            // User Profile Card
            if (!state.isLoading && state.userProfile != null) {
                val user = state.userProfile!!
                Card(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 8.dp),
                    shape = RoundedCornerShape(16.dp),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.surface
                    ),
                    elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
                ) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(16.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Box(
                            modifier = Modifier
                                .size(52.dp)
                                .clip(CircleShape)
                                .background(JeninBlue.copy(alpha = 0.15f)),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(
                                Icons.Default.Person,
                                contentDescription = null,
                                tint = JeninBlue,
                                modifier = Modifier.size(28.dp)
                            )
                        }
                        Spacer(modifier = Modifier.width(14.dp))
                        Column(modifier = Modifier.weight(1f)) {
                            Text(
                                text = user.name,
                                style = ArabicBodyLarge,
                                fontWeight = FontWeight.SemiBold,
                                color = MaterialTheme.colorScheme.onSurface,
                                maxLines = 1,
                                overflow = TextOverflow.Ellipsis
                            )
                            if (!user.email.isNullOrBlank()) {
                                Text(
                                    text = user.email,
                                    style = MaterialTheme.typography.labelSmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                                    maxLines = 1,
                                    overflow = TextOverflow.Ellipsis
                                )
                            }
                            if (!user.phone.isNullOrBlank()) {
                                Text(
                                    text = user.phone,
                                    style = MaterialTheme.typography.labelSmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                                    maxLines = 1,
                                    overflow = TextOverflow.Ellipsis
                                )
                            }
                        }
                        Column(horizontalAlignment = Alignment.End) {
                            Text(
                                text = "${user.totalAnalyses}",
                                style = ArabicTitleSmall,
                                fontWeight = FontWeight.Bold,
                                color = JeninBlue
                            )
                            Text(
                                text = "تحليل",
                                style = MaterialTheme.typography.labelSmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    }
                }
            }

            if (state.isLoading) {
                LinearProgressIndicator(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp)
                )
            }

            // Language
            SettingsItem(
                icon = Icons.Default.Language,
                title = "اللغة",
                subtitle = if (selectedLanguage == "ar") "العربية" else "English",
                onClick = { showLanguageDialog = true }
            )

            HorizontalDivider(modifier = Modifier.padding(horizontal = 56.dp), color = MaterialTheme.colorScheme.outlineVariant)

            // Theme Toggle
            SettingsItem(
                icon = if (isDarkTheme) Icons.Default.DarkMode else Icons.Default.Brightness6,
                title = "المظهر",
                subtitle = if (isDarkTheme) "داكن" else "فاتح",
                trailing = {
                    Switch(
                        checked = isDarkTheme,
                        onCheckedChange = { isDarkTheme = it }
                    )
                }
            )

            HorizontalDivider(modifier = Modifier.padding(horizontal = 56.dp), color = MaterialTheme.colorScheme.outlineVariant)

            // Server URL
            SettingsItem(
                icon = Icons.Default.Dns,
                title = "رابط الخادم",
                subtitle = serverUrl,
                onClick = { showServerDialog = true }
            )

            HorizontalDivider(modifier = Modifier.padding(horizontal = 56.dp), color = MaterialTheme.colorScheme.outlineVariant)

            // Cache
            SettingsItem(
                icon = Icons.Default.Cached,
                title = "الذاكرة المؤقتة",
                subtitle = if (state.isClearingCache) "جاري المسح..." else state.cacheSize,
                trailing = {
                    if (state.isClearingCache) {
                        CircularProgressIndicator(modifier = Modifier.size(20.dp), strokeWidth = 2.dp)
                    }
                },
                onClick = { showClearCacheDialog = true }
            )

            Spacer(modifier = Modifier.height(24.dp))

            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp),
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.surface
                ),
                elevation = CardDefaults.cardElevation(defaultElevation = 1.dp)
            ) {
                Column(modifier = Modifier.padding(4.dp)) {
                    SettingsItem(
                        icon = Icons.Default.Info,
                        title = "حول التطبيق",
                        subtitle = "SkinAnalyzer v${BuildConfig.VERSION_NAME}",
                        onClick = { showAboutDialog = true }
                    )

                    HorizontalDivider(modifier = Modifier.padding(horizontal = 56.dp), color = MaterialTheme.colorScheme.outlineVariant)

                    SettingsItem(
                        icon = Icons.Default.Policy,
                        title = "سياسة الخصوصية",
                        subtitle = "اقرأ سياسة الخصوصية",
                        onClick = {
                            context.startActivity(
                                Intent(Intent.ACTION_VIEW, Uri.parse("https://jenincare.shop/privacy"))
                            )
                        }
                    )
                }
            }

            Spacer(modifier = Modifier.height(32.dp))

            // Logout
            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp)
                    .clickable(enabled = !state.isLoggingOut) { showLogoutDialog = true },
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.errorContainer
                )
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(16.dp),
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.Center
                ) {
                    if (state.isLoggingOut) {
                        CircularProgressIndicator(
                            modifier = Modifier.size(20.dp),
                            strokeWidth = 2.dp,
                            color = MaterialTheme.colorScheme.error
                        )
                    } else {
                        Icon(
                            Icons.AutoMirrored.Filled.ExitToApp,
                            contentDescription = null,
                            tint = MaterialTheme.colorScheme.error
                        )
                    }
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "تسجيل الخروج",
                        style = ArabicBodyLarge,
                        color = MaterialTheme.colorScheme.error,
                        fontWeight = FontWeight.SemiBold
                    )
                }
            }

            Spacer(modifier = Modifier.height(32.dp))
        }
    }

    // Language Dialog
    if (showLanguageDialog) {
        AlertDialog(
            onDismissRequest = { showLanguageDialog = false },
            title = { Text("اختر اللغة", style = ArabicTitleSmall) },
            text = {
                Column {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable {
                                selectedLanguage = "ar"
                                showLanguageDialog = false
                            }
                            .padding(vertical = 12.dp, horizontal = 4.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text("العربية", style = ArabicBodyLarge, modifier = Modifier.weight(1f))
                        if (selectedLanguage == "ar") {
                            Text("✓", color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.Bold)
                        }
                    }
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable {
                                selectedLanguage = "en"
                                showLanguageDialog = false
                            }
                            .padding(vertical = 12.dp, horizontal = 4.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text("English", style = ArabicBodyLarge, modifier = Modifier.weight(1f))
                        if (selectedLanguage == "en") {
                            Text("✓", color = MaterialTheme.colorScheme.primary, fontWeight = FontWeight.Bold)
                        }
                    }
                }
            },
            confirmButton = {
                TextButton(onClick = { showLanguageDialog = false }) { Text("إلغاء") }
            }
        )
    }

    // Server URL Dialog
    if (showServerDialog) {
        var editUrl by remember(serverUrl) { mutableStateOf(serverUrl) }
        AlertDialog(
            onDismissRequest = { showServerDialog = false },
            title = { Text("رابط الخادم", style = ArabicTitleSmall) },
            text = {
                OutlinedTextField(
                    value = editUrl,
                    onValueChange = { editUrl = it },
                    label = { Text("Server URL") },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth()
                )
            },
            confirmButton = {
                TextButton(onClick = {
                    serverUrl = editUrl
                    viewModel.updateServerUrl(editUrl)
                    showServerDialog = false
                    Toast.makeText(context, "تم تحديث رابط الخادم", Toast.LENGTH_SHORT).show()
                }) { Text("حفظ") }
            },
            dismissButton = {
                TextButton(onClick = { showServerDialog = false }) { Text("إلغاء") }
            }
        )
    }

    // Clear Cache Dialog
    if (showClearCacheDialog) {
        AlertDialog(
            onDismissRequest = { showClearCacheDialog = false },
            title = { Text("مسح الذاكرة المؤقتة", style = ArabicTitleSmall) },
            text = { Text("سيتم حذف جميع البيانات المخزنة محلياً (التقارير والصور المخبأة). هل تريد المتابعة؟", style = ArabicBodyMedium) },
            confirmButton = {
                TextButton(onClick = {
                    showClearCacheDialog = false
                    viewModel.clearCache()
                }) { Text("مسح") }
            },
            dismissButton = {
                TextButton(onClick = { showClearCacheDialog = false }) { Text("إلغاء") }
            }
        )
    }

    // Logout Dialog
    if (showLogoutDialog) {
        AlertDialog(
            onDismissRequest = { showLogoutDialog = false },
            title = { Text("تأكيد الخروج", style = ArabicTitleSmall) },
            text = { Text("هل أنت متأكد من تسجيل الخروج؟", style = ArabicBodyMedium) },
            confirmButton = {
                TextButton(onClick = {
                    showLogoutDialog = false
                    viewModel.logout()
                }) {
                    Text("تسجيل الخروج", color = MaterialTheme.colorScheme.error)
                }
            },
            dismissButton = {
                TextButton(onClick = { showLogoutDialog = false }) { Text("إلغاء") }
            }
        )
    }

    // About Dialog
    if (showAboutDialog) {
        AlertDialog(
            onDismissRequest = { showAboutDialog = false },
            title = { Text("حول التطبيق", style = ArabicTitleSmall) },
            text = {
                Column {
                    Text("SkinAnalyzer", style = ArabicBodyLarge, fontWeight = FontWeight.Bold)
                    Spacer(modifier = Modifier.height(4.dp))
                    Text("الإصدار ${BuildConfig.VERSION_NAME}", style = ArabicBodyMedium)
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        "منصة متكاملة لتحليل البشرة باستخدام الذكاء الاصطناعي. تقدم تقارير طبية دقيقة مع توصيات منتجات مخصصة.",
                        style = ArabicBodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text("© 2024 Jenin Care", style = MaterialTheme.typography.labelSmall)
                }
            },
            confirmButton = {
                TextButton(onClick = { showAboutDialog = false }) { Text("موافق") }
            }
        )
    }
}

@Composable
private fun SettingsItem(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    title: String,
    subtitle: String,
    trailing: @Composable (() -> Unit)? = null,
    onClick: (() -> Unit)? = null
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .then(
                if (onClick != null) Modifier.clickable { onClick() }
                else Modifier
            )
            .padding(horizontal = 16.dp, vertical = 14.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(40.dp)
                .clip(CircleShape)
                .background(JeninBlue.copy(alpha = 0.1f)),
            contentAlignment = Alignment.Center
        ) {
            Icon(icon, contentDescription = null, tint = JeninBlue, modifier = Modifier.size(22.dp))
        }

        Spacer(modifier = Modifier.width(16.dp))

        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = title,
                style = ArabicLabelLarge,
                color = MaterialTheme.colorScheme.onSurface
            )
            Text(
                text = subtitle,
                style = MaterialTheme.typography.labelSmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }

        if (trailing != null) {
            trailing()
        }
    }
}
