package com.ebtikar.skinanalyzer.ui.onboarding

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.RecyclerView
import androidx.viewpager2.widget.ViewPager2
import com.ebtikar.skinanalyzer.R
import com.ebtikar.skinanalyzer.databinding.ActivityOnboardingBinding
import com.ebtikar.skinanalyzer.ui.home.HomeActivity
import com.ebtikar.skinanalyzer.util.PreferencesManager
import com.google.android.material.tabs.TabLayoutMediator
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import javax.inject.Inject

data class OnboardingPage(val icon: String, val title: String, val description: String)

val ONBOARDING_PAGES = listOf(
    OnboardingPage(
        "\uD83D\uDD0D",
        "\u062a\u062d\u0644\u064a\u0644 \u0634\u0627\u0645\u0644 \u0644\u0644\u0628\u0634\u0631\u0629",
        "\u064a\u0633\u062a\u062e\u062f\u0645 \u062c\u0647\u0627\u0632 ZMLH02 \u062b\u0645\u0627\u0646\u064a\u0629 \u0623\u0637\u064a\u0627\u0641 \u0636\u0648\u0626\u064a\u0629 \u0645\u062e\u062a\u0644\u0641\u0629 \u0644\u062a\u062d\u0644\u064a\u0644 \u0634\u0627\u0645\u0644 \u0644\u0628\u0634\u0631\u062a\u0643 \u0628\u062f\u0642\u0629 \u0645\u062e\u0628\u0631\u064a\u0629"
    ),
    OnboardingPage(
        "\uD83D\uDCF7",
        "\u0645\u0633\u062d \u0630\u0643\u064a \u0628\u0627\u0644\u0643\u0627\u0645\u064a\u0631\u0627",
        "\u0648\u062c\u0651\u0647 \u0648\u062c\u0647\u0643 \u0646\u062d\u0648 \u0627\u0644\u0643\u0627\u0645\u064a\u0631\u0627 \u0633\u064a\u062a\u0645 \u0627\u0644\u062a\u0642\u0637\u064a\u0637 8 \u0635\u0648\u0631 \u0628\u0623\u0637\u064a\u0627\u0641 \u0645\u062e\u062a\u0644\u0641\u0629 \u062a\u0644\u0642\u0627\u0626\u064a\u0627\u064b"
    ),
    OnboardingPage(
        "\uD83D\uDCCA",
        "\u0646\u062a\u0627\u0626\u062c \u0641\u0648\u0631\u064a\u0629 \u0648\u0645\u0641\u0635\u0644\u0629",
        "\u0627\u062d\u0635\u0644 \u0639\u0644\u0649 \u062a\u0642\u0631\u064a\u0631 \u0645\u0641\u0635\u0644 \u064a\u0634\u0645\u0644 15 \u0645\u0642\u064a\u0627\u0633\u0627\u064b \u0644\u0635\u062d\u0629 \u0627\u0644\u0628\u0634\u0631\u0629 \u0645\u0639 \u062a\u0648\u0635\u064a\u0627\u062a \u0645\u062e\u0635\u0635\u0629 \u0644\u0643"
    )
)

@AndroidEntryPoint
class OnboardingActivity : AppCompatActivity() {

    private lateinit var binding: ActivityOnboardingBinding

    @Inject lateinit var preferencesManager: PreferencesManager

    override fun onCreate(savedInstanceState: Bundle?) {
        enableEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityOnboardingBinding.inflate(layoutInflater)
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

        val adapter = OnboardingAdapter(ONBOARDING_PAGES)
        binding.viewPager.adapter = adapter

        TabLayoutMediator(binding.tabIndicator, binding.viewPager) { _, _ -> }.attach()

        var currentPage = 0

        binding.viewPager.registerOnPageChangeCallback(object : ViewPager2.OnPageChangeCallback() {
            override fun onPageSelected(position: Int) {
                currentPage = position
                if (position == ONBOARDING_PAGES.size - 1) {
                    binding.btnNext.text = "\u0627\u0628\u062f\u0623 \u0627\u0644\u0622\u0646"
                    binding.btnSkip.visibility = View.GONE
                } else {
                    binding.btnNext.text = "\u0627\u0644\u062a\u0627\u0644\u064a"
                    binding.btnSkip.visibility = View.VISIBLE
                }
            }
        })

        binding.btnNext.setOnClickListener {
            if (currentPage < ONBOARDING_PAGES.size - 1) {
                binding.viewPager.currentItem = currentPage + 1
            } else {
                completeOnboarding()
            }
        }

        binding.btnSkip.setOnClickListener {
            completeOnboarding()
        }
    }

    private fun completeOnboarding() {
        lifecycleScope.launch {
            preferencesManager.setOnboardingCompleted()
            startActivity(Intent(this@OnboardingActivity, HomeActivity::class.java))
            finish()
        }
    }

    class OnboardingAdapter(
        private val pages: List<OnboardingPage>
    ) : RecyclerView.Adapter<OnboardingAdapter.PageViewHolder>() {

        class PageViewHolder(view: View) : RecyclerView.ViewHolder(view) {
            val icon: TextView = view.findViewById(R.id.tvOnboardingIcon)
            val title: TextView = view.findViewById(R.id.tvOnboardingTitle)
            val desc: TextView = view.findViewById(R.id.tvOnboardingDesc)
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): PageViewHolder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_onboarding, parent, false)
            return PageViewHolder(view)
        }

        override fun onBindViewHolder(holder: PageViewHolder, position: Int) {
            val page = pages[position]
            holder.icon.text = page.icon
            holder.title.text = page.title
            holder.desc.text = page.description
        }

        override fun getItemCount() = pages.size
    }
}
