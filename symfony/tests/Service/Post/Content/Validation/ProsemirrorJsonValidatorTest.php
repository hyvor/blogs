<?php declare(strict_types=1);

namespace App\Tests\Service\Post\Content\Validation;

use App\Service\Post\Content\PostSchema;
use App\Service\Post\Content\Validation\ProsemirrorJson;
use App\Service\Post\Content\Validation\ProsemirrorJsonValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ProsemirrorJsonValidator>
 */
#[CoversClass(ProsemirrorJsonValidator::class)]
class ProsemirrorJsonValidatorTest extends ConstraintValidatorTestCase
{
    private const string VALID_JSON = '{"type":"doc","content":[{"type":"paragraph","content":[]}]}';

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ProsemirrorJsonValidator(new PostSchema());
    }

    public function test_null_value_is_valid(): void
    {
        $this->validate(null, new ProsemirrorJson());

        $this->assertNoViolation();
    }

    public function test_valid_prosemirror_json_raises_no_violation(): void
    {
        $this->validate(self::VALID_JSON, new ProsemirrorJson());

        $this->assertNoViolation();
    }

    public function test_malformed_json_raises_violation(): void
    {
        $this->validate('not json', new ProsemirrorJson());

        $this->buildViolation('The value must be a valid Prosemirror JSON. Error: Unable to decode JSON')
            ->assertRaised();
    }

    public function test_unknown_node_type_raises_violation(): void
    {
        $json = '{"type":"doc","content":[{"type":"unknown_node_type"}]}';

        $this->validate($json, new ProsemirrorJson());

        $this->buildViolation('The value must be a valid Prosemirror JSON. Error: Node type unknown_node_type not found in schema')
            ->assertRaised();
    }
}
