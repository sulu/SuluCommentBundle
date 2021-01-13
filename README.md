# SuluCommentBundle

<p align="center">
    <a href="https://sulu.io/" target="_blank">
        <img width="30%" src="https://sulu.io/uploads/media/800x/00/230-Official%20Bundle%20Seal.svg?v=2-6&inline=1" alt="Official Sulu Bundle Badge">
    </a>
</p>
<p align="center">
    <a href="https://github.com/sulu/SuluCommentBundle/actions" target="_blank">
        <img src="https://img.shields.io/github/workflow/status/sulu/SuluCommentBundle/Test%20application/1.0.svg?label=test-workflow" alt="Test workflow status">
    </a>
</p>

The SuluCommentBundle adds support for adding comments to different types of entities (pages, articles, custom) in Sulu.

**Included features:**

* Website renderer
* Sulu-Admin integration to delete and update comments

## Status

This repository will become version 1.0 of SuluCommentBundle. It is under **heavy development** and currently its APIs
and code are not stable yet (pre 1.0).

## Requirements

* Composer
* PHP `^5.5 || ^7.0`
* Sulu `^1.3`

For detailed requirements see [composer.json](https://github.com/sulu/SuluCommentBundle/blob/1.0/composer.json).

## Documentation

The the Documentation is stored in the
[Resources/doc/](https://github.com/sulu/SuluCommentBundle/blob/1.0/Resources/doc) folder.

## Installation

All the installation instructions are located in the
[Documentation](https://github.com/sulu/SuluCommentBundle/blob/1.0/Resources/doc/installation.md).

## License

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)

## Reporting an issue or a feature request

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/Sulu/SuluCommentBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project built using the
[Sulu Minimal Edition](https://github.com/sulu/sulu-minimal) to allow developers of the bundle to reproduce the issue
by simply cloning it and following some steps.

## Installation

```
composer require sulu/comment-bundle
```

Configure the routing

```
sulu_comment_api:
    type: rest
    resource: "@SuluCommentBundle/Resources/config/routing_api.xml"
    prefix: /admin/api
    
sulu_comment:
    resource: "@SuluCommentBundle/Resources/config/routing.xml"
    prefix: /admin/comments
```

Add bundle to AbstractKernel:

```
new Sulu\Bundle\CommentBundle\SuluCommentBundle(),    
```
