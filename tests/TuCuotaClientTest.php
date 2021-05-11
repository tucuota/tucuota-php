<?php

namespace Tests;

// Use in production
use PHPUnit\Framework\TestCase;

use TuCuota\TuCuota;
use TuCuota\Exceptions\TucuotaException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use TuCuota\Exceptions\TucuotaPermissionsException;

class TuCuotaClientTest extends TestCase
{
    const KEY = "APIKEY";

    protected $client;
    protected $mockHandler;

    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();

        $this->client = new TuCuota(self::KEY);

        // Guzzle with mockHandler
        $this->client->client = new Client([
            'base_uri' => $this->client->base_uri,
            'timeout'  => 15.0,
            'handler' => $this->mockHandler,
        ]);
    }


    public function testItCanCreateInstanceForProduction()
    {
        $this->assertInstanceOf(TuCuota::class, $this->client);
        $this->assertEquals(self::KEY, $this->client->apiKey);
        $this->assertEquals("production", $this->client->environment);
        $this->assertEquals("https://tucuota.com/api/", $this->client->base_uri);
    }

    public function testItCanCreateInstanceForSandbox()
    {
        $this->client = new TuCuota(self::KEY, "sandbox");
        $this->assertInstanceOf(TuCuota::class, $this->client);
        $this->assertEquals(self::KEY, $this->client->apiKey);
        $this->assertEquals("sandbox", $this->client->environment);
        $this->assertEquals("https://sandbox.tucuota.com/api/", $this->client->base_uri);
    }

    public function testItFailsWithUnknownEnviroment()
    {
        $this->expectException(TucuotaException::class);
        $this->expectExceptionMessage("Invalid environment");

        $this->client = new TuCuota(self::KEY, "other");
    }

    public function test200()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__ . '/fixtures/payment.store.json')));
        $request = $this->client->post('payment/PYvQwEssV56Dp2', [
            "amount" => 100,
            "description" => "Unique payment",
            "customer_id" => "CSr7Dg3LkDP2",
            "payment_method_number" => "4024007127322104"
        ]);
        $this->assertEquals([
            'status' => 200,
            'message' => null,
            'data' => [
                'id' => 'PYnZRPX89R0V',
                'object' => 'payment',
                'amount' => 100,
                'currency' => 'ARS',
                'description' => 'Unique payment',
                'status' => 'pending_submission',
                'response_message' => '',
                'paid' => false,
                'retryable' => false,
                'livemode' => false,
                'created_at' => '2019-11-27T17:30:42-0300',
                'charge_date' => '2019-11-27',
                'effective_charged_date' => null,
                'estimated_accreditation_date' => null,
                'updated_at' => '2019-11-27T17:30:42-0300',
                'updated_status' => null,
                "customer" => [
                    "id" => "CSr7Dg3LkDP2",
                    "object" => "customer",
                    "name" => "John",
                    "email" => "john@doe.com",
                    "identification_type" => "",
                    "identification_number" => "",
                    "mobile_number" => "",
                    "metadata" => null,
                    "livemode" => false,
                    "updated_at" => "2019-11-27T17:28:33-0300",
                    "created_at" => "2019-11-27T17:28:33-0300",
                    "deleted_at" => null
                ],
                "subscription" => null,
                "subscription_payment_number" => null,
                "gateway" => "GW1L49Jr79RW",
                "payment_method" => [
                    "id" => "PMPVWR55pkgj",
                    "object" => "payment_method",
                    "last_four_digits" => "2104",
                    "brand" => "visa-credito",
                    "bank" => "",
                    "livemode" => false,
                    "updated_at" => "2019-11-27T17:28:33-0300",
                    "created_at" => "2019-11-27T17:28:33-0300"
                ],
                'metadata' => null,
            ],
            'meta' => null,
        ], $request);
    }

    public function testUnauthorized()
    {
        $this->expectException(TucuotaPermissionsException::class);
        $this->expectExceptionMessage("Invalid environment");

        $this->mockHandler->append(new Response(401, [], file_get_contents(__DIR__ . '/fixtures/401.json')));
        $request = $this->client->get('payment/PYvQwEssV56Dp2');
        echo json_encode($request);
    }

}
