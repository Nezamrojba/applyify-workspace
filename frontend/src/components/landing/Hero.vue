<template>
  <section class="relative overflow-hidden bg-gradient-to-br from-surface via-white to-primary/5">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-32 -right-16 w-64 h-64 bg-primary/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-20 w-72 h-72 bg-success/10 rounded-full blur-3xl"></div>
    </div>

    <div class="container py-12 lg:py-20 relative">
      <div class="grid lg:grid-cols-[minmax(0,1fr)_380px] gap-10 items-center">
        <div :class="heroTextClasses">
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-sm font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
              <path d="M10 2a.75.75 0 01.66.395l1.45 2.694 2.964.447a.75.75 0 01.416 1.279l-2.144 2.066.506 2.937a.75.75 0 01-1.088.791L10 11.547l-2.764 1.462a.75.75 0 01-1.088-.79l.507-2.938L4.511 6.815a.75.75 0 01.415-1.279l2.965-.447 1.45-2.694A.75.75 0 0110 2z" />
            </svg>
            {{ t('landing.hero.badge') }}
          </span>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight text-balance">
            {{ t('landing.hero.title') }}
          </h1>
          <p class="text-base sm:text-lg text-muted max-w-xl mx-auto lg:mx-0">
            {{ t('landing.hero.subtitle') }}
          </p>
          <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 justify-center lg:justify-start">
            <Button size="lg" class="w-full sm:w-auto" @click="$emit('explore')">
              {{ t('landing.hero.ctaPrimary') }}
            </Button>
            <Button size="lg" variant="ghost" class="w-full sm:w-auto" @click="$emit('apply')">
              {{ t('landing.hero.ctaSecondary') }}
            </Button>
          </div>
        </div>

        <div class="relative hidden lg:block">
          <div class="relative mx-auto w-full max-w-sm">
            <div class="rounded-3xl border border-black/5 bg-white/80 backdrop-blur p-6 shadow-lg space-y-6">
              <div class="space-y-2">
                <p class="text-sm font-semibold text-primary uppercase tracking-wide">{{ t('landing.hero.stats.title') }}</p>
                <p class="text-3xl font-bold text-text">{{ t('landing.hero.stats.value') }}</p>
                <p class="text-xs text-muted">{{ t('landing.hero.stats.subtitle') }}</p>
              </div>
              <div class="grid gap-3 text-sm">
                <div class="rounded-2xl border border-black/5 bg-surface p-4">
                  <div class="font-semibold text-text">{{ t('landing.hero.snapshot.title') }}</div>
                  <p class="text-xs text-muted mt-1 leading-5">{{ t('landing.hero.snapshot.copy') }}</p>
                </div>
                <div class="rounded-2xl border border-black/5 bg-surface p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="text-xs text-muted uppercase tracking-wide">{{ t('landing.hero.timeline.title') }}</div>
                      <div class="text-sm font-semibold text-text">{{ t('landing.hero.timeline.value') }}</div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs text-success bg-success/10 px-2 py-1 rounded-full">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm2.707-9.293a1 1 0 10-1.414-1.414L9 9.586 8.207 8.793a1 1 0 10-1.414 1.414l1.5 1.5a1 1 0 001.414 0l3-3z" clip-rule="evenodd" />
                      </svg>
                      {{ t('landing.hero.timeline.tag') }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="rounded-2xl border border-primary/10 bg-primary/5 p-4 space-y-2">
                <div class="text-sm font-semibold text-primary">{{ t('landing.hero.support.title') }}</div>
                <p class="text-xs text-muted leading-5">{{ t('landing.hero.support.copy') }}</p>
              </div>
            </div>
            <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-primary/20 blur-2xl"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineEmits<{
  explore: []
  apply: []
}>()

const { t, locale } = useI18n()

const heroTextClasses = computed(() => {
  const base = 'space-y-6 text-center'
  return locale.value === 'ar'
    ? `${base} text-right lg:text-right`
    : `${base} lg:text-left`
})
</script>
