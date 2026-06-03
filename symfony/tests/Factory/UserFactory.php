<?php

namespace App\Tests\Factory;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'hyvor_user_id' => self::faker()->randomNumber(),
            'posts_count' => self::faker()->randomNumber(),
            'role' => UserRole::ADMIN,
            'slug' => self::faker()->slug(),
            'sort' => self::faker()->randomNumber(),
            'status' => 'active',
        ];
    }

    public function active(): static
    {
        return $this->with(['status' => 'active']);
    }

    public function asOwner(): static
    {
        return $this->with(['role' => UserRole::OWNER]);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(User $user): void {})
            ;
    }
}
