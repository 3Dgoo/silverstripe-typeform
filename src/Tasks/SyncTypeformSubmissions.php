<?php

namespace DNADesign\Typeform\Tasks;

use DNADesign\Typeform\Model\TypeformSubmission;
use Page;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;

/**
 * Connects with the provided Typeform API key and fetches the responses to
 * cache locally within SilverStripe.
 *
 * Operation is via a cron job on the server which is scheduled to run once an
 * hour. Comments for a single form are synced through the
 * {@link SyncTypeformSubmissionsSingle} class.
 *
 * @package typeform
 */
class SyncTypeformSubmissions extends BuildTask
{
    private static $typeform_classes = [
        Page::class,
    ];

    public function run($request)
    {
        increase_time_limit_to();
        increase_memory_limit_to();

        $formId = $request->getVar('form');
        $force = $request->getVar('force') || false;

        if ($request->getVar('delete') && Director::isDev()) {
            $submissions = TypeformSubmission::get();

            if ($formId) {
                $submissions = $submissions->filter('ParentID', $formId);
            }

            foreach ($submissions as $submission) {
                $submission->delete();
            }
        }

        foreach ($this->config()->typeform_classes as $class) {
            $forms = DataObject::get($class);

            if (Director::is_cli()) {
                echo 'Syncing ' . $class . " forms\n";
            } else {
                echo '<p>Syncing ' . $class . ' forms</p>';
            }

            if (!$formId) {
                if (Director::is_cli()) {
                    echo $forms->count() . " found\n";
                } else {
                    echo '<p>' . $forms->count() . ' found</p>';
                }
            }

            foreach ($forms as $form) {
                $key = null;

                if ($form->hasMethod('getTypeformUid')) {
                    $key = $form->getTypeformUid();
                }

                if ($key && $formId && $form->ID !== $formId) {
                    if (Director::is_cli()) {
                        echo sprintf("* Skipping %s\n", $form->Title);
                    } else {
                        echo sprintf('<li>Skipping %s</li>', $form->Title);
                    }

                    continue;
                }

                if ($key) {
                    $fetch = new SyncTypeformSubmissionsSingle($key);
                    $results = $fetch->syncComments($form, $force);

                    $total = $results['total'];
                    $synced = $results['synced'];

                    if (Director::is_cli()) {
                        echo sprintf("* %d new synced submissions out of %d total for %s\n", $synced, $total, $form->Title);
                    } else {
                        echo sprintf('<li>%d new synced submissions out of %d for %s</li>', $synced, $total, $form->Title);
                    }
                } else {
                    if (Director::is_cli()) {
                        echo sprintf("* No valid key for %s\n", $form->Title);
                    } else {
                        echo sprintf('<li>No valid key for %s</li>', $form->Title);
                    }
                }
            }
        }
    }
}
