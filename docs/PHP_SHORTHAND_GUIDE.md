# PHP Shorthand Guide

This file explains common PHP shorthand and compact syntax you may see in other projects.

## Ternary Operator

Short version:

```php
$status = $role == "hospital" ? "pending" : "active";
```

Full version:

```php
if ($role == "hospital") {
    $status = "pending";
} else {
    $status = "active";
}
```

## Null Coalescing Operator

Short version:

```php
$role = $_POST["role"] ?? "patient";
```

Full version:

```php
if (isset($_POST["role"])) {
    $role = $_POST["role"];
} else {
    $role = "patient";
}
```

## Short Array Syntax

Short version:

```php
$errors = [
    "email" => "",
    "password" => "",
];
```

Older/full version:

```php
$errors = array(
    "email" => "",
    "password" => "",
);
```

## Arrow In Arrays

The `=>` symbol connects a key to a value in an array.

```php
"email" => ""
```

This means the array key is `email`, and its value is currently empty.

## Strict Comparison

Strict comparison checks both value and data type.

```php
if ($role === "patient") {
    // role must be exactly the string "patient"
}
```

Loose comparison checks mostly value.

```php
if ($role == "patient") {
    // easier to read, but less strict
}
```

For this project, we will usually write the clearer full form and avoid compact shortcuts where possible.
