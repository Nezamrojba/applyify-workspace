import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export interface HowItWorksStep {
  id: string
  number: number
  title: string
  description: string
  icon?: string
  imageUrl?: string
  imageAlt?: string
  color: 'primary' | 'success' | 'info' | 'warning'
  features?: string[]
}

export function useHowItWorks() {
  const { t } = useI18n()

  const steps = computed<HowItWorksStep[]>(() => [
    {
      id: 'step-1',
      number: 1,
      title: t('howItWorks.steps.step1.title') as string,
      description: t('howItWorks.steps.step1.description') as string,
      icon: 'search',
      color: 'primary',
      features: [
        t('howItWorks.steps.step1.feature1') as string,
        t('howItWorks.steps.step1.feature2') as string,
        t('howItWorks.steps.step1.feature3') as string,
      ],
    },
    {
      id: 'step-2',
      number: 2,
      title: t('howItWorks.steps.step2.title') as string,
      description: t('howItWorks.steps.step2.description') as string,
      icon: 'document',
      color: 'success',
      features: [
        t('howItWorks.steps.step2.feature1') as string,
        t('howItWorks.steps.step2.feature2') as string,
        t('howItWorks.steps.step2.feature3') as string,
      ],
    },
    {
      id: 'step-3',
      number: 3,
      title: t('howItWorks.steps.step3.title') as string,
      description: t('howItWorks.steps.step3.description') as string,
      icon: 'track',
      color: 'info',
      features: [
        t('howItWorks.steps.step3.feature1') as string,
        t('howItWorks.steps.step3.feature2') as string,
        t('howItWorks.steps.step3.feature3') as string,
      ],
    },
    {
      id: 'step-4',
      number: 4,
      title: t('howItWorks.steps.step4.title') as string,
      description: t('howItWorks.steps.step4.description') as string,
      icon: 'success',
      color: 'warning',
      features: [
        t('howItWorks.steps.step4.feature1') as string,
        t('howItWorks.steps.step4.feature2') as string,
        t('howItWorks.steps.step4.feature3') as string,
      ],
    },
  ])

  return {
    steps,
  }
}
