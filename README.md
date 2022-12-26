# Sandstone - A modern API toolkit for (Decoupled) Drupal

## What is this?
Sandstone aims to make creating APIs for Drupal sites with a focus on simplified controllers.

## Why not JSON:API?
JSON:API has a world of issues many of which are not necessarily issues with JSON:API itself being flawed but the fact JSON:API exists as an out-of-the-box solution for accessing content in a restful manner. As such, some queries are complex and some are downright impossible without extending it yourself. There are modules that add features but these won't necessarily provide the level of flexibility you need.

## Why not REST?
REST is rather poorly implemented in Drupal, and is mostly designed to allow interactng with a sole resource. Like JSON:API, there are ways to make REST work better within Drupal but the amount of setup is cumbersome and you'll often find yourself fighting against the permissions model.

## Goals
Sandstone aims to make creating drupal APIs as simple as creating a controller and hooking it in; the controller can opt to be as-designed - that is, to only allow the built-in configuration options (such as rerouting and basic permissions) or it can be made as generic as possible allowing modules to be created to extend Sandstone for reuse.

Sandstone comes with some basic user account management, can be extended with a module that exposes Search API, or extended with controllers specific to your drupal instance for custom content types.

The intention is to add a query language to allow creating dynamic APIs without custom controllers to allow fetching users, posts, etc. without needing to extend with custom controllers.