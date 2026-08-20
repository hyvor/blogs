<?php

namespace App\Service\Post\Content\Validation;

use App\Service\Post\Content\PostContentService;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProsemirrorJsonValidator extends ConstraintValidator
{

    public function __construct(
        private PostContentService $postContentService
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        // @codeCoverageIgnoreStart
        if (!$constraint instanceof ProsemirrorJson) {
            throw new UnexpectedTypeException($constraint, sprintf('Constraint must be an instance of %s', ProsemirrorJson::class));
        }

        // `false` is the "not provided" sentinel used by tri-state (null|string|false)
        // input properties - e.g. UpdatePostVariantInput's $content/$content_unsaved,
        // where null means "explicitly clear".
        if (null === $value || false === $value) {
            return;
        }

        if (!is_string($value)) {
            $this->context->buildViolation('The value must be a string.')
                ->addViolation();
            return;
        }
        // @codeCoverageIgnoreEnd

        try {
            $this->postContentService->getDocumentFromJson($value);
        } catch (PhrosemirrorException $e) {
            $this->context->buildViolation('The value must be a valid Prosemirror JSON. Error: ' . $e->getMessage())
                ->addViolation();
        }
    }
}
