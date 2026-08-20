<?php

namespace App\Tests\Fake;

use PHPUnit\Framework\Assert;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\ProtocolVersion;
use Symfony\Component\Mercure\Update;

/**
 * Test double for HubInterface - records published Updates in memory instead of making a
 * real HTTP call to a Mercure hub. Bound over the real hub in the test env, see
 * config/services_test.php.
 */
class FakeHub implements HubInterface
{
    /** @var Update[] */
    private array $published = [];

    public function publish(Update $update): string
    {
        $this->published[] = $update;
        return 'fake-id-' . count($this->published);
    }

    public function assertPublished(string $topic): Update
    {
        foreach ($this->published as $update) {
            if (in_array($topic, $update->getTopics(), true)) {
                return $update;
            }
        }

        $allTopics = [];
        foreach ($this->published as $update) {
            /** @var string[] $topics */
            $topics = $update->getTopics();
            foreach ($topics as $topicName) {
                $allTopics[] = $topicName;
            }
        }

        Assert::fail(sprintf(
            'No update was published to topic "%s". Published topics: %s',
            $topic,
            $allTopics === [] ? '<none>' : implode(', ', $allTopics),
        ));
    }

    public function assertNotPublished(string $topic): void
    {
        foreach ($this->published as $update) {
            Assert::assertNotContains($topic, $update->getTopics(), "An update was unexpectedly published to topic \"$topic\".");
        }
    }

    /** @return Update[] */
    public function getPublished(): array
    {
        return $this->published;
    }

    public function getPublicUrl(): string
    {
        return 'https://mercure.test/.mercure';
    }

    public function getFactory(): ?TokenFactoryInterface
    {
        return null;
    }

    public function getProtocolVersion(): ProtocolVersion
    {
        return ProtocolVersion::Legacy;
    }

    public function getCookieName(): string
    {
        return 'mercureAuthorization';
    }
}
