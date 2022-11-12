<template>
  <i-modal
    :visible="rulesVisible"
    size="lg"
    static-backdrop
    :title="!active ? $t('filters.create') : $t('filters.edit_filter')"
    :ok-title="saving ? $t('filters.save_and_apply') : $t('filters.apply')"
    :ok-disabled="!filtersCanBeApplied || form.busy"
    @ok="submit"
    :cancel-title="$t('app.hide')"
    :hide-footer="isReadonly"
    @shown="handleModalShown"
    @hidden="hideRules"
  >
    <i-alert class="mb-3 border border-info-200" v-if="isReadonly">{{
      $t('filters.is_readonly')
    }}</i-alert>

    <div class="mb-3 flex items-center justify-end space-x-2">
      <a
        v-if="!isCurrentFilterDefault && isReadonly"
        href="#"
        class="rounded-md px-2 py-1.5 text-sm font-medium text-primary-700 hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50"
        @click.prevent="markAsDefault"
      >
        {{ $t('filters.mark_as_default') }}
      </a>

      <a
        v-if="isCurrentFilterDefault && isReadonly"
        href="#"
        class="rounded-md px-2 py-1.5 text-sm font-medium text-primary-700 hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50"
        @click.prevent="unmarkAsDefault"
      >
        {{ $t('filters.unmark_as_default') }}
      </a>

      <a
        class="rounded-md px-2 py-1.5 text-sm font-medium text-primary-700 hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50"
        href="#"
        v-show="!isReadonly && hasRulesApplied"
        @click.prevent="clearRules"
      >
        {{ $t('filters.clear_rules') }}
      </a>

      <span
        v-if="active && canDelete"
        v-i-tooltip="
          isSystemDefault ? $t('filters.system_default_delete_info') : null
        "
      >
        <i-button-minimal
          variant="danger"
          :disabled="isSystemDefault || isReadonly"
          @click="destroy"
          >{{ $t('app.delete') }}</i-button-minimal
        >
      </span>
    </div>

    <query-builder
      v-bind="$attrs"
      ref="queryBuilder"
      :rules="availableRules"
      :read-only="isReadonly"
      :identifier="identifier"
      :view="view"
    />
    <div v-show="saving && !isReadonly" class="mt-5">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <i-form-group
            :label="$t('filters.name')"
            label-for="filter_name"
            required
          >
            <i-form-input
              v-model="form.name"
              size="sm"
              :placeholder="$t('filters.name')"
              id="filter_name"
              name="name"
              type="text"
            />
            <form-error field="name" :form="form" />
          </i-form-group>
        </div>
        <div>
          <i-form-group required :label="$t('filters.share.with')">
            <i-dropdown
              auto-size="min"
              size="sm"
              :text="
                form.is_shared
                  ? $t('filters.share.everyone')
                  : $t('filters.share.private')
              "
            >
              <i-dropdown-item @click="form.is_shared = false">
                <div class="flex flex-col">
                  <p class="text-neutral-900 dark:text-white">
                    {{ $t('filters.share.private') }}
                  </p>
                  <p class="text-neutral-500 dark:text-neutral-300">
                    {{ $t('filters.share.private_info') }}
                  </p>
                </div>
              </i-dropdown-item>
              <i-dropdown-item
                @click="form.is_shared = true"
                v-if="!hasRulesAppliedWithAuthorization"
              >
                <div class="flex flex-col">
                  <p class="text-neutral-900 dark:text-white">
                    {{ $t('filters.share.everyone') }}
                  </p>
                  <p class="text-neutral-500 dark:text-neutral-300">
                    {{ $t('filters.share.everyone_info') }}
                  </p>
                </div>
              </i-dropdown-item>
            </i-dropdown>
            <form-error field="is_shared" :form="form" />
          </i-form-group>
        </div>
      </div>
      <i-alert show v-if="hasRulesAppliedWithAuthorization" class="mb-3">
        {{
          $t('filters.cannot_be_shared', {
            rules: rulesLabelsWithAuthorization,
          })
        }}
      </i-alert>
    </div>

    <i-form-group v-show="saving && !isReadonly">
      <i-form-checkbox
        v-model:checked="defaulting"
        :label="$t('filters.is_default')"
      />
    </i-form-group>
    <template #modal-cancel="{ cancel, title }">
      <div class="flex space-x-4">
        <i-form-toggle
          v-model="saving"
          v-show="!editing && !active && hasRulesApplied"
          :label="$t('filters.save_as_new')"
        />
        <i-button
          variant="white"
          class="hidden sm:inline-flex"
          :disabled="form.busy"
          @click="cancel"
        >
          {{ title }}
        </i-button>
      </div>
    </template>
  </i-modal>
