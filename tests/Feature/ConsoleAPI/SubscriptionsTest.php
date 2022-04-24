<?php
namespace Tests\Feature\ConsoleAPI;

use App\Exceptions\TrustedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{

    use RefreshDatabase;

    private function callEndpoint($method, $data = null) {
        return $this->call($method, '/api/console/v0/blog/test/subscription', $data);
    }

    /** 
     * PayLink creations
     **/
    public function testPayLinkCreationSuccess() {

        $response = $this->callEndpoint('post', [
            'plan' => 'team',
            'frequency' => 'monthly',
            'quantity' => 3
        ]);

        $response->assertOk()->assertJson(function (AssertableJson $json) {
            $json->whereType('payLink', 'string');
        });

    }
    public function testPayLinkInvalidPlan() {
        $this->callEndpoint('post', ['plan' => "invalid", 'frequency' => 'monthly', 'quantity' => 3])
            ->assertStatus(400);
    }
    public function testPayLinkInvalidFrequency() {
        $this->callEndpoint('post', ['plan' => "team", 'frequency' => 'annual', 'quantity' => 3])
            ->assertStatus(400);
    }
    public function testPayLinkProMonthlyBilling() {
        $this->callEndpoint('post', ['plan' => "pro", 'frequency' => 'monthly', 'quantity' => 1])
            ->assertStatus(400);
    }
    public function testPayLinkInvalidQuantity() {
        $this->callEndpoint('post', ['plan' => "team", 'frequency' => 'monthly', 'quantity' => 1])
            ->assertStatus(400);
    }
    public function testPayLinkInvalidQuantityHigh() {
        $this->callEndpoint('post', ['plan' => "team", 'frequency' => 'monthly', 'quantity' => 100])
            ->assertStatus(400);
    }
    public function testPayLinkInvalidParams() {
        $this->callEndpoint('post', [])
            ->assertStatus(400);
    }

}
