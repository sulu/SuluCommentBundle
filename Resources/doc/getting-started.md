# Getting started

To include comments to any content you want just add following snippet into your `html.twig` file.

Following example works for a page:

```twig
{{ render(path('sulu_comment.get_threads_comments', {
    threadId: 'page-' ~ uuid, 
    referrer: app.request.uri, 
    _format: 'html'
})) }}
```

But you can use any thread id you want just replace `page` by any type.

This will render a form to add new comments to the page and a list of existing ones.

## Overwrite templates

To customize the templates for all types you can use following config:

```yaml
sulu_comment:
    default_templates:
        comments:             '@SuluComment/WebsiteComment/comments.html.twig'
        comment:              '@SuluComment/WebsiteComment/comment.html.twig'
```

If you only want to customize the templates for the example `page` type:

```yaml
sulu_comment:
    types:
        page:
            templates:
                comments:     '@SuluComment/WebsiteComment/comments.html.twig'
                comment:      '@SuluComment/WebsiteComment/comment.html.twig'
```

To extend the form you can provide a [FormExtension](https://symfony.com/doc/current/form/create_form_type_extension.html)
for `Sulu\Bundle\CommentBundle\Form\Type\CommentType`.

## Nested Comments

By default comments can be nested in the default implementation. If you want to disable it use following configuration:

```yaml
sulu_comment:
    nested_comments: false
```

Or for a single type (here `page`):

```yaml
sulu_comment:
    types:
        page:
            nested_comments: false
```
