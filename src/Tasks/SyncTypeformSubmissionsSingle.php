<?php

namespace DNADesign\Typeform\Tasks;

use DNADesign\Typeform\Model\TypeformQuestion;
use DNADesign\Typeform\Model\TypeformSubmission;
use DNADesign\Typeform\Model\TypeformSubmissionAnswer;
use DNADesign\Typeform\Model\TypeformSubmissionDeleted;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * @package typeform
 */
class SyncTypeformSubmissionsSingle
{
    /**
     * @param string $formKey
     */
    public function __construct($formKey)
    {
        $this->formKey = $formKey;
    }

    /**
     * @param ITypeform $target
     * @param boolean $force
     *
     * @return array
     */
    public function syncComments(ITypeform $target, $force = false, $offset = 0)
    {
        // either now or 10 minutes.
        $results = [
            'total' => 0,
            'synced' => 0,
        ];

        $limit = 500;

        $since = $target->getLastTypeformImportedTimestamp();

        if (!$force) {
            if ($since) {
                $since = '&since=' . $since;
            }
        } else {
            $since = '';
        }

        $rest = new RestfulService('https://api.typeform.com/v0/form/', 0);
        $url =
            sprintf('%s?key=%s&completed=true&offset=0&limit=%s%s',
            $this->formKey,
            SiteConfig::current_site_config()->TypeformApiKey,
            $offset,
            $limit,
            $since
        );

        $response = $rest->request($url);

        if ($response && !$response->isError()) {
            $body = json_decode($response->getBody(), true);

            if (isset($body['stats'])) {
                $target->extend('updateTypeformStats', $body['stats']);
            }

            if (isset($body['questions'])) {
                $this->populateQuestions($body['questions'], $target, $results);
            }

            if (isset($body['responses'])) {
                $this->populateResponses($body['responses'], $target, $results, $force);
            }

            // if the number of responses are 500, then we assume we need to
            // sync another page.
            $body = json_decode($response->getBody());

            if ($body->stats->responses->total >= ($offset + $limit)) {
                $this->syncComments($target, $force, $offset + $limit);
            }
        } else {
            SS_Log::log($response->getBody(), SS_Log::WARN);
        }

        return $results;
    }

    public function populateQuestions($questions, $target, $results)
    {
        foreach ($questions as $question) {
            $existing = TypeformQuestion::get()->filter([
                'ParentID' => $target->ID,
                'Reference' => $question['id'],
            ])->first();

            if (!$existing) {
                $existing = TypeformQuestion::create();
                $existing->ParentID = $target->ID;
                $existing->Reference = $question['id'];
            }

            $existing->FieldID = $question['field_id'];

            if (isset($question['group']) && $question['group']) {
                $group = TypeformQuestion::get()->filter('Reference', $question['group'])->first();

                if ($group) {
                    $existing->GroupFieldID = $group->ID;
                }
            }

            $existing->Title = $question['question'];
            $existing->write();
        }
    }

    public function populateResponses($responses, $target, &$results, $force)
    {
        // assumes comments don't update.
        foreach ($responses as $response) {
            $results['total']++;

            $deleted = TypeformSubmissionDeleted::get()->filter([
                'TypeformID' => $response['id'],
                'ParentID' => $target->ID,
            ]);

            if ($deleted->count() > 0 && !$force) {
                continue;
            }

            $existing = TypeformSubmission::get()->filter([
                'TypeformID' => $response['id'],
                'ParentID' => $target->ID,
            ]);

            if ($existing->count() > 0) {
                continue;
            }
            $results['synced']++;

            // check to make sure it hasn't been deleted
            $submission = TypeformSubmission::create();

            $submission->TypeformID = $response['id'];
            $submission->DateStarted = date('Y-m-d H:i:s', strtotime($response['metadata']['date_land'] . ' UTC'));
            $submission->DateSubmitted = date('Y-m-d H:i:s', strtotime($response['metadata']['date_submit'] . ' UTC'));

            $submission->ParentID = $target->ID;
            $submission->write();

            if (isset($response['answers'])) {
                foreach ($response['answers'] as $field => $value) {
                    $question = TypeformQuestion::get()->filter([
                            'Reference' => $field,
                        ])->first();

                    if (!$question) {
                        $question = TypeformQuestion::create();
                        $question->ParentID = $target->ID;
                        $question->Reference = $reference;
                        $question->write();
                    }

                    $answer = TypeformSubmissionAnswer::create();
                    $answer->Label = $question->Title;
                    $answer->QuestionID = $question->ID;
                    $answer->SubmissionID = $submission->ID;
                    $answer->Value = $value;
                    $answer->write();
                }
            }

            $submission->extend('onAfterAnswersSynced');
        }
    }
}
