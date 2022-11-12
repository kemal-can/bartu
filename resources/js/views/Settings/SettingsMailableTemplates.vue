<template>
  <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
    <i-card :header="$t('mail_template.mail_templates')" :overlay="loading">
      <template #actions>
        <div class="flex flex-col items-center sm:flex-row">
          <div class="flex items-center self-start sm:mr-3">
            <span class="mr-2 text-neutral-700 dark:text-neutral-300">
              {{ $t('app.locale') }}:
            </span>
            <dropdown-select
              :items="locales"
              v-model="locale"
              placement="bottom-end"
              @change="fetch"
            />
          </div>
          <div class="flex items-center self-start">
            <span class="mr-2 text-neutral-700 dark:text-neutral-300">
              {{ $t('mail_template.template') }}:
            </span>
            <dropdown-select
              :items="templates"
              v-model="template"
              @change="setActive"
              placement="bottom-end"
              value-key="id"
              label-key="name"
            />
          </div>
        </div>
      </template>

      <i-form-group
        :label="$t('mail_template.subject')"
        label-for="subject"
        required
      >
        <i-form-input v-model="form.subject" id="subject" name="subject" />
        <form-error :form="form" field="subject" />
      </i-form-group>
      <i-form-group>
        <div class="mb-2 flex items-center">
          <!--
              <dropdown-select :items="['HTML', 'Text']"
              v-model="templateType" />
            -->
          <i-form-label required>{{
            $t('mail_template.message')
          }}</i-form-label>
        </div>
        <div v-show="isHtmlTemplateType">
          <editor
            v-model="form.html_template"
            :config="{ urlconverter_callback: placeholderURLConverter }"
          />
        </div>
        <div v-show="!isHtmlTemplateType">
          <i-form-textarea
            v-model="form.text_template"
            :rows="8"
            name="text_template"
          />
        </div>
        <form-error :form="form" field="html_template" />
        <form-error :form="form" field="text_template" />
      </i-form-group>
      <placeholders :placeholders="template.placeholders" />

      <template #footer>
        <i-button type="submit" :disabled="form.busy">{{
          $t('app.save')
        }}</i-button>
      </template>
    </i-card>
  </form>
</template>
<script>
import findIndex from 'lodash/findIndex'
import find from 'lodash/find'
import Editor from '@/components/Editor'
import Placeholders from '@/views/Emails/Placeholders'
import Form from '@/components/Form/Form'
import { mapGetters } from 'vuex'

export default {
  components: {
    Editor,
    Placeholders,
  },
  data: () => ({
    loading: false,
    form: {},
    templateType: 'HTML', // or text
    templates: [], // in locale templates
    template: {}, // active template
    locale: 'en', // default selected locale
  }),
  computed: {
    ...mapGetters({
      locales: 'locales',
    }),
    active() {
      return find(this.templates, ['id', this.template.id])
    },
    isHtmlTemplateType() {
      return this.templateType === 'HTML'
    },
  },
  methods: {
    /**
     * Submit the form
     * @return {Void}
     */
    submit() {
      this.form.put('/mailables/' + this.active.id).then(data => {
        let index = findIndex(this.templates, ['id', Number(data.id)])
        Innoclapps.success(this.$t('mail_template.updated'))
        this.templates[index] = data
      })
    },

    /**
     * Set active template
     * @param {Void} template
     */
    setActive(template) {
      this.template = template

      this.form = new Form({
        subject: this.active.subject,
        html_template: this.active.html_template,
        text_template: this.active.text_template,
      })
    },

    /**
     * Merge field url converter callback
     *
     * @param  {String} url
     * @param  {Node} node
     * @param  {Boolean} on_save
     * @param  {String} name
     *
     * @return {String}
     */
    placeholderURLConverter(url, node, on_save, name) {
      if (url.indexOf('%7B%7B%20') > -1 && url.indexOf('%20%7D%7D') > -1) {
        url = url.replace('%7B%7B%20', '{').replace('%20%7D%7D', '}')
      }

      return url
    },

    /**
     * Fetch the mail templates in locale
     * @return {Void}
     */
    fetch() {
      this.loading = true
      Innoclapps.request()
        .get('/mailables/' + this.locale + '/locale')
        .then(({ data }) => {
          this.templates = data
          // If previous template selected, keep it selected
          // Otherwise find the template with the same name
          // We find by name because the template may have different id
          this.setActive(
            Object.keys(this.template).length === 0
              ? data[0]
              : find(this.templates, ['name', this.template.name])
          )
        })
        .finally(() => (this.loading = false))
    },
  },
  created() {
    // Mail templates component must make the request each time is created
    // this helps to seed any missing templates in database
    this.fetch()
  },
}
</script>
