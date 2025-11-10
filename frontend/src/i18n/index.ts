import { createI18n } from 'vue-i18n'
import en from '@/locales/en.json'
import ar from '@/locales/ar.json'

function getInitialLocale() {
  const saved = localStorage.getItem('locale')
  if (saved === 'ar' || saved === 'en') return saved
  const nav = navigator.language.toLowerCase()
  return nav.startsWith('ar') ? 'ar' : 'en'
}

export const i18n = createI18n({
  legacy: false,
  locale: getInitialLocale(),
  fallbackLocale: 'en',
  messages: { en, ar }
})

export function applyDir(locale: string) {
  const dir = locale === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.setAttribute('dir', dir)
  document.documentElement.setAttribute('lang', locale)
}

applyDir((i18n.global.locale as any).value)

