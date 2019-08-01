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
        comments:             'SuluCommentBundle:WebsiteComment:comments.html.twig'
        comment:              'SuluCommentBundle:WebsiteComment:comment.html.twig'
        form:                 'SuluCommentBundle:WebsiteComment:form.html.twig'
```

If you only want to customize the templates for the example `page` type:

```yaml
sulu_comment:
    types:
        page:
            templates:
                comment:      'SuluCommentBundle:WebsiteComment:comment.html.twig'
                comments:     'SuluCommentBundle:WebsiteComment:comments.html.twig'
                form:         'SuluCommentBundle:WebsiteComment:form.html.twig'
```
