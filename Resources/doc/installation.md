# Installation

Install bundle over composer:

```bash
composer require sulu/comment-bundle
```

Add bundle to config/bundles.php`:

```php
    Sulu\Bundle\CommentBundle\SuluCommentBundle::class => ['all' => true],
```

Ad the routes of the bunlde to `config/routes/sulu_admin.yaml`:

```yml
sulu_comment_api:
    type: rest
    resource: "@SuluCommentBundle/Resources/config/routing_api.yml"
    prefix: /admin/api

sulu_comment:
    resource: "@SuluCommentBundle/Resources/config/routing.xml"
    prefix: /admin/comments
```

Possible bundle configuration:

```yml
sulu_comment:
    default_templates:
        comments:             'SuluCommentBundle:WebsiteComment:comments.html.twig'
        comment:              'SuluCommentBundle:WebsiteComment:comment.html.twig'
    types:
        templates:
            comments:             'SuluCommentBundle:WebsiteComment:comments.html.twig'
            comment:              'SuluCommentBundle:WebsiteComment:comment.html.twig'
    objects:
        comment:
            model:                Sulu\Bundle\CommentBundle\Entity\Comment
            repository:           Sulu\Bundle\CommentBundle\Entity\CommentRepository
        thread:
            model:                Sulu\Bundle\CommentBundle\Entity\Thread
            repository:           Sulu\Bundle\CommentBundle\Entity\ThreadRepository
```
