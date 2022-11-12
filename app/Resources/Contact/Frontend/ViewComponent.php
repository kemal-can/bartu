<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Resources\Contact\Frontend;

use JsonSerializable;
use App\Http\View\FrontendComposers\Tab;
use App\Http\View\FrontendComposers\HasTabs;
use App\Http\View\FrontendComposers\Section;
use App\Http\View\FrontendComposers\Component;
use App\Http\View\FrontendComposers\HasSections;

class ViewComponent extends Component implements JsonSerializable
{
    use HasTabs, HasSections;

    /**
     * Create new ViewComponent instance.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Build the component data
     *
     * @return array
     */
    public function build() : array
    {
        return [
            'tabs'     => static::$tabs,
            'sections' => $this->mergeSections(
                settings('contact_view_sections') ?: []
            ),
        ];
    }

    /**
     * Init the component data
     *
     * @return void
     */
    protected function init() : void
    {
        static::registerTabs([
            Tab::make('timeline', 'timeline-tab'),
            Tab::make('activities', 'activity-tab'),
            Tab::make('emails', 'emails-tab'),
            Tab::make('calls', 'calls-tab'),
            Tab::make('notes', 'notes-tab'),
        ]);

        static::registerSections([
            Section::make('details', 'details-section')->heading(__('app.record_view.sections.details')),
            Section::make('deals', 'deals-section')->heading(__('deal.deals')),
            Section::make('companies', 'companies-section')->heading(__('company.companies')),
            Section::make('media', 'media-section')->heading(__('app.attachments')),
        ]);
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->build();
    }
}
