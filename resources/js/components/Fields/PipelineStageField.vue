<script>
import BelongsToField from '@/components/Fields/BelongsToField'
import filter from 'lodash/filter'
export default {
  extends: BelongsToField,
  data: () => ({
    previousPipelineStage: null,
  }),
  methods: {
    /**
     * Set the pipeline stages
     *
     * @param {Array} options
     * @param {Boolean} internal
     */
    setOptions(options, internal) {
      // We set the options only via the event invoked
      // Because setOptions is executed after the event on the
      // initial field load, in this case, they event options are replaced
      if (internal === true) {
        this.options = options
      }
    },

    /**
     * Handle the pipeline_id changed event
     *
     * @param  {Object} pipeline
     *
     * @return {Void}
     */
    pipelineIdValueChangedHandler(pipeline) {
      this.setOptions(
        filter(this.field.options, [this.field.dependsOn, pipeline.id]),
        true
      )

      if (this.value && this.value[this.field.valueKey]) {
        // The pipeline id changed and the previous selected
        // stage does not belong to this pipeline id
        if (this.value.pipeline_id != pipeline.id) {
          let oldStage = this.value
          this.value = this.prepareValue(
            this.previousPipelineStage || pipeline.stages[0]
          )
          this.previousPipelineStage = oldStage
        }
      } else {
        // Set the first stage selected when there is no value
        this.value = this.prepareValue(pipeline.stages[0])
        // For form reset e.q. create and add another
        this.form.set(this.field.attribute, this.value)
      }
    },
  },
  created() {
    Innoclapps.$on(
      'field-pipeline_id-value-changed',
      this.pipelineIdValueChangedHandler
    )
    this.selected = this.form[this.field.belongsToRelation]
  },
  unmounted() {
    Innoclapps.$off(
      'field-pipeline_id-value-changed',
      this.pipelineIdValueChangedHandler
    )
  },
}
</script>
