h1. Introduction


XHP is a PHP extension which augments the syntax of the language such that XML document fragments become valid PHP expressions. This allows you to use PHP as a stricter templating engine and offers much more straightforward implementation of reusable components.

h1. Simple Example

bc. <?php
$href = 'http://www.facebook.com';
echo <a href={$href}>Facebook</a>;

Take note of the syntax on line 3, this is not a string. This is the major new syntax that XHP introduces to PHP.

Anything that's in {}'s is interpreted as a full PHP expression. This differs from {}'s in double-quoted strings; double-quoted strings can only contain variables.

You can define arbitrary elements that can be instantiated in PHP. Under the covers each element you create is an instance of an class. To define new elements you just define a new class. XHP comes with a set of predefined elements which implement most of HTML for you.

*Important*: Please be sure to include "init.php":http://github.com/facebook/xhp/blob/master/php-lib/init.php which is in the "php-lib":http://github.com/facebook/xhp/tree/master/php-lib/ directory of the repository. The XHP extension only handles adding the XML syntax, the actual elements are defined directly in PHP. Including the core XHP libraries in PHP code means you can customize XHP for your own applications, though it's recommended to avoid large forks which may cause incompatibilities as XHP evolves. _All the examples you see on this page omit the required includes._

h1. Complex Structures

Note that XHP structures can be arbitrarily complex. This is a valid XHP program:

bc. <?php
$post =
  <div class="post">
    <h2>{$post}</h2>
    <p><span>Hey there.</span></p>
    <a href={$like_link}>Like</a>
  </div>;

One advantage that XHP has over string construction is that it enforces correct markup structure at compile time. That is, the expression @$foo = <h1>Header</h2>;@ is not a valid expression, because you can not close an @h1@ tag with a @/h2@. When building large chunks of markup it can be difficult to be totally correct. With XHP the compiler now checks your work and will refuse to run until the markup is correct.

h1. Dynamic Structures

Sometimes it may be useful to create a bunch of elements and dynamically add them as children to an element. All XHP objects support the @appendChild@ method which behaves similarly to the same Javascript method. For example:

bc. <?php
$list = <ul />;
foreach ($items as $item) {
  $list->appendChild(<li>{$item}</li>);
}

In the code, @<ul />@ creates a ul with no children. Then we dynamically append children to it for each item in the @$items@ list.

h1. Escaping

An interesting feature of XHP is the idea of automatic escaping. In vanilla PHP if you want to render input from the user you must manually escape it. This practice is error-prone and has been proven over time to be an untenable solution. It increases code complexity and still leads to security vulnerabilities by careless programming. However, since XHP has context-specific about the page structure it can automatically escape data. The following two examples are identical, and both are "safe".

bc. <?php
echo '<div>Hello '.htmlspecialchars($_GET['name']).'</div>';

bc. <?php
echo <div>Hello {$_GET['name']}</div>;

As you can see, using XHP makes safety the default rather than the exception.

If for some reason you need to bypass XHP's escaping you can explicitly upcast a raw-str to an html-str with the @HTML()@ function. For example: @<div>{HTML($html)}</div>@. You should avoid this at all costs, however. See the "Best Practices" section below for more information.

h1. Defining Elements

All elements in XHP are just PHP classes. Even the basic HTML elements like div and span are classes. You define an element just like you do a class, except you use a leading colon to specify that you're creating an XHP element:

bc. <?php
class :fb:thing extends :x:element {
  ...
}

After we define @fb:thing@ we can instantiate it with the expression @<fb:thing />@. @:x:element@ is the core XHP class you should subclass from when defining an element. It will provide you all the methods you need like @appendChild@, and so on. As an @:x:element@ you must define only @render()@. @render()@ should always return more XHP elements. It's important to remember this rule: even elements you define yourself will return XHP elements. The only XHP elements that are allowed to return a string are elements which subclass from @:x:primitive@. The only elements which should subclass @:x:primitive@ are base elements that make HTML building blocks. XHP with the core HTML library is a viable replacement for strings of markup.

You can also use the leading-colon syntax to use an XHP element where you would normally use a class name, for instance while referencing class constants or typehinting:

bc. <?php
echo :fb:thing::someConstant;

bc. <?php
function giveMeAThing(:fb:thing $thing) {
}

