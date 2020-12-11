<?php namespace Modules\Reward\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Account\Models\Account;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductOrder;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class OrderCancellation extends Mailable
{
    use SendGrid, SerializesModels;

    public $account, $product, $product_order;

    /**
     * OrderConfirmation constructor.
     *
     * @param ProductOrder $product_order
     * @param Account $account
     * @param Product $product
     */
    public function __construct(ProductOrder $product_order, Account $account, Product $product)
    {
        $this->product       = $product;
        $this->account       = $account;
        $this->product_order = $product_order;
    }


    public function build(): void
    {
        $this
            ->view([])
            ->to($this->account->email)
            ->from(config('sendgrid.emails.no-reply-email'))
            ->sendgrid([
                'personalizations' => [
                    [
                        'dynamic_template_data' => [
                            'message' => 'We have to cancel your order because the product is out of stock'
                        ],
                    ],
                ],
                'template_id'      => config('sendgrid.templates.order_cancellation'),
            ]);
    }
}