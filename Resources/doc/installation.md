# Installation

Install bundle over composer:

```bash
composer require sulu/comment-bundle
```

Add bundle to config/bundles.php`:

```php
    Sulu\Bundle\CommentBundle\SuluCommentBundle::class => ['all' => true],
```

Add the routes of the bundle to `config/routes/sulu_admin.yaml`:

```yaml
sulu_comment_api:
    type: rest
    resource: "@SuluCommentBundle/Resources/config/routing_api.yml"
    prefix: /admin/api
```

And `config/routes/sulu_website.yaml`:

```yaml
sulu_comments:
    type: rest
    resource: "@SuluCommentBundle/Resources/config/routing_website.yml"
```

Follow the [Getting started](https://github.com/sulu/SuluCommentBundle/blob/master/Resources/doc/getting-started.md)
documentation to include basic comments to your page.

## Bundle configuration

Possible bundle configuration:

```yaml
sulu_comment:
    default_templates:
        comments:             '@SuluComment/WebsiteComment/comments.html.twig'
        comment:              '@SuluComment/WebsiteComment/comment.html.twig'
        form:                 '@SuluComment/WebsiteComment/form.html.twig'
    nested_comments:          true
    types:

        # Prototype
        type:
            templates:
                comments:     '@SuluComment/WebsiteComment/comments.html.twig'
                comment:      '@SuluComment/WebsiteComment/comment.html.twig'
                form:         '@SuluComment/WebsiteComment/form.html.twig'
            nested_comments:  true
    objects:
        comment:
            model:            Sulu\Bundle\CommentBundle\Entity\Comment
            repository:       Sulu\Bundle\CommentBundle\Entity\CommentRepository
        thread:
            model:            Sulu\Bundle\CommentBundle\Entity\Thread
            repository:       Sulu\Bundle\CommentBundle\Entity\ThreadRepository
```
