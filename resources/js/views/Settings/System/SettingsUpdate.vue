<template>
  <i-card class="mb-3">
    <div class="my-1 flex flex-col sm:flex-row sm:items-center">
      <i-form-label for="purchase-key" class="mb-1 shrink-0 sm:mb-0 sm:mr-4">
        {{ $t('app.purchase_key') }}
      </i-form-label>
      <i-form-input
        id="purchase-key"
        :placeholder="$t('app.enter_purchase_key')"
        v-model="updateData.purchase_key"
      ></i-form-input>
    </div>
  </i-card>
  <i-overlay
    :show="(!purchaseKeyIsValid || !passesZipRequirement) && componentReady"
  >
    <template #overlay v-if="!purchaseKeyIsValid">{{
      $t('app.enter_purchase_key')
    }}</template>
    <template #overlay v-if="!passesZipRequirement">
      {{ $t('update.update_zip_is_required') }}
    </template>
    <i-card :header="$t('update.system')" class="mb-3">
      <i-overlay :show="!componentReady">
        <div
          :class="{
            'blur-sm':
              (!purchaseKeyIsValid || !passesZipRequirement) && componentReady,
          }"
        >
          <div v-if="updateData.is_new_version_available">
            <div
              class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0"
            >
              <div
                class="flex-1 rounded bg-warning-100 p-2 px-2 py-3 text-center text-warning-700"
              >
                <h4 class="font-medium" v-t="'update.installed_version'"></h4>
                <h5 v-text="updateData.installed_version"></h5>
              </div>
              <div
                class="flex-1 rounded bg-success-100 p-2 px-2 py-3 text-center text-success-700"
              >
                <h4 class="font-medium" v-t="'update.latest_version'"></h4>
                <h5 v-text="updateData.latest_available_version"></h5>
              </div>
            </div>
          </div>
          <div v-else>
            <h4
              class="text-center text-lg font-semibold text-neutral-800 dark:text-neutral-100"
              v-show="componentReady"
            >
              <icon
                icon="EmojiHappy"
                class="m-auto mb-2 h-10 w-10 text-success-500"
              />
              {{ $t('update.not_available') }}
            </h4>
            <p
              v-show="componentReady"
              class="text-center text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'update.using_latest_version'"
            />
          </div>
        </div>
      </i-overlay>
      <template #footer>
        <div class="flex justify-end">
          <i-button
            @click="update"
            variant="success"
            :disabled="
              !canPerformUpdate ||
              updateInProgress ||
              patchBeingApplied !== false
            "
          >
            {{ updateButtonText }}
          </i-button>
        </div>
      </template>
    </i-card>
  </i-overlay>
  <i-overlay
    :show="(!purchaseKeyIsValid || !passesZipRequirement) && componentReady"
  >
    <template #overlay v-if="!purchaseKeyIsValid || !passesZipRequirement">{{
      !purchaseKeyIsValid
        ? $t('app.enter_purchase_key')
        : $t('update.patch_zip_is_required')
    }}</template>

    <i-card :header="$t('update.patches')" no-body>
      <i-overlay :show="!componentReady">
        <div
          :class="{
            'blur-sm':
              (!purchaseKeyIsValid || !passesZipRequirement) && componentReady,
          }"
        >
          <ul
            class="divide-y divide-neutral-200 dark:divide-neutral-700"
            v-if="hasPatches"
          >
            <li
              class="px-4 py-4 sm:px-6"
              v-for="(patch, index) in sortedPatches"
              :key="patch.token"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p
                    v-html="patch.description"
                    class="text-sm font-medium text-neutral-800 dark:text-neutral-100"
                  />
                  <i-badge
                    class="mr-1"
                    v-if="patch.isApplied"
                    variant="success"
                    v-t="'update.patch_applied'"
                  />
                  <i-badge variant="neutral" v-text="patch.token"></i-badge>
                  <small
                    class="ml-2.5 text-neutral-500 dark:text-neutral-300"
                    >{{ localizedDateTime(patch.date) }}</small
                  ><br />
                </div>
                <div class="flex">
                  <a
                    :href="
                      '/patches/' + patch.token + '/' + updateData.purchase_key
                    "
                    class="link mr-3 mt-2"
                    :class="{
                      'pointer-events-none opacity-70':
                        index > 0 ||
                        patchBeingApplied !== false ||
                        !purchaseKeyIsValid ||
                        !passesZipRequirement ||
                        updateInProgress ||
                        patch.isApplied,
                    }"
                  >
                    <icon icon="DocumentDownload" class="h-5 w-5" />
                  </a>
                  <span
                    class="inline-block"
                    tabindex="-1"
                    v-i-tooltip="
                      index === 0 || patch.isApplied
                        ? null
                        : $t('update.apply_oldest_first')
                    "
                  >
                    <i-button
                      size="sm"
                      :disabled="
                        index > 0 ||
                        patchBeingApplied !== false ||
                        !purchaseKeyIsValid ||
                        !passesZipRequirement ||
                        updateInProgress ||
                        patch.isApplied
                      "
                      @click="applyPatch(patch.token, index)"
                    >
                      {{
                        patchBeingApplied === index
                          ? $t('update.update_in_progress')
                          : $t('app.apply')
                      }}
                    </i-button>
                  </span>
                </div>
              </div>
            </li>
          </ul>
          <i-card-body v-else>
            <p
              class="text-center text-sm text-neutral-500 dark:text-neutral-300"
              v-show="componentReady"
              v-t="'update.no_patches'"
            ></p>
          </i-card-body>
        </div>
      </i-overlay>
    </i-card>
  </i-overlay>
