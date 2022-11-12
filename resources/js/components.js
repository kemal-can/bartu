/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import Notifications from 'notiwind'

import AuthLogin from '@/views/Auth/Login'
import AuthPasswordEmail from '@/views/Auth/PasswordEmail'
import AuthPasswordReset from '@/views/Auth/PasswordReset'

import MigrateDatabase from '@/views/MigrateDatabase'

import ITabs from '@/components/UI/Tabs/Tabs'
import ITab from '@/components/UI/Tabs/Tab'
import ISlideover from '@/components/UI/Slideover'
import ISpinner from '@/components/UI/Spinner'
import IModal from '@/components/UI/Modal'
import IConfirmationDialog from '@/components/UI/ConfirmationDialog'
import IStepsCircle from '@/components/UI/Steps/StepsCircle'
import IStepCircle from '@/components/UI/Steps/StepCircle'
import IEmptyState from '@/components/UI/EmptyState'

import IPopover from '@/components/UI/Popover'
import ITable from '@/components/UI/Table'
import ILayout from '@/components/Layout'
import IBadge from '@/components/UI/Badge'
import ICard from '@/components/UI/Card/Card'
import ICardHeading from '@/components/UI/Card/CardHeading'
import ICardBody from '@/components/UI/Card/CardBody'
import ICardFooter from '@/components/UI/Card/CardFooter'
import IColorSwatches from '@/components/UI/ColorSwatches'
import IVerticalNavigation from '@/components/UI/VerticalNavigation/VerticalNavigation'
import IVerticalNavigationItem from '@/components/UI/VerticalNavigation/VerticalNavigationItem'
import IFormGroup from '@/components/UI/Form/Group'
import IFormLabel from '@/components/UI/Form/Label'
import IFormCheckbox from '@/components/UI/Form/Checkbox'
import IFormToggle from '@/components/UI/Form/Toggle'
import IFormRadio from '@/components/UI/Form/Radio'
import IFormError from '@/components/UI/Form/ErrorMessage'
import IFormText from '@/components/UI/Form/Text'
import IFormInput from '@/components/UI/Form/Input'
import IFormInputDropdown from '@/components/UI/Form/InputDropdown'
import IFormNumericInput from '@/components/UI/Form/NumericInput'
import ICustomSelect from '@/components/UI/CustomSelect/index'
import IFormSelect from '@/components/UI/Form/Select'
import IFormTextarea from '@/components/UI/Form/Textarea'
import IAvatar from '@/components/UI/Avatar'
import IAlert from '@/components/UI/Alert'
import IDropdown from '@/components/UI/Dropdown/Dropdown'
import IDropdownItem from '@/components/UI/Dropdown/DropdownItem'
import IDropdownButtonGroup from '@/components/UI/Dropdown/DropdownButtonGroup'
import IMinimalDropdown from '@/components/UI/Dropdown/MinimalDropdown'

import IButton from '@/components/UI/Buttons/Button'
import IButtonMinimal from '@/components/UI/Buttons/ButtonMinimal'
import IButtonIcon from '@/components/UI/Buttons/ButtonIcon'
import IButtonClose from '@/components/UI/Buttons/ButtonClose'
import IButtonGroup from '@/components/UI/Buttons/ButtonGroup'

import IOverlay from '@/components/UI/Overlay'
import IPopper from '@/components/Popper'
import ActionMessage from '@/components/UI/ActionMessage'
import Icon from '@/components/UI/Icon'
import IconPicker from '@/components/UI/IconPicker'

import FocusAbleFieldsGenerator from '@/components/Fields/FocusAbleFieldsGenerator'
import FieldsGenerator from '@/components/Fields/FieldsGenerator'
import DropdownSelect from '@/components/DropdownSelect'
import FormFieldsPlaceholder from '@/components/Loaders/FormFieldsPlaceholder'
import InputSearch from '@/components/InputSearch'

import CopyButton from '@/components/CopyButton'
import FormError from '@/components/Form/Error'
import Navbar from '@/components/TheNavbar'
import NavbarSeparator from '@/components/NavbarSeparator'
import Sidebar from '@/components/TheSidebar'

import DatePicker from '@/components/DateTimePicker'
import WebFormView from '@/views/WebForms/WebFormView'
import InvitationAcceptForm from '@/views/Users/InvitationAcceptForm'

