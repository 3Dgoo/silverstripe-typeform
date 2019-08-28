<?php

namespace DNADesign\Typeform\Model;

use SilverStripe\ORM\DataObject;

/**
 * @package typeform
 */
class TypeformSubmissionAnswer extends DataObject
{
    private static $db = [
        'Value' => 'Text',
        'Label' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Submission' => TypeformSubmission::class,
        'Question' => TypeformQuestion::class,
    ];

    private static $table_name = 'TypeformSubmissionAnswer';
}
