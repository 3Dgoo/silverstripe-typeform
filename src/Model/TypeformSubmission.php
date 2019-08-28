<?php

namespace DNADesign\Typeform\Model;

use Page;
use SilverStripe\ORM\DataObject;

/**
 * @package typeform
 */
class TypeformSubmission extends DataObject
{
    private static $db = [
        'TypeformID' => 'Int',
        'DateStarted' => 'Datetime',
        'DateSubmitted' => 'Datetime',
    ];

    private static $has_one = [
        'Parent' => Page::class,
    ];

    private static $has_many = [
        'Answers' => TypeformSubmissionAnswer::class,
    ];

    private static $default_sort = 'ID DESC';

    private static $searchable_fields = [
        'ParentID',
    ];

    private static $summary_fields = [
        'CMSTitle',
    ];

    private static $field_labels = [
        'CMSTitle' => 'Title',
    ];

    private static $casting = [
        'Title' => 'Varchar',
    ];

    private static $table_name = 'TypeformSubmission';

    public function onAfterDelete()
    {
        parent::onAfterDelete();

        foreach ($this->Answers() as $answer) {
            $answer->delete();
        }
    }

    public function getCMSTitle()
    {
        return sprintf('%s - %s (%s)', $this->ID, $this->DateSubmitted, $this->Parent()->Title);
    }

    public function Title()
    {
        return $this->TypeformID;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function onBeforeDelete()
    {
        parent::onBeforeDelete();

        $deleted = new TypeformSubmissionDeleted();
        $deleted->TypeformID = $this->TypeformID;
        $deleted->ParentID = $this->ParentID;
        $deleted->write();
    }
}
