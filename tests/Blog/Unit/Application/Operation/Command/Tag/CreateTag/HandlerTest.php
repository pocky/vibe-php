<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Tag\CreateTag;

use App\Blog\Application\Operation\Command\Tag\CreateTag\Command;
use App\Blog\Application\Operation\Command\Tag\CreateTag\Handler;
use App\Blog\Application\Shared\Generator\TagIdGeneratorInterface;
use App\Blog\Domain\Tag\CreateTag\CreatorInterface;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\Repository\TagRepositoryInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class HandlerTest extends TestCase
{
    private Handler $handler;

    private \PHPUnit\Framework\MockObject\MockObject $creator;

    private \PHPUnit\Framework\MockObject\MockObject $repository;

    private \PHPUnit\Framework\MockObject\MockObject $idGenerator;

    private \PHPUnit\Framework\MockObject\MockObject $eventBus;

    protected function setUp(): void
    {
        $this->creator = $this->createMock(CreatorInterface::class);
        $this->repository = $this->createMock(TagRepositoryInterface::class);
        $this->idGenerator = $this->createMock(TagIdGeneratorInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new Handler(
            $this->creator,
            $this->repository,
            $this->idGenerator,
            $this->eventBus
        );
    }

    public function test_it_creates_tag_with_provided_slug(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $command = new Command(
            name: 'Technology',
            slug: 'technology'
        );

        $tag = Tag::create(
            id: $tagId,
            name: new TagName('Technology'),
            slug: new TagSlug('technology')
        );

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($tagId);

        $this->creator->expects($this->once())
            ->method('__invoke')
            ->with(
                $tagId,
                $this->callback(fn ($name): bool => 'Technology' === $name->getValue()),
                $this->callback(fn ($slug): bool => 'technology' === $slug->getValue()),
                null
            )
            ->willReturn($tag);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($tag);

        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn ($event): bool => '01J1234567890ABCDEFGHJKMNP' === $event->tagId
                && 'Technology' === $event->name
                && 'technology' === $event->slug))
            ->willReturn(new Envelope(new \stdClass()));

        ($this->handler)($command);
    }

    public function test_it_creates_tag_without_slug(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $command = new Command(
            name: 'Web Development'
        );

        $tag = Tag::create(
            id: $tagId,
            name: new TagName('Web Development'),
            slug: null
        );

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($tagId);

        $this->creator->expects($this->once())
            ->method('__invoke')
            ->with(
                $tagId,
                $this->callback(fn ($name): bool => 'Web Development' === $name->getValue()),
                null,
                null
            )
            ->willReturn($tag);

        $this->repository->expects($this->once())
            ->method('add')
            ->with($tag);

        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn ($event): bool => 'Web Development' === $event->name
                && 'web-development' === $event->slug))
            ->willReturn(new Envelope(new \stdClass()));

        ($this->handler)($command);
    }

    public function test_it_dispatches_all_domain_events(): void
    {
        $tagId = new TagId('01J1234567890ABCDEFGHJKMNP');
        $command = new Command(name: 'Technology');

        $tag = Tag::create(
            id: $tagId,
            name: new TagName('Technology')
        );

        $this->idGenerator->expects($this->once())
            ->method('nextIdentity')
            ->willReturn($tagId);

        $this->creator->expects($this->once())
            ->method('__invoke')
            ->willReturn($tag);

        $this->repository->expects($this->once())
            ->method('add');

        // Should dispatch exactly one event (TagCreated)
        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        ($this->handler)($command);
    }
}
