<?php

namespace App\Service\Post\Content\Validation;

use App\Service\Post\Content\PostSchema;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProsemirrorJsonValidator extends ConstraintValidator
{

    public function __construct(
        private PostSchema $postSchema
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        // @codeCoverageIgnoreStart
        if (!$constraint instanceof ProsemirrorJson) {
            throw new UnexpectedTypeException($constraint, sprintf('Constraint must be an instance of %s', ProsemirrorJson::class));
        }

        if (null === $value) {
            return;
        }

        if (!is_string($value)) {
            $this->context->buildViolation('The value must be a string.')
                ->addViolation();
            return;
        }
        // @codeCoverageIgnoreEnd

        try {
            $this->postSchema->documentFrom($value);
        } catch (PhrosemirrorException $e) {
            $this->context->buildViolation('The value must be a valid Prosemirror JSON. Error: ' . $e->getMessage())
                ->addViolation();
        }
    }
}
