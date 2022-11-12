<template>
  <i-layout>
    <div class="mx-auto max-w-5xl">
      <i-card
        no-body
        :header="$t('api.personal_access_tokens')"
        :overlay="requestInProgress"
      >
        <template #actions>
          <i-button
            @click="showCreateTokenForm"
            icon="plus"
            v-show="totalTokens > 0"
            size="sm"
            >{{ $t('api.create_token') }}</i-button
          >
        </template>

        <i-table v-if="totalTokens > 0" class="-mt-px">
          <thead>
            <tr>
              <th class="text-left" v-t="'api.token_name'"></th>
              <th class="text-left" v-t="'api.token_last_used'"></th>
              <th class="text-left"></th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="token in tokens" :key="token.id">
              <!-- Client Name -->
              <td class="align-middle">
                {{ token.name }}
              </td>
              <td class="align-middle">
                {{
                  token.last_used_at
                    ? localizedDateTime(token.last_used_at)
                    : 'N/A'
                }}
              </td>
              <!-- Delete Button -->
              <td class="align-middle">
                <i-button
                  @click="revoke(token)"
                  variant="white"
                  icon="Trash"
                  size="sm"
                >
                  {{ $t('api.revoke_token') }}
                </i-button>
              </td>
            </tr>
          </tbody>
        </i-table>
        <i-card-body v-else>
          <i-empty-state
            @click="showCreateTokenForm"
            :title="$t('api.no_tokens')"
            :button-text="$t('api.create_token')"
            description="Start making API requests by creating a new token."
          />
        </i-card-body>
      </i-card>
      <i-modal
        @shown="() => $refs['create-token-name'].focus()"
        form
        @submit="store"
        @keydown="form.onKeydown($event)"
        size="sm"
        v-model:visible="showCreateTokenModal"
        :ok-disabled="form.busy"
        :ok-title="$t('app.create')"
        :cancel-title="$t('app.cancel')"
        :title="$t('api.create_token')"
      >
        <i-form-group :label="$t('api.token_name')" label-for="name" required>
          <i-form-input
            id="name"
            name="name"
            ref="create-token-name"
            v-model="form.name"
          />
          <form-error :form="form" field="name" />
        </i-form-group>
      </i-modal>
      <i-modal
        static-backdrop
        hide-footer
        v-model:visible="showAccessTokenModal"
        :title="$t('api.personal_access_token')"
      >
        <p
          class="mb-5 font-semibold text-warning-600"
          v-t="'api.after_token_created_info'"
        ></p>
        <p
          class="select-all break-all rounded-md border border-neutral-300 p-4 text-neutral-900 dark:text-neutral-200"
          v-text="plainTextToken"
        ></p>
      </i-modal>
    </div>
  </i-layout>
</template>
<script>
import Form from '@/components/Form/Form'

export default {
  data() {
    return {
      plainTextToken: null,
      showAccessTokenModal: false,
      showCreateTokenModal: false,
      requestInProgress: false,
      tokens: [],
      form: new Form({
        name: '',
      }),
    }
  },
  computed: {
    /**
     * Get the total number of token
     *
     * @return {Number}
     */
    totalTokens() {
      return this.tokens.length
    },
  },
  methods: {
    /**
     * Prepare the component.
     */
    prepareComponent() {
      this.getTokens()
    },

    /**
     * Get all of the personal access tokens for the user.
     */
    getTokens() {
      this.requestInProgress = true
      Innoclapps.request()
        .get('/personal-access-tokens')
        .then(response => {
          this.tokens = response.data
        })
        .finally(() => (this.requestInProgress = false))
    },

    /**
     * Show the form for creating new tokens.
     */
    showCreateTokenForm() {
      this.showCreateTokenModal = true
    },

    /**
     * Create a new personal access token.
     */
    store() {
      this.plainTextToken = null
      this.form.post('/personal-access-tokens').then(response => {
        this.form.reset()
        this.tokens.push(response.accessToken)
        this.showAccessToken(response.plainTextToken)
      })
    },

    /**
     * Show the given access token to the user.
     */
    showAccessToken(plainTextToken) {
      this.showCreateTokenModal = false
      this.plainTextToken = plainTextToken
      this.showAccessTokenModal = true
    },

    /**
     * Revoke the given token.
     */
    revoke(token) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('/personal-access-tokens/' + token.id)
          .then(response => {
            this.getTokens()
          })
      })
    },
  },
  /**
   * Prepare the component
   */
  mounted() {
    this.prepareComponent()
  },
}
</script>
