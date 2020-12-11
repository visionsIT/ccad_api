<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Program;

class ProgramsTransformer extends TransformerAbstract
{
    /**
     * @param Program $program
     *
     * @return array
     */
    public function transform(Program $program): array
    {
        return [
            'id'                    => $program->id,
            'name'                  => $program->name,
            'status'                => $program->status,
            'internal_id'           => str_random(10), // Edit this later
            'reference'             => $program->reference,
            'agency_id'             => $program->agency_id,
            'client_id'             => $program->client_id,
            'currency_id'           => $program->currency_id,
            'theme'                 => $program->theme,
            'sent_from_email'       => $program->sent_from_email,
            'contact_from_email'    => $program->contact_from_email,
            'google_analytics_id'   => $program->google_analytics_id,
            'google_tag_manager'    => $program->google_tag_manager,
            'modules'               => unserialize($program->modules),
            'user_start_date'       => $program->user_start_date,
            'user_end_date'         => $program->user_end_date,
            'country_currency_rate' => $program->country_currency_rate,
            'points_per_currency'   => $program->points_per_currency,
//            'staging_password'      => $program->staging_password,
        ];
    }
}
