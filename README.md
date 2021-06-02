# RequestBodyValidator
A simple PHP library to make the validation of PSR-7 request's parsed bodies less of a pain to write & read 2 month later.

> GUARANTEED REGEX FREE!

**Note: The doc is not up to date anymore, I'll update it eventually**

## Example
With the classic isset, empty and is_numeric functions
```php
$pb = $request->getParsedBody();
if(
    isset($pb["field"], $pb["field1"], $pb["field2"],  $pb["field3"],  $pb["field4"])
    && !empty($pb["field"]) && !empty($pb["field2"])
    && is_numeric($pb["field3"])
){
    // Do stuff
}
```

With the validator, using the `EXISTS`, `EXISTS_NOT_EMPTY` and `NUMERIC` criterias.

```php
$validator = new RequestBodyValidator($request->getParsedBody());
if(
    $validator->validateMultiple(["field1", "field4"], RequestBodyValidator::EXISTS)
    && $validator->validateMultiple(["field", "field2"], RequestBodyValidator::NOT_EMPTY)
    && $validator->validateOne("field3", RequestBodyValidator::NUMERIC)
){
    // Do stuff
}
```
> Given the fact that if `field3` must be numeric, it must also exist. This is taken into account and you don't need to
> verify that `field3` exists before verifying that it is numeric. This also applies to `field` and `field2` with their
> "non-emptiness".

## Doc 
> Or rather a few lines quickly pieced together to show all the options. See example above as well.
### Methods
```php
// Constructor, $request is your PSR-7 request object
$validator = new RequestBodyValidator($request->getParsedBody());

// validateOne, validates a single field
$validator->validateOne($name, $criteria);

// validateMultiple, validates multiple fields with the same criteria
$validator->validateMultiple([$name, $name1], $criteria);
```

### Criterias
| Class Constant | Purpose | 
|---|---|
|EXISTS|The field exists|
|NOT_EMPTY|The field exists and is not empty|
|NUMERIC|The field exists and is numeric|
|NOT_NUMERIC|The field exists and is not a numeric.|