</template>
<script>
import orderBy from 'lodash/orderBy'
export default {
  data: () => ({
    passesZipRequirement: Innoclapps.config.requirements.zip,
    updateData: {},
    patches: [],
    updateInProgress: false,
    patchBeingApplied: false,
    componentReady: false,
  }),
  computed: {
    /**
     * Get the patches properly sorted
     *
     * @return {Array}
     */
    sortedPatches() {
      return orderBy(
        this.patches.map(patch => {
          // For date sorting
          patch._date = new Date(patch.date)

          return patch
        }),
        ['isApplied', '_date'],
        ['asc', 'asc']
      )
    },

    /**
     * Indicates whether there are patches available
     *
     * @return {Boolean}
     */
    hasPatches() {
      return this.patches.length > 0
    },

    /**
     * The update button text
     *
     * @return {String}
     */
    updateButtonText() {
      if (this.updateInProgress) {
        return this.$t('update.update_in_progress')
      }

      return this.$t('update.perform')
    },

    /**
     * Indicates whether the purchase key is valid
     *
     * @return {Boolean}
     */
    purchaseKeyIsValid() {
      // Valid purchase key test
      let re = new RegExp(
        '[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}',
        'i'
      )

      return re.test(this.updateData.purchase_key)
    },

    /**
     * Indicates whether the user can perform an update
     *
     * @return {Boolean}
     */
    canPerformUpdate() {
      return (
        this.updateData.is_new_version_available &&
        this.purchaseKeyIsValid &&
        this.passesZipRequirement
      )
    },
  },
  methods: {
    /**
     * Handle the update error response
     *
     * @param  {Object} response
     *
     * @return {Void}
     */
    handleUpdateErrorResponse(response) {
      if (response.data === 'Incorrect files permissions.') {
        window.location.href = '/errors/permissions'
      } else {
        Innoclapps.error(response.data)
      }
    },

    /**
     * Perform application update
     *
     * @return {Void}
     */
    update() {
      this.updateInProgress = true
      Innoclapps.request()
        .post('/update/' + this.updateData.purchase_key)
        .then(({ data }) => {
          window.location.reload()
        })
        .catch(({ response }) => this.handleUpdateErrorResponse(response))
        .finally(() => (this.updateInProgress = false))
    },

    /**
     * Apply the given patch
     *
     * @param  {String} token
     *
     * @return {Void}
     */
    applyPatch(token, index) {
      this.patchBeingApplied = index

      Innoclapps.request()
        .post(`/patches/${token}/${this.updateData.purchase_key}`)
        .then(({ data }) => {
          window.location.reload()
        })
        .catch(({ response }) => this.handleUpdateErrorResponse(response))
        .finally(() => (this.patchBeingApplied = false))
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      Promise.all([
        Innoclapps.request().get('/update'),
        Innoclapps.request().get('/patches'),
      ])
        .then(values => {
          this.updateData = values[0].data
          this.patches = values[1].data
        })
        .catch(({ response }) => this.handleUpdateErrorResponse(response))
        .finally(() => (this.componentReady = true))
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
