<?php namespace Modules\CommonSetting\Http\Services;

use Carbon\Carbon;
use Modules\CommonSetting\Repositories\EmailTemplatesRepository;
use Modules\Account\Models\Account;
use Modules\User\Models\UsersGroupList;
use DateTime;
use Modules\User\Models\ProgramUsers;
use DB;
use Modules\User\Models\PageVisits;
use Modules\Nomination\Models\UserNomination;
use Modules\Nomination\Models\ValueSet;
use Modules\Reward\Models\ProductOrder;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductsAccountsSeen;
use Modules\CommonSetting\Models\EmailTemplate;
/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class EmailService
{
    protected $email_repository;

    /**
     * PasswordsService constructor.
     *
     * @param AccountRepository $account_repository
     */
    public function __construct(EmailTemplatesRepository $email_repository)
    {
        $this->email_repository = $email_repository;
    }

    public function saveTemplateData($request) {

        return $this->email_repository->templateDataExists($request);

    }

    public function getEmailTemplateByID($tempate_id) {

        return $this->email_repository->getTemplateData($tempate_id);

    }


}
