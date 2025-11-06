<?php

namespace Database\Factories;

use App\Data\Enums\S3TransferStateEnum;
use App\Models\Blog;
use App\Models\S3Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<S3Storage>
 */
class S3StorageFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'endpoint_url' => $this->faker->url(),
            'bucket_name' => 'blogs',
            'access_key' => 'ACCESSKEY',
            'secret_key_encrypted' => encrypt('SECRETKEY'),
            'region' => $this->faker->randomElement(['us-east-1', 'us-west-2', 'eu-west-1', 'ap-southeast-1']),
            'path_prefix' => $this->faker->optional()->slug(),
            'path_style_access' => $this->faker->boolean(),
            'cdn_url' => $this->faker->optional()->url(),
            'transfer_state' => S3TransferStateEnum::PENDING,
            'reverse_transfer_state' => S3TransferStateEnum::PENDING,
        ];
    }

    /**
     * @param array<mixed> $attrs
     */
    public static function one($attrs = []): S3Storage
    {
        return S3Storage::factory()->create($attrs);
    }

    /**
     * @param array<mixed> $attrs
     */
    public static function forBlog(Blog $blog, $attrs = []): S3Storage
    {
        return self::one($attrs + ['blog_id' => $blog->id]);
    }
}

