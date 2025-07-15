<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle\Constraint;

use App\BlogContext\Application\Gateway\GetArticle\Gateway as GetArticleGateway;
use App\BlogContext\Application\Gateway\GetArticle\Request as GetArticleRequest;
use App\BlogContext\Application\Gateway\PublishArticle\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SeoReadyValidator extends ConstraintValidator
{
    public function __construct(
        private readonly GetArticleGateway $getArticleGateway,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SeoReady) {
            throw new UnexpectedTypeException($constraint, SeoReady::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        try {
            $getRequest = GetArticleRequest::fromData([
                'id' => $value->articleId,
            ]);
            $getResponse = ($this->getArticleGateway)($getRequest);
            $articleData = $getResponse->data();
        } catch (\Throwable) {
            // Article doesn't exist, let other validators handle this
            return;
        }

        // Skip validation if article is already published
        if ('published' === $articleData['status']) {
            return;
        }

        // SEO validation rules
        $titleLength = strlen((string) $articleData['title']);
        $contentLength = strlen((string) $articleData['content']);

        // Title validation
        if (10 > $titleLength) {
            $this->context->buildViolation($constraint->titleTooShort)
                ->setParameters([
                    'min_length' => 10,
                    'actual_length' => $titleLength,
                ])
                ->addViolation();
        }

        // Content validation
        if (50 > $contentLength) {
            $this->context->buildViolation($constraint->contentTooShort)
                ->setParameters([
                    'min_length' => 50,
                    'actual_length' => $contentLength,
                ])
                ->addViolation();
        }

        // Meta description validation
        if (!isset($articleData['meta_description']) || empty($articleData['meta_description'])) {
            $this->context->buildViolation($constraint->missingMetaDescription)
                ->addViolation();
        }
    }
}
