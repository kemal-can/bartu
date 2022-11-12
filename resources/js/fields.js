/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import TextField from '@/components/Fields/TextField'
import TextareaField from '@/components/Fields/TextareaField'
import IconPickerField from '@/components/Fields/IconPickerField'
import DomainField from '@/components/Fields/DomainField'
import BooleanField from '@/components/Fields/BooleanField'
import CheckboxField from '@/components/Fields/CheckboxField'
import RadioField from '@/components/Fields/RadioField'
import DateField from '@/components/Fields/DateField'
import DateTimeField from '@/components/Fields/DateTimeField'
import SelectField from '@/components/Fields/SelectField'
import SelectMultipleField from '@/components/Fields/SelectMultipleField'
import DropdownSelectField from '@/components/Fields/DropdownSelectField'
import GuestsSelectField from '@/components/Fields/GuestsSelectField'
import MailEditorField from '@/components/Fields/MailEditorField'
import EditorField from '@/components/Fields/EditorField'
import NumericField from '@/components/Fields/NumericField'
import BelongsToField from '@/components/Fields/BelongsToField'
import PipelineStageField from '@/components/Fields/PipelineStageField'
import TimezoneField from '@/components/Fields/TimezoneField'
import IntroductionField from '@/components/Fields/IntroductionField'
import ColorSwatchesField from '@/components/Fields/ColorSwatchesField'
import ReminderField from '@/components/Fields/ReminderField'
import PhoneField from '@/components/Fields/PhoneField'
import ActivityTypeField from '@/components/Fields/ActivityTypeField'
import ActivityDueDateField from '@/components/Fields/ActivityDueDateField'
import ActivityEndDateField from '@/components/Fields/ActivityEndDateField'
import LostReasonField from '@/components/Fields/LostReasonField'

export default function (app) {
  app.component('text-field', TextField)
  app.component('textarea-field', TextareaField)
  app.component('icon-picker-field', IconPickerField)
  app.component('domain-field', DomainField)
  app.component('boolean-field', BooleanField)
  app.component('checkbox-field', CheckboxField)
  app.component('radio-field', RadioField)
  app.component('date-field', DateField)
  app.component('date-time-field', DateTimeField)
  app.component('select-field', SelectField)
  app.component('select-multiple-field', SelectMultipleField)
  app.component('dropdown-select-field', DropdownSelectField)
  app.component('guests-select-field', GuestsSelectField)
  app.component('mail-editor-field', MailEditorField)
  app.component('editor-field', EditorField)
  app.component('numeric-field', NumericField)
  app.component('belongs-to-field', BelongsToField)
  app.component('pipeline-stage-field', PipelineStageField)
  app.component('timezone-field', TimezoneField)
  app.component('introduction-field', IntroductionField)
  app.component('color-swatches-field', ColorSwatchesField)
  app.component('reminder-field', ReminderField)
  app.component('phone-field', PhoneField)
  app.component('activity-type-field', ActivityTypeField)
  app.component('activity-due-date-field', ActivityDueDateField)
  app.component('activity-end-date-field', ActivityEndDateField)
  app.component('lost-reason-field', LostReasonField)
}
