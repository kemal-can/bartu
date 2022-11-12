<template>
  <i-card
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing,
    }"
  >
    <template #header>
      <p
        class="font-semibold text-neutral-800 dark:text-neutral-200"
        v-t="'form.sections.message.message'"
      />
    </template>

    <template #actions>
      <div class="inline-flex space-x-2">
        <i-button-icon
          icon="PencilAlt"
          class="block md:hidden md:group-hover:block"
          icon-class="h-4 w-4"
          v-show="!editing"
          @click="setEditingMode"
        />
        <i-button-icon
          icon="Trash"
          class="block md:hidden md:group-hover:block"
          icon-class="h-4 w-4"
          @click="removeSection"
        />
      </div>
    </template>
    <div
      v-show="!editing"
      v-html="section.message"
      class="text-sm text-neutral-600 dark:text-neutral-200"
    ></div>
    <div v-if="editing">
      <i-form-group :label="$t('form.sections.message.message')">
        <editor :with-image="false" v-model="message" />
      </i-form-group>
      <div class="space-x-2 text-right">
        <i-button size="sm" @click="editing = false" variant="white">
          {{ $t('app.cancel') }}
        </i-button>
        <i-button size="sm" @click="saveSection" variant="secondary">
          {{ $t('app.save') }}
        </i-button>
      </div>
    </div>
  </i-card>
</template>
<script>
import Section from './Section'
import Editor from '@/components/Editor'
export default {
  mixins: [Section],
  components: { Editor },
  data: () => ({
    message: null,
  }),
  methods: {
    /**
     * Save the section information
     *
     * @return {Void}
     */
    saveSection() {
      this.updateSection({
        message: this.message,
      })

      this.editing = false
    },

    /**
     * Invoke editing mode
     */
    setEditingMode() {
      this.message = this.section.message
      this.editing = true
    },
  },
}
</script>
