<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Access\Models\AccessType;
use Modules\Access\Models\RegistrationForm;
use Modules\Agency\Models\{Agency, Client};
use Modules\Program\Presenters\ProgramPresenter;
use Modules\User\Models\ProgramUsers;

class Program extends Model
{
    use SoftDeletes, Presentable;

    protected $presenter = ProgramPresenter::class;

    protected $fillable = [ 'name', 'reference', 'agency_id', 'client_id', 'currency_id', 'theme', 'contact_from_email', 'user_start_date',
                            'staging_password', 'status', 'sent_from_email', 'google_analytics_id', 'google_tag_manager', 'user_end_date', 'modules',
                            'country_currency_rate', 'points_per_currency', 'budget_status'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function domains()
    {
        return $this->hasMany(ProgramsDomain::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function points()
    {
        return $this->hasMany(ProgramsPoint::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function points_expiry()
    {
        return $this->hasOne(ProgramsPointsExpiry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function points_budget()
    {
        return $this->hasOne(ProgramsPointsBudget::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accessType()
    {
        return $this->hasOne(AccessType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function registrationForm()
    {
        return $this->hasOne(RegistrationForm::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(ProgramUsers::class);
    }
}
