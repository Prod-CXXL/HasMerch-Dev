# Style Guide

## HTML

- Use semantic HTML.
- Keep nesting minimal.
- Use descriptive IDs and classes.

Good:

<section class="product-card">

Bad:

<div class="box">

## CSS

Naming Convention:

component-element-modifier

Examples:

product-card
product-card-title
product-card-featured

Rules:

- Avoid !important
- Prefer reusable classes
- Mobile-first design

## JavaScript

- Use camelCase.
- Use descriptive variable names.
- Prefer const and let.
- Avoid global variables.

Good:

const productPrice = 10;

Bad:

var x = 10;

## File Names

Use:

product-page.html
product-data.json

Avoid:

ProductPageFinalNEW.html

## Images

Format:

product-name-front.webp
product-name-back.webp

## Documentation

Update documentation when:

- Adding major features
- Changing architecture
- Changing deployment workflows

## Accessibility

- Use alt text
- Maintain color contrast
- Support keyboard navigation
- Use proper heading structure