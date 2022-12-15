<?php

namespace Tests\Unit\Domains\Delivery;

use App\Domains\Delivery\PostPreviewSecretEncryptor;

it('works', function () {
    $post = addPost(blog());

    $secret = PostPreviewSecretEncryptor::getPreviewSecret($post);
    $id = PostPreviewSecretEncryptor::decryptPreviewSecret($secret);

    expect($id)->toBe($post->id);
});

it('decrypt returns null if the timestamp is old', function () {
    $this->travel(-25)->hours();
    $secret = PostPreviewSecretEncryptor::getPreviewSecret(addPost(blog()));
    $this->travelBack();
    $id = PostPreviewSecretEncryptor::decryptPreviewSecret($secret);

    expect($id)->toBeNull();
});

it('returns null when the encrypted is wrong', function () {
    $id = PostPreviewSecretEncryptor::decryptPreviewSecret('hyvor');
    expect($id)->toBeNull();
});

it('returns null when timestamp array items are missing', function () {
    $id = PostPreviewSecretEncryptor::decryptPreviewSecret(encrypt('123'));
    expect($id)->toBeNull();
});