import ActionDialog from '@/components/Actions/ActionsDialog'
import ProgressionChart from '@/components/Charts/ProgressionChart'
import PresentationChart from '@/components/Charts/PresentationChart'
import MyActivitiesCard from '@/views/Activity/MyActivitiesCard'
import CardWithTable from '@/components/Cards/CardWithTable'
import CardWithAsyncTable from '@/components/Cards/CardWithAsyncTable'
import Card from '@/components/Cards/Card'
import DealPresentationCard from '@/views/Deals/DealPresentationCard'
import PreviewModal from '@/components/PreviewModal'
import CompanyPreview from '@/views/Companies/Preview'
import DealPreview from '@/views/Deals/Preview'
import ContactPreview from '@/views/Contacts/Preview'

import {
  Dialog,
  DialogOverlay,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

export default function (app) {
  app.use(Notifications)

  app.component('invitation-accept-form', InvitationAcceptForm)
  app.component('web-form-view', WebFormView)
  app.component('date-picker', DatePicker)
  app.component('auth-login', AuthLogin)
  app.component('auth-password-email', AuthPasswordEmail)
  app.component('auth-password-reset', AuthPasswordReset)
  app.component('migrate-database', MigrateDatabase)

  app.component('copy-button', CopyButton)
  app.component('form-error', FormError)
  app.component('icon', Icon)
  app.component('navbar', Navbar)
  app.component('navbar-separator', NavbarSeparator)
  app.component('sidebar', Sidebar)

  app.component('action-dialog', ActionDialog)

  app.component('progression-chart', ProgressionChart)
  app.component('presentation-chart', PresentationChart)
  app.component('my-activities-card', MyActivitiesCard)
  app.component('card-with-table', CardWithTable)
  app.component('card-with-async-table', CardWithAsyncTable)
  app.component('card', Card)
  app.component('deal-presentation-card', DealPresentationCard)
  app.component('preview-modal', PreviewModal)
  app.component('company-preview', CompanyPreview)
  app.component('deal-preview', DealPreview)
  app.component('contact-preview', ContactPreview)
  app.component('i-popper', IPopper)

  app.component('focus-able-fields-generator', FocusAbleFieldsGenerator)
  app.component('fields-generator', FieldsGenerator)
  app.component('dropdown-select', DropdownSelect)
  app.component('form-fields-placeholder', FormFieldsPlaceholder)
  app.component('input-search', InputSearch)
  app.component('i-action-message', ActionMessage)

  app.component('i-tabs', ITabs)
  app.component('i-tab', ITab)
  app.component('i-dropdown', IDropdown)
  app.component('i-dropdown-item', IDropdownItem)
  app.component('i-minimal-dropdown', IMinimalDropdown)
  app.component('i-form-input-dropdown', IFormInputDropdown)
  app.component('i-dropdown-button-group', IDropdownButtonGroup)

  app.component('i-table', ITable)
  app.component('i-custom-select', ICustomSelect)
  app.component('i-overlay', IOverlay)
  app.component('i-popover', IPopover)
  app.component('i-empty-state', IEmptyState)
  app.component('i-form-toggle', IFormToggle)

  app.component('i-icon-picker', IconPicker)
  app.component('i-slideover', ISlideover)
  app.component('i-spinner', ISpinner)
  app.component('i-steps-circle', IStepsCircle)
  app.component('i-step-circle', IStepCircle)
  app.component('i-confirmation-dialog', IConfirmationDialog)
  app.component('i-modal', IModal)
  app.component('i-layout', ILayout)
  app.component('i-card', ICard)
  app.component('i-card-heading', ICardHeading)
  app.component('i-card-body', ICardBody)
  app.component('i-card-footer', ICardFooter)
  app.component('i-button', IButton)
  app.component('i-button-minimal', IButtonMinimal)
  app.component('i-button-icon', IButtonIcon)
  app.component('i-button-close', IButtonClose)
  app.component('i-button-group', IButtonGroup)
  app.component('i-color-swatches', IColorSwatches)
  app.component('i-vertical-navigation', IVerticalNavigation)
  app.component('i-vertical-navigation-item', IVerticalNavigationItem)
  app.component('i-form-group', IFormGroup)
  app.component('i-form-label', IFormLabel)
  app.component('i-form-error', IFormError)
  app.component('i-form-text', IFormText)
  app.component('i-form-input', IFormInput)
  app.component('i-form-numeric-input', IFormNumericInput)
  app.component('i-form-select', IFormSelect)
  app.component('i-form-textarea', IFormTextarea)
  app.component('i-form-checkbox', IFormCheckbox)
  app.component('i-form-radio', IFormRadio)
  app.component('i-avatar', IAvatar)

  app.component('i-alert', IAlert)
  app.component('i-badge', IBadge)

  app.component('Dialog', Dialog)
  app.component('DialogOverlay', DialogOverlay)
  app.component('DialogTitle', DialogTitle)
  app.component('TransitionRoot', TransitionRoot)
  app.component('TransitionChild', TransitionChild)
}
