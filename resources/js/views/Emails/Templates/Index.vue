<template>
  <div>
    <div v-show="!isCreatingOrEditing">
      <div class="mb-3 text-right">
        <i-button
          variant="primary"
          @click="creatingTemplate = true"
          size="sm"
          >{{ $t('mail.templates.create') }}</i-button
        >
      </div>
      <i-table bordered>
        <thead>
          <tr>
            <th class="text-left" width="50%" v-t="'mail.templates.name'"></th>
            <th class="text-left" v-t="'app.created_by'"></th>
            <th class="text-left" v-t="'app.last_modified_at'"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(template, index) in templates" :key="template.id">
            <td width="50%">
              <div class="flex">
                <div class="grow">
                  <p
                    class="text-neutral-800 dark:text-neutral-100"
                    v-text="template.name"
                  />
                  <p class="text-sm text-neutral-600 dark:text-neutral-300">
                    {{ $t('mail.templates.subject') }}:
                    {{ template.subject }}
                  </p>
                </div>
                <div class="flex items-center space-x-2">
                  <i-button
                    size="sm"
                    variant="primary"
                    @click="handleSelected(index)"
                    >{{ $t('mail.templates.select') }}</i-button
                  >

                  <i-minimal-dropdown
                    v-if="
                      template.authorizations.update ||
                      template.authorizations.delete
                    "
                  >
                    <i-dropdown-item
                      v-if="template.authorizations.update"
                      @click="templateBeingUpdated = template.id"
                      >{{ $t('app.edit') }}</i-dropdown-item
                    >
                    <i-dropdown-item
                      v-if="template.authorizations.delete"
                      @click="destroy(template.id)"
                      >{{ $t('app.delete') }}</i-dropdown-item
                    >
                  </i-minimal-dropdown>
                </div>
              </div>
            </td>
            <td>
              {{ template.user.name }}
            </td>
            <td>
              {{ localizedDateTime(template.updated_at) }}
            </td>
          </tr>
          <tr class="bg-white" v-show="!hasTemplates">
            <td :colspan="3" class="p-5 text-center">
              <div class="text-center" v-t="'table.empty'"></div>
            </td>
          </tr>
        </tbody>
      </i-table>
    </div>
    <template-create
      v-if="creatingTemplate"
      @cancel-requested="creatingTemplate = false"
      @created="creatingTemplate = false"
    />
    <template-edit
      v-if="templateBeingUpdated"
      @updated="templateBeingUpdated = null"
      @cancel-requested="templateBeingUpdated = null"
      :id="templateBeingUpdated"
    />
  </div>
</template>
<script>
import { mapState } from 'vuex'
import TemplateCreate from './Create'
import TemplateEdit from './Edit'
export default {
  emits: ['selected'],
  components: {
    TemplateCreate,
    TemplateEdit,
  },
  data: () => ({
    templateBeingUpdated: null,
    creatingTemplate: false,
  }),
  computed: {
    ...mapState({
      templates: state => state.predefinedMailTemplates.collection,
    }),
    /**
     * Check if user is editing or creating template
     *
     * @return {Boolean}
     */
    isCreatingOrEditing() {
      return this.templateBeingUpdated || this.creatingTemplate
    },

    /**
     * Indicates whether there are templates created
     *
     * @return {Boolean}
     */
    hasTemplates() {
      return this.templates.length > 0
    },
  },
  methods: {
    /**
     * Delete template
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$store
        .dispatch('predefinedMailTemplates/destroy', id)
        .then(() => Innoclapps.success(this.$t('mail.templates.deleted')))
    },

    /**
     * Handle template selected
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    handleSelected(index) {
      this.$emit('selected', this.templates[index])
    },
  },
  created() {
    this.$store.dispatch('predefinedMailTemplates/fetch')
  },
}
</script>
