<?php namespace Modules\Access\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Access\Models\RegistrationForm;

class RegistrationFormTransformer extends TransformerAbstract
{
    /**
     * @param RegistrationForm $form
     *
     * @return array
     * @throws \Laracodes\Presenter\Exceptions\PresenterException
     */
    public function transform(RegistrationForm $form): array
    {
        return [
            'id'   => $form->id,
            'form' => $form->present()->formData
        ];
    }
}

