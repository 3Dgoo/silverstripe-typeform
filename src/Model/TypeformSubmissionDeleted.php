<?php

namespace DNADesign\Typeform\Model;

use Page;
use SilverStripe\ORM\DataObject;

/**
 * @package typeform
 */
class TypeformSubmissionDeleted extends DataObject
{
    private static $db = [
        'TypeformID' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Parent' => Page::class,
    ];

    private static $summary_fields = [
        'TypeformID',
        'Parent.Title',
    ];

    private static $field_labels = [
        'TypeformID' => 'Typeform ID',
        'Parent.Title' => 'Parent',
    ];

    private static $table_name = 'TypeformSubmissionDeleted';
}
