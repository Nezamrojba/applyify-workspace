<template>
  <div class="container py-8 space-y-6">
    <UniversityFilters v-model:course="course" v-model:min="min" v-model:max="max" />
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <UniversityCard v-for="u in unis.items" :key="u.id" :id="u.id" :name="u.name" :acceptance="u.acceptance_percent" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useUniversityStore } from '@/stores/universities'
import UniversityFilters from '@/components/universities/UniversityFilters.vue'
import UniversityCard from '@/components/universities/UniversityCard.vue'

const unis = useUniversityStore()
const course = ref('')
const min = ref(50)
const max = ref(99)

function load() { unis.fetch({ course: course.value, min_accept: min.value, max_accept: max.value }) }

watch([course, min, max], load, { immediate: true })
</script>
