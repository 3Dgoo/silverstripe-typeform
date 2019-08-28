<?php

namespace DNADesign\Typeform\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

/**
 * @package silverstripe-typeform
 */
class TypeformSiteConfigExtension extends DataExtension
{
    private static $db = [
        'TypeformApiKey' => 'Varchar',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Typeform', [
            $key = TextField::create('TypeformApiKey'),
        ]);

        $key->setDescription('Typeform API key');
    }
}
