<script setup>
import GuestLayout from '@/Layouts/Guest.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'

const emitTest = e => console.log(e)

</script>

<template>
  <GuestLayout>
    <Label value="件名">タイトル</Label>
    <Input 
      modelValue="dfafe" 
      @update:modelValue="emitTest"
    >
      a
    </Input>
  </GuestLayout>
</template>