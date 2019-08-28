<?php

namespace DNADesign\Typeform\Model;

use SilverStripe\ORM\DataObject;

/**
 * @package typeform
 */
class TypeformQuestion extends DataObject
{
    private static $db = [
        'Reference' => 'Varchar(255)',
        'Title' => 'Varchar(255)',
        'CustomTitle' => 'Varchar(255)',
        'FieldID' => 'Varchar',
        'ParentID' => 'Int',
    ];

    private static $has_one = [
        'GroupField' => TypeformQuestion::class,
    ];

    private static $has_many = [
        'GroupedChildren' => TypeformQuestion::class,
        'Answers' => TypeformSubmissionAnswer::class,
    ];

    private static $summary_fields = [
        'ID',
        'Title',
        'FieldID',
        'CustomTitle',
        'GroupField.Title',
    ];

    private static $field_labels = [
        'GroupField.Title' => 'Group Field',
    ];

    private static $table_name = 'TypeformQuestion';
}