</template>
<script>
import QueryBuilder from '@/components/QueryBuilder'
import Form from '@/components/Form/Form'
import each from 'lodash/each'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'
import { isValueEmpty } from '@/utils'
import { mapMutations } from 'vuex'

import {
  isNullableOperator,
  isBetweenOperator,
} from '@/components/QueryBuilder/Utils'

export default {
  inheritAttrs: false,
  emits: ['apply'],
  components: { QueryBuilder },
  props: {
    view: { required: true, type: String },
    identifier: { required: true, type: String },
    activeFilterId: Number,
    initialApply: { default: true, type: Boolean },
  },
  data() {
    return {
      editing: false,
      saving: false,
      defaulting: false,
      form: {},
    }
  },
  watch: {
    /**
     * Update the store on rules are valid change
     *
     * @type {Object}
     */
    rulesAreValid: {
      handler: function (newVal) {
        this.$store.commit('filters/SET_RULES_ARE_VALID', {
          identifier: this.identifier,
          view: this.view,
          value: newVal,
        })
      },
      immediate: true,
    },

    /**
     * Update the store on rules applied change
     *
     * @type {Object}
     */
    hasRulesApplied: {
      handler: function (newVal) {
        this.$store.commit('filters/SET_HAS_RULES_APPLIED', {
          identifier: this.identifier,
          view: this.view,
          value: newVal,
        })
      },
      immediate: true,
    },
  },
  computed: {
    /**
     * Get the rule labels with authorization
     *
     * @return {String}
     */
    rulesLabelsWithAuthorization() {
      return this.rulesWithAuthorization.map(rule => rule.label).join(', ')
    },

    /**
     * Get all the rules in the query builder which are having authorization
     *
     * @return {Array}
     */
    rulesWithAuthorization() {
      return this.$store.state.filters.rules[this.identifier].filter(
        rule => rule.has_authorization
      )
    },

    /**
     * Indicates whether there are rules in the query builder which are with authorization
     *
     * @return {Boolean}
     */
    hasRulesAppliedWithAuthorization() {
      return this.rulesWithAuthorization.some(rule =>
        this.$store.getters['filters/findRuleInQueryBuilder'](
          this.identifier,
          this.view,
          rule.id
        )
      )
    },

    /**
     * Check whether the curent active filter is sytem default
     *
     * @return {Boolean}
     */
    isSystemDefault() {
      return this.active && this.active.is_system_default
    },

    /**
     * Indicates whether currently applied filter is default
     *
     * @return {Boolean}
     */
    isCurrentFilterDefault() {
      return this.active && this.default && this.active.id == this.default.id
    },

    /**
     * Indicates whether the filters can be applied
     *
     * @return {Boolean}
     */
    filtersCanBeApplied() {
      return !(!this.rulesAreValid || this.totalValidRules === 0)
    },

    /**
     * Check whether the filter is read only
     *
     * @return {Boolean}
     */
    isReadonly() {
      return (
        (this.active && this.active.is_readonly) ||
        this.activeFilterIsSharedFromAnotherUser
      )
    },

    /**
     * Currently filters rules in the builder
     *
     * @type {Object}
     */
    rules() {
      return this.$store.getters['filters/getBuilderRules'](
        this.identifier,
        this.view
      )
    },

    /**
     * The available rules
     *
     * @return {Array}
     */
    availableRules() {
      return this.$store.state.filters.rules[this.identifier]
    },

    /**
     * Check whether the filters rules should be shown
     *
     * @return {Boolean}
     */
    rulesVisible() {
      return this.$store.getters['filters/rulesAreVisible'](
        this.identifier,
        this.view
      )
    },

    /**
     * Provides the default filter
     *
     * @return {Object|null}
     */
    default() {
      return this.$store.getters['filters/getDefault'](
        this.identifier,
        this.view,
        this.currentUser.id
      )
    },

    /**
     * The applied rules values
     *
     * @return {Array}
     */
    rulesValidationValues() {
      return this.getValuesForValidation(this.rules)
    },

    /**
     * Total number of rules in the query builder
     * The function checks based on the values that exists
     *
     * @return {Number}
     */
    totalValidRules() {
      return this.rulesValidationValues.length
    },

    /**
     * Checks whether the applied rules are valid
     *
     * @return {boolean}
     */
    rulesAreValid() {
      if (!this.hasRulesApplied) {
        return true
      }

      let totalValid = 0

      this.rulesValidationValues.forEach(value => {
        if (!isValueEmpty(value)) {
          totalValid++
        }
      })

      // If all rules has values, the filters are valid
      return this.totalValidRules === totalValid
    },

    /**
     * Check if there are filters applied for the resource
     *
     * @return {Boolean}
     */
    hasRulesApplied() {
      // If there is values, this means that there is at least
      // one rule added in the filter
      return this.totalValidRules > 0
    },

    /**
     * Provides the active filter
     *
     * @return {null|Object}
     */
    active() {
      return this.$store.getters['filters/getActive'](
        this.identifier,
        this.view
      )
    },

    /**
     * Determine whether the active filter is shared and created from another user
     *
     * @return {Boolean}
     */
    activeFilterIsSharedFromAnotherUser() {
      if (!this.active || this.active.is_system_default) {
        return false
      }

      return this.active.is_shared && this.active.user_id != this.currentUser.id
    },

    /**
     * Indicates whether the filter can be updated
     *
     * @return {Boolean}
     */
    canUpdate() {
      return this.$gate.allows('update', this.active)
    },

    /**
     * Indicates whether the filter can be deleted
     *
     * @return {Boolean}
     */
    canDelete() {
      return this.$gate.allows('delete', this.active)
    },
  },
  methods: {
    ...mapMutations({
      PUSH_FILTER: 'filters/PUSH',
      UPDATE_FILTER: 'filters/UPDATE',
      UNMARK_AS_DEFAULT: 'filters/UNMARK_AS_DEFAULT',
    }),

    /**
     * Reset the filters state
     */
    resetState() {
      this.form = new Form({
        name: null,
        rules: [],
        is_shared: false,
      })
      this.saving = false
      this.editing = false
      this.defaulting = false
    },

    /**
     * Handle the modal shown event
     */
    handleModalShown() {
      this.resetState()

      if (this.active && this.canUpdate) {
        this.setUpdateData()
      }
    },

    /**
     * Hide te rules
     */
    hideRules() {
      this.$store.commit('filters/SET_RULES_VISIBLE', {
        view: this.view,
        visible: false,
        identifier: this.identifier,
      })
    },

    /**
     * Store new filter
     *
     * @return {Promise}
     */
    async store() {
      this.form.fill('identifier', this.identifier)
      let filter = await this.form.post(`/filters`)

      this.PUSH_FILTER({
        filter: filter,
        identifier: this.identifier,
      })

      this.setActive(filter.id)

      return filter
    },

    /**
     * Update the currently active filter
     *
     * @return {Promise}
     */
    async update() {
      const filter = await this.form.put(`/filters/${this.active.id}`)
      this.handleUpdatedLifeCycle(filter)

      return filter
    },

    /**
     * Submit the filters form
     *
     * @return {Promise}
     */
    async submit() {
      if (!this.saving) {
        this.apply()
        this.hideRules()
        return
      }

      this.form.fill('rules', this.rules)

      await (this.editing ? this.update() : this.store())
      await this.$nextTick()

      if (!this.editing) {
        this.defaulting && this.markAsDefault()
      } else {
        if (this.isCurrentFilterDefault && !this.defaulting) {
          this.unmarkAsDefault()
        } else if (!this.isCurrentFilterDefault && this.defaulting) {
          this.markAsDefault()
        }
      }

      this.apply()
      this.hideRules()
    },

    /**
     * Set update data so the submit method can use
     */
    setUpdateData() {
      this.form.is_shared = this.active.is_shared
      this.form.name = this.active.name
      this.defaulting = this.isCurrentFilterDefault
      this.editing = true
      this.saving = true
    },

    /**
     * Delete filter
     *
     * @return {Void}
     */
    destroy() {
      this.$store
        .dispatch('filters/destroy', {
          identifier: this.identifier,
          view: this.view,
          id: this.active.id,
        })
        .then(this.hideRules)
    },

    /**
     * Make the active filter as default
     *
     * @return {Void}
     */
    markAsDefault() {
      Innoclapps.request()
        .put(`filters/${this.active.id}/${this.view}/default`)
        .then(({ data }) => {
          // We need to remove the previous default filter data
          if (this.default && this.default.id != data.id) {
            this.UNMARK_AS_DEFAULT({
              id: this.default.id,
              identifier: this.identifier,
              view: this.view,
              userId: this.currentUser.id,
            })
          }

          this.handleUpdatedLifeCycle(data)
        })
    },

    /**
     * Unmark the active filter as default
     *
     * @return {Void}
     */
    unmarkAsDefault() {
      Innoclapps.request()
        .delete(`/filters/${this.active.id}/${this.view}/default`)
        .then(({ data }) => {
          this.handleUpdatedLifeCycle(data)
        })
    },

    /**
     * Update the filter in Vuex
     *
     * @param  {Object} filter
     *
     * @return {Void}
     */
    handleUpdatedLifeCycle(filter) {
      this.UPDATE_FILTER({
        identifier: this.identifier,
        filter: filter,
      })
    },

    /**
     * Clear the current active filter
     *
     * @return {Void}
     */
    clearActive() {
      this.$store.dispatch('filters/clearActive', {
        view: this.view,
        identifier: this.identifier,
      })
    },

    /**
     * Apply filters event
     *
     * @return {Void}
     */
    apply() {
      this.$emit('apply', this.rules)
    },

    /**
     * Set filter as active
     *
     * @param {Number}  id           The filter id
     * @param {Boolean} emit Whether to emit apply event
     */
    setActive(id, emit = true) {
      // First get the filter that we are trying to set as active
      let filter = this.$store.getters['filters/getById'](this.identifier, id)

      // Next, we will update the QB rules
      this.$store.commit('filters/SET_BUILDER_RULES', {
        identifier: this.identifier,
        view: this.view,
        rules: filter.rules,
      })

      // Finally, set the active filter in store
      this.$store.commit('filters/SET_ACTIVE', {
        identifier: this.identifier,
        view: this.view,
        id: filter.id,
      })

      // And if needed emit apply event
      if (emit) {
        this.apply()
      }
    },

    /**
     * Clear the applied query builder rules
     *
     * @return {Void}
     */
    clearRules() {
      this.$store.commit('filters/RESET_BUILDER_RULES', {
        identifier: this.identifier,
        view: this.view,
      })

      this.apply()
    },

    /**
     * Get the applied rules values
     *
     * @param  {Object} query
     *
     * @return {Array}
     */
    getValuesForValidation(query) {
      let vals = []

      each(query.children, (rule, key) => {
        if (rule.query.children) {
          vals = vals.concat(this.getValuesForValidation(rule.query))
        } else {
          let filter = find(this.availableRules, ['id', rule.query.rule])
          if (filter && filter.isStatic) {
            // Push true so it can trigger true rule
            // static rules are always valid as they do not receive any values
            vals.push(true)
          } else if (isNullableOperator(rule.query.operator)) {
            // Push only true so we can validate as valid rule
            vals.push(true)
          } else if (isBetweenOperator(rule.query.operator)) {
            // Validate between, from and to must be selected
            if (
              rule.query.value &&
              !isValueEmpty(rule.query.value[0]) &&
              !isValueEmpty(rule.query.value[1])
            ) {
              vals.push(cloneDeep(rule.query.value))
            } else {
              // Push null so it can trigger false rule
              vals.push(null)
            }
          } else {
            vals.push(cloneDeep(rule.query.value))
          }
        }
      })

      return vals
    },
  },

  /**
   * Handle the created event
   */
  created() {
    Innoclapps.$on(
      `${this.identifier}-${this.view}-filter-selected`,
      this.setActive
    )

    // The setActive method will trigger the refresh too
    if (this.activeFilterId) {
      this.setActive(this.activeFilterId, this.initialApply)
    } else if (this.default) {
      this.setActive(this.default.id, this.initialApply)
    } else {
      this.initialApply && this.apply()
    }
  },

  // The refresh event (apply()) won't be invoked here because listeners
  // are already destroyed in the parent component who uses the filters
  unmounted() {
    this.clearActive()
    Innoclapps.$off(
      `${this.identifier}-${this.view}-filter-selected`,
      this.setActive
    )
  },
}
</script>
