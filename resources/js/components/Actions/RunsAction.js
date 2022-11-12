/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import FieldsCollection from '@/services/FieldsCollection'
import castArray from 'lodash/castArray'
import { throwConfetti } from '@/utils'

let props = {
  actionRequestQueryString: {
    type: Object,
    default: () => {},
  },
  actions: {
    required: true,
    type: Array,
    default() {
      return []
    },
  },
  resourceName: {
    type: String,
    required: true,
  },
  view: {
    default: 'update',
    validator: function (value) {
      return ['update', 'index'].indexOf(value) !== -1
    },
  },
  ids: {
    type: [Number, String, Array],
    required: true,
  },
}

const mixin = {
  props: {
    ...props,
  },
  data: () => ({
    selectedAction: null,
    actionRunInProgress: false,
  }),
  computed: {
    /**
     * Get the current action ids
     *
     * @return {Array}
     */
    actionIds() {
      return castArray(this.ids)
    },

    /**
     * Action run endpoint
     *
     * @return {Void}
     */
    endpoint() {
      return `${this.resourceName}/actions/${this.selectedAction.uriKey}/run`
    },
  },
  methods: {
    /**
     * Run / execute action
     *
     * @return {VOid}
     */
    run() {
      if (!this.selectedAction) {
        return
      }

      if (!this.selectedAction.withoutConfirmation) {
        return this.showDialog()
      }

      this.actionRunInProgress = true

      Innoclapps.request({
        method: 'post',
        data: {
          ids: this.actionIds,
          ...this.actionRequestQueryString,
        },
        url: this.endpoint,
      })
        .then(({ data }) => this.handleResponse(data))
        .finally(() => (this.actionRunInProgress = false))
    },

    /**
     * Show action dialog
     *
     * @return {Void}
     */
    showDialog() {
      const dialogOptions = {
        component: 'action-dialog',
        title: this.selectedAction.name,
        message: this.selectedAction.message,
        ids: this.actionIds,
        endpoint: this.endpoint,
        action: this.selectedAction,
        queryString: this.actionRequestQueryString,
        fields: this.selectedAction.fields
          ? new FieldsCollection(this.cleanObject(this.selectedAction.fields))
          : null,
      }

      this.$dialog
        .confirm(dialogOptions)
        .then(dialog => this.handleResponse(dialog.response))
        // If canceled, set selectedAction to null because
        // when not setting the selectedAction to null will
        // not trigger change if the user click again on the same action
        .catch(() => (this.selectedAction = null))
    },

    /**
     * Handle executed action response
     *
     * @param  {Obejct} response
     *
     * @return {Void}
     */
    handleResponse(response) {
      if (response.openInNewTab) {
        window.open(response.openInNewTab, '_blank')
      } else {
        if (response.error) {
          Innoclapps.error(response.error)
        } else if (response.success) {
          Innoclapps.success(response.success)
        } else if (response.info) {
          Innoclapps.info(response.info)
        } else if (response.confetti) {
          throwConfetti()
        }

        // Set the action ids and response to be included in the event
        let payload = Object.assign({}, this.selectedAction, {
          ids: this.actionIds,
          response: response,
          resourceName: this.resourceName,
        })

        // Global event
        Innoclapps.$emit('action-executed', payload)

        // Local event for e.q. used in table refresh
        this.$emit('run', payload)
      }

      this.selectedAction = null
    },
  },
}

export { mixin as default, props as props }
