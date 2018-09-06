<?php

namespace Derralf\Elemental\ArchiveReport;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataList;
use SilverStripe\Reports\Report;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

class ElementsInArchiveReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.ReportTitle', 'Archived content blocks');
    }

    public function description()
    {
        return _t(__CLASS__ . '.ReportDescription', 'Unarchive and "unlive" Elements');
    }

    public function sourceRecords($params = [])
    {
        /** @var DataList $elements */
        //$elements = BaseElement::get()->exclude(['ClassName' => BaseElement::class]);

        $elements = Versioned::get_including_deleted(BaseElement::class);

        $elements = $elements->filterByCallback(function ($Item) {
            // Doesn't exist on either stage or live
            return ($Item->isArchived() || $Item->isOnLiveOnly());
        });
        return $elements;

    }

    public function columns()
    {
        return [
            'Icon' => [
                'title' => '',
                'formatting' => function ($value, BaseElement $item) {
                    return $item->getIcon();
                },
            ],
            'Title' => [
                'title' => _t(__CLASS__ . '.Title', 'Title'),
                'formatting' => function ($value, BaseElement $item) {
                    $value = $item->Title;
                    if (!empty($value)) {
                        return $value;
                    }
                    return '<span class="element__note">' . _t(__CLASS__ . '.None', 'None') . '</span>';
                },
            ],
            'Summary' => [
                'title' => _t(__CLASS__ . '.Summary', 'Summary'),
                'casting' => 'HTMLText->RAW',
                'formatting' => function ($value, BaseElement $item) {
                    return $item->getSummary();
                },
            ],
            'Type' => [
                'title' => _t(__CLASS__ . '.Type', 'Type'),
                'formatting' => function ($value, BaseElement $item) {
                    return $item->getTypeNice();
                },
            ],
            'Page.Title' => [
                'title' => _t(__CLASS__ . '.Page', 'Page'),
                'formatting' => function ($value, BaseElement $item) {
                    if($value && !$item->isArchived() && !$item->isOnLiveOnly()) {
                        return $this->getEditLink($value, $item);
                    }
                    return $item->getPageTitle();
                },
            ]
        ];
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $ReportField = $fields->dataFieldByName('Report');
        $ReportFieldConfig = $ReportField->getConfig();
        $ReportFieldConfig->addComponent(new GridFieldUnarchiveElementAction());

        return $fields;
    }

    /**
     * Helper method to return the link to edit an element
     *
     * @param string $value
     * @param BaseElement $item
     * @return string
     */
    protected function getEditLink($value, $item)
    {
        return sprintf(
            '<a class="grid-field__link" href="%s" title="%s">%s</a>',
            $item->CMSEditLink(),
            //'/admin/huhu/',
            $value,
            $value
        );
    }

    protected function getUnarchiveLink($value, $item)
    {
        return sprintf(
            '<a class="grid-field__link" href="%s" title="%s">%s</a>',
            //$item->CMSEditLink(),
            '/admin/huhu/',
            $value,
            _t(__CLASS__ . '.Unarchive', 'Unarchive')
        );
    }



}