h1. Defining Attributes

Most elements will take some number of attributes which affect its behavior. You define attributes in an element with the @attribute@ keyword.

bc. <?php
class :fb:thing extends :x:element {
  attribute
    string title = "No Title",
    enum { "cool", "lame" } type;
}

Here we define two attributes, @title@ and @type@. @title@ is of type @string@ and the default value is @"No Title"@. type is of type @enum@ and has two valid values -- @"cool"@ and @"lame"@. Valid types are @bool@, @int@, @array@, @string@, and @var@. You can also specify a class or element name as a type. Finally, you can put @required after the name to specify that this attribute is required for the element to render.

Note that when you extend another element you will always inherit its attributes. However, any attributes you specify with the same name will override your parent's attributes.

You can also steal another element's attributes by specifying only a tag name in a definition. The declaration @attribute :div@ says that this element may accept any attribute that a div element could accept.

h1. Defining Element Structure

All elements have some kind of structure that they must follow. For instance, in HTML5 it is illegal for an @<input />@ to appear directly inside of a @<body />@ tag (it must be inside a form). XHP allows you to define a content model which documents ".":http://www.overheadmovies.com must adhere to. This is done with the @children@ keyword, which uses a syntax similar to regular expressions. Note, that unlike @attribute@, @children@ may only appear once inside any class.

bc. <?php
class :fb:thing-container extends :x:element {
  children (:fb:thing1 | :fb:thing2)*;
}

A children declaration supports the following postfix operators:
* ? : Zero or one instance
* * : Zero or more instances
* + : One or more instances

If no operator is specified then the declaration must be matched exactly one time. You may also use the @,@ or @|@ operator to combine multiple declarations into one. The @,@ operator specifies a list of declarations that must appear in order, and a @|@ specifies a list of declarations, of which one must match. For instance if you are defining a page layout your children declaration may look like:

bc. children (:fb:left-column?, :fb:content, :fb:right-column?);

This specifies an optional left column followed by a required content and an optional right column.

You can also use the special declarations @any@ or @empty@ to specify that your element can accept any elements, or no elements. If you don't specify a children declaration your parent class's declaration will be inherited. @:x:element@'s children declaration is @children any;@.

Note: The algorithm which checks adherence to a children declaration is greedy with no backtracking. For most children declarations this will make no difference, however if you're defining a complex children declaration you should know how it works. Basically, this declaration is impossible to satisfy: @children (:fb:thing*, :fb:thing);@. The @*@ postfix operator is greedy and will capture all @fb:thing@'s. After that it's impossible to match another @fb:thing@.

h1. Element Categories

A lot of times you may want to accept all children of a particular group, but enumerating the group starts to become unsustainable. When this happens you can define an element group and specify in your child declaration that your element is a member. Then you can reference that group in a children declaration using the % prefix.

bc. class :fb:thing1 extends :x:element {
  category %fb:thing;
}
class :fb:thing2 extends :x:element {
  category %fb:thing;
}
class :fb:thing-container extends :x:element {
  children (%fb:thing)*;
}

h1. Whitespace

In XHP, text nodes that contain only whitespace are removed. The expressions @<div> </div>@ and @<div />@ are identical. Text nodes that contain non-whitespace are trimmed on the left and right to at most 1 space. This is worth noting because you may want to do something like:

bc. <?php
$node = <div><label>Title:</label> <span>{$title}</span></div>;

This will lead to non-desirable results as the space in between the @:@ and @$title@ will be lost. In order to fix this try moving the space into the @<label />@ tag. If you can't do this then just use @{' '}@ which will not be stripped.

h1. Best Practices

There are certain conventions that you should comply with while using XHP.

* Don't pollute the global XHP namespace with namespace-less elements. Most elements you define should use some namespace. Elements that use no namespace should not be "magic" at all. For instance,

bc. <?php
class :fb:thing extends :x:element {
  protected function render() {
    return <div class="thing">thing</div>;
  }
}

This element would be considered magic because when you print an @<fb:thing />@ it actually returns a div.

* Don't use @HTML()@. You should not use @HTML()@ unless you have a very good reason, and you have a very very good understanding of the differences between HTML strings and raw strings. And even then, avoid @HTML()@.

