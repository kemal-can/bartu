<template>
  <i-card :header="$t('settings.translator.translator')" no-body>
    <template #actions>
      <div class="flex items-center space-x-3">
        <dropdown-select
          :items="locales"
          v-model="locale"
          @change="getTranslations"
          placement="bottom-end"
        />
        <i-button v-i-modal="'new-locale'" icon="plus" size="sm">{{
          $t('settings.translator.new_locale')
        }}</i-button>
      </div>
    </template>
    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li
        v-for="(groupTranslations, group) in translations.current"
        :key="group"
        v-show="!activeGroup || activeGroup === group"
      >
        <div class="hover:bg-neutral-100 dark:hover:bg-neutral-700/60">
          <div class="flex items-center">
            <div class="grow">
              <a
                href="#"
                @click.prevent="toggleGroup(group)"
                class="block px-7 py-2 font-medium text-neutral-600 dark:text-neutral-200"
              >
                {{ strTitle(group.replace('_', ' ')) }}
              </a>
            </div>
            <div class="ml-2 py-2 pr-7">
              <i-button
                variant="white"
                size="sm"
                @click="toggleGroup(group)"
                icon="ChevronDown"
              />
            </div>
          </div>
        </div>
        <form
          @submit.prevent="saveGroup(group)"
          novalidate="true"
          v-show="activeGroup === group"
        >
          <i-table :shadow="false">
            <thead>
              <tr>
                <th class="text-left" width="15%">Key</th>
                <th class="text-left" width="30%">Source</th>
                <th class="text-left" width="55%">{{ locale }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(translation, key) in groupTranslations" :key="key">
                <td width="15%">
                  {{ key }}
                </td>
                <td width="30%">
                  {{ translations.source[group][key] }}
                </td>
                <td width="55%">
                  <!-- When disabled, means that the key is array and empty
                    because when the key is empty will the value will be empty array instead of key e.q. lang.key -->
                  <i-form-textarea
                    v-if="!Array.isArray(translations.current[group][key])"
                    v-model="translations.current[group][key]"
                    rows="3"
                  ></i-form-textarea>
                </td>
              </tr>
            </tbody>
          </i-table>
          <div
            class="-mt-px space-x-3 bg-neutral-50 px-6 py-3 text-right dark:bg-neutral-700"
          >
            <i-button
              type="submit"
              size="sm"
              :disabled="groupIsBeingSaved"
              :loading="groupIsBeingSaved"
              >{{ $t('app.save') }}</i-button
            >
            <i-button
              size="sm"
              @click="deactivateGroup(group, true)"
              :disabled="groupIsBeingSaved"
              variant="white"
              >{{ $t('app.cancel') }}</i-button
            >
          </div>
        </form>
      </li>
    </ul>
    <i-modal
      size="sm"
      id="new-locale"
      form
      @submit="storeNewLocale"
      @keydown="localeForm.onKeydown($event)"
      :ok-title="$t('app.create')"
      :cancel-title="$t('app.cancel')"
      :title="$t('settings.translator.create_new_locale')"
    >
      <i-form-group
        label-for="localeName"
        :label="$t('settings.translator.locale_name')"
        required
      >
        <i-form-input id="localeName" v-model="localeForm.name"></i-form-input>
        <form-error :form="localeForm" field="name" />
      </i-form-group>
    </i-modal>
  </i-card>
</template>
<script>
import { mapGetters } from 'vuex'
import { strTitle } from '@/utils'
import Form from '@/components/Form/Form'
import isEqual from 'lodash/isEqual'
import cloneDeep from 'lodash/cloneDeep'

export default {
  data: () => ({
    localeForm: new Form({
      name: null,
    }),
    // Active locale
    locale: Innoclapps.config.locale,
    // Active locale groups translation
    translations: {},
    originalTranslations: {},
    activeGroup: null,
    groupIsBeingSaved: false,
  }),
  computed: {
    ...mapGetters({
      locales: 'locales',
    }),
  },
  beforeRouteLeave(to, from, next) {
    const unsaved = this.getUnsavedTranslationGroups()
    if (unsaved.length > 0) {
      this.$dialog
        .confirm({
          message: this.$t('settings.translator.changes_not_saved'),
          title: 'Are you sure you want to leave this page?',
          confirmText: this.$t('app.discard_changes'),
        })
        .then(() => next())
        .catch(() => next(false))
    } else {
      next()
    }
  },
  methods: {
    strTitle,

    /**
     * Get the unsaved translation groups
     *
     * @return {Array}
     */
    getUnsavedTranslationGroups() {
      let groups = Object.keys(this.originalTranslations)
      let unsaved = []

      groups.forEach(group => {
        if (
          !isEqual(
            this.originalTranslations[group],
            this.translations['current'][group]
          )
        ) {
          unsaved.push(group)
        }
      })

      return unsaved
    },
    /**
     * Save translation group
     *
     * @param  {String} group
     *
     * @return {Void}
     */
    saveGroup(group) {
      this.groupIsBeingSaved = true
      Innoclapps.request()
        .put(
          `/translation/${this.locale}/${group}`,
          this.translations['current'][group]
        )
        .then(() => window.location.reload(true))
        .finally(() => setTimeout(() => (this.groupIsBeingSaved = false), 1000))
    },

    /**
     * Get translations info for a given locale
     *
     * @param  {String} locale
     *
     * @return {Void}
     */
    getTranslations(locale) {
      Innoclapps.request()
        .get('/translation/' + locale)
        .then(({ data }) => {
          this.translations = data
          var original = cloneDeep(data['current'])
          Object.freeze(original)
          this.originalTranslations = original
        })
    },

    /**
     * Create new locale
     *
     * @return {Void}
     */
    storeNewLocale() {
      this.localeForm.post('/translation').then(data => {
        this.locales.push(data.locale)
        this.locale = data.locale
        this.getTranslations(data.locale)
        this.$iModal.hide('new-locale')
      })
    },

    /**
     * Toggle the given group from translation
     *
     * @return {Void}
     */
    toggleGroup(group) {
      if (this.activeGroup === group) {
        this.deactivateGroup(group)
        return
      }

      this.activateGroup(group)
    },

    /**
     * Activate the group that is currently being translated
     *
     * @param  {String} group
     *
     * @return {Void}
     */
    activateGroup(group) {
      this.activeGroup = group
    },

    /**
     * Deactivate the group that is currently being translated
     *
     * @param  {String} group
     * @param  {Boolean} skipConfirmation
     */
    deactivateGroup(group, skipConfirmation = false) {
      const unsaved = this.getUnsavedTranslationGroups()
      const groupIsModified = unsaved.indexOf(group) > -1

      if (skipConfirmation || !groupIsModified) {
        this.activeGroup = null
        // Replace only when group group modified
        if (groupIsModified) {
          this.replaceOriginalTranslations(group)
        }
        return
      }

      this.$dialog
        .confirm({
          message: this.$t('settings.translator.changes_not_saved'),
          title: 'The group has unsaved translations!',
          confirmText: this.$t('app.discard_changes'),
        })
        .then(() => {
          this.activeGroup = null
          this.replaceOriginalTranslations(group)
        })
    },

    /**
     * Resets the current translation to it's original state
     */
    replaceOriginalTranslations(group) {
      this.translations['current'][group] = cloneDeep(
        this.originalTranslations[group]
      )
    },
  },
  created() {
    this.getTranslations(this.locale)
  },
}
</script>
