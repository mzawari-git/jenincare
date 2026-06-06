package com.jenincare.skinanalyzer.ui.navigation

import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.navArgument
import androidx.navigation.navDeepLink
import com.jenincare.skinanalyzer.ui.camera.CameraScreen
import com.jenincare.skinanalyzer.ui.report.ReportScreen
import com.jenincare.skinanalyzer.ui.settings.SettingsScreen
import com.jenincare.skinanalyzer.ui.timeline.TimelineScreen
import com.jenincare.skinanalyzer.ui.waiting.WaitingScreen
import com.jenincare.skinanalyzer.ui.home.HomeScreen
import com.jenincare.skinanalyzer.ui.auth.LoginScreen
import com.jenincare.skinanalyzer.ui.splash.SplashScreen
import com.jenincare.skinanalyzer.ui.web.WebViewScreen
import com.jenincare.skinanalyzer.data.local.OnboardingManager
import com.jenincare.skinanalyzer.ui.onboarding.OnboardingScreen
import com.jenincare.skinanalyzer.ui.onboarding.OnboardingViewModel

object Routes {
    const val SPLASH = "splash"
    const val ONBOARDING = "onboarding"
    const val LOGIN = "login"
    const val HOME = "home"
    const val CAMERA = "camera"
    const val WAITING = "waiting/{scanId}"
    const val REPORT = "report/{scanId}"
    const val TIMELINE = "timeline/{scanId}"
    const val SETTINGS = "settings"
    const val WEB = "web/{url}"

    fun waiting(scanId: String) = "waiting/$scanId"
    fun report(scanId: String) = "report/$scanId"
    fun timeline(scanId: String) = "timeline/$scanId"
    fun web(url: String) = "web/${java.net.URLEncoder.encode(url, "UTF-8")}"
}

@Composable
fun SkinAnalyzerNavGraph(
    navController: NavHostController,
    startDestination: String = Routes.CAMERA
) {
    NavHost(
        navController = navController,
        startDestination = startDestination
    ) {
        composable(Routes.SPLASH) {
            SplashScreen(
                onNavigateToLogin = {
                    navController.navigate(Routes.LOGIN) {
                        popUpTo(Routes.SPLASH) { inclusive = true }
                    }
                },
                onNavigateToHome = {
                    navController.navigate(Routes.HOME) {
                        popUpTo(Routes.SPLASH) { inclusive = true }
                    }
                }
            )
        }

        composable(Routes.ONBOARDING) {
            val onboardingManager: OnboardingManager = hiltViewModel<OnboardingViewModel>().manager
            OnboardingScreen(
                onComplete = {
                    navController.navigate(Routes.HOME) {
                        popUpTo(Routes.ONBOARDING) { inclusive = true }
                    }
                },
                onboardingManager = onboardingManager
            )
        }

        composable(Routes.LOGIN) {
            LoginScreen(
                onLoginSuccess = {
                    navController.navigate(Routes.HOME) {
                        popUpTo(Routes.LOGIN) { inclusive = true }
                    }
                }
            )
        }

        composable(Routes.HOME) {
            HomeScreen(
                onNavigateToCamera = {
                    android.util.Log.d("NavGraph", "navigating to ${Routes.CAMERA}")
                    try {
                        navController.navigate(Routes.CAMERA)
                        android.util.Log.d("NavGraph", "navigate call succeeded")
                    } catch (e: Exception) {
                        android.util.Log.e("NavGraph", "navigate failed", e)
                    }
                },
                onNavigateToWaiting = { scanId ->
                    android.util.Log.d("NavGraph", "navigating to waiting/$scanId")
                    navController.navigate(Routes.waiting(scanId))
                },
                onNavigateToReport = { scanId ->
                    android.util.Log.d("NavGraph", "navigating to report/$scanId")
                    navController.navigate(Routes.report(scanId))
                },
                onNavigateToTimeline = { scanId ->
                    navController.navigate(Routes.timeline(scanId))
                },
                onNavigateToSettings = {
                    android.util.Log.d("NavGraph", "navigating to ${Routes.SETTINGS}")
                    try {
                        navController.navigate(Routes.SETTINGS)
                        android.util.Log.d("NavGraph", "navigate call succeeded")
                    } catch (e: Exception) {
                        android.util.Log.e("NavGraph", "navigate failed", e)
                    }
                },
                onLogout = {
                    navController.navigate(Routes.LOGIN) {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }

        composable(Routes.CAMERA) {
            CameraScreen(
                onScanSubmitted = { scanId ->
                    navController.navigate(Routes.waiting(scanId)) {
                        popUpTo(Routes.HOME)
                    }
                },
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        composable(
            route = Routes.WAITING,
            arguments = listOf(navArgument("scanId") { type = NavType.StringType })
        ) { backStackEntry ->
            val scanId = backStackEntry.arguments?.getString("scanId") ?: return@composable
            WaitingScreen(
                scanId = scanId,
                onReportReady = { id ->
                    navController.navigate(Routes.report(id)) {
                        popUpTo(Routes.WAITING) { inclusive = true }
                    }
                },
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        composable(
            route = Routes.REPORT,
            arguments = listOf(navArgument("scanId") { type = NavType.StringType }),
            deepLinks = listOf(navDeepLink { uriPattern = "skinanalyzer://report/{scanId}" })
        ) { backStackEntry ->
            val scanId = backStackEntry.arguments?.getString("scanId") ?: return@composable
            ReportScreen(
                scanId = scanId,
                onNavigateToTimeline = { id ->
                    navController.navigate(Routes.timeline(id))
                },
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        composable(
            route = Routes.TIMELINE,
            arguments = listOf(navArgument("scanId") { type = NavType.StringType })
        ) { backStackEntry ->
            val scanId = backStackEntry.arguments?.getString("scanId") ?: return@composable
            TimelineScreen(
                scanId = scanId,
                onNavigateToReport = { id ->
                    navController.navigate(Routes.report(id))
                },
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        composable(Routes.SETTINGS) {
            SettingsScreen(
                onNavigateBack = {
                    navController.popBackStack()
                },
                onLogout = {
                    navController.navigate(Routes.LOGIN) {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }

        composable(
            route = Routes.WEB,
            arguments = listOf(navArgument("url") { type = NavType.StringType })
        ) { backStackEntry ->
            val url = backStackEntry.arguments?.getString("url") ?: return@composable
            WebViewScreen(
                url = java.net.URLDecoder.decode(url, "UTF-8"),
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }
    }
}
