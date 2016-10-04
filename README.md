# SuluCommentBundle

[![StyleCI](https://styleci.io/repos/25727590/shield)](https://styleci.io/repos/25727590)
[![Build Status](https://travis-ci.org/sulu/SuluCommentBundle.svg?branch=master)](https://travis-ci.org/sulu/SuluCommentBundle)

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
