<script>
import SelectField from '@/components/Fields/SelectField'
export default {
  extends: SelectField,
  created() {
    this.selected = this.form[this.field.belongsToRelation]
  },
}
</script>
