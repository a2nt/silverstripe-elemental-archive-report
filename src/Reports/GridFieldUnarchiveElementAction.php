<?php

namespace Derralf\Elemental\ArchiveReport;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Control\Controller;
use SilverStripe\Versioned\Versioned;

class GridFieldUnarchiveElementAction implements GridField_ColumnProvider, GridField_ActionProvider
{

    public function augmentColumns($gridField, &$columns)
    {
        if(!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if($columnName == 'Actions') {
            return ['title' => ''];
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        if(!$record->canEdit()) return;

        $showField = false;

        if($record->isArchived()){
            $showField = true;
            $label = _t(__CLASS__ . '.Unarchive', 'Unarchive');
        }
        if($record->isOnLiveOnly()) {
            $showField = true;
            $label = _t(__CLASS__ . '.MakeDraft', 'Unlive');
        }

        if($showField){
            $field = GridField_FormAction::create(
                $gridField,
                'UnarchiveElementAction'.$record->ID,
                $label,
                "dounarchiveelementaction",
                ['RecordID' => $record->ID]
            );


            return $field->Field();
        }
    }


    public function getActions($gridField)
    {
        return ['dounarchiveelementaction'];
    }


    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        //Debug::show($arguments);
        //return;
        if($actionName == 'dounarchiveelementaction') {
            return $this->handleUnarchiveElement($arguments);
        }
    }


    public function handleUnarchiveElement($arguments) {
        $id = (int) $arguments["RecordID"];
        $item = Versioned::get_latest_version(BaseElement::class, $id);

        if(!$item){
            $message = "Error: Item with ID {$id} not found.";
            Controller::curr()->getResponse()->setStatusCode(
                400,
                $message
            );
            return;
        }

        if($item->isOnLiveOnly()) {
            $item->doUnpublish();
            $message = "Item with ID {$id} set to draft";
        }

        if($item->isArchived()) {
            // $message = "Item with ID {$id} is archived and will be restored.";
            //$oldReadingMode = Versioned::get_reading_mode();
            //Versioned::set_stage(Versioned::DRAFT);
            Versioned::set_stage(Versioned::DRAFT);
            $item->forceChange();
            $item->write();
            //Versioned::set_reading_mode($oldReadingMode);
            $message = "Item with ID {$id} was restored to draft stage";
            //Debug::show($message);
        }

        // output a success message to the user
        Controller::curr()->getResponse()->setStatusCode(
            200,
            $message
        );
    }

}
