# API Platform Integration

**API Platform as UI layer using Gateways.**

## Architecture

**Flow**: HTTP → Resource → Provider/Processor → Gateway → CQRS → Domain

**Principles**:
- API = UI layer only
- Use Gateways exclusively
- No business logic in API
- Clean DTO ↔ Domain transformation

## Structure
```
UI/Api/Rest/
├── Resource/      # API DTOs
├── Provider/      # Read ops (Get*, List*)
├── Processor/     # Write ops (Create*, Update*, Delete*)
└── Filter/        # Search/filter
```

**Single Responsibility**: One provider/processor per operation

## Resource Definition

```php
#[ApiResource(
    shortName: 'Article',
    operations: [
        new Get('/articles/{id}', provider: GetArticleProvider::class),
        new GetCollection('/articles', provider: ListArticlesProvider::class),
        new Post('/articles', processor: CreateArticleProcessor::class),
        new Put('/articles/{id}', processor: UpdateArticleProcessor::class),
        new Delete('/articles/{id}', processor: DeleteArticleProcessor::class),
    ],
)]
final class ArticleResource
{
    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        // ... other fields
    ) {}
}
```

## Provider Pattern (Read)

```php
final readonly class GetArticleProvider implements ProviderInterface
{
    public function __construct(private GetArticleGateway $gateway) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = GetArticleRequest::fromData(['id' => $uriVariables['id']]);
        $response = ($this->gateway)($request);
        
        return $this->transformToResource($response->data()['article']);
    }
}
```

**List Provider**: Similar, handles pagination via context['filters']

## Processor Pattern (Write)

```php
final readonly class CreateArticleProcessor implements ProcessorInterface
{
    public function __construct(private CreateArticleGateway $gateway) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = CreateArticleRequest::fromData([
            'title' => $data->title,
            'content' => $data->content,
            // map fields
        ]);

        $response = ($this->gateway)($request);
        
        return $this->transformToResource($response->data());
    }
}
```

**Exception Handling**: Transform domain exceptions to HTTP exceptions

## Filters

```php
final class ArticleSearchFilter extends AbstractFilter
{
    protected function filterProperty(...): void
    {
        if ($property === 'search') {
            $queryBuilder->andWhere("title LIKE :search OR content LIKE :search")
                        ->setParameter('search', "%{$value}%");
        }
    }
}
```

## Service Config

```yaml
services:
    App\[Context]\UI\Api\Rest\Resource\:
        tags: ['api_platform.resource']
    App\[Context]\UI\Api\Rest\Provider\:
        tags: ['api_platform.state_provider']
    App\[Context]\UI\Api\Rest\Processor\:
        tags: ['api_platform.state_processor']
```

## Error Handling

```php
trait ExceptionTransformerTrait
{
    private function transformException(\Throwable $e): \Throwable
    {
        return match ($e->getCode()) {
            404 => new ItemNotFoundException($e->getMessage()),
            400 => new \InvalidArgumentException($e->getMessage()),
            403 => new \RuntimeException('Access denied', 403),
            default => $e,
        };
    }
}
```

## Validation

```php
#[Assert\NotBlank]
#[Assert\Length(min: 3, max: 200)]
public ?string $title = null;
```

## Security

```php
new Put(security: "is_granted('ARTICLE_EDIT', object)")
new Delete(security: "is_granted('ARTICLE_DELETE', object)")
```

## Testing

```php
final class ArticleResourceTest extends ApiTestCase
{
    public function testGetArticle(): void
    {
        $client->request('GET', '/api/articles/{id}');
        $this->assertResponseIsSuccessful();
    }
}
```

## Best Practices

1. **Resources**: DTOs only, focused representation
2. **Providers/Processors**: Thin, delegate to Gateways
3. **Performance**: Pagination, caching, optimized queries
4. **Documentation**: OpenAPI annotations, clear errors

## Common Patterns

**Pagination**: Use context['filters'] for page/limit
**Async**: Return 202 with status='processing'

## Migration Guide

1. Map controller actions → API operations
2. Extract business logic → Domain/Application
3. Create Resources (DTOs)
4. Implement Providers/Processors → Gateways
5. Add tests

**Remember**: API Platform = UI adapter only