<?php

namespace Database\Factories;

use App\Models\Blog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Paddle\Receipt;

class ReceiptFactory extends Factory
{

    protected $model = Receipt::class;

    public function definition()
    {
        return [
            'billable_id' => Blog::factory(),
            'billable_type' => Blog::class,
            'paddle_subscription_id' => rand(),
            'checkout_id' => Str::random(),
            'order_id' => Str::random(40), // unique
            'amount' => rand(1, 100),
            'tax' => rand(0, 20),
            'currency' => 'USD',
            'quantity' => rand(1, 5),
            'receipt_url' => $this->faker->unique()->url,
            'paid_at' => Carbon::createFromTimestamp($this->faker->unixTime()),
        ];
    }
}