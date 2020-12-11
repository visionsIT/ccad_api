<?php namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Agency\Presenters\ClientPresenter;
use Modules\Program\Models\Program;

class Client extends Model
{
    use SoftDeletes, Presentable;

    protected $presenter = ClientPresenter::class;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'agency_id',
        'contact_name',
        'contact_email',
        'logo',
        'accent_color'
    ];

    /**
     * @var array
     */
    protected $dates = [ 'deleted_at' ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function catalogues()
    {
        return $this->belongsToMany(Catalogue::class, 'client_catalogues');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function admins()
    {
        return $this->hasMany(ClientsAdmin::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function programs()
    {
        return $this->hasOne(Program::class);
    }


}
