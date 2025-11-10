<template>
  <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
    <Input v-model="passportModel" :placeholder="$t('fields.passport') as string" />
    <Input v-model.number="universityIdModel" :placeholder="$t('nav.universities') + ' ID'" />
    <Input v-model.number="courseIdModel" :placeholder="'Course ID'" />
    <Button :disabled="creating" @click="$emit('create', { university_id: universityIdModel, course_id: courseIdModel, passport_no: passportModel })">{{$t('applications.create')}}</Button>
  </div>
</template>

<script setup lang="ts">
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

defineProps<{ creating?: boolean }>()
defineEmits(['create'])
const passportModel = defineModel<string>('passport')
const universityIdModel = defineModel<number | undefined>('universityId')
const courseIdModel = defineModel<number | undefined>('courseId')
</script>
