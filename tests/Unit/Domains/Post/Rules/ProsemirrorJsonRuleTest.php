<?php

namespace Tests\Unit\Domains\Post\Rules;

use App\Domains\Post\Rules\ProsemirrorJsonRule;

function getProsemirrorJsonRuleError(?string $json) : ?string {
    $rule = new ProsemirrorJsonRule();
    $error = null;
    $rule->validate('test', $json, function($m) use (&$error) {
        $error = $m;
    });
    return $error;
}

it('validates', function() {

    expect(getProsemirrorJsonRuleError(null))->toBeNull();
    expect(getProsemirrorJsonRuleError(''))->toContain('Unable to decode JSON');
    expect(getProsemirrorJsonRuleError('{"type":"doc","content":[{"type":"text","text":"Hello world!"}]}'))
        ->toBeNull();
    // html
    expect(getProsemirrorJsonRuleError('<p>Hello world!</p>'))->toContain('Unable to decode JSON');
    // invalid prosemirror
    expect(getProsemirrorJsonRuleError('{"type":"doc","content":[{"type":"plain_text","text":"Hello world!"}]}'))
        ->toContain('Node type plain_text not found in schema');

});