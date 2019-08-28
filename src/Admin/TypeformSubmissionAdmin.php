<?php

namespace DNADesign\Typeform\Admin;

use DNADesign\Typeform\Model\TypeformQuestion;
use DNADesign\Typeform\Model\TypeformSubmission;
use DNADesign\Typeform\Model\TypeformSubmissionDeleted;
use SilverStripe\Admin\ModelAdmin;

/**
 * @package typeform
 */
class TypeformSubmissionAdmin extends ModelAdmin
{
    private static $managed_models = [
        TypeformSubmission::class,
        TypeformQuestion::class,
        TypeformSubmissionDeleted::class,
    ];

    private static $menu_title = 'Typeform';

    private static $url_segment = 'typeform';

    public $showImportForm = false;
}
