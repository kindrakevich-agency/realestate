# Realestate profile for Drupal

## What is Realestate?
Realestate is a Drupal 10 profile designed to build real estate websites. It's based on the latest frontend technologies, including Bootstrap 5. The maintainer of Droopler is [Vitaly Kindrakevich](https://kindrakevich.com).

* **Demo**: [dist.kindrakevich.com](https://dist.kindrakevich.com)
* **Composer template**: [github.com/kindrakevich-agency/realestate](https://github.com/kindrakevich-agency/realestate)
* **Drupal.org project**: [drupal.org/project/realestate](https://www.drupal.org/project/realestate)

## What's in this repository?
This repository contains a Drupal profile. When you put it in the `/profiles/contrib/realestate` directory, the Drupal installer gets modified and installs base Realestate theme, some module dependencies, and demo content.

## Installation
The Realestate profile should be installed via Composer. We recommend using [Realestate skeleton repository](https://github.com/kindrakevich-agency/realestate). If you are starting from the scratch - in the **require** section of your composer.json put:

```json
"require": {
  "kindrakevich-agency/realestate": "^10"
}
```

And run **composer update**.

In case of unexpected problems please update your main composer.json to comply with the latest [Realestate skeleton repository](https://github.com/kindrakevich-agency/realestate). 
