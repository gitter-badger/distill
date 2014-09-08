Distill PHP
===========

Short Intro
-----------

Tagline: "Because PHP *IS* my framework"

Distill is a PHP framework of extremely limited scope.  It's general
philosophy is to let PHP itself be the underlying the base abstraction,
while providing just enough functionality to provide for building well
architected applications and allow for a module based ecosystem.

New features need to be extremely vetted for inclusion in this
framework.  Why?  Because here, NIH means "not in here".  Whatever
feature you think would be nice to have probably already has a better
place where it could live.

The objective of Distill, like its name, is to distill the amount of
framework necessary to build applications into a small focused amount
of code with a finite set of goals.

Long Intro / General Perspectives
---------------------------------

Out of the box, Distill delivers just enough features to allow PHP
developers to build structurally sound applications without a lot
of boilerplate code or unnecessarily deep abstractions as the foundation
for an application.  Distill's learning curve is shallow enough so that
developers can be immediately productive with just a well rounded
understanding of PHP itself.

Here are some statements that help understand the Distill philosophy:

* You'll like the simple *dependency injection*
* You won't miss the lack of HTTP Request/Response abstraction
* PHP itself is the *middleware*
* You'll like that most objects are built on well known PHP idioms and SPL structures where appropriate
* You won't miss hard to debug recursive and endless stack traces
* Distill expands on terminology already present in the PHP manual, instead of inventing new terminology
* You'll like the blissfully short implementations
* You won't miss the endless and deep abstractions

That said Distill promotes an application architecture with just a
handful of concepts and features:

* Fast name based service/dependency injection
* Simple built-in router for both HTTP and CLI request handling
* Configuration file management and processing
* Application lifecycle callback registration
* Basic modules for PHP/HTML, CLI, and REST output handling

Installation
------------

Install from Skeleton

    composer create-project distill/skeleton ./project

Services and Invocation
-----------------------

The framework will occasionally need to both instantiate objects, with
parameters to their constructors or factories resolved, and also invoke
some kind of callable.

### Invocation of Services

Basic service registration via closure:

    $serviceLocator = new ServiceLocator;
    $serviceLocator->set('foo', function () { return new Foo; });
    $foo = $serviceLocator->get('foo'); // is_a(Foo)

Basic instantiation of unregistered class:

    class Foo {}
    $foo = $serviceLocator->instantiate('Foo'); // is_a(Foo)

`Instantiate & Invoke` syntax:

    class HelloWorld { public function sayHi() { echo 'hi'; } }
    $serviceLocator->invoke('HelloWorld->sayHi'); // hi


Callbacks
---------

The frameworks features the ability to register and call stacks of
PHP callables, these are known as callbacks.  All callbacks use the
PHP callback syntax http://php.net/manual/en/language.types.callable.php
with the addition of the Distill `Instantiate & Invoke` syntax.

@todo

Routes & Dispatching
--------------------

@todo

Built-in Application Services
-----------------------------

@todo

Built-in Application Callbacks
---------------------

PHP Callables and Callbacks can be registered with:

    $application->on('CallbackName', $callbackOrCallable);

`$application->run()` will:

- `Application.Initialize`
- `Application.PreRoute`
- `Application.PostRoute`

If there is a dispatchable, it will:

- `Application.PreDispatch`
- `Application.PostDispatch`

When there is an exception, with at least one callback registered, it will:

- `Application.Error`
