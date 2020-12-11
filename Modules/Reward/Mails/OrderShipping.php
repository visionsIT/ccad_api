<?php namespace Modules\Reward\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Account\Models\Account;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductOrder;
use Sichikawa\LaravelSendgridDriver\SendGrid;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;

class OrderShipping extends Mailable
{
    use SendGrid, SerializesModels;

    public $account;

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
                            'first_name' => $this->product_order->first_name,
                            'last_name'  => $this->product_order->last_name,
                            'city'       => $this->product_order->city,
                            'country'    => $this->product_order->country,
                            'name'     => $this->product_order->name,
                            'value'    => $this->product_order->value,
                        ],
                    ],
                ],
                'template_id'      => config('sendgrid.templates.order_shipping'),
            ] , SendgridTransport::SMTP_API_NAME);
    }
}