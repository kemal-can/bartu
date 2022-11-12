<template>
  <note
    v-for="(note, index) in notes"
    :key="'note-' + note.id"
    :class="{ 'mt-4': index !== 0 }"
    :note="note"
    :resource-name="resourceName"
  />
</template>
<script>
import Note from './View'
export default {
  components: { Note },
  props: {
    notes: Array,
    resourceName: {
      type: String,
      required: true,
    },
  },
}
</script>
