# vygu

A platform independent library to add GUI support for PHP

Read the [Wiki](https://github.com/Doi6doi/vygu/wiki) for more information.

The library intends to be small and easy to use. A Hello, World application is just a few lines:

```
$w = new Window([Window::TITLE=>"Hello", Window::MAIN=>true, Group::LAYOUT=>[Layout::class,"center"]]);
$w->add( new Label("Hello, World!") );
Vygu::ins()->run();
```
