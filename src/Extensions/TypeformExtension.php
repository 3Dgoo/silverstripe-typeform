<?php

namespace DNADesign\Typeform\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * @package silverstripe-typeform
 */
class TypeformExtension extends DataExtension
{
    private static $db = [
        'TypeformKey' => 'Varchar',
        'TypeformURL' => 'Varchar',
        'TypeformImported' => 'Datetime',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Typeform', [
            TextField::create('TypeformURL', 'Typeform URL'),
            $key = TextField::create('TypeformKey', 'Typeform UID'),
        ]);

        $key->setDescription('The UID of a typeform is found at the end of its URL');
    }

    public function getTypeformUid()
    {
        return $this->owner->dbObject('TypeformKey')->getValue();
    }

    public function getLastTypeformImportedTimestamp()
    {
        return $this->owner->dbObject('TypeformImported')->Format('U');
    }

    public function updateLastTypeformImportedTimestamp()
    {
        $this->owner->TypeformImported = DBDatetime::now();
        $this->owner->write();
    }
}
