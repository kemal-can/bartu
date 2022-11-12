<template>
  <form @submit.prevent="submit" v-show="editOrCreate">
    <div class="mx-auto max-w-2xl p-4">
      <h2
        class="mb-4 text-lg font-medium text-neutral-700 dark:text-neutral-200"
        v-text="workflow.title"
      ></h2>
      <i-form-group label-for="title" :label="$t('workflow.title')" required>
        <i-form-input v-model="form.title" id="title" />
        <form-error :form="form" field="title" />
      </i-form-group>
      <i-form-group
        class="mb-4"
        label-for="description"
        :label="$t('workflow.description')"
        optional
      >
        <i-form-textarea v-model="form.description" rows="2" id="description" />
        <form-error :form="form" field="description" />
      </i-form-group>
      <i-form-group :label="$t('workflow.when')" required>
        <i-custom-select
          :input-id="'trigger-' + index"
          :clearable="false"
          label="name"
          @option:selected="handleTriggerChange"
          v-model="trigger"
          :options="triggers"
        >
        </i-custom-select>
        <form-error :form="form" field="trigger_type" />
      </i-form-group>
      <i-form-group
        v-if="hasChangeField"
        :label="$t('workflow.field_change_to')"
        required
      >
        <fields-generator
          :form="form"
          :fields="fields"
          :view="workflow.id ? 'update' : 'create'"
          :only="trigger.change_field.attribute"
        />
      </i-form-group>
      <i-form-group
        v-if="trigger"
        :class="{ 'mt--3': hasChangeField }"
        :label="$t('workflow.then')"
        required
      >
        <i-custom-select
          :input-id="'action-' + index"
          :clearable="false"
          v-if="trigger"
          @option:selected="handleActionChange"
          v-model="action"
          label="name"
          :options="trigger.actions"
        >
        </i-custom-select>
        <form-error :form="form" field="action_type" />
        <placeholders
          v-if="action"
          class="mt-3"
          :placeholders="action.placeholders"
        />
      </i-form-group>
      <i-form-group v-if="hasActionFields">
        <fields-generator
          :form="form"
          :view="workflow.id ? 'update' : 'create'"
          :fields="fields"
          :except="hasChangeField ? trigger.change_field.attribute : []"
        />
      </i-form-group>
    </div>
    <div class="bg-neutral-50 py-3 px-4 dark:bg-neutral-900">
      <div class="flex items-center justify-end">
        <i-form-toggle
          class="mr-4 border-r border-neutral-200 pr-4 dark:border-neutral-700"
          v-model="form.is_active"
          :label="$t('app.active')"
        />
        <i-button @click="cancel" variant="secondary">{{
          $t('app.cancel')
        }}</i-button>
        <i-button @click="submit" class="ml-2">{{ $t('app.save') }}</i-button>
      </div>
    </div>
  </form>

  <div :class="{ 'opacity-70': !workflow.is_active }" v-show="!editOrCreate">
    <div class="flex items-center px-4 py-4 sm:px-6">
      <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
        <div class="truncate">
          <div class="flex">
            <a
              href="#"
              class="link truncate font-medium"
              @click.prevent="editOrCreate = true"
              v-text="workflow.title"
            />
          </div>
          <div class="mt-1 flex">
            <div class="flex items-center space-x-4 text-sm text-neutral-500">
              <p class="text-neutral-800 dark:text-neutral-300">
                {{
                  $t('workflow.total_executions', {
                    total: workflow.total_executions,
                  })
                }}
              </p>
              <p
                class="text-sm text-neutral-800 dark:text-white"
                v-if="workflow.created_at"
              >
                {{ $t('app.created_at') }}:
                {{ localizedDateTime(workflow.created_at) }}
              </p>
            </div>
          </div>
        </div>
        <div class="mt-4 shrink-0 sm:mt-0 sm:ml-5">
          <div class="flex -space-x-1 overflow-hidden">
            <i-form-toggle
              :label="$t('app.active')"
              @change="handleWorkflowActiveChangeEvent"
              :model-value="workflow.is_active"
            />
          </div>
        </div>
      </div>
      <div class="ml-5 shrink-0">
        <i-minimal-dropdown>
          <i-dropdown-item @click="editOrCreate = true">
            {{ $t('app.edit') }}
          </i-dropdown-item>

          <i-dropdown-item @click="requestDestroy">
            {{ $t('app.delete') }}
          </i-dropdown-item>
        </i-minimal-dropdown>
      </div>
    </div>
  </div>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Placeholders from '@/views/Emails/Placeholders'
import Form from '@/components/Form/Form'
import find from 'lodash/find'

