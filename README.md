# Boots 👢 🥾

<p>
<a href="https://github.com/imliam/boots/actions"><img src="https://github.com/imliam/boots/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/imliam/boots"><img src="https://img.shields.io/packagist/dt/imliam/boots?v=1" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/imliam/boots"><img src="https://img.shields.io/packagist/v/imliam/boots?v=1" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/imliam/boots"><img src="https://img.shields.io/packagist/l/imliam/boots?v=1" alt="License"></a>
</p>

Give your AI agents some boots, so they can walk from one agent to the next.

## Introduction

Boots is a slimmed-down fork of [Laravel Boost](https://github.com/laravel/boost) with one goal: to provide a way to make your AI guidelines consistent across different agents, no matter what your app's stack is.

What's the difference?

* Laravel Boost is tightly specific to the Laravel framework, provides its own guidelines, and must be a dependency of your application
* Boots is framework (and language) agnostic, allows you to define your own guidelines, and can be used as a standalone command line tool

> [!NOTE]
> It is still recommended to use Laravel Boost in a Laravel application - the additional features it provides are worth it.

With this, you can easily switch between different AI agents or have multiple team members using different agents on the same project, while keeping your guidelines consistent and up to date.

## Usage

Boots can be installed globally via Composer, then run via the `boots` command in your terminal:

```bash
composer global require imliam/boots

boots
```

Or run via [cpx](https://cpx.dev):

```bash
cpx imliam/boots
```

When run, Boots will prompt you to ask which agents you wish to generate guidelines files for. Boots will also try to automatically detect which agents you may be using based on your existing AI guideline files or tools you have installed.

Boots will look for a `./.ai/guidelines` directory in your current working directory and process all files within it, compiling them into agent-specific guidelines.


> [!IMPORTANT]
> You should put all your project's AI guidelines files in the `./.ai/guidelines` directory - Boots will handle the rest.

The following agents are supported out of the box:

- Cascade
- Claude Code
- Cline
- Cursor
- GitHub Copilot
- Junie
- Kilo Code
- Kiro
- Roocode
- Trae
- Warp
