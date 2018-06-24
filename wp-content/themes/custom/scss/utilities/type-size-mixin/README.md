# type-size-mixin
A SCSS mixin for responsive type size and leading definitions.  
The repo contains example values for the variable lists.

# Table of Contents
- [Usage](#usage)
- [Helpers](#helpers)
  - [Strip Units](#strip-units-helper)
  - [Map Helper](#map-helper)
  - [Responsive Queries](#responsive-queries)
- [Variable Lists](#lists)
  - [Responsive Breakpoints](#breakpoints-list)
  - [Type Size and Leading](#type-size-and-leading-lists)
- [Example](#example)

# Usage
```sass
@include text-size($large);
```  

The arguments that the mixin can take on are the names of the [type size/leading variable lists](#type-size-and-leading-lists).  

If the mixin is called and values are not entered, it will default to the `regular` font-size.  

# Helpers
### strip-units helper
[_strip-units__helper.scss](https://github.com/codeCrit/type-size-mixin/blob/master/_strip-units__helper.scss)  
This allows the mixin to make calculations using unitless numbers

### map helper
[_map__helper.scss](https://github.com/codeCrit/type-size-mixin/blob/master/_map__helper.scss)  
Can get values from the next and previous keys when mapping variable lists

### Responsive queries
[_responsive-query__mixin.scss](https://github.com/codeCrit/type-size-mixin/blob/master/_responsive-query__mixin.scss)  
This mixin is required for the type-size mixin, but can be used through your styling. It uses the `_responsive-breakpoints__list.scss` values and has 3 options:
```
@include respond-above();
@include respond-below();
@include respond-at();
```
`respond-above` is a min-width query  
`respond-below` is a max-width query  
`respond-at` is a min and max-width query

# Variable Lists

### Breakpoints list
[_responsive-breakpoints__list.scss](https://github.com/codeCrit/type-size-mixin/blob/master/_responsive-breakpoints__list.scss)  

A list of breakpoints for responsive styling  

For example,
```sass
$breakpoints: (
  base: 0px,
  small: 512px,
  medium: 768px,
  large: 1024px,
  huge: 1440px,
  massive: 1800px
) !default;
```

### Type size and leading lists
[_type-size__list.scss](https://github.com/codeCrit/type-size-mixin/blob/master/_type-size__list.scss)  

A specific text-size in this mixin is defined with a font-size and a leading.  

For example,  
  `small: (64px, 72px)`  
  where `small` is the media query width defined in the [`$breakpoints`](#breakpoints-list) map-list in `_responsive.scss`,  
  `64px` is the `font-size`  
  `72px` is the `line-height` (which will be converted to a unitless value for the browser in the mixin)  


The definition of each font-size/leading pair can be different for each media query width.  
This is an example of a font size with different definitions for each media query width:  
```sass
$medium: (
  base: (12px, 18px),
  small: (14px, 20px),
  medium: (16px, 24px),
  large: (18px, 28px),
  huge: (20px, 32px)
) !default;
```
This is generally an unusual situation because it would involve a pretty complicated typography system.  


A more common example would be something like this:  
```sass
$huge: (
  base: (52px, 60px),
  small: (64px, 72px),
  medium: (64px, 72px),
  large: (72px, 80px),
  huge: (72px, 80px)
) !default;
```

This definition includes redundant repitition of values, so it can be defined more simply like this:  
```sass
$huge: (
  base: (52px, 60px),
  small: (64px, 72px),
  large: (72px, 80px)
) !default;
```

# Example
**Breakpoints**
```sass
$breakpoints: (
  base: 0px,
  medium: 768px,
  large: 1024px
) !default;
```
**Font Sizes**
```sass
$huge: (
  base: (52px, 60px),
  medium: (64px, 72px),
  large: (72px, 80px)
) !default;
```
**Usage**
```sass
@include text-size($huge);
```  
**Output**
```sass
@media screen and (min-width: 0px) {
  font-size: 52px;
  line-height: calc(60px/52px);
};
@media screen and (min-width: 768px) {
  font-size: 64px;
  line-height: calc(72px/64px);
};
@media screen and (min-width: 1024px) {
  font-size: 72px;
  line-height: calc(80px/72px);
};
```  