export default {
  emits: ['update:workflow', 'delete-requested'],
  mixins: [InteractsWithResourceFields],
  components: { Placeholders },
  data: () => ({
    editOrCreate: false,
    form: {},
    // Selected trigger
    trigger: null,
    // Selected action
    action: null,
  }),
  props: {
    index: {
      required: true,
      type: Number,
    },
    triggers: {
      required: true,
      type: Array,
    },
    workflow: {
      required: true,
      type: Object,
    },
  },
  watch: {
    /**
     * Watch the action change
     * We need to remove the old fields and add the new ones
     * in the same time keeps the CHANGEFIELD in the DOM to not loose
     * the FILL method
     *
     * @param  {mixed} newVal
     * @param  {mixed} oldVal
     *
     * @return {void}
     */
    action: function (newVal, oldVal) {
      // Remove any previous fields
      if (oldVal && oldVal.fields) {
        oldVal.fields.forEach(field => {
          // We don't remove the change field as this field is trigger based
          if (
            !this.hasChangeField ||
            (this.hasChangeField &&
              field.attribute !== this.trigger.change_field.attribute)
          ) {
            this.fields.remove(field.attribute)
          }
        })
      }

      // Add any new fields
      if (newVal && newVal.fields) {
        newVal.fields.forEach(field => {
          // Check if exists, it may exists if added via setFieldsForUpdate
          if (!this.fields.find(field.attribute)) {
            this.fields.push(this.cleanObject(field))
          }
        })
      }
    },
  },
  computed: {
    /**
     * Indicates wheter the trigger has change field
     *
     * @return {Boolean}
     */
    hasChangeField() {
      if (!this.trigger) {
        return false
      }

      return Boolean(this.trigger.change_field)
    },

    /**
     * Indicates whether the action has fields
     *
     * @return {Boolean}
     */
    hasActionFields() {
      if (!this.action) {
        return false
      }

      return this.action.fields.length > 0
    },
  },
  methods: {
    handleWorkflowActiveChangeEvent(value) {
      this.form.is_active = value
      this.submit()
    },
    /**
     * Cancel the workflow creation/edit
     *
     * @return {Void}
     */
    cancel() {
      if (this.workflow.id) {
        this.editOrCreate = false
        return
      }

      this.requestDestroy()
    },

    /**
     * Update workflow
     *
     * @return {Void}
     */
    update() {
      this.form.put('/workflows/' + this.workflow.id).then(data => {
        this.$emit('update:workflow', data)
        this.editOrCreate = false
        Innoclapps.success(this.$t('workflow.updated'))
      })
    },

    /**
     * Create new workflow
     *
     * @return {Void}
     */
    store() {
      this.form.post('/workflows').then(data => {
        this.$emit('update:workflow', data)
        Innoclapps.success(this.$t('workflow.created'))
      })
    },

    /**
     * Submit the workflow form
     *
     * @return {Promise}
     */
    async submit() {
      // Wait for the active switch to update
      await this.$nextTick()
      this.fillFormFields(this.form)
      this.workflow.id ? this.update() : this.store()
    },

    /**
     * Request remove
     *
     * @return {Void}
     */
    requestDestroy() {
      this.$emit('delete-requested', this.workflow)
    },

    /**
     * Handle trigger change
     *
     * @param  {Object} trigger
     *
     * @return {Void}
     */
    handleTriggerChange(trigger) {
      this.action = null
      this.form.errors.clear('trigger_type')
      this.createForm({
        title: this.form.title,
        description: this.form.description,
        is_active: this.form.is_active,
      })
      this.setFields(this.hasChangeField ? [this.trigger.change_field] : [])
      this.form.fill('trigger_type', trigger.identifier)
    },

    /**
     * Action change
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    handleActionChange(action) {
      this.form.errors.clear('action_type')
      this.form.fill('action_type', action.identifier || null)
    },

    /**
     * Create new workflow form
     *
     * @param {Object} data
     *
     * @return {Void}
     */
    createForm(data = {}) {
      this.form = new Form({
        trigger_type: data.trigger || null,
        action_type: data.action || null,
        title: data.title || null,
        description: data.description || null,
        is_active: data.is_active || true,
      })
    },

    /**
     * Set the workflow for update
     */
    setWorkflowForUpdate() {
      this.trigger = find(this.triggers, [
        'identifier',
        this.workflow.trigger_type,
      ])
      this.action = find(this.trigger.actions, [
        'identifier',
        this.workflow.action_type,
      ])

      // Set the fields for update
      let fields = this.hasActionFields ? this.action.fields : []

      if (this.hasChangeField) {
        fields.push(this.trigger.change_field)
      }

      // Avoid duplicate field id's as the fields
      // are inline for all workflows
      fields = fields.map(field => {
        field.id = field.attribute + '-' + this.index
        return field
      })

      this.setFieldsForUpdate(fields, this.workflow.data)
    },
  },
  mounted() {
    this.createForm({
      title: this.workflow.title,
      description: this.workflow.description,
      is_active: this.workflow.is_active,
      trigger: this.workflow.trigger_type,
      action: this.workflow.action_type,
    })

    if (this.workflow.id) {
      this.setWorkflowForUpdate()
    } else {
      this.editOrCreate = true
    }
  },
}
</script>
