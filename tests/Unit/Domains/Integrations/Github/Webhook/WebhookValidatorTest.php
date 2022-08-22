<?php

namespace Tests\Unit\Domains\Integrations\Github\Webhook;

use App\Domains\Integrations\Github\Webhook\WebhookValidator;
use App\Exceptions\TrustedException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

it('fails when signature is missing', function() {

    $request = Request::create('', 'POST');
    WebhookValidator::validate($request, 'test');

})->throws(BadRequestHttpException::class, 'Github Signature 256 not defined');

it('requires a valid signature format', function() {

    $request = Request::create('', 'POST');
    $request->headers->set('X_HUB_SIGNATURE_256', 'test');
    WebhookValidator::validate($request, 'test');

})->throws(BadRequestHttpException::class, 'Invalid signature format');

it('requires a valid signature', function() {

    $content = json_encode(['test' => 'test']);
    $request = Request::create('', 'POST', [], [], [], [], $content);
    $signature = hash_hmac('sha256', $content, 'test');
    $request->headers->set('X_HUB_SIGNATURE_256', 'sha256=' . $signature);
    WebhookValidator::validate($request, 'invalid secret');

})->throws(TrustedException::class, 'Count not verify the signature');

it('validates', function() {


    $content = json_encode(['test' => 'test']);
    $request = Request::create('', 'POST', [], [], [], [], $content);
    $signature = hash_hmac('sha256', $content, 'test');
    $request->headers->set('X_HUB_SIGNATURE_256', 'sha256=' . $signature);

    expect(WebhookValidator::validate($request, 'test'))->toBeTrue();

});