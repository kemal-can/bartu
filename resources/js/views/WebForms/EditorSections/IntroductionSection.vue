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
        v-t="'form.sections.introduction.introduction'"
      />
    </template>
    <template #actions>
      <i-button-icon
        icon="PencilAlt"
        v-show="!editing"
        class="block md:hidden md:group-hover:block"
        icon-class="h-4 w-4"
        @click="setEditingMode"
      />
    </template>
    <div v-show="!editing">
      <h4
        class="text-lg font-medium text-neutral-800 dark:text-neutral-100"
        v-text="section.title"
      />
      <p
        class="text-sm text-neutral-600 dark:text-neutral-300"
        v-html="section.message"
      />
    </div>
    <div v-if="editing">
      <i-form-group
        :label="$t('form.sections.introduction.title')"
        label-for="title"
      >
        <i-form-input id="title" v-model="title" />
      </i-form-group>
      <i-form-group :label="$t('form.sections.introduction.message')">
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
    title: null,
    message: null,
  }),
  methods: {
    /**
     * Save section information
     *
     * @return {Void}
     */
    saveSection() {
      this.updateSection({
        title: this.title,
        message: this.message,
      })

      this.editing = false
    },

    /**
     * Invoke editing mode
     */
    setEditingMode() {
      this.title = this.section.title
      this.message = this.section.message
      this.editing = true
    },
  },
}
</script>
