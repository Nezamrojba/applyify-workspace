import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export interface StudyProcessStep {
  number: number
  title: string
  description: string
  items: string[]
  color: 'primary' | 'success' | 'info' | 'warning'
}

export function useStudyProcessSteps() {
  const { t } = useI18n()

  const steps = computed<StudyProcessStep[]>(() => [
    {
      number: 1,
      title: t('landing.processSteps.step1.title') as string,
      description: t('landing.processSteps.step1.description') as string,
      items: [
        t('landing.processSteps.step1.item1') as string,
        t('landing.processSteps.step1.item2') as string,
      ],
      color: 'primary',
    },
    {
      number: 2,
      title: t('landing.processSteps.step2.title') as string,
      description: t('landing.processSteps.step2.description') as string,
      items: [
        t('landing.processSteps.step2.item1') as string,
        t('landing.processSteps.step2.item2') as string,
        t('landing.processSteps.step2.item3') as string,
      ],
      color: 'success',
    },
    {
      number: 3,
      title: t('landing.processSteps.step3.title') as string,
      description: t('landing.processSteps.step3.description') as string,
      items: [
        t('landing.processSteps.step3.item1') as string,
        t('landing.processSteps.step3.item2') as string,
        t('landing.processSteps.step3.item3') as string,
      ],
      color: 'info',
    },
    {
      number: 4,
      title: t('landing.processSteps.step4.title') as string,
      description: t('landing.processSteps.step4.description') as string,
      items: [
        t('landing.processSteps.step4.item1') as string,
        t('landing.processSteps.step4.item2') as string,
        t('landing.processSteps.step4.item3') as string,
      ],
      color: 'warning',
    },
  ])

  return {
    steps,
  }
}

